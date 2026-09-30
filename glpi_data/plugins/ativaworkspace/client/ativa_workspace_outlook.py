"""Instalacao corporativa do Outlook PWA no Google Chrome.

O servico (SYSTEM) aplica somente politicas conhecidas do Chrome. Um helper
sem elevacao, na sessao do funcionario, abre o navegador, aguarda a PWA criada
pela politica e fixa o atalho na barra de tarefas. A autenticacao usa o SSO do
Windows/Entra (CloudAP); nenhuma senha permanente e armazenada ou digitada.
"""

from __future__ import annotations

import json
import logging
import os
import re
import subprocess
import time
import unicodedata
from pathlib import Path
from typing import Any

OUTLOOK_URL = "https://outlook.office.com/mail/"
NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)

PROGRAM_DATA = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData"))
HELPER_DIR = PROGRAM_DATA / "AtivaLocacao" / "WorkspaceHelper"

PHASE_OPENING = "OPENING_OUTLOOK"
PHASE_INSTALLING = "INSTALLING_PWA"
PHASE_SIGNING_IN = "SIGNING_IN"
PHASE_PINNING = "PINNING_TASKBAR"
PHASE_VERIFYING = "VERIFYING_CONFIGURATION"

# Fixacao na barra: o Windows bloqueia a fixacao programatica (verbo oculto
# desde o 1809). O caminho suportado e a politica "Start Layout" com um XML
# de barra de tarefas. O XML fica legivel para o usuario (o Explorer o le).
LAYOUT_DIR = PROGRAM_DATA / "AtivaLocacao" / "WorkspaceLayout"
LAYOUT_PATH = LAYOUT_DIR / "TaskbarLayout.xml"
EXPLORER_POLICY = r"SOFTWARE\Policies\Microsoft\Windows\Explorer"
# Atalho relativo ao %APPDATA% do usuario; sem "..", so dentro do Menu Iniciar.
SHORTCUT_REL_RE = re.compile(
    r"^Microsoft\\Windows\\Start Menu\\Programs\\(?:Chrome Apps\\)?[^\\/:*?\"<>|%]{1,120}\.lnk$",
    re.IGNORECASE,
)


def result_path(step_id: int) -> Path:
    return HELPER_DIR / f"outlook-step-{int(step_id)}.json"


def read_result(step_id: int) -> dict[str, Any]:
    try:
        data = json.loads(result_path(step_id).read_text("utf-8"))
    except (OSError, ValueError):
        return {}
    return data if isinstance(data, dict) else {}


def clear_result(step_id: int) -> None:
    try:
        result_path(step_id).unlink(missing_ok=True)
    except OSError:
        pass


def _write_result(step_id: int, phase: str, message: str, ok: bool | None = None, shortcut: str = "") -> None:
    """Arquivo pequeno de progresso; o servico o trata como entrada nao confiavel."""
    HELPER_DIR.mkdir(parents=True, exist_ok=True)
    payload: dict[str, Any] = {
        "step_id": int(step_id),
        "phase": phase,
        "message": str(message)[:200],
        "updated_at": int(time.time()),
    }
    if shortcut:
        payload["shortcut"] = shortcut
    if ok is not None:
        payload["done"] = True
        payload["ok"] = bool(ok)
    temporary = result_path(step_id).with_suffix(".tmp")
    temporary.write_text(json.dumps(payload, ensure_ascii=False), "utf-8")
    os.replace(temporary, result_path(step_id))


def apply_chrome_policies(outlook_url: str = OUTLOOK_URL) -> tuple[bool, str]:
    """Mescla a PWA do Outlook nas politicas HKLM sem apagar outras PWAs."""
    if outlook_url != OUTLOOK_URL:
        return False, "URL do Outlook recusada: somente o endereco corporativo predefinido e aceito."

    try:
        import winreg  # type: ignore

        path = r"SOFTWARE\Policies\Google\Chrome"
        with winreg.CreateKeyEx(
            winreg.HKEY_LOCAL_MACHINE,
            path,
            0,
            winreg.KEY_READ | winreg.KEY_WRITE | winreg.KEY_WOW64_64KEY,
        ) as key:
            # Chrome 111+: usa a conta corporativa adicionada ao Windows para
            # SSO em recursos protegidos pelo Microsoft Entra.
            winreg.SetValueEx(key, "CloudAPAuthEnabled", 0, winreg.REG_DWORD, 1)

            existing: list[dict[str, Any]] = []
            try:
                raw, value_type = winreg.QueryValueEx(key, "WebAppInstallForceList")
                if value_type != winreg.REG_SZ:
                    return False, "A politica WebAppInstallForceList existente nao e REG_SZ."
                decoded = json.loads(str(raw))
                if not isinstance(decoded, list):
                    return False, "A politica WebAppInstallForceList existente nao contem uma lista JSON."
                existing = [item for item in decoded if isinstance(item, dict)]
            except FileNotFoundError:
                pass
            except (TypeError, ValueError, json.JSONDecodeError):
                return False, "A politica WebAppInstallForceList existente possui JSON invalido; nada foi sobrescrito."

            # Preserva outras aplicacoes e substitui apenas a entrada do Outlook.
            existing = [
                item for item in existing
                if not str(item.get("url", "")).lower().startswith("https://outlook.office.com/")
            ]
            existing.append({
                "url": outlook_url,
                "default_launch_container": "window",
                "create_desktop_shortcut": True,
                "fallback_app_name": "Microsoft Outlook",
            })
            compact = json.dumps(existing, ensure_ascii=False, separators=(",", ":"))
            winreg.SetValueEx(key, "WebAppInstallForceList", 0, winreg.REG_SZ, compact)
        return True, "Politicas CloudAPAuthEnabled e WebAppInstallForceList aplicadas."
    except OSError as exc:
        return False, f"Falha ao gravar as politicas do Chrome: {exc}"


def find_chrome() -> Path | None:
    candidates = [
        Path(os.environ.get("ProgramFiles", r"C:\Program Files")) / "Google" / "Chrome" / "Application" / "chrome.exe",
        Path(os.environ.get("ProgramFiles(x86)", r"C:\Program Files (x86)")) / "Google" / "Chrome" / "Application" / "chrome.exe",
        Path(os.environ.get("LOCALAPPDATA", "")) / "Google" / "Chrome" / "Application" / "chrome.exe",
    ]
    return next((path for path in candidates if path.is_file()), None)


def chrome_installed() -> bool:
    """Checagem do servico SYSTEM (instalacoes por maquina)."""
    candidates = [
        Path(os.environ.get("ProgramFiles", r"C:\Program Files")) / "Google" / "Chrome" / "Application" / "chrome.exe",
        Path(os.environ.get("ProgramFiles(x86)", r"C:\Program Files (x86)")) / "Google" / "Chrome" / "Application" / "chrome.exe",
    ]
    return any(path.is_file() for path in candidates)


def _shortcut_candidates(started_at: float) -> list[Path]:
    appdata = Path(os.environ.get("APPDATA", ""))
    profile = Path(os.environ.get("USERPROFILE", ""))
    roots = [
        appdata / "Microsoft" / "Windows" / "Start Menu" / "Programs" / "Chrome Apps",
        appdata / "Microsoft" / "Windows" / "Start Menu" / "Programs",
        profile / "Desktop",
        Path(os.environ.get("OneDrive", "")) / "Desktop",
    ]
    named: list[Path] = []
    recent: list[Path] = []
    seen: set[str] = set()
    for root in roots:
        if not root.is_dir():
            continue
        try:
            files = list(root.glob("*.lnk"))
            if root.name == "Programs":
                files.extend(root.glob("Chrome Apps/*.lnk"))
        except OSError:
            continue
        for path in files:
            key = str(path).lower()
            if key in seen:
                continue
            seen.add(key)
            name = path.stem.lower()
            if "outlook" in name:
                named.append(path)
                continue
            try:
                if "chrome apps" in key and path.stat().st_mtime >= started_at - 5:
                    recent.append(path)
            except OSError:
                pass
    return sorted(named, key=lambda item: item.stat().st_mtime, reverse=True) + recent


def _wait_for_outlook_shortcut(started_at: float, timeout: int) -> Path | None:
    deadline = time.time() + timeout
    while time.time() < deadline:
        candidates = _shortcut_candidates(started_at)
        if candidates:
            return candidates[0]
        time.sleep(2)
    return None


def _norm(text: str) -> str:
    normalized = unicodedata.normalize("NFKD", (text or "").replace("&", "").strip().lower())
    return "".join(char for char in normalized if not unicodedata.combining(char))


def _taskbar_folder() -> Path:
    return Path(os.environ.get("APPDATA", "")) / "Microsoft" / "Internet Explorer" / "Quick Launch" / "User Pinned" / "TaskBar"


def _already_pinned(shortcut: Path) -> bool:
    folder = _taskbar_folder()
    if not folder.is_dir():
        return False
    try:
        return any("outlook" in item.stem.lower() or item.name.lower() == shortcut.name.lower()
                   for item in folder.glob("*.lnk"))
    except OSError:
        return False


def _xml_attr(text: str) -> str:
    return (text.replace("&", "&amp;").replace('"', "&quot;")
            .replace("<", "&lt;").replace(">", "&gt;"))


def apply_taskbar_layout(shortcut_rel: str) -> tuple[bool, str]:
    """
    (SYSTEM) Fixa o atalho pela politica oficial "Start Layout": XML so com a
    barra de tarefas (PinListPlacement=Append, nao mexe no Menu Iniciar nem
    remove os pins do usuario). O Explorer aplica ao iniciar/no logon.
    """
    if not SHORTCUT_REL_RE.fullmatch(shortcut_rel or "") or ".." in shortcut_rel:
        return False, "Caminho do atalho do Outlook recusado."
    link = "%APPDATA%\\" + shortcut_rel
    xml = (
        '<?xml version="1.0" encoding="utf-8"?>\n'
        '<LayoutModificationTemplate'
        ' xmlns="http://schemas.microsoft.com/Start/2014/LayoutModification"'
        ' xmlns:defaultlayout="http://schemas.microsoft.com/Start/2014/FullDefaultLayout"'
        ' xmlns:start="http://schemas.microsoft.com/Start/2014/StartLayout"'
        ' xmlns:taskbar="http://schemas.microsoft.com/Start/2014/TaskbarLayout"'
        ' Version="1">\n'
        '  <CustomTaskbarLayoutCollection PinListPlacement="Append">\n'
        '    <defaultlayout:TaskbarLayout>\n'
        '      <taskbar:TaskbarPinList>\n'
        f'        <taskbar:DesktopApp DesktopApplicationLinkPath="{_xml_attr(link)}" />\n'
        '      </taskbar:TaskbarPinList>\n'
        '    </defaultlayout:TaskbarLayout>\n'
        '  </CustomTaskbarLayoutCollection>\n'
        '</LayoutModificationTemplate>\n'
    )
    try:
        import winreg  # type: ignore

        with winreg.CreateKeyEx(
            winreg.HKEY_LOCAL_MACHINE, EXPLORER_POLICY, 0,
            winreg.KEY_READ | winreg.KEY_WRITE | winreg.KEY_WOW64_64KEY,
        ) as key:
            # Nao sobrescreve um layout corporativo de outra origem.
            try:
                current = str(winreg.QueryValueEx(key, "StartLayoutFile")[0])
            except FileNotFoundError:
                current = ""
            if current and os.path.normcase(current) != os.path.normcase(str(LAYOUT_PATH)):
                return False, f"Ja existe outra politica de layout da barra ({current}); nada foi alterado."

            LAYOUT_DIR.mkdir(parents=True, exist_ok=True)
            temporary = LAYOUT_PATH.with_suffix(".tmp")
            temporary.write_text(xml, "utf-8")
            os.replace(temporary, LAYOUT_PATH)
            winreg.SetValueEx(key, "StartLayoutFile", 0, winreg.REG_EXPAND_SZ, str(LAYOUT_PATH))
            winreg.SetValueEx(key, "LockedStartLayout", 0, winreg.REG_DWORD, 1)
        return True, "Politica de fixacao na barra de tarefas aplicada."
    except OSError as exc:
        return False, f"Falha ao aplicar a politica da barra de tarefas: {exc}"


def _layout_has(shortcut_rel: str) -> bool:
    try:
        return shortcut_rel.lower() in LAYOUT_PATH.read_text("utf-8").lower()
    except OSError:
        return False


def _restart_explorer() -> None:
    """Reinicia so o Explorer do proprio usuario, para ler o layout novo."""
    subprocess.run(["taskkill.exe", "/F", "/IM", "explorer.exe"],
                   capture_output=True, timeout=20, creationflags=NO_WINDOW)
    time.sleep(2)
    subprocess.Popen(["explorer.exe"], close_fds=True)


def _outlook_window_visible(timeout: int) -> bool:
    try:
        import uiautomation as auto  # type: ignore
    except ImportError:
        return False

    def compare(control, _depth):
        try:
            name = _norm(str(control.Name or ""))
            return "outlook" in name and not control.IsOffscreen
        except Exception:  # noqa: BLE001
            return False

    deadline = time.time() + timeout
    while time.time() < deadline:
        window = auto.WindowControl(searchDepth=2, Compare=compare)
        if window.Exists(0, 0):
            return True
        time.sleep(2)
    return False


def configure_for_current_user(step_id: int, logger: logging.Logger) -> int:
    """Entry point do helper que roda sem elevacao na sessao interativa."""
    if step_id <= 0:
        return 2
    clear_result(step_id)
    started_at = time.time()
    try:
        chrome = find_chrome()
        if chrome is None:
            _write_result(step_id, PHASE_VERIFYING, "Google Chrome nao encontrado apos a instalacao.", False)
            return 1

        _write_result(step_id, PHASE_OPENING, "Reiniciando o Chrome para carregar as politicas.")
        # A documentacao do Chrome exige reinicio apos aplicar politicas. Sem /F,
        # o encerramento fica limitado aos processos do usuario desta sessao.
        subprocess.run(
            ["taskkill.exe", "/IM", "chrome.exe", "/T"],
            capture_output=True,
            timeout=20,
            creationflags=NO_WINDOW,
        )
        time.sleep(3)

        _write_result(step_id, PHASE_INSTALLING, "Abrindo o Outlook e aguardando a instalacao forçada da PWA.")
        subprocess.Popen(
            [str(chrome), "--no-first-run", "--new-window", OUTLOOK_URL],
            close_fds=True,
            creationflags=NO_WINDOW,
        )
        shortcut = _wait_for_outlook_shortcut(started_at, 240)
        if shortcut is None:
            _write_result(
                step_id,
                PHASE_VERIFYING,
                "O Chrome nao criou o atalho do Outlook PWA em 4 minutos. Confira chrome://policy e o acesso ao Outlook.",
                False,
            )
            return 1

        _write_result(step_id, PHASE_SIGNING_IN, "Abrindo o Outlook. Com Entra, o SSO entra sozinho; senao, faca o login (ex.: com um TAP).")
        try:
            os.startfile(str(shortcut))  # noqa: S606 - atalho criado pelo Chrome
        except OSError as exc:
            _write_result(step_id, PHASE_VERIFYING, f"O atalho da PWA nao abriu: {exc}", False)
            return 1
        # Best-effort: espera a janela aparecer, mas NAO falha se o login ainda
        # for manual (sem Entra o usuario entra depois, ex.: com um TAP). A etapa
        # cuida de instalar e fixar a PWA; o login em si e do usuario.
        _outlook_window_visible(60)

        if _already_pinned(shortcut):
            _write_result(step_id, PHASE_VERIFYING, "Outlook PWA instalado e ja fixado na barra de tarefas.", True)
            return 0

        # Pede ao servico (SYSTEM) a politica de layout com este atalho e
        # espera o XML refleti-lo. O caminho vai relativo ao %APPDATA%.
        try:
            shortcut_rel = str(shortcut.resolve().relative_to(Path(os.environ.get("APPDATA", "")).resolve()))
        except ValueError:
            _write_result(step_id, PHASE_VERIFYING, "O atalho do Outlook nao esta no Menu Iniciar do usuario.", False)
            return 1
        _write_result(step_id, PHASE_PINNING, "Aplicando a fixacao do Outlook na barra de tarefas.",
                      shortcut=shortcut_rel)
        deadline = time.time() + 180
        while time.time() < deadline and not _layout_has(shortcut_rel):
            time.sleep(3)
        if not _layout_has(shortcut_rel):
            _write_result(step_id, PHASE_VERIFYING, "O servico nao aplicou a politica da barra de tarefas a tempo.", False)
            return 1

        _restart_explorer()
        deadline = time.time() + 20
        while time.time() < deadline and not _already_pinned(shortcut):
            time.sleep(2)
        pinned_now = _already_pinned(shortcut)
        _write_result(
            step_id,
            PHASE_VERIFYING,
            "Outlook PWA instalado e fixado na barra de tarefas." if pinned_now else
            "Outlook PWA instalado; a fixacao na barra foi aplicada pela politica do Windows e aparece no proximo logon.",
            True,
        )
        logger.info("Outlook PWA concluido para o usuario da sessao.")
        return 0
    except Exception as exc:  # noqa: BLE001
        logger.exception("Falha no helper do Outlook PWA.")
        try:
            _write_result(step_id, PHASE_VERIFYING, f"Falha inesperada na configuracao do Outlook PWA: {exc}", False)
        except OSError:
            pass
        return 1
