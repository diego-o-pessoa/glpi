"""Ativa Guardian - servico Windows de monitoramento de saude.

Verifica os componentes Ativa instalados na maquina e envia um heartbeat para a
API do plugin Ativa Guardian no GLPI, e executa acoes corretivas enfileiradas
pelo painel: CHECK_COMPONENT, START_COMPONENT, RESTART_COMPONENT e
REPAIR_COMPONENT (por enquanto so o Ativa Updater sabe se reinstalar).

O servico nunca executa comando enviado como texto: o servidor manda apenas um
par (componente, acao) de listas fechadas, e o mapeamento para servicos reais
do Windows vive neste arquivo (COMPONENT_SERVICES / REPAIR_HANDLERS).

O reparo baixa o pacote oficial publicado na API do proprio Ativa Updater, no
mesmo host que o Guardian ja usa. A URL nunca vem da acao e o arquivo so e
executado depois que o SHA-256 confere. Nada aqui desativa ou contorna
antivirus: se o AV remover o componente de novo, isso vira erro reportado.

Caminhos e nomes de servico dos componentes nao foram inventados: vieram do
codigo que ja os instala e gerencia (Ativa Updater / instalador unificado).

Modos:
    --service            executado pelo Windows Service Control Manager
    --run-once           uma verificacao + heartbeat, util para teste
    --check              so imprime o diagnostico, sem enviar nada
    --configure <json>   grava config.json protegido em ProgramData
    --install-service    registra o servico no Windows
    --uninstall-service  remove o servico
    --version            imprime a versao
"""

from __future__ import annotations

import argparse
import ctypes
import hashlib
import json
import logging
import os
import re
import shutil
import ssl
import subprocess
import sys
import threading
import time
import uuid
from ctypes import wintypes
from logging.handlers import RotatingFileHandler
from pathlib import Path
from typing import Any
from urllib.error import HTTPError, URLError
from urllib.parse import quote, urlsplit
from urllib.request import HTTPRedirectHandler, HTTPSHandler, Request, build_opener

GUARDIAN_VERSION = "1.5.1"

SERVICE_NAME = "AtivaGuardian"
SERVICE_DISPLAY_NAME = "Ativa Guardian"
SERVICE_DESCRIPTION = "Monitora a saude dos componentes Ativa e reporta ao GLPI."

PROGRAM_DATA = Path(os.environ.get("ProgramData", r"C:\ProgramData"))
PROGRAM_FILES = Path(os.environ.get("ProgramFiles", r"C:\Program Files"))

PRODUCT_DIR = PROGRAM_DATA / "AtivaLocacao" / "Guardian"
CONFIG_PATH = PRODUCT_DIR / "config.json"
MACHINE_PATH = PRODUCT_DIR / "machine.json"
LOG_DIR = PRODUCT_DIR / "logs"
LOG_NAME = "guardian.log"
INSTALL_DIR = PROGRAM_FILES / "Ativa Locacao" / "Guardian"
SERVICE_EXE = INSTALL_DIR / "AtivaGuardian.exe"

MUTEX_NAME = r"Global\AtivaGuardianService"

DEFAULT_INTERVAL_SECONDS = 30
MIN_INTERVAL_SECONDS = 30
MAX_INTERVAL_SECONDS = 86400
API_TIMEOUT_SECONDS = 30
# Backoff between heartbeat attempts. The loop never gives up for good: after the
# last delay it simply waits for the next cycle.
API_RETRY_DELAYS_SECONDS = (5, 15, 45)
# Teto de leitura das respostas da API; a fila de acoes e sempre pequena.
MAX_RESPONSE_BYTES = 65536
# Acoes sao consultadas entre um heartbeat e outro, para o clique no painel
# nao esperar os 5 minutos do ciclo completo.
ACTION_POLL_SECONDS = 30
ANTIVIRUS_CACHE_SECONDS = 3600

API_PATH_SUFFIX = "/plugins/ativaguardian/api/v1"

# --- Status aceitos pela API (espelham GlpiPlugin\Ativaguardian\HealthStatus) ---
# "offline" nao entra: e derivado no servidor quando o heartbeat para de chegar,
# e a API rejeita um componente que reporte esse valor.
STATUS_HEALTHY = "healthy"
STATUS_WARNING = "warning"
STATUS_ERROR = "error"
STATUS_SERVICE_STOPPED = "service_stopped"
STATUS_PROCESS_STOPPED = "process_stopped"
STATUS_FILE_MISSING = "file_missing"
STATUS_VERSION_OUTDATED = "version_outdated"
STATUS_UNKNOWN = "unknown"

REPORTABLE_STATUSES = frozenset({
    STATUS_HEALTHY, STATUS_WARNING, STATUS_ERROR, STATUS_SERVICE_STOPPED,
    STATUS_PROCESS_STOPPED, STATUS_FILE_MISSING, STATUS_VERSION_OUTDATED, STATUS_UNKNOWN,
})

# --- Componentes monitorados -------------------------------------------------
# Ativa Updater: servico SYSTEM. Nome e caminho vindos de unified_updater_service.py.
UPDATER_SERVICE_NAME = "AtivaUnifiedUpdater"
UPDATER_DIR = PROGRAM_DATA / "AtivaLocacao" / "UnifiedUpdater"
UPDATER_EXE = UPDATER_DIR / "AtivaUnifiedUpdater.exe"
# O proprio Updater escreve este arquivo a cada ciclo; ler dele evita disparar
# outro processo so para descobrir a versao.
UPDATER_HEARTBEAT_PATH = UPDATER_DIR / "heartbeat.json"

# Ativa Wallpaper: roda POR USUARIO (HKCU), nao e servico.
WALLPAPER_DIR = PROGRAM_DATA / "AtivaLocacao" / "Wallpaper"
WALLPAPER_EXE = WALLPAPER_DIR / "AtivaWallpaperClient.exe"
WALLPAPER_VERSION_PATH = WALLPAPER_DIR / "version.json"

# Ativa Workspace: servico SYSTEM proprio (provisionamento / Entra ID),
# instalado pelo pacote unificado. Grava version.json ao iniciar.
WORKSPACE_SERVICE_NAME = "AtivaWorkspace"
WORKSPACE_DIR = PROGRAM_DATA / "AtivaLocacao" / "Workspace"
WORKSPACE_EXE = WORKSPACE_DIR / "AtivaWorkspace.exe"
WORKSPACE_VERSION_PATH = WORKSPACE_DIR / "version.json"
WORKSPACE_CONFIG_PATH = WORKSPACE_DIR / "config.json"

# Ativa Remote = RustDesk gerenciado pelo Ativa Updater.
RUSTDESK_SERVICE_NAME = "RustDesk"
RUSTDESK_INSTALLED_EXE = PROGRAM_FILES / "RustDesk" / "rustdesk.exe"
RUSTDESK_UNINSTALL_KEY = r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\RustDesk"

# GLPI Agent: instalado pelo MSI oficial. O projeto nunca nomeia o servico
# (so executa o MSI), entao os nomes conhecidos sao sondados em ordem em vez de
# assumir um unico. A versao vem do registro de desinstalacao.
GLPI_AGENT_SERVICE_CANDIDATES = ("glpi-agent", "GLPIAgent", "GLPI-Agent", "glpi-agent-service")
UNINSTALL_ROOT = r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall"
GLPI_AGENT_NAME_RE = re.compile(r"glpi agent(?:\s+v?\d[\w.+-]*)?(?:\s+\(.*\))?")

VERSION_RE = re.compile(r"^[A-Za-z0-9.+_-]{1,64}$")
ANTIVIRUS_CLEAN_RE = re.compile(r"[^\w .,_\-/()+]", re.UNICODE)

# Win32
SC_MANAGER_CONNECT = 0x0001
SERVICE_QUERY_STATUS = 0x0004
SERVICE_STATE_RUNNING = 4
SERVICE_STATE_START_PENDING = 2
ERROR_SERVICE_DOES_NOT_EXIST = 1060
SC_STATUS_PROCESS_INFO = 0
TH32CS_SNAPPROCESS = 0x00000002
NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)


class GuardianError(RuntimeError):
    pass


# ---------------------------------------------------------------------------
# Infra basica
# ---------------------------------------------------------------------------

def load_json(path: Path) -> dict[str, Any]:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, ValueError) as exc:
        raise GuardianError(f"Nao foi possivel ler {path.name}: {exc}") from exc
    if not isinstance(data, dict):
        raise GuardianError(f"{path.name} nao contem um objeto JSON.")
    return data


def atomic_json(path: Path, payload: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    staged = path.with_name(path.name + ".new")
    staged.write_text(json.dumps(payload, indent=2, ensure_ascii=False), encoding="utf-8")
    os.replace(staged, path)


def configure_logging(debug: bool = False) -> logging.Logger:
    """Log rotativo em ProgramData. Nunca recebe token: o ApiClient nao loga corpo."""
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger("ativaguardian")
    logger.setLevel(logging.DEBUG if debug else logging.INFO)
    logger.handlers.clear()
    formatter = logging.Formatter("%(asctime)s %(levelname)s %(message)s")
    file_handler = RotatingFileHandler(
        LOG_DIR / LOG_NAME, maxBytes=2 * 1024 * 1024, backupCount=5, encoding="utf-8"
    )
    file_handler.setFormatter(formatter)
    logger.addHandler(file_handler)
    if debug:
        stream = logging.StreamHandler()
        stream.setFormatter(formatter)
        logger.addHandler(stream)
    return logger


class SingleInstance:
    """Impede duas copias do servico rodando ao mesmo tempo."""

    def __init__(self) -> None:
        self.handle = None

    def __enter__(self) -> "SingleInstance":
        if os.name != "nt":
            return self
        kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
        self.handle = kernel32.CreateMutexW(None, False, MUTEX_NAME)
        if kernel32.GetLastError() == 183:  # ERROR_ALREADY_EXISTS
            raise GuardianError("Outra instancia do Ativa Guardian ja esta em execucao.")
        return self

    def __exit__(self, *_exc: object) -> None:
        if self.handle and os.name == "nt":
            ctypes.WinDLL("kernel32", use_last_error=True).CloseHandle(self.handle)


# ---------------------------------------------------------------------------
# Identidade da maquina
# ---------------------------------------------------------------------------

def machine_identity(logger: logging.Logger) -> str:
    """machine_id persistente. Gerado uma vez e reusado apos reboot/atualizacao.

    O formato respeita a validacao da API ([A-Za-z0-9._-], ate 128).
    """
    try:
        stored = str(load_json(MACHINE_PATH).get("machine_id", "")).strip()
        if re.fullmatch(r"[A-Za-z0-9._-]{1,128}", stored):
            return stored
        logger.warning("machine.json invalido; gerando um novo machine_id.")
    except GuardianError:
        pass  # primeiro boot, ou arquivo corrompido: gera abaixo

    machine_id = uuid.uuid4().hex
    try:
        atomic_json(MACHINE_PATH, {
            "machine_id": machine_id,
            "created_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        })
        logger.info("machine_id gerado e gravado em machine.json.")
    except OSError as exc:
        # Sem persistencia o ID mudaria a cada boot; avisa alto, mas nao derruba.
        logger.error("Nao foi possivel gravar machine.json (%s); o ID nao sera estavel.", exc)
    return machine_id


def hostname() -> str:
    name = (os.environ.get("COMPUTERNAME") or "").strip()
    if not name:
        try:
            import socket

            name = socket.gethostname()
        except OSError:
            name = ""
    name = re.sub(r"[^A-Za-z0-9._-]", "-", name)
    return name[:255]


WTS_CURRENT_SERVER_HANDLE = 0
WTS_USER_NAME = 5
WTS_DOMAIN_NAME = 7
INVALID_SESSION = 0xFFFFFFFF


def logged_on_user() -> str:
    """Usuario da sessao de console (DOMINIO\\usuario), ou vazio se nao houver.

    O Guardian roda como SYSTEM e nao enxerga o usuario por variavel de
    ambiente. As APIs de Terminal Services respondem isso sem precisar de outro
    componente instalado - so leitura, nada e alterado.
    """
    if os.name != "nt":
        return ""

    try:
        kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
        wtsapi32 = ctypes.WinDLL("wtsapi32", use_last_error=True)
        kernel32.WTSGetActiveConsoleSessionId.restype = wintypes.DWORD
        wtsapi32.WTSQuerySessionInformationW.argtypes = [
            wintypes.HANDLE, wintypes.DWORD, ctypes.c_int,
            ctypes.POINTER(ctypes.c_wchar_p), ctypes.POINTER(wintypes.DWORD),
        ]
        wtsapi32.WTSQuerySessionInformationW.restype = wintypes.BOOL
        wtsapi32.WTSFreeMemory.argtypes = [ctypes.c_void_p]

        session = kernel32.WTSGetActiveConsoleSessionId()
        if session == INVALID_SESSION:
            return ""  # nenhuma sessao de console ativa

        def query(info_class: int) -> str:
            buffer = ctypes.c_wchar_p()
            size = wintypes.DWORD()
            if not wtsapi32.WTSQuerySessionInformationW(
                WTS_CURRENT_SERVER_HANDLE, session, info_class,
                ctypes.byref(buffer), ctypes.byref(size)
            ):
                return ""
            try:
                return (buffer.value or "").strip()
            finally:
                wtsapi32.WTSFreeMemory(buffer)

        user = query(WTS_USER_NAME)
        if not user:
            return ""  # sessao existe mas ninguem logado (tela de bloqueio/logon)
        # So o nome da conta, sem o dominio/computador (ex.: "Diego", nao
        # "DESKTOP-R1C8ICN\\Diego").
        return user[:255]
    except Exception:  # noqa: BLE001 - nunca derruba o heartbeat
        return ""


def operating_system() -> str:
    try:
        import platform

        return f"{platform.system()} {platform.release()} {platform.version()}".strip()[:128]
    except Exception:
        return ""


# ---------------------------------------------------------------------------
# Configuracao
# ---------------------------------------------------------------------------

def validate_config(config: dict[str, Any]) -> dict[str, Any]:
    """Exige HTTPS, endpoint do proprio plugin e TLS verificado."""
    api_url = str(config.get("api_url", "")).rstrip("/")
    parsed = urlsplit(api_url)
    if (
        parsed.scheme.lower() != "https"
        or not parsed.hostname
        or parsed.username is not None
        or parsed.password is not None
        or parsed.query
        or parsed.fragment
        or not parsed.path.endswith(API_PATH_SUFFIX)
    ):
        raise GuardianError(f"api_url deve apontar por HTTPS para {API_PATH_SUFFIX}.")

    token = str(config.get("api_token", ""))
    if not re.fullmatch(r"[a-fA-F0-9]{64}", token):
        raise GuardianError("api_token invalido.")

    if config.get("verify_tls", True) is not True:
        raise GuardianError("verify_tls deve permanecer habilitado.")

    interval = config.get("heartbeat_interval_seconds", DEFAULT_INTERVAL_SECONDS)
    try:
        interval = int(interval)
    except (TypeError, ValueError):
        raise GuardianError("heartbeat_interval_seconds invalido.") from None
    if not MIN_INTERVAL_SECONDS <= interval <= MAX_INTERVAL_SECONDS:
        raise GuardianError(
            f"heartbeat_interval_seconds deve ficar entre {MIN_INTERVAL_SECONDS} e {MAX_INTERVAL_SECONDS}."
        )

    validated = {
        "api_url": api_url,
        "api_token": token,
        "verify_tls": True,
        "heartbeat_interval_seconds": interval,
    }
    from maintenance import validate_verifier
    try:
        verifier = validate_verifier(str(config.get("maintenance_password_hash", "")))
    except ValueError as exc:
        raise GuardianError(str(exc)) from exc
    if verifier:
        validated["maintenance_password_hash"] = verifier

    # Coordenadas da API do Ativa Updater, usadas para baixar o pacote no
    # reparo. Ficam AQUI, no config do Guardian, e nao na pasta do Updater:
    # quando ele e apagado (que e exatamente quando o reparo importa), aquela
    # pasta some junto. Opcionais: sem elas ainda existe o fallback de ler o
    # service-config.json do Updater, se ele estiver no lugar.
    updater_url = str(config.get("updater_api_url", "")).rstrip("/")
    updater_token = str(config.get("updater_api_token", ""))
    if updater_url or updater_token:
        parsed_updater = urlsplit(updater_url)
        if (
            parsed_updater.scheme.lower() != "https"
            or not parsed_updater.path.endswith(UPDATER_API_SUFFIX)
            or parsed_updater.query
            or parsed_updater.fragment
        ):
            raise GuardianError(f"updater_api_url deve apontar por HTTPS para {UPDATER_API_SUFFIX}.")
        if not re.fullmatch(r"[a-fA-F0-9]{64}", updater_token):
            raise GuardianError("updater_api_token invalido.")
        validated["updater_api_url"] = updater_url
        validated["updater_api_token"] = updater_token

    return validated


def harden_product_dir(logger: logging.Logger) -> None:
    """config.json guarda o token: so SYSTEM e Administradores podem ler a pasta."""
    if os.name != "nt":
        return
    for arguments in (
        [str(PRODUCT_DIR), "/inheritance:r"],
        [str(PRODUCT_DIR), "/grant:r", "*S-1-5-18:(OI)(CI)F", "*S-1-5-32-544:(OI)(CI)F"],
    ):
        try:
            subprocess.run(
                ["icacls.exe", *arguments],
                capture_output=True, timeout=60, creationflags=NO_WINDOW,
            )
        except (OSError, subprocess.SubprocessError) as exc:
            # Uma ACL que nao pode ser aplicada nao impede o servico de funcionar.
            logger.warning("Nao foi possivel ajustar a ACL de %s: %s", PRODUCT_DIR, exc)


def write_configuration(source: Path, logger: logging.Logger) -> None:
    config = validate_config(load_json(source))
    # An older bundle must not silently remove a configured maintenance password.
    if CONFIG_PATH.exists() and not config.get("maintenance_password_hash"):
        previous = load_json(CONFIG_PATH).get("maintenance_password_hash")
        if previous:
            from maintenance import validate_verifier
            config["maintenance_password_hash"] = validate_verifier(str(previous))
    PRODUCT_DIR.mkdir(parents=True, exist_ok=True)
    harden_product_dir(logger)
    atomic_json(CONFIG_PATH, config)
    logger.info("Configuracao gravada em config.json.")


# ---------------------------------------------------------------------------
# Consultas ao Windows (sem parsear saida localizada de sc.exe/tasklist)
# ---------------------------------------------------------------------------

class SERVICE_STATUS_PROCESS(ctypes.Structure):
    _fields_ = [
        ("dwServiceType", wintypes.DWORD),
        ("dwCurrentState", wintypes.DWORD),
        ("dwControlsAccepted", wintypes.DWORD),
        ("dwWin32ExitCode", wintypes.DWORD),
        ("dwServiceSpecificExitCode", wintypes.DWORD),
        ("dwCheckPoint", wintypes.DWORD),
        ("dwWaitHint", wintypes.DWORD),
        ("dwProcessId", wintypes.DWORD),
        ("dwServiceFlags", wintypes.DWORD),
    ]


def query_service(name: str) -> int | None:
    """Estado do servico, ou None quando ele nao existe.

    Usa a API do Windows em vez de `sc.exe query`: a saida do sc.exe e traduzida
    (em pt-BR imprime "ESTADO"), o que quebraria qualquer parsing de texto.
    """
    if os.name != "nt":
        return None
    advapi32 = ctypes.WinDLL("advapi32", use_last_error=True)
    advapi32.OpenSCManagerW.argtypes = [wintypes.LPCWSTR, wintypes.LPCWSTR, wintypes.DWORD]
    advapi32.OpenSCManagerW.restype = wintypes.HANDLE
    advapi32.OpenServiceW.argtypes = [wintypes.HANDLE, wintypes.LPCWSTR, wintypes.DWORD]
    advapi32.OpenServiceW.restype = wintypes.HANDLE
    advapi32.QueryServiceStatusEx.argtypes = [
        wintypes.HANDLE, ctypes.c_int, ctypes.c_void_p, wintypes.DWORD, ctypes.POINTER(wintypes.DWORD),
    ]
    advapi32.QueryServiceStatusEx.restype = wintypes.BOOL
    advapi32.CloseServiceHandle.argtypes = [wintypes.HANDLE]

    manager = advapi32.OpenSCManagerW(None, None, SC_MANAGER_CONNECT)
    if not manager:
        raise ctypes.WinError(ctypes.get_last_error())
    try:
        service = advapi32.OpenServiceW(manager, name, SERVICE_QUERY_STATUS)
        if not service:
            error = ctypes.get_last_error()
            if error == ERROR_SERVICE_DOES_NOT_EXIST:
                return None
            raise ctypes.WinError(error)
        try:
            status = SERVICE_STATUS_PROCESS()
            needed = wintypes.DWORD()
            if not advapi32.QueryServiceStatusEx(
                service, SC_STATUS_PROCESS_INFO, ctypes.byref(status), ctypes.sizeof(status), ctypes.byref(needed)
            ):
                raise ctypes.WinError(ctypes.get_last_error())
            return int(status.dwCurrentState)
        finally:
            advapi32.CloseServiceHandle(service)
    finally:
        advapi32.CloseServiceHandle(manager)


def first_existing_service(names: tuple[str, ...]) -> tuple[str, int] | None:
    """(nome, estado) do primeiro servico da lista que existir."""
    for name in names:
        try:
            state = query_service(name)
        except OSError:
            continue
        if state is not None:
            return name, state
    return None


class PROCESSENTRY32W(ctypes.Structure):
    _fields_ = [
        ("dwSize", wintypes.DWORD),
        ("cntUsage", wintypes.DWORD),
        ("th32ProcessID", wintypes.DWORD),
        ("th32DefaultHeapID", ctypes.POINTER(ctypes.c_ulong)),
        ("th32ModuleID", wintypes.DWORD),
        ("cntThreads", wintypes.DWORD),
        ("th32ParentProcessID", wintypes.DWORD),
        ("pcPriClassBase", ctypes.c_long),
        ("dwFlags", wintypes.DWORD),
        ("szExeFile", wintypes.WCHAR * 260),
    ]


def is_process_running(image_name: str) -> bool:
    """Ha algum processo com esse nome de imagem? Snapshot via kernel32.

    Evita `tasklist`, que alem de ser outro processo tem saida localizada.
    """
    if os.name != "nt":
        return False
    kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
    kernel32.CreateToolhelp32Snapshot.restype = wintypes.HANDLE
    kernel32.Process32FirstW.argtypes = [wintypes.HANDLE, ctypes.POINTER(PROCESSENTRY32W)]
    kernel32.Process32NextW.argtypes = [wintypes.HANDLE, ctypes.POINTER(PROCESSENTRY32W)]

    snapshot = kernel32.CreateToolhelp32Snapshot(TH32CS_SNAPPROCESS, 0)
    if snapshot == wintypes.HANDLE(-1).value or not snapshot:
        raise ctypes.WinError(ctypes.get_last_error())
    try:
        entry = PROCESSENTRY32W()
        entry.dwSize = ctypes.sizeof(PROCESSENTRY32W)
        target = image_name.lower()
        if not kernel32.Process32FirstW(snapshot, ctypes.byref(entry)):
            return False
        while True:
            if str(entry.szExeFile).lower() == target:
                return True
            if not kernel32.Process32NextW(snapshot, ctypes.byref(entry)):
                return False
    finally:
        kernel32.CloseHandle(snapshot)


# Versoes mudam raramente, mas descobri-las custa caro (o GLPI Agent exige
# varrer toda a arvore de desinstalacao). Como as verificacoes passaram a rodar
# a cada 30 s, o resultado fica em cache; estado de servico/processo continua
# sendo lido fresco a cada vez, que e o que muda de fato.
VERSION_CACHE_SECONDS = 600
_version_cache: dict[str, tuple[float, str]] = {}


def _cached_version(key: str, producer) -> str:
    cached = _version_cache.get(key)
    now = time.time()
    if cached is not None and (now - cached[0]) < VERSION_CACHE_SECONDS:
        return cached[1]
    value = producer()
    _version_cache[key] = (now, value)
    return value


def registry_uninstall_version(subkey: str) -> str:
    """DisplayVersion de uma chave de desinstalacao conhecida."""
    if os.name != "nt":
        return ""
    import winreg

    for view in (winreg.KEY_WOW64_64KEY, winreg.KEY_WOW64_32KEY):
        try:
            with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, subkey, 0, winreg.KEY_READ | view) as key:
                value = str(winreg.QueryValueEx(key, "DisplayVersion")[0]).strip()
                if VERSION_RE.fullmatch(value):
                    return value
        except OSError:
            continue
    return ""


def glpi_agent_version() -> str:
    """Versao do GLPI Agent pelo DisplayName registrado pelo MSI oficial."""
    if os.name != "nt":
        return ""
    import winreg

    for view in (winreg.KEY_WOW64_64KEY, winreg.KEY_WOW64_32KEY):
        try:
            with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, UNINSTALL_ROOT, 0, winreg.KEY_READ | view) as root:
                for index in range(winreg.QueryInfoKey(root)[0]):
                    try:
                        with winreg.OpenKey(root, winreg.EnumKey(root, index)) as entry:
                            name = str(winreg.QueryValueEx(entry, "DisplayName")[0]).strip().lower()
                            if not GLPI_AGENT_NAME_RE.fullmatch(name):
                                continue
                            version = str(winreg.QueryValueEx(entry, "DisplayVersion")[0]).strip()
                            if VERSION_RE.fullmatch(version):
                                return version
                    except OSError:
                        continue
        except OSError:
            continue
    return ""


# ---------------------------------------------------------------------------
# Antivirus (somente deteccao)
# ---------------------------------------------------------------------------

_antivirus_cache: tuple[float, str] = (0.0, "")


def _is_microsoft_defender(name: str) -> bool:
    """Cuidado: "Bitdefender" contem "defender". A ordem do teste importa."""
    lowered = name.strip().lower()
    return "defender" in lowered and "bitdefender" not in lowered


def _normalize_antivirus(name: str) -> str:
    lowered = name.strip().lower()
    if not lowered:
        return ""
    if "bitdefender" in lowered:
        return "Bitdefender"
    if "defender" in lowered:
        return "Microsoft Defender"
    cleaned = ANTIVIRUS_CLEAN_RE.sub(" ", name).strip()
    cleaned = re.sub(r"\s+", " ", cleaned)
    return cleaned[:128]


def detect_antivirus(logger: logging.Logger) -> str:
    """Nome do antivirus ativo. Apenas le; nunca altera configuracao de AV.

    Consulta root\\SecurityCenter2 (presente nas edicoes cliente do Windows) e,
    quando ela nao existe (Windows Server), cai para o servico WinDefend.
    O resultado e cacheado: antivirus nao muda de minuto em minuto, e cada
    consulta custa um processo.
    """
    global _antivirus_cache
    cached_at, cached_value = _antivirus_cache
    if cached_value and (time.time() - cached_at) < ANTIVIRUS_CACHE_SECONDS:
        return cached_value

    detected = ""
    if os.name == "nt":
        try:
            completed = subprocess.run(
                [
                    "powershell.exe", "-NoProfile", "-NonInteractive", "-ExecutionPolicy", "Bypass",
                    "-Command",
                    "(Get-CimInstance -Namespace root/SecurityCenter2 -ClassName AntiVirusProduct"
                    " -ErrorAction Stop | Select-Object -ExpandProperty displayName) -join '|'",
                ],
                capture_output=True, text=True, errors="replace", timeout=60, creationflags=NO_WINDOW,
            )
            if completed.returncode == 0:
                names = [n for n in completed.stdout.strip().split("|") if n.strip()]
                # Um AV de terceiros costuma ser mais relevante que o Defender,
                # que permanece listado mesmo passivo.
                preferred = [n for n in names if not _is_microsoft_defender(n)]
                if preferred:
                    detected = _normalize_antivirus(preferred[0])
                elif names:
                    detected = _normalize_antivirus(names[0])
        except (OSError, subprocess.SubprocessError) as exc:
            logger.debug("SecurityCenter2 indisponivel: %s", exc)

        if not detected:
            try:
                if query_service("WinDefend") is not None:
                    detected = "Microsoft Defender"
            except OSError:
                pass

    if not detected:
        detected = "unknown"
    _antivirus_cache = (time.time(), detected)
    return detected


# ---------------------------------------------------------------------------
# Verificacoes de componente (cada uma isolada: nunca propaga excecao)
# ---------------------------------------------------------------------------

def _component(status: str, version: str = "") -> dict[str, str]:
    if status not in REPORTABLE_STATUSES:
        status = STATUS_UNKNOWN
    version = version if VERSION_RE.fullmatch(version or "") else ""
    return {"status": status, "version": version}


def check_updater() -> dict[str, str]:
    """Ativa Updater: executavel + servico SYSTEM."""
    version = ""
    try:
        heartbeat = load_json(UPDATER_HEARTBEAT_PATH)
        candidate = str(heartbeat.get("version", "")).strip()
        if VERSION_RE.fullmatch(candidate):
            version = candidate
    except GuardianError:
        version = ""

    if not UPDATER_EXE.is_file():
        return _component(STATUS_FILE_MISSING, version)

    state = query_service(UPDATER_SERVICE_NAME)
    if state is None:
        # O executavel existe mas o servico sumiu (o Defender ja removeu ambos
        # em producao); nao e "arquivo ausente" nem "saudavel".
        return _component(STATUS_ERROR, version)
    if state in (SERVICE_STATE_RUNNING, SERVICE_STATE_START_PENDING):
        return _component(STATUS_HEALTHY, version)
    return _component(STATUS_SERVICE_STOPPED, version)


def check_wallpaper() -> dict[str, str]:
    """Ativa Wallpaper: roda por usuario, entao o sinal e o processo, nao um servico."""
    version = ""
    try:
        candidate = str(load_json(WALLPAPER_VERSION_PATH).get("client_version", "")).strip()
        if VERSION_RE.fullmatch(candidate):
            version = candidate
    except GuardianError:
        version = ""

    if not WALLPAPER_EXE.is_file():
        return _component(STATUS_FILE_MISSING, version)
    if is_process_running(WALLPAPER_EXE.name):
        return _component(STATUS_HEALTHY, version)
    # Sem usuario logado nao ha processo, e isso nao e falha. O servidor vera
    # process_stopped; a decisao sobre gravidade fica com quem le o painel.
    return _component(STATUS_PROCESS_STOPPED, version)


def check_workspace() -> dict[str, str]:
    """Ativa Workspace: executavel + servico SYSTEM (mesma regra do Updater)."""
    version = ""
    try:
        candidate = str(load_json(WORKSPACE_VERSION_PATH).get("version", "")).strip()
        if VERSION_RE.fullmatch(candidate):
            version = candidate
    except GuardianError:
        version = ""

    if not WORKSPACE_EXE.is_file():
        # O Workspace e opcional no pacote. Sem a config (gravada pelo
        # instalador; o antivirus nao a apaga) ele nunca foi instalado aqui:
        # "unknown" evita reinstalar o pacote em loop numa maquina sem ele.
        if not WORKSPACE_CONFIG_PATH.is_file():
            return _component(STATUS_UNKNOWN, version)
        return _component(STATUS_FILE_MISSING, version)

    state = query_service(WORKSPACE_SERVICE_NAME)
    if state is None:
        return _component(STATUS_ERROR, version)
    if state in (SERVICE_STATE_RUNNING, SERVICE_STATE_START_PENDING):
        return _component(STATUS_HEALTHY, version)
    return _component(STATUS_SERVICE_STOPPED, version)


def check_remote() -> dict[str, str]:
    """Ativa Remote = RustDesk, instalado e mantido pelo Ativa Updater."""
    version = _cached_version("rustdesk", lambda: registry_uninstall_version(RUSTDESK_UNINSTALL_KEY))

    if not RUSTDESK_INSTALLED_EXE.is_file():
        return _component(STATUS_FILE_MISSING, version)

    state = query_service(RUSTDESK_SERVICE_NAME)
    if state is None:
        return _component(STATUS_ERROR, version)
    if state in (SERVICE_STATE_RUNNING, SERVICE_STATE_START_PENDING):
        return _component(STATUS_HEALTHY, version)
    return _component(STATUS_SERVICE_STOPPED, version)


def check_glpi_agent() -> dict[str, str]:
    """GLPI Agent: instalado pelo MSI oficial, com nome de servico sondado."""
    version = _cached_version("glpi_agent", glpi_agent_version)
    found = first_existing_service(GLPI_AGENT_SERVICE_CANDIDATES)

    if found is None:
        # Sem servico: se nem o registro conhece o produto, ele nao esta instalado.
        return _component(STATUS_FILE_MISSING if not version else STATUS_ERROR, version)

    _name, state = found
    if state in (SERVICE_STATE_RUNNING, SERVICE_STATE_START_PENDING):
        return _component(STATUS_HEALTHY, version)
    return _component(STATUS_SERVICE_STOPPED, version)


COMPONENT_CHECKS = {
    "glpi_agent": check_glpi_agent,
    "wallpaper": check_wallpaper,
    "updater": check_updater,
    "remote": check_remote,
    "workspace": check_workspace,
}


# ---------------------------------------------------------------------------
# Acoes corretivas (nivel 1: somente verificar / iniciar / reiniciar)
# ---------------------------------------------------------------------------

ACTION_CHECK = "CHECK_COMPONENT"
ACTION_START = "START_COMPONENT"
ACTION_RESTART = "RESTART_COMPONENT"
ACTION_REPAIR = "REPAIR_COMPONENT"
ACTION_FIX = "FIX_COMPONENT"
ALLOWED_ACTIONS = frozenset({ACTION_CHECK, ACTION_START, ACTION_RESTART, ACTION_REPAIR, ACTION_FIX})

# O servidor manda apenas um par (componente, acao) de listas fechadas. Este
# mapa - compilado dentro do executavel - e o unico lugar que traduz isso para
# um servico real do Windows. Nome de servico, caminho e comando NUNCA chegam
# pela API: se o servidor mandar algo fora daqui, a acao e recusada.
#
# "wallpaper" nao aparece: ele roda por usuario (HKCU) e nao e servico, entao
# iniciar/reiniciar exigiria lancar processo na sessao do usuario - hoje isso
# pertence ao Ativa Updater. Ele aceita apenas CHECK_COMPONENT.
COMPONENT_SERVICES: dict[str, tuple[str, ...]] = {
    "updater": (UPDATER_SERVICE_NAME,),
    "remote": (RUSTDESK_SERVICE_NAME,),
    "glpi_agent": GLPI_AGENT_SERVICE_CANDIDATES,
    "workspace": (WORKSPACE_SERVICE_NAME,),
}

SERVICE_WAIT_SECONDS = 45
SERVICE_STATE_STOPPED = 1

# --- Reparo ------------------------------------------------------------------
# O pacote oficial vem da API do proprio Ativa Updater no mesmo GLPI: ela ja
# publica versao, SHA-256 e o instalador unificado assinado pelo build. A URL
# NUNCA chega pela acao; e derivada da configuracao local e conferida contra o
# host que o Guardian ja usa.
UPDATER_API_SUFFIX = "/plugins/ativaupdater/api/v1"
UPDATER_CONFIG_PATH = UPDATER_DIR / "service-config.json"
REPAIR_DIR = PRODUCT_DIR / "repair"
REPAIR_STATE_PATH = PRODUCT_DIR / "repair-state.json"
# O instalador unificado cabe folgado aqui; um corpo maior indica outra coisa.
MAX_PACKAGE_BYTES = 400 * 1024 * 1024
REQUIRED_FREE_BYTES = 1024 * 1024 * 1024
DOWNLOAD_TIMEOUT_SECONDS = 600
INSTALL_TIMEOUT_SECONDS = 900
SHA256_RE = re.compile(r"^[a-fA-F0-9]{64}$")

# Quais componentes sabem se reparar. Wallpaper, Remote e GLPI Agent entram
# depois que o Updater estiver validado em campo; ate la a acao e recusada com
# mensagem clara em vez de fingir que funciona.
REPAIRABLE_COMPONENTS = ("updater", "wallpaper", "remote", "glpi_agent", "workspace")


def resolve_component_service(component: str) -> str | None:
    """Nome real do servico deste componente, ou None se nao houver."""
    candidates = COMPONENT_SERVICES.get(component)
    if not candidates:
        return None
    found = first_existing_service(candidates)
    return found[0] if found else None


def wait_for_service_state(name: str, expected: int, timeout: int = SERVICE_WAIT_SECONDS,
                           sleep=time.sleep) -> bool:
    deadline = time.time() + timeout
    while time.time() < deadline:
        try:
            state = query_service(name)
        except OSError:
            state = None
        if state == expected:
            return True
        if expected == SERVICE_STATE_RUNNING and state == SERVICE_STATE_START_PENDING:
            pass  # ainda subindo
        sleep(1)
    try:
        return query_service(name) == expected
    except OSError:
        return False


def updater_api_credentials() -> tuple[str, str]:
    """(api_url, token) da API do Ativa Updater, a partir de config LOCAL.

    Nunca vem da acao. A ordem e: bloco opcional no config.json do Guardian e,
    se ausente, o service-config.json que o proprio Updater ja mantem (a pasta
    dele e restrita a SYSTEM/Administradores, e o Guardian roda como SYSTEM).

    O host precisa ser o mesmo da API do Guardian: assim o pacote so pode vir do
    servidor GLPI que esta maquina ja confia, e nao de outro qualquer.
    """
    guardian = validate_config(load_json(CONFIG_PATH))
    guardian_host = urlsplit(guardian["api_url"]).hostname

    api_url = str(guardian.get("updater_api_url", "")).rstrip("/")
    token = str(guardian.get("updater_api_token", ""))

    if not api_url or not token:
        # Fallback: a config do proprio Updater. So funciona se a pasta dele
        # ainda existir - se ela foi apagada, cai na mensagem abaixo.
        try:
            updater = load_json(UPDATER_CONFIG_PATH)
        except GuardianError as exc:
            raise GuardianError(
                "Nao ha como saber de onde baixar o pacote: o config.json do Guardian nao traz "
                "updater_api_url/updater_api_token e a pasta do Ativa Updater nao existe mais. "
                "Baixe a configuracao do servico novamente no GLPI (Ativa Guardian > Configurar) "
                "e aplique com --configure."
            ) from exc
        api_url = str(updater.get("api_url", "")).rstrip("/")
        token = str(updater.get("api_token", ""))

    parsed = urlsplit(api_url)
    if (
        parsed.scheme.lower() != "https"
        or not parsed.path.endswith(UPDATER_API_SUFFIX)
        or parsed.query
        or parsed.fragment
    ):
        raise GuardianError(f"api_url do Updater invalida (esperado HTTPS .../{UPDATER_API_SUFFIX.lstrip('/')}).")
    if parsed.hostname != guardian_host:
        raise GuardianError("A API do Updater aponta para outro host; pacote recusado.")
    if not re.fullmatch(r"[a-fA-F0-9]{64}", token):
        raise GuardianError("Token da API do Updater invalido.")

    return api_url, token


def download_package(url: str, token: str, expected_sha256: str, destination: Path,
                     expected_size: int = 0) -> None:
    """Baixa e valida o pacote. Qualquer divergencia apaga o arquivo e falha.

    O arquivo so e considerado valido quando o SHA-256 do conteudo gravado bate
    com o publicado pela API: download truncado, proxy que devolve HTML ou
    pacote trocado sao todos rejeitados aqui, antes de qualquer execucao.
    """
    if not SHA256_RE.fullmatch(expected_sha256):
        raise GuardianError("SHA-256 publicado pela API e invalido.")

    free = shutil.disk_usage(destination.parent).free
    if free < max(REQUIRED_FREE_BYTES, expected_size * 2):
        raise GuardianError(f"Espaco em disco insuficiente ({free // (1024 * 1024)} MB livres).")

    digest = hashlib.sha256()
    written = 0
    request = Request(url, headers={
        "Authorization": f"Bearer {token}",
        "Accept": "application/octet-stream",
        "User-Agent": f"AtivaGuardian/{GUARDIAN_VERSION}",
    }, method="GET")

    opener = build_opener(NoRedirect(), HTTPSHandler(context=ssl.create_default_context()))
    try:
        with opener.open(request, timeout=DOWNLOAD_TIMEOUT_SECONDS) as response, \
                destination.open("wb") as handle:
            while True:
                chunk = response.read(1024 * 256)
                if not chunk:
                    break
                written += len(chunk)
                if written > MAX_PACKAGE_BYTES:
                    raise GuardianError("Pacote maior que o limite aceito.")
                digest.update(chunk)
                handle.write(chunk)
    except HTTPError as exc:
        _safe_unlink(destination)
        raise GuardianError(f"Servidor respondeu HTTP {exc.code} ao baixar o pacote.") from exc
    except (URLError, OSError) as exc:
        _safe_unlink(destination)
        raise GuardianError(f"Falha ao baixar o pacote: {exc}") from exc

    if expected_size and written != expected_size:
        _safe_unlink(destination)
        raise GuardianError(f"Download incompleto: {written} de {expected_size} bytes.")
    if digest.hexdigest().lower() != expected_sha256.lower():
        _safe_unlink(destination)
        raise GuardianError("SHA-256 do arquivo baixado nao confere; pacote descartado.")


def _safe_unlink(path: Path) -> None:
    try:
        path.unlink(missing_ok=True)
    except OSError:
        pass


def is_elevated() -> bool:
    """O processo tem privilegio administrativo? (SYSTEM tambem responde True.)"""
    if os.name != "nt":
        return False
    try:
        return bool(ctypes.WinDLL("shell32", use_last_error=True).IsUserAnAdmin())
    except Exception:  # noqa: BLE001
        return False


def installer_command(package: Path, install_log: Path) -> list[str]:
    """Mesma linha silenciosa que o Ativa Updater e o Deploy ja usam."""
    return [
        str(package),
        "/VERYSILENT",
        "/SUPPRESSMSGBOXES",
        "/NORESTART",
        # /CLOSEAPPLICATIONS faria o Restart Manager tentar parar servicos por
        # 90 s e abortar a instalacao inteira.
        "/NOCLOSEAPPLICATIONS",
        "/SP-",
        f"/LOG={install_log}",
    ]


def repair_component(component: str, logger: logging.Logger, action_id: int = 0) -> tuple[bool, str]:
    """Reinstala um componente a partir do pacote oficial publicado no GLPI.

    O pacote e o instalador unificado - o mesmo para todos, porque e ele que
    instala GLPI Agent, Wallpaper, Updater e RustDesk. Nao existe pacote avulso
    por componente: o MSI do Agent, por exemplo, e apagado da maquina depois da
    instalacao, entao reinstalar so e possivel pelo pacote completo.

    Na pratica um reparo restaura todos os quatro; a verificacao final olha o
    componente que foi pedido.

    Preserva configuracao: o instalador reaproveita o registro do Wallpaper,
    regrava o config.json do Guardian e nao toca em machine.json. Nada e
    apagado aqui antes da instalacao.
    """
    # O instalador unificado pede privilegio de administrador no manifesto. Sob
    # o servico (SYSTEM) isso e concedido sem nenhuma janela. Rodando como
    # usuario comum, o Windows abriria o pedido de permissao - o que quebraria a
    # instalacao silenciosa. Entao aqui a gente recusa em vez de disparar o UAC.
    if not is_elevated():
        return False, ("O reparo so roda pelo servico Ativa Guardian (SYSTEM). "
                       "Executado como usuario, o Windows pediria permissao e a "
                       "instalacao deixaria de ser silenciosa.")

    api_url, token = updater_api_credentials()

    client = ApiClient({"api_url": api_url, "api_token": token})
    release = client._json(f"{api_url}/latest", "GET")

    version = str(release.get("version", "")).strip()
    sha256 = str(release.get("sha256", "")).strip()
    file_name = str(release.get("file_name", "")).strip()
    size = int(release.get("size", 0) or 0)
    if not VERSION_RE.fullmatch(version) or not SHA256_RE.fullmatch(sha256):
        raise GuardianError("A API do Updater devolveu versao ou SHA-256 invalido.")
    # O pacote precisa ser o instalador unificado, nao outro arquivo qualquer.
    if not file_name.lower().endswith(".exe"):
        raise GuardianError(f"Pacote publicado nao e um instalador: {file_name}")

    REPAIR_DIR.mkdir(parents=True, exist_ok=True)
    package = REPAIR_DIR / f"Ativa-Unified-Agent-Setup-{version}.exe"
    logger.info("Reparo: baixando o pacote oficial %s", version)
    # A URL de download e montada a partir da api_url local, nunca do payload.
    download_package(f"{api_url}/download/{quote(version, safe='')}", token, sha256, package, size)
    logger.info("Reparo: SHA-256 conferido para o pacote %s", version)

    install_log = LOG_DIR / f"repair-{int(time.time())}.log"
    # O instalador unificado PARA o servico AtivaGuardian para trocar binarios,
    # ou seja, este processo pode morrer no meio. O marcador deixa registrado
    # qual acao estava em curso para reportar o resultado ao voltar.
    if action_id > 0:
        atomic_json(REPAIR_STATE_PATH, {
            "action_id": action_id,
            "component": component,
            "version": version,
            "started_at": time.time(),
        })

    logger.info("Reparo: executando a instalacao silenciosa")
    # Destacado, como o Ativa Updater faz: o instalador para o servico
    # AtivaGuardian para trocar binarios, e um processo filho comum morreria
    # junto, deixando a instalacao pela metade. Destacado ele sobrevive, e o
    # marcador de reparo devolve o resultado quando o servico voltar.
    detached = NO_WINDOW | getattr(subprocess, "DETACHED_PROCESS", 0) \
        | getattr(subprocess, "CREATE_NEW_PROCESS_GROUP", 0)
    try:
        process = subprocess.Popen(
            installer_command(package, install_log),
            close_fds=True,
            creationflags=detached,
        )
    except OSError as exc:
        _safe_unlink(package)
        return False, f"Nao foi possivel executar o instalador: {exc}"

    try:
        exit_code = process.wait(timeout=INSTALL_TIMEOUT_SECONDS)
    except subprocess.TimeoutExpired:
        return False, "O instalador excedeu o tempo limite."
    finally:
        _safe_unlink(package)

    # 0 = ok; 3010/1641 = sucesso pedindo reinicio.
    if exit_code not in (0, 1641, 3010):
        return False, f"O instalador terminou com codigo {exit_code}. Log: {install_log}"

    return verify_repair(component, logger)


def verify_repair(component: str, logger: logging.Logger, sleep=time.sleep,
                  timeout: int = SERVICE_WAIT_SECONDS) -> tuple[bool, str]:
    """Health check apos o reparo: o componente precisa estar realmente de pe."""
    _safe_unlink(REPAIR_STATE_PATH)
    # A reinstalacao troca a versao instalada: o cache precisa sair do caminho,
    # senao o painel mostraria a versao antiga por ate VERSION_CACHE_SECONDS.
    _version_cache.clear()

    check = COMPONENT_CHECKS.get(component)
    if check is None:
        return False, f"Componente desconhecido: {component}"

    service = resolve_component_service(component)
    if service is not None:
        # Da tempo de o SCM subir o servico antes de julgar o resultado.
        wait_for_service_state(service, SERVICE_STATE_RUNNING, timeout=timeout, sleep=sleep)

    status = check()["status"]
    if status == STATUS_HEALTHY:
        logger.info("Reparo concluido: %s healthy", component)
        return True, f"{component} reinstalado e em execucao."

    if status == STATUS_FILE_MISSING:
        # Cenario conhecido: o antivirus remove o executavel de novo. Nao
        # tentamos desativar nem contornar o AV - so reportamos.
        return False, (f"O executavel de {component} sumiu logo apos a instalacao. "
                       "Um antivirus provavelmente o colocou em quarentena.")

    if component == "wallpaper" and status == STATUS_PROCESS_STOPPED:
        # O cliente roda na sessao do usuario: sem ninguem logado nao ha
        # processo, e isso nao e falha do reparo.
        return True, ("Ativa Wallpaper reinstalado. O cliente roda na sessao do "
                      "usuario e sera iniciado no proximo logon.")

    return False, f"Apos o reparo {component} segue com status {status}."


# Todos reparam pelo mesmo pacote unificado (ver repair_component).
REPAIR_HANDLERS = {
    name: (lambda name: lambda logger, action_id=0: repair_component(name, logger, action_id))(name)
    for name in COMPONENT_CHECKS
}


def fix_component(component: str, logger: logging.Logger, action_id: int = 0,
                  sleep=time.sleep, timeout: int = SERVICE_WAIT_SECONDS) -> tuple[bool, str]:
    """Diagnostica na hora e aplica só o que o estado atual exige.

    O painel manda apenas "corrigir"; quem decide o procedimento e a maquina,
    com uma verificacao feita neste instante - nao com o status que o servidor
    tinha guardado, que pode estar velho.

    A escada e deliberadamente conservadora:
      saudavel          -> nao faz nada
      servico parado    -> inicia (os arquivos estao la; reinstalar seria inutil)
      arquivo ausente   -> reinstala o pacote oficial
      servico sumido    -> reinstala (so o instalador registra o servico de novo)
    """
    check = COMPONENT_CHECKS.get(component)
    if check is None:
        return False, f"Componente desconhecido: {component}"

    status = check()["status"]
    logger.info("Corrigir %s: estado atual e %s", component, status)

    if status == STATUS_HEALTHY:
        return True, f"{component} ja esta em execucao; nenhuma correcao foi necessaria."

    if status in (STATUS_SERVICE_STOPPED, STATUS_PROCESS_STOPPED):
        service = resolve_component_service(component)
        if service is None:
            return False, (f"{component} nao roda como servico do Windows nesta maquina; "
                           "correcao automatica indisponivel.")
        logger.info("Corrigir %s: arquivos presentes, apenas iniciando %s", component, service)
        run_sc("start", service)
        if not wait_for_service_state(service, SERVICE_STATE_RUNNING, timeout=timeout, sleep=sleep):
            # Nao escala para reinstalacao: os arquivos estao no lugar, entao o
            # problema e outro e reinstalar so mascararia a causa.
            return False, (f"{service} nao entrou em execucao. Os arquivos estao no lugar, "
                           "entao nenhuma reinstalacao foi feita.")
        final = check()["status"]
        if final == STATUS_HEALTHY:
            return True, f"{service} estava parado e foi iniciado; agora esta saudavel."
        return False, f"{service} iniciou, mas {component} segue com status {final}."

    if status in (STATUS_FILE_MISSING, STATUS_ERROR):
        handler = REPAIR_HANDLERS.get(component)
        if handler is None:
            return False, (f"{component} esta com status {status}, mas ainda nao sabe "
                           "se reinstalar sozinho.")
        motivo = ("o executavel nao esta em disco" if status == STATUS_FILE_MISSING
                  else "o servico nao esta registrado")
        logger.info("Corrigir %s: %s; reinstalando o pacote oficial", component, motivo)
        ok, message = handler(logger, action_id)
        return ok, f"Reinstalacao necessaria ({motivo}). {message}"

    return False, (f"{component} esta com status {status}; nao ha correcao automatica "
                   "definida para esse caso.")


def execute_action(component: str, action: str, logger: logging.Logger,
                   sleep=time.sleep, timeout: int = SERVICE_WAIT_SECONDS,
                   action_id: int = 0) -> tuple[bool, str]:
    """Executa uma acao permitida. Retorna (sucesso, mensagem).

    Nunca levanta: qualquer falha vira (False, motivo) para o GLPI registrar.
    """
    if action not in ALLOWED_ACTIONS:
        return False, f"Acao nao permitida: {action}"
    if component not in COMPONENT_CHECKS:
        return False, f"Componente desconhecido: {component}"

    try:
        if action == ACTION_CHECK:
            result = COMPONENT_CHECKS[component]()
            return True, f"{component}: {result['status']}"

        if action == ACTION_FIX:
            return fix_component(component, logger, action_id, sleep, timeout)

        if action == ACTION_REPAIR:
            handler = REPAIR_HANDLERS.get(component)
            if handler is None:
                return False, (f"O reparo de '{component}' ainda nao foi implementado; "
                               "por enquanto apenas o Ativa Updater se repara sozinho.")
            return handler(logger, action_id)

        service = resolve_component_service(component)
        if service is None:
            return False, f"{component} nao e um servico do Windows nesta maquina."

        if action == ACTION_START:
            state = query_service(service)
            if state in (SERVICE_STATE_RUNNING, SERVICE_STATE_START_PENDING):
                return True, f"{service} ja estava em execucao."
            logger.info("Acao: iniciando %s", service)
            run_sc("start", service)

        elif action == ACTION_RESTART:
            logger.info("Acao: reiniciando %s", service)
            run_sc("stop", service)
            # Parada controlada: so inicia depois que o SCM confirmar STOPPED.
            if not wait_for_service_state(service, SERVICE_STATE_STOPPED, timeout=timeout, sleep=sleep):
                return False, f"{service} nao parou dentro do tempo previsto."
            run_sc("start", service)

        if wait_for_service_state(service, SERVICE_STATE_RUNNING, timeout=timeout, sleep=sleep):
            return True, f"{service} em execucao."
        return False, f"{service} nao ficou em execucao apos a acao."

    except Exception as exc:  # noqa: BLE001 - uma acao nunca derruba o servico
        logger.warning("Falha ao executar %s em %s: %s", action, component, exc)
        return False, str(exc)[:400]


def collect_components(logger: logging.Logger, quiet: bool = False) -> dict[str, dict[str, str]]:
    """Roda todas as verificacoes. Uma que falhe vira 'unknown' e nao afeta as outras.

    quiet=True nos ticks de 30 s: sem isso o log encheria de linhas repetidas.
    O resultado e registrado normalmente quando o heartbeat sai.
    """
    components: dict[str, dict[str, str]] = {}
    for name, check in COMPONENT_CHECKS.items():
        try:
            components[name] = check()
        except Exception as exc:  # noqa: BLE001 - isolamento e o objetivo
            logger.warning("Falha ao verificar %s: %s", name, exc)
            components[name] = _component(STATUS_UNKNOWN)
        if not quiet:
            logger.info("%s: %s%s", name, components[name]["status"],
                        f" ({components[name]['version']})" if components[name]["version"] else "")
    return components


def build_heartbeat(machine_id: str, components: dict[str, dict[str, str]], antivirus: str) -> dict[str, Any]:
    return {
        "machine_id": machine_id,
        "hostname": hostname(),
        # Quem esta usando a maquina. O Updater mostra isso cruzando com a
        # tabela do Wallpaper; aqui o proprio Guardian descobre pela sessao de
        # console, entao nao depende de outro componente estar instalado.
        "username": logged_on_user(),
        "guardian_version": GUARDIAN_VERSION,
        "antivirus": antivirus,
        "components": components,
    }


# ---------------------------------------------------------------------------
# Cliente HTTP
# ---------------------------------------------------------------------------

class NoRedirect(HTTPRedirectHandler):
    """Recusa redirecionamento: seguir um levaria o cabecalho Authorization
    (e portanto o token) para um host que nao foi o configurado."""

    def redirect_request(self, req, fp, code, msg, headers, newurl):
        raise GuardianError(f"Redirecionamento recusado (HTTP {code}).")


def is_transient(exc: BaseException) -> bool:
    if isinstance(exc, HTTPError):
        return exc.code in (408, 429, 500, 502, 503, 504)
    return isinstance(exc, (URLError, OSError))


class ApiClient:
    """Fala com a API do plugin. Nunca loga o token nem o coloca na URL."""

    def __init__(self, config: dict[str, Any]) -> None:
        self.base_url = str(config["api_url"]).rstrip("/")
        self._token = str(config["api_token"])
        self.opener = build_opener(NoRedirect(), HTTPSHandler(context=ssl.create_default_context()))

    def send_heartbeat(self, payload: dict[str, Any], sleep=time.sleep) -> None:
        body = json.dumps(payload).encode("utf-8")
        headers = {
            # O token vive apenas no cabecalho: nunca em query string, nunca em log.
            "Authorization": f"Bearer {self._token}",
            "Content-Type": "application/json",
            "Accept": "application/json",
            "User-Agent": f"AtivaGuardian/{GUARDIAN_VERSION}",
        }
        last_error: BaseException | None = None
        for attempt in range(len(API_RETRY_DELAYS_SECONDS) + 1):
            request = Request(self.base_url + "/heartbeat", data=body, headers=headers, method="POST")
            try:
                with self.opener.open(request, timeout=API_TIMEOUT_SECONDS) as response:
                    if response.status not in (200, 202):
                        raise GuardianError(f"API respondeu HTTP {response.status}.")
                    return
            except HTTPError as exc:
                # O corpo pode conter a mensagem de validacao, util e sem segredo.
                detail = exc.read(512).decode("utf-8", errors="replace")
                last_error = GuardianError(f"HTTP {exc.code}: {detail}")
                if not is_transient(exc) or attempt == len(API_RETRY_DELAYS_SECONDS):
                    raise last_error from exc
            except (URLError, OSError) as exc:
                last_error = exc
                if attempt == len(API_RETRY_DELAYS_SECONDS):
                    raise GuardianError(f"Falha de rede: {exc}") from exc
            sleep(API_RETRY_DELAYS_SECONDS[attempt])
        if last_error is not None:
            raise GuardianError(str(last_error))

    def _json(self, url: str, method: str, body: dict[str, Any] | None = None) -> dict[str, Any]:
        data = json.dumps(body).encode("utf-8") if body is not None else None
        headers = {
            "Authorization": f"Bearer {self._token}",
            "Accept": "application/json",
            "User-Agent": f"AtivaGuardian/{GUARDIAN_VERSION}",
        }
        if data is not None:
            headers["Content-Type"] = "application/json"
        request = Request(url, data=data, headers=headers, method=method)
        try:
            with self.opener.open(request, timeout=API_TIMEOUT_SECONDS) as response:
                raw = response.read(MAX_RESPONSE_BYTES)
                return json.loads(raw.decode("utf-8")) if raw else {}
        except HTTPError as exc:
            detail = exc.read(512).decode("utf-8", errors="replace")
            raise GuardianError(f"HTTP {exc.code}: {detail}") from exc
        except (URLError, OSError, ValueError) as exc:
            raise GuardianError(f"Falha de rede: {exc}") from exc

    def fetch_actions(self, machine_id: str) -> list[dict[str, Any]]:
        """Acoes que o servidor ja reservou para esta maquina."""
        payload = self._json(f"{self.base_url}/actions/{quote(machine_id, safe='')}", "GET")
        actions = payload.get("actions")
        return actions if isinstance(actions, list) else []

    def report_action(self, action_id: int, machine_id: str, success: bool, message: str) -> None:
        self._json(
            f"{self.base_url}/actions/{int(action_id)}/result",
            "POST",
            {
                "machine_id": machine_id,
                "status": "success" if success else "failed",
                "message": message[:500],
            },
        )


# ---------------------------------------------------------------------------
# Ciclo do servico
# ---------------------------------------------------------------------------

class GuardianRuntime:
    def __init__(self) -> None:
        self.stop_event = threading.Event()
        # Assinatura do ultimo status que o servidor confirmou ter recebido.
        # Fica aqui (e nao no laco) porque run_actions tambem envia heartbeat.
        self.last_signature: str | None = None
        # Ultima tentativa de auto-reparo por componente, para nao repetir num
        # loop apertado (ex.: reinstalacao que o antivirus desfaz em seguida).
        self._last_autofix: dict[str, float] = {}

    def run_cycle(self, logger: logging.Logger, machine_id: str,
                  components: dict[str, dict[str, str]] | None = None) -> None:
        """Uma verificacao + um envio. Qualquer falha e registrada e engolida."""
        if components is None:
            logger.info("Checking components")
            components = collect_components(logger)
        antivirus = "unknown"
        try:
            antivirus = detect_antivirus(logger)
        except Exception as exc:  # noqa: BLE001
            logger.warning("Nao foi possivel detectar o antivirus: %s", exc)
        logger.info("Antivirus: %s", antivirus)

        try:
            config = validate_config(load_json(CONFIG_PATH))
        except GuardianError as exc:
            logger.error("Configuracao invalida; heartbeat nao enviado: %s", exc)
            return

        payload = build_heartbeat(machine_id, components, antivirus)
        try:
            ApiClient(config).send_heartbeat(payload)
            logger.info("Heartbeat sent")
            # So marca como reportado quando o envio deu certo. Se a API estava
            # fora, a mudanca continua pendente e o proximo tick tenta de novo -
            # antes isso era registrado como enviado e o status novo se perdia
            # ate o ciclo seguinte.
            self.last_signature = json.dumps(components, sort_keys=True)
        except GuardianError as exc:
            logger.warning("Heartbeat failed: %s", exc)
        except Exception as exc:  # noqa: BLE001 - a API nunca derruba o servico
            logger.warning("Heartbeat failed (inesperado): %s", exc)

    def interval(self, logger: logging.Logger) -> int:
        try:
            return int(validate_config(load_json(CONFIG_PATH))["heartbeat_interval_seconds"])
        except GuardianError:
            return DEFAULT_INTERVAL_SECONDS
        except Exception:  # noqa: BLE001
            logger.debug("Intervalo padrao aplicado.")
            return DEFAULT_INTERVAL_SECONDS

    def run_actions(self, logger: logging.Logger, machine_id: str) -> bool:
        """Busca e executa as acoes que o GLPI reservou para esta maquina.

        O servidor ja marcou cada acao como running ao entrega-la, entao uma
        acao nunca chega duas vezes. Falhas de rede sao registradas e a proxima
        coleta tenta de novo; nada aqui derruba o servico.

        Retorna True quando executou pelo menos uma acao, para o laco principal
        saber que o status ja foi reenviado.
        """
        try:
            config = validate_config(load_json(CONFIG_PATH))
        except GuardianError:
            return False  # sem configuracao valida nao ha o que consultar

        try:
            api = ApiClient(config)
            actions = api.fetch_actions(machine_id)
        except GuardianError as exc:
            logger.debug("Nao foi possivel consultar acoes: %s", exc)
            return False

        executed = False
        for item in actions:
            if not isinstance(item, dict):
                continue
            try:
                action_id = int(item.get("id", 0))
            except (TypeError, ValueError):
                continue
            component = str(item.get("component", "")).strip().lower()
            action = str(item.get("action", "")).strip().upper()
            if action_id <= 0:
                continue

            logger.info("Acao %s recebida: %s em %s", action_id, action, component)
            success, message = execute_action(component, action, logger, action_id=action_id)
            logger.info("Acao %s: %s (%s)", action_id, "success" if success else "failed", message)

            # Heartbeat ANTES de devolver o resultado: o painel abre o modal e o
            # fecha assim que a acao termina, entao o status novo precisa ja
            # estar no servidor nesse instante - senao a tela mostraria
            # "concluido" com o estado antigo ate a proxima coleta.
            try:
                self.run_cycle(logger, machine_id)
            except Exception:  # noqa: BLE001 - nao impede reportar o resultado
                logger.exception("Falha ao atualizar o status apos a acao.")

            try:
                api.report_action(action_id, machine_id, success, message)
            except GuardianError as exc:
                # O servidor expira sozinho o que ficar preso em running.
                logger.warning("Nao foi possivel devolver o resultado da acao %s: %s", action_id, exc)
            executed = True

        return executed

    def finish_pending_repair(self, logger: logging.Logger, machine_id: str) -> None:
        """Fecha um reparo que o proprio instalador interrompeu.

        O instalador unificado para o servico AtivaGuardian para trocar
        binarios, entao o processo que pediu o reparo morre antes de reportar.
        Ao voltar, o marcador diz qual acao ficou em aberto: roda-se o health
        check e o resultado real e devolvido ao GLPI.
        """
        if not REPAIR_STATE_PATH.is_file():
            return
        try:
            state = load_json(REPAIR_STATE_PATH)
        except GuardianError:
            _safe_unlink(REPAIR_STATE_PATH)
            return

        action_id = int(state.get("action_id", 0) or 0)
        component = str(state.get("component", "updater")).strip().lower()
        logger.info("Retomando o reparo interrompido de %s (acao %s)", component, action_id)
        success, message = verify_repair(component, logger)
        logger.info("Reparo %s: %s (%s)", action_id, "success" if success else "failed", message)

        if action_id <= 0:
            return
        try:
            config = validate_config(load_json(CONFIG_PATH))
            ApiClient(config).report_action(action_id, machine_id, success, message)
        except GuardianError as exc:
            # O servidor expira sozinho o que ficar preso em running.
            logger.warning("Nao foi possivel reportar o reparo %s: %s", action_id, exc)

    # Quanto tempo esperar antes de tentar de novo o auto-reparo do mesmo
    # componente. Reparar (reinstalar) e caro; iniciar servico e barato, mas a
    # janela evita marteladas se o problema persistir.
    AUTOFIX_MIN_INTERVAL = 600

    def auto_repair(self, logger: logging.Logger, components: dict[str, dict[str, str]]) -> None:
        """Corrige sozinho o que estiver quebrado, sem esperar clique no painel.

        Mesma escada do botao "Corrigir" (fix_component): so age no que da para
        agir, e nunca reinstala se os arquivos estao no lugar. Rate-limit por
        componente para nao entrar em loop com um antivirus que reapaga o exe.
        """
        now = time.monotonic()
        for name, data in components.items():
            status = data.get("status")
            if status in (STATUS_HEALTHY, STATUS_UNKNOWN):
                continue
            if now - self._last_autofix.get(name, 0.0) < self.AUTOFIX_MIN_INTERVAL:
                continue
            self._last_autofix[name] = now
            logger.info("Auto-reparo: %s esta %s; corrigindo.", name, status)
            try:
                ok, message = fix_component(name, logger)
                logger.info("Auto-reparo de %s: %s (%s)", name, "ok" if ok else "falhou", message)
            except Exception as exc:  # noqa: BLE001 - nunca derruba o loop
                logger.warning("Auto-reparo de %s falhou: %s", name, exc)

    def run(self, logger: logging.Logger) -> None:
        logger.info("Guardian started (versao %s)", GUARDIAN_VERSION)
        machine_id = machine_identity(logger)
        try:
            self.finish_pending_repair(logger, machine_id)
        except Exception:  # noqa: BLE001 - nunca impede o servico de subir
            logger.exception("Falha ao retomar o reparo pendente.")
        next_heartbeat = 0.0

        # As verificacoes sao locais e baratas, entao rodam a cada
        # ACTION_POLL_SECONDS. O heartbeat sai no intervalo configurado OU assim
        # que algum status muda - sem isso, parar um servico so aparecia no
        # painel depois de ate 5 minutos, ainda como "Saudavel".
        while not self.stop_event.is_set():
            components: dict[str, dict[str, str]] | None = None
            try:
                components = collect_components(logger, quiet=True)
            except Exception:  # noqa: BLE001
                logger.exception("Falha inesperada ao verificar componentes.")

            # Corrige sozinho o que estiver quebrado, sem depender de clique.
            if components is not None:
                try:
                    self.auto_repair(logger, components)
                except Exception:  # noqa: BLE001
                    logger.exception("Falha inesperada no auto-reparo.")

            signature = json.dumps(components, sort_keys=True) if components is not None else None
            changed = (
                signature is not None
                and self.last_signature is not None
                and signature != self.last_signature
            )
            if changed:
                logger.info("Mudanca de status detectada; enviando heartbeat imediato.")

            if time.monotonic() >= next_heartbeat or changed:
                try:
                    # run_cycle grava self.last_signature quando o envio funciona.
                    self.run_cycle(logger, machine_id, components)
                except Exception:  # noqa: BLE001 - nenhum ciclo pode matar o loop
                    logger.exception("Falha inesperada no ciclo de verificacao.")
                next_heartbeat = time.monotonic() + self.interval(logger)

            try:
                if self.run_actions(logger, machine_id):
                    # run_actions ja reenviou o status (e atualizou
                    # last_signature); so adia o proximo envio agendado.
                    next_heartbeat = time.monotonic() + self.interval(logger)
            except Exception:  # noqa: BLE001
                logger.exception("Falha inesperada ao processar acoes.")

            self.stop_event.wait(ACTION_POLL_SECONDS)
        logger.info("Guardian stopped")


# ---------------------------------------------------------------------------
# Instalacao do servico
# ---------------------------------------------------------------------------

def run_sc(*arguments: str) -> int:
    try:
        return subprocess.run(
            ["sc.exe", *arguments], capture_output=True, timeout=60, creationflags=NO_WINDOW
        ).returncode
    except (OSError, subprocess.SubprocessError):
        return -1


def install_service(logger: logging.Logger) -> int:
    if os.name != "nt":
        raise GuardianError("O modo servico esta disponivel somente no Windows.")
    executable = Path(sys.executable) if getattr(sys, "frozen", False) else SERVICE_EXE
    if not executable.is_file():
        raise GuardianError(f"Executavel nao encontrado: {executable}")

    PRODUCT_DIR.mkdir(parents=True, exist_ok=True)
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    harden_product_dir(logger)

    if query_service(SERVICE_NAME) is not None:
        run_sc("stop", SERVICE_NAME)
        run_sc("config", SERVICE_NAME, "binPath=", f'"{executable}" --service',
               "start=", "auto", "DisplayName=", SERVICE_DISPLAY_NAME)
    else:
        run_sc("create", SERVICE_NAME, "binPath=", f'"{executable}" --service',
               "start=", "auto", "DisplayName=", SERVICE_DISPLAY_NAME)
    run_sc("description", SERVICE_NAME, SERVICE_DESCRIPTION)
    # Se o processo morrer, o proprio Windows o levanta de novo.
    run_sc("failure", SERVICE_NAME, "reset=", "86400",
           "actions=", "restart/60000/restart/60000/restart/60000")
    run_sc("start", SERVICE_NAME)
    logger.info("Servico %s registrado e iniciado.", SERVICE_NAME)
    return 0


def uninstall_service(logger: logging.Logger) -> int:
    if os.name != "nt":
        raise GuardianError("O modo servico esta disponivel somente no Windows.")
    if query_service(SERVICE_NAME) is None:
        logger.info("Servico %s nao esta instalado.", SERVICE_NAME)
        return 0
    run_sc("stop", SERVICE_NAME)
    run_sc("delete", SERVICE_NAME)
    logger.info("Servico %s removido. Dados em %s foram preservados.", SERVICE_NAME, PRODUCT_DIR)
    return 0


# ---------------------------------------------------------------------------
# Service Control Manager
# ---------------------------------------------------------------------------

SERVICE_WIN32_OWN_PROCESS = 0x10
SERVICE_START_PENDING = 2
SERVICE_RUNNING = 4
SERVICE_STOP_PENDING = 3
SERVICE_STOPPED = 1
SERVICE_ACCEPT_STOP = 0x1
SERVICE_ACCEPT_SHUTDOWN = 0x4
SERVICE_CONTROL_STOP = 1
SERVICE_CONTROL_SHUTDOWN = 5
NO_ERROR = 0


class SERVICE_STATUS(ctypes.Structure):
    _fields_ = [
        ("dwServiceType", wintypes.DWORD),
        ("dwCurrentState", wintypes.DWORD),
        ("dwControlsAccepted", wintypes.DWORD),
        ("dwWin32ExitCode", wintypes.DWORD),
        ("dwServiceSpecificExitCode", wintypes.DWORD),
        ("dwCheckPoint", wintypes.DWORD),
        ("dwWaitHint", wintypes.DWORD),
    ]


if os.name == "nt":
    HANDLER_EX = ctypes.WINFUNCTYPE(wintypes.DWORD, wintypes.DWORD, wintypes.DWORD, wintypes.LPVOID, wintypes.LPVOID)
    SERVICE_MAIN = ctypes.WINFUNCTYPE(None, wintypes.DWORD, ctypes.POINTER(wintypes.LPWSTR))

    class SERVICE_TABLE_ENTRY(ctypes.Structure):
        _fields_ = [("lpServiceName", wintypes.LPWSTR), ("lpServiceProc", SERVICE_MAIN)]

    _advapi32 = ctypes.WinDLL("advapi32", use_last_error=True)
    _advapi32.RegisterServiceCtrlHandlerExW.restype = wintypes.HANDLE
    _advapi32.SetServiceStatus.argtypes = [wintypes.HANDLE, ctypes.POINTER(SERVICE_STATUS)]
    _advapi32.StartServiceCtrlDispatcherW.argtypes = [ctypes.POINTER(SERVICE_TABLE_ENTRY)]
    _service_handle = None
    _runtime: GuardianRuntime | None = None

    def set_service_status(state: int, accepted: int = 0, wait_hint: int = 0) -> None:
        status = SERVICE_STATUS(SERVICE_WIN32_OWN_PROCESS, state, accepted, 0, 0, 0, wait_hint)
        if _service_handle and not _advapi32.SetServiceStatus(_service_handle, ctypes.byref(status)):
            raise ctypes.WinError(ctypes.get_last_error())

    @HANDLER_EX
    def service_handler(control, _event_type, _event_data, _context):
        if control in (SERVICE_CONTROL_STOP, SERVICE_CONTROL_SHUTDOWN):
            set_service_status(SERVICE_STOP_PENDING, wait_hint=15000)
            if _runtime is not None:
                _runtime.stop_event.set()
        return NO_ERROR

    @SERVICE_MAIN
    def service_main(_argc, _argv):
        global _service_handle, _runtime
        _service_handle = _advapi32.RegisterServiceCtrlHandlerExW(SERVICE_NAME, service_handler, None)
        if not _service_handle:
            return
        set_service_status(SERVICE_START_PENDING, wait_hint=15000)
        logger = configure_logging(False)
        _runtime = GuardianRuntime()
        set_service_status(SERVICE_RUNNING, SERVICE_ACCEPT_STOP | SERVICE_ACCEPT_SHUTDOWN)
        try:
            with SingleInstance():
                _runtime.run(logger)
        except Exception:
            logger.exception("Falha nao tratada no servico.")
        finally:
            set_service_status(SERVICE_STOPPED)


def run_service_dispatcher() -> int:
    if os.name != "nt":
        raise GuardianError("O modo servico esta disponivel somente no Windows.")
    table = (SERVICE_TABLE_ENTRY * 2)()
    table[0].lpServiceName = SERVICE_NAME
    table[0].lpServiceProc = service_main
    table[1].lpServiceName = None
    table[1].lpServiceProc = SERVICE_MAIN()
    if not _advapi32.StartServiceCtrlDispatcherW(table):
        raise ctypes.WinError(ctypes.get_last_error())
    return 0


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def main() -> int:
    parser = argparse.ArgumentParser(description="Servico de monitoramento Ativa Guardian")
    parser.add_argument("--service", action="store_true", help="Executa pelo Windows Service Control Manager")
    parser.add_argument("--run-once", action="store_true", help="Uma verificacao e um heartbeat")
    parser.add_argument("--check", action="store_true", help="So mostra o diagnostico, sem enviar")
    parser.add_argument("--configure", metavar="ARQUIVO", help="Grava config.json a partir de um JSON")
    parser.add_argument("--install-service", action="store_true", help="Registra o servico no Windows")
    parser.add_argument("--uninstall-service", action="store_true", help="Remove o servico do Windows")
    parser.add_argument("--maintenance", action="store_true", help="Solicita a senha Ativa para manutencao local")
    parser.add_argument("--authorize-install", action="store_true", help="Autoriza atualizacao do pacote")
    parser.add_argument("--enforce-protection", action="store_true", help="Restaura a protecao apos manutencao")
    parser.add_argument("--setup-protection", action="store_true", help="Configura protecao e tarefa de recuperacao")
    parser.add_argument("--debug", action="store_true", help="Tambem escreve o log no console")
    parser.add_argument("--version", action="store_true", help="Imprime a versao e sai")
    arguments = parser.parse_args()

    if arguments.version:
        print(GUARDIAN_VERSION)
        return 0

    if arguments.service:
        return run_service_dispatcher()

    # The unelevated shortcut cannot open protected logs/config before UAC.
    if arguments.maintenance:
        import maintenance
        try:
            return maintenance.launch_panel()
        except Exception as exc:
            maintenance.show_message(str(exc), error=True)
            return 1

    logger = configure_logging(arguments.debug or arguments.check or arguments.run_once)

    try:
        if arguments.authorize_install or arguments.enforce_protection or arguments.setup_protection:
            import maintenance
            if arguments.authorize_install:
                maintenance.authorize(installation=True)
            elif arguments.setup_protection:
                maintenance.install_recovery_task()
                maintenance.enforce(close=True)
            else:
                maintenance.enforce()
            logger.info("Operacao de protecao/manutencao concluida.")
            return 0
        if arguments.configure:
            write_configuration(Path(arguments.configure), logger)
            return 0
        if arguments.install_service:
            return install_service(logger)
        if arguments.uninstall_service:
            return uninstall_service(logger)
        if arguments.check:
            components = collect_components(logger)
            payload = build_heartbeat(machine_identity(logger), components, detect_antivirus(logger))
            payload["operating_system"] = operating_system()
            print(json.dumps(payload, indent=2, ensure_ascii=False))
            return 0
        if arguments.run_once:
            with SingleInstance():
                GuardianRuntime().run_cycle(logger, machine_identity(logger))
            return 0
    except (GuardianError, ValueError, OSError, RuntimeError) as exc:
        logger.error("%s", exc)
        print(f"Erro: {exc}", file=sys.stderr)
        return 1

    parser.error("selecione --service, --run-once, --check, --configure, --install-service ou --uninstall-service")
    return 2


if __name__ == "__main__":
    sys.exit(main())
