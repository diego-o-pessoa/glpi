# Manutencao autorizada e wallpaper offline — pacote 1.7.13

## Ativar

1. Atualize o plugin Ativa Guardian no servidor (versao 1.8.0).
2. Em Configuracoes, defina e confirme a senha de manutencao Ativa (12 caracteres ou mais).
3. Baixe novamente a configuracao do servico Guardian. Use esse JSON como
   `-GuardianConfig` no build do instalador unificado. O build recusa configuracao
   sem verificador de senha para evitar distribuir um pacote sem protecao.
4. Gere o pacote 1.7.13 e instale primeiro em uma maquina piloto. Depois publique.

O JSON exportado inclui PBKDF2-HMAC-SHA256 com salt aleatorio e 600.000 iteracoes.
A senha original nao fica no pacote, comando ou log. O JSON ainda tem tokens de
API: armazene-o como segredo. A troca da senha chega aos clientes pelo novo pacote;
ate a atualizacao cada maquina aceita a senha anterior. Mantenha a anterior durante
a transicao. Atualizacoes como SYSTEM nao precisam de senha interativa.

## Usar

Menu Iniciar > Ativa > Manutencao Ativa. O Windows solicita elevacao UAC, e o
Guardian solicita a senha Ativa. A senha libera controle dos servicos por 15 minutos
e abre Servicos do Windows. O Guardian e parado para permitir manutencao; os demais
servicos sao escolhidos pelo administrador. Uma tarefa independente como SYSTEM
restaura as permissoes em ate um minuto depois do prazo e reinicia os servicos que
estavam rodando antes da manutencao. O watchdog do Updater respeita esse prazo.
Cinco senhas incorretas bloqueiam o dialogo por cinco minutos.

Servicos abrangidos: AtivaGuardian, AtivaUnifiedUpdater, RustDesk e GLPI Agent
(nomes `glpi-agent` ou `GLPIAgent`, quando instalados). Leitura de status continua
permitida. Uma parada direta pelo Gerenciador de Servicos recebe acesso negado
fora da manutencao. Atualizacoes locais pelo instalador pedem a senha antes de
parar servicos; atualizacoes automaticas como SYSTEM continuam silenciosas.

Executaveis/configuracoes sao protegidos contra alteracao/exclusao por usuarios
comuns. O processo do wallpaper e seu evento de parada tambem restringem
encerramento por usuarios comuns. O instalador e o sistema conservam controle.

## Limites reais

- Nao ha uma caixa de senha global do Windows para `taskkill`, Explorer e
  Gerenciador de Tarefas. Essas ferramentas mostram acesso negado; a senha aparece
  no atalho de manutencao. O popup nao e injetado em programas de terceiros.
- Administradores locais/SYSTEM podem tomar posse de objetos ou alterar ACLs.
  A senha protege o fluxo normal de manutencao, nao e uma fronteira contra quem
  administra o Windows. Para funcionarios, use contas padrao.
- A interface do RustDesk pode ser fechada pelo proprio programa; o servico
  protegido continua separado. Nao bloqueamos as rotinas internas do RustDesk.
- O cliente de wallpaper roda como usuario e precisa escrever em data/state/logs.
  Esses dados de execucao continuam gravaveis. Nao se promete proteger todos os
  arquivos contra o mesmo usuario que precisa grava-los. O cache e conferido por
  SHA-256 antes de ser aplicado; isso detecta corrupcao, nao um administrador.
- Nao altera antivirus, nao implementa driver e nao impede desligamento/logoff.
- Perder a senha requer redefini-la no GLPI e distribuir outro pacote por um
  canal SYSTEM existente; administracao local continua sendo a recuperacao final.

## Wallpaper ao entrar no Windows

Wallpaper Client 1.6.4 aplica o ultimo cache validado antes de carregar a
configuracao/conectar a API. A politica e o caminho local persistem em uma parada
normal. Somente desinstalacao ou distribuicao desativada retiram a politica.
Uma tarefa de logon, sem dependencia de rede ou atraso, inicia o cliente na
sessao do usuario; HKLM Run permanece como alternativa, com exclusao mutua.

Isso elimina a espera da API para perfis que ja receberam a imagem. O primeiro
login de um usuario sem cache ainda precisa receber um wallpaper. Nao altera a
tela de bloqueio/logon do Windows, nem garante o momento exato de desenho do
Explorer antes de uma sessao nova existir.

## Validacao piloto obrigatoria

- Conta comum: encerrar o cliente por taskkill e parar servicos devem falhar;
  apagar executaveis deve falhar. Confirmar que sincronizacao e logs funcionam.
- Senha incorreta/cancelar: nenhum servico e liberado. Senha correta: manutencao
  permitida; esperar 16 minutos e conferir reprotecao/reinicio do que foi parado.
- Upgrade silencioso SYSTEM e upgrade manual com senha: verificar todos os
  componentes, tarefas e configuracoes apos a instalacao.
- Depois de uma sincronizacao, desconectar a rede/VPN e reiniciar/logar: a
  imagem anterior deve permanecer e o log deve registrar eventual correcao local
  antes da tentativa de conexao. Ao reconectar, o status pendente deve ser enviado.
- Testar dois usuarios e logoff, politica externa de dominio, cache corrompido,
  distribuicao desativada e desinstalacao autorizada.

Nao foi realizada instalacao/alteracao de servicos nas maquinas de producao
durante o desenvolvimento desta mudanca.
