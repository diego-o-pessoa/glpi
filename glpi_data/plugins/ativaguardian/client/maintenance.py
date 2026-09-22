"""Local, offline maintenance authorization. Never accepts remote shell commands.

The password gates the supported maintenance workflow; it is NOT a security
boundary against a Windows administrator with ownership/debug privileges.
SYSTEM installers remain authorized, so fleet updates do not need the password.
"""
from __future__ import annotations

import ctypes
from ctypes import wintypes
import hashlib
import hmac
import json
import os
from pathlib import Path
import re
import subprocess
import sys
import time
import shutil

ROOT = Path(os.environ.get("ProgramData", r"C:\ProgramData")) / "AtivaLocacao"
GUARDIAN = ROOT / "Guardian"
CONFIG = GUARDIAN / "config.json"
STATE = GUARDIAN / "maintenance.json"
EXE = Path(os.environ.get("ProgramFiles", r"C:\Program Files")) / "Ativa Locacao/Guardian/AtivaGuardian.exe"
SERVICES = ("AtivaGuardian", "AtivaUnifiedUpdater", "RustDesk", "glpi-agent", "GLPIAgent")
TASK = "Ativa Guardian Protection"
MINUTES = 15
# Administrators may inspect, but STOP/CHANGE_CONFIG/DELETE require the
# maintenance workflow. SYSTEM retains full access for the updater and SCM.
LOCKED_SERVICE_DACL = "D:P(A;;GA;;;SY)(A;;CCLCSWLOCRRCWD;;;BA)(A;;CCLCSWLOCRRC;;;AU)"
OPEN_SERVICE_DACL = "D:P(A;;GA;;;SY)(A;;GA;;;BA)(A;;CCLCSWLOCRRC;;;AU)"
VERIFIER_RE = re.compile(r"pbkdf2_sha256\$600000\$[a-f0-9]{32}\$[a-f0-9]{64}\Z")


def validate_verifier(value: str) -> str:
    if value and not VERIFIER_RE.fullmatch(value):
        raise ValueError("Verificador da senha de manutencao invalido.")
    return value


def verify_password(password: str, verifier: str) -> bool:
    if not verifier or not VERIFIER_RE.fullmatch(verifier):
        return False
    _, rounds, salt, expected = verifier.split("$")
    actual = hashlib.pbkdf2_hmac("sha256", password.encode("utf-8"), bytes.fromhex(salt), int(rounds)).hex()
    return hmac.compare_digest(actual, expected)


def read_json(path: Path) -> dict:
    if not path.exists():
        return {}
    value = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(value, dict):
        raise ValueError("Arquivo de manutencao invalido.")
    return value


def save_state(value: dict) -> None:
    staged = STATE.with_suffix(".new")
    staged.write_text(json.dumps(value), encoding="utf-8")
    os.replace(staged, STATE)


def command(*args: str, required: bool = True) -> subprocess.CompletedProcess:
    result = subprocess.run(list(args), capture_output=True, text=True, timeout=60,
                            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0))
    if required and result.returncode:
        raise RuntimeError(f"Falha em {args[0]} ({result.returncode}): {result.stderr or result.stdout}")
    return result


def is_system() -> bool:
    # SID, not a localized account name or spoofable environment variable.
    result = command("whoami.exe", "/user", "/fo", "csv", "/nh")
    return '"S-1-5-18"' in result.stdout


def is_admin() -> bool:
    return os.name == "nt" and bool(ctypes.windll.shell32.IsUserAnAdmin())


def show_message(message: str, *, error: bool = False) -> None:
    user32 = ctypes.WinDLL("user32", use_last_error=True)
    user32.MessageBoxW.argtypes = [wintypes.HWND, wintypes.LPCWSTR, wintypes.LPCWSTR, wintypes.UINT]
    user32.MessageBoxW.restype = ctypes.c_int
    user32.MessageBoxW(None, message, "Manutencao Ativa", 0x10 if error else 0x40)


def prompt_password() -> str | None:
    # Native Windows credential UI: no Tcl/Tk dependency in the frozen service.
    # GENERIC credentials are verified against our hash, not a Windows account.
    class CredentialInfo(ctypes.Structure):
        _fields_ = [("size", wintypes.DWORD), ("parent", wintypes.HWND),
                    ("message", wintypes.LPCWSTR), ("caption", wintypes.LPCWSTR),
                    ("banner", wintypes.HANDLE)]
    info = CredentialInfo(ctypes.sizeof(CredentialInfo), None,
                          "Digite a senha da Ativa para liberar manutencao por 15 minutos.",
                          "Manutencao Ativa", None)
    username = ctypes.create_unicode_buffer("Ativa", 256)
    password = ctypes.create_unicode_buffer(256)
    save = wintypes.BOOL(False)
    credui = ctypes.WinDLL("credui", use_last_error=True)
    credui.CredUIPromptForCredentialsW.argtypes = [ctypes.POINTER(CredentialInfo), wintypes.LPCWSTR,
        wintypes.LPVOID, wintypes.DWORD, wintypes.LPWSTR, wintypes.ULONG,
        wintypes.LPWSTR, wintypes.ULONG, ctypes.POINTER(wintypes.BOOL), wintypes.DWORD]
    credui.CredUIPromptForCredentialsW.restype = wintypes.DWORD
    flags = 0x40000 | 0x80 | 0x2 | 0x100000  # generic, always show, never persist, keep username
    result = credui.CredUIPromptForCredentialsW(ctypes.byref(info), "AtivaMaintenance", None,
        0, username, len(username), password, len(password), ctypes.byref(save), flags)
    try:
        if result == 1223:
            return None
        if result:
            raise ctypes.WinError(result)
        return password.value
    finally:
        ctypes.memset(ctypes.addressof(password), 0, ctypes.sizeof(password))


def lease_active(state: dict, now: float | None = None) -> bool:
    now = time.time() if now is None else now
    until = float(state.get("until", 0))
    return now < until <= now + MINUTES * 60 + 5


def service_exists(name: str) -> bool:
    result = command("sc.exe", "query", name, required=False)
    if result.returncode == 1060:
        return False
    if result.returncode:
        raise RuntimeError(f"Nao foi possivel consultar {name}: {result.returncode}")
    return True


def protect_files() -> None:
    # Do not recursively deny the wallpaper runtime directories: its client
    # writes data/state/logs in the user's session. Protect the executable,
    # registration and startup controls instead.
    for directory in (GUARDIAN, ROOT / "UnifiedUpdater", EXE.parent):
        if not directory.exists():
            continue
        public = directory == EXE.parent
        grants = ["*S-1-5-18:(OI)(CI)F", "*S-1-5-32-544:(OI)(CI)F"]
        if public:
            grants.append("*S-1-5-32-545:(OI)(CI)RX")
        command("icacls.exe", str(directory), "/inheritance:r", "/grant:r", *grants)
        command("icacls.exe", str(directory), "/remove:g", "*S-1-1-0", "*S-1-5-11")
        if not public:
            command("icacls.exe", str(directory), "/remove:g", "*S-1-5-32-545")
    wallpaper = ROOT / "Wallpaper"
    for name in ("AtivaWallpaperClient.exe", "client.json", "version.json"):
        target = wallpaper / name
        if target.is_file():
            command("icacls.exe", str(target), "/inheritance:r", "/grant:r",
                    "*S-1-5-18:F", "*S-1-5-32-544:F", "*S-1-5-32-545:RX")
            command("icacls.exe", str(target), "/remove:g", "*S-1-1-0", "*S-1-5-11")


def install_recovery_task() -> None:
    # A separate SYSTEM task closes a lease even if the administrator stops
    # Guardian itself. The task runs again after reboot, when the lease expires.
    # The recovery task must not lock the service EXE during the next upgrade.
    # Keep a versioned copy; an old running recovery exits before the next run.
    digest = hashlib.sha256(EXE.read_bytes()).hexdigest()[:16]
    recovery_dir = GUARDIAN / "protection"
    recovery_dir.mkdir(parents=True, exist_ok=True)
    recovery = recovery_dir / f"AtivaGuardian-{digest}.exe"
    if not recovery.exists():
        shutil.copy2(EXE, recovery)
    command("schtasks.exe", "/Create", "/F", "/TN", TASK, "/RU", "SYSTEM",
            "/RL", "HIGHEST", "/SC", "MINUTE", "/MO", "1",
            "/TR", f'"{recovery}" --enforce-protection')


def enforce(close: bool = False) -> None:
    if not is_admin():
        raise PermissionError("Execute como administrador.")
    config = read_json(CONFIG)
    verifier = validate_verifier(str(config.get("maintenance_password_hash", "")))
    if not verifier:
        return  # Old installations remain upgradeable until a password is set.
    state = read_json(STATE)
    if not close and lease_active(state):
        return
    # Nothing changed since the last successful protection pass. The task's
    # purpose is lease recovery; do not rewrite ACLs/state every minute.
    if not close and state.get("protected_at") and not state.get("until"):
        return
    for name in SERVICES:
        if service_exists(name):
            command("sc.exe", "sdset", name, LOCKED_SERVICE_DACL)
    protect_files()
    # Only resume services that were running before this authorized maintenance.
    for name in state.get("resume", []):
        if name in SERVICES and service_exists(name):
            command("sc.exe", "start", name, required=False)
    save_state({"until": 0, "resume": [], "protected_at": time.time()})


def open_lease() -> None:
    state = read_json(STATE)
    resume = set(state.get("resume", []))
    for name in SERVICES:
        if service_exists(name):
            status = command("sc.exe", "query", name).stdout
            if re.search(r":\s*4\s+RUNNING", status):
                resume.add(name)
    # Persist recovery information before relaxing even one service ACL.
    save_state({"until": time.time() + MINUTES * 60, "resume": sorted(resume)})
    for name in SERVICES:
        if service_exists(name):
            command("sc.exe", "sdset", name, OPEN_SERVICE_DACL)


def authorize(installation: bool = False) -> None:
    if not is_admin():
        raise PermissionError("Abra Manutencao Ativa como administrador (UAC).")
    verifier = validate_verifier(str(read_json(CONFIG).get("maintenance_password_hash", "")))
    if not verifier:
        if installation:
            return
        raise RuntimeError("Defina a senha no GLPI / Ativa Guardian / Configuracoes e distribua a configuracao.")
    if installation and is_system():
        open_lease()
        return
    # No password argument, log, environment variable or file with clear text.
    attempts = read_json(GUARDIAN / "password-attempts.json")
    if float(attempts.get("locked_until", 0)) > time.time():
        raise PermissionError("Muitas tentativas. Aguarde cinco minutos.")
    password = prompt_password()
    if password is None or not verify_password(password, verifier):
        failures = int(attempts.get("failures", 0)) + 1
        (GUARDIAN / "password-attempts.json").write_text(json.dumps({
            "failures": 0 if failures >= 5 else failures,
            "locked_until": time.time() + 300 if failures >= 5 else 0,
        }), encoding="utf-8")
        raise PermissionError("Manutencao nao autorizada: senha incorreta ou cancelamento.")
    del password
    (GUARDIAN / "password-attempts.json").write_text("{}", encoding="utf-8")
    install_recovery_task()
    open_lease()
    if not installation:
        show_message("Manutencao liberada por 15 minutos. Os servicos serao protegidos novamente automaticamente.")
        command("sc.exe", "stop", "AtivaGuardian", required=False)
        subprocess.Popen(["mmc.exe", "services.msc"])


def launch_panel() -> int:
    if not is_admin():
        shell = ctypes.WinDLL("shell32", use_last_error=True)
        shell.ShellExecuteW.argtypes = [ctypes.c_void_p, ctypes.c_wchar_p, ctypes.c_wchar_p,
                                        ctypes.c_wchar_p, ctypes.c_wchar_p, ctypes.c_int]
        shell.ShellExecuteW.restype = ctypes.c_void_p
        result = shell.ShellExecuteW(None, "runas", str(EXE), "--maintenance", None, 1)
        return 0 if result and result > 32 else 1
    authorize()
    return 0
