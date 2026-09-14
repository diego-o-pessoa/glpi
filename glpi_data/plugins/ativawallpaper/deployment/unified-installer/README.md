# Instalador unificado Ativa para Windows

O gerador cria um único arquivo Windows x64:

- `Ativa-Unified-Agent-Setup-X.Y.Z.exe`: instala ou atualiza o GLPI Agent, o Wallpaper Client e o serviço Ativa Unified Updater.

O serviço roda como `SYSTEM`, identifica o computador no dashboard imediatamente ao iniciar e consulta as versões do Ativa Updater a cada hora. A partir do pacote 1.5.1, também consulta comandos leves a cada 15 segundos, permitindo usar **Verificar agora** sem alterar o intervalo regular. Quando encontra uma versão maior do pacote unificado (ou uma menor com rollback autorizado, a partir do serviço 1.3.0 / pacote 1.6.0), baixa o EXE, valida tamanho e SHA-256 e o executa silenciosamente. Em falhas de comunicação, tenta novamente em cinco minutos.

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

## Rollback (downgrade)

Se uma versão publicada causar problemas, volte o parque para uma versão anterior pelo dashboard do **Ativa Updater**:

1. no **Histórico de Versões**, clique em **Rollback** na versão desejada e confirme;
2. clique em **Verificar agora** para antecipar (sem isso, cada máquina volta na próxima consulta, em até 1 hora);
3. acompanhe a tabela **Computadores**: máquinas pendentes mostram “Rollback pendente” e, ao terminar, contam como “na versão atual”.

Regras:

- **Definir como atual** publica a versão só para atualização: máquinas em versões maiores continuam onde estão e aparecem como “Acima da versão publicada”. Somente **Rollback** (ou **Autorizar downgrade** na versão atual) faz máquinas voltarem de versão.
- O rollback só é permitido para pacotes **1.6.0 ou superiores**. Pacotes anteriores registram o Wallpaper Client com o segredo de bootstrap embutido no build; se esse segredo já foi rotacionado, a instalação falharia no meio. A partir do 1.6.0, a reinstalação reaproveita o registro existente da máquina.
- **Não exclua** do dashboard versões que possam ser necessárias como destino de rollback.
- O MSI oficial do GLPI Agent aceita downgrade (`AllowDowngrades`) e preserva `etc/` e `var/`, então a máquina continua ligada ao mesmo computador no inventário.
- Se a instalação não reconfigurar o serviço em 30 minutos, o serviço registra falha com o final do `installer-*.log` e tenta de novo após 5 min e 30 min. A partir da 3ª falha do mesmo pacote, tenta uma vez por dia; **Verificar agora** ou a publicação de outra versão libera uma tentativa imediata.

## Como a atualização silenciosa acontece

1. Na consulta (a cada hora ou por **Verificar agora**), o serviço compara a versão publicada com a instalada e decide entre atualizar, voltar (rollback autorizado) ou não fazer nada.
2. Se precisar instalar, baixa o EXE, valida tamanho e SHA-256, remove instaladores de versões anteriores e executa o setup em modo `/VERYSILENT`.
3. O setup para o serviço e espera ele terminar. Depois atualiza o GLPI Agent, repetindo por até 5 minutos se o Windows Installer estiver ocupado (código 1618), atualiza o Wallpaper Client e reconfigura e inicia o serviço.
4. O Wallpaper Client é iniciado novamente em **cada sessão de usuário conectada**. Antes, quando o setup rodava como SYSTEM, o cliente só voltava no próximo logon.
5. Ao reiniciar, o serviço reporta **Atualizado** ("Versão X instalada com sucesso") no dashboard.
