"""
Ativa Workspace - desinstalacao silenciosa de um programa (Fase 2).

O servidor manda apenas o ALVO: em que ramo do registro (scope) e em qual chave
de desinstalacao o programa esta. O comando de desinstalacao e o que o proprio
Windows registrou (QuietUninstallString/UninstallString), lido aqui. Nenhum
comando arbitrario vem da rede.
"""

from __future__ import annotations

import logging
import re
import subprocess

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)
UNINSTALL_PATH = r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall"
GUID_RE = re.compile(r"\{[0-9A-Fa-f-]{36}\}")


def _open_key(scope: str, reg_key: str):
    import winreg

    scopes = {
        "hklm64": (winreg.HKEY_LOCAL_MACHINE, winreg.KEY_WOW64_64KEY),
        "hklm32": (winreg.HKEY_LOCAL_MACHINE, winreg.KEY_WOW64_32KEY),
        "hkcu": (winreg.HKEY_CURRENT_USER, 0),
    }
    if scope not in scopes or "\\" in reg_key or "/" in reg_key:
        return None
    hive, view = scopes[scope]
    try:
        return winreg.OpenKey(hive, UNINSTALL_PATH + "\\" + reg_key, 0, winreg.KEY_READ | view)
    except OSError:
        return None


def _reg_str(key, name: str) -> str:
    import winreg
    try:
        value, _ = winreg.QueryValueEx(key, name)
        return str(value).strip()
    except OSError:
        return ""


def _run(command, timeout: int, shell: bool) -> tuple[int, str]:
    try:
        completed = subprocess.run(
            command, capture_output=True, text=True, timeout=timeout,
            shell=shell, creationflags=NO_WINDOW,
        )
    except subprocess.TimeoutExpired:
        return 1460, "tempo de desinstalacao esgotado"
    except OSError as exc:
        return 1, f"nao foi possivel executar: {exc}"
    output = (completed.stdout or "") + (completed.stderr or "")
    return completed.returncode, " ".join(output.split())[-180:]


def run_uninstall(scope: str, reg_key: str, timeout: int, logger: logging.Logger) -> tuple[bool, str]:
    key = _open_key(scope, reg_key)
    if key is None:
        return False, "Entrada de desinstalacao nao encontrada no registro."
    with key:
        quiet = _reg_str(key, "QuietUninstallString")
        normal = _reg_str(key, "UninstallString")

    # 1) String silenciosa registrada pelo proprio programa: melhor caminho.
    if quiet:
        logger.info("Uninstall: usando QuietUninstallString.")
        code, tail = _run(quiet, timeout, shell=True)
        return _ok(code), _msg(code, tail)

    if not normal:
        return False, "O programa nao registrou como desinstalar."

    # 2) MSI: forca /qn /norestart pelo codigo do produto.
    if "msiexec" in normal.lower():
        guid = GUID_RE.search(normal)
        if guid:
            logger.info("Uninstall: MSI silencioso (%s).", guid.group(0))
            code, tail = _run(["msiexec", "/x", guid.group(0), "/qn", "/norestart"], timeout, shell=False)
            return _ok(code), _msg(code, tail)

    # 3) Sem string silenciosa: tenta as flags silenciosas comuns.
    for flag in ("/S", "/silent", "/qn", "/quiet", "/VERYSILENT /NORESTART"):
        logger.info("Uninstall: tentando '%s' com %s.", flag, reg_key)
        code, tail = _run(f'{normal} {flag}', timeout, shell=True)
        if _ok(code):
            return True, _msg(code, tail)
    return False, "Nao foi possivel desinstalar em silencio (sem string silenciosa)."


def _ok(code: int) -> bool:
    return code in (0, 1605, 1641, 3010)  # 1605 = ja nao instalado


def _msg(code: int, tail: str) -> str:
    if _ok(code):
        return "Programa desinstalado." if code != 1605 else "Programa já não estava instalado."
    return f"Desinstalacao terminou com codigo {code}. {tail}".strip()
