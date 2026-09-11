# Documentação da API - Ativa Updater

A API permite consultar e baixar a versão mais recente ou versões específicas do instalador unificado.

## Autenticação

Todos os endpoints exigem um cabeçalho HTTP de autorização:

```http
Authorization: Bearer <SEU_TOKEN>
```

O token pode ser gerado/visualizado na página de Configurações do plugin no GLPI.

---

## Obter versão atual

Retorna os detalhes da versão marcada atualmente como ativa.

**Request:**

```http
GET /plugins/ativaupdater/api/v1/latest
```

**Response (200 OK):**

```json
{
  "version": "1.4.4",
  "file_name": "Ativa-Wallpaper-Client-Setup-1.4.4.exe",
  "size": 32581742,
  "sha256": "8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92",
  "published_at": "2026-09-11T15:00:00Z",
  "download_url": "https://servidor/plugins/ativaupdater/api/v1/download/1.4.4"
}
```

---

## Obter versão específica

Retorna os detalhes de uma versão específica pelo seu número de versão.

**Request:**

```http
GET /plugins/ativaupdater/api/v1/releases/{version}
```

**Response (200 OK):**
*(Mesmo formato do endpoint latest)*

---

## Download

Faz o streaming do arquivo de instalação. 

**Request:**

```http
GET /plugins/ativaupdater/api/v1/download/{version}
```

**Response:**
Retorna o conteúdo binário (`application/octet-stream`) do executável associado àquela versão.

---

## Erros Comuns

- `401 Unauthorized`: Cabeçalho `Authorization` não enviado ou malformado.
- `403 Forbidden`: Token inválido ou API desabilitada.
- `404 Not Found`: Nenhuma versão publicada encontrada ou arquivo não encontrado.
