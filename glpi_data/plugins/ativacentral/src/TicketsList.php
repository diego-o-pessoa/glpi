<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Session;
use Ticket;

/**
 * Tela "Chamados" do Super-Admin: cards por situacao e a fila de chamados
 * abertos (e os finalizados recentes) com filtro por situacao.
 */
final class TicketsList
{
    private const FINISHED_LIMIT = 200;

    public static function url(): string
    {
        return Shell::url('/front/tickets.php');
    }

    /** @return array<string, mixed> */
    public static function data(): array
    {
        global $DB;
        $me = (int) Session::getLoginUserID();
        $where = ['t.is_deleted' => 0];
        $entities = getEntitiesRestrictCriteria('t', '', '', true);
        if ($entities !== []) {
            $where[] = $entities;
        }
        if (!Session::haveRight('ticket', Ticket::READALL)) {
            // Sem "ver todos": so os chamados em que a pessoa (ou o grupo dela) atua.
            $groups = array_map('intval', (array) ($_SESSION['glpigroups'] ?? []));
            $or = ['t.id' => new \Glpi\DBAL\QuerySubQuery(['SELECT' => 'tickets_id', 'FROM' => 'glpi_tickets_users', 'WHERE' => ['users_id' => $me]])];
            if ($groups !== []) {
                $or[] = ['t.id' => new \Glpi\DBAL\QuerySubQuery(['SELECT' => 'tickets_id', 'FROM' => 'glpi_groups_tickets', 'WHERE' => ['groups_id' => $groups]])];
            }
            $where[] = ['OR' => $or];
        }

        $rows = [];
        foreach ([[1, 2, 3, 4], [5, 6]] as $i => $statuses) {
            $req = [
                'SELECT'    => ['t.id', 't.name', 't.status', 't.priority', 't.date', 't.date_mod', 't.solvedate', 't.closedate', 'c.completename AS category'],
                'FROM'      => 'glpi_tickets AS t',
                'LEFT JOIN' => ['glpi_itilcategories AS c' => ['ON' => ['t' => 'itilcategories_id', 'c' => 'id']]],
                'WHERE'     => $where + ['t.status' => $statuses],
                'ORDER'     => $i === 0 ? 't.date DESC' : 't.date_mod DESC',
            ];
            if ($i === 1) {
                $req['LIMIT'] = self::FINISHED_LIMIT;
            }
            foreach ($DB->request($req) as $row) {
                $rows[(int) $row['id']] = $row;
            }
        }
        $ids = array_keys($rows);

        $actors = [];
        $reasons = [];
        if ($ids !== []) {
            foreach ($DB->request(['SELECT' => ['tickets_id', 'users_id', 'type'], 'FROM' => 'glpi_tickets_users', 'WHERE' => ['tickets_id' => $ids]]) as $a) {
                $actors[(int) $a['tickets_id']][(int) $a['type']][] = (int) $a['users_id'];
            }
            foreach ($DB->request([
                'SELECT'     => ['pi.items_id', 'r.name'],
                'FROM'       => 'glpi_pendingreasons_items AS pi',
                'INNER JOIN' => ['glpi_pendingreasons AS r' => ['ON' => ['pi' => 'pendingreasons_id', 'r' => 'id']]],
                'WHERE'      => ['pi.itemtype' => 'Ticket', 'pi.items_id' => $ids],
            ]) as $r) {
                $reasons[(int) $r['items_id']] = (string) $r['name'];
            }
        }

        $names = [];
        $name = static function (int $id) use (&$names): string {
            if (!isset($names[$id])) {
                $names[$id] = trim(preg_replace('/\s+-\s+Ativa\s+Loca\S*o$/iu', '', (string) getUserName($id)) ?? '');
            }
            return $names[$id];
        };

        $tickets = [];
        $kpis = ['new' => 0, 'assigned' => 0, 'waiting' => 0, 'finished' => 0, 'mine' => 0];
        $counts = array_fill_keys(array_keys(Statuses::SITUATIONS), 0);
        foreach ($rows as $id => $row) {
            $status = (int) $row['status'];
            $sit = Statuses::situation($status, $reasons[$id] ?? '');
            $counts[$sit]++;
            $techs = $actors[$id][2] ?? [];
            $mine = in_array($me, $techs, true);
            if ($status === 1) {
                $kpis['new']++;
            } elseif ($status === 2 || $status === 3) {
                $kpis['assigned']++;
            } elseif ($status === 4) {
                $kpis['waiting']++;
            } else {
                $kpis['finished']++;
            }
            if ($mine && $status < 5) {
                $kpis['mine']++;
            }
            $tickets[] = [
                'id'         => $id,
                'name'       => (string) $row['name'],
                'category'   => str_replace(' > ', ' › ', (string) ($row['category'] ?? '')),
                'priority'   => (int) $row['priority'],
                'priority_label' => Ticket::getPriorityName((int) $row['priority']),
                'requester'  => implode(', ', array_map($name, $actors[$id][1] ?? [])),
                'techs'      => implode(', ', array_map($name, $techs)),
                'mine'       => $mine,
                'open'       => $status < 5,
                'situation'  => $sit,
                'sit_label'  => Statuses::SITUATIONS[$sit][0],
                'sit_tone'   => Statuses::SITUATIONS[$sit][1],
                'date'       => \Html::convDateTime((string) $row['date']),
                'age'        => self::age((string) ($status < 5 ? $row['date'] : ($row['closedate'] ?: $row['solvedate'] ?: $row['date_mod']))),
                'url'        => Shell::glpiUrl('/front/ticket.form.php?id=' . $id),
            ];
        }

        $prioCounts = [];
        foreach ($tickets as $t) {
            if ($t['open']) {
                $prioCounts[$t['priority']] = ($prioCounts[$t['priority']] ?? 0) + 1;
            }
        }
        $priorities = [];
        foreach ([6, 5, 4, 3, 2, 1] as $p) {
            if ($p === 6 && empty($prioCounts[6])) {
                continue;
            }
            $priorities[] = ['key' => $p, 'label' => Ticket::getPriorityName($p), 'count' => $prioCounts[$p] ?? 0];
        }

        $situations = [];
        foreach (Statuses::SITUATIONS as $key => [$label, $tone]) {
            $situations[] = ['key' => $key, 'label' => $label, 'tone' => $tone, 'count' => $counts[$key]];
        }

        return [
            'tickets'    => $tickets,
            'kpis'       => $kpis,
            'situations' => $situations,
            'priorities' => $priorities,
            'open_count' => count(array_filter($tickets, static fn (array $t): bool => $t['open'])),
            'new_url'    => Session::haveRight('ticket', CREATE) ? Shell::glpiUrl('/front/ticket.form.php') : '',
            'more_url'   => Shell::glpiUrl(Nav::hubUrl('helpdesk')),
            'search_url' => Shell::glpiUrl('/front/ticket.php?classic=1'),
            'finished_limit' => self::FINISHED_LIMIT,
            'css_url'    => Shell::assetUrl('css/app.css'),
        ];
    }

    /** "há 5 min", "há 3 h", "há 2 dias". */
    private static function age(string $date): string
    {
        $ts = strtotime($date);
        if (!$ts) {
            return '';
        }
        $diff = max(0, time() - $ts);
        if ($diff < 3600) {
            return 'há ' . max(1, intdiv($diff, 60)) . ' min';
        }
        if ($diff < 86400) {
            return 'há ' . intdiv($diff, 3600) . ' h';
        }
        $days = intdiv($diff, 86400);
        return 'há ' . $days . ($days === 1 ? ' dia' : ' dias');
    }
}
