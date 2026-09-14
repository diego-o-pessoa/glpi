from __future__ import annotations

import argparse
import ctypes
from ctypes import wintypes
import hashlib
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
from typing import Any
from urllib.error import HTTPError, URLError
from urllib.parse import urlsplit
from urllib.request import Request, build_opener, HTTPRedirectHandler, HTTPSHandler


SERVICE_NAME = "AtivaUnifiedUpdater"
SERVICE_DISPLAY_NAME = "Ativa Unified Updater"
UPDATER_VERSION = "1.0.0"
DEFAULT_INTERVAL = 3600
MAX_INSTALLER_BYTES = 2 * 1024 * 1024 * 1024
VERSION_RE = re.compile(r"^\d{1,5}\.\d{1,5}\.\d{1,5}$")

PROGRAM_DATA = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData"))
PRODUCT_DIR = PROGRAM_DATA / "AtivaLocacao" / "UnifiedUpdater"
CONFIG_PATH = PRODUCT_DIR / "service-config.json"
STATE_PATH = PRODUCT_DIR / "state.json"
DOWNLOAD_DIR = PRODUCT_DIR / "downloads"
LOG_DIR = PRODUCT_DIR / "logs"
MUTEX_NAME = r"Global\AtivaUnifiedUpdater"


class UpdaterError(RuntimeError):
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
        return payload

    def report(self, status: str, installed: str, available: str = "", message: str = "") -> None:
        payload = {
            "machine_guid": machine_guid(),
            "hostname": socket.gethostname(),
            "updater_version": UPDATER_VERSION,
            "installed_version": installed,
            "available_version": available,
            "status": status,
            "message": message[:1000],
        }
        data = json.dumps(payload, separators=(",", ":")).encode("utf-8")
        with self._request(self.base_url + "/status", method="POST", data=data, timeout=30) as response:
            response.read(4096)

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


def launch_installer(path: Path, logger: logging.Logger) -> None:
    install_log = LOG_DIR / f"installer-{int(time.time())}.log"
    command = [
        str(path),
        "/VERYSILENT",
        "/SUPPRESSMSGBOXES",
        "/NORESTART",
        "/CLOSEAPPLICATIONS",
        f"/LOG={install_log}",
    ]
    flags = getattr(subprocess, "DETACHED_PROCESS", 0) | getattr(subprocess, "CREATE_NEW_PROCESS_GROUP", 0)
    subprocess.Popen(command, close_fds=True, creationflags=flags)
    logger.info("Instalador %s iniciado silenciosamente.", path.name)


def check_once(logger: logging.Logger) -> int:
    config = validate_config(load_json(CONFIG_PATH))
    state = get_state()
    installed = str(state.get("installed_version", "0.0.0"))
    version_tuple(installed)
    api = ApiClient(config)
    try:
        pending_version = str(state.get("pending_version", ""))
        pending_started_at = float(state.get("pending_started_at", 0) or 0)
        if pending_version and time.time() - pending_started_at < int(config["check_interval_seconds"]):
            api.report(
                "installing", installed, pending_version,
                "Aguardando a conclusao ou a proxima tentativa da instalacao silenciosa.",
            )
            logger.info("A instalacao de %s ainda esta na janela de espera.", pending_version)
            return 0
        api.report("checking", installed, message="Consultando a versao publicada.")
        release = api.latest()
        available = str(release["version"])
        interval = max(300, min(86400, int(release.get("check_interval_seconds", config["check_interval_seconds"]))))
        state["last_check"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
        state["last_available_version"] = available
        state["check_interval_seconds"] = interval
        if version_tuple(available) <= version_tuple(installed):
            state["last_result"] = "current"
            state["last_error"] = ""
            atomic_json(STATE_PATH, state)
            api.report("current", installed, available, "A maquina ja esta na versao publicada.")
            logger.info("Versao atual %s; publicada %s. Nenhuma acao necessaria.", installed, available)
            return 0

        DOWNLOAD_DIR.mkdir(parents=True, exist_ok=True)
        destination = DOWNLOAD_DIR / f"Ativa-Wallpaper-Client-Setup-{available}.exe"
        api.report("downloading", installed, available, "Baixando e validando o instalador.")
        api.download(release, destination)
        state["last_result"] = "installing"
        state["pending_version"] = available
        state["pending_started_at"] = time.time()
        state["last_error"] = ""
        atomic_json(STATE_PATH, state)
        api.report("installing", installed, available, "Instalacao silenciosa iniciada.")
        launch_installer(destination, logger)
        return 10
    except Exception as exc:
        state["last_result"] = "error"
        state["last_error"] = str(exc)[:1000]
        state["last_check"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
        atomic_json(STATE_PATH, state)
        try:
            api.report("error", installed, str(state.get("last_available_version", "")), str(exc))
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
        subprocess.run(
            [
                "icacls.exe", str(PRODUCT_DIR), "/inheritance:r",
                "/grant:r", "*S-1-5-18:(OI)(CI)F", "*S-1-5-32-544:(OI)(CI)F",
                "/T", "/C",
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
    previous.pop("pending_version", None)
    previous.pop("pending_started_at", None)
    atomic_json(STATE_PATH, previous)
    return 0


class ServiceRuntime:
    def __init__(self):
        self.stop_event = threading.Event()

    def run(self, logger: logging.Logger) -> None:
        while not self.stop_event.is_set():
            result = check_once(logger)
            if result == 10:
                self.stop_event.set()
                break
            interval = int(get_state().get("check_interval_seconds", DEFAULT_INTERVAL))
            self.stop_event.wait(max(300, min(86400, interval)))


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
    if args.run_once:
        with SingleInstance():
            return check_once(configure_logging(args.debug))
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
