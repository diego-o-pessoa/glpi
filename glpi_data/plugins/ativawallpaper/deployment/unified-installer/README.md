# Instalador unificado Ativa para Windows

O gerador cria um único arquivo Windows x64:

- `Ativa-Unified-Agent-Setup-X.Y.Z.exe`: instala ou atualiza o GLPI Agent, o Wallpaper Client e o serviço Ativa Unified Updater.

O serviço roda como `SYSTEM`, identifica o computador no dashboard imediatamente ao iniciar e consulta as versões do Ativa Updater a cada hora. A partir do pacote 1.5.1, também consulta comandos leves a cada 15 segundos, permitindo usar **Verificar agora** sem alterar o intervalo regular. Quando encontra uma versão maior do pacote unificado, baixa o EXE, valida tamanho e SHA-256 e o executa silenciosamente. Em falhas de comunicação, tenta novamente em cinco minutos.

## Preparar as configurações

Baixe os dois arquivos no GLPI e coloque-os nesta pasta:

1. `bootstrap-config.json`, em **Ativa Wallpaper > Configurações**;
2. `ativaupdater-service-config.json`, em **Ativa Updater > Configurações > Baixar configuração do serviço**.

Os dois arquivos contêm segredos. Não faça commit deles nem os envie por canal público.

## Gerar com dois cliques

Atualize `unified-version.txt` para uma versão nova no formato `X.Y.Z` e execute `Criar-Instalador-Para-Todos.cmd`.

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

O arquivo final e seu manifesto SHA-256 são gerados em `dist`.

## Primeira instalação e atualizações

Instale uma única vez:

```text
Ativa-Unified-Agent-Setup-X.Y.Z.exe /VERYSILENT /SUPPRESSMSGBOXES /NORESTART
```

Para distribuir qualquer alteração futura:

1. atualize o componente desejado;
2. aumente a versão de `unified-version.txt`;
3. gere o instalador novamente;
4. envie o EXE em **Ativa Updater**, informando a mesma versão `X.Y.Z`.

O GLPI Agent pode ser alterado pelos parâmetros `-AgentVersion` e `-AgentSha256` do builder. Mesmo que apenas o GLPI Agent mude, gere e publique uma nova versão do pacote unificado. Máquinas que já têm uma versão igual ou maior apenas registram “Atualizado” e não reinstalam nada.

Máquinas desligadas ou sem internet atualizam quando voltarem a funcionar. O instalador contém segredos de bootstrap e deve ser distribuído somente por canal corporativo protegido.
