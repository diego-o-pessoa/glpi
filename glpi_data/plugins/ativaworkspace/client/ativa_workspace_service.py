"""
Ativa Workspace - servico Windows.

Independente do Ativa Updater: uma falha aqui nunca afeta a atualizacao do
pacote unificado. Roda como SYSTEM e conduz a etapa ENTRA_LOGIN de um
provisionamento:

  1. pergunta ao Workspace se ha uma etapa Entra para esta maquina;
  2. `dsregcmd /status`: se ja ingressado no tenant esperado -> SUCCESS, sem UI;
  3. se nao, abre `ms-settings:workplace` NA SESSAO DO USUARIO (o servico nao
     tem tela), e reporta WAITING_INTERVENTION com as instrucoes;
  4. o tecnico entra pelo Ativa Remote, clica Conectar -> Ingressar no Entra e
     digita as credenciais direto na tela da Microsoft (nada passa pelo servico);
  5. o servico verifica o `dsregcmd` periodicamente e conclui SUCCESS quando o
     ingresso no tenant certo e confirmado.

Nenhuma credencial Microsoft e lida, digitada, guardada ou registrada.
"""

from __future__ import annotations

import argparse
import ctypes
import json
import logging
import os
import re
import sys
import time
from ctypes import wintypes
from pathlib import Path
from typing import Any

import ativa_workspace_entra as lib

SERVICE_NAME = "AtivaWorkspace"
SERVICE_DISPLAY_NAME = "Ativa Workspace"
SERVICE_DESCRIPTION = "Provisionamento Ativa: conduz a etapa de ingresso no Microsoft Entra ID."
WORKSPACE_AGENT_VERSION = "1.2.0"

PROGRAM_DATA = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData"))
PRODUCT_DIR = PROGRAM_DATA / "AtivaLocacao" / "Workspace"
LOG_DIR = PRODUCT_DIR / "logs"
STATE_PATH = PRODUCT_DIR / "entra-state.json"
SERVICE_EXE = PRODUCT_DIR / "AtivaWorkspace.exe"

POLL_SECONDS = 15               # ritmo normal do loop
WAIT_HUMAN_TIMEOUT = 30 * 60    # desiste de aguardar o tecnico depois disto
OPEN_UI_MIN_INTERVAL = 120      # nao reabre a janela com mais frequencia que isto
MUTEX_NAME = r"Global\AtivaWorkspaceService"

# Subestados (espelham EntraStep.php).
PRECHECK = "PRECHECK"
OPENING_SETTINGS = "OPENING_SETTINGS"
WAITING_HUMAN = "WAITING_HUMAN"
VERIFYING_JOIN = "VERIFYING_JOIN"
SUCCESS = "SUCCESS"
FAILED = "FAILED"


def configure_logging(debug: bool = False) -> logging.Logger:
    LOG_DIR.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger("ativa-workspace")
    if logger.handlers:
        return logger
    logger.setLevel(logging.INFO)
    handler = logging.FileHandler(LOG_DIR / "service.log", encoding="utf-8")
    handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s %(message)s"))
    logger.addHandler(handler)
    if debug:
        logger.addHandler(logging.StreamHandler())
    return logger


class SingleInstance:
    """Impede duas instancias do servico ao mesmo tempo."""

    def __init__(self) -> None:
        self._handle = ctypes.windll.kernel32.CreateMutexW(None, False, MUTEX_NAME)
        self._owned = ctypes.get_last_error() != 183  # ERROR_ALREADY_EXISTS

    def __enter__(self) -> "SingleInstance":
        if not self._owned:
            raise RuntimeError("Outra instancia do Ativa Workspace ja esta em execucao.")
        return self

    def __exit__(self, *_exc: object) -> None:
        if self._handle:
            ctypes.windll.kernel32.CloseHandle(self._handle)


def load_json(path: Path) -> dict[str, Any]:
    try:
        return json.loads(path.read_text("utf-8"))
    except (OSError, ValueError):
        return {}


def save_json(path: Path, value: dict[str, Any]) -> None:
    try:
        PRODUCT_DIR.mkdir(parents=True, exist_ok=True)
        path.write_text(json.dumps(value), encoding="utf-8")
    except OSError:
        pass


# --------------------------------------------------------------------------- #
#  Abrir "Acessar trabalho ou escola" na sessao do usuario
# --------------------------------------------------------------------------- #

WTS_ACTIVE = 0


def user_sessions() -> list[int]:
    """
    Sessoes com usuario logado: a de console (tela fisica) primeiro, depois as
    ativas (ex.: RDP). Maquina acessada so por RDP nao tem ninguem na console.
    """
    wtsapi32 = ctypes.windll.wtsapi32
    kernel32 = ctypes.windll.kernel32
    kernel32.WTSGetActiveConsoleSessionId.restype = wintypes.DWORD

    class WTS_SESSION_INFOW(ctypes.Structure):
        _fields_ = [
            ("SessionId", wintypes.DWORD),
            ("pWinStationName", wintypes.LPWSTR),
            ("State", ctypes.c_int),
        ]

    candidates: list[int] = []
    console = int(kernel32.WTSGetActiveConsoleSessionId())
    if console != 0xFFFFFFFF:
        candidates.append(console)

    info = ctypes.c_void_p()
    count = wintypes.DWORD()
    if wtsapi32.WTSEnumerateSessionsW(None, 0, 1, ctypes.byref(info), ctypes.byref(count)):
        try:
            array = ctypes.cast(info, ctypes.POINTER(WTS_SESSION_INFOW))
            for i in range(count.value):
                entry = array[i]
                if entry.State == WTS_ACTIVE and entry.SessionId != 0 and entry.SessionId not in candidates:
                    candidates.append(int(entry.SessionId))
        finally:
            wtsapi32.WTSFreeMemory(info)

    sessions: list[int] = []
    for session_id in candidates:
        token = wintypes.HANDLE()
        if wtsapi32.WTSQueryUserToken(session_id, ctypes.byref(token)):
            kernel32.CloseHandle(token)
            sessions.append(session_id)
    return sessions


def launch_in_session(session_id: int, arguments: str, logger: logging.Logger) -> bool:
    """
    Cria este mesmo exe na sessao do usuario, com os argumentos dados
    (ex.: --open-workplace). Mesmo mecanismo que o Ativa Updater usa para o
    Wallpaper. Nada sensivel na linha de comando.
    """
    advapi32 = ctypes.windll.advapi32
    kernel32 = ctypes.windll.kernel32
    userenv = ctypes.windll.userenv
    wtsapi32 = ctypes.windll.wtsapi32

    class STARTUPINFOW(ctypes.Structure):
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

    class PROCESS_INFORMATION(ctypes.Structure):
        _fields_ = [
            ("hProcess", wintypes.HANDLE), ("hThread", wintypes.HANDLE),
            ("dwProcessId", wintypes.DWORD), ("dwThreadId", wintypes.DWORD),
        ]

    advapi32.CreateProcessAsUserW.argtypes = [
        wintypes.HANDLE, wintypes.LPCWSTR, wintypes.LPWSTR, ctypes.c_void_p, ctypes.c_void_p,
        wintypes.BOOL, wintypes.DWORD, ctypes.c_void_p, wintypes.LPCWSTR,
        ctypes.POINTER(STARTUPINFOW), ctypes.POINTER(PROCESS_INFORMATION),
    ]
    advapi32.CreateProcessAsUserW.restype = wintypes.BOOL

    token = wintypes.HANDLE()
    if not wtsapi32.WTSQueryUserToken(session_id, ctypes.byref(token)):
        logger.warning("Sem token de usuario na sessao %s (erro %s).", session_id, kernel32.GetLastError())
        return False

    env = ctypes.c_void_p()
    try:
        if not userenv.CreateEnvironmentBlock(ctypes.byref(env), token, False):
            env = ctypes.c_void_p()

        exe = Path(sys.executable) if getattr(sys, "frozen", False) else SERVICE_EXE
        command = ctypes.create_unicode_buffer(f'"{exe}" {arguments}')

        si = STARTUPINFOW()
        si.cb = ctypes.sizeof(STARTUPINFOW)
        si.lpDesktop = "winsta0\\default"
        pi = PROCESS_INFORMATION()

        # Pasta de trabalho acessivel ao usuario (a do exe empacotado e o
        # temporario do SYSTEM, que o usuario nao abre).
        working_dir = str(Path(os.environ.get("SystemRoot", r"C:\Windows")) / "System32")
        CREATE_UNICODE_ENVIRONMENT = 0x00000400
        CREATE_NO_WINDOW = 0x08000000

        ok = advapi32.CreateProcessAsUserW(
            token, None, command, None, None, False,
            CREATE_UNICODE_ENVIRONMENT | CREATE_NO_WINDOW,
            env if env.value else None, working_dir,
            ctypes.byref(si), ctypes.byref(pi),
        )
        if not ok:
            logger.warning("CreateProcessAsUserW falhou na sessao %s (erro %s).", session_id, kernel32.GetLastError())
            return False
        kernel32.CloseHandle(pi.hProcess)
        kernel32.CloseHandle(pi.hThread)
        return True
    finally:
        if env.value:
            userenv.DestroyEnvironmentBlock(env)
        kernel32.CloseHandle(token)


def open_workplace_settings(logger: logging.Logger) -> bool:
    """Abre a tela em pelo menos uma sessao de usuario. True se alguma abriu."""
    opened = False
    for session_id in user_sessions():
        if launch_in_session(session_id, "--open-workplace", logger):
            opened = True
    return opened


# Troca com o helper (sessao do usuario): fica FORA da pasta Workspace (restrita
# a SYSTEM/Admins). Guarda o TAP - de uso unico e curta duracao - so ate o helper
# ler e apagar. O helper roda como o usuario, por isso precisa de acesso.
HELPER_DIR = PROGRAM_DATA / "AtivaLocacao" / "WorkspaceHelper"
HELPER_PAYLOAD = HELPER_DIR / "entra.json"


def write_helper_payload(data: dict[str, Any], logger: logging.Logger) -> None:
    import subprocess
    try:
        HELPER_DIR.mkdir(parents=True, exist_ok=True)
        subprocess.run(
            ["icacls", str(HELPER_DIR), "/inheritance:r",
             "/grant:r", "*S-1-5-18:(OI)(CI)F", "/grant:r", "*S-1-5-32-544:(OI)(CI)F",
             "/grant:r", "*S-1-5-32-545:(OI)(CI)M"],
            capture_output=True, timeout=30, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        HELPER_PAYLOAD.write_text(json.dumps(data), encoding="utf-8")
    except (OSError, subprocess.SubprocessError):
        logger.exception("Nao foi possivel preparar os dados do helper.")


def clear_helper_payload() -> None:
    HELPER_PAYLOAD.unlink(missing_ok=True)


# --------------------------------------------------------------------------- #
#  Loop do servico
# --------------------------------------------------------------------------- #

class WorkspaceRuntime:
    def __init__(self) -> None:
        self.stop_event = _StopEvent()
        self._last_reason = ""

    def run(self, logger: logging.Logger) -> None:
        logger.info("Ativa Workspace %s iniciado.", WORKSPACE_AGENT_VERSION)
        while not self.stop_event.is_set():
            try:
                self.tick(logger)
            except Exception:  # noqa: BLE001 - nada derruba o loop
                logger.exception("Falha inesperada no ciclo do Workspace.")
            self.stop_event.wait(POLL_SECONDS)

    def _idle(self, logger: logging.Logger, reason: str) -> None:
        if reason != self._last_reason:
            self._last_reason = reason
            logger.info("Workspace: %s", reason)

    def tick(self, logger: logging.Logger) -> None:
        config = lib.load_config()
        if config is None:
            self._idle(logger, f"inativo: {lib.CONFIG_PATH} ausente ou sem api_url/api_token")
            return

        try:
            guid = lib.machine_guid()
        except OSError:
            self._idle(logger, "nao foi possivel ler o MachineGuid")
            return

        api = lib.WorkspaceApi(config["api_url"], config["api_token"], guid)
        status, step = api.next_step()
        if status == 204:
            self._idle(logger, "ok, nenhuma etapa Entra pendente")
            return
        if status != 200 or step is None:
            self._idle(logger, lib.describe_http_error(status, step))
            return
        if step.get("type") != "ENTRA_LOGIN":
            return

        step_id = int(step.get("step_id", 0))
        entra = step.get("entra", {}) if isinstance(step.get("entra"), dict) else {}
        self._handle_entra(logger, api, step_id, entra)

    def _handle_entra(self, logger: logging.Logger, api: "lib.WorkspaceApi", step_id: int, entra: dict) -> None:
        expected_tenant = str(entra.get("expected_tenant", ""))
        expected_domain = str(entra.get("expected_domain", ""))

        # 1) Verificacao (nao abre UI).
        api.progress(step_id, PRECHECK, "dsregcmd /status")
        fields = lib.parse_dsregcmd(lib.run_dsregcmd())
        joined, reason = lib.evaluate_join(fields, expected_tenant, expected_domain)
        logger.info("Entra: AzureAdJoined=%s (%s)", fields.get("AzureAdJoined", "?"), reason)

        if joined:
            api.result(step_id, SUCCESS, "Dispositivo ingressado no Microsoft Entra ID.", {
                "azure_ad_joined": True,
                "device_id": fields.get("DeviceId", ""),
                "tenant_id": fields.get("TenantId", ""),
            })
            STATE_PATH.unlink(missing_ok=True)
            return

        state = load_json(STATE_PATH)
        if state.get("step_id") != step_id:
            state = {"step_id": step_id, "started_at": time.time(), "last_open": 0.0}

        # 2) Ja aguardando o tecnico: continua verificando ate o timeout.
        current = str(entra.get("substate", ""))
        if current in (WAITING_HUMAN, VERIFYING_JOIN):
            if time.time() - float(state.get("started_at", time.time())) > WAIT_HUMAN_TIMEOUT:
                api.result(step_id, FAILED, "Tempo esgotado aguardando a autenticacao.", {"azure_ad_joined": False})
                STATE_PATH.unlink(missing_ok=True)
                return
            api.progress(step_id, VERIFYING_JOIN, "Aguardando a autenticacao do tecnico")
            save_json(STATE_PATH, state)
            return

        # 3) Nao ingressado e ainda nao aguardando: abre a tela na sessao do usuario.
        if not user_sessions():
            api.progress(step_id, OPENING_SETTINGS, "Sem usuario logado; aguardando login")
            save_json(STATE_PATH, state)
            return

        if time.time() - float(state.get("last_open", 0.0)) > OPEN_UI_MIN_INTERVAL:
            # Gera o TAP (senha temporaria de uso unico) e entrega ao helper, que
            # roda na sessao do usuario e digita conta + TAP na tela do Entra.
            tap = api.request_tap(step_id)
            if tap and tap.get("tap"):
                write_helper_payload({"upn": tap.get("upn", ""), "tap": tap["tap"]}, logger)
                logger.info("Entra: TAP obtido; abrindo o fluxo automatico.")
            else:
                clear_helper_payload()
                logger.warning("Entra: sem TAP (Graph nao configurado?); abrindo so a tela.")
            opened = open_workplace_settings(logger)
            state["last_open"] = time.time()
            logger.info("Entra: abertura automatica %s.", "solicitada" if opened else "falhou")
        state["started_at"] = time.time()
        save_json(STATE_PATH, state)

        api.progress(step_id, OPENING_SETTINGS, "Abrindo o ingresso no Microsoft Entra ID")
        api.result(step_id, WAITING_HUMAN,
                   "Ingressando no Microsoft Entra ID automaticamente. Se pedir confirmacao, acompanhe pelo Ativa Remote.",
                   {"azure_ad_joined": False})


class _StopEvent:
    """threading.Event minimo (evita import so para isto)."""

    def __init__(self) -> None:
        import threading
        self._event = threading.Event()

    def set(self) -> None:
        self._event.set()

    def is_set(self) -> bool:
        return self._event.is_set()

    def wait(self, timeout: float) -> None:
        self._event.wait(timeout)


# --------------------------------------------------------------------------- #
#  Componente da sessao do usuario
# --------------------------------------------------------------------------- #

def _read_helper_payload() -> dict[str, str]:
    """Le e APAGA o arquivo de troca (conta + TAP). O TAP so vive aqui na memoria."""
    try:
        data = json.loads(HELPER_PAYLOAD.read_text("utf-8"))
    except (OSError, ValueError):
        return {}
    finally:
        HELPER_PAYLOAD.unlink(missing_ok=True)
    return data if isinstance(data, dict) else {}


def open_workplace_now() -> int:
    """
    Roda NA SESSAO DO USUARIO. Abre "Acessar trabalho ou escola", clica em
    Conectar -> Ingressar no Microsoft Entra ID e, se houver um TAP entregue
    pelo servico, digita a CONTA e o TAP (senha temporaria de uso unico) na tela
    da Microsoft. O TAP nunca e registrado em log.
    """
    logger = configure_logging(False)
    payload = _read_helper_payload()
    upn = str(payload.get("upn", "")).strip()
    tap = str(payload.get("tap", ""))

    try:
        os.startfile("ms-settings:workplace")  # noqa: S606 - URI oficial do Windows
    except OSError:
        logger.warning("Nao foi possivel abrir ms-settings:workplace.")
        return 1

    try:
        import uiautomation as auto  # type: ignore
    except ImportError:
        logger.warning("uiautomation ausente: fluxo manual.")
        return 0

    connect_texts = ("conectar", "connect")
    join_texts = ("microsoft entra id", "azure active directory")

    def click_by_text(control_type, texts, timeout):
        deadline = time.time() + timeout
        while time.time() < deadline:
            try:
                for ctrl in control_type(searchDepth=40):
                    name = (ctrl.Name or "").strip().lower()
                    if name and any(t in name for t in texts):
                        try:
                            ctrl.GetInvokePattern().Invoke()
                        except Exception:  # noqa: BLE001
                            ctrl.Click(simulateMove=False)
                        return True
            except Exception:  # noqa: BLE001
                pass
            time.sleep(1)
        return False

    def type_into_edit(hints, value, timeout, is_secret):
        """Acha um campo de texto (por nome/placeholder) e digita, sem registrar o valor."""
        deadline = time.time() + timeout
        while time.time() < deadline:
            try:
                for edit in auto.EditControl(searchDepth=40):
                    name = (edit.Name or "").strip().lower()
                    if any(h in name for h in hints):
                        edit.SetFocus()
                        try:
                            edit.GetValuePattern().SetValue(value)
                        except Exception:  # noqa: BLE001 - campos de senha nao aceitam SetValue
                            edit.SendKeys("{Ctrl}a{Delete}", waitTime=0.05)
                            edit.SendKeys(value, waitTime=0.02)
                        logger.info("Campo %s preenchido.", "de senha" if is_secret else name or "(sem nome)")
                        return True
            except Exception:  # noqa: BLE001
                pass
            time.sleep(1)
        return False

    def click_next():
        return (click_by_text(auto.ButtonControl, ("avancar", "avançar", "next", "entrar", "sign in", "concluir", "done"), 8))

    try:
        auto.uiautomation.SetGlobalSearchTimeout(2)
        if not click_by_text(auto.ButtonControl, connect_texts, 25):
            logger.warning("Botao 'Conectar' nao encontrado.")
            return 0
        if not (click_by_text(auto.HyperlinkControl, join_texts, 20)
                or click_by_text(auto.TextControl, join_texts, 5)
                or click_by_text(auto.ButtonControl, join_texts, 5)):
            logger.warning("Opcao 'Ingressar no Microsoft Entra ID' nao encontrada.")
            return 0

        if not tap or not upn:
            logger.info("Sem TAP/conta: tela aberta para preenchimento manual.")
            return 0

        # Tela da Microsoft: conta -> Avancar -> TAP -> Avancar.
        email_hints = ("email", "e-mail", "someone@example.com", "conta", "usuario", "usuário", "account")
        if not type_into_edit(email_hints, upn, 40, is_secret=False):
            logger.warning("Campo de e-mail nao encontrado; preenchimento manual.")
            return 0
        click_next()

        tap_hints = ("senha", "password", "codigo", "código", "passcode", "acesso", "pass")
        if not type_into_edit(tap_hints, tap, 40, is_secret=True):
            logger.warning("Campo de senha/TAP nao encontrado; preenchimento manual.")
            return 0
        click_next()
        logger.info("Conta e TAP enviados; aguardando o Windows concluir o ingresso.")
        return 0
    except Exception as exc:  # noqa: BLE001
        logger.warning("Falha na automacao da tela do Entra: %s", exc)
        return 0
    finally:
        tap = ""  # nao deixa o TAP na memoria alem do necessario


# --------------------------------------------------------------------------- #
#  Instalacao do servico
# --------------------------------------------------------------------------- #

def run_sc(*args: str) -> int:
    import subprocess
    completed = subprocess.run(["sc.exe", *args], capture_output=True, text=True,
                               creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0))
    return completed.returncode


def service_is_running() -> bool:
    """Confirma o estado RUNNING; `sc start` pode retornar antes de o processo cair."""
    import subprocess
    try:
        completed = subprocess.run(
            ["sc.exe", "query", SERVICE_NAME], capture_output=True, text=True, timeout=15,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
    except (OSError, subprocess.SubprocessError):
        return False
    return completed.returncode == 0 and re.search(
        r"(?:STATE|ESTADO)\s*:\s*4\b", completed.stdout or "", re.IGNORECASE
    ) is not None


def write_configuration(source: Path, logger: logging.Logger) -> None:
    """Grava config.json a partir do JSON baixado, com acesso restrito."""
    data = json.loads(source.read_text("utf-8"))
    if not str(data.get("api_url", "")).startswith("https://"):
        raise ValueError("api_url invalida no config.")
    PRODUCT_DIR.mkdir(parents=True, exist_ok=True)
    lib.CONFIG_PATH.write_text(json.dumps({
        "api_url": str(data["api_url"]).rstrip("/"),
        "api_token": str(data.get("api_token", "")),
        "verify_tls": True,
    }), encoding="utf-8")
    harden_product_dir(logger)
    logger.info("Config do Workspace gravada em %s.", lib.CONFIG_PATH)


def harden_product_dir(logger: logging.Logger) -> None:
    """So SYSTEM e Administradores acessam a pasta (o token fica ali)."""
    import subprocess
    try:
        subprocess.run(
            ["icacls", str(PRODUCT_DIR), "/inheritance:r",
             "/grant:r", "*S-1-5-18:(OI)(CI)F", "/grant:r", "*S-1-5-32-544:(OI)(CI)F"],
            capture_output=True, timeout=30, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
    except (OSError, subprocess.SubprocessError):
        logger.warning("Nao foi possivel restringir a pasta %s.", PRODUCT_DIR)


def install_service(logger: logging.Logger) -> int:
    executable = Path(sys.executable) if getattr(sys, "frozen", False) else SERVICE_EXE
    if not executable.is_file():
        raise RuntimeError(f"Executavel nao encontrado: {executable}")
    PRODUCT_DIR.mkdir(parents=True, exist_ok=True)
    LOG_DIR.mkdir(parents=True, exist_ok=True)

    exists = run_sc("query", SERVICE_NAME) == 0
    if exists:
        run_sc("stop", SERVICE_NAME)
        configured = run_sc("config", SERVICE_NAME, "binPath=", f'"{executable}" --service',
                            "start=", "auto", "DisplayName=", SERVICE_DISPLAY_NAME)
        if configured != 0:
            raise RuntimeError(f"Nao foi possivel configurar o servico {SERVICE_NAME} (sc.exe={configured}).")
    else:
        created = run_sc("create", SERVICE_NAME, "binPath=", f'"{executable}" --service',
                         "start=", "auto", "DisplayName=", SERVICE_DISPLAY_NAME)
        if created != 0:
            raise RuntimeError(f"Nao foi possivel criar o servico {SERVICE_NAME} (sc.exe={created}).")
    run_sc("description", SERVICE_NAME, SERVICE_DESCRIPTION)
    run_sc("failure", SERVICE_NAME, "reset=", "86400",
           "actions=", "restart/60000/restart/60000/restart/60000")
    started = run_sc("start", SERVICE_NAME)
    if started != 0:
        raise RuntimeError(f"Nao foi possivel iniciar o servico {SERVICE_NAME} (sc.exe={started}).")
    time.sleep(2)
    if not service_is_running():
        raise RuntimeError(
            f"O servico {SERVICE_NAME} iniciou e encerrou. Consulte {LOG_DIR / 'service.log'}."
        )
    logger.info("Servico %s registrado e iniciado.", SERVICE_NAME)
    return 0


def uninstall_service(logger: logging.Logger) -> int:
    if run_sc("query", SERVICE_NAME) != 0:
        logger.info("Servico %s nao esta instalado.", SERVICE_NAME)
        return 0
    run_sc("stop", SERVICE_NAME)
    run_sc("delete", SERVICE_NAME)
    logger.info("Servico %s removido.", SERVICE_NAME)
    return 0


# --------------------------------------------------------------------------- #
#  Service Control Manager
# --------------------------------------------------------------------------- #

SERVICE_WIN32_OWN_PROCESS = 0x10
SERVICE_START_PENDING = 2
SERVICE_RUNNING = 4
SERVICE_STOP_PENDING = 3
SERVICE_STOPPED = 1
SERVICE_ACCEPT_STOP = 0x1
SERVICE_ACCEPT_SHUTDOWN = 0x4
SERVICE_CONTROL_STOP = 1
SERVICE_CONTROL_SHUTDOWN = 5


class SERVICE_STATUS(ctypes.Structure):
    _fields_ = [
        ("dwServiceType", wintypes.DWORD), ("dwCurrentState", wintypes.DWORD),
        ("dwControlsAccepted", wintypes.DWORD), ("dwWin32ExitCode", wintypes.DWORD),
        ("dwServiceSpecificExitCode", wintypes.DWORD), ("dwCheckPoint", wintypes.DWORD),
        ("dwWaitHint", wintypes.DWORD),
    ]


if os.name == "nt":
    HANDLER_EX = ctypes.WINFUNCTYPE(wintypes.DWORD, wintypes.DWORD, wintypes.DWORD, wintypes.LPVOID, wintypes.LPVOID)
    SERVICE_MAIN = ctypes.WINFUNCTYPE(None, wintypes.DWORD, ctypes.POINTER(wintypes.LPWSTR))

    class SERVICE_TABLE_ENTRY(ctypes.Structure):
        _fields_ = [("lpServiceName", wintypes.LPWSTR), ("lpServiceProc", SERVICE_MAIN)]

    _advapi32 = ctypes.WinDLL("advapi32", use_last_error=True)
    _advapi32.RegisterServiceCtrlHandlerExW.restype = wintypes.HANDLE
    _advapi32.SetServiceStatus.argtypes = [wintypes.HANDLE, ctypes.POINTER(SERVICE_STATUS)]
    _advapi32.StartServiceCtrlDispatcherW.argtypes = [ctypes.POINTER(SERVICE_TABLE_ENTRY)]
    _service_handle = None
    _runtime: WorkspaceRuntime | None = None

    def set_service_status(state: int, accepted: int = 0, wait_hint: int = 0) -> None:
        status = SERVICE_STATUS(SERVICE_WIN32_OWN_PROCESS, state, accepted, 0, 0, 0, wait_hint)
        if _service_handle:
            _advapi32.SetServiceStatus(_service_handle, ctypes.byref(status))

    @HANDLER_EX
    def service_handler(control, _type, _data, _context):
        if control in (SERVICE_CONTROL_STOP, SERVICE_CONTROL_SHUTDOWN):
            set_service_status(SERVICE_STOP_PENDING, wait_hint=15000)
            if _runtime is not None:
                _runtime.stop_event.set()
        return 0

    @SERVICE_MAIN
    def service_main(_argc, _argv):
        global _service_handle, _runtime
        _service_handle = _advapi32.RegisterServiceCtrlHandlerExW(SERVICE_NAME, service_handler, None)
        if not _service_handle:
            return
        set_service_status(SERVICE_START_PENDING, wait_hint=15000)
        logger = configure_logging(False)
        _runtime = WorkspaceRuntime()
        set_service_status(SERVICE_RUNNING, SERVICE_ACCEPT_STOP | SERVICE_ACCEPT_SHUTDOWN)
        try:
            with SingleInstance():
                _runtime.run(logger)
        except Exception:  # noqa: BLE001
            logger.exception("Falha nao tratada no servico.")
        finally:
            set_service_status(SERVICE_STOPPED)


def run_service_dispatcher() -> int:
    table = (SERVICE_TABLE_ENTRY * 2)()
    table[0].lpServiceName = SERVICE_NAME
    table[0].lpServiceProc = service_main
    table[1].lpServiceName = None
    table[1].lpServiceProc = SERVICE_MAIN()
    if not _advapi32.StartServiceCtrlDispatcherW(table):
        raise ctypes.WinError(ctypes.get_last_error())
    return 0


# --------------------------------------------------------------------------- #
#  CLI
# --------------------------------------------------------------------------- #

def main(argv: list[str]) -> int:
    parser = argparse.ArgumentParser(description="Servico Ativa Workspace")
    parser.add_argument("--service", action="store_true", help="Executa pelo Service Control Manager")
    parser.add_argument("--once", action="store_true", help="Roda um ciclo e sai")
    parser.add_argument("--open-workplace", action="store_true", help="(sessao do usuario) abre Acessar trabalho ou escola")
    parser.add_argument("--configure", metavar="ARQUIVO", help="Grava config.json a partir de um JSON")
    parser.add_argument("--install-service", action="store_true")
    parser.add_argument("--uninstall-service", action="store_true")
    parser.add_argument("--version", action="store_true")
    parser.add_argument("--debug", action="store_true")
    args = parser.parse_args(argv)

    if args.version:
        print(WORKSPACE_AGENT_VERSION)
        return 0
    if args.open_workplace:
        return open_workplace_now()
    if args.service:
        return run_service_dispatcher()

    logger = configure_logging(args.debug or args.once)
    try:
        if args.configure:
            write_configuration(Path(args.configure), logger)
            return 0
        if args.install_service:
            return install_service(logger)
        if args.uninstall_service:
            return uninstall_service(logger)
        if args.once:
            WorkspaceRuntime().tick(logger)
            return 0
    except (OSError, ValueError, RuntimeError) as exc:
        logger.error("%s", exc)
        print(f"Erro: {exc}", file=sys.stderr)
        return 1

    parser.error("selecione --service, --once, --configure, --install-service, --uninstall-service ou --version")
    return 2


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
