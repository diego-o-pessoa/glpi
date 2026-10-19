<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use CommonDBTM;
use CommonITILObject;
use Html;
use ReflectionClass;
use Session;
use Throwable;
use Ticket;
use Toolbox;

/**
 * Telas de modulo: no lugar da lista suspensa do menu, uma tela com um botao
 * (cartao) para cada funcao, com a quantidade de itens quando faz sentido.
 *
 * Os cartoes saem do proprio menu do GLPI, entao cada pessoa ve exatamente o
 * que o perfil dela permite, incluindo funcoes de plugins e tipos de ativo
 * criados depois. As descricoes e os grupos abaixo sao so apresentacao.
 */
final class Hubs
{
    /** Tipos em que a contagem nao diz nada (ou e pessoal de cada usuario). */
    private const NO_COUNT = [
        'config', 'reminder', 'rssfeed', 'savedsearch', 'entity', 'planning', 'stat', 'report', 'impact',
        'auth', 'logviewer', 'inventory', 'commondropdown', 'commondevice', 'allassets', 'servicecatalog', 'plugin',
    ];

    /** Apresentacao das secoes nativas: subtitulo e grupos de cartoes. */
    private const SECTORS = [
        'assets' => [
            'subtitle' => 'Todos os tipos de ativo do inventário, com a quantidade de cada um.',
            'groups'   => [
                'Equipamentos'            => ['computer', 'monitor', 'peripheral', 'printer', 'phone', 'networkequipment', 'unmanaged', 'allassets'],
                'Software e suprimentos'  => ['software', 'cartridgeitem', 'consumableitem'],
                'Datacenter e cabeamento' => ['rack', 'enclosure', 'pdu', 'passivedcequipment', 'cable'],
            ],
        ],
        'helpdesk' => [
            'subtitle' => 'Fila de atendimento, problemas, mudanças e estatísticas.',
            'groups'   => [
                'Atendimento'          => ['ticket', 'problem', 'change', 'servicecatalog'],
                'Agenda e recorrência' => ['planning', 'ticketrecurrent', 'recurrentchange'],
                'Análise'              => ['stat'],
            ],
        ],
        'management' => [
            'subtitle' => 'Licenças, contratos, fornecedores, documentos e infraestrutura.',
            'groups'   => [
                'Contratos e financeiro' => ['softwarelicense', 'budget', 'supplier', 'contact', 'contract', 'document'],
                'Infraestrutura'         => ['line', 'certificate', 'datacenter', 'cluster', 'domain', 'appliance', 'database'],
            ],
        ],
        'tools' => [
            'subtitle' => 'Projetos, base de conhecimento, reservas, relatórios e pesquisas salvas.',
            'groups'   => [
                'Trabalho em equipe'   => ['project', 'knowbaseitem', 'reservationitem', 'reminder', 'rssfeed'],
                'Relatórios e análise' => ['report', 'savedsearch', 'impact'],
            ],
        ],
        'admin' => [
            'subtitle' => 'Usuários, grupos, entidades, perfis, regras e registros do sistema.',
            'groups'   => [
                'Pessoas e acesso'   => ['user', 'group', 'entity', 'profile'],
                'Regras e automação' => ['rule', 'form'],
                'Sistema'            => ['queuednotification', 'logviewer', 'inventory'],
            ],
        ],
        'config' => [
            'subtitle' => 'Cadastros, notificações, níveis de serviço, autenticação e opções gerais.',
            'groups'   => [
                'Cadastros'   => ['commondropdown', 'commondevice', 'assetdefinition', 'fieldunicity', 'link'],
                'Atendimento' => ['slm', 'notification', 'mailcollector', 'webhook'],
                'Sistema'     => ['config', 'auth', 'oauthclient', 'crontask', 'plugin'],
            ],
        ],
        'plugins' => [
            'subtitle' => 'Funções adicionadas por plugins.',
            'groups'   => [],
        ],
    ];

    /** Descricao curta de cada funcao (pela chave do item no menu). */
    private const DESCRIPTIONS = [
        // Ativos
        'computer'           => 'Estações de trabalho, notebooks e servidores.',
        'monitor'            => 'Telas e monitores ligados aos computadores.',
        'software'           => 'Programas instalados, versões e licenças.',
        'networkequipment'   => 'Switches, roteadores e pontos de acesso.',
        'peripheral'         => 'Teclados, mouses, webcams e outros dispositivos.',
        'printer'            => 'Impressoras, contadores e suprimentos.',
        'cartridgeitem'      => 'Modelos de cartucho e controle de estoque.',
        'consumableitem'     => 'Materiais de consumo e controle de estoque.',
        'phone'              => 'Telefones e aparelhos de ramal.',
        'rack'               => 'Racks e ocupação dos datacenters.',
        'enclosure'          => 'Gabinetes e chassis.',
        'pdu'                => 'Réguas e unidades de distribuição de energia.',
        'passivedcequipment' => 'Equipamentos passivos do datacenter.',
        'unmanaged'          => 'Dispositivos vistos na rede e ainda não classificados.',
        'cable'              => 'Cabos e conexões entre equipamentos.',
        'allassets'          => 'Todos os tipos de ativo em uma única lista.',
        // Chamados
        'ticket'             => 'Todos os chamados, com filtros e pesquisas salvas.',
        'problem'            => 'Causas que geram chamados repetidos.',
        'change'             => 'Mudanças planejadas no ambiente.',
        'planning'           => 'Agenda de tarefas e atendimentos.',
        'stat'               => 'Estatísticas de atendimento por período.',
        'ticketrecurrent'    => 'Chamados criados automaticamente em intervalos.',
        'recurrentchange'    => 'Mudanças criadas automaticamente em intervalos.',
        'servicecatalog'     => 'Formulários que os usuários veem ao abrir um pedido.',
        // Gerencia
        'softwarelicense'    => 'Licenças adquiridas e quantas estão em uso.',
        'budget'             => 'Orçamentos e valores gastos.',
        'supplier'           => 'Fornecedores e prestadores de serviço.',
        'contact'            => 'Contatos dos fornecedores.',
        'contract'           => 'Contratos, vigências e renovações.',
        'document'           => 'Arquivos anexados a ativos e chamados.',
        'line'               => 'Linhas telefônicas e chips.',
        'certificate'        => 'Certificados digitais e validades.',
        'datacenter'         => 'Datacenters e salas de servidores.',
        'cluster'            => 'Conjuntos de servidores.',
        'domain'             => 'Domínios de internet e vencimentos.',
        'appliance'          => 'Sistemas e aplicações da empresa.',
        'database'           => 'Instâncias de banco de dados.',
        // Ferramentas
        'project'            => 'Projetos, tarefas e equipes.',
        'reminder'           => 'Lembretes pessoais e públicos.',
        'rssfeed'            => 'Notícias acompanhadas por RSS.',
        'knowbaseitem'       => 'Artigos e soluções para consulta.',
        'reservationitem'    => 'Reserva de equipamentos e salas.',
        'report'             => 'Relatórios prontos do GLPI.',
        'savedsearch'        => 'Pesquisas salvas e alertas.',
        'impact'             => 'Mapa de dependência entre ativos.',
        // Usuarios e equipes
        'user'               => 'Contas de acesso, contatos e perfis de cada pessoa.',
        'group'              => 'Equipes e setores usados nos chamados e ativos.',
        'entity'             => 'Empresas, filiais e unidades.',
        'profile'            => 'O que cada tipo de usuário pode ver e fazer.',
        'rule'               => 'Regras automáticas para chamados, ativos e usuários.',
        'form'               => 'Formulários de solicitação e seus campos.',
        'queuednotification' => 'E-mails e avisos aguardando envio.',
        'logviewer'          => 'Registros de eventos e erros do sistema.',
        'inventory'          => 'Agentes, importação e regras do inventário.',
        // Configuracoes
        'commondropdown'     => 'Listas suspensas: categorias, locais, status e outras.',
        'commondevice'       => 'Componentes de hardware (processador, memória, disco).',
        'assetdefinition'    => 'Tipos de ativo personalizados.',
        'fieldunicity'       => 'Campos que não podem se repetir.',
        'link'               => 'Links externos exibidos nos itens.',
        'slm'                => 'Níveis de serviço (SLA e OLA).',
        'notification'       => 'Modelos e regras de notificação.',
        'mailcollector'      => 'Caixas de e-mail que viram chamados.',
        'webhook'            => 'Avisos enviados a outros sistemas.',
        'config'             => 'Opções gerais do GLPI.',
        'auth'               => 'Login: LDAP, e-mail e outras origens.',
        'oauthclient'        => 'Aplicativos autorizados a usar a API.',
        'crontask'           => 'Tarefas automáticas e seus horários.',
        'plugin'             => 'Instalar, ativar e configurar plugins.',
    ];

    /**
     * @return array<string, mixed>|null null quando a tela nao existe para este usuario
     */
    public static function get(string $key): ?array
    {
        $menu = Html::generateMenuSession();
        if ($key === 'computers') {
            return self::computers($menu);
        }
        if ($key === 'preference' || !preg_match('/^[a-z0-9_\\\\-]{1,80}$/i', $key)) {
            return null;
        }
        $hub = self::sector($menu, $key);
        if ($hub !== null && $key === 'helpdesk') {
            $hub = self::withTicketQueue($hub);
        }
        if ($hub !== null && $key === 'config' && Shell::canConfigure()) {
            $hub['groups'][] = [
                'title' => 'Aparência',
                'cards' => [[
                    'key'   => 'ativacentral',
                    'title' => 'Ativa Central',
                    'desc'  => 'Ligar ou desligar o novo visual e ajustar o painel.',
                    'icon'  => 'ti ti-layout-dashboard',
                    'url'   => Shell::url('/front/config.php'),
                    'count' => null,
                    'unit'  => '',
                    'add'   => '',
                    'cta'   => 'Abrir opções',
                ]],
            ];
        }
        return $hub;
    }

    /**
     * Pesquisa de chamados ja filtrada (status, atribuidos a mim, categoria).
     *
     * @param array{status?: int|string, mine?: bool, category?: int} $filter
     */
    public static function ticketSearchUrl(array $filter): string
    {
        $criteria = [];
        if (isset($filter['status'])) {
            $criteria[] = ['link' => 'AND', 'field' => 12, 'searchtype' => 'equals', 'value' => $filter['status']];
        }
        if (!empty($filter['mine'])) {
            $criteria[] = ['link' => 'AND', 'field' => 5, 'searchtype' => 'equals', 'value' => (int) Session::getLoginUserID()];
        }
        if (isset($filter['category'])) {
            $criteria[] = ['link' => 'AND', 'field' => 7, 'searchtype' => 'equals', 'value' => (int) $filter['category']];
        }
        return '/front/ticket.php?' . Toolbox::append_params(['reset' => 'reset', 'criteria' => $criteria], '&');
    }

    /**
     * Tela de uma secao do menu (nativa ou de plugin).
     *
     * @param array<string, mixed> $menu
     * @return array<string, mixed>|null
     */
    private static function sector(array $menu, string $key): ?array
    {
        $sector = $menu[$key] ?? null;
        if (!is_array($sector) || !Nav::hasPages($sector)) {
            return null;
        }
        $classes = [];
        foreach ((array) ($sector['types'] ?? []) as $type) {
            if (is_string($type)) {
                $classes[strtolower($type)] = $type;
            }
        }

        $cards = [];
        foreach (Nav::pages($sector) as $itemKey => $entry) {
            // Solucoes Ativa tem lugar proprio na barra lateral.
            if (Nav::isEmbeddedSolution($itemKey)) {
                continue;
            }
            $short = self::shortKey($itemKey);
            $count = isset($classes[$itemKey]) ? self::countItems($classes[$itemKey]) : null;
            $add = $entry['links']['add'] ?? '';
            $cards[$itemKey] = [
                'key'   => $short,
                'title' => (string) ($entry['title'] ?? $itemKey),
                'desc'  => self::DESCRIPTIONS[$short] ?? '',
                'icon'  => (string) ($entry['icon'] ?? '') ?: 'ti ti-circle-dot',
                'url'   => self::href((string) $entry['page']),
                'count' => $count['count'] ?? null,
                'unit'  => $count['unit'] ?? '',
                'add'   => is_string($add) && $add !== '' ? self::href($add) : '',
                'cta'   => 'Abrir',
            ];
        }
        if ($cards === []) {
            return null;
        }

        $definition = self::SECTORS[$key] ?? ['subtitle' => '', 'groups' => []];
        $title = match ($key) {
            'helpdesk' => 'Central de chamados',
            'admin'    => 'Usuários e equipes',
            'tools'    => 'Ferramentas e relatórios',
            'config'   => 'Configurações',
            default    => (string) ($sector['title'] ?? $key),
        };

        return [
            'key'      => $key,
            'sector'   => $key,
            'section'  => $key,
            'eyebrow'  => 'Ativa Central · Módulo',
            'title'    => $title,
            // Secoes de plugin nao tem texto proprio: uma frase generica.
            'subtitle' => (string) $definition['subtitle'] !== '' ? (string) $definition['subtitle'] : 'Escolha o que deseja abrir em ' . $title . '.',
            'actions'  => [],
            'groups'   => self::group($cards, $definition['groups']),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $cards
     * @param array<string, list<string>> $groups
     * @return list<array{title: string, cards: list<array<string, mixed>>}>
     */
    private static function group(array $cards, array $groups): array
    {
        $out = [];
        foreach ($groups as $title => $keys) {
            $picked = [];
            foreach ($keys as $wanted) {
                foreach ($cards as $itemKey => $card) {
                    if ($card['key'] === $wanted) {
                        $picked[] = $card;
                        unset($cards[$itemKey]);
                    }
                }
            }
            if ($picked !== []) {
                $out[] = ['title' => (string) $title, 'cards' => $picked];
            }
        }
        if ($cards !== []) {
            // O que o GLPI ou um plugin acrescentou depois continua aparecendo.
            $out[] = ['title' => $out === [] ? '' : 'Outros', 'cards' => array_values($cards)];
        }
        return $out;
    }

    /**
     * "Central de chamados": a fila por situacao antes dos modulos.
     *
     * @param array<string, mixed> $hub
     * @return array<string, mixed>
     */
    private static function withTicketQueue(array $hub): array
    {
        if (!Ticket::canView()) {
            return $hub;
        }
        $tiles = [
            ['Novos', 'Ainda sem atendimento.', 'ti ti-inbox', ['status' => Ticket::INCOMING], [Ticket::INCOMING], false],
            ['Em atendimento', 'Atribuídos ou planejados.', 'ti ti-progress', ['status' => 'process'], [Ticket::ASSIGNED, Ticket::PLANNED], false],
            ['Pendentes', 'Aguardando retorno ou terceiros.', 'ti ti-clock-pause', ['status' => Ticket::WAITING], [Ticket::WAITING], false],
            ['Solucionados', 'Com solução, aguardando aprovação para fechar.', 'ti ti-circle-check', ['status' => Ticket::SOLVED], [Ticket::SOLVED], false],
            // Onde vai parar o chamado finalizado quando a entidade fecha na
            // hora (ou quando se usa "Solucionar e fechar").
            ['Fechados', 'Encerrados de vez.', 'ti ti-archive', ['status' => Ticket::CLOSED], [Ticket::CLOSED], false],
            ['Atribuídos a mim', 'Em aberto sob minha responsabilidade.', 'ti ti-user-check', ['status' => 'notold', 'mine' => true],
                [Ticket::INCOMING, Ticket::ASSIGNED, Ticket::PLANNED, Ticket::WAITING], true],
            ['Todos em aberto', 'Tudo o que ainda não foi solucionado.', 'ti ti-list-check', ['status' => 'notold'],
                [Ticket::INCOMING, Ticket::ASSIGNED, Ticket::PLANNED, Ticket::WAITING], false],
        ];
        $cards = [];
        foreach ($tiles as [$title, $desc, $icon, $filter, $statuses, $mine]) {
            $count = null;
            try {
                $count = Metrics::countTickets($statuses, $mine);
            } catch (Throwable $exception) {
                // Sem a contagem o botao continua valendo.
            }
            $cards[] = [
                'key'   => 'queue',
                'title' => $title,
                'desc'  => $desc,
                'icon'  => $icon,
                'url'   => Shell::glpiUrl(self::ticketSearchUrl($filter)),
                'count' => $count,
                'unit'  => 'chamados',
                'add'   => '',
                'cta'   => 'Ver chamados',
            ];
        }
        array_unshift($hub['groups'], ['title' => 'Fila de chamados', 'cards' => $cards]);
        if (Ticket::canCreate()) {
            $hub['actions'][] = ['label' => 'Novo chamado', 'icon' => 'ti ti-plus', 'url' => Shell::glpiUrl('/front/ticket.form.php'), 'primary' => true];
        }
        return $hub;
    }

    /**
     * "Computadores": o que se faz com as estacoes, reunindo o inventario do
     * GLPI e as solucoes Ativa instaladas.
     *
     * @param array<string, mixed> $menu
     * @return array<string, mixed>|null
     */
    private static function computers(array $menu): ?array
    {
        $entry = $menu['assets']['content']['computer'] ?? null;
        if (!is_array($entry) || !isset($entry['page'])) {
            return null;
        }
        $card = static fn (string $title, string $desc, string $icon, string $url, string $cta, ?array $count = null): array => [
            'key' => 'computers', 'title' => $title, 'desc' => $desc, 'icon' => $icon, 'url' => $url,
            'count' => $count['count'] ?? null, 'unit' => $count['unit'] ?? '', 'add' => '', 'cta' => $cta,
        ];

        $inventory = [
            $card('Estações monitoradas', 'Confira nome, usuário conectado, sistema operacional e último contato.',
                'ti ti-device-desktop', self::href((string) $entry['page']), 'Ver computadores', self::countItems('Computer')),
        ];
        if (isset($menu['assets']['content']['software']['page'])) {
            $inventory[] = $card('Inventário e software', 'Consulte programas instalados, versões e histórico de inventário.',
                'ti ti-apps', self::href((string) $menu['assets']['content']['software']['page']), 'Ver inventário', self::countItems('Software'));
        }
        if (isset($menu['assets']['content']['monitor']['page'])) {
            $inventory[] = $card('Monitores', 'Telas ligadas a cada estação.',
                'ti ti-device-desktop-analytics', self::href((string) $menu['assets']['content']['monitor']['page']), 'Ver monitores', self::countItems('Monitor'));
        }

        $solutions = [];
        foreach ([
            'ativaguardian'  => ['Saúde do ambiente', 'Identifique máquinas sem contato ou com alertas técnicos.', 'ti ti-heart-rate-monitor', 'Ver alertas', ''],
            'ativaprint'     => ['Parque de impressão', 'Acompanhe status, toners e volumetria das impressoras.', 'ti ti-printer', 'Abrir Ativa Print', ''],
            'ativarede'      => ['Planta e rede', 'Veja em que mesa e em qual porta de switch cada máquina está.', 'ti ti-layout-board', 'Abrir planta', 'planta'],
            'ativaremote'    => ['Acesso remoto', 'Conecte-se à tela de um computador para dar suporte.', 'ti ti-screen-share', 'Abrir acesso remoto', ''],
            'ativaupdater'   => ['Atualizações do agente', 'Acompanhe a versão instalada e as falhas de atualização.', 'ti ti-refresh', 'Ver atualizações', ''],
            'ativaworkspace' => ['Workspace', 'Provisionamento, perfis e aplicativos das estações.', 'ti ti-layout-grid', 'Abrir Workspace', ''],
            'ativawallpaper' => ['Papel de parede', 'Imagem de fundo distribuída aos computadores.', 'ti ti-photo', 'Abrir Wallpaper', ''],
        ] as $plugin => [$title, $desc, $icon, $cta, $preferred]) {
            $url = self::solutionUrl($menu, $plugin, $preferred);
            if ($url !== '') {
                $solutions[] = $card($title, $desc, $icon, $url, $cta);
            }
        }

        $actions = [['label' => 'Lista e filtros', 'icon' => 'ti ti-adjustments-horizontal', 'url' => self::href((string) $entry['page']), 'primary' => false]];
        $add = $entry['links']['add'] ?? '';
        if (is_string($add) && $add !== '') {
            $actions[] = ['label' => 'Registrar computador', 'icon' => 'ti ti-plus', 'url' => self::href($add), 'primary' => true];
        }

        $groups = [['title' => 'Inventário', 'cards' => $inventory]];
        if ($solutions !== []) {
            $groups[] = ['title' => 'Operação das estações', 'cards' => $solutions];
        }
        return [
            'key'      => 'computers',
            'sector'   => 'assets',
            'section'  => 'computers',
            'eyebrow'  => 'Ativa Central · Módulo',
            'title'    => 'Computadores',
            'subtitle' => 'Visão operacional das estações, inventário e conectividade.',
            'actions'  => $actions,
            'groups'   => $groups,
        ];
    }

    /**
     * Pagina de uma solucao Ativa (secao propria ou item dentro de outra secao).
     *
     * @param array<string, mixed> $menu
     */
    private static function solutionUrl(array $menu, string $plugin, string $preferred = ''): string
    {
        if (isset($menu[$plugin]) && is_array($menu[$plugin])) {
            $pages = Nav::pages($menu[$plugin]);
            if ($preferred !== '' && isset($pages[$preferred]['page'])) {
                return self::href((string) $pages[$preferred]['page']);
            }
            if ($pages !== []) {
                return self::href((string) reset($pages)['page']);
            }
        }
        foreach (Nav::embeddedSolutions($menu) as $itemKey => $entry) {
            if (str_starts_with($itemKey, 'plugin' . $plugin)) {
                return self::href($entry['url']);
            }
        }
        return '';
    }

    /**
     * Quantidade de itens de um tipo que o usuario pode ver.
     *
     * @return array{count: int, unit: string}|null null quando nao se aplica
     */
    private static function countItems(string $class): ?array
    {
        global $DB;

        try {
            if (!class_exists($class) || !is_a($class, CommonDBTM::class, true)) {
                return null;
            }
            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract() || in_array(strtolower($reflection->getShortName()), self::NO_COUNT, true)) {
                return null;
            }
            if (!$class::canView()) {
                return null;
            }
            if ($class === Ticket::class) {
                return ['count' => Metrics::countTickets([Ticket::INCOMING, Ticket::ASSIGNED, Ticket::PLANNED, Ticket::WAITING], false), 'unit' => 'em aberto'];
            }
            $table = $class::getTable();
            if ($table === '' || !$DB->tableExists($table)) {
                return null;
            }

            $item = new $class();
            $where = [];
            $unit = 'cadastrados';
            if ($item->maybeDeleted()) {
                $where[] = [$table . '.is_deleted' => 0];
            }
            if ($item->maybeTemplate()) {
                $where[] = [$table . '.is_template' => 0];
            }
            if ($item->isEntityAssign()) {
                $entities = getEntitiesRestrictCriteria($table, '', '', $item->maybeRecursive());
                if ($entities !== []) {
                    $where[] = $entities;
                }
            }
            // Tipos que dividem a mesma tabela (ativos personalizados).
            if (method_exists($class, 'getSystemSQLCriteria')) {
                $system = $class::getSystemSQLCriteria();
                if (is_array($system) && $system !== []) {
                    $where[] = $system;
                }
            }
            if ($item instanceof CommonITILObject) {
                $where[] = ['NOT' => [$table . '.status' => array_merge($class::getSolvedStatusArray(), $class::getClosedStatusArray())]];
                $unit = 'em aberto';
            }
            return ['count' => (int) countElementsInTable($table, $where), 'unit' => $unit];
        } catch (Throwable $exception) {
            return null;
        }
    }

    /** "glpi\form\form" -> "form". */
    private static function shortKey(string $itemKey): string
    {
        $position = strrpos($itemKey, '\\');
        return $position === false ? $itemKey : substr($itemKey, $position + 1);
    }

    /** Caminho do menu -> endereco publico (com a raiz do GLPI, sem duplicar). */
    private static function href(string $page): string
    {
        global $CFG_GLPI;

        $root = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/');
        if ($root !== '' && str_starts_with($page, $root . '/')) {
            return $page;
        }
        return $root . (str_starts_with($page, '/') ? '' : '/') . $page;
    }
}
