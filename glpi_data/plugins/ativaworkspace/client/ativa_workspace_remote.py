"""
Ativa Workspace - acoes remotas pedidas na pagina do computador.

O servidor manda so a CHAVE da acao (lista fixa) e parametros ja validados;
cada acao tem aqui o seu comando fixo. Nada de comando livre: os parametros
vao como argumentos separados (sem shell) ou por API do Windows.
"""

from __future__ import annotations

import ctypes
import logging
import os
import re
import shutil
import subprocess
import time
from ctypes import wintypes
from pathlib import Path

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)
SYSTEM_ROOT = Path(os.environ.get("SystemRoot", r"C:\Windows"))

# Espelha MachineAction::PROTECTED_PROCESSES (o servidor ja recusa; aqui e a 2a barreira).
PROTECTED_PROCESSES = {
    "system", "smss.exe", "csrss.exe", "wininit.exe", "winlogon.exe", "services.exe", "lsass.exe",
    "svchost.exe", "lsaiso.exe", "fontdrvhost.exe", "dwm.exe", "registry", "memory compression",
    "ativaworkspace.exe", "ativaguardian.exe", "unifiedupdater.exe", "msmpeng.exe",
}
POWER_DELAYS = {0, 60, 300, 600, 1800}
MESSAGE_TITLE = "Mensagem do T.I. - Ativa Locação"


def _run(args: list[str], timeout: int = 120) -> tuple[int, str]:
    try:
        completed = subprocess.run(args, capture_output=True, text=True, timeout=timeout,
                                   creationflags=NO_WINDOW, errors="replace")
    except subprocess.TimeoutExpired:
        return 1460, "tempo esgotado"
    except OSError as exc:
        return 1, f"nao foi possivel executar: {exc}"
    output = " ".join(((completed.stdout or "") + " " + (completed.stderr or "")).split())
    return completed.returncode, output[-160:]


def _clean(text: str, limit: int) -> str:
    return re.sub(r"[\x00-\x09\x0b-\x1f\x7f]", "", str(text or "")).strip()[:limit]


# --------------------------------------------------------------------------- #
#  Energia e mensagem
# --------------------------------------------------------------------------- #

def _power(kind: str, params: dict) -> tuple[bool, str]:
    delay = int(params.get("delay", 60) or 0)
    if delay not in POWER_DELAYS:
        return False, "tempo de espera invalido"
    message = _clean(params.get("message", ""), 200) or (
        "O T.I. vai reiniciar este computador." if kind == "restart" else "O T.I. vai desligar este computador.")
    flag = "/r" if kind == "restart" else "/s"
    # /d p:0:0 = planejado, "outro". Argumentos separados: a mensagem nunca vira comando.
    code, out = _run([str(SYSTEM_ROOT / "System32" / "shutdown.exe"), flag, "/t", str(delay),
                      "/c", message, "/d", "p:0:0"], timeout=30)
    if code not in (0, 1190):  # 1190 = ja havia um desligamento agendado
        return False, f"shutdown retornou {code}. {out}"
    when = "agora" if delay == 0 else f"em {delay // 60} min"
    return True, ("Reinicio" if kind == "restart" else "Desligamento") + f" agendado {when}; o usuario foi avisado."


def _send_message(params: dict, sessions: list[int]) -> tuple[bool, str]:
    """Caixa de mensagem na sessao do usuario (WTSSendMessage: funciona em todas as edicoes)."""
    text = _clean(params.get("message", ""), 500)
    if not text:
        return False, "mensagem vazia"
    if not sessions:
        return False, "nenhum usuario logado para receber a mensagem"
    wtsapi32 = ctypes.windll.wtsapi32
    wtsapi32.WTSSendMessageW.argtypes = [
        wintypes.HANDLE, wintypes.DWORD, wintypes.LPWSTR, wintypes.DWORD, wintypes.LPWSTR, wintypes.DWORD,
        wintypes.DWORD, wintypes.DWORD, ctypes.POINTER(wintypes.DWORD), wintypes.BOOL,
    ]
    MB_OK_INFO_TOP = 0x0 | 0x40 | 0x10000 | 0x40000  # MB_OK | ICONINFORMATION | SETFOREGROUND | TOPMOST
    sent = 0
    for session_id in sessions:
        response = wintypes.DWORD()
        title = MESSAGE_TITLE
        if wtsapi32.WTSSendMessageW(None, session_id, title, len(title) * 2, text, len(text) * 2,
                                    MB_OK_INFO_TOP, 0, ctypes.byref(response), False):
            sent += 1
    if not sent:
        return False, "o Windows nao exibiu a mensagem (WTSSendMessage falhou)"
    return True, f"Mensagem exibida para {sent} sessao(oes) de usuario."


def _kill_process(params: dict) -> tuple[bool, str]:
    name = str(params.get("process", "")).strip()
    if not re.fullmatch(r"[\w .()+-]{1,80}\.exe", name, re.IGNORECASE):
        return False, "nome de processo invalido"
    if name.lower() in PROTECTED_PROCESSES:
        return False, f"{name} e um processo do sistema e nao pode ser encerrado"
    code, out = _run(["taskkill", "/F", "/T", "/IM", name], timeout=60)
    if code == 128 or "not found" in out.lower() or "nao foi encontrado" in out.lower():
        return True, f"{name} ja nao estava em execucao."
    if code != 0:
        return False, f"taskkill retornou {code}. {out}"
    return True, f"{name} encerrado."


# --------------------------------------------------------------------------- #
#  Manutencao
# --------------------------------------------------------------------------- #

def _temp_dirs() -> list[Path]:
    dirs = [SYSTEM_ROOT / "Temp"]
    users = Path(os.environ.get("SystemDrive", "C:") + "\\") / "Users"
    try:
        for profile in users.iterdir():
            temp = profile / "AppData" / "Local" / "Temp"
            if temp.is_dir():
                dirs.append(temp)
    except OSError:
        pass
    return dirs


def _clean_temp() -> tuple[bool, str]:
    """Apaga o que esta nas pastas Temp ha mais de 1 dia (arquivos em uso ficam)."""
    cutoff = time.time() - 86400
    freed = 0
    removed = 0
    for base in _temp_dirs():
        try:
            entries = list(base.iterdir())
        except OSError:
            continue
        for entry in entries:
            try:
                if entry.is_symlink():
                    continue  # nunca segue link para fora da pasta Temp
                if entry.stat().st_mtime > cutoff:
                    continue
                if entry.is_dir():
                    size = sum(f.stat().st_size for f in entry.rglob("*") if f.is_file() and not f.is_symlink())
                    shutil.rmtree(entry, ignore_errors=True)
                    if not entry.exists():
                        freed += size
                        removed += 1
                else:
                    size = entry.stat().st_size
                    entry.unlink()
                    freed += size
                    removed += 1
            except OSError:
                continue  # em uso ou sem permissao: segue
    return True, f"{removed} item(ns) removido(s), {freed / 1048576:.0f} MB liberados."


def _gpupdate() -> tuple[bool, str]:
    code, out = _run(["gpupdate", "/force", "/wait:120"], timeout=180)
    return (code == 0), ("Politicas atualizadas." if code == 0 else f"gpupdate retornou {code}. {out}")


def _flush_dns() -> tuple[bool, str]:
    code, out = _run(["ipconfig", "/flushdns"], timeout=60)
    return (code == 0), ("Cache de DNS limpo." if code == 0 else f"ipconfig retornou {code}. {out}")


def _restart_spooler() -> tuple[bool, str]:
    _run(["net", "stop", "spooler", "/y"], timeout=90)
    code, out = _run(["net", "start", "spooler"], timeout=90)
    # 2 = o servico ja estava iniciado
    return (code in (0, 2)), ("Spooler de impressao reiniciado." if code in (0, 2) else f"net start retornou {code}. {out}")


def _sync_time() -> tuple[bool, str]:
    _run(["net", "start", "w32time"], timeout=60)  # parado em algumas maquinas
    code, out = _run(["w32tm", "/resync", "/force"], timeout=90)
    return (code == 0), ("Relogio sincronizado." if code == 0 else f"w32tm retornou {code}. {out}")


def _windows_update_scan() -> tuple[bool, str]:
    exe = SYSTEM_ROOT / "System32" / "UsoClient.exe"
    if not exe.is_file():
        return False, "UsoClient nao encontrado neste Windows"
    code, out = _run([str(exe), "StartScan"], timeout=60)
    return (code == 0), ("Busca de atualizacoes iniciada (o Windows Update segue sozinho)."
                         if code == 0 else f"UsoClient retornou {code}. {out}")


def run(kind: str, params: dict, sessions: list[int], logger: logging.Logger) -> tuple[bool, str]:
    """Executa a acao `kind`. Retorna (ok, mensagem curta para o painel)."""
    params = params if isinstance(params, dict) else {}
    handlers = {
        "restart": lambda: _power("restart", params),
        "shutdown": lambda: _power("shutdown", params),
        "message": lambda: _send_message(params, sessions),
        "kill_process": lambda: _kill_process(params),
        "clean_temp": _clean_temp,
        "gpupdate": _gpupdate,
        "flush_dns": _flush_dns,
        "restart_spooler": _restart_spooler,
        "sync_time": _sync_time,
        "windows_update_scan": _windows_update_scan,
    }
    handler = handlers.get(kind)
    if handler is None:
        return False, f"Acao nao suportada nesta versao do servico: {kind}"
    logger.info("Acao remota: %s.", kind)
    try:
        return handler()
    except Exception as exc:  # noqa: BLE001 - erro vira resultado da acao
        logger.exception("Acao remota %s falhou.", kind)
        return False, f"Erro inesperado: {exc}"
