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
import subprocess
import time
from ctypes import wintypes
from pathlib import Path

WEB_SIGNIN_KEY = r"SOFTWARE\Microsoft\PolicyManager\current\device\Authentication"
WEB_SIGNIN_VALUE = "EnableWebSignIn"
REBOOT_DELAY_SECONDS = 20
REBOOT_MESSAGE = "Ativa Workspace: reiniciando para concluir o ingresso no Microsoft Entra ID."
ENTRA_DOMAIN = "AZUREAD"

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)


# --------------------------------------------------------------------------- #
#  Web sign-in e reinicio
# --------------------------------------------------------------------------- #

def enable_web_signin(logger: logging.Logger, marker: Path) -> bool:
    """
    Liga o Web sign-in (vale depois do proximo boot). True se ficou ligado.
    Quando liga agora, grava em `marker` o horario, para saber se ja houve um
    boot depois disso (ai nao precisa reiniciar no provisionamento).
    """
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
                marker.write_text(json.dumps({"enabled_at": time.time()}), "utf-8")
                logger.info("Web sign-in habilitado (vale apos o proximo reinicio).")
        return True
    except OSError as exc:
        logger.warning("Nao foi possivel habilitar o Web sign-in: %s", exc)
        return False


def web_signin_active(marker: Path) -> bool:
    """Ligado E ja houve boot depois de ligar (sem registro = ligado antes, por fora)."""
    import winreg  # type: ignore

    try:
        with winreg.OpenKey(winreg.HKEY_LOCAL_MACHINE, WEB_SIGNIN_KEY, 0,
                            winreg.KEY_QUERY_VALUE | winreg.KEY_WOW64_64KEY) as key:
            if winreg.QueryValueEx(key, WEB_SIGNIN_VALUE)[0] != 1:
                return False
    except OSError:
        return False
    try:
        enabled_at = float(json.loads(marker.read_text("utf-8")).get("enabled_at", 0.0))
    except (OSError, ValueError, AttributeError):
        return True
    return enabled_at < boot_time()


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
