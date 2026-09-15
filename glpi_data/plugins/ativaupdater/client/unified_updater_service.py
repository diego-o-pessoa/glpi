from __future__ import annotations

import argparse
import csv
import ctypes
from ctypes import wintypes
import hashlib
import http.client
import io
import json
import logging
from logging.handlers import RotatingFileHandler
import os
from pathlib import Path
import re
import shutil
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
UPDATER_VERSION = "1.6.1"
DEFAULT_INTERVAL = 3600
COMMAND_POLL_SECONDS = 15

# check_once results.
RESULT_OK = 0
RESULT_RETRY = 2
RESULT_SERVICE_STOPPING = 10
RESULT_RESTART_NOW = 20

# Commands sent from the dashboard (polled continuously, even during an installation).
COMMAND_CHECK = "check"
COMMAND_REINSTALL = "reinstall"
COMMAND_RESTART_SERVICE = "restart_service"
COMMAND_SEND_LOGS = "send_logs"
COMMANDS = (COMMAND_CHECK, COMMAND_REINSTALL, COMMAND_RESTART_SERVICE, COMMAND_SEND_LOGS)

# Transient network failures (timeouts, resets) are retried before a check fails.
API_RETRY_DELAYS_SECONDS = (5, 15)
DOWNLOAD_RETRY_DELAYS_SECONDS = (10, 30)
DIAGNOSTICS_MAX_CHARS = 60000

# Watchdog: a scheduled task that runs a separate copy of this executable.
WATCHDOG_TASK_NAME = "Ativa Unified Updater Watchdog"
WATCHDOG_INTERVAL_MINUTES = 15
WATCHDOG_INSTALL_LIMIT_SECONDS = 2700
WATCHDOG_HEARTBEAT_LIMIT_SECONDS = 1800
WATCHDOG_MUTEX_NAME = r"Global\AtivaUnifiedUpdaterWatchdog"
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
# The install runner enforces INSTALL_TIMEOUT_SECONDS on the setup itself; the
# service gives it extra time to stop the service, clean up and write the result.
INSTALL_SUPERVISION_SECONDS = INSTALL_TIMEOUT_SECONDS + 300
PENDING_RECHECK_SECONDS = 30
SERVICE_STOP_TIMEOUT_SECONDS = 60
RUNNER_TIMEOUT_EXIT_CODE = 1460
RUNNER_MUTEX_NAME = r"Global\AtivaUnifiedUpdaterInstall"
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
SERVICE_EXE = PRODUCT_DIR / "AtivaUnifiedUpdater.exe"
WATCHDOG_DIR = PRODUCT_DIR / "watchdog"
WATCHDOG_EXE = WATCHDOG_DIR / "AtivaUnifiedUpdater.exe"
# Separate copy that runs the installation, so the setup can stop the service and
# replace SERVICE_EXE (the same steps as Deploy-AtivaUnifiedAgent.ps1).
RUNNER_DIR = PRODUCT_DIR / "runner"
RUNNER_EXE = RUNNER_DIR / "AtivaUnifiedUpdater.exe"
INSTALL_RESULT_PATH = PRODUCT_DIR / "install-result.json"
RUNNER_LOG_NAME = "install-runner.log"
HEARTBEAT_PATH = PRODUCT_DIR / "heartbeat.json"
RECOVERY_MARKER_PATH = PRODUCT_DIR / "watchdog-recovery.json"
MUTEX_NAME = r"Global\AtivaUnifiedUpdater"


class UpdaterError(RuntimeError):
    pass


class NoReleaseError(UpdaterError):
    pass


class OperationCancelled(UpdaterError):
    """The dashboard asked to cancel the current work and start over."""


class SingleInstance:
    def __init__(self, name: str = MUTEX_NAME) -> None:
        self.name = name
        self.handle = None

    def __enter__(self):
        if os.name != "nt":
            return self
        kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
        kernel32.CreateMutexW.restype = wintypes.HANDLE
        self.handle = kernel32.CreateMutexW(None, False, self.name)
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


def configure_logging(debug: bool = False, filename: str = "service.log") -> logging.Logger:
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger("AtivaUnifiedUpdater." + filename)
    for handler in logger.handlers[:]:
        handler.close()
        logger.removeHandler(handler)
    logger.setLevel(logging.DEBUG if debug else logging.INFO)
    logger.propagate = False
    file_handler = RotatingFileHandler(
        LOG_DIR / filename, maxBytes=5 * 1024 * 1024, backupCount=5, encoding="utf-8"
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
    if state.get("pending_version"):
        # An installation is finishing (e.g. the setup already restarted the
        # service): report its result soon instead of in five minutes.
        delay = min(delay, PENDING_RECHECK_SECONDS)
    try:
        retry_after = float(state.get("retry_after", 0) or 0)
    except (TypeError, ValueError):
        retry_after = 0.0
    if retry_after > now:
        delay = min(delay, max(30, int(retry_after - now) + 1))
    return delay


def describe_outcome(outcome: str, exit_code: int | None, detail: str = "") -> str:
    if outcome == "timeout":
        return f"o instalador excedeu {INSTALL_TIMEOUT_SECONDS // 60} minutos e foi finalizado"
    if outcome == "error":
        return f"falha ao preparar a instalacao: {detail or 'erro desconhecido'}"
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
    candidates = newest_files(directory, pattern, not_before, 1)
    return candidates[0] if candidates else None


def newest_files(directory: Path, pattern: str, not_before: float = 0.0, limit: int = 20) -> list[Path]:
    try:
        candidates = [path for path in directory.glob(pattern) if path.stat().st_mtime >= not_before]
    except OSError:
        return []
    try:
        return sorted(candidates, key=lambda path: path.stat().st_mtime, reverse=True)[:max(0, limit)]
    except OSError:
        return []


def collect_install_log(
    version: str,
    attempt: int,
    outcome: str,
    exit_code: int | None,
    started_at: float,
    installer_log: Path | None = None,
    detail: str = "",
) -> str:
    """Build the diagnostic log sent to the dashboard for a failed attempt."""
    not_before = started_at - 60 if started_at else 0.0
    lines = [
        f"Ativa Unified Updater {UPDATER_VERSION} - falha na instalacao",
        f"Data: {time.strftime('%Y-%m-%d %H:%M:%S')}",
        f"Computador: {socket.gethostname()}",
        f"Versao: {version} | tentativa {attempt} de {TOTAL_INSTALL_ATTEMPTS}",
        f"Resultado: {describe_outcome(outcome, exit_code, detail)}",
    ]

    runner_log = LOG_DIR / RUNNER_LOG_NAME
    try:
        runner_log_recent = runner_log.is_file() and runner_log.stat().st_mtime >= not_before
    except OSError:
        runner_log_recent = False
    if runner_log_recent:
        lines.append("")
        lines.append(f"== Executor da instalacao ({RUNNER_LOG_NAME}) ==")
        lines.extend(read_log_tail(runner_log, 30))

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


def process_ids_by_image(image: Path) -> list[int]:
    """PIDs of the processes running this executable file (compared by full path)."""
    if os.name != "nt":
        return []
    kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
    kernel32.K32EnumProcesses.argtypes = [ctypes.POINTER(wintypes.DWORD), wintypes.DWORD, ctypes.POINTER(wintypes.DWORD)]
    kernel32.OpenProcess.restype = wintypes.HANDLE
    kernel32.OpenProcess.argtypes = [wintypes.DWORD, wintypes.BOOL, wintypes.DWORD]
    kernel32.QueryFullProcessImageNameW.argtypes = [
        wintypes.HANDLE, wintypes.DWORD, wintypes.LPWSTR, ctypes.POINTER(wintypes.DWORD),
    ]
    kernel32.CloseHandle.argtypes = [wintypes.HANDLE]
    capacity = 4096
    while True:
        pids = (wintypes.DWORD * capacity)()
        needed = wintypes.DWORD()
        if not kernel32.K32EnumProcesses(pids, ctypes.sizeof(pids), ctypes.byref(needed)):
            return []
        if needed.value < ctypes.sizeof(pids):
            break
        capacity *= 2
    target = os.path.normcase(os.path.abspath(str(image)))
    matches = []
    for pid in pids[: needed.value // ctypes.sizeof(wintypes.DWORD)]:
        if not pid:
            continue
        handle = kernel32.OpenProcess(0x1000, False, pid)  # PROCESS_QUERY_LIMITED_INFORMATION
        if not handle:
            continue
        try:
            buffer = ctypes.create_unicode_buffer(32768)
            size = wintypes.DWORD(len(buffer))
            if kernel32.QueryFullProcessImageNameW(handle, 0, buffer, ctypes.byref(size)):
                if os.path.normcase(buffer.value) == target:
                    matches.append(int(pid))
        finally:
            kernel32.CloseHandle(handle)
    return matches


def own_process_ids() -> set[int]:
    # PyInstaller onefile: the bootloader (parent) runs the same executable.
    return {os.getpid(), os.getppid()}


def running_setup_processes() -> list[int]:
    """Unified installers and install runners in progress (never this process)."""
    if os.name != "nt":
        return []
    try:
        completed = subprocess.run(
            ["tasklist.exe", "/FO", "CSV", "/NH"],
            capture_output=True, text=True, errors="replace", timeout=60,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        setups = parse_setup_processes(completed.stdout)
    except (OSError, subprocess.SubprocessError):
        setups = []
    try:
        runners = process_ids_by_image(RUNNER_EXE)
    except OSError:
        runners = []
    own = own_process_ids()
    return [pid for pid in setups + runners if pid not in own]


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
    cancel_requested: Callable[[], bool] = lambda: False,
) -> tuple[str, int | None]:
    """Wait for the installer: ('exited', code), ('stopping'|'cancelled'|'timeout', None).

    'stopping' means the installer is restarting this service to switch to the
    new executable; the installer keeps running and must not be killed.
    'cancelled' means the dashboard asked to start over.
    """
    deadline = clock() + timeout_seconds
    while True:
        code = process.poll()
        if code is not None:
            return "exited", int(code)
        if stop_requested():
            return "stopping", None
        if cancel_requested():
            return "cancelled", None
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
    own = own_process_ids()
    for path in candidates:
        try:
            # Services up to 1.4.0 (windowed builds) could stay alive after
            # stopping, hidden behind a message box in session 0.
            for pid in process_ids_by_image(path):
                if pid not in own:
                    kill_process_tree(pid)
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
        # Keeps the current installer and its resumable partial download.
        if keep is not None and path.name.lower().startswith(keep.name.lower()):
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


def is_transient_network_error(exc: BaseException) -> bool:
    """Connection problems worth retrying (timeouts, resets, gateway errors)."""
    if isinstance(exc, HTTPError):
        return exc.code in (408, 429, 502, 503, 504)
    return isinstance(exc, (URLError, OSError))


class ApiClient:
    def __init__(self, config: dict[str, Any], retry_sleep: Callable[[float], None] = time.sleep):
        self.base_url = str(config["api_url"])
        self.token = str(config["api_token"])
        self.origin = self._origin(self.base_url)
        self.opener = build_opener(NoRedirect(), HTTPSHandler(context=ssl.create_default_context()))
        self.retry_sleep = retry_sleep
        # Highest dashboard command received; sent with every report as acknowledgement.
        self.command_seq: int | None = None
        # Note left by the watchdog after repairing this computer.
        self.recovery_note: str | None = None

    @staticmethod
    def _origin(url: str) -> tuple[str, str, int]:
        parsed = urlsplit(url)
        port = parsed.port or (443 if parsed.scheme == "https" else 80)
        return parsed.scheme.lower(), (parsed.hostname or "").lower(), port

    def _request(
        self,
        url: str,
        *,
        method: str = "GET",
        data: bytes | None = None,
        timeout: int = 60,
        headers: dict[str, str] | None = None,
        retry_delays: tuple[float, ...] = (),
    ):
        if self._origin(url) != self.origin:
            raise UpdaterError("A API retornou um endereco de download fora da origem permitida.")
        request_headers = {
            "Authorization": f"Bearer {self.token}",
            "Accept": "application/json",
            "User-Agent": f"AtivaUnifiedUpdater/{UPDATER_VERSION}",
        }
        if data is not None:
            request_headers["Content-Type"] = "application/json"
        request_headers.update(headers or {})
        for attempt in range(len(retry_delays) + 1):
            request = Request(url, data=data, headers=request_headers, method=method)
            try:
                return self.opener.open(request, timeout=timeout)
            except HTTPError as exc:
                body = exc.read(4096).decode("utf-8", errors="replace")
                if exc.code == 404 and '"NO_RELEASE"' in body:
                    raise NoReleaseError("Nenhuma versao foi publicada.") from exc
                if attempt < len(retry_delays) and is_transient_network_error(exc):
                    self.retry_sleep(retry_delays[attempt])
                    continue
                raise UpdaterError(f"API respondeu HTTP {exc.code}: {body}") from exc
            except (URLError, OSError) as exc:
                if attempt < len(retry_delays):
                    self.retry_sleep(retry_delays[attempt])
                    continue
                raise UpdaterError(
                    f"Falha de comunicacao com a API apos {attempt + 1} tentativa(s): {exc}"
                ) from exc
        raise UpdaterError("Falha de comunicacao com a API.")

    def latest(self) -> dict[str, Any]:
        with self._request(self.base_url + "/latest", retry_delays=API_RETRY_DELAYS_SECONDS) as response:
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
        if self.command_seq is not None:
            payload["command_seq"] = int(self.command_seq)
        recovery_note = self.recovery_note
        if recovery_note:
            payload["recovery_note"] = recovery_note[:255]
        data = json.dumps(payload, separators=(",", ":"), ensure_ascii=False).encode("utf-8")
        with self._request(
            self.base_url + "/status", method="POST", data=data, timeout=30, retry_delays=API_RETRY_DELAYS_SECONDS
        ) as response:
            response.read(4096)
        if recovery_note:
            self.recovery_note = None
            RECOVERY_MARKER_PATH.unlink(missing_ok=True)

    def commands(self) -> dict[str, Any]:
        """Pending dashboard command: {'pending': bool, 'seq': int, 'command': str | None}."""
        url = self.base_url + "/commands/" + quote(machine_guid(), safe="")
        with self._request(url, timeout=30) as response:
            if "application/json" not in response.headers.get("Content-Type", ""):
                raise UpdaterError("A API respondeu commands com tipo de conteudo invalido.")
            payload = json.loads(response.read(65537).decode("utf-8"))
        if not isinstance(payload, dict) or not isinstance(payload.get("check_now"), bool):
            raise UpdaterError("Resposta de comandos invalida.")
        return parse_command_response(payload)

    def send_diagnostics(self, diagnostics: str) -> None:
        payload = {
            "machine_guid": machine_guid(),
            "hostname": socket.gethostname(),
            "updater_version": UPDATER_VERSION,
            "diagnostics": diagnostics[:DIAGNOSTICS_MAX_CHARS],
        }
        if self.command_seq is not None:
            payload["command_seq"] = int(self.command_seq)
        data = json.dumps(payload, separators=(",", ":"), ensure_ascii=False).encode("utf-8")
        with self._request(
            self.base_url + "/diagnostics", method="POST", data=data, timeout=60, retry_delays=API_RETRY_DELAYS_SECONDS
        ) as response:
            response.read(4096)

    def download(
        self,
        release: dict[str, Any],
        destination: Path,
        cancel_requested: Callable[[], bool] = lambda: False,
    ) -> None:
        """Download and validate the installer, resuming a previous partial download."""
        size = int(release["size"])
        expected_sha256 = str(release["sha256"])
        partial = destination.with_name(f"{destination.name}.{expected_sha256[:16]}.part")
        offset = partial.stat().st_size if partial.is_file() else 0
        if offset > size:
            partial.unlink(missing_ok=True)
            offset = 0
        digest = hashlib.sha256()
        if offset:
            with partial.open("rb") as existing:
                for block in iter(lambda: existing.read(1024 * 1024), b""):
                    digest.update(block)
        headers = {"Range": f"bytes={offset}-"} if 0 < offset < size else None
        received = offset
        if offset < size:
            try:
                response_context = self._request(str(release["download_url"]), timeout=300, headers=headers)
            except UpdaterError as exc:
                if "HTTP 416" in str(exc):
                    partial.unlink(missing_ok=True)
                raise
            with response_context as response:
                content_type = response.headers.get("Content-Type", "").split(";", 1)[0].strip().lower()
                if content_type != "application/octet-stream":
                    raise UpdaterError(f"Tipo de conteudo inesperado: {content_type}")
                if headers and getattr(response, "status", 200) != 206:
                    # The server ignored the range: start over.
                    digest = hashlib.sha256()
                    received = 0
                with partial.open("ab" if received else "wb") as stream:
                    while True:
                        if cancel_requested():
                            raise OperationCancelled("Download cancelado pelo dashboard.")
                        block = response.read(1024 * 1024)
                        if not block:
                            break
                        received += len(block)
                        if received > size or received > MAX_INSTALLER_BYTES:
                            partial.unlink(missing_ok=True)
                            raise UpdaterError("O download excedeu o tamanho publicado.")
                        digest.update(block)
                        stream.write(block)
                    stream.flush()
                    os.fsync(stream.fileno())
        if received != size:
            raise UpdaterError(f"Download incompleto: {received} de {size} bytes; sera retomado.")
        if digest.hexdigest() != expected_sha256:
            partial.unlink(missing_ok=True)
            raise UpdaterError("O SHA-256 do instalador nao confere.")
        with partial.open("rb") as stream:
            if stream.read(2) != b"MZ":
                partial.unlink(missing_ok=True)
                raise UpdaterError("O arquivo baixado nao e um executavel Windows.")
        os.replace(partial, destination)


def parse_command_response(payload: dict[str, Any]) -> dict[str, Any]:
    pending = payload.get("check_now") is True
    seq = payload.get("request_seq")
    seq = seq if isinstance(seq, int) and not isinstance(seq, bool) and seq >= 0 else 0
    command = payload.get("command")
    if command not in COMMANDS:
        command = COMMAND_CHECK if pending else None
    return {"pending": pending, "seq": seq, "command": command if pending else None}


def download_with_retries(
    api: ApiClient,
    release: dict[str, Any],
    destination: Path,
    logger: logging.Logger,
    cancel_requested: Callable[[], bool] = lambda: False,
    sleep: Callable[[float], None] = time.sleep,
) -> None:
    delays = DOWNLOAD_RETRY_DELAYS_SECONDS
    for attempt in range(len(delays) + 1):
        try:
            api.download(release, destination, cancel_requested)
            return
        except OperationCancelled:
            raise
        except (UpdaterError, OSError, http.client.HTTPException) as exc:
            if attempt >= len(delays):
                if isinstance(exc, UpdaterError):
                    raise
                raise UpdaterError(f"Falha no download do instalador: {exc}") from exc
            logger.warning("Download interrompido (%s); retomando em %s segundos.", exc, delays[attempt])
            sleep(delays[attempt])
            if cancel_requested():
                raise OperationCancelled("Download cancelado pelo dashboard.") from exc


def installer_command(path: Path, install_log: Path) -> list[str]:
    """Silent Inno Setup command, the same used by Deploy-AtivaUnifiedAgent.ps1."""
    return [
        str(path),
        "/VERYSILENT",
        "/SUPPRESSMSGBOXES",
        "/NORESTART",
        # /CLOSEAPPLICATIONS made the Restart Manager try to stop the updater
        # service for 90 s and then abort the whole installation.
        "/NOCLOSEAPPLICATIONS",
        "/SP-",
        f"/LOG={install_log}",
        # No /SUPERVISED=1: the setup stops the (already stopped) service and
        # replaces its executable, like a manual installation.
    ]


def start_detached(command: list[str]) -> Any:
    """Start a process that outlives the service (no console window, own process group)."""
    flags = getattr(subprocess, "CREATE_NO_WINDOW", 0) | getattr(subprocess, "CREATE_NEW_PROCESS_GROUP", 0)
    breakaway = 0x01000000  # CREATE_BREAKAWAY_FROM_JOB
    if os.name == "nt":
        try:
            return subprocess.Popen(command, close_fds=True, creationflags=flags | breakaway)
        except OSError:
            # Not in a job, or the job forbids breakaway: start normally.
            pass
    return subprocess.Popen(command, close_fds=True, creationflags=flags)


def prepare_runner_executable() -> list[str]:
    """Command prefix of the install runner: a copy of this executable outside SERVICE_EXE."""
    source = running_executable()
    if source is None:
        # Development (python unified_updater_service.py).
        return [sys.executable, str(Path(__file__).resolve())]
    RUNNER_DIR.mkdir(parents=True, exist_ok=True)
    if not (RUNNER_EXE.is_file() and file_sha256(RUNNER_EXE) == file_sha256(source)):
        staged = RUNNER_EXE.with_name(RUNNER_EXE.name + ".new")
        try:
            shutil.copy2(source, staged)
            os.replace(staged, RUNNER_EXE)
        except OSError as exc:
            raise UpdaterError(f"nao foi possivel preparar o executor da instalacao: {exc}") from exc
    return [str(RUNNER_EXE)]


def launch_installer(path: Path, logger: logging.Logger, version: str = "", sha256: str = "") -> tuple[Any, Path]:
    """Hand the installation over to the install runner and return (runner process, setup log)."""
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    install_log = LOG_DIR / f"installer-{int(time.time())}.log"
    INSTALL_RESULT_PATH.unlink(missing_ok=True)
    command = prepare_runner_executable() + [
        "--install-package", str(path),
        "--package-version", version,
        "--package-sha256", sha256,
        "--installer-log", str(install_log),
    ]
    process = start_detached(command)
    logger.info("Executor da instalacao de %s iniciado (PID %s).", path.name, process.pid)
    return process, install_log


def read_install_result(version: str, sha256: str) -> dict[str, Any] | None:
    """Result written by the install runner for this package, if any."""
    try:
        result = load_json(INSTALL_RESULT_PATH)
    except UpdaterError:
        return None
    if result.get("version") != version or str(result.get("sha256", "")).lower() != sha256.lower():
        return None
    return result


def runner_outcome(result: dict[str, Any] | None, exit_code: int | None) -> tuple[str, int | None, str]:
    """(outcome, exit_code, detail) of an installation, preferring the runner result."""
    if result is None:
        return "exited", exit_code, ""
    outcome = result.get("outcome")
    if outcome == "timeout":
        return "timeout", None, ""
    if outcome == "error":
        return "error", None, str(result.get("message", ""))[:500]
    try:
        return "exited", int(result.get("exit_code")), ""
    except (TypeError, ValueError):
        return "exited", exit_code, ""


def stop_updater_service(logger: logging.Logger) -> None:
    service = query_service()
    if service is None or service[0] == SERVICE_STATE_STOPPED:
        return
    logger.info("Parando o servico %s para a instalacao.", SERVICE_NAME)
    run_sc("stop", SERVICE_NAME)
    if not wait_service_state(SERVICE_STATE_STOPPED, SERVICE_STOP_TIMEOUT_SECONDS):
        current = query_service()
        if current is not None and current[1]:
            logger.warning("Servico nao parou em %s s; encerrando o processo %s.", SERVICE_STOP_TIMEOUT_SECONDS, current[1])
            kill_process_tree(current[1])
            wait_service_state(SERVICE_STATE_STOPPED, 15)


def kill_leftover_processes(logger: logging.Logger) -> None:
    """Installers from earlier attempts and service executables that did not exit.

    Old windowed builds could stay alive after the service stopped (a message box
    nobody sees in session 0), keeping SERVICE_EXE locked for the setup.
    """
    own = own_process_ids()
    leftovers = [pid for pid in running_setup_processes() + process_ids_by_image(SERVICE_EXE) if pid not in own]
    for pid in leftovers:
        kill_process_tree(pid)
    if leftovers:
        logger.warning("Processos restantes encerrados antes da instalacao: %s", leftovers)
        time.sleep(3)


def ensure_service_running(logger: logging.Logger) -> bool:
    service = query_service()
    if service is not None and service[0] in (SERVICE_STATE_RUNNING, SERVICE_STATE_START_PENDING):
        return True
    if not service_exe_healthy():
        restore_service_executable(logger)
    run_sc("start", SERVICE_NAME)
    started = wait_service_state(SERVICE_STATE_RUNNING, 60)
    if started:
        logger.info("Servico %s em execucao.", SERVICE_NAME)
    else:
        logger.error("O servico %s nao iniciou; o vigia tentara novamente.", SERVICE_NAME)
    return started


def run_install_package(logger: logging.Logger, package: Path, version: str, sha256: str, install_log: Path) -> int:
    """Install runner: the steps of Deploy-AtivaUnifiedAgent.ps1, started by the service.

    Stops the service, removes leftovers, runs the setup silently with a timeout,
    writes INSTALL_RESULT_PATH for the service and makes sure the service runs again.
    """
    try:
        lock = SingleInstance(RUNNER_MUTEX_NAME)
        lock.__enter__()
    except UpdaterError:
        logger.warning("Outra instalacao ja esta em andamento; nada a fazer.")
        return 0
    result: dict[str, Any] = {
        "version": version, "sha256": sha256.lower(), "started_at": time.time(), "installer_log": str(install_log),
    }
    exit_code = 1
    try:
        logger.info("Instalacao de %s iniciada pelo servico.", version)
        if not package.is_file() or file_sha256(package) != sha256.lower():
            raise UpdaterError("o instalador baixado nao confere com o SHA-256 publicado")
        stop_updater_service(logger)
        kill_leftover_processes(logger)
        process = start_detached(installer_command(package, install_log))
        logger.info("Instalador %s iniciado silenciosamente (PID %s).", package.name, process.pid)
        outcome, code = supervise_installer(process, lambda: False)
        if outcome == "timeout":
            kill_process_tree(process.pid)
            for pid in running_setup_processes():
                kill_process_tree(pid)
            logger.error("Instalador excedeu %s minutos e foi encerrado.", INSTALL_TIMEOUT_SECONDS // 60)
            result.update(outcome="timeout")
            exit_code = RUNNER_TIMEOUT_EXIT_CODE
        else:
            logger.info("Instalador terminou com codigo %s.", code)
            result.update(outcome="exited", exit_code=code)
            exit_code = int(code or 0)
    except Exception as exc:
        logger.exception("Falha no executor da instalacao.")
        result.update(outcome="error", message=str(exc)[:500])
    finally:
        result["finished_at"] = time.time()
        try:
            atomic_json(INSTALL_RESULT_PATH, result)
        except OSError:
            logger.exception("Nao foi possivel gravar %s.", INSTALL_RESULT_PATH)
        try:
            ensure_service_running(logger)
        finally:
            lock.__exit__()
    return exit_code


def report_install_failure(
    api: ApiClient,
    state: dict[str, Any],
    logger: logging.Logger,
    outcome: str,
    exit_code: int | None = None,
    installer_log: Path | None = None,
    detail: str = "",
) -> int:
    """Record a failed attempt of the pending package and tell the dashboard."""
    version = str(state.get("pending_version", ""))
    started_at = float(state.get("pending_started_at", 0) or 0)
    attempts, delay, exhausted = register_install_failure(state, time.time())
    install_log = collect_install_log(version, attempts, outcome, exit_code, started_at, installer_log, detail)
    try:
        LOG_DIR.mkdir(parents=True, exist_ok=True)
        INSTALL_FAILURE_LOG_PATH.write_text(install_log, encoding="utf-8")
    except OSError:
        logger.warning("Nao foi possivel gravar %s.", INSTALL_FAILURE_LOG_PATH)

    reason = describe_outcome(outcome, exit_code, detail)
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


def read_recovery_note() -> str | None:
    """Note left by the watchdog after it repaired this computer."""
    if not RECOVERY_MARKER_PATH.is_file():
        return None
    try:
        data = load_json(RECOVERY_MARKER_PATH)
    except UpdaterError:
        RECOVERY_MARKER_PATH.unlink(missing_ok=True)
        return None
    note = f"{data.get('at', '')} {data.get('note', '')}".strip()
    return note[:255] or None


def cancel_running_installation(state: dict[str, Any], logger: logging.Logger) -> bool:
    """Kill any running unified installer and forget the pending installation and failures."""
    setup_pids = running_setup_processes()
    for pid in setup_pids:
        kill_process_tree(pid)
    had_pending = bool(state.get("pending_version"))
    clear_pending_install(state)
    clear_install_failures(state)
    if setup_pids or had_pending:
        logger.warning("Instalacao em andamento cancelada pelo dashboard (PIDs %s).", setup_pids)
    return bool(setup_pids or had_pending)


def check_once(
    logger: logging.Logger,
    manual: bool = False,
    stop_requested: Callable[[], bool] = lambda: False,
    cancel_requested: Callable[[], bool] = lambda: False,
    command: str | None = None,
    command_seq: int | None = None,
) -> int:
    try:
        config = validate_config(load_json(CONFIG_PATH))
    except (UpdaterError, ValueError, TypeError) as exc:
        logger.error("Configuracao do servico invalida; nova tentativa em 5 minutos: %s", exc)
        return RESULT_RETRY
    state = get_state()
    installed = str(state.get("installed_version", "0.0.0"))
    if not VERSION_RE.fullmatch(installed):
        # A damaged state file must not stop the service: reinstalling the
        # published package rewrites installed_version through --configure.
        logger.warning("installed_version invalida no estado local (%r); considerando 0.0.0.", installed)
        installed = "0.0.0"
    api = ApiClient(config)
    api.command_seq = command_seq
    api.recovery_note = read_recovery_note()
    # "Verificar agora" and "Reinstalar" abandon whatever was in progress and start over.
    restart_requested = command in (COMMAND_CHECK, COMMAND_REINSTALL)
    try:
        cancelled_note = ""
        if restart_requested:
            if cancel_running_installation(state, logger):
                cancelled_note = " A instalacao que estava em andamento foi cancelada."
            atomic_json(STATE_PATH, state)
        elif manual and state.get("failed_attempts"):
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
                if setup_pids and time.time() - pending_started_at < INSTALL_SUPERVISION_SECONDS:
                    api.report(
                        "installing", installed, pending_version,
                        f"Instalacao de {pending_version} em andamento; aguardando o instalador terminar.",
                    )
                    logger.info("Instalador de %s ainda em execucao (PIDs %s).", pending_version, setup_pids)
                    return RESULT_RETRY
                for pid in setup_pids:
                    kill_process_tree(pid)
                if setup_pids:
                    return report_install_failure(api, state, logger, "timeout")
                result = read_install_result(pending_version, str(state.get("pending_sha256", "")))
                if result is None:
                    return report_install_failure(api, state, logger, "missing")
                outcome, exit_code, detail = runner_outcome(result, None)
                if outcome == "exited" and exit_code == 0:
                    outcome = "incomplete"
                installer_log = Path(str(result.get("installer_log", ""))) if result.get("installer_log") else None
                return report_install_failure(api, state, logger, outcome, exit_code, installer_log, detail)

        start_message = {
            COMMAND_CHECK: "Verificacao reiniciada pelo dashboard.",
            COMMAND_REINSTALL: "Reinstalacao solicitada pelo dashboard.",
        }.get(command or "", "Consultando a versao publicada.")
        api.report("checking", installed, message=start_message + cancelled_note)
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
            return RESULT_OK
        available = str(release["version"])
        interval = max(300, min(86400, int(release.get("check_interval_seconds", config["check_interval_seconds"]))))
        state["last_check"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
        state["last_available_version"] = available
        state["check_interval_seconds"] = interval
        action = decide_action(installed, available, bool(release["allow_downgrade"]))
        reinstall = command == COMMAND_REINSTALL and action == ACTION_CURRENT
        if reinstall:
            # Repair: install the same published package again.
            action = ACTION_UPGRADE
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
                if command == COMMAND_REINSTALL:
                    message = "Reinstalacao nao aplicada: " + message
            state["last_result"] = "current"
            state["last_error"] = ""
            atomic_json(STATE_PATH, state)
            api.report(status, installed, available, message)
            logger.info("Versao atual %s; publicada %s (%s). Nenhuma acao necessaria.", installed, available, action)
            return RESULT_OK

        blocked = install_block_reason(state, release, time.time())
        if blocked is not None:
            status, message = blocked
            state["last_result"] = status
            state["last_error"] = message
            atomic_json(STATE_PATH, state)
            api.report(status, installed, available, message)
            logger.warning(message)
            return RESULT_OK if status == STATUS_INSTALL_FAILED else RESULT_RETRY

        if cancel_requested():
            raise OperationCancelled("Verificacao cancelada pelo dashboard.")
        is_rollback = action == ACTION_DOWNGRADE
        attempt = attempt_number(state, available, release["sha256"])
        attempt_label = f"tentativa {attempt} de {TOTAL_INSTALL_ATTEMPTS}"
        if reinstall:
            download_message = f"Reinstalacao: baixando a versao {available} ({attempt_label})."
            install_message = f"Reinstalacao da versao {available} iniciada ({attempt_label})."
        elif is_rollback:
            download_message = f"Rollback: baixando a versao {available} para substituir a {installed} ({attempt_label})."
            install_message = f"Rollback: voltando de {installed} para {available}; instalacao silenciosa iniciada ({attempt_label})."
        else:
            download_message = f"Baixando e validando o instalador ({attempt_label})."
            install_message = f"Instalacao silenciosa iniciada ({attempt_label})."
        DOWNLOAD_DIR.mkdir(parents=True, exist_ok=True)
        destination = DOWNLOAD_DIR / f"Ativa-Unified-Agent-Setup-{available}.exe"
        cleanup_downloads(keep=destination)
        api.report("downloading", installed, available, download_message)
        download_with_retries(api, release, destination, logger, cancel_requested)
        if cancel_requested():
            raise OperationCancelled("Verificacao cancelada pelo dashboard.")
        state["last_result"] = "installing"
        state["pending_version"] = available
        state["pending_sha256"] = release["sha256"]
        state["pending_action"] = action
        state["pending_started_at"] = time.time()
        state["last_error"] = ""
        atomic_json(STATE_PATH, state)
        api.report("installing", installed, available, install_message)
        process, installer_log = launch_installer(destination, logger, available, release["sha256"])
        outcome, exit_code = supervise_installer(
            process, stop_requested, timeout_seconds=INSTALL_SUPERVISION_SECONDS, cancel_requested=cancel_requested,
        )
        if outcome == "stopping":
            # Expected: the install runner stops the service before running the
            # setup; the service started at the end reports the result.
            logger.info("Executor da instalacao parando o servico para instalar %s.", available)
            return RESULT_SERVICE_STOPPING
        if outcome == "cancelled":
            kill_process_tree(process.pid)
            for pid in running_setup_processes():
                kill_process_tree(pid)
            refreshed = get_state()
            clear_pending_install(refreshed)
            atomic_json(STATE_PATH, refreshed)
            logger.warning("Instalacao de %s cancelada pelo dashboard; recomecando a verificacao.", available)
            return RESULT_RESTART_NOW

        # The runner finished without stopping the service (e.g. it failed early).
        detail = ""
        if outcome == "exited":
            outcome, exit_code, detail = runner_outcome(read_install_result(available, release["sha256"]), exit_code)
        # Re-read the state: --configure rewrites it when the package is installed.
        refreshed = get_state()
        if refreshed.get("installed_version") == available and outcome == "exited" and exit_code == 0:
            refreshed["last_result"] = "current"
            atomic_json(STATE_PATH, refreshed)
            api.report("updated", available, available, f"Versao {available} instalada com sucesso.")
            logger.info("Instalacao de %s concluida.", available)
            return RESULT_OK
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
        return report_install_failure(api, refreshed, logger, outcome, exit_code, installer_log, detail)
    except OperationCancelled:
        logger.info("Verificacao cancelada pelo dashboard; recomecando.")
        return RESULT_RESTART_NOW
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
        return RESULT_RETRY


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
        # A file icacls cannot process (e.g. locked by antivirus) must not abort
        # the whole installation: the result is logged for diagnostics instead.
        acl_log = []
        for arguments in (
            [str(PRODUCT_DIR), "/reset", "/T", "/C"],
            [str(PRODUCT_DIR), "/inheritance:r", "/grant:r", "*S-1-5-18:(OI)(CI)F", "*S-1-5-32-544:(OI)(CI)F"],
        ):
            completed = subprocess.run(
                ["icacls.exe", *arguments],
                capture_output=True,
                text=True,
                errors="replace",
                creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
            )
            acl_log.append(f"icacls {' '.join(arguments)} -> {completed.returncode}\n{completed.stdout}{completed.stderr}")
        try:
            (LOG_DIR / "configure.log").write_text(
                time.strftime("%Y-%m-%d %H:%M:%S") + "\n" + "\n".join(acl_log), encoding="utf-8"
            )
        except OSError:
            pass
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


def file_sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as stream:
        for block in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(block)
    return digest.hexdigest()


def write_heartbeat() -> None:
    """Proof of life read by the watchdog."""
    try:
        atomic_json(HEARTBEAT_PATH, {
            "at": time.time(),
            # PyInstaller onefile: the service PID known by Windows is the
            # bootloader, i.e. the parent of this Python process.
            "pids": [os.getpid(), os.getppid()],
            "version": UPDATER_VERSION,
        })
    except OSError:
        pass


def heartbeat_age(now: float, service_pid: int | None = None) -> float | None:
    """Seconds since the last heartbeat; None when unknown or written by another process."""
    try:
        heartbeat = load_json(HEARTBEAT_PATH)
        value = float(heartbeat.get("at", 0) or 0)
    except (UpdaterError, TypeError, ValueError):
        return None
    if service_pid is not None and service_pid not in heartbeat.get("pids", []):
        # Services older than 1.5.0 write no heartbeat; a stale file from a
        # previous version must not make the watchdog restart them.
        return None
    return now - value if value > 0 else None


def running_executable() -> Path | None:
    return Path(sys.executable) if getattr(sys, "frozen", False) else None


def promote_known_good(logger: logging.Logger) -> bool:
    """Keep a copy of a service executable that reached the API, used by the watchdog."""
    source = running_executable()
    if os.name != "nt" or source is None or not source.is_file():
        return True
    try:
        WATCHDOG_DIR.mkdir(parents=True, exist_ok=True)
        if WATCHDOG_EXE.is_file() and file_sha256(WATCHDOG_EXE) == file_sha256(source):
            return True
        staged = WATCHDOG_EXE.with_name(WATCHDOG_EXE.name + ".new")
        shutil.copy2(source, staged)
        os.replace(staged, WATCHDOG_EXE)
        logger.info("Copia de seguranca do servico %s disponivel para o vigia.", UPDATER_VERSION)
        return True
    except OSError as exc:
        # The watchdog may be running from that copy; try again on the next check.
        logger.warning("Nao foi possivel atualizar a copia de seguranca do vigia: %s", exc)
        return False


def ensure_watchdog_task(logger: logging.Logger) -> None:
    """Recreate the watchdog scheduled task if someone removed it."""
    if os.name != "nt":
        return
    flags = getattr(subprocess, "CREATE_NO_WINDOW", 0)
    try:
        query = subprocess.run(
            ["schtasks.exe", "/Query", "/TN", WATCHDOG_TASK_NAME], capture_output=True, timeout=60, creationflags=flags
        )
        if query.returncode == 0:
            return
        if not WATCHDOG_EXE.is_file():
            promote_known_good(logger)
        if not WATCHDOG_EXE.is_file():
            return
        created = subprocess.run(
            [
                "schtasks.exe", "/Create", "/F", "/TN", WATCHDOG_TASK_NAME, "/RU", "SYSTEM", "/RL", "HIGHEST",
                "/SC", "MINUTE", "/MO", str(WATCHDOG_INTERVAL_MINUTES), "/TR", f'"{WATCHDOG_EXE}" --watchdog',
            ],
            capture_output=True, timeout=60, creationflags=flags,
        )
        logger.info("Tarefa do vigia recriada (codigo %s).", created.returncode)
    except (OSError, subprocess.SubprocessError) as exc:
        logger.warning("Nao foi possivel verificar a tarefa do vigia: %s", exc)


def schedule_service_restart(logger: logging.Logger) -> None:
    """Start the service again a few seconds after this process stops it."""
    flags = getattr(subprocess, "DETACHED_PROCESS", 0) | getattr(subprocess, "CREATE_NEW_PROCESS_GROUP", 0)
    subprocess.Popen(
        f'cmd.exe /c "ping -n 9 127.0.0.1 >nul & sc.exe start {SERVICE_NAME}"',
        close_fds=True,
        creationflags=flags,
    )
    logger.warning("Reinicio do servico solicitado pelo dashboard.")


WALLPAPER_ERROR_RE = re.compile(r"^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:,\d{3})? (?:ERROR|CRITICAL)\b")


def wallpaper_error_lines(paths: list[Path], max_lines: int = 80, max_bytes: int = 1024 * 1024) -> list[str]:
    """ERROR lines of every user's Wallpaper Client log, oldest first (without tracebacks)."""
    found: list[tuple[str, str]] = []
    for path in paths:
        for line in read_log_tail(path, 100000, max_bytes=max_bytes):
            if WALLPAPER_ERROR_RE.match(line):
                found.append((line[:19], f"{line}  [{path.name}]"))
    found.sort(key=lambda item: item[0])
    return [text for _, text in found[-max_lines:]]


def collect_diagnostics() -> str:
    """Logs and state sent to the dashboard on request ("Enviar logs"). No secrets."""
    now = time.time()
    state = get_state()
    try:
        service = query_service()
    except OSError as exc:
        service = f"erro: {exc}"
    try:
        free_bytes = shutil.disk_usage(PROGRAM_DATA).free
    except OSError:
        free_bytes = -1
    age = heartbeat_age(now)
    lines = [
        f"Ativa Unified Updater {UPDATER_VERSION} - diagnostico",
        f"Data: {time.strftime('%Y-%m-%d %H:%M:%S')}",
        f"Computador: {socket.gethostname()}",
        f"Servico (estado, PID): {service}",
        f"Heartbeat: {'ha ' + str(int(age)) + ' s' if age is not None else 'nenhum'}",
        f"Instaladores em execucao (PIDs): {running_setup_processes()}",
        f"Espaco livre em ProgramData: {free_bytes // (1024 * 1024) if free_bytes >= 0 else '?'} MB",
        f"Copia do vigia: {'presente' if WATCHDOG_EXE.is_file() else 'ausente'}",
        "Estado local: " + json.dumps(state, ensure_ascii=False, sort_keys=True),
    ]
    sections = [
        ("Servico (service.log)", LOG_DIR / "service.log", 200),
        ("Vigia (watchdog.log)", LOG_DIR / "watchdog.log", 60),
        (f"Executor da instalacao ({RUNNER_LOG_NAME})", LOG_DIR / RUNNER_LOG_NAME, 60),
        ("Resultado da ultima instalacao", INSTALL_RESULT_PATH, 20),
        ("Ultima falha de instalacao", INSTALL_FAILURE_LOG_PATH, 80),
        ("Configuracao (configure.log)", LOG_DIR / "configure.log", 30),
    ]
    # Keep the newest execution at the end so it survives the diagnostics
    # tail limit even when the computer has a long installation history.
    for installer_log in reversed(newest_files(LOG_DIR, "installer-*.log")):
        sections.append((f"Instalador ({installer_log.name})", installer_log, 80))
    wallpaper_log = newest_file(WALLPAPER_LOG_DIR, "client-*.log")
    if wallpaper_log:
        sections.append((f"Wallpaper Client ({wallpaper_log.name})", wallpaper_log, 120))
    for title, path, max_lines in sections:
        if path.is_file():
            lines.append("")
            lines.append(f"== {title} ==")
            lines.extend(read_log_tail(path, max_lines))
    # Last on purpose: the diagnostics limit keeps the end of the text. Communication
    # errors (e.g. HTTP 404 while the plugin was being updated) never reach the server,
    # so the Ativa Wallpaper dashboard reads them from here.
    wallpaper_errors = wallpaper_error_lines(newest_files(WALLPAPER_LOG_DIR, "client-*.log"))
    if wallpaper_log or wallpaper_errors:
        lines.append("")
        lines.append("== Wallpaper Client: erros recentes ==")
        lines.extend(wallpaper_errors or ["(nenhum erro registrado nos logs recentes do Wallpaper Client)"])
    text = "\n".join(lines)
    if len(text) > DIAGNOSTICS_MAX_CHARS:
        header = "\n".join(lines[:9])
        text = header + "\n...\n" + text[-(DIAGNOSTICS_MAX_CHARS - len(header) - 5):]
    return text


# --- Watchdog -------------------------------------------------------------

SC_MANAGER_CONNECT = 0x0001
SERVICE_QUERY_STATUS_ACCESS = 0x0004
SC_STATUS_PROCESS_INFO = 0
ERROR_SERVICE_DOES_NOT_EXIST = 1060
SERVICE_STATE_STOPPED = 1
SERVICE_STATE_START_PENDING = 2
SERVICE_STATE_STOP_PENDING = 3
SERVICE_STATE_RUNNING = 4


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


def query_service(name: str = SERVICE_NAME) -> tuple[int, int] | None:
    """(state, pid) of a Windows service, or None when it is not installed."""
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
        service = advapi32.OpenServiceW(manager, name, SERVICE_QUERY_STATUS_ACCESS)
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
            return int(status.dwCurrentState), int(status.dwProcessId)
        finally:
            advapi32.CloseServiceHandle(service)
    finally:
        advapi32.CloseServiceHandle(manager)


def plan_watchdog(
    service: tuple[int, int] | None,
    setup_running: bool,
    install_age: float | None,
    heartbeat_seconds: float | None,
    exe_healthy: Callable[[], bool],
) -> list[str]:
    """Repair actions for the current situation, in execution order.

    install_age is how long an installer has been running (from the pending
    installation or from when the watchdog first saw it).
    """
    actions: list[str] = []
    if setup_running:
        if install_age is None or install_age <= WATCHDOG_INSTALL_LIMIT_SECONDS:
            return []  # A legitimate installation may have stopped the service.
        actions.append("kill_setup")
    if service is not None:
        if service[0] == SERVICE_STATE_RUNNING:
            if heartbeat_seconds is not None and heartbeat_seconds > WATCHDOG_HEARTBEAT_LIMIT_SECONDS:
                actions.append("restart_service")
            return actions
        if service[0] == SERVICE_STATE_START_PENDING:
            return actions
    if not exe_healthy():
        actions.append("restore_exe")
    if service is None:
        actions.append("create_service")
    if service is not None and service[0] == SERVICE_STATE_STOP_PENDING:
        actions.append("restart_service")
    else:
        actions.append("start_service")
    return actions


def setup_running_age(setup_running: bool, state: dict[str, Any], now: float) -> float | None:
    """How long the installer has been running, remembering when the watchdog first saw it."""
    watchdog_state_path = PRODUCT_DIR / "watchdog-state.json"
    try:
        watchdog_state = load_json(watchdog_state_path) if watchdog_state_path.is_file() else {}
    except UpdaterError:
        watchdog_state = {}
    if not setup_running:
        if watchdog_state.pop("setup_first_seen", None) is not None:
            atomic_json(watchdog_state_path, watchdog_state)
        return None
    started_at = float(state.get("pending_started_at", 0) or 0) if state.get("pending_version") else 0.0
    first_seen = float(watchdog_state.get("setup_first_seen", 0) or 0)
    if not first_seen:
        first_seen = now
        watchdog_state["setup_first_seen"] = now
        atomic_json(watchdog_state_path, watchdog_state)
    # The youngest estimate wins: a stale pending installation must not get a
    # freshly started (e.g. manual) installer killed.
    return min(now - first_seen, now - started_at) if started_at else now - first_seen


def service_exe_healthy() -> bool:
    """The service executable exists and starts ("--version" works in every service version)."""
    if not SERVICE_EXE.is_file():
        return False
    try:
        completed = subprocess.run(
            [str(SERVICE_EXE), "--version"],
            capture_output=True, timeout=120, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        return completed.returncode == 0
    except (OSError, subprocess.SubprocessError):
        return False


def run_sc(*arguments: str, timeout: int = 60) -> int:
    try:
        return subprocess.run(
            ["sc.exe", *arguments], capture_output=True, timeout=timeout,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        ).returncode
    except (OSError, subprocess.SubprocessError):
        return -1


def wait_service_state(expected: int, timeout_seconds: float, sleep: Callable[[float], None] = time.sleep) -> bool:
    deadline = time.monotonic() + timeout_seconds
    while time.monotonic() < deadline:
        current = query_service()
        if current is not None and current[0] == expected:
            return True
        sleep(2)
    return False


def restore_service_executable(logger: logging.Logger) -> bool:
    source = running_executable() or WATCHDOG_EXE
    if not source.is_file() or source.resolve() == SERVICE_EXE.resolve():
        logger.error("Copia de seguranca do servico indisponivel para restauracao.")
        return False
    try:
        staged = SERVICE_EXE.with_name(SERVICE_EXE.name + ".restore")
        shutil.copy2(source, staged)
        os.replace(staged, SERVICE_EXE)
        logger.warning("Executavel do servico restaurado a partir da copia do vigia.")
        return True
    except OSError as exc:
        logger.error("Falha ao restaurar o executavel do servico: %s", exc)
        return False


def run_watchdog(logger: logging.Logger) -> int:
    """Keep the updater service alive and repair it after a broken installation."""
    if os.name != "nt":
        return 0
    kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
    kernel32.CreateMutexW.restype = wintypes.HANDLE
    mutex = kernel32.CreateMutexW(None, False, WATCHDOG_MUTEX_NAME)
    if not mutex or ctypes.get_last_error() == 183:
        return 0
    try:
        now = time.time()
        state = get_state()
        setup_pids = running_setup_processes()
        service = query_service()
        actions = plan_watchdog(
            service,
            bool(setup_pids),
            setup_running_age(bool(setup_pids), state, now),
            heartbeat_age(now, service[1] if service else None),
            service_exe_healthy,
        )
        if not actions:
            return 0
        logger.warning("Vigia: servico=%s instaladores=%s acoes=%s", service, setup_pids, actions)
        notes: list[str] = []
        for action in actions:
            if action == "kill_setup":
                for pid in setup_pids:
                    kill_process_tree(pid)
                notes.append("instalador travado encerrado")
            elif action == "restore_exe":
                run_sc("stop", SERVICE_NAME)
                wait_service_state(SERVICE_STATE_STOPPED, 30)
                if restore_service_executable(logger):
                    notes.append("executavel do servico restaurado")
            elif action == "create_service":
                code = subprocess.run(
                    f'sc.exe create {SERVICE_NAME} binPath= "{SERVICE_EXE} --service" start= auto '
                    f'DisplayName= "{SERVICE_DISPLAY_NAME}"',
                    capture_output=True, timeout=60, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
                ).returncode
                run_sc("failure", SERVICE_NAME, "reset=", "86400", "actions=", "restart/60000/restart/60000/restart/60000")
                notes.append(f"servico recriado (codigo {code})")
            elif action == "restart_service":
                run_sc("stop", SERVICE_NAME)
                if not wait_service_state(SERVICE_STATE_STOPPED, 30):
                    current = query_service()
                    if current is not None and current[1]:
                        kill_process_tree(current[1])
                    wait_service_state(SERVICE_STATE_STOPPED, 15)
                run_sc("start", SERVICE_NAME)
                started = wait_service_state(SERVICE_STATE_RUNNING, 30)
                notes.append("servico sem resposta reiniciado" + ("" if started else " (nao iniciou)"))
            elif action == "start_service":
                run_sc("start", SERVICE_NAME)
                if wait_service_state(SERVICE_STATE_RUNNING, 30):
                    notes.append("servico parado foi iniciado")
                elif "restore_exe" not in actions and restore_service_executable(logger):
                    run_sc("start", SERVICE_NAME)
                    started = wait_service_state(SERVICE_STATE_RUNNING, 30)
                    notes.append("servico nao iniciava; executavel restaurado" + ("" if started else " (ainda sem iniciar)"))
                else:
                    notes.append("servico nao conseguiu iniciar")
        if notes:
            note = "Vigia: " + "; ".join(notes)
            logger.warning(note)
            atomic_json(RECOVERY_MARKER_PATH, {"at": time.strftime("%Y-%m-%d %H:%M"), "note": note})
        return 0
    finally:
        kernel32.CloseHandle(mutex)


class ServiceRuntime:
    """Main loop plus a thread that polls dashboard commands every 15 seconds.

    Commands are received even while a check downloads or supervises an
    installer: "Verificar agora" and "Reinstalar" cancel the current work and
    start over immediately.
    """

    def __init__(self):
        self.stop_event = threading.Event()
        self.cancel_event = threading.Event()
        self.wake_event = threading.Event()
        self._lock = threading.Lock()
        self._pending_command: tuple[str, int] | None = None
        self.last_command_seq = 0

    def submit_command(self, command: str, seq: int) -> None:
        with self._lock:
            self._pending_command = (command, seq)
        self.cancel_event.set()
        self.wake_event.set()

    def take_command(self) -> tuple[str, int] | None:
        with self._lock:
            command, self._pending_command = self._pending_command, None
        return command

    def handle_command_response(self, response: dict[str, Any], api: ApiClient, logger: logging.Logger) -> None:
        seq = int(response.get("seq", 0))
        if not response.get("pending") or seq <= self.last_command_seq:
            return
        self.last_command_seq = seq
        api.command_seq = seq
        command = response.get("command") or COMMAND_CHECK
        logger.info("Comando %s recebido do dashboard (#%s).", command, seq)
        if command == COMMAND_SEND_LOGS:
            api.send_diagnostics(collect_diagnostics())
        elif command == COMMAND_RESTART_SERVICE:
            api.send_diagnostics("Reinicio do servico solicitado pelo dashboard.\n\n" + collect_diagnostics())
            schedule_service_restart(logger)
            self.cancel_event.set()
            self.stop_event.set()
            self.wake_event.set()
        else:
            self.submit_command(command, seq)

    def poll_commands(self, logger: logging.Logger) -> None:
        while not self.stop_event.is_set():
            write_heartbeat()
            try:
                api = ApiClient(validate_config(load_json(CONFIG_PATH)))
                api.command_seq = self.last_command_seq
                self.handle_command_response(api.commands(), api, logger)
            except Exception as exc:
                logger.warning("Nao foi possivel consultar comandos agora: %s", exc)
            self.stop_event.wait(COMMAND_POLL_SECONDS)

    def run(self, logger: logging.Logger, start_poller: bool = True) -> None:
        logger.info("Servico %s iniciado; primeira consulta imediata.", UPDATER_VERSION)
        cleanup_replaced_binaries()
        write_heartbeat()
        ensure_watchdog_task(logger)
        if start_poller:
            threading.Thread(target=self.poll_commands, args=(logger,), name="commands", daemon=True).start()
        next_full_check = 0.0
        promoted = False
        while not self.stop_event.is_set():
            command = self.take_command()
            if command is None and time.monotonic() < next_full_check:
                self.wake_event.wait(max(1.0, min(60.0, next_full_check - time.monotonic())))
                self.wake_event.clear()
                continue
            self.cancel_event.clear()
            try:
                result = check_once(
                    logger,
                    manual=command is not None,
                    stop_requested=self.stop_event.is_set,
                    cancel_requested=self.cancel_event.is_set,
                    command=command[0] if command else None,
                    command_seq=self.last_command_seq,
                )
            except Exception:
                # Never let an unexpected error end the service: Windows
                # only restarts it automatically after a crash, not after
                # a clean stop, and the machine would stop updating.
                logger.exception("Falha inesperada na verificacao; nova tentativa em 300 segundos.")
                result = RESULT_RETRY
            if self.stop_event.is_set():
                break
            if result == RESULT_OK and not promoted:
                promoted = promote_known_good(logger)
            if result == RESULT_RESTART_NOW:
                next_full_check = 0.0
                continue
            delay = next_check_delay(result, get_state(), time.time())
            next_full_check = time.monotonic() + delay
            if result != RESULT_OK:
                logger.warning("Nova verificacao completa agendada em %s segundos.", delay)


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
    parser.add_argument(
        "--watchdog", action="store_true",
        help="Vigia: religa o servico, encerra instalador travado e restaura o executavel (tarefa agendada)",
    )
    parser.add_argument(
        "--install-package", type=Path,
        help="Executor: para o servico, instala o pacote em silencio e religa o servico (uso interno do servico)",
    )
    parser.add_argument("--package-version", default="")
    parser.add_argument("--package-sha256", default="")
    parser.add_argument("--installer-log", type=Path)
    parser.add_argument("--config", type=Path)
    parser.add_argument("--installed-version", default="")
    parser.add_argument("--debug", action="store_true")
    parser.add_argument("--version", action="store_true")
    args = parser.parse_args()
    if args.version:
        print(UPDATER_VERSION)
        return 0
    if args.install_package is not None:
        version_tuple(args.package_version)
        if not re.fullmatch(r"[a-fA-F0-9]{64}", args.package_sha256):
            parser.error("--install-package exige --package-sha256 valido")
        installer_log = args.installer_log or LOG_DIR / f"installer-{int(time.time())}.log"
        return run_install_package(
            configure_logging(args.debug, RUNNER_LOG_NAME),
            args.install_package, args.package_version, args.package_sha256, installer_log,
        )
    if args.watchdog:
        return run_watchdog(configure_logging(args.debug, "watchdog.log"))
    if args.configure:
        if args.config is None or not args.installed_version:
            parser.error("--configure exige --config e --installed-version")
        return configure_service(args.config, args.installed_version)
    if args.start_wallpaper_clients:
        return start_wallpaper_clients(configure_logging(args.debug))
    if args.run_once:
        with SingleInstance():
            return check_once(configure_logging(args.debug), manual=True, command=COMMAND_CHECK)
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
