from flask import Flask, request, render_template
import requests
 
app = Flask(__name__)
 
# ================= CONFIGURAÇÕES DO GLPI =================
GLPI_URL = "http://10.117.41.6:8091/apirest.php"
 
# Você precisa gerar esses tokens no GLPI:
# 1. App-Token: Configurar > Geral > API
# 2. User-Token: Administração > Usuários > Seu_Usuario > Token de API (Gerar)
APP_TOKEN = "EaYmVTLPjMsbCt6JN3A18Tu3uB8315FLibAFnErD"
USER_TOKEN = "Zflqy5rID7gHiK1qsntmvTDS7DXEkaJMaScOPPe3"
# =========================================================
 
def get_session():
    """Autentica na API do GLPI e retorna o Session-Token"""
    headers = {
        "App-Token": APP_TOKEN,
        "Authorization": f"user_token {USER_TOKEN}"
    }
    resp = requests.get(f"{GLPI_URL}/initSession", headers=headers)
    if resp.status_code == 200:
        return resp.json().get("session_token")
    return None
 
@app.route('/registrar', methods=['GET', 'POST'])
def registrar():
    if request.method == 'GET':
        return render_template('registrar.html')
    if request.method == 'POST':
        dados = request.form
        nome = dados.get('nome')
        email = dados.get('email')
        senha = dados.get('senha')
        confirma_senha = dados.get('confirma_senha')
        telefone = dados.get('telefone')
        # Validação simples
        if senha != confirma_senha:
            return render_template('registrar.html', mensagem="As senhas não coincidem.", tipo_mensagem="error")
        session_token = get_session()
        if not session_token:
            return render_template('registrar.html', mensagem="Erro interno de comunicação com o GLPI.", tipo_mensagem="error")
        headers = {
            "App-Token": APP_TOKEN,
            "Session-Token": session_token,
            "Content-Type": "application/json"
        }
        # Payload para criar o usuário. 
        # O GLPI usa o 'name' como login. Colocamos o e-mail lá.
        payload = {
            "input": {
                "name": email, 
                "realname": nome,
                "password": senha,
                "phone": telefone,
                "emails": [{"email": email, "is_default": 1}]
            }
        }
        # Criação do usuário na API
        res = requests.post(f"{GLPI_URL}/User", headers=headers, json=payload)
        # Destrói a sessão para segurança
        requests.get(f"{GLPI_URL}/killSession", headers=headers)
        if res.status_code in [200, 201]:
            # Sucesso! Redireciona visualmente o usuário com uma mensagem.
            return render_template('registrar.html', mensagem="Conta criada com sucesso! Você já pode voltar e fazer login.", tipo_mensagem="success")
        else:
            # Erro comum: e-mail/usuário já existente
            return render_template('registrar.html', mensagem="Erro ao criar conta. Talvez este e-mail já esteja em uso.", tipo_mensagem="error")
 
if __name__ == '__main__':
    # Roda o servidor aberto para a rede na porta 5000
    app.run(host='0.0.0.0', port=5000, debug=True)

