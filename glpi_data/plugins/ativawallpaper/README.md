# Ativa Wallpaper 1.1.0

Plugin para GLPI 11 que publica wallpapers corporativos, registra clientes
Windows e acompanha aplicacao, pendencia, erro e indisponibilidade. Nenhum
arquivo do core do GLPI ou do GLPI Agent e modificado.

## Requisitos e ambiente-alvo

- GLPI 11.0.8 detectado nos arquivos desta instalacao;
- PHP 8.2 com `fileinfo`, `gd` e `openssl`;
- banco do GLPI com InnoDB e `utf8mb4` (MySQL 8 no ambiente-alvo);
- GLPI Inventory 1.6.10 detectado e GLPI Agent 1.19 informado para o bootstrap;
- cliente: Windows 10/11 x64 (Windows Server bloqueado por padrao);
- endpoint Inventory:
  `https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/`;
- API do wallpaper:
  `https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1`.

TLS e verificacao do certificado sao obrigatorios. URLs por IP sao rejeitadas.

## Instalacao do plugin

1. Mantenha este diretorio como `plugins/ativawallpaper` no volume persistente
   do GLPI.
2. Confirme que o usuario do servidor web pode criar
   `GLPI_PLUGIN_DOC_DIR/ativawallpaper`.
3. No GLPI, abra **Administracao > Plugins**, instale e ative **Ativa Wallpaper**.
4. Abra **Administracao > Ativa Wallpaper > Configuracoes**.
5. Verifique GLPI Inventory, endpoint e `taskscheduler`.
6. Gere o bootstrap JSON e publique o wallpaper inicial.

A instalacao cria apenas quatro tabelas com prefixo
`glpi_plugin_ativawallpaper_`, configuracoes no contexto
`plugin:ativawallpaper`, direitos de perfil e uma acao automatica de
reconciliacao. O core nao e alterado.

## Permissoes

Na ficha de cada perfil existe a aba **Ativa Wallpaper** com direitos separados:

- Visualizar;
- Publicar/rollback;
- Configurar;
- Administrar clientes (forcar reaplicacao e revogar).

As verificacoes ocorrem novamente no backend e nao dependem apenas da
visibilidade dos botoes. O perfil que instala o plugin recebe os direitos; os
demais perfis iniciam sem acesso.

## Uso diario

Em **Administracao > Ativa Wallpaper**, clique **Alterar wallpaper**, selecione
JPG/JPEG/PNG, confira a pre-visualizacao, escolha Fill/Fit/Stretch/Center/Tile ou
Span e publique. O servidor valida extensao, MIME real, decodificacao, dimensoes
e limite (20 MB por padrao), calcula SHA-256, cria thumbnail e guarda o original
fora do document root.

Cada publicacao recebe uma versao `AAAAMMDD-NNN`. Versoes antigas nao sao
apagadas. Em **Historico**, **Tornar esta versao atual** faz rollback e gera uma
nova revisao detectavel, inclusive quando algum cliente ja tinha aquela versao.

Com um wallpaper atual, **Aplicar em todos** solicita a reaplicacao para todos
os clientes registrados em um unico clique. Se a distribuicao estiver inativa,
o botao passa a se chamar **Ativar e aplicar em todos** e faz as duas operacoes.
A execucao ocorre na proxima consulta de cada cliente; computadores offline
aplicam quando voltarem a se comunicar. No intervalo padrao, uma maquina online
pode levar ate aproximadamente 70 segundos (60 segundos mais o jitter de ate
10 segundos).

Desativar a distribuicao faz a API retornar `enabled: false`; nao apaga o
wallpaper atual. O cliente remove somente bloqueios que ele proprio criou.

## Status

- **Atualizado**: contato recente, ultimo status `success` e versao atual;
- **Pendente**: contato recente, mas versao/status ainda nao correspondem;
- **Erro**: contato recente com ultimo status de erro;
- **Offline**: ultimo contato excedeu o limite (1 hora por padrao).

O limite offline deve permanecer maior que duas vezes o polling. Listagens tem
pesquisa, filtros, ordenacao e paginacao de 25 itens.

## API v1

Todos os endpoints respondem JSON, exceto o download binario:

- `GET /health` — publico, sem dados sensiveis;
- `POST /register` — usa o segredo temporario de bootstrap e devolve um token
  individual uma unica vez;
- `GET /config` — bearer token, ETag e `304 Not Modified`;
- `GET /wallpaper/{id-ou-versao}/download` — bearer token, SHA como ETag;
- `POST /status` e `POST /error` — bearer token e status sanitizado.

Exemplo de registro:

```json
{
  "hostname": "DESKTOP-R1C8ICN",
  "machine_guid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
  "client_version": "1.0.1",
  "registration_secret": "valor-do-bootstrap"
}
```

Chamadas seguintes usam `Authorization: Bearer <client-token>`. Somente o
SHA-256 do token e armazenado no banco. Tokens podem ser revogados no dashboard.
A API limita registros por IP e requisicoes autenticadas por cliente, limita o
corpo JSON a 64 KiB, nao retorna stack trace e nao registra headers/body.

## Modelo de seguranca do bootstrap

O segredo de registro e compartilhado por um pacote de bootstrap; portanto,
quem extrair esse pacote pode registrar um cliente arbitrario enquanto o segredo
continuar valido. As mitigacoes sao: pacote protegido no Inventory, alvo piloto,
rate limiting, rotacao imediata apos o rollout, token individual e revogacao.
Rotacionar o segredo nao invalida clientes existentes. O cliente registra como
SYSTEM durante `--install`, descarta o segredo e persiste somente seu token.

O EXE e `client.json` ficam em area com ACL explicita para
SYSTEM/Administradores (controle total) e Usuarios (leitura/execucao). Somente as pastas
`data`, `state` e `logs` recebem permissao de modificacao para Usuarios, pois a
aplicacao ocorre sem elevacao. Isso evita tornar o executavel de startup
gravavel por usuarios comuns.

## Cliente Windows

O fonte esta em `client/wallpaper_client.py` e usa apenas a biblioteca padrao em
runtime. Compile em Windows com:

```powershell
cd client
.\build-client.ps1
```

O instalador aceita:

```text
AtivaWallpaperClient.exe --install --bootstrap-config "bootstrap-config.json"
AtivaWallpaperClient.exe --uninstall
AtivaWallpaperClient.exe --uninstall --remove-wallpaper
AtivaWallpaperClient.exe --once --debug
AtivaWallpaperClient.exe --version
```

Sem argumentos, roda continuamente no usuario logado. Um mutex impede duas
instancias para o mesmo usuario. HKLM Run inicia o mesmo EXE em cada login; o
Deploy SYSTEM apenas instala e registra, sem tentar alterar HKCU.

O cliente usa `ssl.create_default_context()`, recusa `verify_tls=false`, IPs,
hostnames diferentes de `chamados.ativalocacao.com.br` e redirect para outra
origem. Polling padrao e 60 s com jitter de ate 10 s;
falhas usam backoff 60/120/300/900 s. O download vai para arquivo temporario,
valida tamanho e SHA-256, e so entao troca o cache. Se a aplicacao falhar, o
arquivo anterior e restaurado e reaplicado.

Os valores Windows usados sao:

| Modo | WallpaperStyle | TileWallpaper |
|---|---:|---:|
| Center | 0 | 0 |
| Tile | 0 | 1 |
| Stretch | 2 | 0 |
| Fit | 6 | 0 |
| Fill | 10 | 0 |
| Span | 22 | 0 |

Depois de atualizar HKCU, o cliente chama `SystemParametersInfoW` com
`SPI_SETDESKWALLPAPER`, `SPIF_UPDATEINIFILE` e `SPIF_SENDCHANGE`; nao exige
reinicio. O bloqueio manual e opt-in. Valores de Policy preexistentes nunca sao
sobrescritos, e somente valores que o cliente marcou como proprios sao removidos.

## GLPI Inventory e piloto

Siga [deployment/README-GLPI-INVENTORY.md](deployment/README-GLPI-INVENTORY.md).
A primeira tarefa deve atingir exclusivamente
`DESKTOP-R1C8ICN-2026-08-17-14-54-59`. O JSON gerado tambem traz essa protecao.
Somente depois do fluxo completo ser confirmado o alvo deve mudar para o grupo
dinamico Windows sem Server.

A versao Inventory 1.6.10 inspecionada nao fornece uma API publica estavel para
orquestrar grupo+pacote+tarefa+auditoria. O plugin deliberadamente nao insere em
tabelas internas do Inventory; o Setup Wizard orienta e diagnostica.

## Instalador unico para novos computadores

Para instalar o GLPI Agent e o cliente de wallpaper com um unico executavel,
use [deployment/unified-installer/README.md](deployment/unified-installer/README.md).
O build automatizado baixa e valida o MSI oficial do Agent, habilita todas as
features (inclusive Deploy), compila o cliente e gera
`Ativa-GLPI-Agent-Setup.exe`.

## Logs e troubleshooting

Logs do cliente ficam em
`C:\ProgramData\AtivaLocacao\Wallpaper\logs\client-<usuario>.log`, rotacionados
em 5 x 5 MB. `--once --debug` tambem escreve no console. Procure codigos como
`HASH_MISMATCH`, `HTTP_401`, `SERVER_UNAVAILABLE`, `ACL_FAILED` e
`WALLPAPER_APPLY_FAILED`.

`Deploy task not supported by server0`: o Agent esta usando o endpoint generico.
Use `https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/`.

`Can't decode JSON content` com `<!DOCTYPE html>`: uma pagina HTML foi recebida
no lugar da API, geralmente pela URL errada. Nao use `10.117.41.6` e nao use
`no-ssl-check`/`verify=False`.

`HTTP_401`: token revogado ou bootstrap invalido. Revogue o registro antigo se
necessario, gere novo JSON e execute novamente o Deploy piloto.

## Testes

No servidor:

```bash
python3 -m unittest discover -s client/tests -v
php -d zend.assertions=1 -d assert.exception=1 tests/php/run.php
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

Os testes cobrem versionamento, token/hash, config/ETag, upload disfarçado,
imagem valida, parsing JSON, limites, comparacao de revisao, download/hash,
rollback do arquivo local e retry de status apos 304. O teste de Windows real
(HKCU, SPI, HKLM Run e EXE PyInstaller) deve ser executado no piloto antes da
expansao.

## Atualizacao, rollback e desinstalacao

- Wallpaper: use **Historico > Tornar esta versao atual**.
- Cliente: use novo pacote Inventory apenas quando o EXE mudar; wallpapers nao
  exigem novo Deploy.
- Plugin: desative, substitua os arquivos e execute a atualizacao pela tela de
  plugins. `schema_version` prepara migracoes futuras.
- Cliente: `--uninstall` sinaliza processos, remove HKLM Run, token/config e
  politicas proprias; preserva o wallpaper por padrao. Use
  `--remove-wallpaper` para apagar explicitamente o cache.
- Plugin: **Preservar tabelas e historico ao desinstalar** vem habilitado. Para
  purga intencional, desmarque, salve e so entao desinstale. Arquivos de imagem
  privados nao sao apagados silenciosamente.
