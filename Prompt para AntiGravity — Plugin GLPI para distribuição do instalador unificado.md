Quero criar um NOVO plugin para o GLPI responsável exclusivamente por armazenar e distribuir o nosso instalador unificado do agente para máquinas Windows.

O repositório atual está em:

`C:\Github\glpi`

Antes de alterar qualquer coisa, faça:

`git status`

`git diff`

Analise a arquitetura dos plugins personalizados já existentes no projeto e siga o mesmo padrão utilizado pelo GLPI atual para:

- estrutura do plugin;
- banco de dados;
- controllers;
- templates;
- permissões;
- rotas;
- API;
- armazenamento privado;
- segurança.

Não altere o plugin `ativawallpaper`.

Este novo plugin deve ser independente.

---

# OBJETIVO

Quero um plugin do GLPI onde um administrador consiga fazer upload do nosso instalador unificado.

Exemplo:

`Ativa-Wallpaper-Client-Setup-1.4.3.exe`

ou versões futuras:

`Ativa-Wallpaper-Client-Setup-1.4.4.exe`

`Ativa-Wallpaper-Client-Setup-1.5.0.exe`

Depois do upload, o plugin deverá disponibilizar através de uma API:

- versão mais recente disponível;
- nome do arquivo;
- tamanho;
- SHA-256;
- data da publicação;
- endpoint de download.

Uma aplicação ou serviço Windows utilizará essa API futuramente para verificar a versão e baixar o instalador.

Neste momento NÃO quero que você crie o serviço Windows.

Quero apenas o plugin GLPI e a API necessária para que um serviço Windows possa consumir posteriormente.

---

# NOME DO PLUGIN

Pode utilizar:

`Ativa Updater`

Identificador:

`ativaupdater`

O plugin deve aparecer no menu do GLPI como:

`Ativa Updater`

---

# DASHBOARD

Crie uma página administrativa simples e profissional seguindo o layout do GLPI.

Ela deve mostrar a versão atualmente publicada.

Exemplo:

`Versão atual: 1.4.3`

Também mostrar:

- nome do instalador;
- tamanho;
- SHA-256;
- data da publicação;
- usuário que publicou;
- status da release.

Exemplo visual:

Versão atual:

`1.4.3`

Arquivo:

`Ativa-Wallpaper-Client-Setup-1.4.3.exe`

Tamanho:

`32 MB`

SHA-256:

`...`

Publicado em:

`11/09/2026 15:00`

---

# PUBLICAR NOVA VERSÃO

No dashboard deve existir uma área:

`Publicar nova versão`

Campos:

### Versão

Exemplo:

`1.4.4`

### Instalador

Aceitar inicialmente apenas:

`.exe`

### Botão

`Enviar e publicar`

---

# PROCESSO DO UPLOAD

Quando o administrador enviar o instalador:

1. validar se realmente houve upload;
2. validar extensão;
3. validar MIME quando possível;
4. validar tamanho;
5. armazenar com nome seguro;
6. impedir path traversal;
7. calcular SHA-256;
8. calcular tamanho do arquivo;
9. registrar versão;
10. registrar data;
11. registrar usuário responsável;
12. somente marcar a versão como disponível depois que o upload terminar corretamente.

Se qualquer etapa falhar, a release não deve ser publicada.

---

# ARMAZENAMENTO

Não quero o executável simplesmente salvo em uma pasta pública acessível diretamente pela internet.

Exemplo que NÃO quero:

`https://servidor/uploads/Ativa-Wallpaper-Client-Setup-1.4.4.exe`

Prefiro:

armazenamento privado

↓

endpoint PHP/API autenticado

↓

API verifica autorização

↓

faz streaming do arquivo.

Utilize os mecanismos adequados do GLPI para armazenamento privado.

Não carregue arquivos grandes inteiros na memória PHP.

Faça streaming.

O instalador pode possuir dezenas ou centenas de MB.

---

# BANCO DE DADOS

Crie estrutura própria para releases.

Algo conceitualmente semelhante a:

`glpi_plugin_ativaupdater_releases`

Campos possíveis:

- id;
- version;
- original_filename;
- stored_filename;
- file_path;
- file_size;
- sha256;
- created_at;
- created_by;
- active.

Adapte os nomes e tipos de acordo com os padrões corretos do GLPI.

---

# HISTÓRICO DE VERSÕES

Quero que o plugin possa manter histórico.

Exemplo:

| Versão | Arquivo | SHA-256 | Data | Status |
|---|---|---|---|---|
| 1.4.4 | Setup-1.4.4.exe | ... | 11/09 | Atual |
| 1.4.3 | Setup-1.4.3.exe | ... | 10/09 | Anterior |
| 1.4.2 | Setup-1.4.2.exe | ... | 08/09 | Anterior |

Somente uma versão deve ser considerada a versão atual/ativa por padrão.

Não sobrescreva silenciosamente releases anteriores.

---

# VERSIONAMENTO

Compare versões semanticamente.

Exemplos:

`1.4.3 < 1.4.4`

`1.4.9 < 1.5.0`

`1.9.9 < 2.0.0`

Não faça comparação simples de strings.

Valide o formato informado pelo administrador.

---

# API

Quero uma API própria do plugin para futuramente ser consumida pelo serviço Windows.

Crie API versionada.

Exemplo conceitual:

`/plugins/ativaupdater/api/v1/latest`

ou utilize o padrão correto de rotas da versão atual do GLPI.

---

# ENDPOINT — VERSÃO MAIS RECENTE

Quero um endpoint que retorne os dados da release atual.

Exemplo:

`GET /api/v1/latest`

Resposta conceitual:

```json
{
  "version": "1.4.4",
  "file_name": "Ativa-Wallpaper-Client-Setup-1.4.4.exe",
  "size": 32581742,
  "sha256": "HASH_SHA256",
  "published_at": "2026-09-11T15:00:00-03:00",
  "download_url": "https://servidor/.../api/v1/download/..."
}
```

Se nenhuma versão estiver publicada:

retorne resposta apropriada, por exemplo HTTP 404 ou estrutura padronizada da API.

---

# ENDPOINT — DOWNLOAD

Crie um endpoint específico para o download.

Exemplo:

`GET /api/v1/download/{release}`

O endpoint deverá:

1. validar release;
2. validar autenticação, se configurada;
3. localizar arquivo;
4. verificar que o caminho pertence ao diretório permitido;
5. retornar `Content-Type` apropriado;
6. retornar `Content-Length`;
7. retornar `Content-Disposition`;
8. fazer streaming do arquivo;
9. nunca permitir que o cliente informe um caminho arbitrário do servidor.

Não aceite algo como:

`?file=../../etc/passwd`

O ID/version da release deve ser convertido internamente para o arquivo permitido.

---

# DOWNLOAD POR VERSÃO

Também quero que seja possível obter uma versão específica.

Exemplo conceitual:

`GET /api/v1/releases/1.4.3`

Resposta:

```json
{
  "version": "1.4.3",
  "file_name": "Ativa-Wallpaper-Client-Setup-1.4.3.exe",
  "size": 31733870,
  "sha256": "...",
  "download_url": "..."
}
```

Isso será útil caso futuramente precisemos reinstalar ou fazer rollback.

---

# AUTENTICAÇÃO DA API

Não quero que qualquer pessoa consiga descobrir e baixar nossos instaladores apenas conhecendo a URL.

Implemente uma estrutura preparada para autenticação.

Pode utilizar inicialmente:

`Authorization: Bearer TOKEN`

Crie uma área de configuração no plugin para gerar ou definir o token utilizado por serviços Windows.

Não colocar token hardcoded no código.

Não retornar token em respostas.

Não escrever token em logs.

Não utilizar segredo em query string.

---

# CONFIGURAÇÕES DO PLUGIN

Crie uma área de configurações contendo pelo menos:

### API habilitada

`Sim / Não`

### Token da API

Permitir:

- gerar;
- substituir;
- rotacionar.

Nunca mostrar o token completo depois de salvo, se não for necessário.

### Tamanho máximo permitido para upload

Configurável ou documentado.

---

# SEGURANÇA

Implementar corretamente:

- validação de permissões GLPI;
- CSRF nas ações administrativas;
- prepared statements;
- escaping de saída;
- validação de upload;
- armazenamento privado;
- proteção contra path traversal;
- proteção contra upload arbitrário;
- validação de extensão;
- limite de tamanho;
- autenticação na API;
- nenhuma credencial nos logs.

Somente usuários administrativos autorizados poderão:

- publicar release;
- alterar versão ativa;
- excluir release;
- gerar token;
- alterar configurações.

---

# SHA-256

Sempre calcular SHA-256 no servidor depois do upload.

Não aceitar hash enviado pelo navegador como fonte de verdade.

A API deve retornar esse hash.

Futuramente o serviço Windows fará:

download

↓

calcula SHA-256 localmente

↓

compara com o valor retornado pela API.

---

# EXCLUSÃO DE RELEASE

Permitir excluir releases antigas.

Porém:

- exigir confirmação;
- não permitir apagar acidentalmente a release atual sem confirmação adequada;
- apagar registro e arquivo;
- impedir que o caminho salvo permita exclusão fora do diretório do plugin.

---

# DEFINIR RELEASE ATUAL

Permitir marcar uma release existente como:

`Versão atual`

Exemplo:

Temos:

`1.4.2`

`1.4.3`

`1.4.4`

Por padrão:

`1.4.4 = atual`

Mas o administrador poderá alterar manualmente se necessário.

Isso será útil em casos de rollback.

---

# DOCUMENTAÇÃO DA API

Crie documentação simples dentro do projeto.

Por exemplo:

`docs/API.md`

Documentar:

### Obter versão atual

Request:

`GET /.../latest`

Header:

`Authorization: Bearer TOKEN`

Response:

```json
{
  "version": "1.4.4",
  "file_name": "...",
  "size": 123,
  "sha256": "...",
  "download_url": "..."
}
```

### Download

Request:

`GET /.../download/...`

Header:

`Authorization: Bearer TOKEN`

Documentar também erros:

- 400;
- 401;
- 403;
- 404;
- 500.

---

# TESTES

Crie testes para:

### Upload

- EXE válido;
- extensão inválida;
- arquivo vazio;
- upload interrompido;
- versão inválida;
- versão duplicada;
- tamanho acima do permitido.

### API

- token válido;
- token inválido;
- sem token;
- latest release;
- release inexistente;
- download válido;
- download não autorizado.

### Segurança

- path traversal;
- nome de arquivo malicioso;
- versão maliciosa;
- conteúdo inválido;
- usuário sem permissão administrativa.

### Hash

- SHA-256 gerado corretamente;
- arquivo armazenado corresponde ao hash.

---

# IMPORTANTE

Neste momento NÃO implemente:

- serviço Windows;
- verificação automática de uma em uma hora;
- instalação silenciosa;
- auto-update nas máquinas;
- Windows Service;
- BITS;
- tarefas agendadas.

Essas funcionalidades serão implementadas posteriormente.

O objetivo desta etapa é SOMENTE criar a infraestrutura do servidor:

`GLPI`

↓

`Ativa Updater`

↓

upload do instalador unificado

↓

release publicada

↓

API retorna versão + hash + link

↓

API permite download autenticado.

---

# RESULTADO FINAL ESPERADO

Quero conseguir entrar no GLPI:

`Ativa Updater`

↓

`Publicar nova versão`

↓

Versão:

`1.4.4`

↓

Arquivo:

`Ativa-Wallpaper-Client-Setup-1.4.4.exe`

↓

`Publicar`

Depois disso quero conseguir consultar pela API:

`GET latest`

e receber:

```json
{
  "version": "1.4.4",
  "file_name": "Ativa-Wallpaper-Client-Setup-1.4.4.exe",
  "size": 32581742,
  "sha256": "...",
  "download_url": "..."
}
```

E posteriormente:

`GET download_url`

↓

download do instalador.

Faça a implementação completa dessa etapa.

Antes de começar:

1. rode `git status`;
2. rode `git diff`;
3. analise a arquitetura atual do GLPI;
4. analise os plugins personalizados existentes;
5. defina a estrutura adequada do novo plugin;
6. implemente;
7. crie os testes;
8. valide o fluxo completo de upload → API → download.

Não altere funcionalidades existentes desnecessariamente.