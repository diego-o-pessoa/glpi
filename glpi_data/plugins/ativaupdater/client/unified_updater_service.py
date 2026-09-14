from __future__ import annotations

import argparse
import csv
import ctypes
from ctypes import wintypes
import hashlib
import io
import json
import logging
from logging.handlers import RotatingFileHandler
import os
from pathlib import Path
import re
import socket
import ssl
import subprocess
import sys
import threading
import time
from typing import Any, Callable
from urllib.error import HTTPError, URLError
from urllib.parse import quote, urlsplit
from urllib.request import Request, build_opener, HTTPRedirectHandler, HTTPSHandler


SERVICE_NAME = "AtivaUnifiedUpdater"
SERVICE_DISPLAY_NAME = "Ativa Unified Updater"
UPDATER_VERSION = "1.4.0"
DEFAULT_INTERVAL = 3600
COMMAND_POLL_SECONDS = 15
MAX_INSTALLER_BYTES = 2 * 1024 * 1024 * 1024
VERSION_RE = re.compile(r"^\d{1,5}\.\d{1,5}\.\d{1,5}$")

# Mirrors GlpiPlugin\Ativaupdater\ReleasePolicy. Packages older than this
# re-register the Wallpaper Client with a possibly rotated secret and cannot be
# installed over a newer package safely.
ROLLBACK_MIN_VERSION = (1, 6, 0)
ACTION_UPGRADE = "upgrade"
ACTION_DOWNGRADE = "downgrade"
ACTION_CURRENT = "current"
ACTION_BLOCKED_DOWNGRADE = "blocked_downgrade"

# The service supervises the installer process. A run that exits with an error,
# exceeds the timeout or disappears without configuring the service is a failed
# attempt. After the first failure the same package is retried
# MAX_INSTALL_RETRIES more times; when all of them fail the dashboard shows
# "Falha na Instalacao" with the collected log, and one automatic attempt per
# day follows (Verificar agora starts a new cycle immediately).
INSTALL_TIMEOUT_SECONDS = 1200
MAX_INSTALL_RETRIES = 3
TOTAL_INSTALL_ATTEMPTS = MAX_INSTALL_RETRIES + 1
RETRY_DELAYS_SECONDS = (60, 300, 900)
FAILED_RETRY_SECONDS = 86400
INSTALL_LOG_MAX_CHARS = 24000
SETUP_IMAGE_PREFIX = "ativa-unified-agent-setup"
STATUS_RETRYING = "retrying"
STATUS_INSTALL_FAILED = "install_failed"
NOT_SESSION_ZERO_EXIT_CODE = 3

# Inno Setup exit codes (https://jrsoftware.org/ishelp/topic_setupexitcodes.htm).
SETUP_EXIT_CODES = {
    1: "o instalador nao conseguiu inicializar",
    2: "instalacao cancelada antes de iniciar",
    3: "erro fatal ao preparar a instalacao",
    4: "erro fatal durante a instalacao",
    5: "instalacao cancelada ou abortada durante a execucao",
    6: "instalador encerrado a forca",
    7: "a etapa de preparacao impediu a instalacao",
    8: "a etapa de preparacao exige reiniciar o Windows",
}

PROGRAM_DATA = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData"))
PRODUCT_DIR = PROGRAM_DATA / "AtivaLocacao" / "UnifiedUpdater"
CONFIG_PATH = PRODUCT_DIR / "service-config.json"
STATE_PATH = PRODUCT_DIR / "state.json"
DOWNLOAD_DIR = PRODUCT_DIR / "downloads"
LOG_DIR = PRODUCT_DIR / "logs"
WALLPAPER_VERSION_PATH = PROGRAM_DATA / "AtivaLocacao" / "Wallpaper" / "version.json"
WALLPAPER_CLIENT_PATH = PROGRAM_DATA / "AtivaLocacao" / "Wallpaper" / "AtivaWallpaperClient.exe"
WALLPAPER_LOG_DIR = PROGRAM_DATA / "AtivaLocacao" / "Wallpaper" / "logs"
MSI_LOG_PATH = LOG_DIR / "glpi-agent-msi.log"
INSTALL_FAILURE_LOG_PATH = LOG_DIR / "install-failure.log"
MUTEX_NAME = r"Global\AtivaUnifiedUpdater"


class UpdaterError(RuntimeError):
    pass


class NoReleaseError(UpdaterError):
    pass


class SingleInstance:
    def __init__(self) -> None:
        self.handle = None

    def __enter__(self):
        if os.name != "nt":
            return self
        kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
        kernel32.CreateMutexW.restype = wintypes.HANDLE
        self.handle = kernel32.CreateMutexW(None, False, MUTEX_NAME)
        if not self.handle:
            raise ctypes.WinError(ctypes.get_last_error())
        if ctypes.get_last_error() == 183:
            kernel32.CloseHandle(self.handle)
            self.handle = None
            raise UpdaterError("Outra instancia do atualizador ja esta em execucao.")
        return self

    def __exit__(self, *_args) -> None:
        if self.handle and os.name == "nt":
            ctypes.WinDLL("kernel32", use_last_error=True).CloseHandle(self.handle)
            self.handle = None


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):  # noqa: ANN001
        raise UpdaterError(f"Redirecionamento HTTP inesperado ({code}).")


def configure_logging(debug: bool = False) -> logging.Logger:
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger("AtivaUnifiedUpdater")
    for handler in logger.handlers[:]:
        handler.close()
        logger.removeHandler(handler)
    logger.setLevel(logging.DEBUG if debug else logging.INFO)
    file_handler = RotatingFileHandler(
        LOG_DIR / "service.log", maxBytes=5 * 1024 * 1024, backupCount=5, encoding="utf-8"
    )
    file_handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s %(message)s"))
    logger.addHandler(file_handler)
    if debug:
        console = logging.StreamHandler()
        console.setFormatter(file_handler.formatter)
        logger.addHandler(console)
    return logger


def load_json(path: Path) -> dict[str, Any]:
    if not path.is_file() or path.stat().st_size > 1024 * 1024:
        raise UpdaterError(f"Arquivo de configuracao ausente ou invalido: {path}")
    try:
        value = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        raise UpdaterError(f"JSON invalido em {path.name}: {exc}") from exc
    if not isinstance(value, dict):
        raise UpdaterError(f"O arquivo {path.name} deve conter um objeto JSON.")
    return value


def atomic_json(path: Path, value: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    temporary = path.with_suffix(path.suffix + ".tmp")
    temporary.write_text(json.dumps(value, indent=2, ensure_ascii=False), encoding="utf-8")
    os.replace(temporary, path)


def version_tuple(value: str) -> tuple[int, int, int]:
    if not VERSION_RE.fullmatch(value):
        raise UpdaterError(f"Versao invalida: {value}")
    return tuple(int(part) for part in value.split("."))  # type: ignore[return-value]


def decide_action(installed: str, available: str, allow_downgrade: bool) -> str:
    target = version_tuple(available)
    try:
        current = version_tuple(installed)
    except UpdaterError:
        return ACTION_UPGRADE
    if current < target:
        return ACTION_UPGRADE
    if current == target:
        return ACTION_CURRENT
    if allow_downgrade and target >= ROLLBACK_MIN_VERSION:
        return ACTION_DOWNGRADE
    return ACTION_BLOCKED_DOWNGRADE


def clear_install_failures(state: dict[str, Any]) -> None:
    for key in ("failed_version", "failed_sha256", "failed_attempts", "retry_after"):
        state.pop(key, None)


def clear_pending_install(state: dict[str, Any]) -> None:
    for key in ("pending_version", "pending_sha256", "pending_action", "pending_started_at"):
        state.pop(key, None)


def attempt_number(state: dict[str, Any], version: str, sha256: str) -> int:
    """Number of the next installation attempt of this package (1 = first try)."""
    if state.get("failed_version") == version and state.get("failed_sha256") == sha256:
        return int(state.get("failed_attempts", 0) or 0) + 1
    return 1


def register_install_failure(state: dict[str, Any], now: float) -> tuple[int, int, bool]:
    """Turn the pending installation into a failed attempt.

    Returns (failed_attempts, retry_delay_seconds, exhausted); exhausted means
    the first attempt and all MAX_INSTALL_RETRIES retries failed.
    """
    version = str(state.get("pending_version", ""))
    sha256 = str(state.get("pending_sha256", ""))
    attempts = attempt_number(state, version, sha256)
    clear_pending_install(state)
    exhausted = attempts >= TOTAL_INSTALL_ATTEMPTS
    delay = (
        FAILED_RETRY_SECONDS
        if exhausted
        else RETRY_DELAYS_SECONDS[min(attempts, len(RETRY_DELAYS_SECONDS)) - 1]
    )
    state.update({
        "failed_version": version,
        "failed_sha256": sha256,
        "failed_attempts": attempts,
        "retry_after": now + delay,
    })
    return attempts, delay, exhausted


def install_block_reason(state: dict[str, Any], release: dict[str, Any], now: float) -> tuple[str, str] | None:
    """(status, message) while this release must wait before the next attempt."""
    if state.get("failed_version") != release["version"] or state.get("failed_sha256") != release["sha256"]:
        return None
    attempts = int(state.get("failed_attempts", 0) or 0)
    remaining = int(float(state.get("retry_after", 0) or 0) - now)
    if remaining <= 0:
        return None
    if attempts >= TOTAL_INSTALL_ATTEMPTS:
        return (
            STATUS_INSTALL_FAILED,
            f"Falha na instalacao de {release['version']} apos {attempts} tentativas. "
            f"Nova tentativa automatica em {max(1, remaining // 3600)} h; "
            "use Verificar agora para tentar imediatamente.",
        )
    return (
        STATUS_RETRYING,
        f"Falha na tentativa {attempts} de {TOTAL_INSTALL_ATTEMPTS} ao instalar {release['version']}; "
        f"nova tentativa ({attempts} de {MAX_INSTALL_RETRIES}) em {remaining} segundos.",
    )


def next_check_delay(result: int, state: dict[str, Any], now: float) -> int:
    """Seconds until the next full check, never missing a scheduled retry."""
    delay = configured_interval(state) if result == 0 else 300
    try:
        retry_after = float(state.get("retry_after", 0) or 0)
    except (TypeError, ValueError):
        retry_after = 0.0
    if retry_after > now:
        delay = min(delay, max(30, int(retry_after - now) + 1))
    return delay


def describe_outcome(outcome: str, exit_code: int | None) -> str:
    if outcome == "timeout":
        return f"o instalador excedeu {INSTALL_TIMEOUT_SECONDS // 60} minutos e foi finalizado"
    if outcome == "missing":
        return "o instalador foi encerrado sem concluir a configuracao do servico"
    if outcome == "incomplete":
        return "o instalador terminou sem registrar a nova versao"
    detail = SETUP_EXIT_CODES.get(int(exit_code or 0), "erro no instalador")
    return f"codigo de saida {exit_code} ({detail})"


def decode_log(raw: bytes, utf16: bool) -> str:
    if utf16:
        return raw.decode("utf-16-le", errors="replace").lstrip("\ufeff")
    try:
        return raw.decode("utf-8").lstrip("\ufeff")
    except UnicodeDecodeError:
        return raw.decode("cp1252", errors="replace")


def read_log_tail(path: Path, max_lines: int, max_bytes: int = 262144) -> list[str]:
    try:
        size = path.stat().st_size
        with path.open("rb") as stream:
            head = stream.read(2)
            # msiexec writes UTF-16 logs; keep the offset on a character boundary.
            utf16 = head == b"\xff\xfe"
            start = max(len(head) if utf16 else 0, size - max_bytes)
            if utf16 and start % 2:
                start += 1
            stream.seek(start)
            raw = stream.read()
    except OSError:
        return []
    lines = [line.rstrip() for line in decode_log(raw, utf16).splitlines() if line.strip()]
    return lines[-max_lines:]


def newest_file(directory: Path, pattern: str, not_before: float = 0.0) -> Path | None:
    try:
        candidates = [path for path in directory.glob(pattern) if path.stat().st_mtime >= not_before]
    except OSError:
        return None
    return max(candidates, key=lambda path: path.stat().st_mtime, default=None)


def collect_install_log(
    version: str,
    attempt: int,
    outcome: str,
    exit_code: int | None,
    started_at: float,
    installer_log: Path | None = None,
) -> str:
    """Build the diagnostic log sent to the dashboard for a failed attempt."""
    not_before = started_at - 60 if started_at else 0.0
    lines = [
        f"Ativa Unified Updater {UPDATER_VERSION} - falha na instalacao",
        f"Data: {time.strftime('%Y-%m-%d %H:%M:%S')}",
        f"Computador: {socket.gethostname()}",
        f"Versao: {version} | tentativa {attempt} de {TOTAL_INSTALL_ATTEMPTS}",
        f"Resultado: {describe_outcome(outcome, exit_code)}",
    ]

    inno_log = installer_log if installer_log is not None and installer_log.is_file() else newest_file(
        LOG_DIR, "installer-*.log", not_before
    )
    lines.append("")
    lines.append(f"== Instalador (Inno Setup): {inno_log.name if inno_log else 'log nao encontrado'} ==")
    if inno_log:
        lines.extend(read_log_tail(inno_log, 150))

    try:
        msi_log_recent = MSI_LOG_PATH.is_file() and MSI_LOG_PATH.stat().st_mtime >= not_before
    except OSError:
        msi_log_recent = False
    if msi_log_recent:
        msi_lines = read_log_tail(MSI_LOG_PATH, 4000)
        errors = [
            line for line in msi_lines
            if "return value 3" in line.lower() or re.search(r"\b(error|erro)\b", line, re.IGNORECASE)
        ]
        lines.append("")
        lines.append("== GLPI Agent (msiexec): linhas de erro ==")
        lines.extend(errors[-20:] or ["(nenhuma linha de erro encontrada)"])
        lines.append("== GLPI Agent (msiexec): final do log ==")
        lines.extend(msi_lines[-30:])

    wallpaper_log = newest_file(WALLPAPER_LOG_DIR, "client-*.log", not_before)
    if wallpaper_log:
        lines.append("")
        lines.append(f"== Wallpaper Client: {wallpaper_log.name} ==")
        lines.extend(read_log_tail(wallpaper_log, 40))

    text = "\n".join(lines)
    if len(text) > INSTALL_LOG_MAX_CHARS:
        header = "\n".join(lines[:5])
        text = header + "\n...\n" + text[-(INSTALL_LOG_MAX_CHARS - len(header) - 5):]
    return text


def parse_setup_processes(tasklist_csv: str) -> list[int]:
    """PIDs of the unified installer (setup EXE and Inno Setup's .tmp stub)."""
    pids = []
    for row in csv.reader(io.StringIO(tasklist_csv)):
        if len(row) >= 2 and row[0].strip().lower().startswith(SETUP_IMAGE_PREFIX):
            try:
                pids.append(int(row[1]))
            except ValueError:
                continue
    return pids


def running_setup_processes() -> list[int]:
    if os.name != "nt":
        return []
    try:
        completed = subprocess.run(
            ["tasklist.exe", "/FO", "CSV", "/NH"],
            capture_output=True, text=True, errors="replace", timeout=60,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
    except (OSError, subprocess.SubprocessError):
        return []
    return parse_setup_processes(completed.stdout)


def kill_process_tree(pid: int) -> None:
    try:
        subprocess.run(
            ["taskkill.exe", "/PID", str(pid), "/T", "/F"],
            capture_output=True, timeout=60,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
    except (OSError, subprocess.SubprocessError):
        pass


def supervise_installer(
    process: Any,
    stop_requested: Callable[[], bool],
    timeout_seconds: float = INSTALL_TIMEOUT_SECONDS,
    poll_seconds: float = 2.0,
    clock: Callable[[], float] = time.monotonic,
) -> tuple[str, int | None]:
    """Wait for the installer: ('exited', code), ('stopping', None) or ('timeout', None).

    'stopping' means the installer is restarting this service to switch to the
    new executable; the installer keeps running and must not be killed.
    """
    deadline = clock() + timeout_seconds
    while True:
        code = process.poll()
        if code is not None:
            return "exited", int(code)
        if stop_requested():
            return "stopping", None
        if clock() >= deadline:
            return "timeout", None
        try:
            process.wait(timeout=poll_seconds)
        except subprocess.TimeoutExpired:
            pass


def cleanup_replaced_binaries() -> None:
    """Delete service executables renamed by a supervised installation."""
    try:
        candidates = list(PRODUCT_DIR.glob("AtivaUnifiedUpdater.exe.old*"))
    except OSError:
        return
    for path in candidates:
        try:
            path.unlink()
        except OSError:
            continue


def cleanup_downloads(keep: Path | None = None) -> None:
    """Remove installers from previous versions so ProgramData does not grow forever."""
    try:
        candidates = list(DOWNLOAD_DIR.glob("Ativa-Unified-Agent-Setup-*"))
    except OSError:
        return
    for path in candidates:
        if keep is not None and path.name.lower() == keep.name.lower():
            continue
        try:
            path.unlink()
        except OSError:
            # An installer that is still running stays locked; retry next time.
            continue


def is_glpi_agent_display_name(name: str) -> bool:
    # The official MSI registers "GLPI Agent <version>", e.g. "GLPI Agent 1.19".
    # Do not match other products such as "GLPI Agent Monitor".
    return re.fullmatch(r"glpi agent(?:\s+v?\d[\w.+-]*)?(?:\s+\(.*\))?", name.strip().lower()) is not None


# WTS_CONNECTSTATE_CLASS values that belong to a logged-on user.
WTS_ACTIVE = 0
WTS_DISCONNECTED = 4


def select_user_sessions(sessions: list[tuple[int, int]]) -> list[int]:
    """Session ids (from (id, state) pairs) where a user may be logged on."""
    return [
        session_id
        for session_id, state in sessions
        if session_id != 0 and state in (WTS_ACTIVE, WTS_DISCONNECTED)
    ]


def current_session_id() -> int | None:
    if os.name != "nt":
        return None
    kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
    kernel32.GetCurrentProcessId.restype = wintypes.DWORD
    kernel32.ProcessIdToSessionId.argtypes = [wintypes.DWORD, ctypes.POINTER(wintypes.DWORD)]
    kernel32.ProcessIdToSessionId.restype = wintypes.BOOL
    session = wintypes.DWORD()
    if not kernel32.ProcessIdToSessionId(kernel32.GetCurrentProcessId(), ctypes.byref(session)):
        return None
    return int(session.value)


class WTS_SESSION_INFOW(ctypes.Structure):
    _fields_ = [
        ("SessionId", wintypes.DWORD),
        ("pWinStationName", wintypes.LPWSTR),
        ("State", ctypes.c_int),
    ]


class STARTUPINFOW(ctypes.Structure):
    _fields_ = [
        ("cb", wintypes.DWORD),
        ("lpReserved", wintypes.LPWSTR),
        ("lpDesktop", wintypes.LPWSTR),
        ("lpTitle", wintypes.LPWSTR),
        ("dwX", wintypes.DWORD),
        ("dwY", wintypes.DWORD),
        ("dwXSize", wintypes.DWORD),
        ("dwYSize", wintypes.DWORD),
        ("dwXCountChars", wintypes.DWORD),
        ("dwYCountChars", wintypes.DWORD),
        ("dwFillAttribute", wintypes.DWORD),
        ("dwFlags", wintypes.DWORD),
        ("wShowWindow", wintypes.WORD),
        ("cbReserved2", wintypes.WORD),
        ("lpReserved2", ctypes.POINTER(ctypes.c_byte)),
        ("hStdInput", wintypes.HANDLE),
        ("hStdOutput", wintypes.HANDLE),
        ("hStdError", wintypes.HANDLE),
    ]


class PROCESS_INFORMATION(ctypes.Structure):
    _fields_ = [
        ("hProcess", wintypes.HANDLE),
        ("hThread", wintypes.HANDLE),
        ("dwProcessId", wintypes.DWORD),
        ("dwThreadId", wintypes.DWORD),
    ]


def enumerate_sessions() -> list[tuple[int, int]]:
    wtsapi32 = ctypes.WinDLL("wtsapi32", use_last_error=True)
    wtsapi32.WTSEnumerateSessionsW.argtypes = [
        wintypes.HANDLE, wintypes.DWORD, wintypes.DWORD,
        ctypes.POINTER(ctypes.POINTER(WTS_SESSION_INFOW)), ctypes.POINTER(wintypes.DWORD),
    ]
    wtsapi32.WTSEnumerateSessionsW.restype = wintypes.BOOL
    wtsapi32.WTSFreeMemory.argtypes = [wintypes.LPVOID]
    info = ctypes.POINTER(WTS_SESSION_INFOW)()
    count = wintypes.DWORD()
    if not wtsapi32.WTSEnumerateSessionsW(None, 0, 1, ctypes.byref(info), ctypes.byref(count)):
        raise ctypes.WinError(ctypes.get_last_error())
    try:
        return [(int(info[index].SessionId), int(info[index].State)) for index in range(count.value)]
    finally:
        wtsapi32.WTSFreeMemory(info)


def launch_in_session(session_id: int, executable: Path) -> None:
    """Start executable as the user logged on to session_id. Requires LocalSystem."""
    maximum_allowed = 0x02000000
    security_impersonation = 2
    token_primary = 1
    create_unicode_environment = 0x00000400
    wtsapi32 = ctypes.WinDLL("wtsapi32", use_last_error=True)
    advapi32 = ctypes.WinDLL("advapi32", use_last_error=True)
    userenv = ctypes.WinDLL("userenv", use_last_error=True)
    kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
    wtsapi32.WTSQueryUserToken.argtypes = [wintypes.ULONG, ctypes.POINTER(wintypes.HANDLE)]
    wtsapi32.WTSQueryUserToken.restype = wintypes.BOOL
    advapi32.DuplicateTokenEx.argtypes = [
        wintypes.HANDLE, wintypes.DWORD, wintypes.LPVOID, ctypes.c_int, ctypes.c_int, ctypes.POINTER(wintypes.HANDLE),
    ]
    advapi32.DuplicateTokenEx.restype = wintypes.BOOL
    advapi32.CreateProcessAsUserW.argtypes = [
        wintypes.HANDLE, wintypes.LPCWSTR, wintypes.LPWSTR, wintypes.LPVOID, wintypes.LPVOID, wintypes.BOOL,
        wintypes.DWORD, wintypes.LPVOID, wintypes.LPCWSTR, ctypes.POINTER(STARTUPINFOW),
        ctypes.POINTER(PROCESS_INFORMATION),
    ]
    advapi32.CreateProcessAsUserW.restype = wintypes.BOOL
    userenv.CreateEnvironmentBlock.argtypes = [ctypes.POINTER(wintypes.LPVOID), wintypes.HANDLE, wintypes.BOOL]
    userenv.CreateEnvironmentBlock.restype = wintypes.BOOL
    userenv.DestroyEnvironmentBlock.argtypes = [wintypes.LPVOID]
    kernel32.CloseHandle.argtypes = [wintypes.HANDLE]

    user_token = wintypes.HANDLE()
    primary_token = wintypes.HANDLE()
    environment = wintypes.LPVOID()
    try:
        if not wtsapi32.WTSQueryUserToken(session_id, ctypes.byref(user_token)):
            raise ctypes.WinError(ctypes.get_last_error())
        if not advapi32.DuplicateTokenEx(
            user_token, maximum_allowed, None, security_impersonation, token_primary, ctypes.byref(primary_token)
        ):
            raise ctypes.WinError(ctypes.get_last_error())
        if not userenv.CreateEnvironmentBlock(ctypes.byref(environment), primary_token, False):
            raise ctypes.WinError(ctypes.get_last_error())
        desktop = ctypes.create_unicode_buffer("winsta0\\default")
        command_line = ctypes.create_unicode_buffer(f'"{executable}"')
        startup = STARTUPINFOW()
        startup.cb = ctypes.sizeof(STARTUPINFOW)
        startup.lpDesktop = ctypes.cast(desktop, wintypes.LPWSTR)
        process = PROCESS_INFORMATION()
        if not advapi32.CreateProcessAsUserW(
            primary_token, str(executable), command_line, None, None, False,
            create_unicode_environment, environment, str(executable.parent),
            ctypes.byref(startup), ctypes.byref(process),
        ):
            raise ctypes.WinError(ctypes.get_last_error())
        kernel32.CloseHandle(process.hThread)
        kernel32.CloseHandle(process.hProcess)
    finally:
        if environment:
            userenv.DestroyEnvironmentBlock(environment)
        for handle in (primary_token, user_token):
            if handle:
                kernel32.CloseHandle(handle)


def start_wallpaper_clients(logger: logging.Logger) -> int:
    """Start the Wallpaper Client for every logged-on user after an installation.

    The unified installer runs as SYSTEM when launched by this service or by
    GLPI Inventory. Inno Setup's ExecAsOriginalUser would then start the client
    as SYSTEM in session 0, where it has no visible desktop, while the users'
    clients stay stopped until their next logon. Returns
    NOT_SESSION_ZERO_EXIT_CODE outside session 0 so the installer can fall back
    to starting the client for the user who ran it.
    """
    if current_session_id() != 0:
        return NOT_SESSION_ZERO_EXIT_CODE
    if not WALLPAPER_CLIENT_PATH.is_file():
        logger.warning("Cliente de wallpaper nao encontrado em %s.", WALLPAPER_CLIENT_PATH)
        return 0
    started = 0
    for session_id in select_user_sessions(enumerate_sessions()):
        try:
            launch_in_session(session_id, WALLPAPER_CLIENT_PATH)
            started += 1
        except OSError as exc:
            logger.warning("Nao foi possivel iniciar o cliente de wallpaper na sessao %s: %s", session_id, exc)
    logger.info("Cliente de wallpaper iniciado em %d sessao(oes) de usuario.", started)
    return 0


def validate_config(config: dict[str, Any]) -> dict[str, Any]:
    api_url = str(config.get("api_url", "")).rstrip("/")
    parsed = urlsplit(api_url)
    if (
        parsed.scheme.lower() != "https"
        or not parsed.hostname
        or parsed.username is not None
        or parsed.password is not None
        or parsed.query
        or parsed.fragment
        or not parsed.path.endswith("/plugins/ativaupdater/api/v1")
    ):
        raise UpdaterError("api_url deve apontar por HTTPS para /plugins/ativaupdater/api/v1.")
    token = str(config.get("api_token", ""))
    if not re.fullmatch(r"[a-fA-F0-9]{64}", token):
        raise UpdaterError("api_token invalido.")
    if config.get("verify_tls") is not True:
        raise UpdaterError("verify_tls deve permanecer habilitado.")
    interval = int(config.get("check_interval_seconds", DEFAULT_INTERVAL))
    if interval < 300 or interval > 86400:
        raise UpdaterError("check_interval_seconds deve estar entre 300 e 86400.")
    return {
        "api_url": api_url,
        "api_token": token,
        "verify_tls": True,
        "check_interval_seconds": interval,
    }


def configured_interval(state: dict[str, Any]) -> int:
    try:
        interval = int(state.get("check_interval_seconds", DEFAULT_INTERVAL))
    except (TypeError, ValueError):
        interval = DEFAULT_INTERVAL
    return max(300, min(86400, interval))


def get_state() -> dict[str, Any]:
    if not STATE_PATH.is_file():
        return {"installed_version": "0.0.0"}
    try:
        return load_json(STATE_PATH)
    except UpdaterError:
        return {"installed_version": "0.0.0"}


def machine_guid() -> str:
    if os.name != "nt":
        return hashlib.sha256(socket.gethostname().encode("utf-8")).hexdigest()[:32]
    import winreg

    with winreg.OpenKey(
        winreg.HKEY_LOCAL_MACHINE,
        r"SOFTWARE\Microsoft\Cryptography",
        0,
        winreg.KEY_READ | winreg.KEY_WOW64_64KEY,
    ) as key:
        return str(winreg.QueryValueEx(key, "MachineGuid")[0]).strip().lower()


def wallpaper_client_version() -> str:
    try:
        version = str(load_json(WALLPAPER_VERSION_PATH).get("client_version", ""))
        return version if VERSION_RE.fullmatch(version) else ""
    except (OSError, UpdaterError):
        return ""


def glpi_agent_version() -> str:
    if os.name != "nt":
        return ""
    import winreg

    uninstall = r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall"
    for registry_view in (winreg.KEY_WOW64_64KEY, winreg.KEY_WOW64_32KEY):
        try:
            with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, uninstall, 0, winreg.KEY_READ | registry_view) as root:
                for index in range(winreg.QueryInfoKey(root)[0]):
                    try:
                        with winreg.OpenKey(root, winreg.EnumKey(root, index)) as entry:
                            name = str(winreg.QueryValueEx(entry, "DisplayName")[0])
                            if not is_glpi_agent_display_name(name):
                                continue
                            version = str(winreg.QueryValueEx(entry, "DisplayVersion")[0]).strip()
                            if re.fullmatch(r"\d{1,5}\.\d{1,5}(?:\.\d{1,5})?", version):
                                return version
                    except OSError:
                        continue
        except OSError:
            continue
    return ""


class ApiClient:
    def __init__(self, config: dict[str, Any]):
        self.base_url = str(config["api_url"])
        self.token = str(config["api_token"])
        self.origin = self._origin(self.base_url)
        self.opener = build_opener(NoRedirect(), HTTPSHandler(context=ssl.create_default_context()))

    @staticmethod
    def _origin(url: str) -> tuple[str, str, int]:
        parsed = urlsplit(url)
        port = parsed.port or (443 if parsed.scheme == "https" else 80)
        return parsed.scheme.lower(), (parsed.hostname or "").lower(), port

    def _request(self, url: str, *, method: str = "GET", data: bytes | None = None, timeout: int = 60):
        if self._origin(url) != self.origin:
            raise UpdaterError("A API retornou um endereco de download fora da origem permitida.")
        headers = {
            "Authorization": f"Bearer {self.token}",
            "Accept": "application/json",
            "User-Agent": f"AtivaUnifiedUpdater/{UPDATER_VERSION}",
        }
        if data is not None:
            headers["Content-Type"] = "application/json"
        request = Request(url, data=data, headers=headers, method=method)
        try:
            return self.opener.open(request, timeout=timeout)
        except HTTPError as exc:
            body = exc.read(4096).decode("utf-8", errors="replace")
            if exc.code == 404 and '"NO_RELEASE"' in body:
                raise NoReleaseError("Nenhuma versao foi publicada.") from exc
            raise UpdaterError(f"API respondeu HTTP {exc.code}: {body}") from exc
        except (URLError, OSError) as exc:
            raise UpdaterError(f"Falha de comunicacao com a API: {exc}") from exc

    def latest(self) -> dict[str, Any]:
        with self._request(self.base_url + "/latest") as response:
            if "application/json" not in response.headers.get("Content-Type", ""):
                raise UpdaterError("A API respondeu latest com tipo de conteudo invalido.")
            payload = json.loads(response.read(65537).decode("utf-8"))
        if not isinstance(payload, dict):
            raise UpdaterError("Resposta latest invalida.")
        version = str(payload.get("version", ""))
        sha256 = str(payload.get("sha256", "")).lower()
        size = int(payload.get("size", 0))
        download_url = str(payload.get("download_url", ""))
        if (
            not VERSION_RE.fullmatch(version)
            or not re.fullmatch(r"[a-f0-9]{64}", sha256)
            or size < 1024
            or size > MAX_INSTALLER_BYTES
            or self._origin(download_url) != self.origin
        ):
            raise UpdaterError("Metadados da versao publicada sao invalidos.")
        payload["version"] = version
        payload["sha256"] = sha256
        payload["size"] = size
        payload["download_url"] = download_url
        payload["allow_downgrade"] = payload.get("allow_downgrade") is True
        return payload

    def report(
        self, status: str, installed: str, available: str = "", message: str = "", install_log: str | None = None
    ) -> None:
        payload = {
            "machine_guid": machine_guid(),
            "hostname": socket.gethostname(),
            "updater_version": UPDATER_VERSION,
            "installed_version": installed,
            "available_version": available,
            "wallpaper_client_version": wallpaper_client_version(),
            "glpi_agent_version": glpi_agent_version(),
            "status": status,
            "message": message[:1000],
        }
        if install_log is not None:
            payload["install_log"] = install_log[:INSTALL_LOG_MAX_CHARS]
        data = json.dumps(payload, separators=(",", ":"), ensure_ascii=False).encode("utf-8")
        with self._request(self.base_url + "/status", method="POST", data=data, timeout=30) as response:
            response.read(4096)

    def command_pending(self) -> bool:
        url = self.base_url + "/commands/" + quote(machine_guid(), safe="")
        with self._request(url, timeout=30) as response:
            if "application/json" not in response.headers.get("Content-Type", ""):
                raise UpdaterError("A API respondeu commands com tipo de conteudo invalido.")
            payload = json.loads(response.read(65537).decode("utf-8"))
        if not isinstance(payload, dict) or not isinstance(payload.get("check_now"), bool):
            raise UpdaterError("Resposta de comandos invalida.")
        return bool(payload["check_now"])

    def download(self, release: dict[str, Any], destination: Path) -> None:
        temporary = destination.with_suffix(".part")
        temporary.unlink(missing_ok=True)
        digest = hashlib.sha256()
        received = 0
        try:
            with self._request(str(release["download_url"]), timeout=300) as response, temporary.open("wb") as stream:
                content_type = response.headers.get("Content-Type", "").split(";", 1)[0].strip().lower()
                if content_type != "application/octet-stream":
                    raise UpdaterError(f"Tipo de conteudo inesperado: {content_type}")
                while True:
                    block = response.read(1024 * 1024)
                    if not block:
                        break
                    received += len(block)
                    if received > int(release["size"]) or received > MAX_INSTALLER_BYTES:
                        raise UpdaterError("O download excedeu o tamanho publicado.")
                    digest.update(block)
                    stream.write(block)
                stream.flush()
                os.fsync(stream.fileno())
            if received != int(release["size"]) or digest.hexdigest() != str(release["sha256"]):
                raise UpdaterError("O tamanho ou SHA-256 do instalador nao confere.")
            with temporary.open("rb") as stream:
                if stream.read(2) != b"MZ":
                    raise UpdaterError("O arquivo baixado nao e um executavel Windows.")
            os.replace(temporary, destination)
        finally:
            temporary.unlink(missing_ok=True)


def launch_installer(path: Path, logger: logging.Logger) -> tuple[Any, Path]:
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    install_log = LOG_DIR / f"installer-{int(time.time())}.log"
    command = [
        str(path),
        "/VERYSILENT",
        "/SUPPRESSMSGBOXES",
        "/NORESTART",
        "/CLOSEAPPLICATIONS",
        f"/LOG={install_log}",
        # Tells the installer that this service is supervising it: the service
        # keeps running until the end instead of being stopped up front.
        "/SUPERVISED=1",
    ]
    flags = getattr(subprocess, "DETACHED_PROCESS", 0) | getattr(subprocess, "CREATE_NEW_PROCESS_GROUP", 0)
    process = subprocess.Popen(command, close_fds=True, creationflags=flags)
    logger.info("Instalador %s iniciado silenciosamente (PID %s).", path.name, process.pid)
    return process, install_log


def report_install_failure(
    api: ApiClient,
    state: dict[str, Any],
    logger: logging.Logger,
    outcome: str,
    exit_code: int | None = None,
    installer_log: Path | None = None,
) -> int:
    """Record a failed attempt of the pending package and tell the dashboard."""
    version = str(state.get("pending_version", ""))
    started_at = float(state.get("pending_started_at", 0) or 0)
    attempts, delay, exhausted = register_install_failure(state, time.time())
    install_log = collect_install_log(version, attempts, outcome, exit_code, started_at, installer_log)
    try:
        LOG_DIR.mkdir(parents=True, exist_ok=True)
        INSTALL_FAILURE_LOG_PATH.write_text(install_log, encoding="utf-8")
    except OSError:
        logger.warning("Nao foi possivel gravar %s.", INSTALL_FAILURE_LOG_PATH)

    reason = describe_outcome(outcome, exit_code)
    if exhausted:
        status = STATUS_INSTALL_FAILED
        message = (
            f"Falha na instalacao de {version} apos {attempts} tentativas: {reason}. "
            f"Nova tentativa automatica em {delay // 3600} h ou use Verificar agora."
        )
    else:
        status = STATUS_RETRYING
        message = (
            f"Falha na tentativa {attempts} de {TOTAL_INSTALL_ATTEMPTS} ao instalar {version}: {reason}. "
            f"Nova tentativa ({attempts} de {MAX_INSTALL_RETRIES}) em {delay} segundos."
        )
    installed = str(state.get("installed_version", "0.0.0"))
    state["last_result"] = status
    state["last_error"] = message[:1000]
    state["last_check"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
    atomic_json(STATE_PATH, state)
    logger.error("%s\n%s", message, install_log)
    api.report(status, installed, version, message, install_log=install_log)
    return 0 if exhausted else 2


def check_once(
    logger: logging.Logger,
    manual: bool = False,
    stop_requested: Callable[[], bool] = lambda: False,
) -> int:
    try:
        config = validate_config(load_json(CONFIG_PATH))
    except (UpdaterError, ValueError, TypeError) as exc:
        logger.error("Configuracao do servico invalida; nova tentativa em 5 minutos: %s", exc)
        return 2
    state = get_state()
    installed = str(state.get("installed_version", "0.0.0"))
    if not VERSION_RE.fullmatch(installed):
        # A damaged state file must not stop the service: reinstalling the
        # published package rewrites installed_version through --configure.
        logger.warning("installed_version invalida no estado local (%r); considerando 0.0.0.", installed)
        installed = "0.0.0"
    api = ApiClient(config)
    try:
        if manual and state.get("failed_attempts"):
            clear_install_failures(state)
            atomic_json(STATE_PATH, state)
            logger.info("Verificacao manual: contador de falhas de instalacao zerado.")

        pending_version = str(state.get("pending_version", ""))
        if pending_version:
            # A pending installation survives only when the service restarted
            # before the installer finished: an installer that stopped the
            # service, a reboot, or a crash. --configure clears it on success.
            pending_started_at = float(state.get("pending_started_at", 0) or 0)
            if pending_version == installed:
                clear_pending_install(state)
                clear_install_failures(state)
                atomic_json(STATE_PATH, state)
            else:
                setup_pids = running_setup_processes()
                if setup_pids and time.time() - pending_started_at < INSTALL_TIMEOUT_SECONDS:
                    api.report(
                        "installing", installed, pending_version,
                        f"Instalacao de {pending_version} em andamento; aguardando o instalador terminar.",
                    )
                    logger.info("Instalador de %s ainda em execucao (PIDs %s).", pending_version, setup_pids)
                    return 2
                for pid in setup_pids:
                    kill_process_tree(pid)
                return report_install_failure(api, state, logger, "timeout" if setup_pids else "missing")

        api.report("checking", installed, message="Consultando a versao publicada.")
        try:
            release = api.latest()
        except NoReleaseError:
            state["last_check"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
            state["last_result"] = "waiting_release"
            state["last_available_version"] = ""
            state["last_error"] = ""
            state["check_interval_seconds"] = int(config["check_interval_seconds"])
            atomic_json(STATE_PATH, state)
            api.report(
                "waiting_release", installed,
                message="Computador registrado; aguardando a primeira versao publicada.",
            )
            logger.info("Nenhuma versao publicada; nova consulta no intervalo configurado.")
            return 0
        available = str(release["version"])
        interval = max(300, min(86400, int(release.get("check_interval_seconds", config["check_interval_seconds"]))))
        state["last_check"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
        state["last_available_version"] = available
        state["check_interval_seconds"] = interval
        action = decide_action(installed, available, bool(release["allow_downgrade"]))
        if action in (ACTION_CURRENT, ACTION_BLOCKED_DOWNGRADE):
            status = "current"
            if action == ACTION_CURRENT:
                clear_install_failures(state)
                cleanup_downloads()
                if state.get("last_result") == "installed":
                    # First contact after --configure: tell the dashboard the
                    # silent installation finished instead of a generic state.
                    status = "updated"
                    message = f"Versao {installed} instalada com sucesso."
                else:
                    message = "A maquina ja esta na versao publicada."
            else:
                message = (
                    f"Versao instalada {installed} e superior a publicada {available}; "
                    "downgrade nao autorizado no dashboard."
                )
            state["last_result"] = "current"
            state["last_error"] = ""
            atomic_json(STATE_PATH, state)
            api.report(status, installed, available, message)
            logger.info("Versao atual %s; publicada %s (%s). Nenhuma acao necessaria.", installed, available, action)
            return 0

        blocked = install_block_reason(state, release, time.time())
        if blocked is not None:
            status, message = blocked
            state["last_result"] = status
            state["last_error"] = message
            atomic_json(STATE_PATH, state)
            api.report(status, installed, available, message)
            logger.warning(message)
            return 0 if status == STATUS_INSTALL_FAILED else 2

        is_rollback = action == ACTION_DOWNGRADE
        attempt = attempt_number(state, available, release["sha256"])
        attempt_label = f"tentativa {attempt} de {TOTAL_INSTALL_ATTEMPTS}"
        DOWNLOAD_DIR.mkdir(parents=True, exist_ok=True)
        destination = DOWNLOAD_DIR / f"Ativa-Unified-Agent-Setup-{available}.exe"
        cleanup_downloads(keep=destination)
        api.report(
            "downloading", installed, available,
            f"Rollback: baixando a versao {available} para substituir a {installed} ({attempt_label})."
            if is_rollback else f"Baixando e validando o instalador ({attempt_label}).",
        )
        api.download(release, destination)
        state["last_result"] = "installing"
        state["pending_version"] = available
        state["pending_sha256"] = release["sha256"]
        state["pending_action"] = action
        state["pending_started_at"] = time.time()
        state["last_error"] = ""
        atomic_json(STATE_PATH, state)
        api.report(
            "installing", installed, available,
            f"Rollback: voltando de {installed} para {available}; instalacao silenciosa iniciada ({attempt_label})."
            if is_rollback else f"Instalacao silenciosa iniciada ({attempt_label}).",
        )
        process, installer_log = launch_installer(destination, logger)
        outcome, exit_code = supervise_installer(process, stop_requested)
        if outcome == "stopping":
            # Expected on success: the installer restarts the service to run the
            # new executable, and the new process reports the result.
            logger.info("Instalador reiniciando o servico para concluir a atualizacao para %s.", available)
            return 10

        # Re-read the state: --configure rewrites it when the package is installed.
        refreshed = get_state()
        if refreshed.get("installed_version") == available and outcome == "exited" and exit_code == 0:
            refreshed["last_result"] = "current"
            atomic_json(STATE_PATH, refreshed)
            api.report("updated", available, available, f"Versao {available} instalada com sucesso.")
            logger.info("Instalacao de %s concluida.", available)
            return 0
        if outcome == "timeout":
            kill_process_tree(process.pid)
            for pid in running_setup_processes():
                kill_process_tree(pid)
        elif outcome == "exited" and exit_code == 0:
            outcome = "incomplete"
        for key in ("pending_version", "pending_sha256", "pending_action", "pending_started_at",
                    "failed_version", "failed_sha256", "failed_attempts"):
            if key in state:
                refreshed[key] = state[key]
        return report_install_failure(api, refreshed, logger, outcome, exit_code, installer_log)
    except Exception as exc:
        # Re-read before writing: the installer may have rewritten the state
        # (--configure) while this check was running.
        current_state = get_state()
        current_state["last_result"] = "error"
        current_state["last_error"] = str(exc)[:1000]
        current_state["last_check"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
        atomic_json(STATE_PATH, current_state)
        try:
            api.report(
                "error", str(current_state.get("installed_version", installed)),
                str(state.get("last_available_version", "")), str(exc),
            )
        except Exception:
            logger.exception("Tambem falhou o envio do status de erro.")
        logger.exception("Falha ao verificar ou instalar atualizacao.")
        return 2


def configure_service(config_source: Path, installed_version: str) -> int:
    version_tuple(installed_version)
    config = validate_config(load_json(config_source))
    PRODUCT_DIR.mkdir(parents=True, exist_ok=True)
    DOWNLOAD_DIR.mkdir(parents=True, exist_ok=True)
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    if os.name == "nt":
        # A previous implementation applied /inheritance:r recursively. On
        # regular files this could remove every inherited ACE while the
        # inheritable (OI)(CI) grants only remained on the parent directory,
        # leaving the service executable with an empty ACL. Reset children
        # first, then protect only the root; children inherit SYSTEM/Admin.
        subprocess.run(
            ["icacls.exe", str(PRODUCT_DIR), "/reset", "/T", "/C"],
            check=True,
            capture_output=True,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        subprocess.run(
            [
                "icacls.exe", str(PRODUCT_DIR), "/inheritance:r",
                "/grant:r", "*S-1-5-18:(OI)(CI)F", "*S-1-5-32-544:(OI)(CI)F",
            ],
            check=True,
            capture_output=True,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
    atomic_json(CONFIG_PATH, config)
    previous = get_state()
    previous.update({
        "installed_version": installed_version,
        "installed_at": time.strftime("%Y-%m-%dT%H:%M:%S%z"),
        "last_result": "installed",
        "last_error": "",
    })
    clear_pending_install(previous)
    clear_install_failures(previous)
    atomic_json(STATE_PATH, previous)
    return 0


class ServiceRuntime:
    def __init__(self):
        self.stop_event = threading.Event()

    def run(self, logger: logging.Logger) -> None:
        logger.info("Servico %s iniciado; primeira consulta imediata.", UPDATER_VERSION)
        cleanup_replaced_binaries()
        next_full_check = 0.0
        while not self.stop_event.is_set():
            now = time.monotonic()
            manual_check = False
            if now < next_full_check:
                try:
                    config = validate_config(load_json(CONFIG_PATH))
                    manual_check = ApiClient(config).command_pending()
                    if manual_check:
                        logger.info("Verificacao imediata recebida do dashboard.")
                except Exception as exc:
                    logger.warning("Nao foi possivel consultar comandos agora: %s", exc)

            if now >= next_full_check or manual_check:
                try:
                    result = check_once(logger, manual=manual_check, stop_requested=self.stop_event.is_set)
                except Exception:
                    # Never let an unexpected error end the service: Windows
                    # only restarts it automatically after a crash, not after
                    # a clean stop, and the machine would stop updating.
                    logger.exception("Falha inesperada na verificacao; nova tentativa em 300 segundos.")
                    result = 2
                if self.stop_event.is_set():
                    break
                delay = next_check_delay(result, get_state(), time.time())
                next_full_check = time.monotonic() + delay
                if result != 0:
                    logger.warning("Nova verificacao completa agendada em %s segundos.", delay)

            wait_seconds = min(COMMAND_POLL_SECONDS, max(1.0, next_full_check - time.monotonic()))
            self.stop_event.wait(wait_seconds)


SERVICE_WIN32_OWN_PROCESS = 0x10
SERVICE_STOPPED = 1
SERVICE_START_PENDING = 2
SERVICE_STOP_PENDING = 3
SERVICE_RUNNING = 4
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
    _runtime: ServiceRuntime | None = None

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
        _runtime = ServiceRuntime()
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
        raise UpdaterError("O modo servico esta disponivel somente no Windows.")
    table = (SERVICE_TABLE_ENTRY * 2)()
    table[0].lpServiceName = SERVICE_NAME
    table[0].lpServiceProc = service_main
    table[1].lpServiceName = None
    table[1].lpServiceProc = SERVICE_MAIN()
    if not _advapi32.StartServiceCtrlDispatcherW(table):
        raise ctypes.WinError(ctypes.get_last_error())
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="Servico de atualizacao do instalador unificado Ativa")
    parser.add_argument("--service", action="store_true", help="Executa pelo Windows Service Control Manager")
    parser.add_argument("--run-once", action="store_true", help="Faz uma verificacao imediata")
    parser.add_argument("--configure", action="store_true", help="Instala a configuracao local protegida")
    parser.add_argument(
        "--start-wallpaper-clients", action="store_true",
        help="Inicia o cliente de wallpaper nas sessoes de usuario (uso pelo instalador como SYSTEM)",
    )
    parser.add_argument("--config", type=Path)
    parser.add_argument("--installed-version", default="")
    parser.add_argument("--debug", action="store_true")
    parser.add_argument("--version", action="store_true")
    args = parser.parse_args()
    if args.version:
        print(UPDATER_VERSION)
        return 0
    if args.configure:
        if args.config is None or not args.installed_version:
            parser.error("--configure exige --config e --installed-version")
        return configure_service(args.config, args.installed_version)
    if args.start_wallpaper_clients:
        return start_wallpaper_clients(configure_logging(args.debug))
    if args.run_once:
        with SingleInstance():
            return check_once(configure_logging(args.debug), manual=True)
    if args.service:
        return run_service_dispatcher()
    parser.error("selecione --service, --run-once, --configure ou --version")
    return 2


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception:
        try:
            configure_logging(False).exception("Ativa Unified Updater encerrou com erro.")
        finally:
            raise SystemExit(2)
