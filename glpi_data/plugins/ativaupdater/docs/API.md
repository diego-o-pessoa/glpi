# API do Ativa Updater

A API distribui o instalador completo do Wallpaper Client e recebe o estado do serviço Windows. Todos os endpoints exigem:

```http
Authorization: Bearer <TOKEN>
```

O arquivo pronto para o serviço pode ser baixado em **Ativa Updater > Configurações**. Não registre o token em logs e não publique esse JSON.

## Versão publicada

```http
GET /plugins/ativaupdater/api/v1/latest
```

Exemplo de resposta:

```json
{
  "version": "1.5.0",
  "file_name": "Ativa-Unified-Agent-Setup-1.5.0.exe",
  "size": 32581742,
  "sha256": "8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92",
  "published_at": "2026-09-14T15:00:00Z",
  "download_url": "https://chamados.ativalocacao.com.br:8443/plugins/ativaupdater/api/v1/download/1.4.4",
  "check_interval_seconds": 3600,
  "allow_downgrade": false
}
```

Também existe `GET /releases/{version}` para uma versão específica; nela `allow_downgrade` é `true` apenas se a versão consultada for a ativa com rollback autorizado.

### Upgrade e downgrade

O serviço (1.3.0+) compara a versão instalada com `version`:

| Situação | Ação do serviço |
|---|---|
| instalada menor que a publicada | instala (upgrade) |
| instalada igual à publicada | nada (`current`) |
| instalada maior e `allow_downgrade` = `false` | nada; reporta `current` com "downgrade não autorizado" |
| instalada maior, `allow_downgrade` = `true` e publicada ≥ 1.6.0 | instala a versão publicada (rollback) |
| instalada maior e publicada < 1.6.0 | nada: pacotes anteriores não suportam rollback |

`allow_downgrade` só é `true` quando um administrador usa **Rollback** ou **Autorizar downgrade** no dashboard. Um novo upload sempre publica como somente atualização. A mesma regra está em `src/ReleasePolicy.php` (servidor) e em `decide_action()` (serviço). Mantenha as duas alinhadas. Serviços anteriores a 1.3.0 ignoram o campo e nunca fazem downgrade.

## Download autenticado

```http
GET /plugins/ativaupdater/api/v1/download/1.4.4
```

O cliente valida o tamanho, o SHA-256 do conteúdo e a assinatura `MZ` antes da execução. Redirecionamentos e mudanças de origem são recusados.

## Estado de uma máquina

```http
POST /plugins/ativaupdater/api/v1/status
Content-Type: application/json
```

```json
{
  "machine_guid": "00000000-0000-0000-0000-000000000000",
  "hostname": "TI-01-000013",
  "updater_version": "1.1.1",
  "installed_version": "1.4.5",
  "wallpaper_client_version": "1.5.0",
  "glpi_agent_version": "1.19",
  "available_version": "1.5.0",
  "status": "downloading",
  "message": "Baixando e validando o instalador."
}
```

Status aceitos: `checking`, `waiting_release`, `current`, `downloading`, `installing`, `retrying`, `install_failed`, `updated` e `error`.

- Durante um rollback, `downloading` e `installing` trazem mensagens iniciadas por "Rollback".
- `retrying`: uma tentativa falhou e outra está agendada (até 3 novas tentativas após a primeira falha).
- `install_failed`: a tentativa inicial e as 3 novas falharam; o serviço tenta de novo uma vez por dia.
- Na primeira consulta depois de uma instalação concluída, o serviço envia `updated`; nas seguintes, `current`.

Relatórios de falha incluem o campo opcional `install_log` (texto, até ~24 KB), com o log coletado da instalação. O servidor guarda até 65.000 caracteres e apaga o log quando o computador informa `current`, `updated` ou `waiting_release`. O corpo aceito em `/status` vai até 128 KiB.

## Códigos principais

- `401`: cabeçalho Bearer ausente ou malformado;
- `403`: token inválido;
- `404`: versão ou arquivo não encontrado;
- `422`: relatório do cliente inválido;
- `503`: API desabilitada.
