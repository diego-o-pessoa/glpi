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
- Rollbacks seguem as mesmas regras de tentativas e falha descritas abaixo.

## Como a atualização silenciosa acontece

1. Na consulta (a cada hora ou por **Verificar agora**), o serviço compara a versão publicada com a instalada e decide entre atualizar, voltar (rollback autorizado) ou não fazer nada.
2. Se precisar instalar, baixa o EXE (retomando downloads interrompidos), valida tamanho e SHA-256, remove instaladores de versões anteriores e executa o setup com `/VERYSILENT /SUPERVISED=1 /NOCLOSEAPPLICATIONS`. Falhas de rede momentâneas (timeout, conexão recusada) são repetidas antes de desistir.
3. O serviço **continua rodando e acompanha o processo do instalador**. O setup renomeia o executável do serviço em uso (`AtivaUnifiedUpdater.exe.old-*`), atualiza o GLPI Agent (repetindo por até 5 minutos se o Windows Installer estiver ocupado, código 1618), atualiza o Wallpaper Client (mantendo o registro existente se o servidor não responder) e reconfigura o serviço. Se a instalação for abortada, o executável renomeado é devolvido.
4. No fim, o setup reinicia o serviço para carregar o novo executável, cria ou atualiza o **vigia** e inicia o Wallpaper Client em **cada sessão de usuário conectada**.
5. O novo serviço reporta **Atualizado** ("Versão X instalada com sucesso") no dashboard.

> O serviço é gerado como aplicativo de **console** (`build-service.ps1`). Na versão sem console, o PyInstaller mostrava avisos em uma caixa de mensagem que ninguém consegue fechar quando o programa roda como SYSTEM, e o passo `--configure` travava o instalador indefinidamente.

## Verificar agora e ações por computador

O dashboard atualiza a seção **Computadores** sozinho, a cada 3 s, sem recarregar a página.

- **Verificar agora** (todos): o serviço 1.5.0+ recebe o comando em até 15 s mesmo durante downloads e instalações, **cancela o que estiver fazendo** (inclusive o instalador em andamento) e recomeça a verificação. Serviços 1.2.0 a 1.4.x só verificam depois de terminar a instalação atual; anteriores a 1.2.0 não recebem comandos.
- **Logs** (por computador): coleta `service.log`, `watchdog.log`, a última falha de instalação, o log do instalador e o do Wallpaper Client e mostra tudo em **Ver logs enviados**.
- **Reinstalar**: cancela o que estiver em andamento e instala de novo o pacote publicado, mesmo que a versão já esteja instalada. Não faz downgrade sem rollback autorizado.
- **Reiniciar serviço**: envia os logs e reinicia o serviço "Ativa Unified Updater".

As três ações por computador exigem o serviço 1.5.0 (pacote 1.6.2) ou superior. Sem confirmação em 2 minutos, o status vira **Sem resposta ao comando**.

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
