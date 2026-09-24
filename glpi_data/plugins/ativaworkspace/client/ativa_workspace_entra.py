"""
Ativa Workspace - biblioteca da etapa ENTRA_LOGIN.

Funcoes puras usadas pelo servico Ativa Workspace:
  - leitura da config (api_url + token) gravada pelo instalador;
  - cliente da API do Workspace (token Bearer, so HTTPS);
  - leitura e avaliacao do `dsregcmd /status`.

Nenhuma credencial Microsoft passa por aqui. A prova de ingresso e so o que o
proprio Windows informa (AzureAdJoined, DeviceId, TenantId).
"""

from __future__ import annotations

import json
import os
import re
import ssl
import subprocess
from pathlib import Path
from typing import Any
from urllib.request import HTTPSHandler, Request, build_opener

PROGRAM_DATA = Path(os.environ.get("PROGRAMDATA", r"C:\ProgramData"))
# Config e estado do servico: acesso so para SYSTEM e Administradores.
WORKSPACE_DIR = PROGRAM_DATA / "AtivaLocacao" / "Workspace"
CONFIG_PATH = WORKSPACE_DIR / "config.json"

GUID_RE = re.compile(r"^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$")


# --------------------------------------------------------------------------- #
#  Config e identidade
# --------------------------------------------------------------------------- #

def load_config(path: Path = CONFIG_PATH) -> dict[str, Any] | None:
    """{api_url, api_token} gravados pelo instalador, ou None se ausente/invalido."""
    try:
        config = json.loads(path.read_text("utf-8"))
    except (OSError, ValueError):
        return None
    if not isinstance(config, dict):
        return None
    api_url = str(config.get("api_url", ""))
    token = str(config.get("api_token", ""))
    if not api_url.startswith("https://") or not re.fullmatch(r"[a-fA-F0-9]{64}", token):
        return None
    return {"api_url": api_url.rstrip("/"), "api_token": token}


def machine_guid() -> str:
    """MachineGuid do registro, lido igual ao Ativa Updater (ramo de 64 bits)."""
    import winreg  # type: ignore

    with winreg.OpenKey(
        winreg.HKEY_LOCAL_MACHINE,
        r"SOFTWARE\Microsoft\Cryptography",
        0,
        winreg.KEY_READ | winreg.KEY_WOW64_64KEY,
    ) as key:
        value, _ = winreg.QueryValueEx(key, "MachineGuid")
        return str(value).strip().lower()


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
    """So as chaves que interessam; a saida do dsregcmd nao tem credenciais."""
    wanted = {"AzureAdJoined", "DeviceId", "TenantId", "TenantName", "DomainJoined"}
    fields: dict[str, str] = {}
    for line in output.splitlines():
        key, sep, value = line.partition(":")
        if sep and key.strip() in wanted:
            fields[key.strip()] = value.strip()
    return fields


def evaluate_join(fields: dict[str, str], expected_tenant: str, expected_domain: str) -> tuple[bool, str]:
    """
    (ingressado_certo, motivo). O servidor valida de novo o TenantId: esta e so
    a checagem local antes de reportar SUCCESS.
    """
    if fields.get("AzureAdJoined", "").strip().upper() != "YES":
        return False, "AzureAdJoined diferente de YES"
    tenant_id = fields.get("TenantId", "").strip().lower()
    if not GUID_RE.fullmatch(tenant_id):
        return False, "TenantId ausente ou invalido"
    expected_tenant = (expected_tenant or "").strip().lower()
    if expected_tenant and tenant_id != expected_tenant:
        return False, "ingressado em outro tenant"
    expected_domain = (expected_domain or "").strip().lower()
    tenant_name = fields.get("TenantName", "").strip().lower()
    if not expected_tenant and expected_domain and tenant_name and expected_domain.split(".")[0] not in tenant_name:
        return False, "nome do tenant nao corresponde ao dominio esperado"
    return True, "ok"


# --------------------------------------------------------------------------- #
#  API do Workspace
# --------------------------------------------------------------------------- #

class WorkspaceApi:
    """Chamadas autenticadas por token Bearer. So HTTPS, com timeout."""

    def __init__(self, base_url: str, token: str, guid: str) -> None:
        self.base_url = base_url.rstrip("/")
        self.token = token
        self.machine_guid = guid
        self.opener = build_opener(HTTPSHandler(context=ssl.create_default_context()))

    def _request(self, method: str, path: str, payload: dict | None = None, timeout: int = 20) -> tuple[int, Any]:
        data = json.dumps(payload).encode("utf-8") if payload is not None else None
        request = Request(self.base_url + path, data=data, method=method)
        request.add_header("Authorization", f"Bearer {self.token}")
        request.add_header("Accept", "application/json")
        if data is not None:
            request.add_header("Content-Type", "application/json")
        try:
            with self.opener.open(request, timeout=timeout) as response:
                raw = response.read(65536)
                return getattr(response, "status", 200), (json.loads(raw.decode("utf-8")) if raw else None)
        except Exception as exc:  # noqa: BLE001 - erro vira (status, corpo do erro)
            status = getattr(exc, "code", 0)
            status = int(status) if isinstance(status, int) else 0
            body = None
            if status and hasattr(exc, "read"):
                try:
                    body = json.loads(exc.read(8192).decode("utf-8"))
                except Exception:  # noqa: BLE001
                    body = None
            return status, body

    def next_step(self) -> tuple[int, dict | None]:
        """200 = etapa; 204 = nada a fazer; outros = erro (ver corpo)."""
        status, body = self._request("GET", f"/machines/{self.machine_guid}/step")
        return status, body if isinstance(body, dict) else None

    def progress(self, step_id: int, substate: str, log: str = "") -> bool:
        status, _ = self._request("POST", f"/steps/{step_id}/progress", {"substate": substate, "log": log[:200]})
        return status in (200, 202)

    def result(self, step_id: int, result: str, message: str = "", meta: dict | None = None) -> bool:
        payload: dict[str, Any] = {"result": result, "message": message[:200]}
        payload.update(meta or {})
        status, _ = self._request("POST", f"/steps/{step_id}/result", payload)
        return status in (200, 202)


def describe_http_error(status: int, body: dict | None) -> str:
    """Motivo legivel para o log quando a API nao devolve uma etapa."""
    code = ""
    if isinstance(body, dict) and isinstance(body.get("error"), dict):
        code = str(body["error"].get("code", ""))
    reasons = {
        0: "sem conexao com o Workspace (rede, TLS ou URL)",
        401: "token ausente ou invalido",
        403: "token recusado (foi regenerado no GLPI? gere o pacote de novo)",
        404: "maquina nao vinculada a um computador (Ativa Remote) ou rota inexistente",
        503: "API do Workspace desabilitada",
    }
    return f"HTTP {status} {code} - {reasons.get(status, 'resposta inesperada')}".strip()
