# Instaladores Ativa para Windows

O gerador cria dois instaladores para Windows x64:

- `Ativa-Wallpaper-Client-Setup-X.Y.Z.exe`: instala o cliente de wallpaper e o serviço **Ativa Unified Updater**;
- `Ativa-GLPI-Agent-Setup-Only-X.Y.exe`: instala o GLPI Agent oficial apontando para o inventário da Ativa.

O serviço de atualização roda como `SYSTEM`, consulta o plugin **Ativa Updater** imediatamente ao iniciar e depois a cada hora. Quando encontra uma versão maior, baixa o instalador do Wallpaper Client, confere tamanho e SHA-256 e executa a atualização silenciosamente. O serviço informa ao dashboard se está consultando, baixando, instalando, atualizado ou com erro.

## Preparar as configurações

Baixe os dois arquivos no GLPI e coloque-os nesta pasta:

1. `bootstrap-config.json`, em **Ativa Wallpaper > Configurações**;
2. `ativaupdater-service-config.json`, em **Ativa Updater > Configurações > Baixar configuração do serviço**.

Os dois arquivos contêm segredos. Não faça commit deles nem os envie por canal público.

## Gerar com dois cliques

Execute `Criar-Instalador-Para-Todos.cmd`. O script localiza o Python e o Inno Setup já instalados. Com `-InstallBuildTools`, tenta instalar dependências ausentes pela fonte comunitária do winget.

O fluxo equivalente no PowerShell é:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
cd C:\Github\glpi\glpi_data\plugins\ativawallpaper\deployment\unified-installer
.\build-unified-installer.ps1 `
  -BootstrapConfig ".\bootstrap-config.json" `
  -UpdaterConfig ".\ativaupdater-service-config.json" `
  -AllComputers `
  -InstallBuildTools
```

Os arquivos são gerados em `dist`. A versão do instalador de wallpaper é obtida diretamente do `CLIENT_VERSION` existente em `wallpaper_client.py`.

## Primeira instalação e atualizações seguintes

Instale uma vez os dois arquivos nos computadores. Para instalação silenciosa:

```text
Ativa-GLPI-Agent-Setup-Only-1.19.exe /VERYSILENT /SUPPRESSMSGBOXES /NORESTART
Ativa-Wallpaper-Client-Setup-X.Y.Z.exe /VERYSILENT /SUPPRESSMSGBOXES /NORESTART
```

Depois disso, para atualizar o Wallpaper Client:

1. altere `CLIENT_VERSION` e o código do cliente;
2. gere novamente `Ativa-Wallpaper-Client-Setup-X.Y.Z.exe`;
3. envie esse instalador na tela **Ativa Updater**, informando exatamente a mesma versão `X.Y.Z`;
4. o arquivo enviado passa a ser a versão ativa e os serviços o instalam em até uma hora.

Máquinas desligadas ou sem internet atualizam quando iniciarem o serviço e conseguirem acessar a API. Se a versão instalada já for igual ou maior que a publicada, o serviço apenas registra “Atualizado” e não executa o instalador.

O instalador e a configuração da API devem ser distribuídos somente em ambiente corporativo. Para rollout amplo, também é recomendável assinar o EXE com o certificado de code signing da organização.
