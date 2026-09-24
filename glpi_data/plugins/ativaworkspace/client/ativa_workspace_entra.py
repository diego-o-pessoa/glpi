"""
Ativa Workspace - executor da etapa ENTRA_LOGIN (Windows).

Responsabilidade (roda a partir do servico AtivaUpdater, como SYSTEM):
  1. perguntar ao Workspace se ha uma etapa Entra para esta maquina;
  2. `dsregcmd /status` -> se ja ingressado no tenant esperado, concluir SUCCESS
     sem abrir interface;
  3. se nao, lancar o HELPER na sessao interativa do usuario para abrir
     "Acessar trabalho ou escola" e chegar ate a tela de login da Microsoft;
  4. reportar WAITING_HUMAN quando a tela de credencial estiver pronta;
  5. enquanto aguarda, verificar `dsregcmd` periodicamente;
  6. quando AzureAdJoined = YES e o TenantId bater, reportar SUCCESS.

REGRAS DE SEGURANCA (inviolaveis):
  - a senha e digitada pelo tecnico direto na tela da Microsoft;
  - o helper NUNCA le, digita, captura, guarda ou registra credenciais;
  - nada de senha vai para o Workspace, arquivos ou logs.

O servico e o dono do estado/comunicacao. O HELPER e um processo curto que so
existe para a interacao com a interface do Windows, na sessao do usuario.

Uso:
    modulo importado pelo servico  -> WorkspaceEntraExecutor(config).tick()
    processo helper (sessao usuario) -> python ativa_workspace_entra.py --helper <arquivo-json>
"""

from __future__ import annotations

import argparse
import ctypes
import json
import logging
import os
import re
import ssl
import subprocess
import sys
import time
from pathlib import Path
from typing import Any, Callable
from urllib.request import Request, build_opener, HTTPSHandler

PROGRAM_DATA = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData"))
WORKSPACE_DIR = PROGRAM_DATA / "AtivaLocacao" / "Workspace"
CONFIG_PATH = WORKSPACE_DIR / "config.json"
STATE_PATH = WORKSPACE_DIR / "entra-state.json"
HELPER_DIR = WORKSPACE_DIR / "helper"
HELPER_SIGNAL_PATH = HELPER_DIR / "entra-helper.json"

# Subestados (espelham EntraStep.php no lado do GLPI).
PRECHECK = "PRECHECK"
OPENING_SETTINGS = "OPENING_SETTINGS"
OPENING_CONNECT = "OPENING_CONNECT"
OPENING_ENTRA_JOIN = "OPENING_ENTRA_JOIN"
WAITING_CREDENTIAL_UI = "WAITING_CREDENTIAL_UI"
WAITING_HUMAN = "WAITING_HUMAN"
VERIFYING_JOIN = "VERIFYING_JOIN"
SUCCESS = "SUCCESS"
FAILED = "FAILED"

# Quanto tempo aguardar a autenticacao do tecnico antes de desistir (segundos).
WAIT_HUMAN_TIMEOUT = 30 * 60
# Intervalo entre verificacoes do dsregcmd enquanto aguarda.
VERIFY_INTERVAL = 15
# Nao roda o helper com mais frequencia que isto (evita reabrir janelas).
HELPER_MIN_INTERVAL = 60


# --------------------------------------------------------------------------- #
#  dsregcmd
# --------------------------------------------------------------------------- #

def run_dsregcmd(timeout: int = 30) -> str:
    """Saida de `dsregcmd /status`, ou string vazia em erro."""
    try:
        completed = subprocess.run(
            ["dsregcmd", "/status"],
            capture_output=True, text=True, timeout=timeout,
            creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
        )
        return completed.stdout or ""
    except (OSError, subprocess.SubprocessError):
        return ""


def parse_dsregcmd(output: str) -> dict[str, str]:
    """
    Extrai os campos relevantes. So le chaves conhecidas; nada de credencial
    aparece na saida do dsregcmd.
    """
    fields: dict[str, str] = {}
    wanted = {"AzureAdJoined", "DeviceId", "TenantId", "TenantName", "DomainJoined"}
    for line in output.splitlines():
        if ":" not in line:
            continue
        key, _, value = line.partition(":")
        key = key.strip()
        if key in wanted:
            fields[key] = value.strip()
    return fields


def evaluate_join(fields: dict[str, str], expected_tenant: str, expected_domain: str) -> tuple[bool, str]:
    """
    Decide se o ingresso e valido. Retorna (ok, motivo). O servidor faz a mesma
    validacao; esta e a checagem local antes de reportar SUCCESS.
    """
    if fields.get("AzureAdJoined", "").upper() != "YES":
        return False, "AzureAdJoined != YES"
    tenant_id = fields.get("TenantId", "").strip().lower()
    if not re.fullmatch(r"[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}", tenant_id):
        return False, "TenantId ausente/invalido"
    expected_tenant = (expected_tenant or "").strip().lower()
    if expected_tenant and tenant_id != expected_tenant:
        return False, "tenant diferente do esperado"
    expected_domain = (expected_domain or "").strip().lower()
    tenant_name = fields.get("TenantName", "").strip().lower()
    # O dsregcmd nem sempre expoe o dominio; quando expoe (TenantName), confere.
    if not expected_tenant and expected_domain and tenant_name and expected_domain not in tenant_name:
        return False, "tenant name nao corresponde ao dominio esperado"
    return True, "ok"


# --------------------------------------------------------------------------- #
#  Cliente da API do Workspace
# --------------------------------------------------------------------------- #

class WorkspaceApi:
    """POST/GET autenticados por token Bearer. So HTTPS, sem redirecionamento."""

    def __init__(self, base_url: str, token: str, machine_guid: str) -> None:
        self.base_url = base_url.rstrip("/")
        self.token = token
        self.machine_guid = machine_guid
        self.opener = build_opener(HTTPSHandler(context=ssl.create_default_context()))

    def _request(self, method: str, path: str, payload: dict | None = None, timeout: int = 30) -> tuple[int, Any]:
        url = self.base_url + path
        data = json.dumps(payload).encode("utf-8") if payload is not None else None
        req = Request(url, data=data, method=method)
        req.add_header("Authorization", f"Bearer {self.token}")
        req.add_header("Accept", "application/json")
        if data is not None:
            req.add_header("Content-Type", "application/json")
        try:
            with self.opener.open(req, timeout=timeout) as response:
                status = getattr(response, "status", 200)
                raw = response.read(65536)
                body = json.loads(raw.decode("utf-8")) if raw else None
                return status, body
        except Exception as exc:  # noqa: BLE001 - erro de rede vira (0, None)
            code = getattr(exc, "code", 0)
            status = int(code) if isinstance(code, int) else 0
            body = None
            # HTTPError traz o JSON de erro da API (ex.: MACHINE_UNKNOWN): le para o log.
            if status and hasattr(exc, "read"):
                try:
                    body = json.loads(exc.read(8192).decode("utf-8"))
                except Exception:  # noqa: BLE001
                    body = None
            return status, body

    def next_step(self) -> tuple[int, dict | None]:
        """(status HTTP, corpo). 200 = etapa; 204 = nada a fazer; resto = erro."""
        status, body = self._request("GET", f"/machines/{self.machine_guid}/step")
        return status, body if isinstance(body, dict) else None

    def progress(self, step_id: int, substate: str, log: str = "") -> bool:
        status, _ = self._request("POST", f"/steps/{step_id}/progress", {"substate": substate, "log": log})
        return status in (200, 202)

    def result(self, step_id: int, result: str, message: str = "", meta: dict | None = None) -> bool:
        payload = {"result": result, "message": message}
        payload.update(meta or {})
        status, _ = self._request("POST", f"/steps/{step_id}/result", payload)
        return status in (200, 202)


# --------------------------------------------------------------------------- #
#  Lancamento do helper na sessao do usuario
# --------------------------------------------------------------------------- #

WTS_ACTIVE = 0


def active_console_session() -> int | None:
    """ID da sessao de console ativa (onde o usuario esta), ou None."""
    try:
        session = ctypes.windll.kernel32.WTSGetActiveConsoleSessionId()
        return None if session == 0xFFFFFFFF else int(session)
    except OSError:
        return None


def launch_helper_in_session(session_id: int, python_exe: Path, script: Path, signal_path: Path, logger: logging.Logger) -> bool:
    """
    Cria o processo do helper na sessao interativa (o servico roda na sessao 0,
    sem acesso a interface do usuario). Usa o token do usuario da sessao.
    Nao passa nenhum segredo por linha de comando.
    """
    advapi32 = ctypes.windll.advapi32
    kernel32 = ctypes.windll.kernel32
    userenv = ctypes.windll.userenv

    token = ctypes.c_void_p()
    if not ctypes.windll.wtsapi32.WTSQueryUserToken(session_id, ctypes.byref(token)):
        logger.warning("Sem token de usuario na sessao %s (ninguem logado?).", session_id)
        return False

    try:
        env = ctypes.c_void_p()
        userenv.CreateEnvironmentBlock(ctypes.byref(env), token, False)

        class STARTUPINFO(ctypes.Structure):
            _fields_ = [
                ("cb", ctypes.c_uint32), ("lpReserved", ctypes.c_wchar_p),
                ("lpDesktop", ctypes.c_wchar_p), ("lpTitle", ctypes.c_wchar_p),
                ("dwX", ctypes.c_uint32), ("dwY", ctypes.c_uint32),
                ("dwXSize", ctypes.c_uint32), ("dwYSize", ctypes.c_uint32),
                ("dwXCountChars", ctypes.c_uint32), ("dwYCountChars", ctypes.c_uint32),
                ("dwFillAttribute", ctypes.c_uint32), ("dwFlags", ctypes.c_uint32),
                ("wShowWindow", ctypes.c_uint16), ("cbReserved2", ctypes.c_uint16),
                ("lpReserved2", ctypes.c_void_p), ("hStdInput", ctypes.c_void_p),
                ("hStdOutput", ctypes.c_void_p), ("hStdError", ctypes.c_void_p),
            ]

        class PROCESS_INFORMATION(ctypes.Structure):
            _fields_ = [
                ("hProcess", ctypes.c_void_p), ("hThread", ctypes.c_void_p),
                ("dwProcessId", ctypes.c_uint32), ("dwThreadId", ctypes.c_uint32),
            ]

        si = STARTUPINFO()
        si.cb = ctypes.sizeof(STARTUPINFO)
        si.lpDesktop = "winsta0\\default"
        pi = PROCESS_INFORMATION()

        # Exe congelado (servico empacotado): o proprio exe roda o helper via
        # --workspace-entra-helper. Modo desenvolvimento (.py): python script --helper.
        if getattr(sys, "frozen", False):
            command = f'"{python_exe}" --workspace-entra-helper "{signal_path}"'
        else:
            command = f'"{python_exe}" "{script}" --helper "{signal_path}"'
        CREATE_UNICODE_ENVIRONMENT = 0x00000400
        CREATE_NO_WINDOW = 0x08000000
        created = advapi32.CreateProcessAsUserW(
            token, None, command, None, None, False,
            CREATE_UNICODE_ENVIRONMENT | CREATE_NO_WINDOW, env, str(script.parent),
            ctypes.byref(si), ctypes.byref(pi),
        )
        if not created:
            logger.warning("CreateProcessAsUserW falhou (erro %s).", kernel32.GetLastError())
            return False
        kernel32.CloseHandle(pi.hProcess)
        kernel32.CloseHandle(pi.hThread)
        if env:
            userenv.DestroyEnvironmentBlock(env)
        return True
    finally:
        kernel32.CloseHandle(token)


# --------------------------------------------------------------------------- #
#  Executor (roda no servico)
# --------------------------------------------------------------------------- #

class WorkspaceEntraExecutor:
    def __init__(self, config: dict[str, Any], logger: logging.Logger) -> None:
        self.logger = logger
        self.api = WorkspaceApi(
            str(config["api_url"]), str(config["api_token"]), str(config["machine_guid"]),
        )
        self.python_exe = Path(sys.executable)
        self.script = Path(__file__).resolve()

    def _state(self) -> dict[str, Any]:
        try:
            return json.loads(STATE_PATH.read_text("utf-8"))
        except (OSError, ValueError):
            return {}

    def _save_state(self, state: dict[str, Any]) -> None:
        try:
            WORKSPACE_DIR.mkdir(parents=True, exist_ok=True)
            STATE_PATH.write_text(json.dumps(state), encoding="utf-8")
        except OSError:
            self.logger.exception("Nao foi possivel gravar o estado do Entra.")

    def _report_idle(self, reason: str) -> None:
        """Loga o motivo de estar parado so quando ele muda (sem encher o log)."""
        global _last_idle_reason
        if reason != _last_idle_reason:
            _last_idle_reason = reason
            level = logging.INFO if reason.startswith("ok") else logging.WARNING
            self.logger.log(level, "Executor Entra do Workspace: %s (machine_guid=%s)", reason, self.api.machine_guid)

    def tick(self) -> None:
        """Uma passada. Chamado periodicamente pelo servico."""
        status, step = self.api.next_step()
        if status == 204:
            self._report_idle("ok, nenhuma etapa Entra para esta maquina")
            return
        if status != 200 or step is None:
            code = ""
            if isinstance(step, dict) and isinstance(step.get("error"), dict):
                code = str(step["error"].get("code", ""))
            reasons = {
                0: "sem conexao com o Workspace (rede/TLS/URL)",
                401: "token ausente/invalido",
                403: "token recusado (regenerado no GLPI? gere o pacote de novo)",
                404: "maquina nao vinculada a um computador (Ativa Remote) ou rota inexistente",
                503: "API do Workspace desabilitada",
            }
            self._report_idle(f"HTTP {status} {code} - {reasons.get(status, 'resposta inesperada')}")
            return
        if step.get("type") != "ENTRA_LOGIN":
            return
        self._report_idle(f"ok, etapa Entra #{step.get('step_id')} em andamento")
        step_id = int(step.get("step_id", 0))
        entra = step.get("entra", {}) if isinstance(step.get("entra"), dict) else {}
        expected_tenant = str(entra.get("expected_tenant", ""))
        expected_domain = str(entra.get("expected_domain", ""))

        state = self._state()
        if state.get("step_id") != step_id:
            # Etapa nova: recomeca o acompanhamento (recupera apos reinicio tambem).
            state = {"step_id": step_id, "started_at": time.time(), "last_helper": 0.0}

        # 1) Precheck / verificacao continua.
        self.api.progress(step_id, PRECHECK, "dsregcmd /status")
        fields = parse_dsregcmd(run_dsregcmd())
        joined, reason = evaluate_join(fields, expected_tenant, expected_domain)
        self.logger.info("Entra: AzureAdJoined=%s (%s)", fields.get("AzureAdJoined", "?"), reason)

        if joined:
            self.api.result(step_id, SUCCESS, "Dispositivo ingressado no Microsoft Entra ID.", {
                "azure_ad_joined": True,
                "device_id": fields.get("DeviceId", ""),
                "tenant_id": fields.get("TenantId", ""),
            })
            STATE_PATH.unlink(missing_ok=True)
            return

        # 2) Ja aguardando o tecnico? Continua verificando ate o timeout.
        current_substate = str(entra.get("substate", ""))
        waiting = current_substate in (WAITING_HUMAN, WAITING_CREDENTIAL_UI, VERIFYING_JOIN)

        if waiting:
            self.api.progress(step_id, VERIFYING_JOIN, "Aguardando autenticacao do tecnico")
            if time.time() - float(state.get("started_at", time.time())) > WAIT_HUMAN_TIMEOUT:
                self.api.result(step_id, FAILED, "Tempo esgotado aguardando a autenticacao.", {"azure_ad_joined": False})
                STATE_PATH.unlink(missing_ok=True)
                return
            self._save_state(state)
            return

        # 3) Nao ingressado e ainda nao aguardando: abre a interface pelo helper.
        if time.time() - float(state.get("last_helper", 0.0)) < HELPER_MIN_INTERVAL:
            return
        session = active_console_session()
        if session is None:
            self.api.progress(step_id, OPENING_SETTINGS, "Sem sessao interativa; aguardando usuario logar")
            self._save_state(state)
            return

        self._write_helper_signal(expected_domain)
        self.api.progress(step_id, OPENING_SETTINGS, "Abrindo Acessar trabalho ou escola")
        launched = launch_helper_in_session(session, self.python_exe, self.script, HELPER_SIGNAL_PATH, self.logger)
        state["last_helper"] = time.time()
        self._save_state(state)

        if not launched:
            self.api.progress(step_id, OPENING_SETTINGS, "Nao foi possivel abrir a interface na sessao do usuario")
            return

        # O helper chega ate a tela de credencial e sinaliza; damos um tempo e,
        # ao ver a tela pronta, marcamos WAITING_HUMAN.
        outcome = self._await_helper(timeout=90)
        if outcome == "credential_ui":
            state["started_at"] = time.time()
            self._save_state(state)
            self.api.result(step_id, WAITING_HUMAN, "Tela de login da Microsoft pronta. Autentique pelo Ativa Remote.", {
                "azure_ad_joined": False,
            })
        elif outcome == "already_joined":
            # Raro: ingressou entre o precheck e o helper.
            self.tick()
        else:
            self.api.progress(step_id, OPENING_SETTINGS, f"Helper: {outcome}")

    def _write_helper_signal(self, expected_domain: str) -> None:
        """Arquivo lido pelo helper: so o dominio para o texto/idioma. Sem segredo."""
        try:
            HELPER_DIR.mkdir(parents=True, exist_ok=True)
            HELPER_SIGNAL_PATH.write_text(json.dumps({
                "expected_domain": expected_domain,
                "requested_at": time.time(),
                "outcome": "",
            }), encoding="utf-8")
        except OSError:
            self.logger.exception("Nao foi possivel preparar o sinal do helper.")

    def _await_helper(self, timeout: int) -> str:
        """Le o 'outcome' que o helper grava no arquivo de sinal."""
        deadline = time.time() + timeout
        while time.time() < deadline:
            try:
                data = json.loads(HELPER_SIGNAL_PATH.read_text("utf-8"))
                if data.get("outcome"):
                    return str(data["outcome"])
            except (OSError, ValueError):
                pass
            time.sleep(2)
        return "timeout"


# --------------------------------------------------------------------------- #
#  Helper interativo (roda na sessao do usuario)
# --------------------------------------------------------------------------- #

def helper_main(signal_path: Path) -> int:
    """
    Abre "Acessar trabalho ou escola", aciona Conectar -> Ingressar no Entra ID
    e para quando a tela de login da Microsoft aparecer.

    NUNCA le, digita ou captura credenciais. Nao usa coordenadas fixas: procura
    os controles por UI Automation (nome/AutomationId), com texto pt-BR/en-US
    como referencia.
    """
    def signal(outcome: str) -> None:
        try:
            data = json.loads(signal_path.read_text("utf-8")) if signal_path.exists() else {}
        except (OSError, ValueError):
            data = {}
        data["outcome"] = outcome
        data["finished_at"] = time.time()
        try:
            signal_path.write_text(json.dumps(data), encoding="utf-8")
        except OSError:
            pass

    # Abre direto a pagina certa pelo URI oficial do Windows (sem navegar pela
    # tela inicial das Configuracoes).
    try:
        os.startfile("ms-settings:workplace")  # noqa: S606 - URI oficial do Windows
    except OSError:
        signal("settings_failed")
        return 1

    try:
        import uiautomation as auto  # type: ignore
    except ImportError:
        # Sem a lib de automacao: abriu as Configuracoes, mas o resto e manual.
        signal("credential_ui")  # deixa o tecnico seguir pelo Ativa Remote
        return 0

    # Textos equivalentes por idioma para localizar os controles.
    connect_texts = ["Conectar", "Connect"]
    entra_join_texts = [
        "Ingressar este dispositivo no Microsoft Entra ID",
        "Join this device to Microsoft Entra ID",
        "Ingressar este dispositivo no Azure Active Directory",
        "Join this device to Azure Active Directory",
    ]
    credential_hints = ["entrar", "sign in", "trabalho ou escola", "work or school", "microsoft"]

    def find_by_texts(texts: list[str], control_type: Any, timeout: float) -> Any:
        deadline = time.time() + timeout
        lowered = [t.lower() for t in texts]
        while time.time() < deadline:
            for control in control_type(searchDepth=40):
                name = (control.Name or "").strip().lower()
                if name and any(hint in name for hint in lowered):
                    return control
            time.sleep(1)
        return None

    try:
        auto.uiautomation.SetGlobalSearchTimeout(2)
        # Conectar
        connect = find_by_texts(connect_texts, auto.ButtonControl, timeout=25)
        if connect is None:
            signal("connect_not_found")
            return 0
        connect.Click(simulateMove=False)

        # Ingressar no Microsoft Entra ID (link na janela de conexao)
        join = find_by_texts(entra_join_texts, auto.HyperlinkControl, timeout=20) \
            or find_by_texts(entra_join_texts, auto.TextControl, timeout=5)
        if join is None:
            signal("entra_option_not_found")
            return 0
        try:
            join.Click(simulateMove=False)
        except Exception:  # noqa: BLE001 - alguns controles nao expoem Click
            join.GetInvokePattern().Invoke()

        # Aguarda a janela de login da Microsoft (WebView/janela nova). NAO
        # interage com os campos: so confirma que a tela apareceu.
        deadline = time.time() + 40
        while time.time() < deadline:
            for win in auto.WindowControl(searchDepth=2, foundIndex=1):
                title = (win.Name or "").lower()
                if any(hint in title for hint in credential_hints):
                    signal("credential_ui")
                    return 0
            # Fallback: qualquer janela cujo texto sugira login Microsoft.
            time.sleep(1.5)
        signal("credential_ui")  # segue mesmo assim: o tecnico assume pelo Ativa Remote
        return 0
    except Exception as exc:  # noqa: BLE001
        signal(f"helper_error:{type(exc).__name__}")
        return 1


# --------------------------------------------------------------------------- #
#  Config / entrypoint
# --------------------------------------------------------------------------- #

_last_idle_reason = ""
_config_missing_logged = False


def load_config() -> dict[str, Any] | None:
    """Config gravada pelo instalador: {api_url, api_token}. machine_guid vem do proprio host."""
    try:
        config = json.loads(CONFIG_PATH.read_text("utf-8"))
    except (OSError, ValueError):
        return None
    if not config.get("api_url") or not config.get("api_token"):
        return None
    config.setdefault("machine_guid", machine_guid())
    return config


def machine_guid() -> str:
    """MachineGuid do registro (o mesmo que os outros servicos Ativa usam)."""
    try:
        import winreg  # type: ignore
        # KEY_WOW64_64KEY: mesma leitura do Ativa Updater. Sem ela, um processo
        # 32 bits le o ramo Wow6432Node (sem MachineGuid) e o GUID nao bate com
        # o registrado no Ativa Remote -> o Workspace nao acha o computador.
        with winreg.OpenKey(
            winreg.HKEY_LOCAL_MACHINE,
            r"SOFTWARE\Microsoft\Cryptography",
            0,
            winreg.KEY_READ | winreg.KEY_WOW64_64KEY,
        ) as key:
            value, _ = winreg.QueryValueEx(key, "MachineGuid")
            return str(value).strip().lower()
    except OSError:
        import socket
        import hashlib
        return hashlib.sha256(socket.gethostname().encode()).hexdigest()[:32]


def run_executor_tick(logger: logging.Logger) -> None:
    """Ponto de entrada para o servico chamar a cada ciclo (curto e sem excecao)."""
    global _config_missing_logged
    config = load_config()
    if config is None:
        if not _config_missing_logged:
            _config_missing_logged = True
            logger.warning("Executor Entra do Workspace inativo: %s ausente ou sem api_url/api_token.", CONFIG_PATH)
        return
    try:
        WorkspaceEntraExecutor(config, logger).tick()
    except Exception:  # noqa: BLE001 - nunca derruba o loop do servico
        logger.exception("Falha no executor Entra do Workspace.")


def main(argv: list[str]) -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--helper", metavar="SIGNAL_JSON", help="roda como helper interativo")
    parser.add_argument("--once", action="store_true", help="roda um tick do executor e sai")
    args = parser.parse_args(argv)

    logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
    logger = logging.getLogger("ativa-workspace-entra")

    if args.helper:
        return helper_main(Path(args.helper))
    if args.once:
        run_executor_tick(logger)
        return 0
    parser.print_help()
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
