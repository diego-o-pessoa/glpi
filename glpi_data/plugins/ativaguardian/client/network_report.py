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
# Ativa Rede nao instalado no GLPI (404): tenta de novo so depois disto. Era
# 6 h: um 404 passageiro (plugin "a atualizar" no GLPI) deixava a maquina
# instalada naquele momento fora do Ativa Rede por horas.
ABSENT_BACKOFF_SECONDS = 30 * 60
# Falhas seguidas (coleta ou envio) com nova tentativa em 2 min; depois disso
# volta ao intervalo normal para nao rodar PowerShell + captura sem parar.
MAX_QUICK_RETRIES = 3
# O switch anuncia a cada ~30 s (padrao LLDP). 65 s cobre dois anuncios: com
# 35 s, um anuncio atrasado deixava a captura vazia e o etl2pcap falhava.
LLDP_WAIT_SECONDS = 65
# O pktmon tem uma unica sessao no Windows: servico e --network nunca capturam
# juntos (um derrubaria a captura do outro no meio).
CAPTURE_MUTEX = "Global\\AtivaRedeLldpCapture"
API_TIMEOUT_SECONDS = 30
# Como o heartbeat: rede instavel (WinError 10060) tenta de novo no mesmo ciclo.
SEND_RETRY_DELAYS_SECONDS = (5, 15, 45)
# Get-NetIPConfiguration/CIM podem demorar em maquina carregada.
POWERSHELL_TIMEOUT_SECONDS = 180
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

# GetAdaptersAddresses (iphlpapi): rede sem depender do PowerShell.
AF_INET = 2
IF_TYPE_ETHERNET = 6
IF_TYPE_WIFI = 71
IF_OPER_UP = 1
GAA_FLAGS = 0x2 | 0x4 | 0x8 | 0x80  # sem anycast/multicast/DNS; com gateways
# VPN/maquina virtual tambem se declaram "Ethernet": nunca sao a porta do switch.
VIRTUAL_ADAPTER_RE = re.compile(r"TAP-Windows|Wintun|WireGuard|OpenVPN|VirtualBox|VMware|Fortinet|Cisco AnyConnect", re.I)


# ---------------------------------------------------------------------------
# LLDP
# ---------------------------------------------------------------------------

def _hex_id(data: bytes) -> str:
    return ":".join(f"{b:02X}" for b in data)


def _printable(data: bytes) -> str:
    text = data.decode("utf-8", errors="replace")
    return re.sub(r"[\x00-\x1F\x7F]", " ", text).strip()


def parse_lldp_frame(frame: bytes) -> dict[str, Any] | None:
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


def parse_lldp_payload(payload: bytes) -> dict[str, Any] | None:
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
        elif tlv_type == 7 and len(value) >= 4:
            # System Capabilities: suportadas (2 bytes) + habilitadas (2 bytes).
            result["capabilities"] = str(struct.unpack_from(">H", value, 2)[0])
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
        # Bits habilitados (LLDP_CAP_*); -1 = o anuncio nao informou.
        "capabilities": int(result.get("capabilities", "-1")),
    }


# Capacidades LLDP (802.1AB): ponte (switch) e roteador sao infraestrutura;
# PC anuncia "estacao" (ou nada).
LLDP_CAP_BRIDGE = 0x0004
LLDP_CAP_ROUTER = 0x0010


def is_switch_announcement(lldp: dict[str, Any]) -> bool | None:
    """True = switch/roteador; False = PC ou outro host; None = nao da para saber.

    Visto em campo: atras de um switchzinho de mesa, a maquina ouvia o LLDP do
    Windows de OUTRO PC ("DESKTOP-1IRFUQ2"), que virava um switch falso.
    """
    caps = int(lldp.get("capabilities", -1))
    if caps >= 0:
        return bool(caps & (LLDP_CAP_BRIDGE | LLDP_CAP_ROUTER))
    # Sem a TLV de capacidades: switch gerenciavel sempre se descreve (nome,
    # modelo ou IP de gerencia); o anuncio do Windows nao traz nada disso.
    if not (lldp.get("system_name") or lldp.get("system_description") or lldp.get("mgmt_ip")):
        return False
    return None


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


def first_lldp(frames: list[bytes], local_macs: set[str] | frozenset[str] = frozenset()) -> dict[str, Any] | None:
    """Primeiro anuncio do SWITCH. O pktmon ve as duas direcoes, e o proprio
    Windows tambem anuncia LLDP pela placa: quadros que saem de um MAC desta
    maquina (ou com chassis = MAC local) sao ignorados."""
    local = {normalize_mac(m) for m in local_macs if normalize_mac(m)}
    fallback = None
    for frame in frames:
        if len(frame) >= 12 and frame[6:12].hex().upper() in local:
            continue
        parsed = parse_lldp_frame(frame)
        if not parsed or normalize_mac(parsed["chassis_id"]) in local:
            continue
        kind = is_switch_announcement(parsed)
        if kind is True:
            return parsed
        # Anuncio de outro PC (repassado por um switch simples) nunca e a porta.
        if kind is None and fallback is None:
            fallback = parsed
    return fallback


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


def _problem(problems: list[str] | None, text: str) -> None:
    """Motivo de coleta incompleta, mostrado em Equipamentos no GLPI."""
    if problems is not None:
        problems.append(" ".join(text.split())[:200])


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
                 lock_wait_seconds: float = 0, local_macs: frozenset[str] = frozenset(),
                 problems: list[str] | None = None) -> dict[str, Any] | None:
    """Escuta um quadro LLDP com o pktmon. None se nao houver (ou sem suporte).

    Uma coleta por vez (CaptureLock): o servico pula o ciclo se ja houver uma
    em curso; o --network espera (lock_wait_seconds) a automatica terminar.
    Se outra ferramenta ja estiver usando o pktmon, o start falha e esta
    coleta e pulada sem interromper a outra.
    """
    if not PKTMON.is_file():
        logger.info("Ativa Rede: pktmon nao existe neste Windows; porta do switch nao sera informada.")
        _problem(problems, "pktmon nao existe neste Windows (porta do switch indisponivel)")
        return None
    if lock_wait_seconds > 0:
        logger.info("Ativa Rede: aguardando a coleta automatica em andamento (se houver) terminar...")
    with CaptureLock(wait_seconds=lock_wait_seconds) as lock:
        if not lock.acquired:
            logger.info("Ativa Rede: outra coleta do LLDP ja esta em andamento; esta foi pulada.")
            return None
        found = _capture_locked(work_dir, logger, wait, runner, seconds, local_macs, problems)
    if found is None and problems is not None and not any(p.startswith("pktmon") for p in problems):
        _problem(problems, f"Cabo ligado, mas nenhum anuncio LLDP do switch em {seconds} s (switch sem LLDP?)")
    return found


def _capture_locked(work_dir: Path, logger: logging.Logger, wait: Callable[[float], Any],
                    runner: Runner, seconds: int, local_macs: frozenset[str],
                    problems: list[str] | None = None) -> dict[str, Any] | None:
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
        _problem(problems, f"pktmon recusou o filtro LLDP: {_output(added)}")
        return None

    started = _run(runner, [pktmon, "start", "--capture", "--comp", "nics", "--pkt-size", "0", "-f", str(etl)])
    if started is None or started.returncode != 0:
        _run(runner, [pktmon, "filter", "remove"])
        logger.info("Ativa Rede: pktmon nao iniciou (%s).", _output(started))
        _problem(problems, f"pktmon nao iniciou: {_output(started)}")
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
            _problem(problems, f"pktmon nao gravou a captura: {_output(stopped)}")
            return None
        converted = _run(runner, [pktmon, "etl2pcap", str(etl), "-o", str(pcap)], timeout=120)
        if converted is None or converted.returncode != 0 or not pcap.is_file():
            logger.warning("Ativa Rede: nao foi possivel converter a captura do pktmon (%s; etl com %d bytes).",
                           _output(converted), etl.stat().st_size)
            _problem(problems, f"pktmon nao converteu a captura: {_output(converted)}")
            return None
        return first_lldp(read_pcapng_frames(pcap.read_bytes()), local_macs)
    except OSError as exc:
        logger.warning("Ativa Rede: falha ao ler a captura: %s", exc)
        _problem(problems, f"pktmon: falha ao ler a captura: {exc}")
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

def collect_system(logger: logging.Logger, runner: Runner = subprocess.run,
                   problems: list[str] | None = None) -> dict[str, Any] | None:
    """Adaptador, BIOS e monitores. None = leitura falhou (motivo em problems).

    Nunca devolve um resultado vazio "de mentira": um relatorio sem monitores
    faria o GLPI marcar os monitores da mesa como ausentes.
    """
    args = [str(POWERSHELL), "-NoProfile", "-NonInteractive", "-ExecutionPolicy", "Bypass", "-Command", ADAPTER_AND_MONITORS_PS]
    try:
        completed = runner(args, capture_output=True, timeout=POWERSHELL_TIMEOUT_SECONDS, creationflags=NO_WINDOW)
    except subprocess.TimeoutExpired:
        logger.warning("Ativa Rede: PowerShell nao respondeu em %d s (maquina ocupada?); coleta adiada.", POWERSHELL_TIMEOUT_SECONDS)
        _problem(problems, f"PowerShell nao respondeu em {POWERSHELL_TIMEOUT_SECONDS} s (monitores nao lidos)")
        return None
    except (OSError, subprocess.SubprocessError) as exc:
        logger.warning("Ativa Rede: PowerShell nao executou (%s); coleta adiada.", exc)
        _problem(problems, f"PowerShell nao executou: {exc} (bloqueado pelo antivirus?)")
        return None
    if completed.returncode != 0:
        logger.warning("Ativa Rede: PowerShell falhou ao ler adaptador/monitores (%s); coleta adiada.", _output(completed))
        _problem(problems, f"PowerShell terminou com codigo {completed.returncode}: {_output(completed)}")
        return None
    raw = completed.stdout.decode("utf-8", errors="replace") if isinstance(completed.stdout, bytes) else str(completed.stdout)
    try:
        data = json.loads(raw.strip() or "{}")
    except ValueError:
        logger.warning("Ativa Rede: resposta inesperada do PowerShell; coleta adiada.")
        _problem(problems, "PowerShell devolveu uma resposta ilegivel: " + raw.strip()[:120])
        return None
    if not isinstance(data, dict):
        logger.warning("Ativa Rede: resposta inesperada do PowerShell; coleta adiada.")
        _problem(problems, "PowerShell devolveu uma resposta inesperada")
        return None
    return data


def native_network() -> dict[str, Any] | None:
    """Adaptador principal e MACs pela API do Windows (GetAdaptersAddresses).

    Mesmo formato do PowerShell ({adapter, macs}). Usado quando o PowerShell
    falha (lento, travado ou bloqueado pelo antivirus): a maquina ainda manda
    rede e porta do switch. None se a propria API falhar.
    """
    import ctypes

    class SocketAddress(ctypes.Structure):
        _fields_ = [("sockaddr", ctypes.c_void_p), ("length", ctypes.c_int)]

    class Unicast(ctypes.Structure):
        pass

    Unicast._fields_ = [("length", ctypes.c_ulong), ("flags", ctypes.c_ulong),
                        ("next", ctypes.POINTER(Unicast)), ("address", SocketAddress)]

    class Gateway(ctypes.Structure):
        pass

    Gateway._fields_ = [("length", ctypes.c_ulong), ("reserved", ctypes.c_ulong),
                        ("next", ctypes.POINTER(Gateway)), ("address", SocketAddress)]

    class Adapter(ctypes.Structure):
        pass

    Adapter._fields_ = [
        ("length", ctypes.c_ulong), ("if_index", ctypes.c_ulong),
        ("next", ctypes.POINTER(Adapter)), ("adapter_name", ctypes.c_char_p),
        ("first_unicast", ctypes.POINTER(Unicast)), ("first_anycast", ctypes.c_void_p),
        ("first_multicast", ctypes.c_void_p), ("first_dns", ctypes.c_void_p),
        ("dns_suffix", ctypes.c_wchar_p), ("description", ctypes.c_wchar_p),
        ("friendly_name", ctypes.c_wchar_p), ("physical_address", ctypes.c_ubyte * 8),
        ("physical_address_length", ctypes.c_ulong), ("flags", ctypes.c_ulong),
        ("mtu", ctypes.c_ulong), ("if_type", ctypes.c_ulong), ("oper_status", ctypes.c_int),
        ("ipv6_if_index", ctypes.c_ulong), ("zone_indices", ctypes.c_ulong * 16),
        ("first_prefix", ctypes.c_void_p), ("transmit_speed", ctypes.c_uint64),
        ("receive_speed", ctypes.c_uint64), ("first_wins", ctypes.c_void_p),
        ("first_gateway", ctypes.POINTER(Gateway)),
    ]

    def ipv4(address: SocketAddress) -> str:
        if not address.sockaddr or address.length < 8:
            return ""
        raw = ctypes.string_at(address.sockaddr, 8)
        if struct.unpack_from("<H", raw)[0] != AF_INET:
            return ""
        return ".".join(str(b) for b in raw[4:8])

    try:
        iphlpapi = ctypes.WinDLL("iphlpapi")
        size = ctypes.c_ulong(16 * 1024)
        for _ in range(3):
            buffer = ctypes.create_string_buffer(size.value)
            result = iphlpapi.GetAdaptersAddresses(AF_INET, GAA_FLAGS, None, buffer, ctypes.byref(size))
            if result != 111:  # ERROR_BUFFER_OVERFLOW: size ja foi ajustado
                break
        if result != 0:
            return None
    except (OSError, AttributeError):
        return None

    macs: list[str] = []
    candidates: list[tuple[int, dict[str, Any]]] = []
    node = ctypes.cast(buffer, ctypes.POINTER(Adapter))
    while node:
        item = node.contents
        mac = ""
        if item.physical_address_length == 6:
            mac = "-".join(f"{b:02X}" for b in item.physical_address[:6])
            macs.append(mac)
        gateway, ip = "", ""
        if item.first_gateway:
            gateway = ipv4(item.first_gateway.contents.address)
        if item.first_unicast:
            ip = ipv4(item.first_unicast.contents.address)
        virtual = VIRTUAL_ADAPTER_RE.search(item.description or "") is not None
        if item.oper_status == IF_OPER_UP and gateway and not virtual \
                and item.if_type in (IF_TYPE_ETHERNET, IF_TYPE_WIFI):
            wired = item.if_type == IF_TYPE_ETHERNET
            candidates.append((0 if wired else 1, {
                "name": item.friendly_name or "",
                "description": item.description or "",
                "media": "802.3" if wired else "Native 802.11",
                "mac": mac,
                "ip": ip,
            }))
        node = item.next
    # Cabo antes de Wi-Fi: e o que tem porta de switch.
    candidates.sort(key=lambda c: c[0])
    return {"adapter": candidates[0][1] if candidates else None, "macs": macs}


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
                 lldp: dict[str, Any] | None, problems: list[str] | None = None,
                 monitors_unknown: bool = False) -> dict[str, Any]:
    adapter = system.get("adapter") if isinstance(system.get("adapter"), dict) else None
    link = link_type(adapter)
    return {
        # Parcial: o GLPI nao mexe nos monitores (nao e "sem monitores").
        "monitors_unknown": monitors_unknown,
        # Sempre enviado (vazio quando esta tudo certo): limpa o aviso no GLPI.
        "problems": list(problems or [])[:5],
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
                   lock_wait_seconds: float = 0) -> dict[str, Any] | None:
    """Relatorio da maquina, ou None se nem a rede pode ser lida (adiar).

    Sem PowerShell (lento, travado ou bloqueado), o relatorio sai parcial:
    rede pela API do Windows e porta pelo pktmon, sem monitores, com o motivo.
    Antes nada era enviado e a maquina nunca aparecia no Ativa Rede.
    """
    problems: list[str] = []
    system = collect_system(logger, problems=problems)
    partial = system is None
    if partial or not isinstance(system.get("adapter"), dict):
        native = native_network()
        if native is None and partial:
            return None
        if partial:
            system = {"adapter": native["adapter"], "macs": native["macs"], "bios": "", "monitors": []}
            logger.info("Ativa Rede: enviando relatorio parcial (rede sem PowerShell, monitores nao lidos).")
        elif native is not None:
            # Get-NetIPConfiguration falhou sozinho: o resto do PowerShell vale.
            system["adapter"] = native["adapter"]
            known = system.get("macs")
            system["macs"] = ([known] if isinstance(known, str) else list(known or [])) + native["macs"]
    adapter = system.get("adapter") if isinstance(system.get("adapter"), dict) else None
    local_macs = local_mac_set(system)
    lldp = capture_lldp(work_dir, logger, wait=wait, lock_wait_seconds=lock_wait_seconds, local_macs=local_macs,
                        problems=problems) if link_type(adapter) == "wired" else None
    return build_report(machine_id, hostname, guardian_version, system, lldp, problems, monitors_unknown=partial)


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
        # Coleta ou envio falhou: tenta de novo em 2 min, nao em 15 (ate
        # MAX_QUICK_RETRIES vezes seguidas; depois, no intervalo normal).
        self.retry_soon = False
        self.failures = 0

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

    def _send_with_retry(self, url: str, token: str, payload: dict[str, Any]) -> None:
        """Falha de rede/servidor tenta de novo no mesmo ciclo (5, 15, 45 s)."""
        for attempt, delay in enumerate((*SEND_RETRY_DELAYS_SECONDS, None)):
            try:
                send_report(url, token, payload, self.guardian_version)
                return
            except ReportRejected as exc:
                transient = exc.status == 0 or exc.status in (408, 429) or exc.status >= 500
                if not transient or delay is None or self.stop_event.is_set():
                    raise
                self.logger.info("Ativa Rede: tentativa %d falhou (%s); repetindo em %d s.", attempt + 1, exc, delay)
                if self.stop_event.wait(delay):
                    raise

    def run(self) -> None:
        self.logger.info("Ativa Rede: relatorio automatico iniciado (primeira coleta em %d s).", FIRST_DELAY_SECONDS)
        self._sleep(FIRST_DELAY_SECONDS)
        while not self.stop_event.is_set():
            try:
                self.cycle()
            except Exception:  # noqa: BLE001 - nunca derruba o servico
                self.logger.exception("Ativa Rede: falha inesperada no ciclo.")
                self.retry_soon = True
            self.failures = self.failures + 1 if self.retry_soon else 0
            soon = self.confirm_next or (self.retry_soon and self.failures <= MAX_QUICK_RETRIES)
            self._sleep(CONFIRM_DELAY_SECONDS if soon else REPORT_INTERVAL_SECONDS)

    def cycle(self) -> None:
        now = time.monotonic()
        forced, self.force_send = self.force_send, False
        self.retry_soon = False
        if now < self.absent_until and not forced:
            return
        try:
            config = self.load_config()
            url = rede_url(str(config["api_url"]))
            token = str(config["api_token"])
        except Exception as exc:  # noqa: BLE001
            self.logger.warning("Ativa Rede: sem configuracao valida (%s); coleta pulada.", exc)
            self.retry_soon = True
            return

        payload = collect_report(self.machine_id, self.hostname(), self.guardian_version, self.work_dir,
                                 self.logger, wait=self.stop_event.wait)
        if self.stop_event.is_set():
            return
        if payload is None:
            # Leitura do Windows falhou: nada e enviado (um relatorio sem
            # monitores marcaria os monitores como ausentes).
            self.retry_soon = True
            self.force_send = self.force_send or forced
            return
        current = signature(payload)
        changed = current != self.last_signature
        if not changed and not self.confirm_next and not forced and now - self.last_sent < RESEND_SECONDS:
            return
        try:
            self._send_with_retry(url, token, payload)
        except ReportRejected as exc:
            if exc.status == 404:
                self.absent_until = time.monotonic() + ABSENT_BACKOFF_SECONDS
                self.logger.info("Ativa Rede nao respondeu no GLPI (404); nova tentativa em %d min.",
                                 ABSENT_BACKOFF_SECONDS // 60)
            else:
                self.retry_soon = True
                self.force_send = self.force_send or forced
                self.logger.warning("Ativa Rede: envio falhou: %s (nova tentativa em 2 min)", exc)
            return
        # Primeiro envio do servico tambem conta como mudanca (o GLPI pode ter
        # outra posicao guardada de antes do reinicio).
        self.confirm_next = changed
        self.last_signature = current
        self.last_sent = time.monotonic()
        # Parcial: tenta a leitura completa (monitores) de novo em breve.
        self.retry_soon = bool(payload.get("monitors_unknown"))
        lldp = payload["network"]["lldp"]
        self.logger.info(
            "Ativa Rede: posicao enviada (%s%s, %s).",
            payload["network"]["link"],
            f", switch {lldp.get('mgmt_ip') or lldp.get('chassis_id')} porta {lldp.get('port_description') or lldp.get('port_id')}" if lldp else "",
            "monitores nao lidos" if payload.get("monitors_unknown") else f"{len(payload['monitors'])} monitor(es)",
        )
        if payload.get("problems"):
            self.logger.info("Ativa Rede: avisos da coleta: %s", " | ".join(payload["problems"]))
