# Changelog

## Em desenvolvimento

- Plugin e cliente 1.3.0 removem a barra redundante de conformidade e passam a
  exibir o ciclo completo no painel inferior.
- O cliente envia um heartbeat com o horario exato da proxima sincronizacao;
  o dashboard mostra a contagem regressiva e permanece atualizado a cada segundo.
- A atividade em tempo real informa quando nenhuma troca foi necessaria e quando
  uma alteracao do usuario foi detectada e restaurada automaticamente.

- Plugin e cliente 1.2.0 adicionam a aba **Alteracoes detectadas**, com usuario
  da sessao Windows, computador, tipo de aplicacao e restauracao automatica.
- **Aplicar em todos** passa a iniciar por AJAX e exibe imediatamente o lote,
  o computador em processamento e uma linha do tempo consultada a cada segundo,
  sem recarregar a pagina ao chegar a 100%.
- Cada aplicacao possui identificador unico para que repeticoes de rede nao
  dupliquem os eventos de auditoria enviados pelo cliente.
- Cliente 1.1.1 corrige concorrencia entre duas instancias e deixa de
  substituir o arquivo de wallpaper que esta em uso pelo Windows.
- A reaplicacao libera temporariamente apenas a politica criada pelo cliente,
  restaura o bloqueio em seguida e trata ACL/GPO externa sem falhar a troca.
- Cada polling, inclusive respostas HTTP 304, verifica o wallpaper realmente
  exibido e restaura automaticamente o corporativo quando houver alteracao.
- Operacao **Aplicar em todos** agora usa um identificador exclusivo por lote,
  invalida o ETag de cada cliente e acompanha aguardando, aplicando, sucesso e erro.
- Dashboard ganhou barra de progresso atualizada automaticamente e lista dos
  computadores participantes da aplicacao.
- Correcao do repasse do cabecalho `Authorization` pelo Apache/FastCGI e
  compatibilidade adicional com `X-Ativa-Client-Token`.
- O instalador agora inicia o cliente em polling continuo imediatamente, sem
  aguardar o proximo logon para receber o comando **Aplicar**.
- Polling padrao reduzido para 60 segundos, com jitter de 10 segundos, para
  executar solicitacoes do dashboard rapidamente.
- Botao unico **Aplicar em todos** para solicitar a reaplicacao do wallpaper
  atual em todos os clientes nao revogados.
- Diagnostico explicito de falta de espaco e permissoes no armazenamento
  privado durante uploads.
- Build automatizado do instalador unico para GLPI Agent 1.19 e Ativa Wallpaper
  Client, com validacao de hash e configuracao completa do endpoint Inventory.

## 1.0.0 - 2026-09-08

- Plugin GLPI 11 instalavel com menu, direitos de perfil e aba no computador.
- Upload JPG/PNG validado, thumbnail, SHA-256, versao e rollback com auditoria.
- Dashboard paginado com status atualizado, pendente, offline e erro.
- API v1 HTTPS com registro, bearer token com hash, rate limiting, ETag e 304.
- Cliente Windows 10/11 com instalacao SYSTEM, execucao no usuario, troca atomica,
  politicas conservadoras, backoff, jitter e logs rotativos.
- Build PyInstaller, pacote de bootstrap e roteiro de rollout piloto.
