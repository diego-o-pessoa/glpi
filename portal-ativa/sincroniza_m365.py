import requests
 
# ================= CONFIGURAÇÕES MICROSOFT 365 =================
TENANT_ID = "382dad99-5217-404f-9f06-a72dab837494"
CLIENT_ID = "51b48310-9369-42dc-847c-f89d940978bc"
CLIENT_SECRET = "1__8Q~V36z9ncmhbA5mtqaTAsGrrlaZT76q3AcS9"
 
# ================= CONFIGURAÇÕES DO GLPI =================
GLPI_URL = "http://10.117.41.6:8091/apirest.php"
APP_TOKEN = "EaYmVTLPjMsbCt6JN3A18Tu3uB8315FLibAFnErD"
USER_TOKEN = "Zflqy5rID7gHiK1qsntmvTDS7DXEkaJMaScOPPe3"
 
 
def obter_token_ms():
    """Autentica no Microsoft Entra ID via Client Credentials"""
    print("[*] Autenticando na Microsoft...")
    url = f"https://login.microsoftonline.com/{TENANT_ID}/oauth2/v2.0/token"
    payload = {
        "client_id": CLIENT_ID,
        "scope": "https://graph.microsoft.com/.default",
        "client_secret": CLIENT_SECRET,
        "grant_type": "client_credentials"
    }
    res = requests.post(url, data=payload)
    if res.status_code == 200:
        return res.json().get("access_token")
    else:
        print(f"[!] Erro MS: {res.text}")
        exit()
 
 
def obter_usuarios_m365(token):
    """Busca todos os usuários do M365 (com paginação automática)"""
    print("[*] Baixando lista de usuários do M365...")
    usuarios = []
    # Selecionamos apenas os campos necessários para economizar banda
    url = "https://graph.microsoft.com/v1.0/users?$select=displayName,userPrincipalName,accountEnabled,jobTitle,mobilePhone&$top=500"
    headers = {"Authorization": f"Bearer {token}"}
 
    while url:
        res = requests.get(url, headers=headers)
        if res.status_code == 200:
            dados = res.json()
            usuarios.extend(dados.get('value', []))
            # Se houver mais usuários, a Microsoft devolve o link da próxima página
            url = dados.get('@odata.nextLink') 
        else:
            print(f"[!] Erro ao buscar usuários: {res.text}")
            break
    print(f"[*] Total de {len(usuarios)} usuários encontrados no M365.")
    return usuarios
 
 
def obter_sessao_glpi():
    """Autentica na API do GLPI"""
    print("[*] Iniciando sessão no GLPI...")
    headers = {
        "App-Token": APP_TOKEN,
        "Authorization": f"user_token {USER_TOKEN}"
    }
    res = requests.get(f"{GLPI_URL}/initSession", headers=headers)
    if res.status_code == 200:
        return res.json().get("session_token")
    else:
        print("[!] Erro ao conectar no GLPI.")
        exit()
 
 
def sincronizar_usuarios(usuarios_ms, session_token):
    """Injeta os usuários no GLPI"""
    headers = {
        "App-Token": APP_TOKEN,
        "Session-Token": session_token,
        "Content-Type": "application/json"
    }
    criados = 0
    ignorados = 0
 
    print("[*] Iniciando sincronização no banco de dados...")
    for ms_user in usuarios_ms:
        email = ms_user.get('userPrincipalName')
        nome = ms_user.get('displayName')
        ativo = 1 if ms_user.get('accountEnabled') else 0
        telefone = ms_user.get('mobilePhone', '')
        # Ignora contas de sistema ou sem nome
        if not email or not nome:
            continue
 
        payload = {
            "input": {
                "name": email,               # Usamos o e-mail como login
                "realname": nome,
                "phone": telefone,
                "is_active": ativo,          # Desativa no GLPI se estiver bloqueado na Microsoft
                "emails": [{"email": email, "is_default": 1}]
            }
        }
 
        res = requests.post(f"{GLPI_URL}/User", headers=headers, json=payload)
        if res.status_code in [200, 201]:
            print(f"  [+] Criado: {nome} ({email})")
            criados += 1
        elif "already used" in res.text or res.status_code == 400:
            # Usuário já existe, não precisamos poluir a tela
            ignorados += 1
        else:
            print(f"  [!] Falha ao criar {nome}: {res.text}")
 
    print("-" * 30)
    print(f"[*] Sincronização concluída: {criados} novos, {ignorados} já existentes.")
    # Encerra a sessão
    requests.get(f"{GLPI_URL}/killSession", headers=headers)
 
 
if __name__ == "__main__":
    token_ms = obter_token_ms()
    usuarios_ms = obter_usuarios_m365(token_ms)
    sessao_glpi = obter_sessao_glpi()
    sincronizar_usuarios(usuarios_ms, sessao_glpi)

