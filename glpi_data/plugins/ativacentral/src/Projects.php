<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use CommonITILObject;
use Glpi\DBAL\QuerySubQuery;
use Project;
use ProjectTask;
use Session;
use Throwable;

/**
 * Dados da nova tela "Projetos" (Etapa 1): indicadores, lista de projetos,
 * contagem de tarefas e equipe, respeitando as entidades e permissoes do GLPI.
 */
final class Projects
{
    /**
     * @return array<string, mixed>
     */
    public static function dashboard(): array
    {
        $canViewProjects = (bool) Project::canView();
        $canViewTasks = (bool) ProjectTask::canView();
        $canCreate = (bool) Session::haveRight(Project::$rightname, CREATE);

        $payload = [
            'updated' => date('H:i'),
            'can'     => [
                'view'   => $canViewProjects || $canViewTasks,
                'create' => $canCreate,
                'tasks'  => $canViewTasks,
            ],
            'kpis'    => [
                'active'   => ['value' => 0, 'total' => 0, 'progress' => 0],
                'running'  => ['value' => 0, 'progress' => 0],
                'my_tasks' => ['value' => 0, 'total' => 0, 'progress' => 0],
                'done'     => ['value' => 0, 'progress' => 0],
            ],
            'projects' => [],
        ];

        if (!$canViewProjects && !$canViewTasks) {
            return $payload;
        }

        $states = self::states();
        $rows = self::visibleProjects();
        $projectIds = array_map(static fn (array $r): int => (int) $r['id'], $rows);

        $taskStats = self::taskStats($projectIds, $states);
        $teams = self::projectTeams($rows);
        $entities = self::entityNames($rows);

        $me = (int) Session::getLoginUserID();
        $myGroups = array_map('intval', (array) ($_SESSION['glpigroups'] ?? []));

        $projects = [];
        $activeCount = 0;
        $runningCount = 0;
        $doneCount = 0;

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $stateId = (int) $row['projectstates_id'];
            $state = $states[$stateId] ?? ['name' => 'Sem estado', 'color' => '', 'is_finished' => false];
            $percent = max(0, min(100, (int) $row['percent_done']));

            $isDone = $state['is_finished'] || $percent >= 100;
            $bucket = $isDone
                ? 'done'
                : (self::isPlanningState($state['name']) ? 'planning' : 'running');

            if ($bucket === 'done') {
                $doneCount++;
            } else {
                $activeCount++;
                if ($bucket === 'running') {
                    $runningCount++;
                }
            }

            $team = $teams[$id] ?? ['members' => [], 'mine' => false];
            $mine = $team['mine']
                || (int) $row['users_id'] === $me
                || ((int) $row['groups_id'] > 0 && in_array((int) $row['groups_id'], $myGroups, true));

            $stats = $taskStats[$id] ?? ['total' => 0, 'done' => 0];
            $priority = (int) $row['priority'];
            $priorityLabel = CommonITILObject::getPriorityName($priority);

            $projects[] = [
                'id'             => $id,
                'name'           => (string) ($row['name'] ?: 'Projeto #' . $id),
                'code'           => (string) ($row['code'] ?? ''),
                'desc'           => self::excerpt((string) ($row['content'] ?? '')),
                'entity'         => $entities[(int) $row['entities_id']] ?? '',
                'priority'       => $priority,
                'priority_label' => $priorityLabel,
                'priority_tone'  => self::priorityTone($priority),
                'state'          => $state['name'],
                'state_color'    => $state['color'],
                'bucket'         => $bucket,
                'percent'        => $percent,
                'tasks_total'    => $stats['total'],
                'tasks_done'     => $stats['done'],
                'start_date'     => self::formatDate((string) ($row['plan_start_date'] ?? '')),
                'end_date'       => self::formatDate((string) ($row['plan_end_date'] ?? '')),
                'team'           => $team['members'],
                'mine'           => $mine,
                'url'            => Shell::glpiUrl('/front/project.form.php?id=' . $id),
            ];
        }

        $totalProjects = count($projects);
        $myTasks = self::myTasksStats($states);

        $payload['projects'] = $projects;
        $payload['kpis'] = [
            'active' => [
                'value'    => $activeCount,
                'total'    => $totalProjects,
                'progress' => $totalProjects > 0 ? (int) round(100 * $activeCount / $totalProjects) : 0,
            ],
            'running' => [
                'value'    => $runningCount,
                'progress' => $activeCount > 0 ? (int) round(100 * $runningCount / $activeCount) : 0,
            ],
            'my_tasks' => $myTasks,
            'done' => [
                'value'    => $doneCount,
                'progress' => $totalProjects > 0 ? (int) round(100 * $doneCount / $totalProjects) : 0,
            ],
        ];

        return $payload;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function visibleProjects(): array
    {
        global $DB;

        $where = [
            'glpi_projects.is_deleted'  => 0,
            'glpi_projects.is_template' => 0,
        ];
        $entities = getEntitiesRestrictCriteria('glpi_projects', '', '', true);
        $scope = $entities === [] ? [$where] : [$where, $entities];

        if (!Session::haveRight(Project::$rightname, Project::READALL)) {
            $me = (int) Session::getLoginUserID();
            $myGroups = array_values(array_filter(array_map('intval', (array) ($_SESSION['glpigroups'] ?? []))));
            $actorWhere = [];

            if (Session::haveRight(Project::$rightname, Project::READMY)) {
                $actorWhere[] = ['glpi_projects.users_id' => $me];
                if ($myGroups !== []) {
                    $actorWhere[] = ['glpi_projects.groups_id' => $myGroups];
                }
                $teamMatch = [['itemtype' => 'User', 'items_id' => $me]];
                if ($myGroups !== []) {
                    $teamMatch[] = ['itemtype' => 'Group', 'items_id' => $myGroups];
                }
                $actorWhere[] = [
                    'glpi_projects.id' => new QuerySubQuery([
                        'SELECT' => 'projects_id',
                        'FROM'   => 'glpi_projectteams',
                        'WHERE'  => ['OR' => $teamMatch],
                    ]),
                ];
            }

            if (ProjectTask::canView()) {
                $taskTeamMatch = [['itemtype' => 'User', 'items_id' => $me]];
                if ($myGroups !== []) {
                    $taskTeamMatch[] = ['itemtype' => 'Group', 'items_id' => $myGroups];
                }
                $actorWhere[] = [
                    'glpi_projects.id' => new QuerySubQuery([
                        'SELECT' => 'projects_id',
                        'FROM'   => 'glpi_projecttasks',
                        'WHERE'  => [
                            'is_deleted'  => 0,
                            'is_template' => 0,
                            'OR'          => [
                                ['users_id' => $me],
                                [
                                    'id' => new QuerySubQuery([
                                        'SELECT' => 'projecttasks_id',
                                        'FROM'   => 'glpi_projecttaskteams',
                                        'WHERE'  => ['OR' => $taskTeamMatch],
                                    ]),
                                ],
                            ],
                        ],
                    ]),
                ];
            }

            if ($actorWhere === []) {
                return [];
            }
            $scope[] = ['OR' => $actorWhere];
        }

        $rows = [];
        $iterator = $DB->request([
            'SELECT' => [
                'id', 'name', 'code', 'priority', 'entities_id', 'projectstates_id',
                'users_id', 'groups_id', 'plan_start_date', 'plan_end_date',
                'percent_done', 'content', 'date', 'date_mod',
            ],
            'FROM'   => 'glpi_projects',
            'WHERE'  => $scope,
            'ORDER'  => ['date_mod DESC', 'id DESC'],
        ]);
        foreach ($iterator as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @return array<int, array{name: string, color: string, is_finished: bool}>
     */
    private static function states(): array
    {
        global $DB;

        $states = [];
        try {
            foreach ($DB->request(['SELECT' => ['id', 'name', 'color', 'is_finished'], 'FROM' => 'glpi_projectstates']) as $row) {
                $states[(int) $row['id']] = [
                    'name'        => (string) ($row['name'] ?? ''),
                    'color'       => self::safeColor((string) ($row['color'] ?? '')),
                    'is_finished' => (int) ($row['is_finished'] ?? 0) === 1,
                ];
            }
        } catch (Throwable $exception) {
            // Sem tabela de estados: segue com padrao.
        }
        return $states;
    }

    /**
     * Contagem total e de tarefas concluidas por projeto.
     *
     * @param list<int> $projectIds
     * @param array<int, array{name: string, color: string, is_finished: bool}> $states
     * @return array<int, array{total: int, done: int}>
     */
    private static function taskStats(array $projectIds, array $states): array
    {
        global $DB;

        if ($projectIds === []) {
            return [];
        }
        // Mesma regra do Self-Service (plugin Painel): contam so as tarefas
        // sem subtarefas e que nao foram canceladas.
        $tasks = [];
        $parents = [];
        $iterator = $DB->request([
            'SELECT' => ['id', 'projecttasks_id', 'projects_id', 'percent_done', 'projectstates_id'],
            'FROM'   => 'glpi_projecttasks',
            'WHERE'  => [
                'projects_id' => $projectIds,
                'is_deleted'  => 0,
                'is_template' => 0,
            ],
        ]);
        foreach ($iterator as $row) {
            $tasks[] = $row;
            if ((int) $row['projecttasks_id'] > 0) {
                $parents[(int) $row['projecttasks_id']] = true;
            }
        }
        $stats = [];
        foreach ($tasks as $row) {
            $sid = (int) $row['projectstates_id'];
            if (isset($parents[(int) $row['id']]) || preg_match('/cancel/i', $states[$sid]['name'] ?? '') === 1) {
                continue;
            }
            $pid = (int) $row['projects_id'];
            $stats[$pid] ??= ['total' => 0, 'done' => 0];
            $stats[$pid]['total']++;
            $finished = !empty($states[$sid]['is_finished']) || (int) $row['percent_done'] >= 100;
            if ($finished) {
                $stats[$pid]['done']++;
            }
        }
        return $stats;
    }

    /**
     * Tarefas em aberto atribuídas ao usuário (ou seus grupos).
     *
     * @param array<int, array{name: string, color: string, is_finished: bool}> $states
     * @return array{value: int, total: int, progress: int}
     */
    private static function myTasksStats(array $states): array
    {
        global $DB;

        if (!ProjectTask::canView()) {
            return ['value' => 0, 'total' => 0, 'progress' => 0];
        }

        $me = (int) Session::getLoginUserID();
        $myGroups = array_values(array_filter(array_map('intval', (array) ($_SESSION['glpigroups'] ?? []))));
        $teamMatch = [['itemtype' => 'User', 'items_id' => $me]];
        if ($myGroups !== []) {
            $teamMatch[] = ['itemtype' => 'Group', 'items_id' => $myGroups];
        }

        $where = [
            'glpi_projecttasks.is_deleted'  => 0,
            'glpi_projecttasks.is_template' => 0,
            'OR' => [
                ['glpi_projecttasks.users_id' => $me],
                [
                    'glpi_projecttasks.id' => new QuerySubQuery([
                        'SELECT' => 'projecttasks_id',
                        'FROM'   => 'glpi_projecttaskteams',
                        'WHERE'  => ['OR' => $teamMatch],
                    ]),
                ],
            ],
        ];
        $entities = getEntitiesRestrictCriteria('glpi_projecttasks', '', '', true);
        if ($entities !== []) {
            $where = [$where, $entities];
        }

        $total = 0;
        $open = 0;
        $iterator = $DB->request([
            'SELECT' => ['percent_done', 'projectstates_id'],
            'FROM'   => 'glpi_projecttasks',
            'WHERE'  => $where,
        ]);
        foreach ($iterator as $row) {
            $total++;
            $sid = (int) $row['projectstates_id'];
            $finished = !empty($states[$sid]['is_finished']) || (int) $row['percent_done'] >= 100;
            if (!$finished) {
                $open++;
            }
        }

        return [
            'value'    => $open,
            'total'    => $total,
            'progress' => $total > 0 ? (int) round(100 * $open / $total) : 0,
        ];
    }

    /**
     * Membros da equipe de cada projeto (inclui gerente se ainda nao estiver na equipe).
     *
     * @param list<array<string, mixed>> $rows
     * @return array<int, array{members: list<array{name: string, kind: string}>, mine: bool}>
     */
    private static function projectTeams(array $rows): array
    {
        global $DB;

        if ($rows === []) {
            return [];
        }

        $projectIds = [];
        $userIds = [];
        $groupIds = [];
        $rawMembers = [];

        foreach ($rows as $row) {
            $pid = (int) $row['id'];
            $projectIds[] = $pid;
            $rawMembers[$pid] = [];
            if ((int) $row['users_id'] > 0) {
                $uid = (int) $row['users_id'];
                $userIds[$uid] = $uid;
                $rawMembers[$pid]['U:' . $uid] = ['type' => 'User', 'id' => $uid];
            }
            if ((int) $row['groups_id'] > 0) {
                $gid = (int) $row['groups_id'];
                $groupIds[$gid] = $gid;
                $rawMembers[$pid]['G:' . $gid] = ['type' => 'Group', 'id' => $gid];
            }
        }

        $iterator = $DB->request([
            'SELECT' => ['projects_id', 'itemtype', 'items_id'],
            'FROM'   => 'glpi_projectteams',
            'WHERE'  => ['projects_id' => $projectIds],
        ]);
        foreach ($iterator as $row) {
            $pid = (int) $row['projects_id'];
            $type = (string) ($row['itemtype'] ?? '');
            $iid = (int) ($row['items_id'] ?? 0);
            if ($iid <= 0) {
                continue;
            }
            if ($type === 'User') {
                $userIds[$iid] = $iid;
                $rawMembers[$pid]['U:' . $iid] = ['type' => 'User', 'id' => $iid];
            } elseif ($type === 'Group') {
                $groupIds[$iid] = $iid;
                $rawMembers[$pid]['G:' . $iid] = ['type' => 'Group', 'id' => $iid];
            }
        }

        $userNames = self::userNames(array_values($userIds));
        $groupNames = self::groupNames(array_values($groupIds));
        $me = (int) Session::getLoginUserID();
        $myGroups = array_map('intval', (array) ($_SESSION['glpigroups'] ?? []));

        $out = [];
        foreach ($rawMembers as $pid => $items) {
            $members = [];
            $mine = false;
            foreach ($items as $entry) {
                if ($entry['type'] === 'User') {
                    if ($entry['id'] === $me) {
                        $mine = true;
                    }
                    $name = $userNames[$entry['id']] ?? '';
                    if ($name !== '') {
                        $members[] = ['name' => $name, 'kind' => 'user'];
                    }
                } elseif ($entry['type'] === 'Group') {
                    if (in_array($entry['id'], $myGroups, true)) {
                        $mine = true;
                    }
                    $name = $groupNames[$entry['id']] ?? '';
                    if ($name !== '') {
                        $members[] = ['name' => $name, 'kind' => 'group'];
                    }
                }
            }
            $out[$pid] = ['members' => $members, 'mine' => $mine];
        }

        return $out;
    }

    /**
     * @param list<int> $ids
     * @return array<int, string>
     */
    private static function userNames(array $ids): array
    {
        global $DB;

        if ($ids === []) {
            return [];
        }
        $names = [];
        foreach ($DB->request(['SELECT' => ['id', 'firstname', 'realname', 'name'], 'FROM' => 'glpi_users', 'WHERE' => ['id' => $ids]]) as $row) {
            $full = trim((string) ($row['firstname'] ?? '') . ' ' . (string) ($row['realname'] ?? ''));
            $raw = $full !== '' ? $full : (string) ($row['name'] ?? '');
            $clean = trim((string) preg_replace('/\s*-\s*Ativa\s+Loca[cç][aã]o\b.*$/iu', '', $raw));
            $names[(int) $row['id']] = $clean !== '' ? $clean : $raw;
        }
        return $names;
    }

    /**
     * @param list<int> $ids
     * @return array<int, string>
     */
    private static function groupNames(array $ids): array
    {
        global $DB;

        if ($ids === []) {
            return [];
        }
        $names = [];
        foreach ($DB->request(['SELECT' => ['id', 'name', 'completename'], 'FROM' => 'glpi_groups', 'WHERE' => ['id' => $ids]]) as $row) {
            $names[(int) $row['id']] = (string) ($row['name'] ?: $row['completename'] ?: '');
        }
        return $names;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<int, string>
     */
    private static function entityNames(array $rows): array
    {
        global $DB;

        $ids = array_values(array_unique(array_map(static fn (array $r): int => (int) $r['entities_id'], $rows)));
        if ($ids === []) {
            return [];
        }
        $names = [];
        foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_entities', 'WHERE' => ['id' => $ids]]) as $row) {
            $names[(int) $row['id']] = (string) ($row['name'] ?? '');
        }
        return $names;
    }

    private static function isPlanningState(string $stateName): bool
    {
        $lower = mb_strtolower(trim($stateName));
        if ($lower === '') {
            return true;
        }
        return preg_match('/planej|backlog|rascunho|aguard|novo|iniciar|aberto/u', $lower) === 1;
    }

    private static function priorityTone(int $priority): string
    {
        return match (true) {
            $priority >= 5 => 'is-red',
            $priority === 4 => 'is-orange',
            $priority === 3 => 'is-rose',
            $priority === 2 => 'is-blue',
            default => 'is-muted',
        };
    }

    private static function safeColor(string $color): string
    {
        $color = trim($color);
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) === 1 ? $color : '';
    }

    private static function excerpt(string $html): string
    {
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return '';
        }
        if (mb_strlen($text) <= 135) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, 132)) . '…';
    }

    private static function formatDate(string $value): string
    {
        $value = trim($value);
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return '';
        }
        $ts = strtotime($value);
        return $ts === false ? '' : date('d/m/Y', $ts);
    }
}
