# Instalador unificado Ativa GLPI Agent

Gera um unico `Ativa-GLPI-Agent-Setup-1.2.0.exe` para Windows x64 contendo o MSI
oficial do GLPI Agent e o Ativa Wallpaper Client.

O instalador configura o GLPI Agent como servico com:

- servidor `https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/`;
- todas as features do MSI (`ADDLOCAL=ALL`), incluindo Inventory e Deploy;
- primeira execucao imediata (`RUNNOW=1`);
- verificacao TLS habilitada;
- HTTP listener e excecao de firewall habilitados para wake-up;
- P2P de pacotes habilitado;
- inventario de softwares nos perfis de usuarios;
- tag `Ativa-Locacao`.

Depois instala e registra o cliente de wallpaper. Quando existe um usuario
interativo, tenta aplicar o wallpaper ainda durante o setup. Em instalacoes por
SYSTEM sem sessao interativa, o cliente aplica no proximo login.

## Gerar com dois cliques

Em um Windows x64 com acesso a internet:

1. Baixe um novo `bootstrap-config.json` em **Administracao > Ativa Wallpaper
   > Configuracoes**.
2. Coloque o JSON nesta pasta, sem alterar o nome.
3. Execute `Criar-Instalador-Para-Todos.cmd`.

O `.cmd` instala automaticamente Python 3.12 e Inno Setup 6 via winget quando
necessario. Em seguida baixa o Agent, valida o hash, compila tudo e abre o
resultado em `dist`.

O builder usa explicitamente a fonte comunitaria `winget` e nao consulta a
`msstore`. Se a instalacao automatica do Inno Setup ainda falhar, execute uma
vez e repita o `.cmd`:

```powershell
winget install --id JRSoftware.InnoSetup --exact --source winget
```

O fluxo equivalente pela linha de comando e:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
cd deployment\unified-installer
.\build-unified-installer.ps1 `
  -BootstrapConfig ".\bootstrap-config.json" `
  -AllComputers `
  -InstallBuildTools
```

O comando baixa o GLPI Agent 1.19 da release oficial, valida o SHA-256 fixado,
compila o cliente de wallpaper, instala o Inno Setup via winget quando preciso e
gera em `dist`:

```text
Ativa-GLPI-Agent-Setup-1.2.0.exe
Ativa-GLPI-Agent-Setup-1.2.0.manifest.json
```

Sem `-AllComputers`, o hostname piloto presente no JSON e preservado. Com essa
opcao, a restricao e removida para rollout; limite a distribuicao ao grupo
corporativo correto.

## Instalar

Interativo:

```powershell
.\Ativa-GLPI-Agent-Setup-1.2.0.exe
```

Silencioso:

```text
Ativa-GLPI-Agent-Setup-1.2.0.exe /VERYSILENT /SUPPRESSMSGBOXES /NORESTART
```

O MSI oficial permanece assinado e nao e alterado. O bootstrapper gerado nao e
assinado por padrao; para distribuicao ampla, assine-o com o certificado de code
signing da organizacao.

O instalador contem o segredo de registro do wallpaper. Depois do rollout,
rotacione o segredo no GLPI. Instaladores antigos deixam de registrar novos
clientes, mas os clientes ja registrados continuam funcionando.
