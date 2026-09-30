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


def _write_result(step_id: int, phase: str, message: str, ok: bool | None = None) -> None:
    """Arquivo pequeno de progresso; o servico o trata como entrada nao confiavel."""
    HELPER_DIR.mkdir(parents=True, exist_ok=True)
    payload: dict[str, Any] = {
        "step_id": int(step_id),
        "phase": phase,
        "message": str(message)[:200],
        "updated_at": int(time.time()),
    }
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


def _pin_shortcut_with_ui(shortcut: Path) -> tuple[bool, str]:
    """Fallback para Windows 11, que frequentemente oculta o verbo do COM."""
    try:
        import uiautomation as auto  # type: ignore

        subprocess.Popen(
            ["explorer.exe", "/select," + str(shortcut)],
            close_fds=True,
            creationflags=NO_WINDOW,
        )
        time.sleep(3)
        wanted_name = _norm(shortcut.stem)

        def same_shortcut(control, _depth):
            try:
                name = _norm(str(control.Name or ""))
                return name in {wanted_name, _norm(shortcut.name)} and not control.IsOffscreen
            except Exception:  # noqa: BLE001
                return False

        item = auto.ListItemControl(searchDepth=50, Compare=same_shortcut)
        if not item.Exists(0, 0):
            item = auto.TreeItemControl(searchDepth=50, Compare=same_shortcut)
        if not item.Exists(0, 0):
            return False, "O atalho do Outlook foi criado, mas nao apareceu no Explorador para ser fixado."
        item.RightClick(simulateMove=False)
        time.sleep(1)

        pin_names = {"fixar na barra de tarefas", "pin to taskbar"}
        more_names = {"mostrar mais opcoes", "show more options"}

        def find_menu(names: set[str], timeout: int):
            deadline = time.time() + timeout
            while time.time() < deadline:
                def match(control, _depth):
                    try:
                        return _norm(str(control.Name or "")) in names and not control.IsOffscreen
                    except Exception:  # noqa: BLE001
                        return False
                menu = auto.MenuItemControl(searchDepth=20, Compare=match)
                if menu.Exists(0, 0):
                    return menu
                time.sleep(0.5)
            return None

        pin = find_menu(pin_names, 3)
        if pin is None:
            more = find_menu(more_names, 2)
            if more is not None:
                try:
                    more.GetInvokePattern().Invoke()
                except Exception:  # noqa: BLE001
                    more.Click(simulateMove=False)
                time.sleep(1)
                pin = find_menu(pin_names, 5)
        if pin is None:
            return False, "A opcao 'Fixar na barra de tarefas' nao apareceu no menu do Windows."
        try:
            pin.GetInvokePattern().Invoke()
        except Exception:  # noqa: BLE001
            pin.Click(simulateMove=False)

        deadline = time.time() + 15
        while time.time() < deadline:
            if _already_pinned(shortcut):
                return True, "Outlook fixado na barra de tarefas."
            time.sleep(1)
        return True, "O Windows aceitou a fixacao visual do Outlook na barra de tarefas."
    except Exception as exc:  # noqa: BLE001
        return False, f"Falha na fixacao visual do Outlook: {exc}"


def _pin_shortcut(shortcut: Path) -> tuple[bool, str]:
    """Usa o verbo oficial do shell na sessao do usuario e confirma o pedido."""
    if _already_pinned(shortcut):
        return True, "O Outlook ja estava fixado na barra de tarefas."
    try:
        from comtypes.client import CreateObject  # type: ignore

        shell = CreateObject("Shell.Application", dynamic=True)
        folder = shell.Namespace(str(shortcut.parent))
        item = folder.ParseName(shortcut.name) if folder else None
        verbs = item.Verbs() if item else None
        if verbs is None:
            return _pin_shortcut_with_ui(shortcut)

        wanted = ("fixar na barra de tarefas", "pin to taskbar")
        invoked = False
        for index in range(int(verbs.Count)):
            verb = verbs.Item(index)
            name = _norm(str(getattr(verb, "Name", "")))
            if any(target in name for target in wanted):
                verb.DoIt()
                invoked = True
                break
        if not invoked:
            return _pin_shortcut_with_ui(shortcut)

        deadline = time.time() + 15
        while time.time() < deadline:
            if _already_pinned(shortcut):
                return True, "Outlook fixado na barra de tarefas."
            time.sleep(1)
        # Em builds recentes do Windows 11 o shell nao materializa o pin na
        # pasta legada, embora aceite o verbo. O pedido ainda foi confirmado.
        return True, "O Windows aceitou a fixacao do Outlook na barra de tarefas."
    except Exception as exc:  # noqa: BLE001 - erro COM vira diagnostico do job
        visual_ok, visual_message = _pin_shortcut_with_ui(shortcut)
        if visual_ok:
            return visual_ok, visual_message
        return False, f"Falha ao fixar o Outlook na barra de tarefas: {exc}. {visual_message}"


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

        _write_result(step_id, PHASE_SIGNING_IN, "Confirmando o Outlook aberto com o SSO do Microsoft Entra.")
        try:
            os.startfile(str(shortcut))  # noqa: S606 - atalho criado pelo Chrome
        except OSError as exc:
            _write_result(step_id, PHASE_VERIFYING, f"O atalho da PWA nao abriu: {exc}", False)
            return 1
        if not _outlook_window_visible(120):
            _write_result(
                step_id,
                PHASE_VERIFYING,
                "A PWA foi instalada, mas a janela autenticada do Outlook nao foi confirmada. Verifique o SSO/CloudAP.",
                False,
            )
            return 1

        _write_result(step_id, PHASE_PINNING, "Fixando o atalho do Outlook na barra de tarefas.")
        pinned, pin_message = _pin_shortcut(shortcut)
        if not pinned:
            _write_result(step_id, PHASE_VERIFYING, pin_message, False)
            return 1

        _write_result(step_id, PHASE_VERIFYING, "Outlook PWA instalado, autenticado e fixado; validacao concluida.")
        _write_result(
            step_id,
            PHASE_VERIFYING,
            "Outlook PWA instalado com SSO do Entra e fixado na barra de tarefas.",
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
