<?php
if (!defined('GLPI_ROOT')) {
    include("../../../inc/includes.php");
}

require_once __DIR__ . '/_common.php';

$ctx = painel_user_context('/plugins/ativacentral/front/projects.php');
$data = painel_user_projects_data($ctx['uid'], $ctx['my_groups']);
$projects = $data['projects'];
$kpis = $data['kpis'];

$total_count = count($projects);
$running_count = count(array_filter($projects, static fn (array $p): bool => $p['bucket'] === 'running'));
$planning_count = count(array_filter($projects, static fn (array $p): bool => $p['bucket'] === 'planning'));
$done_count = count(array_filter($projects, static fn (array $p): bool => $p['bucket'] === 'done'));

$active_val = (int) ($kpis['active']['value'] ?? 0);
$active_prog = (int) ($kpis['active']['progress'] ?? 0);
$running_val = (int) ($kpis['running']['value'] ?? 0);
$running_prog = (int) ($kpis['running']['progress'] ?? 0);
$my_tasks_val = (int) ($kpis['my_tasks']['value'] ?? 0);
$my_tasks_total = (int) ($kpis['my_tasks']['total'] ?? 0);
$my_tasks_prog = (int) ($kpis['my_tasks']['progress'] ?? 0);
$done_val = (int) ($kpis['done']['value'] ?? 0);
$done_prog = (int) ($kpis['done']['progress'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos - Ativa Locação</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #F7F8FA; color: #333; }
        a { text-decoration: none; color: inherit; }

        .topbar { display: flex; justify-content: space-between; align-items: center; background-color: #FFFFFF; padding: 0 40px; height: 70px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .topbar-left { display: flex; align-items: center; }
        .logo { display: flex; align-items: center; font-weight: 800; font-size: 20px; color: #0b457e; margin-right: 40px; }

        .nav-links { display: flex; gap: 30px; }
        .nav-links a { display: flex; align-items: center; padding: 24px 0; color: #555; font-weight: 600; font-size: 14px; border-bottom: 2px solid transparent; transition: color 0.2s; }
        .nav-links a:hover { color: #0b457e; }
        .nav-links a.active { color: #1A1F36; border-bottom: 2px solid #1A1F36; }
        .nav-links a i { margin-right: 8px; }

        .topbar-right { display: flex; align-items: center; }
        .btn-new-ticket { background-color: #0b457e; color: #FFF; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: background 0.2s; margin-right: 20px; }
        .btn-new-ticket:hover { background-color: #08335e; }

        .user-profile { display: flex; align-items: center; position: relative; cursor: pointer; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #E2E4E8; display: flex; align-items: center; justify-content: center; color: #666; margin-right: 12px; font-size: 14px; background-size: cover; background-position: center; overflow: hidden; }
        .user-name { font-size: 14px; color: #333; }

        .dropdown-menu { display: none; position: absolute; top: 45px; right: 0; background-color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-radius: 8px; padding: 10px 0; min-width: 220px; z-index: 100; border: 1px solid #EEE; }
        .dropdown-menu::before { content: ''; position: absolute; top: -20px; left: 0; width: 100%; height: 20px; }
        .user-profile:hover .dropdown-menu { display: block; }
        .dropdown-menu a { display: block; padding: 12px 20px; font-size: 14px; color: #444; font-weight: 500; }
        .dropdown-menu a:hover { background-color: #F7F8FA; color: #0b457e; }
        .dropdown-menu i { margin-right: 12px; width: 16px; text-align: center; color: #888; }

        .container { max-width: 1300px; margin: 36px auto; padding: 0 30px; }
        .header-action { margin-bottom: 24px; }
        .header-action h1 { font-size: 28px; font-weight: 800; color: #1A1F36; letter-spacing: -0.5px; }
        .header-subtitle { margin-top: 6px; color: #7a85a3; font-size: 14px; }

        <?= painel_kpi_styles() ?>

        .proj-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px 16px; margin-bottom: 18px; }
        .proj-pills { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .proj-pill { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border: 1px solid transparent; border-radius: 10px; background: transparent; color: #7a85a3; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .proj-pill:hover { background: rgba(74, 108, 247, 0.08); color: #1a2340; }
        .proj-pill.is-active { border-color: #e9ecf4; background: #ffffff; color: #1a2340; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05); }
        .proj-pill span { color: #7a85a3; font-weight: 500; }

        .proj-search { position: relative; }
        .proj-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #7a85a3; font-size: 14px; pointer-events: none; }
        .proj-search input { width: 250px; height: 40px; padding: 0 14px 0 38px; border: 1px solid #e9ecf4; border-radius: 12px; background: #ffffff; color: #1a2340; font-size: 13.5px; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .proj-search input:focus { border-color: rgba(74, 108, 247, 0.55); box-shadow: 0 0 0 4px rgba(74, 108, 247, 0.12); }

        .proj-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .proj-card { position: relative; display: flex; flex-direction: column; min-height: 210px; padding: 22px 24px 20px; border: 1px solid #e9ecf4; border-radius: 16px; background: #ffffff; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04), 0 12px 30px -20px rgba(16, 24, 40, 0.22); transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.28s, border-color 0.22s; }
        .proj-card:hover { border-color: rgba(74, 108, 247, 0.4); box-shadow: 0 2px 4px rgba(16, 24, 40, 0.05), 0 24px 42px -24px rgba(74, 108, 247, 0.55); transform: translateY(-3px); }
        .proj-card.is-hidden { display: none; }

        .proj-tags { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .proj-tag { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; line-height: 1.2; background: #eef1f8; color: #1a2340; }
        .proj-tag.is-rose { background: rgba(239, 77, 90, 0.12); color: #b93342; }
        .proj-tag.is-red { background: rgba(239, 77, 90, 0.16); color: #b91c1c; }
        .proj-tag.is-orange { background: rgba(245, 159, 50, 0.16); color: #a15c07; }
        .proj-tag.is-blue { background: rgba(74, 108, 247, 0.13); color: #2f4bc7; }
        .proj-tag.is-green { background: rgba(47, 182, 124, 0.14); color: #167a4d; }
        .proj-tag.is-muted { background: #eef1f8; color: #7a85a3; }

        .proj-title { margin-top: 14px; color: #1a2340; font-size: 19px; font-weight: 800; letter-spacing: -0.015em; line-height: 1.25; }
        .proj-desc { flex: 1 1 auto; margin-top: 7px; color: #7a85a3; font-size: 13px; line-height: 1.5; }

        .proj-progress { margin-top: 16px; }
        .proj-progress-label { color: #7a85a3; font-size: 12.5px; font-weight: 600; }
        .proj-progress-label b { color: #1a2340; font-weight: 700; }
        .proj-bar { height: 6px; margin-top: 8px; border-radius: 999px; background: #eef1f8; overflow: hidden; }
        .proj-bar > span { display: block; height: 100%; border-radius: inherit; background: #4a6cf7; }
        .proj-card.is-done .proj-bar > span { background: #2fb67c; }

        .proj-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 18px; padding-top: 14px; border-top: 1px solid #e9ecf4; }
        .proj-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .proj-date { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border: 1px solid #e9ecf4; border-radius: 9px; background: #f8f9fc; color: #1a2340; font-size: 12px; font-weight: 600; }
        .proj-date i { color: #7a85a3; font-size: 13px; }

        .proj-avatars { display: flex; align-items: center; gap: 5px; }
        .proj-avatar { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 999px; background: rgba(74, 108, 247, 0.14); color: #4a6cf7; font-size: 11px; font-weight: 800; }
        .proj-avatar.tone-1 { background: rgba(47, 182, 124, 0.14); color: #2fb67c; }
        .proj-avatar.tone-2 { background: rgba(245, 159, 50, 0.14); color: #f59f32; }
        .proj-avatar.tone-3 { background: rgba(139, 108, 246, 0.14); color: #8b6cf6; }
        .proj-avatar.is-more { background: #eef1f8; color: #7a85a3; position: relative; cursor: default; }
        /* Equipe: grupo com nome inteiro, pessoa abre o nome no hover, "+N" com balao */
        /* Equipe: a bolinha cresce para o lado com o nome e empurra as outras; "+N" com balao */
        .proj-foot, .proj-meta, .proj-avatars { flex-wrap: nowrap; }
        .proj-meta { min-width: 0; }
        .proj-foot .proj-date, .proj-foot .proj-cta { flex-shrink: 0; white-space: nowrap; }
        .proj-foot .proj-cta { transition: opacity 0.2s; }
        .proj-foot:has(.proj-avatar.is-person:hover) .proj-cta { opacity: 0; }
        .proj-avatar.is-person { display: inline-flex; align-items: center; width: auto; min-width: 28px; white-space: nowrap; }
        .proj-avatar .av-short { min-width: 28px; text-align: center; }
        .proj-avatar .av-full { max-width: 0; overflow: hidden; opacity: 0; transition: max-width 0.3s, opacity 0.2s, padding 0.3s; }
        .proj-avatar.is-person:hover .av-short { display: none; }
        .proj-avatar.is-person:hover .av-full { max-width: 160px; text-overflow: ellipsis; padding: 0 12px; opacity: 1; }
        .proj-avatar .av-pop { position: absolute; bottom: calc(100% + 8px); left: 50%; z-index: 30; display: flex; flex-direction: column; gap: 6px; min-width: 180px; padding: 10px 12px; border: 1px solid #e9ecf4; border-radius: 12px; background: #fff; box-shadow: 0 16px 34px -14px rgba(16, 24, 40, 0.35); color: #1a2340; font-size: 12.5px; font-weight: 600; white-space: nowrap; opacity: 0; pointer-events: none; transform: translate(-50%, 4px); transition: opacity 0.18s, transform 0.18s; }
        .proj-avatar .av-pop::after { content: ''; position: absolute; top: 100%; left: 50%; border: 6px solid transparent; border-top-color: #fff; transform: translateX(-50%); }
        .proj-avatar.is-more:hover .av-pop { opacity: 1; transform: translate(-50%, 0); }
        .av-pop-row { display: flex; align-items: center; gap: 8px; }
        .av-pop-ini { display: grid; place-items: center; flex: 0 0 24px; width: 24px; height: 24px; border-radius: 50%; background: rgba(74, 108, 247, 0.14); color: #3c5ce8; font-size: 10px; font-weight: 800; }

        .proj-cta { display: inline-flex; align-items: center; gap: 6px; color: #4a6cf7; font-size: 13px; font-weight: 700; }
        .proj-cta i { transition: transform 0.25s; }
        .proj-card:hover .proj-cta i { transform: translateX(4px); }

        .empty-panel { background: #ffffff; border: 1px solid #e9ecf4; border-radius: 16px; padding: 60px 24px; text-align: center; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04); }
        .empty-panel h3 { font-size: 19px; font-weight: 700; color: #1A1F36; margin-bottom: 10px; }
        .empty-panel p { font-size: 14px; color: #7a85a3; line-height: 1.6; }

        @media (max-width: 900px) {
            .proj-grid { grid-template-columns: minmax(0, 1fr); }
            .proj-search, .proj-search input { width: 100%; }
        }
    </style>
</head>
<body>
    <?php painel_render_topbar('projetos', $ctx, true, '?projects=1&switch_to_admin=1'); ?>

    <div class="container">
        <div class="header-action">
            <h1>Projetos</h1>
            <p class="header-subtitle">Acompanhe os projetos em que você participa e suas entregas.</p>
        </div>

        <div class="ac-kpi-grid">
            <button type="button" class="ac-kpi-card is-blue" data-filter-jump="all">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Seus projetos</span>
                    <span class="ac-kpi-tile"><i class="fa-solid fa-diagram-project"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $total_count ?></div>
                <div class="ac-kpi-note"><?= $active_val ?> <?= $active_val === 1 ? 'ativo no momento' : 'ativos no momento' ?></div>
                <div class="ac-kpi-bar"><span style="width: <?= $active_prog ?>%"></span></div>
            </button>

            <button type="button" class="ac-kpi-card is-orange" data-filter-jump="running">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Em execução</span>
                    <span class="ac-kpi-tile"><i class="fa-solid fa-play"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $running_val ?></div>
                <div class="ac-kpi-note"><?= $running_val > 0 ? 'em andamento agora' : 'nenhum em andamento' ?></div>
                <div class="ac-kpi-bar"><span style="width: <?= $running_prog ?>%"></span></div>
            </button>

            <button type="button" class="ac-kpi-card is-violet" data-filter-jump="all">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Minhas tarefas</span>
                    <span class="ac-kpi-tile"><i class="fa-solid fa-list-check"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $my_tasks_val ?></div>
                <div class="ac-kpi-note"><?= $my_tasks_total > 0 ? 'de ' . $my_tasks_total . ' atribuídas a você' : 'nenhuma tarefa pendente' ?></div>
                <div class="ac-kpi-bar"><span style="width: <?= $my_tasks_prog ?>%"></span></div>
            </button>

            <button type="button" class="ac-kpi-card is-green" data-filter-jump="done">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Concluídos</span>
                    <span class="ac-kpi-tile"><i class="fa-regular fa-circle-check"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $done_val ?></div>
                <div class="ac-kpi-note"><?= $done_val > 0 ? 'entregas finalizadas' : 'nenhum finalizado ainda' ?></div>
                <div class="ac-kpi-bar"><span style="width: <?= $done_prog ?>%"></span></div>
            </button>
        </div>

        <div class="proj-toolbar">
            <div class="proj-pills">
                <button type="button" class="proj-pill is-active" data-filter="all">Todos <span>(<?= $total_count ?>)</span></button>
                <button type="button" class="proj-pill" data-filter="running">Em execução <span>(<?= $running_count ?>)</span></button>
                <button type="button" class="proj-pill" data-filter="planning">Planejamento <span>(<?= $planning_count ?>)</span></button>
                <button type="button" class="proj-pill" data-filter="done">Concluídos <span>(<?= $done_count ?>)</span></button>
            </div>
            <label class="proj-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="proj-search-input" placeholder="Filtrar projetos..." aria-label="Filtrar projetos">
            </label>
        </div>

        <?php if ($total_count === 0): ?>
            <div class="empty-panel">
                <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="max-width: 210px; height: auto; opacity: 0.85; margin-bottom: 18px;">
                <h3>Nenhum projeto vinculado</h3>
                <p>Quando você fizer parte da equipe ou tiver uma tarefa atribuída em algum projeto, ele aparecerá aqui.</p>
            </div>
        <?php else: ?>
            <div class="proj-grid" id="proj-grid">
                <?php foreach ($projects as $p):
                    $is_done = $p['bucket'] === 'done';
                    $state_class = $is_done ? 'is-green' : ($p['bucket'] === 'planning' ? 'is-blue' : 'is-orange');
                    $task_label = $p['tasks_done'] . ' de ' . $p['tasks_total'] . ' ' . ($p['tasks_total'] === 1 ? 'tarefa' : 'tarefas');
                    $date_text = 'Sem prazo definido';
                    if ($p['start_date'] !== '' && $p['end_date'] !== '') {
                        $date_text = $p['start_date'] . ' → ' . $p['end_date'];
                    } elseif ($p['start_date'] !== '') {
                        $date_text = $p['start_date'];
                    } elseif ($p['end_date'] !== '') {
                        $date_text = 'Até ' . $p['end_date'];
                    }
                    $search_blob = mb_strtolower($p['name'] . ' ' . $p['desc'] . ' ' . $p['state']);
                ?>
                <a href="<?= htmlspecialchars($p['url']) ?>"
                   class="proj-card <?= $is_done ? 'is-done' : '' ?>"
                   data-bucket="<?= htmlspecialchars($p['bucket']) ?>"
                   data-search="<?= htmlspecialchars($search_blob) ?>">
                    <div class="proj-tags">
                        <span class="proj-tag <?= htmlspecialchars($p['priority_tone']) ?>"><?= htmlspecialchars($p['priority_label']) ?></span>
                        <span class="proj-tag <?= $state_class ?>"
                              <?= $p['state_color'] !== '' ? 'style="border-left: 3px solid ' . htmlspecialchars($p['state_color']) . ';"' : '' ?>>
                            <?= htmlspecialchars($p['state']) ?>
                        </span>
                    </div>

                    <div class="proj-title"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="proj-desc"><?= htmlspecialchars($p['desc'] !== '' ? $p['desc'] : 'Sem descrição cadastrada para este projeto.') ?></div>

                    <div class="proj-progress">
                        <div class="proj-progress-label">
                            <b><?= (int) $p['percent'] ?>% concluído</b> (<?= htmlspecialchars($task_label) ?>)
                        </div>
                        <div class="proj-bar"><span style="width: <?= (int) $p['percent'] ?>%"></span></div>
                    </div>

                    <div class="proj-foot">
                        <div class="proj-meta">
                            <span class="proj-date"><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($date_text) ?></span>
                            <?php if (!empty($p['team'])): ?>
                                <div class="proj-avatars">
                                    <?php foreach (array_slice($p['team'], 0, 4) as $idx => $member): ?>
<span class="proj-avatar is-person tone-<?= $idx % 4 ?>">
                                            <span class="av-short"><?= htmlspecialchars(painel_initials($member['name'])) ?></span>
                                            <span class="av-full"><?= htmlspecialchars($member['name']) ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($p['team']) > 4): ?>
                                        <span class="proj-avatar is-more">+<?= count($p['team']) - 4 ?>
                                            <span class="av-pop">
                                                <?php foreach (array_slice($p['team'], 4) as $member): ?>
                                                    <span class="av-pop-row">
                                                        <span class="av-pop-ini"><?= ($member['kind'] ?? 'user') === 'group' ? 'G' : htmlspecialchars(painel_initials($member['name'])) ?></span>
                                                        <?= htmlspecialchars($member['name']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="proj-cta">Abrir projeto <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="empty-panel" id="proj-nomatch" style="display: none;">
                <h3>Nenhum projeto encontrado</h3>
                <p>Tente limpar a busca ou escolher outro filtro acima.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pills = document.querySelectorAll('.proj-pill');
        const cards = document.querySelectorAll('#proj-grid .proj-card');
        const search = document.getElementById('proj-search-input');
        const grid = document.getElementById('proj-grid');
        const nomatch = document.getElementById('proj-nomatch');
        let currentFilter = 'all';

        function applyFilters() {
            if (!grid) return;
            const term = (search ? search.value : '').trim().toLowerCase();
            let shown = 0;
            cards.forEach(card => {
                const byBucket = currentFilter === 'all' || card.getAttribute('data-bucket') === currentFilter;
                const byTerm = term === '' || (card.getAttribute('data-search') || '').indexOf(term) !== -1;
                const visible = byBucket && byTerm;
                card.classList.toggle('is-hidden', !visible);
                if (visible) shown++;
            });
            grid.style.display = shown === 0 ? 'none' : '';
            if (nomatch) nomatch.style.display = shown === 0 ? '' : 'none';
        }

        function setFilter(next) {
            currentFilter = next;
            pills.forEach(p => p.classList.toggle('is-active', p.getAttribute('data-filter') === next));
            applyFilters();
        }

        pills.forEach(p => p.addEventListener('click', () => setFilter(p.getAttribute('data-filter'))));
        document.querySelectorAll('[data-filter-jump]').forEach(btn => {
            btn.addEventListener('click', () => setFilter(btn.getAttribute('data-filter-jump')));
        });
        if (search) search.addEventListener('input', applyFilters);
    });
    </script>
</body>
</html>
