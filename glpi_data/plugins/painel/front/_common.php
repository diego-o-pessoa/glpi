<?php

declare(strict_types=1);

/**
 * Helpers compartilhados do portal Self-Service (plugin Painel):
 * - dados do usuario logado e troca para perfil administrativo;
 * - topbar unificada com as abas Chamados e Projetos;
 * - consulta segura de projetos e arvore de tarefas/subtarefas do usuario;
 * - CSS compartilhado dos 4 cards no estilo "Visao Geral".
 */

require_once dirname(__DIR__) . '/inc/progress.php';

if (!function_exists('painel_user_context')) {
    /**
     * @return array{
     *   uid: int,
     *   primeiro_nome: string,
     *   avatar_url: string,
     *   has_super_admin: bool,
     *   super_admin_prof_id: int,
     *   my_groups: list<int>
     * }
     */
    function painel_user_context(?string $switchRedirectQuery = null): array
    {
        global $CFG_GLPI;

        Session::checkLoginUser();

        $uid = (int) Session::getLoginUserID();
        $user = new User();
        $user->getFromDB($uid);

        $firstname = (string) ($user->fields['firstname'] ?? '');
        $realname = (string) ($user->fields['realname'] ?? '');
        $display_name = trim($firstname) !== ''
            ? trim($firstname)
            : (trim($realname) !== '' ? trim($realname) : (string) ($user->fields['name'] ?? 'Usuário'));

        $primeiro_nome = explode(' ', $display_name)[0];
        $primeiro_nome = explode('-', $primeiro_nome)[0];

        $picture = (string) ($user->fields['picture'] ?? '');
        $avatar_url = $picture !== ''
            ? $CFG_GLPI['root_doc'] . '/front/document.send.php?file=_pictures/' . rawurlencode($picture)
            : '';

        $has_super_admin = false;
        $super_admin_prof_id = 0;
        if (isset($_SESSION['glpiprofiles']) && is_array($_SESSION['glpiprofiles'])) {
            foreach ($_SESSION['glpiprofiles'] as $id => $prof) {
                if (stripos((string) ($prof['name'] ?? ''), 'admin') !== false) {
                    $has_super_admin = true;
                    $super_admin_prof_id = (int) $id;
                    break;
                }
            }
            if (!$has_super_admin) {
                foreach ($_SESSION['glpiprofiles'] as $id => $prof) {
                    if (strcasecmp(trim((string) ($prof['name'] ?? '')), 'Ativa - Gestor') === 0) {
                        $has_super_admin = true;
                        $super_admin_prof_id = (int) $id;
                        break;
                    }
                }
            }
        }

        if (isset($_GET['switch_to_admin']) && $has_super_admin) {
            Session::changeProfile($super_admin_prof_id);
            $target = $CFG_GLPI['root_doc'] . '/front/central.php';
            if ($switchRedirectQuery !== null && $switchRedirectQuery !== '' && (Project::canView() || ProjectTask::canView())) {
                $target = $CFG_GLPI['root_doc'] . $switchRedirectQuery;
            }
            header('Location: ' . $target);
            exit();
        }

        $my_groups = array_values(array_filter(array_map('intval', (array) ($_SESSION['glpigroups'] ?? []))));

        return [
            'uid'                 => $uid,
            'primeiro_nome'       => $primeiro_nome,
            'avatar_url'          => $avatar_url,
            'has_super_admin'     => $has_super_admin,
            'super_admin_prof_id' => $super_admin_prof_id,
            'my_groups'           => $my_groups,
        ];
    }
}

if (!function_exists('painel_home_url')) {
    function painel_home_url(): string
    {
        global $CFG_GLPI;
        return rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . '/';
    }
}

if (!function_exists('painel_projects_url')) {
    function painel_projects_url(): string
    {
        global $CFG_GLPI;
        return rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . '/front/helpdesk.public.php?projects=1';
    }
}

if (!function_exists('painel_project_detail_url')) {
    function painel_project_detail_url(int $id): string
    {
        global $CFG_GLPI;
        return rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . '/front/helpdesk.public.php?project_id=' . $id;
    }
}

if (!function_exists('painel_render_topbar')) {
    /**
     * @param 'chamados'|'projetos'|'perfil'|'criar' $active
     * @param array<string, mixed> $ctx
     */
    function painel_render_topbar(string $active, array $ctx, bool $showNewTicket = true, string $switchParam = '?switch_to_admin=1'): void
    {
        global $CFG_GLPI;
        $root = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/');
        $avatar = (string) ($ctx['avatar_url'] ?? '');
        $first = (string) ($ctx['primeiro_nome'] ?? '');
        $hasAdmin = !empty($ctx['has_super_admin']);
        ?>
        <div class="topbar">
            <div class="topbar-left">
                <a href="<?= htmlspecialchars(painel_home_url()) ?>" class="logo">
                    <img src="<?= htmlspecialchars($root) ?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="height: 45px;">
                </a>
                <nav class="nav-links">
                    <a href="<?= htmlspecialchars(painel_home_url()) ?>" class="<?= $active === 'chamados' ? 'active' : '' ?>">
                        <i class="fa-regular fa-window-maximize"></i> Chamados
                    </a>
                    <a href="<?= htmlspecialchars(painel_projects_url()) ?>" class="<?= $active === 'projetos' ? 'active' : '' ?>">
                        <i class="fa-solid fa-diagram-project"></i> Projetos
                    </a>
                </nav>
            </div>
            <div class="topbar-right">
                <?php if ($showNewTicket): ?>
                    <a href="<?= htmlspecialchars($root) ?>/front/helpdesk.public.php?create_ticket=1" class="btn-new-ticket">Abrir Chamado</a>
                <?php endif; ?>
                <div class="user-profile">
                    <div class="avatar" style="<?= $avatar !== '' ? 'background-image: url(' . htmlspecialchars($avatar) . ');' : '' ?>">
                        <?= $avatar !== '' ? '' : '<i class="fa-solid fa-user"></i>' ?>
                    </div>
                    <span class="user-name">Olá, <strong><?= htmlspecialchars($first) ?></strong> <i class="fa-solid fa-caret-down" style="font-size: 10px; margin-left: 5px; color: #888;"></i></span>
                    <div class="dropdown-menu">
                        <?php if ($hasAdmin): ?>
                            <a href="<?= htmlspecialchars($switchParam) ?>"><i class="fa-solid fa-user-shield"></i> Trocar para Admin</a>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($root) ?>/plugins/painel/front/profile.php"><i class="fa-solid fa-gear"></i> Minhas configurações</a>
                        <a href="<?= htmlspecialchars($root) ?>/front/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('painel_kpi_styles')) {
    /**
     * Estilos dos 4 cards KPI (mesmo padrao visual do "Visao Geral" no Ativa Central).
     */
    function painel_kpi_styles(): string
    {
        return <<<'CSS'
        .ac-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }
        .ac-kpi-card {
            position: relative;
            display: block;
            padding: 20px 20px 18px;
            border: 1px solid #e9ecf4;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04), 0 12px 30px -20px rgba(16, 24, 40, 0.22);
            color: #1a2340;
            text-align: left;
            cursor: pointer;
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.28s, border-color 0.22s;
        }
        .ac-kpi-card:hover {
            border-color: rgba(var(--ac-tone-rgb), 0.38);
            box-shadow: 0 2px 4px rgba(16, 24, 40, 0.05), 0 24px 40px -24px rgba(var(--ac-tone-rgb), 0.55);
            transform: translateY(-3px);
        }
        .ac-kpi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .ac-kpi-label {
            padding-top: 4px;
            color: #7a85a3;
            font-size: 13px;
            font-weight: 500;
        }
        .ac-kpi-tile {
            display: grid;
            place-items: center;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: rgba(var(--ac-tone-rgb), 0.13);
            color: var(--ac-tone);
            font-size: 16px;
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .ac-kpi-card:hover .ac-kpi-tile {
            transform: scale(1.08) rotate(-5deg);
        }
        .ac-kpi-value {
            margin-top: 14px;
            color: #1a2340;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .ac-kpi-note {
            min-height: 18px;
            margin-top: 10px;
            color: #7a85a3;
            font-size: 12.5px;
        }
        .ac-kpi-note b {
            color: var(--ac-tone);
            font-weight: 700;
        }
        .ac-kpi-bar {
            height: 4px;
            margin-top: 14px;
            overflow: hidden;
            border-radius: 999px;
            background: #eef1f8;
        }
        .ac-kpi-bar > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--ac-tone);
            transition: width 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .is-blue   { --ac-tone: #4a6cf7; --ac-tone-rgb: 74, 108, 247; }
        .is-orange { --ac-tone: #f59f32; --ac-tone-rgb: 245, 159, 50; }
        .is-green  { --ac-tone: #2fb67c; --ac-tone-rgb: 47, 182, 124; }
        .is-violet { --ac-tone: #8b6cf6; --ac-tone-rgb: 139, 108, 246; }
        @media (max-width: 1100px) {
            .ac-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .ac-kpi-grid { grid-template-columns: minmax(0, 1fr); }
        }
CSS;
    }
}

if (!function_exists('painel_project_states')) {
    /**
     * @return array<int, array{name: string, color: string, is_finished: bool}>
     */
    function painel_project_states(): array
    {
        global $DB;
        $states = [];
        try {
            foreach ($DB->request(['SELECT' => ['id', 'name', 'color', 'is_finished'], 'FROM' => 'glpi_projectstates']) as $row) {
                $color = trim((string) ($row['color'] ?? ''));
                $states[(int) $row['id']] = [
                    'name'        => (string) ($row['name'] ?? ''),
                    'color'       => preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) === 1 ? $color : '',
                    'is_finished' => (int) ($row['is_finished'] ?? 0) === 1,
                ];
            }
        } catch (Throwable $e) {
            // Ignora caso a tabela nao exista.
        }
        return $states;
    }
}

if (!function_exists('painel_project_state_label')) {
    function painel_project_state_label(string $name, bool $finished): string
    {
        $lower = mb_strtolower(trim($name));
        return match (true) {
            $lower !== '' && str_contains($lower, 'cancel') => 'Cancelado',
            $finished => 'Concluído',
            $lower !== '' && preg_match('/execu|andamento|progress/u', $lower) === 1 => 'Em andamento',
            $lower === '' || preg_match('/backlog|planej|n[aã]o inici|novo|aberto|sem estado/u', $lower) === 1 => 'Não iniciado',
            default => trim($name),
        };
    }
}

if (!function_exists('painel_is_planning_state')) {
    function painel_is_planning_state(string $name): bool
    {
        $lower = mb_strtolower(trim($name));
        if ($lower === '') {
            return true;
        }
        return preg_match('/planej|backlog|rascunho|aguard|novo|iniciar|aberto/u', $lower) === 1;
    }
}

if (!function_exists('painel_full_text')) {
    function painel_full_text(string $html): string
    {
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/<(?:br|\/p|\/div|\/li|\/h[1-6]|\/tr)\b[^>]*>/i', "\n", $text);
        $text = preg_replace('/<li\b[^>]*>/i', '• ', (string) $text);
        $text = strip_tags((string) $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+\n/u", "\n", $text);
        $text = preg_replace("/\n{3,}/u", "\n\n", (string) $text);
        return trim((string) $text);
    }
}

if (!function_exists('painel_excerpt')) {
    function painel_excerpt(string $html, int $limit = 135): string
    {
        $text = painel_full_text($html);
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return '';
        }
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $limit - 3)) . '…';
    }
}

if (!function_exists('painel_format_date')) {
    function painel_format_date(string $value): string
    {
        $value = trim($value);
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return '';
        }
        $ts = strtotime($value);
        return $ts === false ? '' : date('d/m/Y', $ts);
    }
}

if (!function_exists('painel_format_datetime')) {
    function painel_format_datetime(string $value): string
    {
        $value = trim($value);
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return '';
        }
        $ts = strtotime($value);
        return $ts === false ? '' : date('d/m/Y H:i', $ts);
    }
}

if (!function_exists('painel_clean_person_name')) {
    function painel_clean_person_name(string $name): string
    {
        $clean = trim((string) preg_replace('/\s*-\s*Ativa\s+Loca[cç][aã]o\b.*$/iu', '', $name));
        return $clean !== '' ? $clean : trim($name);
    }
}

if (!function_exists('painel_user_names_map')) {
    /**
     * @param list<int> $ids
     * @return array<int, string>
     */
    function painel_user_names_map(array $ids): array
    {
        global $DB;
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }
        $out = [];
        foreach ($DB->request(['SELECT' => ['id', 'firstname', 'realname', 'name'], 'FROM' => 'glpi_users', 'WHERE' => ['id' => $ids]]) as $row) {
            $full = trim((string) ($row['firstname'] ?? '') . ' ' . (string) ($row['realname'] ?? ''));
            $raw = $full !== '' ? $full : (string) ($row['name'] ?? '');
            $out[(int) $row['id']] = painel_clean_person_name($raw);
        }
        return $out;
    }
}

if (!function_exists('painel_group_names_map')) {
    /**
     * @param list<int> $ids
     * @return array<int, string>
     */
    function painel_group_names_map(array $ids): array
    {
        global $DB;
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }
        $out = [];
        foreach ($DB->request(['SELECT' => ['id', 'name', 'completename'], 'FROM' => 'glpi_groups', 'WHERE' => ['id' => $ids]]) as $row) {
            $out[(int) $row['id']] = (string) ($row['name'] ?: $row['completename'] ?: '');
        }
        return $out;
    }
}

if (!function_exists('painel_initials')) {
    function painel_initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts));
        if ($parts === []) {
            return '?';
        }
        $first = mb_substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';
        return mb_strtoupper($first . $last);
    }
}

if (!function_exists('painel_user_projects_data')) {
    /**
     * Projetos visiveis para o usuario Self-Service (participante direto, por grupo ou por tarefa),
     * mais contadores para os 4 KPIs.
     *
     * @param list<int> $myGroups
     * @return array{
     *   kpis: array<string, array{value: int, total?: int, progress: int}>,
     *   projects: list<array<string, mixed>>
     * }
     */
    function painel_user_projects_data(int $uid, array $myGroups): array
    {
        global $DB;

        $states = painel_project_states();

        // Garante que todos os grupos do usuario entrem, mesmo se $_SESSION['glpigroups'] estiver desatualizado.
        $groupSet = [];
        foreach ($myGroups as $gid) {
            if ((int) $gid > 0) {
                $groupSet[(int) $gid] = (int) $gid;
            }
        }
        foreach ($DB->request(['SELECT' => ['groups_id'], 'FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $uid]]) as $gu) {
            $gid = (int) ($gu['groups_id'] ?? 0);
            if ($gid > 0) {
                $groupSet[$gid] = $gid;
            }
        }
        $myGroups = array_values($groupSet);

        $where = [
            'glpi_projects.is_deleted'  => 0,
            'glpi_projects.is_template' => 0,
        ];

        // No Self-Service, projetos vinculados diretamente ao usuario ou aos seus grupos
        // aparecem mesmo quando criados na entidade raiz sem "Entidades filhas".
        $linkedIds = [];

        $projOr = [['users_id' => $uid]];
        if ($myGroups !== []) {
            $projOr[] = ['groups_id' => $myGroups];
        }
        foreach ($DB->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_projects',
            'WHERE'  => [$where, ['OR' => $projOr]],
        ]) as $r) {
            $linkedIds[(int) $r['id']] = (int) $r['id'];
        }

        $teamMatch = [['itemtype' => 'User', 'items_id' => $uid]];
        if ($myGroups !== []) {
            $teamMatch[] = ['itemtype' => 'Group', 'items_id' => $myGroups];
        }
        foreach ($DB->request([
            'SELECT' => ['projects_id'],
            'FROM'   => 'glpi_projectteams',
            'WHERE'  => ['OR' => $teamMatch],
        ]) as $r) {
            $pid = (int) ($r['projects_id'] ?? 0);
            if ($pid > 0) {
                $linkedIds[$pid] = $pid;
            }
        }

        $myTeamTaskIds = [];
        foreach ($DB->request([
            'SELECT' => ['projecttasks_id'],
            'FROM'   => 'glpi_projecttaskteams',
            'WHERE'  => ['OR' => $teamMatch],
        ]) as $r) {
            $tid = (int) ($r['projecttasks_id'] ?? 0);
            if ($tid > 0) {
                $myTeamTaskIds[$tid] = $tid;
            }
        }

        $taskOr = [['users_id' => $uid]];
        if ($myTeamTaskIds !== []) {
            $taskOr[] = ['id' => array_values($myTeamTaskIds)];
        }
        foreach ($DB->request([
            'SELECT' => ['projects_id'],
            'FROM'   => 'glpi_projecttasks',
            'WHERE'  => [
                'is_deleted'  => 0,
                'is_template' => 0,
                'OR'          => $taskOr,
            ],
        ]) as $r) {
            $pid = (int) ($r['projects_id'] ?? 0);
            if ($pid > 0) {
                $linkedIds[$pid] = $pid;
            }
        }

        if ($linkedIds === []) {
            return [
                'kpis' => [
                    'active'   => ['value' => 0, 'total' => 0, 'progress' => 0],
                    'running'  => ['value' => 0, 'progress' => 0],
                    'my_tasks' => ['value' => 0, 'total' => 0, 'progress' => 0],
                    'done'     => ['value' => 0, 'progress' => 0],
                ],
                'projects' => [],
            ];
        }

        $rows = [];
        foreach ($DB->request([
            'SELECT' => [
                'id', 'name', 'code', 'priority', 'entities_id', 'projectstates_id',
                'users_id', 'groups_id', 'plan_start_date', 'plan_end_date',
                'percent_done', 'content', 'date_mod',
            ],
            'FROM'   => 'glpi_projects',
            'WHERE'  => [
                'glpi_projects.id'          => array_values($linkedIds),
                'glpi_projects.is_deleted'  => 0,
                'glpi_projects.is_template' => 0,
            ],
            'ORDER'  => ['date_mod DESC', 'id DESC'],
        ]) as $row) {
            $rows[] = $row;
        }

        $projectIds = array_map(static fn (array $r): int => (int) $r['id'], $rows);
        // Andamento automatico: recalcula e grava antes de mostrar.
        $progress = [];
        foreach ($projectIds as $pid) {
            $progress[$pid] = painel_project_recalc($pid);
        }
        $myPendingTasks = 0;
        $myTotalTasks = 0;

        if ($projectIds !== []) {
            $taskTeamMatch = [['itemtype' => 'User', 'items_id' => $uid]];
            if ($myGroups !== []) {
                $taskTeamMatch[] = ['itemtype' => 'Group', 'items_id' => $myGroups];
            }
            $myTaskIds = [];
            foreach ($DB->request([
                'SELECT' => ['projecttasks_id'],
                'FROM'   => 'glpi_projecttaskteams',
                'WHERE'  => ['OR' => $taskTeamMatch],
            ]) as $tt) {
                $myTaskIds[(int) $tt['projecttasks_id']] = true;
            }

            foreach ($DB->request([
                'SELECT' => ['id', 'projects_id', 'percent_done', 'projectstates_id', 'users_id'],
                'FROM'   => 'glpi_projecttasks',
                'WHERE'  => [
                    'projects_id' => $projectIds,
                    'is_deleted'  => 0,
                    'is_template' => 0,
                ],
            ]) as $t) {
                $pid = (int) $t['projects_id'];
                $tid = (int) $t['id'];
                // Conta so tarefas de verdade: sem fases (que tem subtarefas) e sem canceladas.
                $node = $progress[$pid]['nodes'][$tid] ?? null;
                if ($node === null || $node['percent'] === null || isset($progress[$pid]['parents'][$tid])) {
                    continue;
                }
                $finished = $node['kind'] === 'done';

                $isMineTask = ((int) $t['users_id'] === $uid) || isset($myTaskIds[$tid]);
                if ($isMineTask) {
                    $myTotalTasks++;
                    if (!$finished) {
                        $myPendingTasks++;
                    }
                }
            }
        }

        // Equipes dos projetos
        $teams = [];
        if ($projectIds !== []) {
            $userIds = [];
            $groupIds = [];
            $rawMembers = [];
            foreach ($rows as $r) {
                $pid = (int) $r['id'];
                $rawMembers[$pid] = [];
                if ((int) $r['users_id'] > 0) {
                    $u = (int) $r['users_id'];
                    $userIds[$u] = $u;
                    $rawMembers[$pid]['U:' . $u] = ['type' => 'User', 'id' => $u];
                }
                if ((int) $r['groups_id'] > 0) {
                    $g = (int) $r['groups_id'];
                    $groupIds[$g] = $g;
                    $rawMembers[$pid]['G:' . $g] = ['type' => 'Group', 'id' => $g];
                }
            }
            foreach ($DB->request([
                'SELECT' => ['projects_id', 'itemtype', 'items_id'],
                'FROM'   => 'glpi_projectteams',
                'WHERE'  => ['projects_id' => $projectIds],
            ]) as $m) {
                $pid = (int) $m['projects_id'];
                $type = (string) ($m['itemtype'] ?? '');
                $iid = (int) ($m['items_id'] ?? 0);
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
            $uNames = painel_user_names_map(array_values($userIds));
            $gNames = painel_group_names_map(array_values($groupIds));
            foreach ($rawMembers as $pid => $entries) {
                $teams[$pid] = [];
                foreach ($entries as $e) {
                    $name = $e['type'] === 'User' ? ($uNames[$e['id']] ?? '') : ($gNames[$e['id']] ?? '');
                    if ($name !== '') {
                        $teams[$pid][] = ['name' => $name, 'kind' => $e['type'] === 'Group' ? 'group' : 'user'];
                    }
                }
            }
        }

        $projects = [];
        $activeCount = 0;
        $runningCount = 0;
        $doneCount = 0;

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $sid = (int) $row['projectstates_id'];
            $state = $states[$sid] ?? ['name' => 'Sem estado', 'color' => '', 'is_finished' => false];
            $percent = max(0, min(100, (int) ($progress[$id]['percent'] ?? $row['percent_done'])));

            $isDone = $state['is_finished'] || $percent >= 100;
            $bucket = $isDone
                ? 'done'
                : (painel_is_planning_state($state['name']) ? 'planning' : 'running');

            if ($bucket === 'done') {
                $doneCount++;
            } else {
                $activeCount++;
                if ($bucket === 'running') {
                    $runningCount++;
                }
            }

            $priority = (int) $row['priority'];
            // 4 prioridades (Baixa, Normal, Alta, Urgente), como no Super-Admin.
            $priorityTone = match (true) {
                $priority <= 2 => 'is-blue',
                $priority === 3 => 'is-muted',
                $priority === 4 => 'is-orange',
                default => 'is-red',
            };
            $priorityLabel = match (true) {
                $priority <= 2 => 'Baixa',
                $priority === 3 => 'Normal',
                $priority === 4 => 'Alta',
                default => 'Urgente',
            };

            $stats = ['total' => $progress[$id]['tasks_total'] ?? 0, 'done' => $progress[$id]['tasks_done'] ?? 0];
            $projects[] = [
                'id'             => $id,
                'name'           => (string) ($row['name'] ?: 'Projeto #' . $id),
                'desc'           => painel_excerpt((string) ($row['content'] ?? '')),
                'priority_label' => $priorityLabel,
                'priority_tone'  => $priorityTone,
                // Mesmos nomes do Super-Admin: Nao iniciado, Em andamento, Concluido, Cancelado.
                'state'          => painel_project_state_label($state['name'], (bool) $state['is_finished']),
                'state_color'    => $state['color'],
                'bucket'         => $bucket,
                'percent'        => $percent,
                'tasks_total'    => $stats['total'],
                'tasks_done'     => $stats['done'],
                'start_date'     => painel_format_date((string) ($row['plan_start_date'] ?? '')),
                'end_date'       => painel_format_date((string) ($row['plan_end_date'] ?? '')),
                'team'           => $teams[$id] ?? [],
                'url'            => painel_project_detail_url($id),
                'progress_nodes' => $progress[$id]['nodes'] ?? [],
            ];
        }

        $totalProjects = count($projects);
        return [
            'kpis' => [
                'active' => [
                    'value'    => $activeCount,
                    'total'    => $totalProjects,
                    'progress' => $totalProjects > 0 ? (int) round(100 * $activeCount / $totalProjects) : 0,
                ],
                'running' => [
                    'value'    => $runningCount,
                    'progress' => $activeCount > 0 ? (int) round(100 * $runningCount / $activeCount) : 0,
                ],
                'my_tasks' => [
                    'value'    => $myPendingTasks,
                    'total'    => $myTotalTasks,
                    'progress' => $myTotalTasks > 0 ? (int) round(100 * $myPendingTasks / $myTotalTasks) : 0,
                ],
                'done' => [
                    'value'    => $doneCount,
                    'progress' => $totalProjects > 0 ? (int) round(100 * $doneCount / $totalProjects) : 0,
                ],
            ],
            'projects' => $projects,
        ];
    }
}

if (!function_exists('painel_project_detail')) {
    /**
     * Carrega os detalhes de um projeto e sua arvore de tarefas/subtarefas
     * para exibicao no Self-Service (retorna null se nao encontrado ou sem acesso).
     *
     * @param list<int> $myGroups
     * @return array<string, mixed>|null
     */
    function painel_project_detail(int $projectId, int $uid, array $myGroups): ?array
    {
        global $DB;

        if ($projectId <= 0) {
            return null;
        }
        $summary = painel_user_projects_data($uid, $myGroups);
        $project = null;
        foreach ($summary['projects'] as $p) {
            if ((int) $p['id'] === $projectId) {
                $project = $p;
                break;
            }
        }
        if ($project === null) {
            return null;
        }

        $states = painel_project_states();
        $projRow = $DB->request([
            'SELECT' => ['content', 'comment', 'users_id', 'real_start_date', 'real_end_date'],
            'FROM'   => 'glpi_projects',
            'WHERE'  => ['id' => $projectId],
            'LIMIT'  => 1,
        ])->current();
        $project['full_desc'] = painel_full_text((string) ($projRow['content'] ?? ''));
        // Descricao com a formatacao original (cor, negrito...), ja limpa pelo GLPI.
        $raw = trim((string) ($projRow['content'] ?? ''));
        if (str_contains($raw, '&lt;') && !str_contains($raw, '<')) {
            $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        $project['full_html'] = $raw !== '' ? \Glpi\RichText\RichText::getEnhancedHtml($raw) : '';
        $project['comment'] = painel_full_text((string) ($projRow['comment'] ?? ''));
        $project['real_start_date'] = painel_format_date((string) ($projRow['real_start_date'] ?? ''));
        $project['real_end_date'] = painel_format_date((string) ($projRow['real_end_date'] ?? ''));
        $ownerId = (int) ($projRow['users_id'] ?? 0);

        $taskTypes = [];
        try {
            foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_projecttasktypes']) as $row) {
                $taskTypes[(int) $row['id']] = (string) ($row['name'] ?? '');
            }
        } catch (Throwable $e) {
            // Ignora
        }

        $tasksRaw = [];
        $taskIds = [];
        $userIds = $ownerId > 0 ? [$ownerId => $ownerId] : [];
        $groupIds = [];

        foreach ($DB->request([
            'SELECT' => [
                'id', 'name', 'content', 'comment', 'projecttasks_id', 'projectstates_id',
                'projecttasktypes_id', 'users_id', 'percent_done', 'auto_percent_done',
                'plan_start_date', 'plan_end_date', 'real_start_date', 'real_end_date',
            ],
            'FROM'   => 'glpi_projecttasks',
            'WHERE'  => [
                'projects_id' => $projectId,
                'is_deleted'  => 0,
                'is_template' => 0,
            ],
            'ORDER'  => ['id ASC'], // ordem de criacao: as novas ficam por ultimo
        ]) as $t) {
            $tid = (int) $t['id'];
            $taskIds[] = $tid;
            if ((int) $t['users_id'] > 0) {
                $userIds[(int) $t['users_id']] = (int) $t['users_id'];
            }
            $tasksRaw[$tid] = $t;
        }

        $taskTeams = [];
        $notesRaw = [];
        if ($taskIds !== []) {
            foreach ($DB->request([
                'SELECT' => ['projecttasks_id', 'itemtype', 'items_id'],
                'FROM'   => 'glpi_projecttaskteams',
                'WHERE'  => ['projecttasks_id' => $taskIds],
            ]) as $m) {
                $tid = (int) $m['projecttasks_id'];
                $iid = (int) ($m['items_id'] ?? 0);
                $type = (string) ($m['itemtype'] ?? '');
                if ($iid <= 0) {
                    continue;
                }
                if ($type === 'User') {
                    $userIds[$iid] = $iid;
                    $taskTeams[$tid][] = ['type' => 'User', 'id' => $iid];
                } elseif ($type === 'Group') {
                    $groupIds[$iid] = $iid;
                    $taskTeams[$tid][] = ['type' => 'Group', 'id' => $iid];
                }
            }

            foreach ($DB->request([
                'SELECT' => ['id', 'items_id', 'users_id', 'date_creation', 'content'],
                'FROM'   => 'glpi_notepads',
                'WHERE'  => [
                    'itemtype' => 'ProjectTask',
                    'items_id' => $taskIds,
                ],
                'ORDER'  => ['date_creation DESC', 'id DESC'],
            ]) as $n) {
                $tid = (int) $n['items_id'];
                $uidAuthor = (int) ($n['users_id'] ?? 0);
                if ($uidAuthor > 0) {
                    $userIds[$uidAuthor] = $uidAuthor;
                }
                $notesRaw[$tid][] = $n;
            }
        }

        $uNames = painel_user_names_map(array_values($userIds));
        $gNames = painel_group_names_map(array_values($groupIds));
        $project['owner_name'] = $ownerId > 0 ? ($uNames[$ownerId] ?? '') : '';

        $progressNodes = $project['progress_nodes'] ?? [];
        unset($project['progress_nodes']);
        $kindLabels = [
            'todo'      => 'Não iniciada',
            'running'   => 'Em andamento',
            'done'      => 'Concluída',
            'cancelled' => 'Cancelada',
        ];
        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+2 days'));
        $myPending = 0;
        $myNextDue = '';

        $nodes = [];
        foreach ($tasksRaw as $tid => $t) {
            $sid = (int) $t['projectstates_id'];
            $st = $states[$sid] ?? ['name' => 'Sem estado', 'color' => '', 'is_finished' => false];
            $prog = $progressNodes[$tid] ?? null;
            $kind = $prog['kind'] ?? painel_state_kind($st['name'], $st['is_finished']);
            $pct = max(0, min(100, (int) ($prog['percent'] ?? $t['percent_done'])));
            $done = $kind === 'done' || ($kind !== 'cancelled' && $pct >= 100);

            // Prazo: atrasada (vermelho) ou vence em ate 2 dias (laranja).
            $endRaw = substr((string) ($t['plan_end_date'] ?? ''), 0, 10);
            $dueTone = '';
            if ($endRaw !== '' && !$done && $kind !== 'cancelled') {
                if ($endRaw < $today) {
                    $dueTone = 'late';
                } elseif ($endRaw <= $soon) {
                    $dueTone = 'soon';
                }
            }

            $assignees = [];
            $isMine = ((int) $t['users_id'] === $uid);
            // Pessoas = so a equipe marcada na tarefa (o responsavel nao entra), como no Super-Admin.
            foreach ($taskTeams[$tid] ?? [] as $m) {
                if ($m['type'] === 'User') {
                    if ($m['id'] === $uid) {
                        $isMine = true;
                    }
                    $n = $uNames[$m['id']] ?? '';
                    if ($n !== '' && !in_array($n, $assignees, true)) {
                        $assignees[] = $n;
                    }
                } elseif ($m['type'] === 'Group') {
                    if (in_array($m['id'], $myGroups, true)) {
                        $isMine = true;
                    }
                    $n = $gNames[$m['id']] ?? '';
                    if ($n !== '' && !in_array($n, $assignees, true)) {
                        $assignees[] = $n;
                    }
                }
            }

            $notes = [];
            foreach ($notesRaw[$tid] ?? [] as $n) {
                $authorId = (int) ($n['users_id'] ?? 0);
                $authorName = $authorId > 0 ? ($uNames[$authorId] ?? 'Usuário #' . $authorId) : 'Sistema';
                $text = painel_full_text((string) ($n['content'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $notes[] = [
                    'id'              => (int) $n['id'],
                    'author'          => $authorName,
                    'author_initials' => painel_initials($authorName),
                    'date'            => painel_format_datetime((string) ($n['date_creation'] ?? '')),
                    'content'         => $text,
                ];
            }

            // Data em que a tarefa foi concluida (fim real), se ja terminou.
            $doneRaw = $done && !empty($t['real_end_date']) ? (string) $t['real_end_date'] : '';
            $nodes[$tid] = [
                'done_raw'     => $doneRaw,
                'done_date'    => $doneRaw !== '' ? date('d/m/Y', strtotime($doneRaw)) : '',
                'id'           => $tid,
                'parent_id'    => (int) $t['projecttasks_id'],
                'name'         => (string) ($t['name'] ?: 'Tarefa #' . $tid),
                'desc'         => painel_excerpt((string) ($t['content'] ?? ''), 180),
                'full_desc'    => painel_full_text((string) ($t['content'] ?? '')),
                'comment'      => painel_full_text((string) ($t['comment'] ?? '')),
                'type'         => $taskTypes[(int) $t['projecttasktypes_id']] ?? '',
                'state_id'     => $sid,
                'kind'         => $kind,
                'kind_label'   => $kindLabels[$kind] ?? $st['name'],
                'leaf_total'   => (int) ($prog['leaf_total'] ?? 0),
                'leaf_done'    => (int) ($prog['leaf_done'] ?? 0),
                'due_tone'     => $dueTone,
                'end_raw'      => $endRaw,
                'state'        => $st['name'],
                'state_color'  => $st['color'],
                'done'         => $done,
                'percent'      => $pct,
                'auto_percent' => (int) ($t['auto_percent_done'] ?? 0) === 1,
                'start_date'   => painel_format_date((string) ($t['plan_start_date'] ?? '')),
                'end_date'     => painel_format_date((string) ($t['plan_end_date'] ?? '')),
                'real_start'   => painel_format_date((string) ($t['real_start_date'] ?? '')),
                'real_end'     => painel_format_date((string) ($t['real_end_date'] ?? '')),
                'real_start_dt' => !empty($t['real_start_date']) ? date('d/m/Y', strtotime((string) $t['real_start_date'])) . ' às ' . date('H:i', strtotime((string) $t['real_start_date'])) : '',
                'real_end_dt'   => !empty($t['real_end_date']) ? date('d/m/Y', strtotime((string) $t['real_end_date'])) . ' às ' . date('H:i', strtotime((string) $t['real_end_date'])) : '',
                'assignees'    => $assignees,
                'mine'         => $isMine,
                'notes'        => $notes,
                'notes_count'  => count($notes),
                'children'     => [],
            ];
        }

        // Calcula trilha hierarquica de cada tarefa (Pai > Subtarefa)
        foreach ($nodes as $tid => &$node) {
            $trail = [$node['name']];
            $cur = $node['parent_id'];
            $guard = 0;
            while ($cur > 0 && isset($nodes[$cur]) && $guard < 10) {
                array_unshift($trail, $nodes[$cur]['name']);
                $cur = $nodes[$cur]['parent_id'];
                $guard++;
            }
            $node['path'] = implode(' › ', $trail);
        }
        unset($node);

        // Monta arvore hierarquica respeitando projecttasks_id
        $roots = [];
        foreach ($nodes as $tid => &$node) {
            $pid = $node['parent_id'];
            if ($pid > 0 && $pid !== $tid && isset($nodes[$pid])) {
                $nodes[$pid]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        unset($node);

        // Principal concluida: data = a ultima tarefa dela a ser finalizada.
        $phaseDone = static function (array &$node) use (&$phaseDone): string {
            if (empty($node['children'])) {
                return $node['kind'] === 'cancelled' ? '-' : (string) $node['done_raw'];
            }
            $last = '';
            $all = true;
            foreach ($node['children'] as &$child) {
                $d = $phaseDone($child);
                if ($d === '-') {
                    continue;
                }
                if ($d === '') {
                    $all = false;
                } elseif ($d > $last) {
                    $last = $d;
                }
            }
            unset($child);
            if ($all && $last !== '') {
                $node['done_raw'] = $last;
                $node['done_date'] = date('d/m/Y', strtotime($last));
            }
            return $all ? $last : '';
        };
        foreach ($roots as &$root) {
            $phaseDone($root);
        }
        unset($root);

        // Resumo do usuario: tarefas pendentes dele (sem fases e canceladas) e proximo prazo.
        foreach ($nodes as $node) {
            if (!$node['mine'] || !empty($node['children']) || $node['done'] || $node['kind'] === 'cancelled') {
                continue;
            }
            $myPending++;
            if ($node['end_raw'] !== '' && ($myNextDue === '' || $node['end_raw'] < $myNextDue)) {
                $myNextDue = $node['end_raw'];
            }
        }
        $project['my_pending'] = $myPending;
        $project['my_next_due'] = $myNextDue !== '' ? painel_format_date($myNextDue) : '';
        $project['my_next_due_late'] = $myNextDue !== '' && $myNextDue < $today;

        $stateOptions = [];
        foreach ($states as $sid => $st) {
            $stateOptions[] = [
                'id'          => $sid,
                'name'        => $st['name'],
                'color'       => $st['color'],
                'is_finished' => $st['is_finished'],
            ];
        }

        $project['states'] = $stateOptions;
        $project['tasks_map'] = $nodes;
        $project['tree'] = $roots;
        return $project;
    }
}

if (!function_exists('painel_project_client_payload')) {
    /**
     * Monta payload leve de projeto para JSON do frontend (substitui tasks_map por tasks com child_ids).
     *
     * @param array<string, mixed>|null $project
     * @return array<string, mixed>|null
     */
    function painel_project_client_payload(?array $project): ?array
    {
        if ($project === null) {
            return null;
        }
        $tasks = [];
        foreach (($project['tasks_map'] ?? []) as $tid => $t) {
            $childIds = [];
            foreach (($t['children'] ?? []) as $ch) {
                $childIds[] = (int) $ch['id'];
            }
            unset($t['children']);
            $t['child_ids'] = $childIds;
            $tasks[(string) $tid] = $t;
        }
        $out = $project;
        unset($out['tasks_map']);
        $out['tasks'] = $tasks;
        return $out;
    }
}
