import argparse
import ctypes
from ctypes import wintypes
import json
import logging
import os
from pathlib import Path
import platform
import subprocess
import sys
import time
import urllib.request
import urllib.error

# Constants
SERVICE_NAME = "AtivaRemoteService"
RUSTDESK_PATH = Path(os.environ.get('PROGRAMDATA', 'C:\\ProgramData')) / "AtivaLocacao" / "UnifiedUpdater" / "rustdesk.exe"
RUSTDESK_SERVICE_NAME = "RustDesk"

def setup_logging():
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s [%(levelname)s] %(message)s',
        handlers=[
            logging.FileHandler(Path(os.environ.get('PROGRAMDATA', 'C:\\ProgramData')) / "AtivaLocacao" / "UnifiedUpdater" / "ativaremote.log"),
            logging.StreamHandler(sys.stdout)
        ]
    )

def ensure_rustdesk_service():
    """Install and start the RustDesk service if it's not running."""
    try:
        # Check if service exists
        result = subprocess.run(["sc", "query", RUSTDESK_SERVICE_NAME], capture_output=True, text=True)
        if "FAILED_1060" in result.stdout or "não existe" in result.stdout or result.returncode != 0:
            logging.info("Instalando servico RustDesk...")
            subprocess.run([str(RUSTDESK_PATH), "--silent-install"], check=False)
            time.sleep(2)
        
        # Ensure it's running
        result = subprocess.run(["sc", "query", RUSTDESK_SERVICE_NAME], capture_output=True, text=True)
        if "STOPPED" in result.stdout:
            logging.info("Iniciando servico RustDesk...")
            subprocess.run(["sc", "start", RUSTDESK_SERVICE_NAME], check=False)
            
    except Exception as e:
        logging.error(f"Erro ao gerenciar RustDesk: {e}")

def get_rustdesk_id_password():
    """Read ID and Password from RustDesk2.toml config."""
    config_path = Path(os.environ.get('WINDIR', 'C:\\Windows')) / "System32" / "config" / "systemprofile" / "AppData" / "Roaming" / "RustDesk" / "config" / "RustDesk2.toml"
    
    # If not in systemprofile, try local appdata (depends on how RustDesk was installed)
    if not config_path.exists():
        config_path = Path(os.environ.get('PROGRAMDATA', 'C:\\ProgramData')) / "RustDesk" / "config" / "RustDesk2.toml"

    if not config_path.exists():
        logging.warning("Configuracao do RustDesk nao encontrada.")
        return None, None

    rustdesk_id = None
    rustdesk_password = None

    try:
        with open(config_path, 'r', encoding='utf-8') as f:
            for line in f:
                if line.startswith("id ="):
                    rustdesk_id = line.split("=")[1].strip().strip("'").strip('"')
                elif line.startswith("password ="):
                    rustdesk_password = line.split("=")[1].strip().strip("'").strip('"')
    except Exception as e:
        logging.error(f"Erro ao ler configuracao do RustDesk: {e}")

    return rustdesk_id, rustdesk_password

def get_active_console_session_id():
    """Get the active console session ID using WTSGetActiveConsoleSessionId."""
    kernel32 = ctypes.WinDLL("kernel32")
    kernel32.WTSGetActiveConsoleSessionId.restype = wintypes.DWORD
    return kernel32.WTSGetActiveConsoleSessionId()

def ask_user_consent(timeout_seconds=60):
    """Ask for user consent using WTSSendMessageW."""
    session_id = get_active_console_session_id()
    if session_id == 0xFFFFFFFF:
        logging.warning("Nenhuma sessao de console ativa encontrada.")
        return False

    wtsapi32 = ctypes.WinDLL("wtsapi32")
    WTS_CURRENT_SERVER_HANDLE = 0
    
    title = "Solicitacao de Suporte Remoto - Ativa"
    message = "A equipe de TI da Ativa esta solicitando acesso remoto a sua maquina para prestar suporte.\n\nVoce autoriza o acesso?"
    
    MB_YESNO = 0x04
    MB_ICONQUESTION = 0x20
    MB_TOPMOST = 0x40000
    MB_SETFOREGROUND = 0x10000
    
    style = MB_YESNO | MB_ICONQUESTION | MB_TOPMOST | MB_SETFOREGROUND
    
    response = wintypes.DWORD()
    
    logging.info(f"Enviando prompt de consentimento para sessao {session_id}...")
    
    result = wtsapi32.WTSSendMessageW(
        WTS_CURRENT_SERVER_HANDLE,
        session_id,
        title, len(title) * 2,
        message, len(message) * 2,
        style,
        timeout_seconds,
        ctypes.byref(response),
        True
    )
    
    if result == 0:
        logging.warning("Falha ao enviar mensagem ou timeout atingido.")
        return False
        
    IDYES = 6
    if response.value == IDYES:
        logging.info("Usuario PERMITIU o acesso.")
        return True
        
    logging.info("Usuario NEGOU o acesso.")
    return False

def api_request(url, payload):
    req = urllib.request.Request(url, data=json.dumps(payload).encode('utf-8'), headers={'Content-Type': 'application/json'}, method='POST')
    try:
        with urllib.request.urlopen(req, timeout=10) as response:
            return json.loads(response.read().decode('utf-8'))
    except Exception as e:
        logging.error(f"Erro na API {url}: {e}")
        return None

def main():
    setup_logging()
    logging.info("Ativa Remote Client iniciado.")
    
    # In a real scenario, these should be passed via CLI or config file.
    # We will hardcode for now based on the unified installer's knowledge.
    API_URL = "https://chamados.ativalocacao.com.br:8443/plugins/ativaremote/front/api.php"
    
    # Try to read Machine GUID (same as Wallpaper/Updater)
    guid_path = Path("C:\\ProgramData\\AtivaLocacao\\machine_guid.txt")
    if not guid_path.exists():
        logging.error("machine_guid.txt nao encontrado. Abortando.")
        return 1
        
    machine_guid = guid_path.read_text().strip()
    hostname = platform.node()
    
    ensure_rustdesk_service()
    rustdesk_id, rustdesk_pwd = get_rustdesk_id_password()
    
    payload = {
        "action": "register",
        "hostname": hostname,
        "machine_guid": machine_guid,
        "client_version": "1.0.0",
        "rustdesk_id": rustdesk_id,
        "rustdesk_password": rustdesk_pwd
    }
    
    res = api_request(API_URL, payload)
    if not res or 'token' not in res:
        logging.error("Falha ao registrar.")
        return 1
        
    token = res['token']
    logging.info("Registrado com sucesso.")
    
    poll_interval = 10
    
    while True:
        try:
            rustdesk_id, rustdesk_pwd = get_rustdesk_id_password()
            payload = {
                "action": "heartbeat",
                "token": token,
                "rustdesk_id": rustdesk_id,
                "rustdesk_password": rustdesk_pwd
            }
            
            hb_res = api_request(API_URL, payload)
            if hb_res and hb_res.get('status') == 'ok':
                status = hb_res.get('remote_access_status')
                if status == 'pending':
                    # Ask user!
                    accepted = ask_user_consent(timeout_seconds=60)
                    new_status = 'accepted' if accepted else 'rejected'
                    
                    # Report back immediately
                    api_request(API_URL, {
                        "action": "heartbeat",
                        "token": token,
                        "remote_access_status": new_status
                    })
                    
                poll_interval = hb_res.get('next_check_seconds', 10)
            
        except Exception as e:
            logging.error(f"Erro no loop principal: {e}")
            
        time.sleep(poll_interval)

if __name__ == "__main__":
    main()
