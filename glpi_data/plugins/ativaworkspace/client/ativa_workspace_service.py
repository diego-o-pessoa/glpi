"""
Ativa Workspace - servico Windows.

Independente do Ativa Updater: uma falha aqui nunca afeta a atualizacao do
pacote unificado. Roda como SYSTEM e executa as etapas automaticas do
provisionamento (ENTRA_LOGIN, SOFTWARE e CONFIGURATION).

  1. pergunta ao Workspace se ha uma etapa Entra para esta maquina;
  2. `dsregcmd /status`: se ja ingressado no tenant esperado -> SUCCESS, sem UI;
  3. se nao, abre `ms-settings:workplace` NA SESSAO DO USUARIO (o servico nao
     tem tela), e reporta WAITING_INTERVENTION com as instrucoes;
  4. o tecnico entra pelo Ativa Remote, clica Conectar -> Ingressar no Entra e
     digita as credenciais direto na tela da Microsoft (nada passa pelo servico);
  5. o servico verifica o `dsregcmd` periodicamente e conclui SUCCESS quando o
     ingresso no tenant certo e confirmado.

O TAP e temporario, apagado assim que o helper o le e nunca registrado. Para
Outlook PWA, a autenticacao usa o SSO CloudAP do Windows/Entra, sem senha.
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
import unicodedata
import zipfile
from ctypes import wintypes
from pathlib import Path
from typing import Any

import ativa_workspace_entra as lib
import ativa_workspace_install as installer
import ativa_workspace_inventory as inventory
import ativa_workspace_logon as logon
import ativa_workspace_openvpn as openvpn
import ativa_workspace_remote as remote
import ativa_workspace_outlook as outlook
import ativa_workspace_uninstall as uninstall

SERVICE_NAME = "AtivaWorkspace"
SERVICE_DISPLAY_NAME = "Ativa Workspace"
SERVICE_DESCRIPTION = "Provisionamento Ativa: conduz a etapa de ingresso no Microsoft Entra ID."
WORKSPACE_AGENT_VERSION = "1.8.14"

PROGRAM_DATA = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData"))
PRODUCT_DIR = PROGRAM_DATA / "AtivaLocacao" / "Workspace"
LOG_DIR = PRODUCT_DIR / "logs"
STATE_PATH = PRODUCT_DIR / "entra-state.json"
VERSION_PATH = PRODUCT_DIR / "version.json"
SERVICE_EXE = PRODUCT_DIR / "AtivaWorkspace.exe"

POLL_SECONDS = 15               # ritmo normal do loop
WAIT_HUMAN_TIMEOUT = 30 * 60    # desiste de aguardar o tecnico depois disto
OPEN_UI_MIN_INTERVAL = 120      # nao reabre a janela com mais frequencia que isto
DESKTOP_STABLE_SECONDS = 45     # area de trabalho estavel (PIN ja criado) antes de concluir
NO_PIN_FALLBACK_SECONDS = 15 * 60  # sem PIN (tenant sem Windows Hello): espera longa antes de seguir
MUTEX_NAME = r"Global\AtivaWorkspaceService"

# Fase de login do usuario Entra (depois do ingresso).
WEB_SIGNIN_MARKER = PRODUCT_DIR / "websignin.json"  # legado: Web sign-in ligado por versao antiga
SOFTWARE_STATE_PATH = PRODUCT_DIR / "software-install.lock"  # instalacao em andamento
CONFIGURATION_STATE_PATH = PRODUCT_DIR / "configuration-state.json"
OPENVPN_STATE_PATH = PRODUCT_DIR / "openvpn-state.json"
PENDING_RESULT_PATH = PRODUCT_DIR / "pending-result.json"  # resultado que nao chegou ao servidor (troca de VPN)
OPENVPN_CONNECT_TIMEOUT = 3 * 60  # tempo para a VPN conectar depois de mandar o GUI
SOFTWARE_LOCK_SECONDS = 4 * 60 * 60  # nao reentra na instalacao dentro disto
INVENTORY_MARKER = PRODUCT_DIR / "inventory.stamp"  # ultimo envio de inventario
INVENTORY_INTERVAL = 60  # coleta e envia o inventario a cada 1 min

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


def elevated_linked_token(token: wintypes.HANDLE) -> wintypes.HANDLE | None:
    """
    Token elevado (vinculado) do mesmo usuario, se ele for administrador com
    UAC. O dialogo de ingresso no Entra roda elevado e o Windows (UIPI) nao
    deixa um processo comum ver/clicar nele. So o servico (SYSTEM) consegue
    esse token; o exe fica em pasta protegida (SYSTEM/Administradores).
    """
    advapi32 = ctypes.windll.advapi32
    TOKEN_LINKED_TOKEN_CLASS = 19
    linked = wintypes.HANDLE()
    size = wintypes.DWORD()
    if advapi32.GetTokenInformation(
        token, TOKEN_LINKED_TOKEN_CLASS, ctypes.byref(linked), ctypes.sizeof(linked), ctypes.byref(size)
    ) and linked.value:
        return linked
    return None


def launch_in_session(
    session_id: int,
    arguments: str,
    logger: logging.Logger,
    elevated: bool = False,
    executable: Path | None = None,
) -> bool:
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

    if elevated:
        linked = elevated_linked_token(token)
        if linked is not None:
            kernel32.CloseHandle(token)
            token = linked
            logger.info("Helper iniciado com o token elevado do usuario.")
        else:
            logger.info("Usuario sem token elevado: helper com token comum.")

    env = ctypes.c_void_p()
    try:
        if not userenv.CreateEnvironmentBlock(ctypes.byref(env), token, False):
            env = ctypes.c_void_p()

        exe = executable or (Path(sys.executable) if getattr(sys, "frozen", False) else SERVICE_EXE)
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
        if launch_in_session(session_id, "--open-workplace", logger, elevated=True):
            opened = True
    return opened


# Troca com o helper (sessao do usuario): fica FORA da pasta Workspace (restrita
# a SYSTEM/Admins). Guarda o TAP - de uso unico e curta duracao - so ate o helper
# ler e apagar. O helper roda como o usuario, por isso precisa de acesso.
HELPER_DIR = PROGRAM_DATA / "AtivaLocacao" / "WorkspaceHelper"
HELPER_PAYLOAD = HELPER_DIR / "entra.json"
OUTLOOK_HELPER_EXE = HELPER_DIR / "AtivaWorkspaceUser.exe"


def ensure_helper_dir(logger: logging.Logger) -> bool:
    """Area de troca do helper: SYSTEM/Admins e usuarios com modificacao."""
    import subprocess
    try:
        HELPER_DIR.mkdir(parents=True, exist_ok=True)
        completed = subprocess.run(
            ["icacls", str(HELPER_DIR), "/inheritance:r",
             "/grant:r", "*S-1-5-18:(OI)(CI)F", "/grant:r", "*S-1-5-32-544:(OI)(CI)F",
             "/grant:r", "*S-1-5-32-545:(OI)(CI)M"],
            capture_output=True, timeout=30, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        return completed.returncode == 0
    except (OSError, subprocess.SubprocessError):
        logger.exception("Nao foi possivel preparar a pasta do helper.")
        return False


def write_helper_payload(data: dict[str, Any], logger: logging.Logger) -> None:
    try:
        if not ensure_helper_dir(logger):
            return
        HELPER_PAYLOAD.write_text(json.dumps(data), encoding="utf-8")
    except OSError:
        logger.exception("Nao foi possivel preparar os dados do helper.")


def clear_helper_payload() -> None:
    HELPER_PAYLOAD.unlink(missing_ok=True)


def prepare_outlook_helper(logger: logging.Logger) -> Path | None:
    """
    O executavel do servico fica numa pasta que usuarios comuns nao podem ler
    (ela contem o token da API). Copia somente o binario para a area do helper,
    que roda sem elevacao e nunca recebe o token nem um TAP.
    """
    import shutil
    if not ensure_helper_dir(logger):
        return None
    source = Path(sys.executable) if getattr(sys, "frozen", False) else SERVICE_EXE
    if not source.is_file():
        logger.warning("Executavel do Workspace nao encontrado para o helper: %s", source)
        return None
    try:
        shutil.copy2(source, OUTLOOK_HELPER_EXE)
        return OUTLOOK_HELPER_EXE
    except OSError:
        logger.exception("Nao foi possivel preparar o helper do Outlook PWA.")
        return None


def configure_user_helper_logging() -> logging.Logger:
    """Log sem dados sensiveis, gravavel pelo usuario que executa a PWA."""
    HELPER_DIR.mkdir(parents=True, exist_ok=True)
    logger = logging.getLogger("ativa-workspace-user-helper")
    if not logger.handlers:
        logger.setLevel(logging.INFO)
        handler = logging.FileHandler(HELPER_DIR / "outlook-helper.log", encoding="utf-8")
        handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s %(message)s"))
        logger.addHandler(handler)
    return logger


# --------------------------------------------------------------------------- #
#  Loop do servico
# --------------------------------------------------------------------------- #

class WorkspaceRuntime:
    def __init__(self) -> None:
        self.stop_event = _StopEvent()
        self._last_reason = ""

    def run(self, logger: logging.Logger) -> None:
        logger.info("Ativa Workspace %s iniciado.", WORKSPACE_AGENT_VERSION)
        # O login do Entra agora e manual (usuario normal na tela de bloqueio):
        # desfaz o Web sign-in que versoes anteriores ligaram.
        logon.remove_web_signin(logger, WEB_SIGNIN_MARKER)
        # Lido pelo Ativa Guardian e pelo Ativa Updater (painel de versoes).
        try:
            save_json(VERSION_PATH, {"version": WORKSPACE_AGENT_VERSION})
        except Exception:  # noqa: BLE001
            logger.warning("Nao foi possivel gravar %s.", VERSION_PATH)
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

        # Troca de VPN em andamento: confere a conexao pelo log local, sem
        # depender do servidor (que so e alcancado pela VPN). Depois tenta
        # entregar um resultado que ficou guardado sem conexao.
        try:
            self._watch_openvpn(logger, api)
            self._flush_pending_result(logger, api)
        except Exception:  # noqa: BLE001
            logger.exception("Falha ao acompanhar a VPN.")

        # Inventario (programas, discos, memoria, processos) no seu intervalo.
        try:
            self._maybe_report_inventory(logger, api)
        except Exception:  # noqa: BLE001
            logger.exception("Falha ao reportar o inventario.")

        # Acoes pendentes na maquina (ex.: desinstalar programa).
        try:
            self._handle_actions(logger, api)
        except Exception:  # noqa: BLE001
            logger.exception("Falha ao processar acoes da maquina.")

        status, step = api.next_step()
        if status == 204:
            # Sem etapa ativa (cancelado/excluido/concluido): nao deixa estado
            # local que faca o processo continuar/retomar na maquina.
            self._clear_local_state()
            self._idle(logger, "ok, nenhuma etapa Entra pendente")
            return
        if status != 200 or step is None:
            self._idle(logger, lib.describe_http_error(status, step))
            return
        step_id = int(step.get("step_id", 0))
        step_type = step.get("type")
        if step_type == "ENTRA_LOGIN":
            entra = step.get("entra", {}) if isinstance(step.get("entra"), dict) else {}
            self._handle_entra(logger, api, step_id, entra)
        elif step_type == "SOFTWARE":
            software = step.get("software", {}) if isinstance(step.get("software"), dict) else {}
            self._handle_software(logger, api, step_id, software)
        elif step_type == "CONFIGURATION":
            configuration = step.get("configuration", {}) if isinstance(step.get("configuration"), dict) else {}
            self._handle_configuration(logger, api, step_id, configuration)

    @staticmethod
    def _clear_local_state() -> None:
        """Remove marcadores locais para nada resumir apos cancelar/excluir."""
        config_state = load_json(CONFIGURATION_STATE_PATH)
        if int(config_state.get("step_id", 0) or 0) > 0:
            outlook.clear_result(int(config_state["step_id"]))
        for path in (STATE_PATH, SOFTWARE_STATE_PATH, CONFIGURATION_STATE_PATH, OPENVPN_STATE_PATH):
            try:
                path.unlink(missing_ok=True)
            except OSError:
                pass

    def _handle_actions(self, logger: logging.Logger, api: "lib.WorkspaceApi") -> None:
        """Executa a proxima acao pendente (por enquanto: desinstalar programa)."""
        action = api.next_action()
        if not action:
            return
        action_id = int(action.get("id", 0))
        kind = str(action.get("action", ""))
        if action_id <= 0:
            return
        if kind != "uninstall":
            # Acoes remotas (reiniciar, mensagem, encerrar processo, manutencao).
            params = action.get("params") if isinstance(action.get("params"), dict) else {}
            if kind in ("restart", "shutdown"):
                # Reporta antes: depois o Windows desliga e o servico para.
                ok, message = remote.run(kind, params, user_sessions(), logger)
                api.report_action(action_id, ok, message)
                return
            ok, message = remote.run(kind, params, user_sessions(), logger)
            logger.info("Acao remota %s -> %s (%s)", kind, "ok" if ok else "falhou", message)
            api.report_action(action_id, ok, message)
            if kind in ("kill_process", "clean_temp"):
                INVENTORY_MARKER.unlink(missing_ok=True)  # processos/disco mudaram
            return
        target = str(action.get("target", ""))
        logger.info("Acao: desinstalar '%s'.", target)
        ok, message = uninstall.run_uninstall(
            str(action.get("scope", "")), str(action.get("reg_key", "")), 20 * 60, logger,
        )
        logger.info("Acao: desinstalar '%s' -> %s (%s)", target, "ok" if ok else "falhou", message)
        api.report_action(action_id, ok, message)
        # Inventario ficou desatualizado: forca reenvio no proximo ciclo.
        INVENTORY_MARKER.unlink(missing_ok=True)

    def _maybe_report_inventory(self, logger: logging.Logger, api: "lib.WorkspaceApi") -> None:
        """Coleta e envia o inventario a cada INVENTORY_INTERVAL."""
        now = time.time()
        try:
            last = INVENTORY_MARKER.stat().st_mtime
        except OSError:
            last = 0.0
        if now - last < INVENTORY_INTERVAL:
            return
        def vpn_profiles() -> list[str]:
            # Perfil do OpenVPN conectado pelo usuario da sessao (pelo log do GUI).
            sessions = user_sessions()
            return openvpn.connected_profiles(logon.session_user_sid(sessions[0])) if sessions else []

        data = inventory.collect(WORKSPACE_AGENT_VERSION, api.base_url, vpn_profiles)
        if api.report_inventory(data):
            INVENTORY_MARKER.write_text(str(int(now)), "utf-8")
            logger.info("Inventario enviado (%s programas).", len(data.get("programs", [])))
        else:
            logger.warning("Inventario nao aceito pelo servidor (maquina vinculada?).")

    def _handle_software(self, logger: logging.Logger, api: "lib.WorkspaceApi", step_id: int, payload: dict) -> None:
        """Instala o app da etapa (winget ou instalador enviado) e reporta o resultado."""
        # Marca uma execucao em andamento; evita reinstalar em paralelo se o
        # ciclo se sobrepuser (a instalacao pode demorar).
        marker = SOFTWARE_STATE_PATH
        if marker.exists() and time.time() - marker.stat().st_mtime < SOFTWARE_LOCK_SECONDS:
            return
        marker.write_text(str(step_id), "utf-8")
        try:
            api.progress(step_id, "PRECHECK", f"Preparando: {payload.get('name', 'aplicativo')}")
            ok, message = installer.install(payload, api, step_id, logger)
            logger.info("Software: %s -> %s (%s)", payload.get("name", "app"), "ok" if ok else "falhou", message)
            api.result(step_id, SUCCESS if ok else FAILED, message)
        except Exception as exc:  # noqa: BLE001
            logger.exception("Software: erro inesperado.")
            api.result(step_id, FAILED, f"Erro inesperado na instalacao: {exc}")
        finally:
            marker.unlink(missing_ok=True)

    def _handle_configuration(
        self,
        logger: logging.Logger,
        api: "lib.WorkspaceApi",
        step_id: int,
        payload: dict,
    ) -> None:
        """Executa configuracoes predefinidas; hoje, Outlook PWA."""
        key = str(payload.get("configuration_key", ""))
        if key == "openvpn":
            self._handle_openvpn(logger, api, step_id, payload)
            return
        if key != "outlook_pwa":
            api.result(
                step_id,
                FAILED,
                f"A configuracao '{key or 'sem chave'}' ainda nao possui executor no Ativa Workspace.",
            )
            return

        state = load_json(CONFIGURATION_STATE_PATH)
        if int(state.get("step_id", 0) or 0) != step_id:
            state = {
                "step_id": step_id,
                "started_at": time.time(),
                "helper_started_at": 0.0,
                "last_phase": "",
            }
            outlook.clear_result(step_id)
            save_json(CONFIGURATION_STATE_PATH, state)
            api.progress(step_id, "CONFIG_PRECHECK", "Validando Chrome e sessao do usuario")

        timeout = max(5, int(payload.get("timeout_minutes", 30) or 30)) * 60
        if time.time() - float(state.get("started_at", time.time())) > timeout:
            api.result(step_id, FAILED, "Tempo esgotado ao configurar o Outlook PWA.")
            self._finish_configuration(step_id)
            return

        # O Outlook PWA NAO exige o ingresso no Entra: instala e fixa de qualquer
        # forma. Se a maquina estiver no Entra, o SSO (CloudAP) autentica sozinho;
        # senao, o usuario faz o primeiro login (ex.: com um TAP) na tela aberta.
        # So e preciso uma sessao interativa para abrir o navegador.
        sessions = user_sessions()
        if not sessions:
            api.progress(step_id, "WAITING_USER_SESSION", "Nenhuma sessao interativa esta ativa")
            return

        # Primeira passagem: garante o navegador, aplica politicas e dispara o
        # helper no desktop do usuario. Nas passagens seguintes, le o progresso.
        if float(state.get("helper_started_at", 0.0)) <= 0:
            if not outlook.chrome_installed() and not installer.find_installed("Google Chrome"):
                api.progress(step_id, "INSTALLING_CHROME", "Instalando Google Chrome pelo winget")
                ok, message = installer.install_winget("Google.Chrome", 15 * 60, logger)
                if not ok or not outlook.chrome_installed():
                    api.result(step_id, FAILED, "Nao foi possivel instalar o Google Chrome: " + message)
                    self._finish_configuration(step_id)
                    return

            api.progress(step_id, "APPLYING_POLICY", "CloudAP SSO e instalacao forcada do Outlook PWA")
            ok, message = outlook.apply_chrome_policies(str(payload.get("outlook_url", outlook.OUTLOOK_URL)))
            if not ok:
                api.result(step_id, FAILED, message)
                self._finish_configuration(step_id)
                return

            helper = prepare_outlook_helper(logger)
            if helper is None:
                api.result(step_id, FAILED, "Nao foi possivel preparar o helper do Outlook PWA.")
                self._finish_configuration(step_id)
                return
            if not launch_in_session(
                sessions[0],
                f"--configure-outlook-pwa {step_id}",
                logger,
                elevated=False,
                executable=helper,
            ):
                api.result(step_id, FAILED, "Nao foi possivel iniciar a configuracao na sessao do usuario.")
                self._finish_configuration(step_id)
                return

            state["helper_started_at"] = time.time()
            state["last_phase"] = "OPENING_OUTLOOK"
            save_json(CONFIGURATION_STATE_PATH, state)
            api.progress(step_id, "OPENING_OUTLOOK", "Helper iniciado na sessao do usuario")
            return

        result = outlook.read_result(step_id)
        # O arquivo pode ser criado pelo usuario: aceite somente o step atual,
        # fases conhecidas e textos curtos. Ele nunca pode alterar outro job.
        if int(result.get("step_id", 0) or 0) != step_id:
            return
        allowed_phases = {
            "OPENING_OUTLOOK", "INSTALLING_PWA", "SIGNING_IN",
            "PINNING_TASKBAR", "VERIFYING_CONFIGURATION",
        }
        phase = str(result.get("phase", ""))
        message = str(result.get("message", ""))[:200]
        if phase in allowed_phases and phase != str(state.get("last_phase", "")):
            api.progress(step_id, phase, message)
            state["last_phase"] = phase
            save_json(CONFIGURATION_STATE_PATH, state)

        # O helper pede a fixacao: so o SYSTEM grava a politica de layout. O
        # caminho vem de arquivo nao confiavel e e validado em apply_taskbar_layout.
        shortcut = str(result.get("shortcut", ""))
        if phase == "PINNING_TASKBAR" and shortcut and not state.get("layout_applied"):
            ok, layout_message = outlook.apply_taskbar_layout(shortcut)
            logger.info("Outlook PWA: layout da barra -> %s (%s)", "ok" if ok else "falhou", layout_message)
            if not ok:
                api.result(step_id, FAILED, layout_message)
                self._finish_configuration(step_id)
                return
            state["layout_applied"] = True
            save_json(CONFIGURATION_STATE_PATH, state)

        if result.get("done") is True:
            ok = result.get("ok") is True
            api.result(
                step_id,
                SUCCESS if ok else FAILED,
                message or ("Outlook PWA configurado." if ok else "Falha ao configurar o Outlook PWA."),
            )
            self._finish_configuration(step_id)

    def _handle_openvpn(self, logger: logging.Logger, api: "lib.WorkspaceApi", step_id: int, payload: dict) -> None:
        """
        OpenVPN: instala (se faltar), coloca o perfil na pasta config do OpenVPN,
        grava usuario/senha da VPN (arquivo protegido) e conecta pelo OpenVPN GUI
        na sessao do usuario. Confirma pelo log do OpenVPN.
        """
        state = load_json(OPENVPN_STATE_PATH)
        if int(state.get("step_id", 0) or 0) != step_id:
            state = {"step_id": step_id, "started_at": time.time()}
            save_json(OPENVPN_STATE_PATH, state)

        def fail(message: str) -> None:
            api.result(step_id, FAILED, message)
            OPENVPN_STATE_PATH.unlink(missing_ok=True)

        # O GUI (icone da bandeja) roda na sessao do usuario: precisa de alguem logado.
        sessions = user_sessions()
        if not sessions:
            api.progress(step_id, "WAITING_USER_SESSION", "Nenhuma sessao interativa esta ativa")
            return
        session_id = sessions[0]
        user_sid = logon.session_user_sid(session_id)

        # Ja mandou conectar: quem acompanha e o _watch_openvpn (no inicio do tick).
        if state.get("connect_at"):
            return

        # 1) OpenVPN instalado?
        if not openvpn.installed():
            api.progress(step_id, "INSTALLING_OPENVPN", "Instalando o OpenVPN pelo winget")
            ok, message = installer.install_winget(openvpn.WINGET_ID, 15 * 60, logger)
            if not ok or not openvpn.installed():
                fail("Nao foi possivel instalar o OpenVPN: " + message)
                return

        # 2) Perfil (.zip) conferido por SHA-256 e extraido na pasta config.
        api.progress(step_id, "DOWNLOADING_VPN", "Baixando o perfil do OpenVPN")
        tmp = PRODUCT_DIR / f"vpn-{step_id}.zip"
        try:
            ok, server_sha, error = api.download_vpn_profile(step_id, tmp)
            if not ok:
                fail(f"Falha ao baixar o perfil do OpenVPN: {error}")
                return
            expected = str(payload.get("vpn_file_sha256", "")).strip().lower() or server_sha
            if not expected or installer._sha256(tmp) != expected:
                fail("SHA-256 do perfil do OpenVPN nao confere; arquivo recusado.")
                return
            api.progress(step_id, "APPLYING_VPN", "Copiando o perfil para a pasta config do OpenVPN")
            try:
                ovpn = openvpn.extract_profile(tmp, str(payload.get("vpn_ovpn", "")))
            except (ValueError, OSError, zipfile.BadZipFile) as exc:
                fail(f"Perfil do OpenVPN invalido: {exc}")
                return
        finally:
            tmp.unlink(missing_ok=True)
        logger.info("OpenVPN: perfil extraido em %s.", ovpn.parent)

        # 3) Usuario/senha: pedidos uma vez, gravados so no arquivo protegido.
        credentials, error = api.vpn_credentials(step_id)
        if credentials is None:
            fail(f"Sem usuario/senha da VPN para esta maquina: {error}")
            return
        ok, message = openvpn.apply_credentials(
            ovpn, str(credentials.get("username", "")), str(credentials.get("password", "")), user_sid,
        )
        credentials = None  # nao guarda a senha em memoria alem do necessario
        if not ok:
            fail(message)
            return

        # 4) So uma VPN por vez: anota a atual (ex.: a do T.I., por onde o
        # servico fala com o servidor), desconecta e conecta a do funcionario.
        # Se a nova falhar, _watch_openvpn reconecta a anterior.
        name = ovpn.stem
        previous = [p for p in openvpn.connected_profiles(user_sid) if p.lower() != name.lower()]
        api.progress(step_id, "CONNECTING_VPN",
                     "Trocando a VPN: " + (", ".join(previous) or "nenhuma") + " -> " + name)
        state.update({"profile_name": name, "previous": previous,
                      "session_id": session_id, "user_sid": user_sid})
        names = logon.session_process_names(session_id) or set()
        gui_running = "openvpn-gui.exe" in names
        if gui_running:
            launch_in_session(session_id, openvpn.DISCONNECT_ALL, logger, elevated=False, executable=openvpn.GUI_EXE)
            time.sleep(5)
        for arguments in openvpn.connect_arguments(name, gui_running):
            if not launch_in_session(session_id, arguments, logger, elevated=False, executable=openvpn.GUI_EXE):
                fail("Nao foi possivel abrir o OpenVPN GUI na sessao do usuario.")
                return
            time.sleep(3)
        state["connect_at"] = time.time()
        save_json(OPENVPN_STATE_PATH, state)
        logger.info("OpenVPN: conexao solicitada para o perfil %s (anterior: %s).", name, previous or "-")

    def _watch_openvpn(self, logger: logging.Logger, api: "lib.WorkspaceApi") -> None:
        """Acompanha a VPN do funcionario pelo log local; em falha, volta a anterior."""
        state = load_json(OPENVPN_STATE_PATH)
        if not state.get("connect_at"):
            return
        step_id = int(state.get("step_id", 0) or 0)
        name = str(state.get("profile_name", ""))
        user_sid = str(state.get("user_sid", ""))
        status, detail = openvpn.connection_status(user_sid, name, float(state["connect_at"]))
        logger.info("OpenVPN: %s (%s).", status, detail)
        if status == "connected":
            OPENVPN_STATE_PATH.unlink(missing_ok=True)
            self._deliver_result(logger, api, step_id, SUCCESS, f"VPN conectada ({name}).")
            return
        if status == "auth_failed":
            reason = "O servidor da VPN recusou o usuario ou a senha informados no provisionamento."
        elif status == "error":
            reason = f"O OpenVPN nao conectou: {detail}"
        elif time.time() - float(state["connect_at"]) > OPENVPN_CONNECT_TIMEOUT:
            reason = "A VPN nao conectou em 3 minutos. Confira o perfil e o acesso ao servidor da VPN."
        else:
            return

        # Falhou: derruba a tentativa e reconecta a VPN anterior, para o servico
        # voltar a falar com o servidor e reportar o erro.
        OPENVPN_STATE_PATH.unlink(missing_ok=True)
        sessions = user_sessions()
        previous = [str(p) for p in state.get("previous", []) if str(p)]
        if sessions:
            session_id = sessions[0]
            launch_in_session(session_id, openvpn.DISCONNECT_ALL, logger, elevated=False, executable=openvpn.GUI_EXE)
            if previous:
                time.sleep(5)
                for arguments in openvpn.connect_arguments(previous[0], True):
                    launch_in_session(session_id, arguments, logger, elevated=False, executable=openvpn.GUI_EXE)
                    time.sleep(3)
                reason += f" A VPN anterior ({previous[0]}) foi reconectada."
                logger.info("OpenVPN: falhou; reconectando a VPN anterior %s.", previous[0])
        self._deliver_result(logger, api, step_id, FAILED, reason)

    @staticmethod
    def _deliver_result(logger: logging.Logger, api: "lib.WorkspaceApi", step_id: int, result: str, message: str) -> None:
        """Envia o resultado; sem conexao (troca de VPN), guarda para reenviar."""
        if api.send_result(step_id, result, message) == 0:
            save_json(PENDING_RESULT_PATH, {"step_id": step_id, "result": result, "message": message})
            logger.info("Sem conexao com o servidor; resultado da etapa %s guardado para reenvio.", step_id)

    @staticmethod
    def _flush_pending_result(logger: logging.Logger, api: "lib.WorkspaceApi") -> None:
        pending = load_json(PENDING_RESULT_PATH)
        if not pending.get("step_id"):
            return
        status = api.send_result(int(pending["step_id"]), str(pending.get("result", "")), str(pending.get("message", "")))
        if status == 0:
            return  # ainda sem conexao
        # Entregue ou recusado de vez (ex.: etapa cancelada): nao insiste.
        PENDING_RESULT_PATH.unlink(missing_ok=True)
        logger.info("Resultado guardado da etapa %s entregue (HTTP %s).", pending["step_id"], status)

    @staticmethod
    def _finish_configuration(step_id: int) -> None:
        outlook.clear_result(step_id)
        try:
            CONFIGURATION_STATE_PATH.unlink(missing_ok=True)
        except OSError:
            pass

    def _handle_entra(self, logger: logging.Logger, api: "lib.WorkspaceApi", step_id: int, entra: dict) -> None:
        expected_tenant = str(entra.get("expected_tenant", ""))
        expected_domain = str(entra.get("expected_domain", ""))

        # 1) Verificacao (nao abre UI). O PRECHECK so vai ao servidor antes do
        # ingresso: depois, ele esconderia o andamento (reinicio/login) no painel.
        fields = lib.parse_dsregcmd(lib.run_dsregcmd())
        joined, reason = lib.evaluate_join(fields, expected_tenant, expected_domain)
        logger.info("Entra: AzureAdJoined=%s (%s)", fields.get("AzureAdJoined", "?"), reason)
        if not joined:
            api.progress(step_id, PRECHECK, "dsregcmd /status")

        state = load_json(STATE_PATH)
        if state.get("step_id") != step_id:
            state = {"step_id": step_id, "started_at": time.time(), "last_open": 0.0}

        if joined:
            proof = {
                "azure_ad_joined": True,
                "device_id": fields.get("DeviceId", ""),
                "tenant_id": fields.get("TenantId", ""),
            }
            # Ja possuia conta do Entra ID antes desta etapa (nunca abrimos o
            # fluxo): pula a etapa em vez de trocar de usuario/reiniciar.
            if not state.get("acted"):
                logger.info("Entra: computador ja ingressado; pulando a etapa.")
                api.result(step_id, SUCCESS, "Computador já ingressado no Microsoft Entra ID; etapa concluída.", proof)
                STATE_PATH.unlink(missing_ok=True)
                return
            # So conclui quando a conta do Entra CHEGA a area de trabalho: logo
            # apos o login o Windows pede para criar o PIN (Windows Hello) e as
            # proximas etapas nao podem comecar antes disso.
            desktop, reason, session_id = logon.entra_desktop_state()
            if desktop == "desktop":
                # A tela "Configurar um PIN" fica por cima do Explorer: quem diz
                # se o primeiro login acabou e o Windows Hello (eventos + PIN novo).
                hello, detail = logon.hello_status(float(state.get("started_at", time.time())), session_id)
                has_pin = hello in ("done", "not_needed")
                if hello == "pending":
                    # PIN pedido e ainda nao criado: nunca segue sem ele.
                    logger.info("Entra: tela do PIN (Windows Hello) aberta; aguardando (%s).", detail)
                    state["desktop_since"] = 0.0
                    save_json(STATE_PATH, state)
                    api.result(step_id, WAITING_HUMAN,
                               "Usuário do Entra conectado. Conclua a criação do PIN (Windows Hello) "
                               "até abrir a área de trabalho.", {"azure_ad_joined": True})
                    return
                since = float(state.get("desktop_since", 0.0)) or time.time()
                # Sem nenhum evento do Windows Hello: espera longa antes de seguir.
                # PIN confirmado ja e prova de fim do primeiro login: segue na hora.
                if hello == "done":
                    needed = 0
                else:
                    needed = DESKTOP_STABLE_SECONDS if has_pin else NO_PIN_FALLBACK_SECONDS
                logger.info("Entra: area de trabalho carregada (Windows Hello: %s, %s).", hello, detail)
                if time.time() - since >= needed:
                    logger.info("Entra: usuario na area de trabalho; etapa concluida.")
                    api.result(step_id, SUCCESS, "Ingressado no Microsoft Entra ID e usuario na area de trabalho.", proof)
                    STATE_PATH.unlink(missing_ok=True)
                    return
                state["desktop_since"] = since
                save_json(STATE_PATH, state)
                api.result(step_id, WAITING_HUMAN,
                           "Usuário do Entra na área de trabalho; confirmando..." if has_pin else
                           "Usuário do Entra conectado. Conclua a criação do PIN (Windows Hello) "
                           "até abrir a área de trabalho.", {"azure_ad_joined": True})
                return
            if desktop == "setup":
                logger.info("Entra: usuario conectado, mas ainda nao na area de trabalho (%s).", reason)
                state["desktop_since"] = 0.0
                save_json(STATE_PATH, state)
                api.result(step_id, WAITING_HUMAN,
                           "Usuário do Entra conectado. Conclua a criação do PIN (Windows Hello) "
                           "até abrir a área de trabalho.", {"azure_ad_joined": True})
                return
            self._handle_user_signin(logger, api, step_id, entra, state)
            return

        # 2) Nao ingressado: conduz o ingresso AUTOMATICO. O job fica RUNNING (sem
        # "aguardando intervencao"); so a etapa de login do usuario aguarda o TI.
        if not user_sessions():
            api.progress(step_id, OPENING_SETTINGS, "Sem usuario logado; aguardando login")
            save_json(STATE_PATH, state)
            return

        # Timeout do ingresso automatico.
        if time.time() - float(state.get("started_at", time.time())) > WAIT_HUMAN_TIMEOUT:
            api.result(step_id, FAILED, "Tempo esgotado no ingresso automatico ao Microsoft Entra ID.",
                       {"azure_ad_joined": False})
            STATE_PATH.unlink(missing_ok=True)
            return

        # Abre/reabre a tela e dispara a automacao do ingresso no intervalo minimo.
        if time.time() - float(state.get("last_open", 0.0)) > OPEN_UI_MIN_INTERVAL:
            upn = str(entra.get("upn", ""))
            tap_data, tap_error = api.request_tap(step_id)
            tap_code = str(tap_data.get("tap", "")) if tap_data else ""
            if tap_code:
                logger.info("Entra: TAP obtido; abrindo o fluxo automatico.")
            else:
                logger.warning("Entra: sem TAP (%s); digita o e-mail e aguarda o TAP manual.", tap_error or "motivo desconhecido")
            write_helper_payload({"upn": upn, "tap": tap_code}, logger)
            opened = open_workplace_settings(logger)
            state["acted"] = True  # a partir daqui, um join detectado foi nosso
            state["last_open"] = time.time()
            logger.info("Entra: abertura automatica %s.", "solicitada" if opened else "falhou")
        save_json(STATE_PATH, state)

        # Sem WAITING_HUMAN aqui: mantem RUNNING enquanto o ingresso ocorre.
        api.progress(step_id, VERIFYING_JOIN, "Ingressando no Microsoft Entra ID automaticamente")

    @staticmethod
    def _report(logger: logging.Logger, api: "lib.WorkspaceApi", step_id: int, substate: str, log: str) -> None:
        """Envia o subestado e avisa no log se o servidor recusar."""
        if not api.progress(step_id, substate, log):
            logger.warning("Servidor recusou o subestado %s: confira se o src/EntraStep.php "
                           "atualizado foi enviado ao GLPI.", substate)

    WAIT_TI_MESSAGE = ("Ingressado no Entra ID. Na tela de bloqueio, entre manualmente em "
                       "\"Outro usuário\" com o e-mail e a senha da conta do Entra.")

    def _handle_user_signin(self, logger: logging.Logger, api: "lib.WorkspaceApi", step_id: int,
                            entra: dict, state: dict) -> None:
        """
        Ingressado, mas a conta do Entra ainda nao entrou. Sem Web sign-in e sem
        reinicio: vai para a tela de login e aguarda o login MANUAL (e-mail e
        senha), que deixa a conta salva como usuario da maquina. O servico so
        confirma quando a sessao do Entra aparece.
        """
        # a) Status amigavel ANTES de trocar de usuario: bloquear a tela pode
        # derrubar a VPN, entao o painel ja tem que refletir o "aguardando o TI".
        api.result(step_id, WAITING_HUMAN, self.WAIT_TI_MESSAGE, {"azure_ad_joined": True})

        # b) Troca para a tela de login uma unica vez (equivale a "Trocar usuario").
        if not state.get("switched_user"):
            logon.disconnect_local_sessions(logger)
            state["switched_user"] = True
            save_json(STATE_PATH, state)
            logger.info("Login: tela de login pronta; aguardando o T.I. entrar no usuario do Entra.")


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

    # A busca da uiautomation varre a arvore internamente com o filtro Compare
    # (control_type(searchDepth=..., Compare=fn) devolve UM controle). O jeito
    # antigo (for ctrl in control_type(...)) nao funciona: retorna um controle so.
    def find_control(control_type, texts, depth=50):
        lowered = [t.lower() for t in texts]

        def compare(ctrl, _depth):
            try:
                name = (ctrl.Name or "").strip().lower()
            except Exception:  # noqa: BLE001
                return False
            return bool(name) and any(t in name for t in lowered)

        ctrl = control_type(searchDepth=depth, Compare=compare)
        return ctrl if ctrl.Exists(0, 0) else None

    def click_by_text(control_type, texts, timeout):
        deadline = time.time() + timeout
        while time.time() < deadline:
            ctrl = find_control(control_type, texts)
            if ctrl is not None:
                try:
                    ctrl.GetInvokePattern().Invoke()
                except Exception:  # noqa: BLE001
                    try:
                        ctrl.Click(simulateMove=False)
                    except Exception:  # noqa: BLE001
                        pass
                return True
            time.sleep(1)
        return False

    def norm(text):
        text = unicodedata.normalize("NFKD", (text or "").strip().lower())
        return "".join(c for c in text if not unicodedata.combining(c))

    def click_exact_button(texts, timeout):
        """
        Botao com nome EXATO (sem acento), visivel. Evita pegar o link
        "Ingressar este dispositivo..." que fica atras do dialogo. Invoke e,
        se o botao continuar na tela, clique real do mouse.
        """
        wanted = {norm(t) for t in texts}

        def compare(ctrl, _depth):
            try:
                return norm(ctrl.Name) in wanted and not ctrl.IsOffscreen
            except Exception:  # noqa: BLE001
                return False

        deadline = time.time() + timeout
        while time.time() < deadline:
            button = auto.ButtonControl(searchDepth=50, Compare=compare)
            if button.Exists(0, 0):
                try:
                    button.GetInvokePattern().Invoke()
                except Exception:  # noqa: BLE001
                    pass
                time.sleep(2)
                if button.Exists(0, 0):
                    try:
                        button.Click(simulateMove=False)
                    except Exception:  # noqa: BLE001
                        pass
                return True
            time.sleep(1)
        return False

    def first_edit():
        """Primeiro campo de texto visivel (fallback p/ telas de um campo so)."""
        def visible(ctrl, _d):
            try:
                return not ctrl.IsOffscreen
            except Exception:  # noqa: BLE001
                return True
        edit = auto.EditControl(searchDepth=50, Compare=visible)
        return edit if edit.Exists(0, 0) else None

    def type_into_edit(hints, value, timeout, is_secret):
        """
        Acha o campo (pelo nome; senao o unico campo da tela) e digita.
        Campo de senha (TAP) rejeita SetValue: foca de verdade (Click) e envia
        as teclas para a janela em foco. O valor nunca vai pro log.
        """
        deadline = time.time() + timeout
        while time.time() < deadline:
            edit = find_control(auto.EditControl, hints) or (first_edit() if is_secret else None)
            if edit is not None:
                try:
                    # Foco real: Click coloca o cursor no input web, onde SetFocus falha.
                    try:
                        edit.Click(simulateMove=False, waitTime=0.2)
                    except Exception:  # noqa: BLE001
                        edit.SetFocus()
                    if not is_secret:
                        try:
                            edit.GetValuePattern().SetValue(value)
                            logger.info("Campo preenchido (conta).")
                            return True
                        except Exception:  # noqa: BLE001
                            pass
                    # Digita por teclas na janela em foco (funciona no campo de senha web).
                    auto.SendKeys("{Ctrl}a", waitTime=0.05)
                    auto.SendKeys("{Delete}", waitTime=0.05)
                    auto.SendKeys(value, waitTime=0.02)
                    logger.info("Campo preenchido (%s).", "senha" if is_secret else "conta")
                    return True
                except Exception as exc:  # noqa: BLE001
                    logger.warning("Falha ao preencher campo: %s", exc)
            time.sleep(1)
        return False

    # Inclui "Proximo" (o botao real da tela) e variantes.
    next_texts = ("proximo", "próximo", "avancar", "avançar", "next", "entrar", "sign in", "concluir", "done")

    def click_next():
        return click_by_text(auto.ButtonControl, next_texts, 10)

    try:
        auto.uiautomation.SetGlobalSearchTimeout(2)
        if not click_by_text(auto.ButtonControl, connect_texts, 25):
            logger.warning("Botao 'Conectar' nao encontrado.")
            return 0
        time.sleep(2)  # o dialogo "Configurar uma conta corporativa" abre
        # "Ingressar este dispositivo no Microsoft Entra ID" (link em Acoes alternativas).
        if not (click_by_text(auto.HyperlinkControl, join_texts, 20)
                or click_by_text(auto.TextControl, join_texts, 5)
                or click_by_text(auto.ButtonControl, join_texts, 5)):
            logger.warning("Opcao 'Ingressar no Microsoft Entra ID' nao encontrada.")
            return 0

        if not upn:
            logger.info("Sem conta (UPN): tela aberta para preenchimento manual.")
            return 0

        time.sleep(4)  # a tela de login da organizacao (web) carrega
        # Conta -> Proximo -> TAP -> Proximo.
        email_hints = ("email", "e-mail", "endereco de email", "endereço de email",
                       "someone", "phone", "telefone", "skype", "conta", "account", "usuario", "usuário")
        if not type_into_edit(email_hints, upn, 40, is_secret=False):
            logger.warning("Campo de e-mail nao encontrado; preenchimento manual.")
            return 0
        click_next()

        if not tap:
            logger.info("E-mail preenchido; sem TAP (digite a senha temporaria manualmente).")
            return 0

        time.sleep(4)  # proxima tela (senha ou TAP)

        tap_hints = ("temporary", "temporária", "temporaria", "tap", "passcode",
                     "codigo", "código", "acesso", "senha", "password", "pass", "pin")
        if not type_into_edit(tap_hints, tap, 40, is_secret=True):
            logger.warning("Campo de senha/TAP nao encontrado; preenchimento manual.")
            return 0
        click_next()

        # Confirmacao "Verifique se esta e sua organizacao" -> Ingressar.
        # O login/validacao pode demorar: espera ate 2 min pelo dialogo.
        logger.info("TAP enviado; aguardando a confirmacao da organizacao (Ingressar).")
        if click_exact_button(("ingressar", "join"), 120):
            logger.info("Confirmacao da organizacao: Ingressar clicado.")
        else:
            logger.warning("Botao 'Ingressar' nao encontrado.")
        # Tela final "Esta tudo pronto!" -> Concluido (o ingresso leva um tempo).
        if click_exact_button(("concluido", "done", "finish"), 180):
            logger.info("Ingresso concluido (Concluido clicado).")
        else:
            logger.warning("Botao 'Concluido' nao encontrado.")
        logger.info("Fluxo do Entra finalizado; aguardando o dsregcmd confirmar.")
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
    parser.add_argument("--configure-outlook-pwa", type=int, metavar="STEP_ID",
                        help="(sessao do usuario) instala e fixa o Outlook PWA")
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
    if args.configure_outlook_pwa is not None:
        return outlook.configure_for_current_user(args.configure_outlook_pwa, configure_user_helper_logging())
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

    parser.error("selecione --service, --once, --configure, --configure-outlook-pwa, --install-service, --uninstall-service ou --version")
    return 2


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
