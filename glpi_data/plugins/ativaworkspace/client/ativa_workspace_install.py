"""
Ativa Workspace - execucao da etapa SOFTWARE.

Dois metodos, escolhidos pelo servidor:
  - winget: instala pelo gerenciador do Windows (App Installer), escopo maquina;
  - file:   baixa o instalador enviado ao catalogo (conferido por SHA-256) e
            instala em silencio (MSI /qn, EXE com os argumentos do app).

Nenhum comando/script livre: o servidor so manda o ID do winget, o tipo do
instalador e os argumentos silenciosos ja validados. Nada aqui monta comando a
partir de texto arbitrario.
"""

from __future__ import annotations

import hashlib
import logging
import os
import shlex
import subprocess
import tempfile
from pathlib import Path

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)


def _run(args: list[str], timeout: int) -> tuple[int, str]:
    """Executa e devolve (codigo, ultima linha util da saida)."""
    try:
        completed = subprocess.run(
            args, capture_output=True, text=True, timeout=timeout,
            creationflags=NO_WINDOW,
        )
    except subprocess.TimeoutExpired:
        return 1460, "tempo de instalacao esgotado"
    except OSError as exc:
        return 1, f"nao foi possivel executar: {exc}"
    output = (completed.stdout or "") + (completed.stderr or "")
    tail = " ".join(output.split())[-180:]
    return completed.returncode, tail


def _winget_path() -> str | None:
    """
    winget nao fica no PATH do SYSTEM: resolve o winget.exe real dentro de
    WindowsApps. Sem ele, a instalacao por winget nao e possivel.
    """
    from glob import glob

    base = Path(os.environ.get("ProgramFiles", r"C:\Program Files")) / "WindowsApps"
    matches = sorted(glob(str(base / "Microsoft.DesktopAppInstaller_*" / "winget.exe")))
    return matches[-1] if matches else None


def _install_winget(winget_id: str, timeout: int, logger: logging.Logger) -> tuple[bool, str]:
    exe = _winget_path()
    if exe is None:
        return False, "winget (App Installer) nao encontrado nesta maquina."
    args = [
        exe, "install", "--id", winget_id, "--exact", "--silent",
        "--scope", "machine", "--accept-package-agreements",
        "--accept-source-agreements", "--disable-interactivity",
    ]
    logger.info("Software: winget install %s", winget_id)
    code, tail = _run(args, timeout)
    # 0 = ok; -1978335189 = ja instalado/atualizado (No applicable update).
    if code in (0, -1978335189, 0x8A15002B - 0x100000000):
        return True, "Instalado pelo winget."
    return False, f"winget falhou (codigo {code}). {tail}"


def _sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with open(path, "rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 256), b""):
            digest.update(chunk)
    return digest.hexdigest()


def _install_file(path: Path, installer_type: str, install_args: str,
                  timeout: int, logger: logging.Logger) -> tuple[bool, str]:
    installer_type = (installer_type or "OTHER").upper()
    if installer_type == "MSI":
        args = ["msiexec", "/i", str(path), "/qn", "/norestart"]
    elif installer_type == "EXE":
        extra = shlex.split(install_args) if install_args else []
        if not extra:
            logger.warning("Software: EXE sem argumentos silenciosos; pode abrir a UI.")
        args = [str(path), *extra]
    elif installer_type == "OTHER" and path.suffix.lower() in (".msix", ".msixbundle", ".appx", ".appxbundle"):
        args = ["powershell", "-NoProfile", "-NonInteractive", "-Command",
                "Add-AppxProvisionedPackage", "-Online", "-PackagePath", str(path), "-SkipLicense"]
    else:
        return False, f"Tipo de instalador '{installer_type}' nao tem instalacao automatica."

    logger.info("Software: instalando %s (%s)", path.name, installer_type)
    code, tail = _run(args, timeout)
    # 0 ok; 3010/1641 ok pedindo reinicio.
    if code in (0, 1641, 3010):
        return True, "Instalado a partir do pacote enviado."
    return False, f"Instalador terminou com codigo {code}. {tail}"


def install(payload: dict, api, step_id: int, logger: logging.Logger) -> tuple[bool, str]:
    """
    Executa a etapa SOFTWARE. Reporta o andamento pelo `api` e devolve
    (ok, mensagem) para o resultado final.
    """
    method = str(payload.get("method", "none"))
    timeout = max(60, int(payload.get("timeout_minutes", 30) or 30) * 60)
    name = str(payload.get("name", "aplicativo"))

    if method == "winget":
        api.progress(step_id, "INSTALLING", f"Instalando {name} pelo winget")
        return _install_winget(str(payload.get("winget_id", "")), timeout, logger)

    if method == "file":
        api.progress(step_id, "DOWNLOADING", f"Baixando o instalador de {name}")
        tmp_dir = Path(tempfile.gettempdir()) / "AtivaWorkspaceInstall"
        tmp_dir.mkdir(parents=True, exist_ok=True)
        dest = tmp_dir / f"step-{step_id}.bin"
        ok, server_sha, error = api.download_installer(step_id, dest, timeout=timeout)
        if not ok:
            return False, f"Falha ao baixar o instalador: {error}"
        expected = str(payload.get("file_sha256", "")).strip().lower() or server_sha
        actual = _sha256(dest)
        if expected and actual != expected:
            dest.unlink(missing_ok=True)
            return False, "SHA-256 do instalador nao confere; download recusado."
        api.progress(step_id, "INSTALLING", f"Instalando {name}")
        try:
            return _install_file(dest, str(payload.get("installer_type", "OTHER")),
                                 str(payload.get("install_args", "")), timeout, logger)
        finally:
            dest.unlink(missing_ok=True)

    return False, "Aplicativo sem ID do winget nem instalador enviado."
