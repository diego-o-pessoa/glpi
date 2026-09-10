"""Ativa Wallpaper Client for Windows 10/11.

The installed process runs in the interactive user's session.  The GLPI
Inventory deployment process only calls ``--install`` as SYSTEM/admin.
"""

from __future__ import annotations

import argparse
import ctypes
import hashlib
import hmac
import ipaddress
import json
import logging
from logging.handlers import RotatingFileHandler
import os
from pathlib import Path
import platform
import random
import shutil
import socket
import ssl
import subprocess
import sys
import time
from typing import Any, Callable
from urllib import error, parse, request

if os.name == "nt":
    import winreg
else:  # pragma: no cover - imported only to make unit tests platform-neutral
    winreg = None  # type: ignore[assignment]


CLIENT_VERSION = "1.1.1"
SERVER_HOSTNAME = "chamados.ativalocacao.com.br"
PRODUCT_DIR = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData")) / "AtivaLocacao" / "Wallpaper"
EXECUTABLE_NAME = "AtivaWallpaperClient.exe"
RUN_KEY = r"SOFTWARE\Microsoft\Windows\CurrentVersion\Run"
RUN_VALUE = "AtivaWallpaperClient"
PRODUCT_KEY = r"SOFTWARE\AtivaLocacao\Wallpaper"
STOP_EVENT_NAME = r"Global\AtivaWallpaperClientStop"
MAX_JSON_BYTES = 1024 * 1024
MAX_WALLPAPER_BYTES = 100 * 1024 * 1024
SPI_SETDESKWALLPAPER = 0x0014
SPI_GETDESKWALLPAPER = 0x0073
SPIF_UPDATEINIFILE = 0x01
SPIF_SENDCHANGE = 0x02
ERROR_ALREADY_EXISTS = 183


if os.name == "nt":
    from ctypes import wintypes

    _kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
    _kernel32.CreateMutexW.argtypes = [wintypes.LPVOID, wintypes.BOOL, wintypes.LPCWSTR]
    _kernel32.CreateMutexW.restype = wintypes.HANDLE
    _kernel32.CreateEventW.argtypes = [wintypes.LPVOID, wintypes.BOOL, wintypes.BOOL, wintypes.LPCWSTR]
    _kernel32.CreateEventW.restype = wintypes.HANDLE
    _kernel32.OpenEventW.argtypes = [wintypes.DWORD, wintypes.BOOL, wintypes.LPCWSTR]
    _kernel32.OpenEventW.restype = wintypes.HANDLE
    _kernel32.SetEvent.argtypes = [wintypes.HANDLE]
    _kernel32.SetEvent.restype = wintypes.BOOL
    _kernel32.WaitForSingleObject.argtypes = [wintypes.HANDLE, wintypes.DWORD]
    _kernel32.WaitForSingleObject.restype = wintypes.DWORD
    _kernel32.CloseHandle.argtypes = [wintypes.HANDLE]
    _kernel32.CloseHandle.restype = wintypes.BOOL
    _kernel32.MoveFileExW.argtypes = [wintypes.LPCWSTR, wintypes.LPCWSTR, wintypes.DWORD]
    _kernel32.MoveFileExW.restype = wintypes.BOOL
else:
    _kernel32 = None


class ClientError(RuntimeError):
    def __init__(self, code: str, message: str, *, retriable: bool = True) -> None:
        super().__init__(message)
        self.code = code
        self.retriable = retriable


class CloseAfterEmitRotatingFileHandler(RotatingFileHandler):
    """Avoid keeping client log files locked between polling cycles on Windows."""

    def emit(self, record: logging.LogRecord) -> None:
        try:
            super().emit(record)
        finally:
            self.close()


def normalize_server_url(value: str) -> str:
    value = value.strip().rstrip("/")
    parsed = parse.urlparse(value)
    if parsed.scheme.lower() != "https" or not parsed.hostname or parsed.username or parsed.password or parsed.query or parsed.fragment:
        raise ClientError("INVALID_SERVER_URL", "Server URL must use HTTPS and a hostname", retriable=False)
    try:
        ipaddress.ip_address(parsed.hostname)
    except ValueError:
        pass
    else:
        raise ClientError("INVALID_SERVER_URL", "IP addresses are not accepted; use the certificate hostname", retriable=False)
    if parsed.hostname.lower() != SERVER_HOSTNAME:
        raise ClientError("INVALID_SERVER_URL", f"Server hostname must be {SERVER_HOSTNAME}", retriable=False)
    if not parsed.path.endswith("/plugins/ativawallpaper/api/v1"):
        raise ClientError("INVALID_SERVER_URL", "Server URL does not point to the Ativa Wallpaper v1 API", retriable=False)
    return value


def bounded_interval(value: Any, minimum: int = 60, maximum: int = 86400) -> int:
    try:
        result = int(value)
    except (TypeError, ValueError):
        result = 900
    return max(minimum, min(maximum, result))


def compare_versions(left: str, right: str) -> int:
    def parts(value: str) -> tuple[int, ...]:
        numbers = []
        for item in value.split("."):
            numeric = "".join(character for character in item if character.isdigit())
            numbers.append(int(numeric or 0))
        return tuple(numbers)

    left_parts, right_parts = parts(left), parts(right)
    width = max(len(left_parts), len(right_parts))
    left_parts += (0,) * (width - len(left_parts))
    right_parts += (0,) * (width - len(right_parts))
    return (left_parts > right_parts) - (left_parts < right_parts)


def wallpaper_registry_values(style: str) -> tuple[str, str]:
    values = {
        "center": ("0", "0"),
        "tile": ("0", "1"),
        "stretch": ("2", "0"),
        "fit": ("6", "0"),
        "fill": ("10", "0"),
        "span": ("22", "0"),
    }
    if style not in values:
        raise ClientError("INVALID_STYLE", f"Unsupported wallpaper style: {style}", retriable=False)
    return values[style]


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def atomic_write_json(path: Path, data: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    temporary = path.with_name(path.name + ".tmp")
    with temporary.open("w", encoding="utf-8", newline="\n") as handle:
        json.dump(data, handle, ensure_ascii=False, indent=2, sort_keys=True)
        handle.write("\n")
        handle.flush()
        os.fsync(handle.fileno())
    os.replace(temporary, path)


def load_json(path: Path) -> dict[str, Any]:
    try:
        if path.stat().st_size > MAX_JSON_BYTES:
            raise ClientError("INVALID_CONFIG", f"JSON file is too large: {path.name}", retriable=False)
        with path.open("r", encoding="utf-8") as handle:
            value = json.load(handle)
    except ClientError:
        raise
    except (OSError, json.JSONDecodeError) as exc:
        raise ClientError("INVALID_CONFIG", f"Cannot read {path.name}: {exc}", retriable=False) from exc
    if not isinstance(value, dict):
        raise ClientError("INVALID_CONFIG", f"{path.name} must contain a JSON object", retriable=False)
    return value


def should_download(server: dict[str, Any], local: dict[str, Any]) -> bool:
    if not server.get("enabled"):
        return False
    return bool(
        server.get("force_reapply")
        or local.get("wallpaper_version") != server.get("wallpaper_version")
        or local.get("config_revision") != server.get("config_revision")
        or local.get("sha256") != server.get("sha256")
        or not local.get("wallpaper_path")
    )


def user_key() -> str:
    identity = "\\".join(filter(None, [os.environ.get("USERDOMAIN"), os.environ.get("USERNAME") or os.environ.get("USER")]))
    return hashlib.sha256(identity.encode("utf-8", "replace")).hexdigest()[:16]


def machine_guid() -> str:
    if os.name != "nt" or winreg is None:
        raise ClientError("WINDOWS_REQUIRED", "MachineGuid is available only on Windows", retriable=False)
    try:
        with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, r"SOFTWARE\Microsoft\Cryptography", 0, winreg.KEY_READ | winreg.KEY_WOW64_64KEY) as key:
            value, _ = winreg.QueryValueEx(key, "MachineGuid")
            return str(value)
    except OSError as exc:
        raise ClientError("MACHINE_GUID_ERROR", f"Cannot read MachineGuid: {exc}", retriable=False) from exc


def windows_product_name() -> str:
    if os.name != "nt" or winreg is None:
        return platform.platform()
    try:
        with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, r"SOFTWARE\Microsoft\Windows NT\CurrentVersion", 0, winreg.KEY_READ | winreg.KEY_WOW64_64KEY) as key:
            value, _ = winreg.QueryValueEx(key, "ProductName")
            return str(value)
    except OSError:
        return platform.platform()


def ensure_supported_windows(allow_server: bool = False) -> None:
    if os.name != "nt":
        raise ClientError("WINDOWS_REQUIRED", "Ativa Wallpaper Client supports Windows only", retriable=False)
    if not allow_server and "server" in windows_product_name().lower():
        raise ClientError("WINDOWS_SERVER_EXCLUDED", "Windows Server is excluded by configuration", retriable=False)


def is_admin() -> bool:
    if os.name != "nt":
        return False
    try:
        return bool(ctypes.windll.shell32.IsUserAnAdmin())
    except (AttributeError, OSError):
        return False


def attach_parent_console() -> None:
    """Attach a console for diagnostic switches in a PyInstaller windowed EXE."""
    if os.name != "nt":
        return
    try:
        ctypes.windll.kernel32.AttachConsole(-1)  # ATTACH_PARENT_PROCESS
        if sys.stdout is None:
            sys.stdout = open("CONOUT$", "w", encoding="utf-8", buffering=1)  # type: ignore[assignment]
        if sys.stderr is None:
            sys.stderr = open("CONOUT$", "w", encoding="utf-8", buffering=1)  # type: ignore[assignment]
    except OSError:
        # Running from Explorer has no parent console; file logging remains active.
        pass


class SameOriginRedirectHandler(request.HTTPRedirectHandler):
    def __init__(self, origin: tuple[str, str, int | None]) -> None:
        self.origin = origin

    @staticmethod
    def _origin(url: str) -> tuple[str, str, int | None]:
        parsed = parse.urlparse(url)
        return parsed.scheme.lower(), (parsed.hostname or "").lower(), parsed.port

    def redirect_request(self, req: request.Request, fp: Any, code: int, msg: str, headers: Any, newurl: str) -> request.Request | None:
        if self._origin(newurl) != self.origin:
            raise ClientError("UNSAFE_REDIRECT", "Server attempted a cross-origin redirect", retriable=False)
        return super().redirect_request(req, fp, code, msg, headers, newurl)


class ApiClient:
    def __init__(self, server: str, token: str | None = None, timeout: int = 30) -> None:
        self.server = normalize_server_url(server)
        self.token = token
        self.timeout = timeout
        parsed = parse.urlparse(self.server)
        origin = (parsed.scheme.lower(), (parsed.hostname or "").lower(), parsed.port)
        context = ssl.create_default_context()
        https_handler = request.HTTPSHandler(context=context)
        self.opener = request.build_opener(SameOriginRedirectHandler(origin), https_handler)

    def _headers(self) -> dict[str, str]:
        headers = {"Accept": "application/json", "User-Agent": f"AtivaWallpaperClient/{CLIENT_VERSION}"}
        if self.token:
            headers["Authorization"] = f"Bearer {self.token}"
            # Some Apache CGI/FastCGI configurations hide Authorization from
            # PHP. Keep the standard header and duplicate only the opaque
            # plugin token in a dedicated HTTPS header as a compatibility
            # fallback.
            headers["X-Ativa-Client-Token"] = self.token
        return headers

    def _json_request(self, method: str, url: str, payload: dict[str, Any] | None = None, etag: str | None = None) -> tuple[int, dict[str, Any] | None, str | None]:
        headers = self._headers()
        body = None
        if payload is not None:
            body = json.dumps(payload, ensure_ascii=False, separators=(",", ":")).encode("utf-8")
            headers["Content-Type"] = "application/json"
        if etag:
            headers["If-None-Match"] = f'"{etag}"'
        req = request.Request(url, data=body, headers=headers, method=method)
        try:
            with self.opener.open(req, timeout=self.timeout) as response:
                raw = response.read(MAX_JSON_BYTES + 1)
                if len(raw) > MAX_JSON_BYTES:
                    raise ClientError("RESPONSE_TOO_LARGE", "JSON response exceeds 1 MiB")
                if not str(response.headers.get("Content-Type", "")).lower().startswith("application/json"):
                    raise ClientError("INVALID_CONTENT_TYPE", "Server did not return JSON")
                try:
                    decoded = json.loads(raw.decode("utf-8"))
                except (UnicodeDecodeError, json.JSONDecodeError) as exc:
                    raise ClientError("INVALID_JSON", "Server returned invalid JSON") from exc
                if not isinstance(decoded, dict):
                    raise ClientError("INVALID_JSON", "Server JSON root is not an object")
                return int(response.status), decoded, _clean_etag(response.headers.get("ETag"))
        except error.HTTPError as exc:
            if exc.code == 304:
                return 304, None, _clean_etag(exc.headers.get("ETag")) or etag
            message = f"HTTP {exc.code}"
            try:
                raw = exc.read(8192)
                decoded = json.loads(raw.decode("utf-8"))
                message = str(decoded.get("error", {}).get("message", message))[:300]
            except (OSError, UnicodeDecodeError, json.JSONDecodeError, AttributeError):
                pass
            raise ClientError(f"HTTP_{exc.code}", message, retriable=exc.code >= 500 or exc.code in (408, 429)) from exc
        except ClientError:
            raise
        except (error.URLError, TimeoutError, OSError) as exc:
            raise ClientError("SERVER_UNAVAILABLE", f"Server unavailable: {exc}") from exc

    def register(self, secret: str, identity: dict[str, str]) -> str:
        payload = dict(identity)
        payload["registration_secret"] = secret
        status, data, _ = self._json_request("POST", self.server + "/register", payload)
        token = data.get("client_token") if data else None
        if status != 201 or not isinstance(token, str) or len(token) < 32:
            raise ClientError("REGISTRATION_FAILED", "Server did not return a valid client token")
        return token

    def get_config(self, etag: str | None) -> tuple[dict[str, Any] | None, str | None]:
        status, data, response_etag = self._json_request("GET", self.server + "/config", etag=etag)
        return (None, response_etag) if status == 304 else (data, response_etag)

    def report(self, payload: dict[str, Any]) -> None:
        status, _, _ = self._json_request("POST", self.server + "/status", payload)
        if status != 202:
            raise ClientError("STATUS_REJECTED", f"Unexpected status response: {status}")

    def download(self, url: str, destination: Path, expected_sha256: str, expected_size: int | None = None) -> None:
        if SameOriginRedirectHandler._origin(url) != SameOriginRedirectHandler._origin(self.server):
            raise ClientError("UNSAFE_DOWNLOAD_URL", "Wallpaper download URL has a different origin", retriable=False)
        if not expected_sha256 or len(expected_sha256) != 64:
            raise ClientError("INVALID_SHA256", "Server supplied an invalid SHA-256", retriable=False)
        maximum = MAX_WALLPAPER_BYTES
        if expected_size is not None and (expected_size < 1 or expected_size > maximum):
            raise ClientError("INVALID_FILESIZE", "Server supplied an invalid wallpaper size", retriable=False)

        destination.parent.mkdir(parents=True, exist_ok=True)
        temporary = destination.with_name(destination.name + ".part")
        req = request.Request(url, headers=self._headers(), method="GET")
        digest = hashlib.sha256()
        total = 0
        try:
            with self.opener.open(req, timeout=max(self.timeout, 60)) as response, temporary.open("wb") as handle:
                content_type = str(response.headers.get("Content-Type", "")).split(";", 1)[0].lower()
                if content_type not in ("image/jpeg", "image/png"):
                    raise ClientError("INVALID_CONTENT_TYPE", "Wallpaper response is not a JPG or PNG")
                content_length = response.headers.get("Content-Length")
                if content_length and int(content_length) > maximum:
                    raise ClientError("DOWNLOAD_TOO_LARGE", "Wallpaper exceeds the client safety limit", retriable=False)
                while True:
                    chunk = response.read(1024 * 1024)
                    if not chunk:
                        break
                    total += len(chunk)
                    if total > maximum:
                        raise ClientError("DOWNLOAD_TOO_LARGE", "Wallpaper exceeds the client safety limit", retriable=False)
                    digest.update(chunk)
                    handle.write(chunk)
                handle.flush()
                os.fsync(handle.fileno())
            if expected_size is not None and total != expected_size:
                raise ClientError("DOWNLOAD_INCOMPLETE", f"Expected {expected_size} bytes, received {total}")
            if not hmac.compare_digest(digest.hexdigest(), expected_sha256.lower()):
                raise ClientError("HASH_MISMATCH", "Wallpaper SHA-256 does not match")
            os.replace(temporary, destination)
        except ClientError:
            _safe_unlink(temporary)
            raise
        except (error.HTTPError, error.URLError, TimeoutError, OSError, ValueError) as exc:
            _safe_unlink(temporary)
            raise ClientError("DOWNLOAD_FAILED", f"Wallpaper download failed: {exc}") from exc


def _clean_etag(value: str | None) -> str | None:
    if not value:
        return None
    value = value.strip()
    if value.startswith("W/"):
        value = value[2:]
    return value.strip('"') or None


def _safe_unlink(path: Path) -> None:
    try:
        path.unlink(missing_ok=True)
    except OSError:
        pass


def set_installation_acls(root: Path) -> None:
    """Protect executable/config while allowing users to update runtime data."""
    root_acl = subprocess.run(
        [
            "icacls", str(root), "/inheritance:r", "/grant:r",
            "*S-1-5-18:(OI)(CI)F",       # SYSTEM
            "*S-1-5-32-544:(OI)(CI)F",  # Administrators
            "*S-1-5-32-545:(OI)(CI)RX", # Users
        ],
        check=False,
        capture_output=True,
        creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
    )
    if root_acl.returncode != 0:
        raise ClientError("ACL_FAILED", "Could not protect the installation directory", retriable=False)

    for directory in (root / "data", root / "state", root / "logs"):
        directory.mkdir(parents=True, exist_ok=True)
        completed = subprocess.run(
            ["icacls", str(directory), "/grant:r", "*S-1-5-32-545:(OI)(CI)M"],
            check=False,
            capture_output=True,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        if completed.returncode != 0:
            raise ClientError("ACL_FAILED", f"Could not grant user write access to {directory.name}", retriable=False)


def configure_logging(root: Path, debug: bool) -> logging.Logger:
    log_directory = root / "logs"
    log_directory.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger("AtivaWallpaperClient")
    for existing_handler in logger.handlers[:]:
        existing_handler.close()
        logger.removeHandler(existing_handler)
    logger.setLevel(logging.DEBUG if debug else logging.INFO)
    formatter = logging.Formatter("%(asctime)s %(levelname)s %(message)s", "%Y-%m-%d %H:%M:%S")
    handler = CloseAfterEmitRotatingFileHandler(
        log_directory / f"client-{user_key()}.log",
        maxBytes=5 * 1024 * 1024,
        backupCount=5,
        encoding="utf-8",
        delay=True,
    )
    handler.setFormatter(formatter)
    logger.addHandler(handler)
    if debug:
        console = logging.StreamHandler()
        console.setFormatter(formatter)
        logger.addHandler(console)
    return logger


def apply_wallpaper(path: Path, style: str) -> None:
    if os.name != "nt" or winreg is None:
        raise ClientError("WINDOWS_REQUIRED", "Wallpaper application requires Windows", retriable=False)
    wallpaper_style, tile_wallpaper = wallpaper_registry_values(style)
    try:
        with winreg.CreateKeyEx(winreg.HKEY_CURRENT_USER, r"Control Panel\Desktop", 0, winreg.KEY_SET_VALUE) as key:
            winreg.SetValueEx(key, "Wallpaper", 0, winreg.REG_SZ, str(path))
            winreg.SetValueEx(key, "WallpaperStyle", 0, winreg.REG_SZ, wallpaper_style)
            winreg.SetValueEx(key, "TileWallpaper", 0, winreg.REG_SZ, tile_wallpaper)
        result = ctypes.windll.user32.SystemParametersInfoW(
            SPI_SETDESKWALLPAPER,
            0,
            str(path),
            SPIF_UPDATEINIFILE | SPIF_SENDCHANGE,
        )
        if not result:
            raise ctypes.WinError()
    except OSError as exc:
        raise ClientError("WALLPAPER_APPLY_FAILED", f"SystemParametersInfoW failed: {exc}") from exc


def wallpaper_is_current(path: Path, style: str) -> bool:
    """Check the wallpaper actually displayed by the interactive Windows user."""
    if os.name != "nt" or winreg is None:
        return False
    buffer = ctypes.create_unicode_buffer(32768)
    try:
        result = ctypes.windll.user32.SystemParametersInfoW(
            SPI_GETDESKWALLPAPER,
            len(buffer),
            buffer,
            0,
        )
        if not result or not buffer.value:
            return False
        expected_path = os.path.normcase(os.path.normpath(str(path)))
        current_path = os.path.normcase(os.path.normpath(buffer.value))
        if expected_path != current_path:
            return False

        expected_style, expected_tile = wallpaper_registry_values(style)
        with winreg.OpenKey(winreg.HKEY_CURRENT_USER, r"Control Panel\Desktop", 0, winreg.KEY_READ) as key:
            current_style, _ = winreg.QueryValueEx(key, "WallpaperStyle")
            current_tile, _ = winreg.QueryValueEx(key, "TileWallpaper")
        return str(current_style) == expected_style and str(current_tile) == expected_tile
    except OSError:
        return False


def manage_lock_policy(lock_change: bool, wallpaper_path: Path | None, style: str, state: dict[str, Any]) -> None:
    if os.name != "nt" or winreg is None:
        return
    owned: dict[str, dict[str, Any]] = state.setdefault("owned_policy_values", {})
    targets: dict[str, tuple[str, str | int, int]] = {}
    try:
        if lock_change and wallpaper_path is not None:
            style_value, _ = wallpaper_registry_values(style)
            targets = {
                "policy_wallpaper": (r"Software\Microsoft\Windows\CurrentVersion\Policies\System", str(wallpaper_path), winreg.REG_SZ),
                "policy_style": (r"Software\Microsoft\Windows\CurrentVersion\Policies\System", style_value, winreg.REG_SZ),
                "no_change": (r"Software\Microsoft\Windows\CurrentVersion\Policies\ActiveDesktop", 1, winreg.REG_DWORD),
            }

            # Treat the three values as one policy unit. If any value belongs
            # to an external GPO, leave that policy untouched and rely on the
            # periodic compliance check instead.
            for marker, (key_path, _value, _value_type) in targets.items():
                value_name = {"policy_wallpaper": "Wallpaper", "policy_style": "WallpaperStyle", "no_change": "NoChangingWallPaper"}[marker]
                current_exists, current_value = _read_registry_value(key_path, value_name)
                if current_exists and (marker not in owned or current_value != owned[marker].get("value")):
                    _remove_owned_policy_values(owned)
                    state["policy_enforced"] = False
                    state["policy_error"] = "Uma politica externa ja controla o wallpaper deste usuario."
                    return

        for marker, (key_path, value, value_type) in targets.items():
            value_name = {"policy_wallpaper": "Wallpaper", "policy_style": "WallpaperStyle", "no_change": "NoChangingWallPaper"}[marker]
            current_exists, current_value = _read_registry_value(key_path, value_name)
            if marker in owned:
                # Update only values that are still equal to what this client last wrote.
                if current_exists and current_value != owned[marker].get("value"):
                    owned.pop(marker, None)
                    continue
            elif current_exists:
                # Never overwrite a GPO or policy created by another administrator.
                continue
            with winreg.CreateKeyEx(winreg.HKEY_CURRENT_USER, key_path, 0, winreg.KEY_SET_VALUE) as key:
                winreg.SetValueEx(key, value_name, 0, value_type, value)
            owned[marker] = {"key": key_path, "name": value_name, "value": value}

        if not lock_change:
            _remove_owned_policy_values(owned)
        state["policy_enforced"] = bool(lock_change and len(owned) == len(targets))
        state.pop("policy_error", None)
    except OSError as exc:
        # Policy keys can be ACL-protected by Windows or a domain GPO. The
        # wallpaper application itself must still succeed; periodic polling
        # provides the fallback enforcement in that case.
        state["policy_enforced"] = False
        state["policy_error"] = f"Nao foi possivel gravar a politica do Windows: {exc}"


def _remove_owned_policy_values(owned: dict[str, dict[str, Any]]) -> None:
    assert winreg is not None
    for marker, item in list(owned.items()):
        current_exists, current_value = _read_registry_value(str(item["key"]), str(item["name"]))
        if not current_exists or current_value != item.get("value"):
            owned.pop(marker, None)
            continue
        if current_exists:
            try:
                with winreg.OpenKey(winreg.HKEY_CURRENT_USER, str(item["key"]), 0, winreg.KEY_SET_VALUE) as key:
                    winreg.DeleteValue(key, str(item["name"]))
                owned.pop(marker, None)
            except FileNotFoundError:
                owned.pop(marker, None)


def _read_registry_value(key_path: str, value_name: str) -> tuple[bool, Any]:
    assert winreg is not None
    try:
        with winreg.OpenKey(winreg.HKEY_CURRENT_USER, key_path, 0, winreg.KEY_READ) as key:
            value, _ = winreg.QueryValueEx(key, value_name)
            return True, value
    except FileNotFoundError:
        return False, None


class WallpaperClient:
    def __init__(
        self,
        root: Path = PRODUCT_DIR,
        *,
        debug: bool = False,
        api_factory: Callable[[str, str], ApiClient] = lambda server, token: ApiClient(server, token),
        apply_function: Callable[[Path, str], None] = apply_wallpaper,
        policy_function: Callable[[bool, Path | None, str, dict[str, Any]], None] = manage_lock_policy,
        current_function: Callable[[Path, str], bool] = wallpaper_is_current,
    ) -> None:
        self.root = root
        self.data_dir = root / "data"
        self.state_path = root / "state" / f"state-{user_key()}.json"
        self.config_path = root / "client.json"
        self.logger = configure_logging(root, debug)
        self.api_factory = api_factory
        self.apply_function = apply_function
        self.policy_function = policy_function
        self.current_function = current_function

    def identity(self) -> dict[str, str]:
        return {
            "hostname": socket.gethostname(),
            "machine_guid": machine_guid(),
            "username": os.environ.get("USERNAME") or os.environ.get("USER") or "",
            "client_version": CLIENT_VERSION,
            "os_version": windows_product_name(),
        }

    def load_config(self) -> dict[str, Any]:
        config = load_json(self.config_path)
        if config.get("verify_tls") is False:
            raise ClientError("TLS_VERIFICATION_DISABLED", "TLS verification cannot be disabled", retriable=False)
        config["server"] = normalize_server_url(str(config.get("server", "")))
        token = config.get("client_token")
        if not isinstance(token, str) or len(token) < 32:
            raise ClientError("MISSING_CLIENT_TOKEN", "Client is not registered; redeploy the bootstrap", retriable=False)
        return config

    def load_state(self) -> dict[str, Any]:
        if not self.state_path.exists():
            return {}
        return load_json(self.state_path)

    def apply_and_enforce(
        self,
        path: Path,
        style: str,
        lock_change: bool,
        state: dict[str, Any],
        *,
        force: bool = False,
    ) -> bool:
        changed = force or not self.current_function(path, style)
        if changed:
            # A policy created by this client would also block its own
            # SystemParametersInfo call. Temporarily release only the values
            # recorded as ours, apply, and restore the lock immediately.
            self.policy_function(False, None, style, state)
            self.apply_function(path, style)
        self.policy_function(lock_change, path, style, state)
        if state.get("policy_error"):
            self.logger.warning("Wallpaper policy fallback: %s", state["policy_error"])
        return changed

    def enforce_cached_wallpaper(self, state: dict[str, Any]) -> bool:
        if not state.get("distribution_enabled", bool(state.get("wallpaper_path"))):
            return False
        path_value = state.get("wallpaper_path")
        expected_hash = str(state.get("sha256", "")).lower()
        if not path_value or len(expected_hash) != 64:
            return False
        path = Path(str(path_value))
        if not path.is_file() or sha256_file(path) != expected_hash:
            # Force the next request to return the complete configuration so
            # the missing/corrupt cache can be downloaded again.
            state.pop("config_etag", None)
            return False
        changed = self.apply_and_enforce(
            path,
            str(state.get("style", "fill")),
            bool(state.get("lock_change")),
            state,
        )
        if changed:
            state["last_apply"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
            state["status_pending"] = True
            self.logger.warning("Wallpaper drift detected and corrected")
        return changed

    @staticmethod
    def report_success(api: ApiClient, identity: dict[str, str], state: dict[str, Any]) -> None:
        api.report({
            **identity,
            "wallpaper_version": state["wallpaper_version"],
            "wallpaper_sha256": state["sha256"],
            "rollout_id": state.get("rollout_id", ""),
            "status": "success",
        })

    def sync_once(self) -> tuple[int, int]:
        config = self.load_config()
        state = self.load_state()
        identity = self.identity()
        api = self.api_factory(config["server"], config["client_token"])
        self.logger.info("Checking server configuration")
        server, response_etag = api.get_config(state.get("config_etag"))
        if server is None:
            self.logger.info("Configuration unchanged (HTTP 304)")
            self.enforce_cached_wallpaper(state)
            if state.get("status_pending") and state.get("wallpaper_version") and state.get("sha256"):
                atomic_write_json(self.state_path, state)
                self.report_success(api, identity, state)
                state["status_pending"] = False
            atomic_write_json(self.state_path, state)
            return bounded_interval(state.get("poll_interval_seconds", 900)), bounded_interval(state.get("poll_jitter_seconds", 120), 0, 3600)

        interval = bounded_interval(server.get("poll_interval_seconds"))
        jitter = bounded_interval(server.get("poll_jitter_seconds", 120), 0, 3600)
        minimum_client = str(server.get("minimum_client_version", "1.0.0"))
        if compare_versions(CLIENT_VERSION, minimum_client) < 0:
            self.logger.warning("Client %s is below minimum server version %s; schedule a GLPI Inventory upgrade", CLIENT_VERSION, minimum_client)
        state["poll_interval_seconds"] = interval
        state["poll_jitter_seconds"] = jitter

        if not server.get("enabled"):
            self.policy_function(False, None, "fill", state)
            state["distribution_enabled"] = False
            state["lock_change"] = False
            state["config_revision"] = server.get("config_revision", "")
            state["rollout_id"] = server.get("rollout_id", "")
            state["config_etag"] = response_etag
            atomic_write_json(self.state_path, state)
            self.logger.info("Distribution is disabled; keeping current wallpaper")
            return interval, jitter

        required = ("wallpaper_version", "download_url", "sha256", "style", "mime_type", "filesize")
        if any(key not in server for key in required):
            raise ClientError("INVALID_SERVER_CONFIG", "Server configuration is missing wallpaper fields")
        style = str(server["style"])
        wallpaper_registry_values(style)
        expected_hash = str(server["sha256"]).lower()
        requested_apply = should_download(server, state)
        self.data_dir.mkdir(parents=True, exist_ok=True)
        extension = ".png" if server["mime_type"] == "image/png" else ".jpg"
        safe_version = "".join(character if character.isalnum() or character in ".-_" else "_" for character in str(server["wallpaper_version"]))[:64]
        destination = self.data_dir / f"wallpaper-{safe_version}{extension}"
        previous_path = Path(str(state.get("wallpaper_path", ""))) if state.get("wallpaper_path") else None
        cached_path: Path | None = None
        for candidate in (destination, previous_path):
            if candidate is not None and candidate.is_file() and sha256_file(candidate) == expected_hash:
                cached_path = candidate
                break

        downloaded = False
        if cached_path is None:
            self.logger.info("Downloading wallpaper version %s", server["wallpaper_version"])
            staged = self.data_dir / "wallpaper.download"
            api.download(str(server["download_url"]), staged, expected_hash, int(server["filesize"]))
            self.logger.info("SHA256 verified")
            os.replace(staged, destination)
            cached_path = destination
            downloaded = True

        previous_style = str(state.get("style", "fill"))
        previous_lock = bool(state.get("lock_change"))
        try:
            applied = self.apply_and_enforce(
                cached_path,
                style,
                bool(server.get("lock_change")),
                state,
                force=requested_apply,
            )
        except Exception as exc:
            if downloaded:
                _safe_unlink(destination)
            if previous_path is not None and previous_path.is_file() and previous_path != cached_path:
                try:
                    self.apply_function(previous_path, previous_style)
                    self.policy_function(previous_lock, previous_path, previous_style, state)
                except Exception:
                    pass
            if isinstance(exc, ClientError):
                raise
            raise ClientError("WALLPAPER_APPLY_FAILED", f"Wallpaper application failed: {exc}") from exc

        state.update({
            "wallpaper_version": str(server["wallpaper_version"]),
            "config_revision": str(server.get("config_revision", "")),
            "rollout_id": str(server.get("rollout_id", "")),
            "sha256": expected_hash,
            "style": style,
            "wallpaper_path": str(cached_path),
            "distribution_enabled": True,
            "lock_change": bool(server.get("lock_change")),
            "config_etag": response_etag,
        })
        if applied or server.get("force_reapply"):
            state["last_apply"] = time.strftime("%Y-%m-%dT%H:%M:%S%z")
            state["status_pending"] = True
        atomic_write_json(self.state_path, state)
        if state.get("status_pending"):
            self.report_success(api, identity, state)
            state["status_pending"] = False
            atomic_write_json(self.state_path, state)
            self.logger.info("Wallpaper applied and status sent")
        else:
            self.logger.info("Wallpaper is already current: %s", server["wallpaper_version"])
        return interval, jitter

    def report_error(self, exc: ClientError) -> None:
        try:
            config = self.load_config()
            state = self.load_state()
            api = self.api_factory(config["server"], config["client_token"])
            api.report({
                **self.identity(),
                "wallpaper_version": state.get("wallpaper_version", ""),
                "wallpaper_sha256": state.get("sha256", ""),
                "rollout_id": state.get("rollout_id", ""),
                "status": "error",
                "error_code": exc.code[:64],
                "message": str(exc)[:500],
            })
        except Exception:
            self.logger.debug("Could not report error to server", exc_info=True)


class SingleInstance:
    def __init__(self) -> None:
        self.handle: int | None = None

    def __enter__(self) -> "SingleInstance":
        if os.name != "nt":
            return self
        assert _kernel32 is not None
        name = "Local\\AtivaWallpaperClient-" + user_key()
        self.handle = _kernel32.CreateMutexW(None, False, name)
        if not self.handle:
            raise ClientError("MUTEX_FAILED", "Cannot create instance mutex", retriable=False)
        if ctypes.get_last_error() == ERROR_ALREADY_EXISTS:
            _kernel32.CloseHandle(self.handle)
            self.handle = None
            raise ClientError("ALREADY_RUNNING", "Client is already running for this user", retriable=False)
        return self

    def __exit__(self, *_: Any) -> None:
        if self.handle and os.name == "nt":
            assert _kernel32 is not None
            _kernel32.CloseHandle(self.handle)


class StopEvent:
    def __init__(self, flag_path: Path = PRODUCT_DIR / "uninstall.flag") -> None:
        self.handle: int | None = None
        self.flag_path = flag_path
        if os.name == "nt":
            assert _kernel32 is not None
            self.handle = _kernel32.CreateEventW(None, True, False, STOP_EVENT_NAME)

    def wait(self, seconds: int) -> bool:
        deadline = time.monotonic() + max(0, seconds)
        while True:
            if self.flag_path.exists():
                return True
            remaining = deadline - time.monotonic()
            if remaining <= 0:
                return False
            slice_seconds = min(2.0, remaining)
            if self.handle and os.name == "nt":
                assert _kernel32 is not None
                if _kernel32.WaitForSingleObject(self.handle, int(slice_seconds * 1000)) == 0:
                    return True
            else:
                time.sleep(slice_seconds)

    def close(self) -> None:
        if self.handle and os.name == "nt":
            assert _kernel32 is not None
            _kernel32.CloseHandle(self.handle)


def _bootstrap_values(args: argparse.Namespace) -> dict[str, Any]:
    values: dict[str, Any] = {}
    if args.bootstrap_config:
        values.update(load_json(Path(args.bootstrap_config).resolve()))
    if args.server:
        values["server"] = args.server
    if args.registration_secret:
        values["registration_secret"] = args.registration_secret
    if values.get("verify_tls") is False:
        raise ClientError("TLS_VERIFICATION_DISABLED", "Bootstrap cannot disable TLS verification", retriable=False)
    values["server"] = normalize_server_url(str(values.get("server", "")))
    secret = values.get("registration_secret")
    if not isinstance(secret, str) or len(secret) < 32:
        raise ClientError("MISSING_REGISTRATION_SECRET", "Bootstrap registration secret is missing", retriable=False)
    pilot = str(values.get("pilot_hostname", "")).strip()
    if pilot and socket.gethostname().lower() != pilot.lower():
        raise ClientError("PILOT_SCOPE_MISMATCH", f"This package is restricted to pilot {pilot}", retriable=False)
    return values


def install_client(args: argparse.Namespace) -> None:
    ensure_supported_windows(bool(args.allow_windows_server))
    if not is_admin():
        raise ClientError("ADMIN_REQUIRED", "--install must run as SYSTEM or administrator", retriable=False)
    source = Path(sys.executable if getattr(sys, "frozen", False) else __file__).resolve()
    if source.suffix.lower() != ".exe":
        raise ClientError("FROZEN_BUILD_REQUIRED", "Installation requires the PyInstaller EXE", retriable=False)
    values = _bootstrap_values(args)
    root = PRODUCT_DIR
    root.mkdir(parents=True, exist_ok=True)
    set_installation_acls(root)
    logger = configure_logging(root, args.debug)
    logger.info("Starting installation of client %s", CLIENT_VERSION)

    identity = {
        "hostname": socket.gethostname(),
        "machine_guid": machine_guid(),
        "client_version": CLIENT_VERSION,
        "os_version": windows_product_name(),
    }
    token = ApiClient(values["server"]).register(str(values["registration_secret"]), identity)

    destination = root / EXECUTABLE_NAME
    _safe_unlink(root / "uninstall.flag")
    temporary = root / (EXECUTABLE_NAME + ".new")
    if source != destination.resolve():
        if destination.exists():
            _signal_stop()
            time.sleep(2)
        shutil.copy2(source, temporary)
        os.replace(temporary, destination)

    atomic_write_json(root / "client.json", {
        "server": values["server"],
        "client_token": token,
        "verify_tls": True,
        "allow_windows_server": bool(values.get("allow_windows_server", False)),
    })
    atomic_write_json(root / "version.json", {"client_version": CLIENT_VERSION, "installed_at": time.strftime("%Y-%m-%dT%H:%M:%S%z")})
    assert winreg is not None
    with winreg.CreateKeyEx(winreg.HKEY_LOCAL_MACHINE, RUN_KEY, 0, winreg.KEY_SET_VALUE | winreg.KEY_WOW64_64KEY) as key:
        winreg.SetValueEx(key, RUN_VALUE, 0, winreg.REG_SZ, f'"{destination}"')
    with winreg.CreateKeyEx(winreg.HKEY_LOCAL_MACHINE, PRODUCT_KEY, 0, winreg.KEY_SET_VALUE | winreg.KEY_WOW64_64KEY) as key:
        winreg.SetValueEx(key, "ClientVersion", 0, winreg.REG_SZ, CLIENT_VERSION)
        winreg.SetValueEx(key, "InstallPath", 0, winreg.REG_SZ, str(root))
    logger.info("Installation completed")


def _signal_stop() -> None:
    if os.name != "nt":
        return
    assert _kernel32 is not None
    event_modify_state = 0x0002
    handle = _kernel32.OpenEventW(event_modify_state, False, STOP_EVENT_NAME)
    if handle:
        _kernel32.SetEvent(handle)
        _kernel32.CloseHandle(handle)


def uninstall_client(remove_wallpaper: bool, debug: bool) -> None:
    ensure_supported_windows(True)
    if not is_admin():
        raise ClientError("ADMIN_REQUIRED", "--uninstall must run as SYSTEM or administrator", retriable=False)
    root = PRODUCT_DIR
    logger = configure_logging(root, debug)
    logger.info("Starting uninstall")
    (root / "uninstall.flag").touch(exist_ok=True)
    _signal_stop()
    time.sleep(2)
    assert winreg is not None
    try:
        with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, RUN_KEY, 0, winreg.KEY_QUERY_VALUE | winreg.KEY_SET_VALUE | winreg.KEY_WOW64_64KEY) as key:
            current, _ = winreg.QueryValueEx(key, RUN_VALUE)
            if str(root / EXECUTABLE_NAME).lower() in str(current).lower():
                winreg.DeleteValue(key, RUN_VALUE)
    except FileNotFoundError:
        pass
    try:
        with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, PRODUCT_KEY, 0, winreg.KEY_QUERY_VALUE | winreg.KEY_SET_VALUE | winreg.KEY_WOW64_64KEY) as key:
            installed_path, _ = winreg.QueryValueEx(key, "InstallPath")
            if str(installed_path).lower() == str(root).lower():
                for value_name in ("ClientVersion", "InstallPath"):
                    try:
                        winreg.DeleteValue(key, value_name)
                    except FileNotFoundError:
                        pass
        try:
            winreg.DeleteKeyEx(winreg.HKEY_LOCAL_MACHINE, PRODUCT_KEY, access=winreg.KEY_WOW64_64KEY)
        except (FileNotFoundError, OSError):
            pass
    except FileNotFoundError:
        pass

    try:
        state_path = root / "state" / f"state-{user_key()}.json"
        state = load_json(state_path) if state_path.exists() else {}
        manage_lock_policy(False, None, "fill", state)
    except Exception:
        logger.warning("Could not remove current user's owned policy values", exc_info=debug)

    for path in (root / "client.json", root / "version.json"):
        _safe_unlink(path)
    shutil.rmtree(root / "state", ignore_errors=True)
    if remove_wallpaper:
        shutil.rmtree(root / "data", ignore_errors=True)
    executable = root / EXECUTABLE_NAME
    if executable.exists():
        movefile_delay_until_reboot = 0x4
        assert _kernel32 is not None
        _kernel32.MoveFileExW(str(executable), None, movefile_delay_until_reboot)
    logger.info("Uninstall completed; executable deletion may finish at reboot")


def run_client(once: bool, debug: bool) -> int:
    ensure_supported_windows(False)
    client = WallpaperClient(debug=debug)
    stop = StopEvent()
    backoff = [60, 120, 300, 900]
    failures = 0
    try:
        with SingleInstance():
            client.logger.info("Client started, version %s", CLIENT_VERSION)
            while True:
                try:
                    interval, jitter = client.sync_once()
                    failures = 0
                    if once:
                        return 0
                    wait_seconds = interval + random.randint(0, jitter)
                except ClientError as exc:
                    client.logger.error("%s %s", exc.code, exc)
                    client.report_error(exc)
                    if once:
                        return 2
                    wait_seconds = backoff[min(failures, len(backoff) - 1)]
                    failures += 1
                if stop.wait(wait_seconds):
                    try:
                        state = client.load_state()
                        client.policy_function(False, None, "fill", state)
                        atomic_write_json(client.state_path, state)
                    except Exception:
                        client.logger.warning("Could not remove owned policy values while stopping", exc_info=debug)
                    client.logger.info("Stop requested")
                    return 0
    finally:
        stop.close()


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(description="Ativa Wallpaper Client")
    mode = parser.add_mutually_exclusive_group()
    mode.add_argument("--install", action="store_true", help="Install and register the client")
    mode.add_argument("--uninstall", action="store_true", help="Uninstall the client")
    mode.add_argument("--once", action="store_true", help="Run one synchronization and exit")
    parser.add_argument("--version", action="store_true", help="Show client version and exit")
    parser.add_argument("--debug", action="store_true", help="Also write logs to the console")
    parser.add_argument("--server", help="HTTPS base URL of the v1 API (installation only)")
    parser.add_argument("--registration-secret", help="Bootstrap secret (prefer --bootstrap-config)")
    parser.add_argument("--bootstrap-config", help="Path to protected bootstrap JSON")
    parser.add_argument("--allow-windows-server", action="store_true", help=argparse.SUPPRESS)
    parser.add_argument("--remove-wallpaper", action="store_true", help="Remove cached wallpaper on uninstall")
    return parser


def main(argv: list[str] | None = None) -> int:
    raw_args = list(sys.argv[1:] if argv is None else argv)
    if any(option in raw_args for option in ("--debug", "--version", "--help", "-h")):
        attach_parent_console()
    args = build_parser().parse_args(raw_args)
    if args.version:
        print(CLIENT_VERSION)
        return 0
    try:
        if args.install:
            install_client(args)
            return 0
        if args.uninstall:
            uninstall_client(args.remove_wallpaper, args.debug)
            return 0
        return run_client(args.once, args.debug)
    except ClientError as exc:
        if args.debug:
            print(f"ERROR {exc.code}: {exc}", file=sys.stderr)
        try:
            configure_logging(PRODUCT_DIR, args.debug).error("%s %s", exc.code, exc)
        except OSError:
            pass
        return 2
    except Exception as exc:
        if args.debug:
            print(f"ERROR INTERNAL_ERROR: {exc}", file=sys.stderr)
        try:
            configure_logging(PRODUCT_DIR, args.debug).exception("INTERNAL_ERROR")
        except OSError:
            pass
        return 3


if __name__ == "__main__":
    raise SystemExit(main())
