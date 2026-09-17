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

O número do pacote (`unified-version.txt`) é independente das versões dos componentes. O build informa o que foi incluído ("Conteúdo do pacote X: Wallpaper Client Y | Ativa Unified Updater Z | GLPI Agent W"), grava essas versões no manifesto (`wallpaper_client_version`, `unified_updater_version`) e na descrição do EXE (Propriedades > Detalhes). O build recusa gerar de novo uma versão que já existe em `dist`: dois arquivos diferentes com o mesmo número deixam máquinas com componentes antigos. Use `-AllowOverwrite` apenas para uma versão que nunca foi distribuída.

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
- Rollbacks seguem as mesmas regras de tentativas e falha descritas abaixo.

## Como a atualização silenciosa acontece

1. Na consulta (a cada hora ou por **Verificar agora**), o serviço compara a versão publicada com a instalada e decide entre atualizar, voltar (rollback autorizado) ou não fazer nada.
2. Se precisar instalar, baixa o EXE (retomando downloads interrompidos), valida tamanho e SHA-256 e remove instaladores de versões anteriores. Falhas de rede momentâneas (timeout, conexão recusada) são repetidas antes de desistir.
3. O serviço (1.6.0+) entrega a instalação ao **executor**: uma cópia do serviço em `UnifiedUpdater\runner`, iniciada fora do processo do serviço. O executor repete os passos de `Deploy-AtivaUnifiedAgent.ps1` (`Instalar-Ativa-Agent.cmd`):
   1. confere de novo o SHA-256;
   2. para o serviço;
   3. encerra instaladores anteriores e processos `AtivaUnifiedUpdater.exe` que ficaram presos;
   4. executa `/VERYSILENT /SUPPRESSMSGBOXES /NORESTART /NOCLOSEAPPLICATIONS /SP- /LOG=...` com limite de 20 minutos (se passar, encerra o setup e tudo o que ele abriu);
   5. grava `install-result.json` e garante que o serviço volte a rodar.
4. O setup atualiza o GLPI Agent (repetindo por até 5 minutos se o Windows Installer estiver ocupado, código 1618), atualiza o Wallpaper Client (mantendo o registro existente se o servidor não responder), reconfigura e reinicia o serviço, cria ou atualiza o **vigia** e inicia o Wallpaper Client em **cada sessão de usuário conectada**.
5. O novo serviço reporta **Atualizado** ("Versão X instalada com sucesso") no dashboard. Em caso de falha, lê o `install-result.json` e envia o motivo (código de saída, tempo limite ou erro do executor) com o log.

Durante a instalação (normalmente 1 a 3 minutos) o serviço fica parado: comandos do dashboard enviados nesse intervalo são atendidos quando ele voltar. O executor grava `logs\install-runner.log`, incluído em **Logs** e no log de falha.

> Serviços anteriores a 1.6.0 continuam instalando o próximo pacote pelo fluxo antigo (acompanhado ou não). O executor passa a valer a partir da atualização seguinte.

> O serviço é gerado como aplicativo de **console** (`build-service.ps1`). Na versão sem console, o PyInstaller mostrava avisos em uma caixa de mensagem que ninguém consegue fechar quando o programa roda como SYSTEM, e o passo `--configure` travava o instalador indefinidamente.

## Verificar agora e ações por computador

O dashboard atualiza a seção **Computadores** sozinho, a cada 3 s, sem recarregar a página.

- **Verificar agora** (todos): o serviço 1.5.0+ recebe o comando em até 15 s mesmo durante downloads, **cancela o que estiver fazendo** (inclusive um instalador em andamento) e recomeça a verificação. No serviço 1.6.0+, durante os minutos em que o executor instala o pacote, o serviço está parado e atende o comando ao voltar. Serviços 1.2.0 a 1.4.x só verificam depois de terminar a instalação atual; anteriores a 1.2.0 não recebem comandos.
- **Logs** (por computador): coleta `service.log`, `watchdog.log`, a última falha de instalação, o log do instalador e o do Wallpaper Client e mostra tudo em **Ver logs enviados**.
- **Reinstalar**: cancela o que estiver em andamento e instala de novo o pacote publicado, mesmo que a versão já esteja instalada. Não faz downgrade sem rollback autorizado.
- **Reiniciar serviço**: envia os logs e reinicia o serviço "Ativa Unified Updater".

As três ações por computador exigem o serviço 1.5.0 (pacote 1.6.2) ou superior. Sem confirmação em 2 minutos, o status vira **Sem resposta ao comando**.

## Acesso remoto (Ativa Remote + RustDesk)

A partir do pacote 1.6.8 (serviço 1.7.1), o pacote leva o RustDesk 1.3.1 oficial (SHA-256 conferido no build) em `UnifiedUpdater\rustdesk.exe`. O serviço, que já roda como SYSTEM:

1. instala o RustDesk (`--silent-install`) se ele não estiver em `Program Files\RustDesk` e mantém o serviço **RustDesk** ligado;
2. lê o ID (`--get-id`) e informa a cada 10 s em `/plugins/ativaremote/api/v1/report`, com o mesmo token da API do Ativa Updater;
3. troca a senha permanente por uma aleatória desconhecida ao iniciar, para que nenhuma senha antiga continue válida.

Em **Administração > Ativa Remote**, **Solicitar acesso** envia o pedido ao computador. Com **Autorização: ON**, o usuário responde Sim/Não em até 60 s. Com a autorização, o serviço gera uma senha nova e a envia só ao GLPI, onde ela fica criptografada e aparece para quem tem direito de gerenciar, junto com o botão **Conectar** (`rustdesk://`). **Encerrar**, ou 4 horas de sessão, faz o computador trocar a senha de novo. Pedidos sem resposta em 3 minutos viram **Falhou**.

Requisitos: plugin **Ativa Updater** ativo com a API habilitada e o RustDesk instalado no computador do técnico. O log fica em `UnifiedUpdater\logs\service.log` (linhas "Ativa Remote:").

## Vigia (segurança na máquina)

O instalador cria a tarefa agendada **Ativa Unified Updater Watchdog**, que roda a cada 15 minutos como SYSTEM uma **cópia separada** do serviço (`UnifiedUpdater\watchdog\AtivaUnifiedUpdater.exe`). Essa cópia só é substituída depois que uma versão nova do serviço consegue falar com a API. O vigia:

- **inicia o serviço** se ele estiver parado e nenhuma instalação estiver em andamento;
- **recria o serviço** se ele tiver sido removido;
- **restaura o executável** do serviço a partir da cópia se ele estiver ausente ou não iniciar;
- **encerra um instalador travado** há mais de 45 minutos;
- **reinicia o serviço** se ele parar de dar sinal de vida (heartbeat) por 30 minutos.

Cada reparo é registrado em `logs\watchdog.log` e aparece no dashboard, na coluna Detalhes, como "Vigia: …". Se a tarefa agendada for apagada, o serviço a recria ao iniciar.

**Logs** também traz o estado do serviço, os instaladores em execução, o espaço livre em disco e o `configure.log`. O texto não inclui a configuração nem o token da API.

## Falhas, novas tentativas e log de erro

| Situação | O que o serviço faz | Status no dashboard |
|---|---|---|
| Setup termina com código de erro | registra a falha na hora | **Nova tentativa** |
| Setup passa de 20 minutos | finaliza o setup (e processos filhos) e registra a falha | **Nova tentativa** |
| Serviço reinicia e o setup não existe mais sem ter configurado a nova versão | registra a falha na hora | **Nova tentativa** |
| 1ª, 2ª e 3ª falha do mesmo pacote | novas tentativas após 1 min, 5 min e 15 min | **Nova tentativa** |
| 4ª falha (tentativa inicial + 3 novas) | para de tentar e passa a tentar 1 vez por dia; **Verificar agora** ou outra versão publicada inicia um novo ciclo | **Falha na Instalação** |

Toda falha envia ao dashboard um **log da instalação** (link **Ver log da instalação** na coluna Detalhes) com:

- resultado (código de saída do setup, tempo limite etc.);
- final do log do Inno Setup (`logs\installer-*.log`);
- linhas de erro e final do log do GLPI Agent (`logs\glpi-agent-msi.log`);
- final do log do Wallpaper Client.

O mesmo conteúdo fica em `C:\ProgramData\AtivaLocacao\UnifiedUpdater\logs\install-failure.log`.

O dashboard também marca **Falha na Instalação** quando o computador fica mais de 45 min sem informar progresso durante uma instalação, ou quando a instalação passa de 3 h sem concluir. Isso cobre serviço parado, setup travado e máquinas com serviço anterior ao 1.4.0 (pacotes até 1.6.0), que podiam ficar presas em "Instalando". Nesses casos não há log remoto: consulte a pasta `logs` no próprio computador.
