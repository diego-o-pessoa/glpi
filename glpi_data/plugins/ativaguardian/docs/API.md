# API do Ativa Guardian — v1

API autenticada por token para o serviço Windows (futuro) enviar heartbeats de
saúde. Esta primeira etapa entrega apenas o servidor; o serviço ainda não existe.

- **Base:** `https://<seu-glpi>/plugins/ativaguardian/api/v1`
- **Autenticação:** cabeçalho `Authorization: Bearer <token>` em todo endpoint,
  exceto `/health`. O token é gerado na instalação e obtido em
  **GLPI > Ativa Guardian > (engrenagem) Configurar > Baixar token**.
- **Formato:** JSON em UTF-8. Erros retornam `{ "error": { "code", "message" } }`.

A API é **somente de ingestão**: recebe status, não executa comandos. Não há
endpoint que rode script, reinicie serviço ou altere a máquina.

---

## GET /health

Verificação pública de disponibilidade. Não exige token.

```
200 OK
{ "status": "ok", "plugin_version": "1.0.0", "api_version": "1" }
```

## POST /heartbeat

Registra (ou atualiza) o estado de uma máquina. Chave de identidade: `machine_id`.

### Corpo

```json
{
  "machine_id": "ID-PERSISTENTE",
  "hostname": "PC-001",
  "guardian_version": "1.0.0",
  "antivirus": "Microsoft Defender",
  "components": {
    "wallpaper":  { "version": "1.4.1", "status": "healthy" },
    "updater":    { "version": "1.4.3", "status": "service_stopped" },
    "remote":     { "status": "healthy" },
    "glpi_agent": { "status": "healthy" }
  }
}
```

### Campos e validação

| Campo | Regra |
| ----- | ----- |
| `machine_id` | obrigatório, `[A-Za-z0-9._-]`, 1–128 |
| `hostname` | opcional, `[A-Za-z0-9._-]`, até 255 |
| `guardian_version` | opcional, `[A-Za-z0-9.+_-]`, até 64 |
| `antivirus` | opcional, texto, até 128 |
| `components` | objeto, no máximo 50 entradas |
| `components.<nome>` | nome em `[a-z0-9_]` (1–64) — aceita componentes futuros |
| `components.<nome>.status` | um dos status válidos (abaixo) |
| `components.<nome>.version` | opcional, `[A-Za-z0-9.+_-]`, até 64 |

O corpo é limitado a 64 KB. Qualquer campo fora do padrão retorna `422` e nada é gravado.

### Status de componente aceitos

`healthy`, `warning`, `error`, `service_stopped`, `process_stopped`,
`file_missing`, `version_outdated`, `unknown`.

> `offline` **não** é reportável por um componente: é derivado no servidor quando
> a máquina fica sem heartbeat além do limite configurado (padrão 2 h).

### Status geral da máquina

Calculado como o pior status entre os componentes:

- todos `healthy` → **healthy**
- algum `warning`/`version_outdated` → **warning**
- algum `service_stopped`/`process_stopped`/`file_missing`/`error` → **error**
- sem heartbeat recente → **offline**
- só `unknown`/sem componentes → **unknown**

### Respostas

```
202 Accepted   { "ok": true, "machine_id": "ID-PERSISTENTE" }
400            JSON inválido
401/403        token ausente / inválido
413            corpo acima de 64 KB
422            algum campo inválido (INVALID_PAYLOAD, INVALID_COMPONENT, ...)
503            API desabilitada nas configurações
```

---

## Teste manual rápido (curl)

```bash
TOKEN="cole-o-token-aqui"
BASE="https://<seu-glpi>/plugins/ativaguardian/api/v1"

curl -s "$BASE/health"

curl -s -X POST "$BASE/heartbeat" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "machine_id": "TESTE-0001",
    "hostname": "PC-TESTE-01",
    "guardian_version": "1.0.0",
    "antivirus": "Microsoft Defender",
    "components": {
      "wallpaper":  { "version": "1.4.1", "status": "healthy" },
      "updater":    { "version": "1.4.3", "status": "service_stopped" },
      "remote":     { "status": "healthy" },
      "glpi_agent": { "status": "healthy" }
    }
  }'
```

Depois abra **GLPI > Ativa Guardian**: a máquina `PC-TESTE-01` aparece com o
Updater marcado como *Serviço parado*. Há também um script pronto em
`tests/heartbeat_test.php` (`php heartbeat_test.php`).
