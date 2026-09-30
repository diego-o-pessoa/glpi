"""
Ativa Workspace - fase "login do usuario Entra" (depois do ingresso).

Fluxo (conduzido pelo servico, SYSTEM):
  1. depois do ingresso, troca para a tela de login ("Trocar usuario");
  2. o T.I./funcionario entra MANUALMENTE com a conta do Entra (e-mail e
     senha) em "Outro usuario". Nao liga Web sign-in, nao reinicia e nao
     digita nada: a conta fica salva como usuario normal da maquina;
  3. o servico confirma quando existe uma sessao do dominio AzureAD.
"""

from __future__ import annotations

import ctypes
import logging
import subprocess
from ctypes import wintypes
from pathlib import Path

WEB_SIGNIN_KEY = r"SOFTWARE\Microsoft\PolicyManager\current\device\Authentication"
WEB_SIGNIN_VALUE = "EnableWebSignIn"
ENTRA_DOMAIN = "AZUREAD"

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)


# --------------------------------------------------------------------------- #
#  Web sign-in (legado)
# --------------------------------------------------------------------------- #

def remove_web_signin(logger: logging.Logger, marker: Path) -> None:
    """
    Versoes anteriores ligavam o Web sign-in. Desfaz SO se foi o Workspace que
    ligou (existe o marcador); um Web sign-in ligado por fora nao e tocado.
    """
    if not marker.exists():
        return
    import winreg  # type: ignore

    try:
        with winreg.OpenKey(
            winreg.HKEY_LOCAL_MACHINE, WEB_SIGNIN_KEY, 0,
            winreg.KEY_SET_VALUE | winreg.KEY_WOW64_64KEY,
        ) as key:
            winreg.DeleteValue(key, WEB_SIGNIN_VALUE)
        logger.info("Web sign-in ligado pelo Workspace foi removido.")
    except FileNotFoundError:
        pass
    except OSError as exc:
        logger.warning("Nao foi possivel remover o Web sign-in: %s", exc)
        return
    marker.unlink(missing_ok=True)


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
