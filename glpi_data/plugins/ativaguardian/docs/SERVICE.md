# Ativa Guardian — serviço Windows

Serviço de **monitoramento**. Ele observa os componentes Ativa e envia um
heartbeat para a API do plugin. Nesta etapa ele **não** repara, não reinstala,
não reinicia componentes, não baixa nada e não altera antivírus.

| | |
|---|---|
| Serviço | `AtivaGuardian` |
| Display name | `Ativa Guardian` |
| Startup | `Automatic` (com auto-restart em caso de falha) |
| Executável | `C:\Program Files\Ativa Locacao\Guardian\AtivaGuardian.exe` |
| Dados | `C:\ProgramData\AtivaLocacao\Guardian\` |
| Log | `...\Guardian\logs\guardian.log` (rotativo, 2 MB × 5) |
| Heartbeat | ao iniciar e a cada 5 min |

---

## O que é verificado

Os caminhos e nomes de serviço **não foram assumidos**: vieram do código que já
instala e gerencia cada componente (`unified_updater_service.py`, instalador
unificado e plugin `ativaremote`).

| Componente | Como é verificado | Versão |
|---|---|---|
| **GLPI Agent** | serviço (sonda `glpi-agent`, `GLPIAgent`, `GLPI-Agent`) | registro de desinstalação (`DisplayName` = "GLPI Agent X.Y") |
| **Ativa Wallpaper** | executável em `ProgramData\AtivaLocacao\Wallpaper` + **processo** (roda por usuário, não é serviço) | `version.json` → `client_version` |
| **Ativa Updater** | executável + serviço `AtivaUnifiedUpdater` | `heartbeat.json` → `version` (sem disparar outro processo) |
| **Ativa Remote** | é o **RustDesk**: `%ProgramFiles%\RustDesk\rustdesk.exe` + serviço `RustDesk` | registro `Uninstall\RustDesk` |

Mapeamento de status: executável ausente → `file_missing`; serviço existe mas
parado → `service_stopped`; processo esperado ausente → `process_stopped`;
serviço sumiu com o executável presente → `error`; tudo certo → `healthy`;
falha ao verificar → `unknown`.

> `offline` nunca é enviado por um componente — é derivado no servidor quando o
> heartbeat para de chegar. A API rejeita esse valor.

---

## Instalar (máquina de teste)

Compile e rode **como administrador**:

```powershell
# 1. Compilar (roda os testes antes de empacotar)
.\build-guardian.ps1

# 2. Copiar para o diretório de instalação
New-Item -ItemType Directory -Force "C:\Program Files\Ativa Locacao\Guardian"
Copy-Item .\dist\AtivaGuardian.exe "C:\Program Files\Ativa Locacao\Guardian\"

# 3. Configurar (ver abaixo) e registrar o serviço
cd "C:\Program Files\Ativa Locacao\Guardian"
.\AtivaGuardian.exe --configure C:\caminho\config.json
.\AtivaGuardian.exe --install-service
```

`--install-service` cria o serviço em `Automatic`, define recuperação
(reinício automático em 60 s, três vezes) e já inicia.

## Desinstalar

```powershell
.\AtivaGuardian.exe --uninstall-service
```

Remove o serviço. **Preserva** `C:\ProgramData\AtivaLocacao\Guardian\`, para o
`machine_id` sobreviver a uma reinstalação. Para limpar de vez, apague a pasta.

---

## Configurar API/token

1. No GLPI: **Ativa Guardian → Configurar → Baixar configuração do serviço**.
   O arquivo já vem no formato que o serviço espera, sem edição manual:

```json
{
  "api_url": "https://seu-glpi/plugins/ativaguardian/api/v1",
  "api_token": "<64 caracteres hex>",
  "verify_tls": true,
  "heartbeat_interval_seconds": 300
}
```

2. Aplique: `AtivaGuardian.exe --configure ativaguardian-config.json`

Há um modelo em `client/config.example.json` para montar à mão, se preferir.

O comando **valida antes de gravar** e recusa: URL que não seja HTTPS, endpoint
que não seja o do plugin, token fora do formato, `verify_tls` desabilitado e
intervalo fora de 60–86400 s. Em seguida grava em
`ProgramData\AtivaLocacao\Guardian\config.json` e restringe a ACL da pasta a
SYSTEM + Administradores.

**Sobre o token:** só existe em `config.json`; nunca é compilado no executável,
nunca aparece na URL (vai apenas no cabeçalho `Authorization`) e nunca é escrito
no log. Redirecionamentos HTTP são recusados justamente para o cabeçalho não
vazar para outro host.

---

## Validar que o heartbeat chegou

**Na máquina**, sem enviar nada:

```powershell
.\AtivaGuardian.exe --check      # imprime o diagnóstico em JSON
```

Enviando de fato, uma vez:

```powershell
.\AtivaGuardian.exe --run-once --debug
```

No log (`logs\guardian.log`) espere ver:

```
Guardian started (versao 1.0.0)
Checking components
glpi_agent: healthy (1.19)
wallpaper: healthy (1.6.2)
updater: service_stopped (1.7.3)
remote: healthy (1.3.1)
Heartbeat sent
```

**No GLPI**: abra **Ativa Guardian**. A máquina aparece na lista com o status
real de cada componente e "Último contato" recente. A dashboard atualiza sozinha
a cada 5 s.

Se falhar, o log diz o motivo sem vazar segredo — por exemplo
`Heartbeat failed: HTTP 403: {"error":{"code":"FORBIDDEN"...}}` (token errado) ou
`Heartbeat failed: Falha de rede: ...` (servidor inacessível).

---

## Resiliência

Nenhuma dessas situações derruba o serviço:

- componente inexistente → `file_missing`, os outros seguem normais;
- erro ao descobrir versão → versão vazia, status preservado;
- antivírus desconhecido → `unknown`;
- API offline / timeout → 3 tentativas com backoff (5 s, 15 s, 45 s), registra
  `Heartbeat failed` e tenta de novo no próximo ciclo;
- configuração ausente ou inválida → registra e segue verificando;
- exceção inesperada em qualquer verificação → vira `unknown` e é logada.

Cada verificação é isolada: uma falhar não afeta as demais.

---

## Testes

Automatizados (39 casos):

```powershell
python -m unittest discover -s tests -p "test_guardian.py"
```

Cobrem: mapeamento de status dos 4 componentes (inclusive ausentes), isolamento
de falhas, detecção de Defender/Bitdefender/outro/desconhecido, persistência do
`machine_id`, validação de configuração, retry com backoff, API offline sem
derrubar o ciclo e aderência do payload ao contrato do plugin.

Manuais (exigem uma máquina real):

| Cenário | Como verificar |
|---|---|
| Serviço inicia | `sc query AtivaGuardian` → `RUNNING`; log com `Guardian started` |
| Reboot | reiniciar; serviço sobe sozinho (Automatic) e o `machine_id` continua o mesmo |
| API online | máquina aparece no GLPI; log `Heartbeat sent` |
| API offline | parar o GLPI; log `Heartbeat failed`, serviço continua `RUNNING` |
| Updater parado | `sc stop AtivaUnifiedUpdater` → coluna Updater vira *Serviço parado* |
| Wallpaper parado | encerrar `AtivaWallpaperClient.exe` → *Processo parado* |
| Componente inexistente | renomear a pasta do componente → *Arquivo ausente* |
| Defender detectado | máquina só com Defender → antivírus "Microsoft Defender" |
| Bitdefender detectado | máquina com Bitdefender → "Bitdefender" |
