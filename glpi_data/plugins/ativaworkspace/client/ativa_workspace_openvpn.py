"""
Ativa Workspace - configuracao OpenVPN.

Fluxo (servico SYSTEM + OpenVPN GUI na sessao do usuario):
  1. o .zip do perfil (baixado da etapa, SHA-256 conferido) e extraido, com
     validacao de caminhos, em C:\\Program Files\\OpenVPN\\config\\<pasta>;
  2. usuario/senha da VPN vao para um arquivo ao lado do .ovpn (formato do
     OpenVPN: `auth-user-pass arquivo`), com acesso so para SYSTEM,
     Administradores e o usuario da sessao. Nunca vao para log;
  3. o OpenVPN GUI (o icone da bandeja) conecta o perfil na sessao do usuario;
  4. o log do OpenVPN do usuario confirma a conexao ou o erro de login.
"""

from __future__ import annotations

import os
import re
import subprocess
import zipfile
from pathlib import Path

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)

# Servico 32 ou 64 bits: o OpenVPN fica no Program Files de 64 bits.
PROGRAM_FILES = Path(os.environ.get("ProgramW6432") or os.environ.get("ProgramFiles", r"C:\Program Files"))
OPENVPN_HOME = PROGRAM_FILES / "OpenVPN"
GUI_EXE = OPENVPN_HOME / "bin" / "openvpn-gui.exe"
CONFIG_DIR = OPENVPN_HOME / "config"
WINGET_ID = "OpenVPNTechnologies.OpenVPN"

AUTH_FILE_NAME = "ativa-vpn.auth"
ALLOWED_EXTENSIONS = {"ovpn", "conf", "crt", "cer", "pem", "key", "p12", "pfx", "txt", "tlsauth", "ta"}
MAX_UNCOMPRESSED = 10 * 1024 * 1024
AUTH_LINE_RE = re.compile(r"^\s*auth-user-pass(\s|$)", re.IGNORECASE)


def installed() -> bool:
    return GUI_EXE.is_file()


def _safe_member(name: str) -> str:
    """Nome do zip normalizado ('/'), ou "" se tiver caminho perigoso."""
    name = name.replace("\\", "/")
    if not name or name.startswith("/") or re.match(r"^[a-zA-Z]:", name):
        return ""
    if any(part == ".." for part in name.split("/")):
        return ""
    return name


def extract_profile(zip_path: Path, ovpn_name: str) -> Path:
    """
    Extrai o perfil em CONFIG_DIR e devolve o caminho do .ovpn. Se o .zip nao
    tiver pasta (arquivos soltos), cria uma com o nome do .ovpn.

    Raises ValueError com a mensagem para o painel.
    """
    base = CONFIG_DIR.resolve()
    with zipfile.ZipFile(zip_path) as archive:
        members = [info for info in archive.infolist() if not info.is_dir()]
        names = [_safe_member(info.filename) for info in members]
        if not members or "" in names:
            raise ValueError("O .zip do perfil tem um caminho invalido.")
        if sum(info.file_size for info in members) > MAX_UNCOMPRESSED:
            raise ValueError("O conteudo do .zip do perfil passa de 10 MB.")
        ovpn_name = ovpn_name.replace("\\", "/")
        if ovpn_name not in names:
            found = [n for n in names if n.lower().endswith(".ovpn")]
            if len(found) != 1:
                raise ValueError("O .zip precisa ter exatamente um arquivo .ovpn.")
            ovpn_name = found[0]
        prefix = "" if "/" in ovpn_name else Path(ovpn_name).stem + "/"

        for info, name in zip(members, names):
            if Path(name).suffix.lower().lstrip(".") not in ALLOWED_EXTENSIONS:
                raise ValueError(f"Arquivo nao permitido no perfil: {Path(name).name}")
            target = (base / (name if "/" in name else prefix + name)).resolve()
            if os.path.commonpath([str(base), str(target)]) != str(base):
                raise ValueError("O .zip do perfil tenta gravar fora da pasta do OpenVPN.")
            target.parent.mkdir(parents=True, exist_ok=True)
            with archive.open(info) as source, open(target, "wb") as handle:
                handle.write(source.read(MAX_UNCOMPRESSED + 1)[:MAX_UNCOMPRESSED])
    return base / (ovpn_name if "/" in ovpn_name else prefix + ovpn_name)


def _restrict_acl(path: Path, user_sid: str) -> bool:
    """Acesso so para SYSTEM, Administradores (total) e o usuario da sessao (leitura)."""
    completed = subprocess.run(
        ["icacls", str(path), "/inheritance:r",
         "/grant:r", "*S-1-5-18:(F)", "*S-1-5-32-544:(F)", f"*{user_sid}:(R)"],
        capture_output=True, text=True, timeout=30, creationflags=NO_WINDOW,
    )
    return completed.returncode == 0


def apply_credentials(ovpn: Path, username: str, password: str, user_sid: str) -> tuple[bool, str]:
    """
    Grava usuario/senha no arquivo de autenticacao do perfil e aponta o .ovpn
    para ele. O arquivo e criado vazio, recebe a ACL restrita e so depois
    recebe o conteudo (nunca fica legivel para outros usuarios).
    """
    if not re.fullmatch(r"S-1-5-21-[\d-]+|S-1-12-1-[\d-]+", user_sid or ""):
        return False, "Nao foi possivel identificar o usuario da sessao para proteger a senha da VPN."
    if any(ch in username + password for ch in "\r\n"):
        return False, "Usuario ou senha da VPN com caracteres invalidos."

    auth = ovpn.with_name(AUTH_FILE_NAME)
    try:
        auth.write_text("", "utf-8")
        if not _restrict_acl(auth, user_sid):
            auth.unlink(missing_ok=True)
            return False, "Nao foi possivel proteger o arquivo de credenciais da VPN (icacls)."
        with open(auth, "w", encoding="utf-8", newline="\n") as handle:
            handle.write(f"{username}\n{password}\n")

        raw = ovpn.read_bytes()
        text = raw.decode("utf-8-sig", errors="replace")
        newline = "\r\n" if "\r\n" in text else "\n"
        lines = [line for line in text.splitlines() if not AUTH_LINE_RE.match(line)]
        # Relativo: o OpenVPN GUI roda o perfil a partir da pasta do .ovpn.
        lines.append(f"auth-user-pass {AUTH_FILE_NAME}")
        ovpn.write_text(newline.join(lines) + newline, "utf-8")
    except OSError as exc:
        return False, f"Falha ao gravar o perfil da VPN: {exc}"
    return True, "Perfil e credenciais da VPN aplicados."


def connect_arguments(profile_name: str, gui_running: bool) -> list[str]:
    """Argumentos do openvpn-gui.exe para conectar o perfil (na ordem)."""
    if gui_running:
        # GUI ja aberto: relê a pasta (perfil novo) e manda conectar.
        return ["--command rescan", f'--command connect "{profile_name}"']
    return [f'--connect "{profile_name}.ovpn"']


def _profile_dir(user_sid: str) -> Path | None:
    import winreg  # type: ignore

    try:
        with winreg.OpenKey(
            winreg.HKEY_LOCAL_MACHINE,
            r"SOFTWARE\Microsoft\Windows NT\CurrentVersion\ProfileList" + "\\" + user_sid,
            0, winreg.KEY_READ | winreg.KEY_WOW64_64KEY,
        ) as key:
            return Path(os.path.expandvars(str(winreg.QueryValueEx(key, "ProfileImagePath")[0])))
    except OSError:
        return None


def connection_status(user_sid: str, profile_name: str, since_epoch: float) -> tuple[str, str]:
    """
    ("connected" | "auth_failed" | "error" | "connecting", detalhe). Le o log
    que o OpenVPN GUI grava por perfil em %USERPROFILE%\\OpenVPN\\log.
    """
    profile = _profile_dir(user_sid)
    if profile is None:
        return "connecting", "perfil do usuario nao encontrado"
    log = profile / "OpenVPN" / "log" / f"{profile_name}.log"
    try:
        if log.stat().st_mtime < since_epoch - 5:
            return "connecting", "aguardando o OpenVPN iniciar"
        with open(log, "rb") as handle:
            handle.seek(0, os.SEEK_END)
            size = handle.tell()
            handle.seek(max(0, size - 65536))
            text = handle.read().decode("utf-8", errors="replace")
    except OSError:
        return "connecting", "aguardando o log do OpenVPN"

    if "AUTH_FAILED" in text:
        return "auth_failed", "usuario ou senha da VPN recusados pelo servidor"
    if "Initialization Sequence Completed" in text:
        return "connected", "VPN conectada"
    if "Exiting due to fatal error" in text or "SIGTERM" in text:
        lines = [line.strip() for line in text.splitlines() if line.strip()]
        last = next((line for line in reversed(lines) if "error" in line.lower()), lines[-1] if lines else "")
        # Tira a data do inicio da linha; o log do OpenVPN nao contem a senha.
        return "error", re.sub(r"^\w{3} \w{3} +\d+ [\d:]+ \d{4} ", "", last)[:160]
    return "connecting", "negociando a conexao"
