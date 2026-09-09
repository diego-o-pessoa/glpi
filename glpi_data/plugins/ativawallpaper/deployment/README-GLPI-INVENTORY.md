# Implantacao pelo GLPI Inventory 1.6.10

Esta versao do plugin Inventory expoe classes internas para grupos, pacotes e
tarefas, mas nao uma API publica e estavel que garanta a criacao atomica de todo
o fluxo de Deploy. Por isso o Ativa Wallpaper nao grava diretamente nas tabelas
do Inventory. O assistente diagnostica o Inventory e o `taskscheduler`; a
configuracao abaixo e feita uma vez pela interface oficial.

## 1. Preparar o pacote

1. Compile `client/AtivaWallpaperClient.exe` com `client/build-client.ps1`.
2. Em **Administracao > Ativa Wallpaper > Configuracoes**, clique em
   **Rotacionar e baixar JSON**. O arquivo contem o segredo somente nessa vez.
3. Mantenha `pilot_hostname` com
   `DESKTOP-R1C8ICN-2026-08-17-14-54-59` no primeiro teste.
4. Em uma estacao administrativa Windows, execute:

   ```powershell
   .\build-deployment-package.ps1 `
     -ClientExe .\AtivaWallpaperClient.exe `
     -BootstrapConfig .\bootstrap-config.json
   ```

Nao passe o segredo na linha de comando. A linha de comando pode aparecer nos
logs do Agent; o JSON fica apenas no pacote protegido e no diretorio temporario
do Deploy. O instalador persiste somente o token individual recebido e nunca o
segredo compartilhado.

## 2. Grupo piloto

Em **Administracao > GLPI Inventory > Grupos de computadores**, crie primeiro um
grupo estatico chamado `Ativa Wallpaper - PILOTO` contendo exclusivamente:

`DESKTOP-R1C8ICN-2026-08-17-14-54-59`

Nao use ainda o grupo de todos os Windows.

## 3. Pacote

Em **Administracao > GLPI Inventory > Pacotes > Adicionar**:

- Nome: `Ativa Wallpaper Client - Bootstrap 1.0.0`
- Arquivos: `AtivaWallpaperClient.exe` e `bootstrap-config.json`
- Auditoria: **File is missing**
- Arquivo: `C:\ProgramData\AtivaLocacao\Wallpaper\AtivaWallpaperClient.exe`
- Se a auditoria nao tiver sucesso: **skip job**
- Acao/Command:

  ```text
  AtivaWallpaperClient.exe --install --bootstrap-config "bootstrap-config.json"
  ```

- Codigo de sucesso: `0`

A auditoria faz o bootstrap rodar apenas quando o EXE esta ausente. O instalador
tambem grava `HKLM\SOFTWARE\AtivaLocacao\Wallpaper\ClientVersion`, que pode ser
usado em pacotes futuros de upgrade. Um erro critico retorna codigo diferente de
zero; nao configure o pacote para ignorar esse retorno.

## 4. Tarefa permanente piloto

Em **Administracao > GLPI Inventory > Tarefas**:

- Nome: `Ativa Wallpaper - Bootstrap PILOTO`
- Ativa: sim
- Metodo do job: Deploy
- Target: pacote `Ativa Wallpaper Client - Bootstrap 1.0.0`
- Actor/alvo de computadores: grupo `Ativa Wallpaper - PILOTO`
- Agendamento: permanente, respeitando a janela operacional da empresa

Garanta que a acao automatica `taskscheduler` esteja em modo CLI e execute com
frequencia. O Setup Wizard mostra sua ultima execucao, mas nao altera o cron do
sistema operacional.

## 5. Validacao obrigatoria do piloto

No computador piloto, valide nesta ordem:

1. O job de Deploy termina com codigo 0.
2. Existe
   `C:\ProgramData\AtivaLocacao\Wallpaper\AtivaWallpaperClient.exe`.
3. `client.json` contem `client_token`, mas nao `registration_secret`.
4. Existe a entrada HKLM Run `AtivaWallpaperClient`.
5. Apos login do usuario, o processo roda sem elevacao.
6. `AtivaWallpaperClient.exe --once --debug` termina com codigo 0.
7. A imagem em `data\wallpaper.jpg` ou `data\wallpaper.png` tem o SHA-256
   publicado.
8. O wallpaper muda sem logoff/reinicio.
9. O dashboard mostra esse computador como **Atualizado**.
10. O log nao contem token, segredo ou header Authorization.

Pode-se executar `Test-AtivaWallpaperPilot.ps1` na sessao do usuario para as
checagens locais. Nao avance se qualquer item falhar.

## 6. Expandir para todos os Windows elegiveis

Somente depois do piloto aprovado, crie o grupo dinamico
`Ativa Wallpaper - Windows elegiveis` com criterios equivalentes a:

- Sistema operacional contem `Windows`;
- Sistema operacional nao contem `Server`;
- Item nao esta excluido nem na lixeira.

Revise a pre-visualizacao do grupo antes de salvar. Clone a tarefa piloto,
troque apenas o grupo-alvo pelo grupo dinamico e mantenha o pacote/auditoria.
Ative a tarefa em uma janela controlada. Marque **Bootstrap Deploy configurado**
no Setup Wizard depois de confirmar o alvo.

## Atualizacao futura do cliente

Compile o novo EXE, publique um pacote com nova versao e SHA-512 e use uma
auditoria **SHA-512 hash value mismatch** sobre o EXE instalado, com **skip job**
quando a verificacao nao exigir atualizacao. Teste cada pacote novamente no
grupo piloto. Trocas comuns de wallpaper nao usam Deploy; somente upgrades do
cliente usam este fluxo.

## Problemas conhecidos

`Deploy task not supported by server0` indica que o Agent esta apontando para o
endpoint generico. Neste ambiente o endpoint correto e:

`https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/`

`Can't decode JSON content` seguido de `<!DOCTYPE html>` tambem indica endpoint
incorreto retornando uma pagina HTML. Nao use `https://10.117.41.6:8443/` e nao
desative a validacao do certificado.
