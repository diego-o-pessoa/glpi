"""Ativa Rede - posicao fisica da maquina, enviada pelo Ativa Guardian.

Descobre em qual switch/porta a maquina esta ligada e quais monitores estao
conectados, e envia ao plugin Ativa Rede do GLPI (mesmo host e mesmo token da
API do Guardian).

- Switch/porta: o switch anuncia em cada porta, a cada ~30 s, um quadro LLDP
  ("sou o switch X, esta e a porta 15"). O Guardian so ESCUTA esse quadro com o
  pktmon, o capturador de pacotes que ja vem no Windows 10/11 - nada e enviado
  para a rede e nada e instalado. Exige SYSTEM/Administrador (o servico ja e).
- Monitores: WMI (WmiMonitorID / WmiMonitorConnectionParams), so leitura.
  Telas internas de notebook ficam de fora (nao mudam de mesa sozinhas).

Tudo aqui e best-effort: qualquer falha vira log e o proximo ciclo tenta de
novo; nunca derruba o servico.
"""

from __future__ import annotations

import json
import logging
import os
import re
import ssl
import struct
import subprocess
import threading
import time
from pathlib import Path
from typing import Any, Callable
from urllib.error import HTTPError, URLError
from urllib.parse import urlsplit
from urllib.request import HTTPRedirectHandler, HTTPSHandler, Request, build_opener

GUARDIAN_API_SUFFIX = "/plugins/ativaguardian/api/v1"
REDE_API_SUFFIX = "/plugins/ativarede/api/v1"

# Coleta a cada 15 min; o envio so sai quando algo mudou ou a cada 6 h.
REPORT_INTERVAL_SECONDS = 15 * 60
# Depois de uma mudanca, a coleta de confirmacao (o GLPI so registra a troca
# de mesa com dois relatorios seguidos) sai logo, nao em 15 min.
CONFIRM_DELAY_SECONDS = 2 * 60
RESEND_SECONDS = 6 * 3600
FIRST_DELAY_SECONDS = 90
# Ativa Rede nao instalado no GLPI (404): tenta de novo so depois disto.
ABSENT_BACKOFF_SECONDS = 6 * 3600
# O switch anuncia a cada ~30 s (padrao LLDP). 65 s cobre dois anuncios: com
# 35 s, um anuncio atrasado deixava a captura vazia e o etl2pcap falhava.
LLDP_WAIT_SECONDS = 65
# O pktmon tem uma unica sessao no Windows: servico e --network nunca capturam
# juntos (um derrubaria a captura do outro no meio).
CAPTURE_MUTEX = "Global\\AtivaRedeLldpCapture"
API_TIMEOUT_SECONDS = 30
MAX_MONITORS = 8

LLDP_ETHERTYPE = 0x88CC
VLAN_ETHERTYPES = (0x8100, 0x88A8)
NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)
SYSTEM32 = Path(os.environ.get("SystemRoot", r"C:\Windows")) / "System32"
PKTMON = SYSTEM32 / "pktmon.exe"
POWERSHELL = SYSTEM32 / "WindowsPowerShell" / "v1.0" / "powershell.exe"

# VideoOutputTechnology (D3DKMDT_VIDEO_OUTPUT_TECHNOLOGY).
VIDEO_OUTPUTS = {
    0: "VGA", 1: "S-Video", 2: "Composto", 3: "Componente", 4: "DVI", 5: "HDMI",
    8: "D-Jpn", 9: "SDI", 10: "DisplayPort", 12: "UDI", 14: "SDTV", 15: "Miracast", 16: "USB/Indireto",
}
# LVDS, DisplayPort embutido, UDI embutido e "interno": tela do proprio notebook.
INTERNAL_OUTPUTS = {6, 11, 13, 0x80000000, -0x80000000}

ADAPTER_AND_MONITORS_PS = r"""
$ErrorActionPreference = 'SilentlyContinue'
function Dec($a) { if (-not $a) { return '' }; return (-join ($a | Where-Object { $_ -ne 0 } | ForEach-Object { [char]$_ })).Trim() }
$r = [ordered]@{ adapter = $null; bios = ''; monitors = @() }
$cfg = Get-NetIPConfiguration | Where-Object { $_.IPv4DefaultGateway -and $_.NetAdapter.Status -eq 'Up' } | Select-Object -First 1
if ($cfg) {
  $r.adapter = [ordered]@{
    name = [string]$cfg.InterfaceAlias
    description = [string]$cfg.NetAdapter.InterfaceDescription
    media = [string]$cfg.NetAdapter.PhysicalMediaType
    mac = [string]$cfg.NetAdapter.MacAddress
    ip = [string](($cfg.IPv4Address | Select-Object -First 1).IPAddress)
  }
}
$r.bios = [string](Get-CimInstance -ClassName Win32_BIOS).SerialNumber
$r.macs = @(Get-NetAdapter -IncludeHidden | Where-Object { $_.MacAddress } | ForEach-Object { [string]$_.MacAddress })
$conn = @(Get-CimInstance -Namespace root\wmi -ClassName WmiMonitorConnectionParams)
$r.monitors = @(Get-CimInstance -Namespace root\wmi -ClassName WmiMonitorID | ForEach-Object {
  $id = $_
  $c = $conn | Where-Object { $_.InstanceName -eq $id.InstanceName } | Select-Object -First 1
  [ordered]@{
    active = [bool]$id.Active
    manufacturer = Dec $id.ManufacturerName
    product = Dec $id.ProductCodeID
    serial = Dec $id.SerialNumberID
    name = Dec $id.UserFriendlyName
    year = [int]$id.YearOfManufacture
    week = [int]$id.WeekOfManufacture
    output = if ($c) { [int64]$c.VideoOutputTechnology } else { -1 }
  }
})
$r | ConvertTo-Json -Depth 5 -Compress
"""

Runner = Callable[..., subprocess.CompletedProcess]


# ---------------------------------------------------------------------------
# LLDP
# ---------------------------------------------------------------------------

def _hex_id(data: bytes) -> str:
    return ":".join(f"{b:02X}" for b in data)


def _printable(data: bytes) -> str:
    text = data.decode("utf-8", errors="replace")
    return re.sub(r"[\x00-\x1F\x7F]", " ", text).strip()


def parse_lldp_frame(frame: bytes) -> dict[str, str] | None:
    """Quadro Ethernet completo -> campos LLDP, ou None se nao for LLDP."""
    if len(frame) < 16:
        return None
    offset = 12
    ethertype = struct.unpack_from(">H", frame, offset)[0]
    while ethertype in VLAN_ETHERTYPES and len(frame) >= offset + 6:
        offset += 4
        ethertype = struct.unpack_from(">H", frame, offset)[0]
    if ethertype != LLDP_ETHERTYPE:
        return None
    return parse_lldp_payload(frame[offset + 2:])


def parse_lldp_payload(payload: bytes) -> dict[str, str] | None:
    result: dict[str, str] = {}
    mgmt_v4 = ""
    pos = 0
    while pos + 2 <= len(payload):
        header = struct.unpack_from(">H", payload, pos)[0]
        tlv_type, length = header >> 9, header & 0x1FF
        pos += 2
        if tlv_type == 0:
            break
        value = payload[pos:pos + length]
        pos += length
        if len(value) < length:
            break
        if tlv_type in (1, 2) and value:
            subtype, data = value[0], value[1:]
            # 4 = MAC (chassis) / 3 = MAC (porta); demais sao texto.
            is_mac = (tlv_type == 1 and subtype == 4) or (tlv_type == 2 and subtype == 3)
            text = _hex_id(data) if is_mac else _printable(data)
            result["chassis_id" if tlv_type == 1 else "port_id"] = text
        elif tlv_type == 4:
            result["port_description"] = _printable(value)
        elif tlv_type == 5:
            result["system_name"] = _printable(value)
        elif tlv_type == 6:
            result["system_description"] = _printable(value)
        elif tlv_type == 8 and len(value) >= 2:
            addr_len = value[0]
            subtype = value[1]
            addr = value[2:1 + addr_len]
            if subtype == 1 and len(addr) == 4 and not mgmt_v4:
                mgmt_v4 = ".".join(str(b) for b in addr)
    if not result.get("chassis_id"):
        return None
    result["mgmt_ip"] = mgmt_v4
    return {
        "chassis_id": result.get("chassis_id", "")[:64],
        "port_id": result.get("port_id", "")[:128],
        "port_description": result.get("port_description", "")[:128],
        "system_name": result.get("system_name", "")[:255],
        "system_description": result.get("system_description", "")[:255],
        "mgmt_ip": result.get("mgmt_ip", "")[:64],
    }


def read_pcapng_frames(data: bytes) -> list[bytes]:
    """Pacotes Ethernet de um arquivo pcapng (formato gerado pelo pktmon etl2pcap)."""
    frames: list[bytes] = []
    pos = 0
    endian = "<"
    link_types: list[int] = []
    while pos + 12 <= len(data):
        block_type = struct.unpack_from(endian + "I", data, pos)[0]
        if block_type == 0x0A0D0D0A:  # Section Header: define a ordem dos bytes
            magic = data[pos + 8:pos + 12]
            endian = "<" if magic == b"\x4d\x3c\x2b\x1a" else ">"
            link_types = []
        total = struct.unpack_from(endian + "I", data, pos + 4)[0]
        if total < 12 or pos + total > len(data):
            break
        body = data[pos + 8:pos + total - 4]
        if block_type == 1 and len(body) >= 2:  # Interface Description
            link_types.append(struct.unpack_from(endian + "H", body, 0)[0])
        elif block_type == 6 and len(body) >= 20:  # Enhanced Packet
            interface = struct.unpack_from(endian + "I", body, 0)[0]
            captured = struct.unpack_from(endian + "I", body, 12)[0]
            if interface >= len(link_types) or link_types[interface] == 1:
                frames.append(body[20:20 + captured])
        elif block_type == 3 and len(body) >= 4:  # Simple Packet
            original = struct.unpack_from(endian + "I", body, 0)[0]
            frames.append(body[4:4 + original])
        pos += total
    return frames


def normalize_mac(value: str) -> str:
    return re.sub(r"[^0-9A-F]", "", str(value).upper())


def first_lldp(frames: list[bytes], local_macs: set[str] | frozenset[str] = frozenset()) -> dict[str, str] | None:
    """Primeiro anuncio do SWITCH. O pktmon ve as duas direcoes, e o proprio
    Windows tambem anuncia LLDP pela placa: quadros que saem de um MAC desta
    maquina (ou com chassis = MAC local) sao ignorados."""
    local = {normalize_mac(m) for m in local_macs if normalize_mac(m)}
    for frame in frames:
        if len(frame) >= 12 and frame[6:12].hex().upper() in local:
            continue
        parsed = parse_lldp_frame(frame)
        if parsed and normalize_mac(parsed["chassis_id"]) not in local:
            return parsed
    return None


def _output(completed: subprocess.CompletedProcess | None) -> str:
    """Saida do pktmon (console do Windows, nao UTF-8) resumida para o log."""
    if completed is None:
        return "nao executou"
    raw = (completed.stdout or b"") + (completed.stderr or b"")
    if isinstance(raw, bytes):
        text = raw.decode("mbcs" if os.name == "nt" else "utf-8", errors="replace")
    else:
        text = str(raw)
    text = re.sub(r"\s+", " ", text).strip()
    return f"codigo {completed.returncode}: {text[:400]}"


def _run(runner: Runner, args: list[str], timeout: int = 60) -> subprocess.CompletedProcess | None:
    try:
        return runner(args, capture_output=True, timeout=timeout, creationflags=NO_WINDOW)
    except (OSError, subprocess.SubprocessError):
        return None


class CaptureLock:
    """Mutex nomeado do Windows; acquired=False quando outra coleta esta em curso."""

    def __init__(self, name: str = CAPTURE_MUTEX, wait_seconds: float = 0) -> None:
        self.name = name
        self.wait_ms = max(0, int(wait_seconds * 1000))
        self.handle = None
        self.acquired = False

    def __enter__(self) -> "CaptureLock":
        if os.name != "nt":
            self.acquired = True
            return self
        import ctypes

        kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
        kernel32.CreateMutexW.restype = ctypes.c_void_p
        kernel32.CreateMutexW.argtypes = [ctypes.c_void_p, ctypes.c_bool, ctypes.c_wchar_p]
        kernel32.WaitForSingleObject.argtypes = [ctypes.c_void_p, ctypes.c_uint32]
        kernel32.WaitForSingleObject.restype = ctypes.c_uint32
        self.handle = kernel32.CreateMutexW(None, False, self.name)
        if not self.handle:
            return self  # sem acesso ao mutex de outro processo: tratado como ocupado
        result = kernel32.WaitForSingleObject(self.handle, self.wait_ms)
        self.acquired = result in (0x00000000, 0x00000080)  # WAIT_OBJECT_0 / WAIT_ABANDONED
        return self

    def __exit__(self, *_exc: object) -> None:
        if os.name != "nt" or not self.handle:
            return
        import ctypes

        kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
        kernel32.ReleaseMutex.argtypes = [ctypes.c_void_p]
        kernel32.CloseHandle.argtypes = [ctypes.c_void_p]
        if self.acquired:
            kernel32.ReleaseMutex(self.handle)
        kernel32.CloseHandle(self.handle)


def capture_lldp(work_dir: Path, logger: logging.Logger, wait: Callable[[float], Any] = time.sleep,
                 runner: Runner = subprocess.run, seconds: int = LLDP_WAIT_SECONDS,
                 lock_wait_seconds: float = 0, local_macs: frozenset[str] = frozenset()) -> dict[str, str] | None:
    """Escuta um quadro LLDP com o pktmon. None se nao houver (ou sem suporte).

    Uma coleta por vez (CaptureLock): o servico pula o ciclo se ja houver uma
    em curso; o --network espera (lock_wait_seconds) a automatica terminar.
    Se outra ferramenta ja estiver usando o pktmon, o start falha e esta
    coleta e pulada sem interromper a outra.
    """
    if not PKTMON.is_file():
        logger.info("Ativa Rede: pktmon nao existe neste Windows; porta do switch nao sera informada.")
        return None
    if lock_wait_seconds > 0:
        logger.info("Ativa Rede: aguardando a coleta automatica em andamento (se houver) terminar...")
    with CaptureLock(wait_seconds=lock_wait_seconds) as lock:
        if not lock.acquired:
            logger.info("Ativa Rede: outra coleta do LLDP ja esta em andamento; esta foi pulada.")
            return None
        return _capture_locked(work_dir, logger, wait, runner, seconds, local_macs)


def _capture_locked(work_dir: Path, logger: logging.Logger, wait: Callable[[float], Any],
                    runner: Runner, seconds: int, local_macs: frozenset[str]) -> dict[str, str] | None:
    work_dir.mkdir(parents=True, exist_ok=True)
    etl = work_dir / "lldp.etl"
    pcap = work_dir / "lldp.pcapng"
    for path in (etl, pcap):
        _unlink(path)

    pktmon = str(PKTMON)
    _run(runner, [pktmon, "filter", "remove"])
    added = _run(runner, [pktmon, "filter", "add", "AtivaRede-LLDP", "-d", "0x88CC"])
    if added is None or added.returncode != 0:
        added = _run(runner, [pktmon, "filter", "add", "AtivaRede-LLDP", "-d", str(LLDP_ETHERTYPE)])
    if added is None or added.returncode != 0:
        logger.warning("Ativa Rede: pktmon recusou o filtro LLDP (%s); coleta da porta pulada.", _output(added))
        return None

    started = _run(runner, [pktmon, "start", "--capture", "--comp", "nics", "--pkt-size", "0", "-f", str(etl)])
    if started is None or started.returncode != 0:
        _run(runner, [pktmon, "filter", "remove"])
        logger.info("Ativa Rede: pktmon nao iniciou (%s).", _output(started))
        return None
    try:
        wait(seconds)
    finally:
        stopped = _run(runner, [pktmon, "stop"], timeout=120)
        _run(runner, [pktmon, "filter", "remove"])
    logger.debug("Ativa Rede: pktmon stop (%s).", _output(stopped))

    try:
        if not etl.is_file():
            logger.warning("Ativa Rede: o pktmon nao gravou a captura em %s (stop: %s).", etl, _output(stopped))
            return None
        converted = _run(runner, [pktmon, "etl2pcap", str(etl), "-o", str(pcap)], timeout=120)
        if converted is None or converted.returncode != 0 or not pcap.is_file():
            logger.warning("Ativa Rede: nao foi possivel converter a captura do pktmon (%s; etl com %d bytes).",
                           _output(converted), etl.stat().st_size)
            return None
        return first_lldp(read_pcapng_frames(pcap.read_bytes()), local_macs)
    except OSError as exc:
        logger.warning("Ativa Rede: falha ao ler a captura: %s", exc)
        return None
    finally:
        for path in (etl, pcap):
            _unlink(path)


def _unlink(path: Path) -> None:
    try:
        path.unlink()
    except FileNotFoundError:
        pass
    except OSError:
        pass


# ---------------------------------------------------------------------------
# Adaptador, BIOS e monitores
# ---------------------------------------------------------------------------

def collect_system(logger: logging.Logger, runner: Runner = subprocess.run) -> dict[str, Any]:
    completed = _run(runner, [
        str(POWERSHELL), "-NoProfile", "-NonInteractive", "-ExecutionPolicy", "Bypass", "-Command", ADAPTER_AND_MONITORS_PS,
    ], timeout=90)
    if completed is None or completed.returncode != 0:
        logger.warning("Ativa Rede: nao foi possivel ler adaptador/monitores via PowerShell.")
        return {}
    raw = completed.stdout.decode("utf-8", errors="replace") if isinstance(completed.stdout, bytes) else str(completed.stdout)
    try:
        data = json.loads(raw.strip() or "{}")
    except ValueError:
        logger.warning("Ativa Rede: resposta inesperada do PowerShell.")
        return {}
    return data if isinstance(data, dict) else {}


def link_type(adapter: dict[str, Any] | None) -> str:
    if not adapter:
        return "none"
    media = str(adapter.get("media", "")).lower()
    description = str(adapter.get("description", "")).lower()
    if "802.11" in media or "wi-fi" in description or "wireless" in description or "wlan" in description:
        return "wifi"
    if "802.3" in media or media == "":
        return "wired"
    return "unknown"


def normalize_monitors(raw: Any) -> list[dict[str, Any]]:
    if isinstance(raw, dict):
        raw = [raw]
    if not isinstance(raw, list):
        return []
    monitors = []
    for item in raw:
        if not isinstance(item, dict) or not item.get("active", True):
            continue
        try:
            output = int(item.get("output", -1))
        except (TypeError, ValueError):
            output = -1
        if output in INTERNAL_OUTPUTS:
            continue
        monitors.append({
            "manufacturer": str(item.get("manufacturer", ""))[:32],
            "product_code": str(item.get("product", ""))[:32],
            "serial": str(item.get("serial", ""))[:128],
            "model": str(item.get("name", ""))[:128],
            "year": _int(item.get("year")),
            "week": _int(item.get("week")),
            "connection": VIDEO_OUTPUTS.get(output, ""),
        })
    return monitors[:MAX_MONITORS]


def _int(value: Any) -> int:
    try:
        return int(value)
    except (TypeError, ValueError):
        return 0


def local_mac_set(system: dict[str, Any]) -> frozenset[str]:
    """MACs de todas as placas desta maquina (inclusive virtuais)."""
    macs = system.get("macs")
    if isinstance(macs, str):
        macs = [macs]
    found = {normalize_mac(m) for m in (macs if isinstance(macs, list) else [])}
    adapter = system.get("adapter")
    if isinstance(adapter, dict):
        found.add(normalize_mac(adapter.get("mac", "")))
    return frozenset(m for m in found if len(m) == 12)


def build_report(machine_id: str, hostname: str, guardian_version: str, system: dict[str, Any],
                 lldp: dict[str, str] | None) -> dict[str, Any]:
    adapter = system.get("adapter") if isinstance(system.get("adapter"), dict) else None
    link = link_type(adapter)
    return {
        "machine_id": machine_id,
        "hostname": hostname,
        "guardian_version": guardian_version,
        "bios_serial": str(system.get("bios", "") or "")[:128],
        "network": {
            "link": link,
            "ip": str((adapter or {}).get("ip", ""))[:64],
            "mac": str((adapter or {}).get("mac", "")).replace("-", ":")[:32],
            "lldp": lldp if link == "wired" else None,
        },
        "monitors": normalize_monitors(system.get("monitors")),
    }


def collect_report(machine_id: str, hostname: str, guardian_version: str, work_dir: Path,
                   logger: logging.Logger, wait: Callable[[float], Any] = time.sleep,
                   lock_wait_seconds: float = 0) -> dict[str, Any]:
    system = collect_system(logger)
    adapter = system.get("adapter") if isinstance(system.get("adapter"), dict) else None
    local_macs = local_mac_set(system)
    lldp = capture_lldp(work_dir, logger, wait=wait, lock_wait_seconds=lock_wait_seconds, local_macs=local_macs) \
        if link_type(adapter) == "wired" else None
    return build_report(machine_id, hostname, guardian_version, system, lldp)


# ---------------------------------------------------------------------------
# Envio
# ---------------------------------------------------------------------------

def rede_url(guardian_api_url: str) -> str:
    """Mesma origem do Guardian, endpoint do Ativa Rede."""
    base = guardian_api_url.rstrip("/")
    if not base.endswith(GUARDIAN_API_SUFFIX):
        raise ValueError("api_url do Guardian inesperada.")
    url = base[: -len(GUARDIAN_API_SUFFIX)] + REDE_API_SUFFIX + "/report"
    if urlsplit(url).scheme.lower() != "https":
        raise ValueError("O Ativa Rede so aceita HTTPS.")
    return url


class _NoRedirect(HTTPRedirectHandler):
    """Seguir redirecionamento levaria o token para outro host."""

    def redirect_request(self, req, fp, code, msg, headers, newurl):
        raise HTTPError(req.full_url, code, "Redirecionamento recusado", headers, fp)


class ReportRejected(RuntimeError):
    def __init__(self, status: int, detail: str) -> None:
        super().__init__(f"HTTP {status}: {detail}")
        self.status = status


def send_report(url: str, token: str, payload: dict[str, Any], guardian_version: str) -> None:
    opener = build_opener(_NoRedirect(), HTTPSHandler(context=ssl.create_default_context()))
    request = Request(url, data=json.dumps(payload).encode("utf-8"), method="POST", headers={
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": f"AtivaGuardian/{guardian_version}",
    })
    try:
        with opener.open(request, timeout=API_TIMEOUT_SECONDS) as response:
            if response.status not in (200, 202):
                raise ReportRejected(response.status, "resposta inesperada")
    except HTTPError as exc:
        detail = exc.read(512).decode("utf-8", errors="replace") if exc.fp else ""
        raise ReportRejected(exc.code, detail) from exc
    except (URLError, OSError) as exc:
        raise ReportRejected(0, f"falha de rede: {exc}") from exc


def signature(payload: dict[str, Any]) -> str:
    return json.dumps(payload, sort_keys=True)


class NetworkReporter(threading.Thread):
    """Laco proprio (a captura leva ~65 s e nao pode atrasar o heartbeat)."""

    def __init__(self, stop_event: threading.Event, logger: logging.Logger, machine_id: str,
                 hostname: Callable[[], str], guardian_version: str, work_dir: Path,
                 load_config: Callable[[], dict[str, Any]]) -> None:
        super().__init__(name="AtivaRedeReporter", daemon=True)
        self.stop_event = stop_event
        self.logger = logger
        self.machine_id = machine_id
        self.hostname = hostname
        self.guardian_version = guardian_version
        self.work_dir = work_dir
        self.load_config = load_config
        self.last_signature: str | None = None
        self.last_sent = 0.0
        self.absent_until = 0.0
        # O GLPI so confirma a troca de porta com dois relatorios seguidos na
        # porta nova: depois de uma mudanca, o ciclo seguinte reenvia mesmo
        # igual (senao a confirmacao esperaria o reenvio de 6 h).
        self.confirm_next = False
        # "Atualizar agora" na planta: acorda o laco e envia mesmo sem mudanca.
        self.wake = threading.Event()
        self.force_send = False

    def request_now(self) -> None:
        self.force_send = True
        self.wake.set()

    def _sleep(self, seconds: float) -> None:
        """Espera o intervalo, acordando antes se pedirem coleta ou o servico parar."""
        deadline = time.monotonic() + seconds
        while not self.stop_event.is_set():
            remaining = deadline - time.monotonic()
            if remaining <= 0 or self.wake.wait(min(remaining, 5)):
                break
        self.wake.clear()

    def run(self) -> None:
        self._sleep(FIRST_DELAY_SECONDS)
        while not self.stop_event.is_set():
            try:
                self.cycle()
            except Exception:  # noqa: BLE001 - nunca derruba o servico
                self.logger.exception("Ativa Rede: falha inesperada no ciclo.")
            self._sleep(CONFIRM_DELAY_SECONDS if self.confirm_next else REPORT_INTERVAL_SECONDS)

    def cycle(self) -> None:
        now = time.monotonic()
        forced, self.force_send = self.force_send, False
        if now < self.absent_until and not forced:
            return
        try:
            config = self.load_config()
            url = rede_url(str(config["api_url"]))
            token = str(config["api_token"])
        except Exception as exc:  # noqa: BLE001
            self.logger.debug("Ativa Rede: sem configuracao valida (%s).", exc)
            return

        payload = collect_report(self.machine_id, self.hostname(), self.guardian_version, self.work_dir,
                                 self.logger, wait=self.stop_event.wait)
        if self.stop_event.is_set():
            return
        current = signature(payload)
        changed = current != self.last_signature
        if not changed and not self.confirm_next and not forced and now - self.last_sent < RESEND_SECONDS:
            return
        try:
            send_report(url, token, payload, self.guardian_version)
        except ReportRejected as exc:
            if exc.status == 404:
                self.absent_until = time.monotonic() + ABSENT_BACKOFF_SECONDS
                self.logger.info("Ativa Rede nao esta instalado no GLPI; nova tentativa em 6 h.")
            else:
                self.logger.warning("Ativa Rede: envio falhou: %s", exc)
            return
        # Primeiro envio do servico tambem conta como mudanca (o GLPI pode ter
        # outra posicao guardada de antes do reinicio).
        self.confirm_next = changed
        self.last_signature = current
        self.last_sent = time.monotonic()
        lldp = payload["network"]["lldp"]
        self.logger.info(
            "Ativa Rede: posicao enviada (%s%s, %d monitor(es)).",
            payload["network"]["link"],
            f", switch {lldp.get('mgmt_ip') or lldp.get('chassis_id')} porta {lldp.get('port_description') or lldp.get('port_id')}" if lldp else "",
            len(payload["monitors"]),
        )
