"""Ativa Guardian - servico Windows de monitoramento de saude.

Verifica os componentes Ativa instalados na maquina e envia um heartbeat para a
API do plugin Ativa Guardian no GLPI. Esta etapa e SOMENTE de monitoramento: o
servico nao repara, nao reinstala, nao reinicia componentes, nao baixa nada e
nao altera antivirus. Ele apenas observa e reporta.

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
import json
import logging
import os
import re
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
from urllib.parse import urlsplit
from urllib.request import HTTPRedirectHandler, HTTPSHandler, Request, build_opener

GUARDIAN_VERSION = "1.0.0"

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

DEFAULT_INTERVAL_SECONDS = 300
MIN_INTERVAL_SECONDS = 60
MAX_INTERVAL_SECONDS = 86400
API_TIMEOUT_SECONDS = 30
# Backoff between heartbeat attempts. The loop never gives up for good: after the
# last delay it simply waits for the next cycle.
API_RETRY_DELAYS_SECONDS = (5, 15, 45)
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

    return {
        "api_url": api_url,
        "api_token": token,
        "verify_tls": True,
        "heartbeat_interval_seconds": interval,
    }


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


def check_remote() -> dict[str, str]:
    """Ativa Remote = RustDesk, instalado e mantido pelo Ativa Updater."""
    version = registry_uninstall_version(RUSTDESK_UNINSTALL_KEY)

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
    version = glpi_agent_version()
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
}


def collect_components(logger: logging.Logger) -> dict[str, dict[str, str]]:
    """Roda todas as verificacoes. Uma que falhe vira 'unknown' e nao afeta as outras."""
    components: dict[str, dict[str, str]] = {}
    for name, check in COMPONENT_CHECKS.items():
        try:
            components[name] = check()
        except Exception as exc:  # noqa: BLE001 - isolamento e o objetivo
            logger.warning("Falha ao verificar %s: %s", name, exc)
            components[name] = _component(STATUS_UNKNOWN)
        logger.info("%s: %s%s", name, components[name]["status"],
                    f" ({components[name]['version']})" if components[name]["version"] else "")
    return components


def build_heartbeat(machine_id: str, components: dict[str, dict[str, str]], antivirus: str) -> dict[str, Any]:
    return {
        "machine_id": machine_id,
        "hostname": hostname(),
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


# ---------------------------------------------------------------------------
# Ciclo do servico
# ---------------------------------------------------------------------------

class GuardianRuntime:
    def __init__(self) -> None:
        self.stop_event = threading.Event()

    def run_cycle(self, logger: logging.Logger, machine_id: str) -> None:
        """Uma verificacao + um envio. Qualquer falha e registrada e engolida."""
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

    def run(self, logger: logging.Logger) -> None:
        logger.info("Guardian started (versao %s)", GUARDIAN_VERSION)
        machine_id = machine_identity(logger)
        while not self.stop_event.is_set():
            try:
                self.run_cycle(logger, machine_id)
            except Exception:  # noqa: BLE001 - nenhum ciclo pode matar o loop
                logger.exception("Falha inesperada no ciclo de verificacao.")
            self.stop_event.wait(self.interval(logger))
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
    parser.add_argument("--debug", action="store_true", help="Tambem escreve o log no console")
    parser.add_argument("--version", action="store_true", help="Imprime a versao e sai")
    arguments = parser.parse_args()

    if arguments.version:
        print(GUARDIAN_VERSION)
        return 0

    if arguments.service:
        return run_service_dispatcher()

    logger = configure_logging(arguments.debug or arguments.check or arguments.run_once)

    try:
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
    except GuardianError as exc:
        logger.error("%s", exc)
        print(f"Erro: {exc}", file=sys.stderr)
        return 1

    parser.error("selecione --service, --run-once, --check, --configure, --install-service ou --uninstall-service")
    return 2


if __name__ == "__main__":
    sys.exit(main())
