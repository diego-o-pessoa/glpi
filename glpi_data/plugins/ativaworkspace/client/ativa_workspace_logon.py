"""
Ativa Workspace - fase "login do usuario Entra" (depois do ingresso).

Fluxo (conduzido pelo servico, SYSTEM):
  1. liga o Web sign-in (politica Authentication/EnableWebSignIn) e reinicia;
  2. depois do boot, desconecta sessoes locais para a tela de login aparecer;
  3. abre um helper NA TELA DE LOGIN (desktop winlogon, como SYSTEM) que clica
     em Outro usuario -> Opcoes de entrada -> globo (Web sign-in) -> Entrar e
     digita a conta + um TAP novo (senha temporaria de uso unico);
  4. o servico confirma quando existe uma sessao do dominio AzureAD.

O TAP fica num arquivo da pasta do Workspace (so SYSTEM/Administradores), e
lido e apagado pelo helper. Nunca vai para log nem para linha de comando.
"""

from __future__ import annotations

import ctypes
import json
import logging
import os
import subprocess
import sys
import time
import unicodedata
from ctypes import wintypes
from pathlib import Path

WEB_SIGNIN_KEY = r"SOFTWARE\Microsoft\PolicyManager\current\device\Authentication"
WEB_SIGNIN_VALUE = "EnableWebSignIn"
REBOOT_DELAY_SECONDS = 60
REBOOT_MESSAGE = "Ativa Workspace: reiniciando para concluir o ingresso no Microsoft Entra ID."
ENTRA_DOMAIN = "AZUREAD"

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)


# --------------------------------------------------------------------------- #
#  Web sign-in e reinicio
# --------------------------------------------------------------------------- #

def enable_web_signin(logger: logging.Logger) -> bool:
    """Liga o Web sign-in (vale depois do proximo boot). True se ficou ligado."""
    import winreg  # type: ignore

    try:
        with winreg.CreateKeyEx(
            winreg.HKEY_LOCAL_MACHINE, WEB_SIGNIN_KEY, 0,
            winreg.KEY_SET_VALUE | winreg.KEY_QUERY_VALUE | winreg.KEY_WOW64_64KEY,
        ) as key:
            try:
                current, _ = winreg.QueryValueEx(key, WEB_SIGNIN_VALUE)
            except OSError:
                current = None
            if current != 1:
                winreg.SetValueEx(key, WEB_SIGNIN_VALUE, 0, winreg.REG_DWORD, 1)
                logger.info("Web sign-in habilitado (vale apos reiniciar).")
        return True
    except OSError as exc:
        logger.warning("Nao foi possivel habilitar o Web sign-in: %s", exc)
        return False


def boot_time() -> float:
    """Momento (epoch) do ultimo boot."""
    kernel32 = ctypes.windll.kernel32
    kernel32.GetTickCount64.restype = ctypes.c_ulonglong
    return time.time() - kernel32.GetTickCount64() / 1000.0


def request_reboot(logger: logging.Logger) -> bool:
    """Reinicio com aviso na tela (motivo: reconfiguracao planejada)."""
    try:
        completed = subprocess.run(
            ["shutdown", "/r", "/t", str(REBOOT_DELAY_SECONDS), "/c", REBOOT_MESSAGE, "/d", "p:4:1"],
            capture_output=True, text=True, timeout=30, creationflags=NO_WINDOW,
        )
    except (OSError, subprocess.SubprocessError) as exc:
        logger.warning("Falha ao agendar o reinicio: %s", exc)
        return False
    if completed.returncode not in (0, 1190):  # 1190 = ja existe um desligamento agendado
        logger.warning("shutdown /r retornou %s.", completed.returncode)
        return False
    logger.info("Reinicio agendado em %ss para ativar o Web sign-in.", REBOOT_DELAY_SECONDS)
    return True


# --------------------------------------------------------------------------- #
#  Sessoes
# --------------------------------------------------------------------------- #

WTS_ACTIVE = 0
WTS_USER_NAME = 5
WTS_DOMAIN_NAME = 7


class _WTS_SESSION_INFOW(ctypes.Structure):
    _fields_ = [("SessionId", wintypes.DWORD), ("pWinStationName", wintypes.LPWSTR), ("State", ctypes.c_int)]


def _session_string(session_id: int, info_class: int) -> str:
    wtsapi32 = ctypes.windll.wtsapi32
    buffer = wintypes.LPWSTR()
    size = wintypes.DWORD()
    if not wtsapi32.WTSQuerySessionInformationW(None, session_id, info_class, ctypes.byref(buffer), ctypes.byref(size)):
        return ""
    try:
        return buffer.value or ""
    finally:
        wtsapi32.WTSFreeMemory(buffer)


def logged_sessions() -> list[tuple[int, str, str, int]]:
    """(session_id, dominio, usuario, estado) de cada sessao com usuario."""
    wtsapi32 = ctypes.windll.wtsapi32
    info = ctypes.c_void_p()
    count = wintypes.DWORD()
    result: list[tuple[int, str, str, int]] = []
    if not wtsapi32.WTSEnumerateSessionsW(None, 0, 1, ctypes.byref(info), ctypes.byref(count)):
        return result
    try:
        array = ctypes.cast(info, ctypes.POINTER(_WTS_SESSION_INFOW))
        for i in range(count.value):
            session_id = int(array[i].SessionId)
            if session_id == 0:
                continue
            user = _session_string(session_id, WTS_USER_NAME)
            if user:
                result.append((session_id, _session_string(session_id, WTS_DOMAIN_NAME), user, int(array[i].State)))
    finally:
        wtsapi32.WTSFreeMemory(info)
    return result


def entra_user_logged_in() -> bool:
    """Alguma sessao de conta do Entra (dominio AzureAD)."""
    return any(domain.upper() == ENTRA_DOMAIN for _sid, domain, _user, _state in logged_sessions())


def disconnect_local_sessions(logger: logging.Logger) -> None:
    """Igual a "Trocar usuario": desconecta (nao encerra) as sessoes locais ativas."""
    wtsapi32 = ctypes.windll.wtsapi32
    for session_id, domain, _user, state in logged_sessions():
        if state == WTS_ACTIVE and domain.upper() != ENTRA_DOMAIN:
            if wtsapi32.WTSDisconnectSession(None, session_id, False):
                logger.info("Sessao local %s desconectada (trocar usuario).", session_id)


# --------------------------------------------------------------------------- #
#  Helper na tela de login (desktop winlogon, como SYSTEM)
# --------------------------------------------------------------------------- #

class _STARTUPINFOW(ctypes.Structure):
    _fields_ = [
        ("cb", wintypes.DWORD), ("lpReserved", wintypes.LPWSTR),
        ("lpDesktop", wintypes.LPWSTR), ("lpTitle", wintypes.LPWSTR),
        ("dwX", wintypes.DWORD), ("dwY", wintypes.DWORD),
        ("dwXSize", wintypes.DWORD), ("dwYSize", wintypes.DWORD),
        ("dwXCountChars", wintypes.DWORD), ("dwYCountChars", wintypes.DWORD),
        ("dwFillAttribute", wintypes.DWORD), ("dwFlags", wintypes.DWORD),
        ("wShowWindow", wintypes.WORD), ("cbReserved2", wintypes.WORD),
        ("lpReserved2", ctypes.c_void_p), ("hStdInput", wintypes.HANDLE),
        ("hStdOutput", wintypes.HANDLE), ("hStdError", wintypes.HANDLE),
    ]


class _PROCESS_INFORMATION(ctypes.Structure):
    _fields_ = [
        ("hProcess", wintypes.HANDLE), ("hThread", wintypes.HANDLE),
        ("dwProcessId", wintypes.DWORD), ("dwThreadId", wintypes.DWORD),
    ]


def launch_on_logon_screen(exe: Path, arguments: str, logger: logging.Logger) -> bool:
    """
    Cria o exe (como SYSTEM) no desktop da tela de login da sessao de console.
    So o servico (SYSTEM, SeTcbPrivilege) consegue: um processo comum nao ve a
    tela de login.
    """
    advapi32 = ctypes.windll.advapi32
    kernel32 = ctypes.windll.kernel32
    kernel32.WTSGetActiveConsoleSessionId.restype = wintypes.DWORD
    kernel32.GetCurrentProcess.restype = wintypes.HANDLE
    advapi32.CreateProcessAsUserW.argtypes = [
        wintypes.HANDLE, wintypes.LPCWSTR, wintypes.LPWSTR, ctypes.c_void_p, ctypes.c_void_p,
        wintypes.BOOL, wintypes.DWORD, ctypes.c_void_p, wintypes.LPCWSTR,
        ctypes.POINTER(_STARTUPINFOW), ctypes.POINTER(_PROCESS_INFORMATION),
    ]
    advapi32.CreateProcessAsUserW.restype = wintypes.BOOL

    console = int(kernel32.WTSGetActiveConsoleSessionId())
    if console == 0xFFFFFFFF:
        logger.warning("Sem sessao de console para a tela de login.")
        return False

    TOKEN_ALL_ACCESS = 0xF01FF
    SECURITY_IMPERSONATION = 2
    TOKEN_PRIMARY = 1
    TOKEN_SESSION_ID = 12

    own = wintypes.HANDLE()
    primary = wintypes.HANDLE()
    if not advapi32.OpenProcessToken(kernel32.GetCurrentProcess(), TOKEN_ALL_ACCESS, ctypes.byref(own)):
        logger.warning("OpenProcessToken falhou (erro %s).", kernel32.GetLastError())
        return False
    try:
        if not advapi32.DuplicateTokenEx(own, TOKEN_ALL_ACCESS, None, SECURITY_IMPERSONATION,
                                         TOKEN_PRIMARY, ctypes.byref(primary)):
            logger.warning("DuplicateTokenEx falhou (erro %s).", kernel32.GetLastError())
            return False
        session = wintypes.DWORD(console)
        if not advapi32.SetTokenInformation(primary, TOKEN_SESSION_ID, ctypes.byref(session), ctypes.sizeof(session)):
            logger.warning("SetTokenInformation falhou (erro %s).", kernel32.GetLastError())
            return False

        command = ctypes.create_unicode_buffer(f'"{exe}" {arguments}')
        si = _STARTUPINFOW()
        si.cb = ctypes.sizeof(_STARTUPINFOW)
        si.lpDesktop = "winsta0\\winlogon"
        pi = _PROCESS_INFORMATION()
        CREATE_NO_WINDOW = 0x08000000
        working_dir = str(Path(os.environ.get("SystemRoot", r"C:\Windows")) / "System32")
        if not advapi32.CreateProcessAsUserW(primary, None, command, None, None, False, CREATE_NO_WINDOW,
                                             None, working_dir, ctypes.byref(si), ctypes.byref(pi)):
            logger.warning("CreateProcessAsUserW (tela de login) falhou (erro %s).", kernel32.GetLastError())
            return False
        kernel32.CloseHandle(pi.hProcess)
        kernel32.CloseHandle(pi.hThread)
        return True
    finally:
        if primary.value:
            kernel32.CloseHandle(primary)
        kernel32.CloseHandle(own)


def write_payload(path: Path, upn: str, tap: str) -> None:
    path.write_text(json.dumps({"upn": upn, "tap": tap}), "utf-8")


def read_payload(path: Path) -> dict[str, str]:
    """Le e APAGA o arquivo (conta + TAP)."""
    try:
        data = json.loads(path.read_text("utf-8"))
    except (OSError, ValueError):
        return {}
    finally:
        path.unlink(missing_ok=True)
    return data if isinstance(data, dict) else {}


# --------------------------------------------------------------------------- #
#  Automacao da tela de login (roda no helper)
# --------------------------------------------------------------------------- #

def _norm(text: str) -> str:
    text = unicodedata.normalize("NFKD", (text or "").strip().lower())
    return "".join(c for c in text if not unicodedata.combining(c))


def run_logon_signin(payload_path: Path, logger: logging.Logger) -> int:
    """
    Outro usuario -> Opcoes de entrada -> globo (Web sign-in) -> Entrar ->
    conta -> Avancar -> TAP -> Entrar. O TAP nunca vai para o log.
    """
    payload = read_payload(payload_path)
    upn = str(payload.get("upn", "")).strip()
    tap = str(payload.get("tap", ""))
    if not upn or not tap:
        logger.warning("Login: sem conta ou TAP; tela fica para entrada manual.")
        return 0

    try:
        import uiautomation as auto  # type: ignore
    except ImportError:
        logger.warning("uiautomation ausente: login manual.")
        return 0

    auto.uiautomation.SetGlobalSearchTimeout(2)

    def find(predicate, control_type=None, depth=40):
        def compare(ctrl, _depth):
            try:
                return not ctrl.IsOffscreen and predicate(_norm(ctrl.Name))
            except Exception:  # noqa: BLE001
                return False
        ctrl = (control_type or auto.Control)(searchDepth=depth, Compare=compare)
        return ctrl if ctrl.Exists(0, 0) else None

    def click(ctrl) -> None:
        try:
            ctrl.Click(simulateMove=False, waitTime=0.3)
            return
        except Exception:  # noqa: BLE001
            pass
        for getter in ("GetInvokePattern", "GetSelectionItemPattern"):
            try:
                pattern = getattr(ctrl, getter)()
                (pattern.Invoke if getter == "GetInvokePattern" else pattern.Select)()
                return
            except Exception:  # noqa: BLE001
                continue

    def wait_click(predicate, timeout, control_type=None) -> bool:
        deadline = time.time() + timeout
        while time.time() < deadline:
            ctrl = find(predicate, control_type)
            if ctrl is not None:
                click(ctrl)
                return True
            time.sleep(1)
        return False

    def exact(*names):
        wanted = {_norm(n) for n in names}
        return lambda name: name in wanted

    def contains(*parts):
        wanted = [_norm(p) for p in parts]
        return lambda name: bool(name) and any(p in name for p in wanted)

    def type_into(predicate, value, timeout, fallback_first_edit) -> bool:
        deadline = time.time() + timeout
        while time.time() < deadline:
            edit = find(predicate, auto.EditControl) or (find(lambda _n: True, auto.EditControl) if fallback_first_edit else None)
            if edit is not None:
                try:
                    edit.Click(simulateMove=False, waitTime=0.2)
                except Exception:  # noqa: BLE001
                    edit.SetFocus()
                auto.SendKeys("{Ctrl}a", waitTime=0.05)
                auto.SendKeys("{Delete}", waitTime=0.05)
                auto.SendKeys(value, waitTime=0.02)
                return True
            time.sleep(1)
        return False

    def visible_names() -> str:
        """Nomes de botoes/links visiveis (sem segredo) para diagnostico."""
        names: list[str] = []
        try:
            for ctrl, _depth in auto.WalkControl(auto.GetRootControl(), maxDepth=12):
                if ctrl.ControlTypeName in ("ButtonControl", "HyperlinkControl", "ListItemControl") and ctrl.Name:
                    names.append(ctrl.Name[:40])
                if len(names) >= 30:
                    break
        except Exception:  # noqa: BLE001
            pass
        return ", ".join(names)

    try:
        # Tira a "cortina" da tela de bloqueio (relogio), se estiver na frente.
        width, height = auto.GetScreenSize()
        auto.Click(width // 2, height // 2, waitTime=1)

        if wait_click(exact("outro usuario", "other user"), 30):
            logger.info("Login: 'Outro usuario' selecionado.")
        time.sleep(1)

        if not wait_click(contains("opcoes de entrada", "sign-in options", "opcoes de entrar"), 20):
            logger.warning("Login: 'Opcoes de entrada' nao encontrado. Visiveis: %s", visible_names())
            return 0
        time.sleep(1)

        # Globo = provedor Web sign-in (o nome costuma citar "web").
        if not wait_click(contains("web"), 15):
            logger.warning("Login: botao do Web sign-in (globo) nao encontrado. Visiveis: %s", visible_names())
            return 0
        time.sleep(1)

        if not wait_click(exact("entrar", "sign in"), 15, auto.ButtonControl):
            logger.warning("Login: botao 'Entrar' nao encontrado. Visiveis: %s", visible_names())
            return 0
        logger.info("Login: Web sign-in aberto.")

        # Pagina da Microsoft: conta -> Avancar.
        if not type_into(contains("email", "telefone", "phone", "conta", "account"), upn, 60, True):
            logger.warning("Login: campo de e-mail nao encontrado. Visiveis: %s", visible_names())
            return 0
        logger.info("Login: conta preenchida.")
        wait_click(exact("avancar", "next"), 10, auto.ButtonControl)

        time.sleep(4)
        if not type_into(contains("temporar", "temporary", "acesso", "access pass", "senha", "password"), tap, 60, True):
            logger.warning("Login: campo do TAP nao encontrado. Visiveis: %s", visible_names())
            return 0
        logger.info("Login: TAP preenchido.")
        wait_click(exact("entrar", "sign in", "avancar", "next"), 10, auto.ButtonControl)
        logger.info("Login: enviado; aguardando a sessao do Entra.")
        return 0
    except Exception as exc:  # noqa: BLE001
        logger.warning("Login: falha na automacao da tela de login: %s", exc)
        return 0
    finally:
        tap = ""  # nao deixa o TAP na memoria alem do necessario


def current_exe(fallback: Path) -> Path:
    return Path(sys.executable) if getattr(sys, "frozen", False) else fallback
