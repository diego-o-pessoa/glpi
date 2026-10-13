<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use CommonITILActor;
use CommonITILValidation;
use Computer;
use Glpi\DBAL\QuerySubQuery;
use Session;
use Throwable;
use Ticket;

/**
 * Numeros da "Visao geral": chamados, fila, categorias, desempenho e
 * computadores. So leitura, respeitando a entidade ativa e os direitos de
 * quem esta olhando (sem o direito de ver todos os chamados, entram so os
 * chamados em que a pessoa participa).
 *
 * As contas sao feitas em PHP sobre poucas consultas simples, em vez de SQL
 * com funcoes de data: fica igual em qualquer banco e mais facil de conferir.
 */
final class Metrics
{
    private const OPEN = [Ticket::INCOMING, Ticket::ASSIGNED, Ticket::PLANNED, Ticket::WAITING];
    private const DONE = [Ticket::SOLVED, Ticket::CLOSED];

    /** Prioridade "alta" em diante conta como prioritario. */
    private const PRIORITY_HIGH = 4;
    /** Prioridade "muito alta" em diante aparece como urgente na fila. */
    private const PRIORITY_URGENT = 5;

    private const WEEKDAYS = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

    /** Chamados novos (selo do menu lateral). */
    public static function newTickets(): int
    {
        if (!Ticket::canView()) {
            return 0;
        }
        return (int) countElementsInTable('glpi_tickets', [self::scope(), ['glpi_tickets.status' => Ticket::INCOMING]]);
    }

    /**
     * Chamados visiveis em certas situacoes; $mine = so os atribuidos a mim.
     *
     * @param list<int> $statuses
     */
    public static function countTickets(array $statuses, bool $mine): int
    {
        if (!Ticket::canView()) {
            return 0;
        }
        $where = [self::scope(), ['glpi_tickets.status' => $statuses]];
        if ($mine) {
            $where[] = [
                'glpi_tickets.id' => new QuerySubQuery([
                    'SELECT' => 'tickets_id',
                    'FROM'   => 'glpi_tickets_users',
                    'WHERE'  => ['users_id' => (int) Session::getLoginUserID(), 'type' => CommonITILActor::ASSIGN],
                ]),
            ];
        }
        return (int) countElementsInTable('glpi_tickets', $where);
    }

    /**
     * Chamados que a pessoa pode ver (nao apagados, entidade ativa).
     *
     * @return array<int|string, mixed>
     */
    private static function scope(): array
    {
        $where = ['glpi_tickets.is_deleted' => 0];
        $entities = getEntitiesRestrictCriteria('glpi_tickets');
        $scope = $entities === [] ? [$where] : [$where, $entities];
        if (!Session::haveRight(Ticket::$rightname, Ticket::READALL)) {
            $scope[] = [
                'glpi_tickets.id' => new QuerySubQuery([
                    'SELECT' => 'tickets_id',
                    'FROM'   => 'glpi_tickets_users',
                    'WHERE'  => ['users_id' => (int) Session::getLoginUserID()],
                ]),
            ];
        }
        return $scope;
    }

    /**
     * @return array<string, mixed>
     */
    public static function dashboard(int $days): array
    {
        $days = in_array($days, Settings::PERIODS, true) ? $days : Settings::PERIODS[0];
        $today = strtotime('today');
        $start = strtotime('-' . ($days - 1) . ' days', $today);
        $previous = strtotime('-' . $days . ' days', $start);

        $payload = [
            'days'       => $days,
            'periods'    => Settings::PERIODS,
            'updated'    => date('H:i'),
            'user'       => ['name' => self::firstName()],
            'can'        => ['tickets' => (bool) Ticket::canView(), 'create' => (bool) Ticket::canCreate()],
            'kpis'       => [
                'open'      => ['value' => 0, 'received' => 0, 'delta' => null, 'progress' => 0],
                'waiting'   => ['value' => 0, 'priority' => 0, 'progress' => 0],
                'resolved'  => ['value' => 0, 'delta' => null, 'progress' => 0],
                'computers' => self::computers(),
            ],
            'series'     => self::emptySeries($days, $today),
            'queue'      => [],
            'categories' => [],
            'me'         => ['solved' => 0, 'open' => 0, 'pct' => null, 'rank' => null],
            'team'       => [],
        ];
        if (!Ticket::canView()) {
            unset($payload['series']['start'], $payload['series']['step']);
            return $payload;
        }

        $open = self::openTickets();
        $flow = self::flow($start, $previous, $payload['series']);
        $payload['series'] = $flow['series'];

        $new = count(array_filter($open, static fn (array $t): bool => $t['status'] === Ticket::INCOMING));
        $priority = count(array_filter($open, static fn (array $t): bool => $t['status'] === Ticket::INCOMING && $t['priority'] >= self::PRIORITY_HIGH));
        $total = count($open);

        $payload['kpis']['open'] = [
            'value'    => $total,
            'received' => $flow['received'],
            'delta'    => self::delta($flow['received'], $flow['previous_received']),
            // Barra: quanto da fila ja tem alguem cuidando.
            'progress' => $total > 0 ? (int) round(100 * ($total - $new) / $total) : 0,
        ];
        $payload['kpis']['waiting'] = [
            'value'    => $new,
            'priority' => $priority,
            'progress' => $total > 0 ? (int) round(100 * $new / $total) : 0,
        ];
        $payload['kpis']['resolved'] = [
            'value'    => $flow['resolved'],
            'delta'    => self::delta($flow['resolved'], $flow['previous_resolved']),
            // Barra: resolvidos em relacao aos recebidos no mesmo periodo.
            'progress' => $flow['received'] > 0 ? (int) min(100, round(100 * $flow['resolved'] / $flow['received'])) : ($flow['resolved'] > 0 ? 100 : 0),
        ];

        $payload['queue'] = self::queue($open);
        $payload['categories'] = self::categories($open);
        $performance = self::performance($start);
        $payload['me'] = $performance['me'];
        $payload['team'] = $performance['team'];

        return $payload;
    }

    /**
     * Chamados em aberto (uma consulta, usada pelos cartoes, fila e categorias).
     *
     * @return list<array{id: int, name: string, status: int, priority: int, date: string, category: int}>
     */
    private static function openTickets(): array
    {
        global $DB;

        $rows = [];
        $iterator = $DB->request([
            'SELECT' => ['id', 'name', 'status', 'priority', 'date', 'itilcategories_id'],
            'FROM'   => 'glpi_tickets',
            'WHERE'  => [self::scope(), ['glpi_tickets.status' => self::OPEN]],
        ]);
        foreach ($iterator as $row) {
            $rows[] = [
                'id'       => (int) $row['id'],
                'name'     => (string) $row['name'],
                'status'   => (int) $row['status'],
                'priority' => (int) $row['priority'],
                'date'     => (string) ($row['date'] ?? ''),
                'category' => (int) $row['itilcategories_id'],
            ];
        }
        return $rows;
    }

    /**
     * Recebidos e resolvidos no periodo e no periodo anterior, mais a serie
     * do grafico.
     *
     * @param array{labels: list<string>, received: list<int>, resolved: list<int>, start: int, step: int} $series
     * @return array<string, mixed>
     */
    private static function flow(int $start, int $previous, array $series): array
    {
        global $DB;

        $since = date('Y-m-d H:i:s', min($previous, $series['start']));
        $out = ['received' => 0, 'previous_received' => 0, 'resolved' => 0, 'previous_resolved' => 0];
        $iterator = $DB->request([
            'SELECT' => ['date', 'solvedate', 'status'],
            'FROM'   => 'glpi_tickets',
            'WHERE'  => [self::scope(), ['OR' => [
                ['glpi_tickets.date' => ['>=', $since]],
                ['glpi_tickets.solvedate' => ['>=', $since]],
            ]]],
        ]);
        foreach ($iterator as $row) {
            $created = empty($row['date']) ? false : strtotime((string) $row['date']);
            if ($created !== false) {
                if ($created >= $start) {
                    $out['received']++;
                } elseif ($created >= $previous) {
                    $out['previous_received']++;
                }
                self::bucket($series, 'received', $created);
            }
            $solved = empty($row['solvedate']) ? false : strtotime((string) $row['solvedate']);
            if ($solved !== false && in_array((int) $row['status'], self::DONE, true)) {
                if ($solved >= $start) {
                    $out['resolved']++;
                } elseif ($solved >= $previous) {
                    $out['previous_resolved']++;
                }
                self::bucket($series, 'resolved', $solved);
            }
        }
        unset($series['start'], $series['step']);
        $out['series'] = $series;
        return $out;
    }

    /**
     * Eixo do grafico: um ponto por dia (7 e 30 dias) ou por semana (90 dias).
     *
     * @return array{labels: list<string>, received: list<int>, resolved: list<int>, start: int, step: int}
     */
    private static function emptySeries(int $days, int $today): array
    {
        $step = $days > 30 ? 7 : 1;
        $points = (int) ceil($days / $step);
        $start = strtotime('-' . ($points * $step - 1) . ' days', $today);
        $labels = [];
        for ($i = 0; $i < $points; $i++) {
            $day = strtotime('+' . ($i * $step) . ' days', $start);
            if ($step === 1 && $days <= 7) {
                $labels[] = $i === $points - 1 ? 'Hoje' : self::WEEKDAYS[(int) date('w', $day)];
            } else {
                $labels[] = date('d/m', $day);
            }
        }
        return [
            'labels'   => $labels,
            'received' => array_fill(0, $points, 0),
            'resolved' => array_fill(0, $points, 0),
            'start'    => $start,
            'step'     => $step,
        ];
    }

    /** @param array<string, mixed> $series */
    private static function bucket(array &$series, string $key, int $time): void
    {
        if ($time < $series['start']) {
            return;
        }
        $index = intdiv((int) floor(($time - $series['start']) / DAY_TIMESTAMP), $series['step']);
        if (isset($series[$key][$index])) {
            $series[$key][$index]++;
        }
    }

    /** Variacao em % (uma casa); null quando nao ha base de comparacao. */
    private static function delta(int $current, int $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }
        return round(100 * ($current - $previous) / $previous, 1);
    }

    /**
     * Chamados que pedem atencao: mais prioritarios primeiro, depois os mais antigos.
     *
     * @param list<array<string, mixed>> $open
     * @return list<array<string, mixed>>
     */
    private static function queue(array $open): array
    {
        global $DB;

        usort($open, static fn (array $a, array $b): int => [$b['priority'], $a['date']] <=> [$a['priority'], $b['date']]);
        $top = array_slice($open, 0, 5);
        if ($top === []) {
            return [];
        }
        $ids = array_column($top, 'id');

        $requesters = [];
        $iterator = $DB->request([
            'SELECT'     => ['glpi_tickets_users.tickets_id', 'glpi_users.firstname', 'glpi_users.realname', 'glpi_users.name'],
            'FROM'       => 'glpi_tickets_users',
            'INNER JOIN' => ['glpi_users' => ['ON' => ['glpi_tickets_users' => 'users_id', 'glpi_users' => 'id']]],
            'WHERE'      => ['glpi_tickets_users.tickets_id' => $ids, 'glpi_tickets_users.type' => CommonITILActor::REQUESTER],
            'ORDER'      => 'glpi_tickets_users.id',
        ]);
        foreach ($iterator as $row) {
            $requesters[(int) $row['tickets_id']] ??= self::personName($row);
        }
        $categories = self::categoryNames(array_column($top, 'category'));

        $queue = [];
        foreach ($top as $ticket) {
            $queue[] = [
                'id'        => $ticket['id'],
                'title'     => $ticket['name'] !== '' ? $ticket['name'] : 'Chamado sem título',
                'requester' => $requesters[$ticket['id']] ?? '',
                'category'  => $categories[$ticket['category']] ?? '',
                'badge'     => self::badge($ticket['status'], $ticket['priority']),
                'url'       => Shell::glpiUrl('/front/ticket.form.php?id=' . $ticket['id']),
            ];
        }
        return $queue;
    }

    /** @return array{key: string, label: string} */
    private static function badge(int $status, int $priority): array
    {
        if ($priority >= self::PRIORITY_URGENT) {
            return ['key' => 'urgent', 'label' => 'Urgente'];
        }
        return match ($status) {
            Ticket::INCOMING => ['key' => 'new', 'label' => 'Novo'],
            Ticket::WAITING  => ['key' => 'pending', 'label' => 'Pendente'],
            default          => ['key' => 'progress', 'label' => 'Em andamento'],
        };
    }

    /**
     * Chamados em aberto por categoria (as 6 maiores).
     *
     * @param list<array<string, mixed>> $open
     * @return list<array{name: string, total: int, url: string}>
     */
    private static function categories(array $open): array
    {
        $totals = [];
        foreach ($open as $ticket) {
            $totals[$ticket['category']] = ($totals[$ticket['category']] ?? 0) + 1;
        }
        arsort($totals);
        $totals = array_slice($totals, 0, 6, true);
        $names = self::categoryNames(array_keys($totals));

        $out = [];
        foreach ($totals as $id => $total) {
            $out[] = [
                'name'  => $id > 0 ? ($names[$id] ?? 'Categoria #' . $id) : 'Sem categoria',
                'total' => $total,
                'url'   => Shell::glpiUrl(Hubs::ticketSearchUrl(['status' => 'notold', 'category' => (int) $id])),
            ];
        }
        return $out;
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, string>
     */
    private static function categoryNames(array $ids): array
    {
        global $DB;

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }
        $names = [];
        foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_itilcategories', 'WHERE' => ['id' => $ids]]) as $row) {
            $names[(int) $row['id']] = (string) $row['name'];
        }
        return $names;
    }

    /**
     * Resolvidos no periodo e em aberto, por tecnico.
     *
     * "Resolvido por" = quem escreveu a solucao do chamado ou quem estava
     * atribuido como tecnico (o mesmo chamado nao conta duas vezes para a
     * mesma pessoa). So pelo tecnico atribuido, um chamado solucionado sem
     * ninguem atribuido nao aparecia no desempenho de quem o resolveu.
     *
     * @return array{me: array<string, mixed>, team: list<array<string, mixed>>}
     */
    private static function performance(int $start): array
    {
        global $DB;

        $me = (int) Session::getLoginUserID();
        $stats = [];
        $solvedBy = [];
        $iterator = $DB->request([
            'SELECT'     => ['glpi_tickets_users.users_id', 'glpi_tickets.id AS ticket', 'glpi_tickets.status', 'glpi_tickets.solvedate'],
            'FROM'       => 'glpi_tickets_users',
            'INNER JOIN' => ['glpi_tickets' => ['ON' => ['glpi_tickets_users' => 'tickets_id', 'glpi_tickets' => 'id']]],
            'WHERE'      => [
                self::scope(),
                ['glpi_tickets_users.type' => CommonITILActor::ASSIGN],
                ['OR' => [
                    ['glpi_tickets.status' => self::OPEN],
                    ['glpi_tickets.solvedate' => ['>=', date('Y-m-d H:i:s', $start)]],
                ]],
            ],
        ]);
        foreach ($iterator as $row) {
            $user = (int) $row['users_id'];
            if ($user <= 0) {
                continue;
            }
            $stats[$user] ??= ['solved' => 0, 'open' => 0];
            $status = (int) $row['status'];
            if (in_array($status, self::OPEN, true)) {
                $stats[$user]['open']++;
            } elseif (in_array($status, self::DONE, true) && !empty($row['solvedate']) && strtotime((string) $row['solvedate']) >= $start) {
                $solvedBy[$user][(int) $row['ticket']] = true;
            }
        }

        // Autor da solucao (solucoes recusadas pelo requerente nao contam).
        $iterator = $DB->request([
            'SELECT'     => ['glpi_itilsolutions.users_id', 'glpi_itilsolutions.items_id'],
            'FROM'       => 'glpi_itilsolutions',
            'INNER JOIN' => ['glpi_tickets' => ['ON' => ['glpi_itilsolutions' => 'items_id', 'glpi_tickets' => 'id']]],
            'WHERE'      => [
                self::scope(),
                ['glpi_itilsolutions.itemtype' => 'Ticket'],
                ['NOT' => ['glpi_itilsolutions.status' => CommonITILValidation::REFUSED]],
                ['glpi_tickets.status' => self::DONE],
                ['glpi_tickets.solvedate' => ['>=', date('Y-m-d H:i:s', $start)]],
            ],
        ]);
        foreach ($iterator as $row) {
            $user = (int) $row['users_id'];
            if ($user > 0) {
                $solvedBy[$user][(int) $row['items_id']] = true;
            }
        }
        foreach ($solvedBy as $user => $tickets) {
            $stats[$user] ??= ['solved' => 0, 'open' => 0];
            $stats[$user]['solved'] = count($tickets);
        }

        uasort($stats, static fn (array $a, array $b): int => [$b['solved'], $a['open']] <=> [$a['solved'], $b['open']]);
        $ranked = array_filter($stats, static fn (array $s): bool => $s['solved'] > 0);
        $names = self::userNames(array_unique(array_merge(array_keys(array_slice($ranked, 0, 5, true)), [$me])));

        $team = [];
        foreach (array_slice($ranked, 0, 5, true) as $user => $data) {
            $team[] = [
                'name'   => $names[$user] ?? 'Usuário #' . $user,
                'solved' => $data['solved'],
                'open'   => $data['open'],
                'me'     => $user === $me,
            ];
        }

        $mine = $stats[$me] ?? ['solved' => 0, 'open' => 0];
        $done = $mine['solved'] + $mine['open'];
        $rank = array_search($me, array_keys($ranked), true);

        return [
            'me'   => [
                'solved' => $mine['solved'],
                'open'   => $mine['open'],
                // Do que passou por mim no periodo, quanto ja foi resolvido.
                'pct'    => $done > 0 ? (int) round(100 * $mine['solved'] / $done) : null,
                'rank'   => $rank === false ? null : $rank + 1,
            ],
            'team' => $team,
        ];
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, string>
     */
    private static function userNames(array $ids): array
    {
        global $DB;

        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }
        $names = [];
        foreach ($DB->request(['SELECT' => ['id', 'firstname', 'realname', 'name'], 'FROM' => 'glpi_users', 'WHERE' => ['id' => $ids]]) as $row) {
            $names[(int) $row['id']] = self::personName($row);
        }
        return $names;
    }

    /** @param array<string, mixed> $row */
    private static function personName(array $row): string
    {
        $full = trim((string) ($row['firstname'] ?? '') . ' ' . (string) ($row['realname'] ?? ''));
        return $full !== '' ? $full : (string) ($row['name'] ?? '');
    }

    private static function firstName(): string
    {
        $first = trim((string) ($_SESSION['glpifirstname'] ?? ''));
        if ($first !== '') {
            return explode(' ', $first)[0];
        }
        $login = (string) ($_SESSION['glpiname'] ?? '');
        return ucfirst(explode('.', $login)[0]);
    }

    /**
     * Computadores cadastrados e quantos tem inventario recente.
     *
     * @return array{value: int, fresh: ?int, days: int, visible: bool}
     */
    private static function computers(): array
    {
        $days = Settings::int('inventory_fresh_days', 1, 365);
        $out = ['value' => 0, 'fresh' => null, 'days' => $days, 'visible' => (bool) Computer::canView()];
        if (!$out['visible']) {
            return $out;
        }
        try {
            $base = ['glpi_computers.is_deleted' => 0, 'glpi_computers.is_template' => 0];
            $entities = getEntitiesRestrictCriteria('glpi_computers', '', '', true);
            $scope = $entities === [] ? [$base] : [$base, $entities];
            $total = (int) countElementsInTable('glpi_computers', $scope);
            $out['value'] = $total;
            if ($total > 0) {
                $limit = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
                $fresh = (int) countElementsInTable('glpi_computers', array_merge($scope, [['glpi_computers.last_inventory_update' => ['>=', $limit]]]));
                $out['fresh'] = (int) round(100 * $fresh / $total);
            }
        } catch (Throwable $exception) {
            // Sem a coluna de inventario (versao diferente do GLPI): mostra so o total.
        }
        return $out;
    }
}
