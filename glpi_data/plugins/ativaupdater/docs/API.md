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
  "check_interval_seconds": 3600
}
```

Também existe `GET /releases/{version}` para uma versão específica.

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

Status aceitos: `checking`, `current`, `downloading`, `installing`, `updated` e `error`.

## Códigos principais

- `401`: cabeçalho Bearer ausente ou malformado;
- `403`: token inválido;
- `404`: versão ou arquivo não encontrado;
- `422`: relatório do cliente inválido;
- `503`: API desabilitada.
