# Ativa Central

Novo visual da interface de administração do GLPI 11: barra lateral em seções,
topo, página inicial ("Visão geral") e telas de módulo com um botão para cada
função.

Nada do núcleo do GLPI é alterado. Tudo entra por ganchos de plugin, e o
plugin não cria tabelas. Desligado, o GLPI volta exatamente ao visual original.

## O que muda

- **Barra lateral**: Workspace (Visão geral, Chamados, Ativos, Computadores),
  Soluções Ativa (os plugins Ativa instalados) e Gestão e sistema. Cada item
  abre uma tela; não há mais listas suspensas. O selo em "Chamados" é a
  quantidade de chamados novos.
- **Visão geral**: chamados em aberto, aguardando e resolvidos, computadores,
  gráfico do período, fila, categorias, desempenho pessoal e da equipe.
  Substitui a página inicial do GLPI (os painéis nativos continuam em
  "Painéis nativos do GLPI", no rodapé da tela).
- **Telas de módulo**: um cartão para cada função da seção, com a quantidade
  de itens. Os cartões saem do próprio menu do GLPI, então cada perfil vê só o
  que pode acessar, e o que um plugin ou o GLPI acrescentar depois aparece
  sozinho.
- O autoatendimento (interface simplificada) não muda.

## Ligar e desligar

| Para quem | Como |
|---|---|
| Todos | Configurar > Plugins > Ativa Central (ícone de chave), opção "Usar o novo visual". Também em Configurações > Ativa Central, no visual novo. |
| Só eu, nesta sessão | Menu do usuário (canto superior direito) > "Usar o visual clássico do GLPI". Para voltar: mesmo menu > "Voltar ao novo visual". |
| Emergência | Abrir `/plugins/ativacentral/front/toggle.php?mode=classic`. Devolve o GLPI original mesmo que alguma tela do visual novo não abra. |

Desativar o plugin em Configurar > Plugins tem o mesmo efeito de desligar para
todos.

## Arquivos

- `setup.php`, `src/Shell.php`: decide se o visual vale para a sessão e liga
  CSS, JS e o gancho do menu.
- `src/Nav.php`: monta a barra lateral (gancho `redefine_menus`). Os menus
  originais continuam no array, só escondidos: é deles que saem a trilha e os
  botões do topo.
- `src/Hubs.php`: telas de módulo. `src/Metrics.php`: números da Visão geral.
- `public/css/shell.css`: barra lateral e topo. `public/css/app.css` e
  `public/js/app.js`: conteúdo das telas próprias.

Os arquivos de `public/` vão para o navegador com a data de modificação no
endereço, então uma alteração de CSS/JS aparece sem subir a versão do plugin.
