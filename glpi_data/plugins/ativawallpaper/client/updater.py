from __future__ import annotations

import argparse
import ctypes
import logging
from logging.handlers import RotatingFileHandler
import os
from pathlib import Path
import subprocess
import time
from typing import Any

import wallpaper_client as wc


UPDATER_VERSION = "1.0.0"
AGENT_SERVER = "https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/"
UPDATES_DIR = wc.PRODUCT_DIR / "updates"
UPDATE_STATE_PATH = wc.PRODUCT_DIR / "update-state.json"
UPDATE_RESTART_FLAG = wc.PRODUCT_DIR / "update-restart.flag"
MUTEX_NAME = r"Global\AtivaWallpaperUpdater"
MAX_UPDATE_BYTES = 250 * 1024 * 1024


def configure_logger(debug: bool = False) -> logging.Logger:
    directory = wc.PRODUCT_DIR / "logs"
    directory.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger("AtivaWallpaperUpdater")
    for handler in logger.handlers[:]:
        handler.close()
        logger.removeHandler(handler)
    logger.setLevel(logging.DEBUG if debug else logging.INFO)
    handler = RotatingFileHandler(directory / "updater.log", maxBytes=5 * 1024 * 1024, backupCount=5, encoding="utf-8")
    handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s %(message)s", "%Y-%m-%d %H:%M:%S"))
    logger.addHandler(handler)
    if debug:
        console = logging.StreamHandler()
        console.setFormatter(handler.formatter)
        logger.addHandler(console)
    return logger


def installed_agent_version(use_state: bool = True) -> str:
    if os.name != "nt" or wc.winreg is None:
        return "0.0.0"
    uninstall = r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall"
    for view in (wc.winreg.KEY_WOW64_64KEY, wc.winreg.KEY_WOW64_32KEY):
        try:
            with wc.winreg.OpenKey(wc.winreg.HKEY_LOCAL_MACHINE, uninstall, 0, wc.winreg.KEY_READ | view) as root:
                for index in range(wc.winreg.QueryInfoKey(root)[0]):
                    try:
                        name = wc.winreg.EnumKey(root, index)
                        with wc.winreg.OpenKey(root, name) as item:
                            display_name = str(wc.winreg.QueryValueEx(item, "DisplayName")[0])
                            if not display_name.strip().lower().startswith("glpi agent"):
                                continue
                            return str(wc.winreg.QueryValueEx(item, "DisplayVersion")[0]).strip() or "0.0.0"
                    except (FileNotFoundError, OSError):
                        continue
        except (FileNotFoundError, OSError):
            continue
    if use_state:
        state = wc.load_json(UPDATE_STATE_PATH) if UPDATE_STATE_PATH.exists() else {}
        return str(state.get("glpi_agent_version", "0.0.0"))
    return "0.0.0"


def installed_client_version() -> str:
    path = wc.PRODUCT_DIR / "version.json"
    if not path.exists():
        return "0.0.0"
    return str(wc.load_json(path).get("client_version", "0.0.0"))


def validate_download(path: Path, component: str) -> None:
    magic = path.read_bytes()[:8]
    expected = b"MZ" if component == "wallpaper_client" else b"\xd0\xcf\x11\xe0\xa1\xb1\x1a\xe1"
    if not magic.startswith(expected):
        raise wc.ClientError("INVALID_UPDATE_BINARY", "Downloaded package has an invalid file signature", retriable=False)


def report(api: wc.ApiClient, update: dict[str, Any], status: str, from_version: str, message: str = "") -> None:
    api.report_update({
        "update_id": int(update["id"]),
        "status": status,
        "from_version": from_version,
        "message": message[:1000],
    })


def retry_allowed(update_id: int) -> bool:
    state = wc.load_json(UPDATE_STATE_PATH) if UPDATE_STATE_PATH.exists() else {}
    retry = state.get("retry", {}).get(str(update_id), {})
    return float(retry.get("after", 0)) <= time.time()


def record_retry(update_id: int, failed: bool) -> int:
    state = wc.load_json(UPDATE_STATE_PATH) if UPDATE_STATE_PATH.exists() else {}
    retries = state.setdefault("retry", {})
    if not failed:
        retries.pop(str(update_id), None)
        wc.atomic_write_json(UPDATE_STATE_PATH, state)
        return 0
    previous = retries.get(str(update_id), {})
    failures = min(10, int(previous.get("failures", 0)) + 1)
    delay = min(3600, 60 * (2 ** (failures - 1)))
    retries[str(update_id)] = {"failures": failures, "after": time.time() + delay}
    wc.atomic_write_json(UPDATE_STATE_PATH, state)
    return delay


def replace_wallpaper_client(staged: Path, version: str) -> str:
    version_file = staged.with_name(staged.name + ".version")
    version_file.unlink(missing_ok=True)
    try:
        version_check = subprocess.run(
            [str(staged), "--version-file", str(version_file)], check=False, capture_output=True, timeout=30,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        embedded_version = version_file.read_text(encoding="utf-8").strip() if version_file.is_file() else ""
    finally:
        version_file.unlink(missing_ok=True)
    if version_check.returncode != 0 or wc.compare_versions(embedded_version, version) != 0:
        raise wc.ClientError(
            "UPDATE_VERSION_MISMATCH",
            f"Package declares {version}, but executable reports {embedded_version or 'unknown'}",
            retriable=False,
        )
    destination = wc.PRODUCT_DIR / wc.EXECUTABLE_NAME
    wc.atomic_write_json(UPDATE_RESTART_FLAG, {"version": version, "created_at": time.time()})
    wc._signal_stop()
    last_error: OSError | None = None
    try:
        for _attempt in range(30):
            try:
                os.replace(staged, destination)
                wc.atomic_write_json(wc.PRODUCT_DIR / "version.json", {
                    "client_version": version,
                    "installed_at": time.strftime("%Y-%m-%dT%H:%M:%S%z"),
                    "installed_by": "automatic_updater",
                })
                return "Wallpaper Client atualizado e reiniciado para o usuario conectado."
            except OSError as exc:
                last_error = exc
                time.sleep(1)
        raise wc.ClientError("CLIENT_REPLACE_FAILED", f"Could not replace the running client: {last_error}")
    finally:
        UPDATE_RESTART_FLAG.unlink(missing_ok=True)


def install_glpi_agent(staged: Path, version: str) -> tuple[str, str]:
    completed = subprocess.run([
        "msiexec.exe", "/i", str(staged), "/qn", "/norestart",
        f"SERVER={AGENT_SERVER}", "ADDLOCAL=ALL", "EXECMODE=1", "RUNNOW=1",
        "GLPI_VERSION=11", "ADD_FIREWALL_EXCEPTION=1", "NO_SSL_CHECK=0",
        "NO_HTTPD=0", "NO_P2P=0", "SCAN_PROFILES=1", 'TAG=Ativa-Locacao',
    ], check=False, capture_output=True, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0))
    if completed.returncode not in (0, 1641, 3010):
        raise wc.ClientError("GLPI_AGENT_UPDATE_FAILED", f"msiexec returned {completed.returncode}")
    detected_version = installed_agent_version(use_state=False)
    if detected_version != "0.0.0" and wc.compare_versions(detected_version, version) != 0:
        raise wc.ClientError(
            "GLPI_AGENT_VERSION_MISMATCH",
            f"Installer completed, but Windows reports version {detected_version} instead of {version}",
        )
    state = wc.load_json(UPDATE_STATE_PATH) if UPDATE_STATE_PATH.exists() else {}
    state["glpi_agent_version"] = version
    wc.atomic_write_json(UPDATE_STATE_PATH, state)
    if completed.returncode in (1641, 3010):
        return "restart_required", "GLPI Agent atualizado; o Windows solicitou reinicializacao."
    return "success", "GLPI Agent atualizado e inventario solicitado."


def apply_update(api: wc.ApiClient, update: dict[str, Any], logger: logging.Logger) -> None:
    component = str(update.get("component", ""))
    if component not in ("wallpaper_client", "glpi_agent"):
        raise wc.ClientError("INVALID_UPDATE_COMPONENT", "Unknown update component", retriable=False)
    version = str(update.get("version", ""))
    update_id = int(update["id"])
    if not retry_allowed(update_id):
        logger.info("Update %s is waiting for its retry window", update_id)
        return
    current = installed_client_version() if component == "wallpaper_client" else installed_agent_version()
    extension = ".exe" if component == "wallpaper_client" else ".msi"
    UPDATES_DIR.mkdir(parents=True, exist_ok=True)
    staged = UPDATES_DIR / f"update-{int(update['id'])}{extension}"
    try:
        report(api, update, "downloading", current)
        api.download(
            str(update["download_url"]),
            staged,
            str(update["sha256"]),
            int(update["filesize"]),
            maximum_bytes=MAX_UPDATE_BYTES,
            allowed_content_types=("application/octet-stream",),
        )
        validate_download(staged, component)
        report(api, update, "installing", current)
        if component == "wallpaper_client":
            message = replace_wallpaper_client(staged, version)
            status = "success"
        else:
            status, message = install_glpi_agent(staged, version)
        report(api, update, status, current, message)
        record_retry(update_id, False)
        logger.info("%s %s: %s", component, version, message)
    except Exception as exc:
        logger.exception("Update %s %s failed", component, version)
        delay = record_retry(update_id, True)
        try:
            report(api, update, "error", current, f"{exc}; nova tentativa em {delay} segundos")
        except Exception:
            logger.exception("Could not report update failure")
    finally:
        staged.unlink(missing_ok=True)


def check_updates(debug: bool = False) -> int:
    if os.name != "nt":
        return 2
    logger = configure_logger(debug)
    config = wc.load_json(wc.PRODUCT_DIR / "client.json")
    api = wc.ApiClient(str(config.get("server", "")), str(config.get("client_token", "")))
    payload = {
        "machine_guid": wc.machine_guid(),
        "wallpaper_client_version": installed_client_version(),
        "glpi_agent_version": installed_agent_version(),
        "updater_version": UPDATER_VERSION,
    }
    updates = api.check_updates(payload)
    logger.info("Update check completed: %d package(s)", len(updates))
    order = {"wallpaper_client": 0, "glpi_agent": 1}
    for update in sorted(updates, key=lambda item: order.get(str(item.get("component")), 99)):
        apply_update(api, update, logger)
    return 0


def relaunch_client() -> int:
    deadline = time.monotonic() + 120
    while UPDATE_RESTART_FLAG.exists() and time.monotonic() < deadline:
        time.sleep(1)
    executable = wc.PRODUCT_DIR / wc.EXECUTABLE_NAME
    if UPDATE_RESTART_FLAG.exists() or not executable.is_file():
        return 2
    time.sleep(1)
    subprocess.Popen([str(executable)], close_fds=True, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0))
    return 0


class SingleInstance:
    def __init__(self) -> None:
        self.handle = None

    def __enter__(self):
        kernel32 = ctypes.WinDLL("kernel32", use_last_error=True)
        self.handle = kernel32.CreateMutexW(None, False, MUTEX_NAME)
        if not self.handle or ctypes.get_last_error() == 183:
            raise RuntimeError("Updater is already running")
        return self

    def __exit__(self, *_args):
        if self.handle:
            ctypes.WinDLL("kernel32", use_last_error=True).CloseHandle(self.handle)


def main() -> int:
    parser = argparse.ArgumentParser(description="Ativa automatic updater")
    parser.add_argument("--check", action="store_true")
    parser.add_argument("--relaunch-client", action="store_true")
    parser.add_argument("--version", action="store_true")
    parser.add_argument("--debug", action="store_true")
    args = parser.parse_args()
    if args.version:
        print(UPDATER_VERSION)
        return 0
    if args.relaunch_client:
        return relaunch_client()
    if args.check:
        try:
            with SingleInstance():
                return check_updates(args.debug)
        except RuntimeError:
            return 0
    parser.error("select --check, --relaunch-client or --version")
    return 2


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception:
        try:
            configure_logger(False).exception("Updater failed")
        finally:
            raise SystemExit(2)
