<?php
if (!defined('GLPI_ROOT')) {
    include("../../../inc/includes.php");
}

require_once __DIR__ . '/_common.php';

$project_id = (int) ($_GET['project_id'] ?? $_GET['id'] ?? 0);
$ctx = painel_user_context($project_id > 0 ? '/front/project.form.php?id=' . $project_id : '/plugins/ativacentral/front/projects.php');
$project = painel_project_detail($project_id, $ctx['uid'], $ctx['my_groups']);

/**
 * Renderiza recursivamente a arvore de tarefas e subtarefas com indentacao clara.
 *
 * @param list<array<string, mixed>> $nodes
 */
function painel_render_task_nodes(array $nodes, int $depth = 0): void
{
    foreach ($nodes as $node) {
        $hasChildren = !empty($node['children']);
        $isDone = !empty($node['done']);
        $isMine = !empty($node['mine']);
        $pad = 16 + ($depth * 26);

        $dateStr = '—';
        if ($node['start_date'] !== '' && $node['end_date'] !== '') {
            $dateStr = $node['start_date'] . ' → ' . $node['end_date'];
        } elseif ($node['end_date'] !== '') {
            $dateStr = 'Até ' . $node['end_date'];
        } elseif ($node['start_date'] !== '') {
            $dateStr = $node['start_date'];
        }
        ?>
        <div class="tree-node" data-task-id="<?= (int) $node['id'] ?>" data-mine="<?= $isMine ? '1' : '0' ?>" data-done="<?= $isDone ? '1' : '0' ?>">
            <div class="tree-row <?= $depth === 0 && $hasChildren ? 'is-phase' : '' ?>" style="padding-left: <?= $pad ?>px;">
                <div class="tree-main">
                    <?php if ($hasChildren): ?>
                        <button type="button" class="tree-toggle" aria-expanded="true" title="Expandir/recolher">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    <?php else: ?>
                        <span class="tree-spacer"></span>
                    <?php endif; ?>

                    <span class="tree-icon <?= $isDone ? 'is-done' : ($hasChildren ? 'is-phase' : '') ?>">
                        <i class="fa-solid <?= $isDone ? 'fa-circle-check' : ($hasChildren ? 'fa-folder-open' : 'fa-circle-dot') ?>"></i>
                    </span>

                    <div class="tree-text">
                        <div class="tree-name">
                            <?= htmlspecialchars($node['name']) ?>
                            <?php if ($node['type'] !== ''): ?>
                                <span class="tree-type"><?= htmlspecialchars($node['type']) ?></span>
                            <?php endif; ?>
                            <?php if ($isMine): ?>
                                <span class="tree-mine">Minha</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($node['desc'] !== ''): ?>
                            <div class="tree-desc"><?= htmlspecialchars($node['desc']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tree-cols">
                    <span class="tree-state <?= $isDone ? 'is-done' : '' ?>"
                          <?= $node['state_color'] !== '' ? 'style="border-left: 3px solid ' . htmlspecialchars($node['state_color']) . ';"' : '' ?>>
                        <?= htmlspecialchars($node['state']) ?>
                    </span>

                    <div class="tree-pct" title="<?= (int) $node['percent'] ?>%">
                        <div class="tree-pct-bar"><span style="width: <?= (int) $node['percent'] ?>%"></span></div>
                        <span><?= (int) $node['percent'] ?>%</span>
                    </div>

                    <div class="tree-assignees" title="<?= htmlspecialchars(implode(', ', $node['assignees'])) ?>">
                        <?= !empty($node['assignees']) ? htmlspecialchars(implode(', ', array_slice($node['assignees'], 0, 2))) . (count($node['assignees']) > 2 ? ' +' . (count($node['assignees']) - 2) : '') : '—' ?>
                    </div>

                    <div class="tree-dates"><?= htmlspecialchars($dateStr) ?></div>
                </div>
            </div>

            <?php if ($hasChildren): ?>
                <div class="tree-children">
                    <?php painel_render_task_nodes($node['children'], $depth + 1); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $project ? htmlspecialchars($project['name']) . ' - Projetos' : 'Projeto não encontrado' ?> - Ativa Locação</title>
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

        .container { max-width: 1300px; margin: 32px auto; padding: 0 30px; }

        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #7a85a3; font-size: 13.5px; font-weight: 600; margin-bottom: 16px; }
        .back-link:hover { color: #0b457e; }

        .summary-card { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 24px; padding: 26px 28px; border: 1px solid #e9ecf4; border-radius: 16px; background: #fff; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04), 0 12px 30px -20px rgba(16, 24, 40, 0.22); margin-bottom: 22px; }
        .summary-left { flex: 1 1 520px; }
        .summary-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 10px; }
        .proj-tag { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #eef1f8; color: #1a2340; }
        .proj-tag.is-rose { background: rgba(239, 77, 90, 0.12); color: #b93342; }
        .proj-tag.is-red { background: rgba(239, 77, 90, 0.16); color: #b91c1c; }
        .proj-tag.is-orange { background: rgba(245, 159, 50, 0.16); color: #a15c07; }
        .proj-tag.is-blue { background: rgba(74, 108, 247, 0.13); color: #2f4bc7; }
        .proj-tag.is-green { background: rgba(47, 182, 124, 0.14); color: #167a4d; }
        .proj-tag.is-muted { background: #eef1f8; color: #7a85a3; }

        .summary-left h1 { font-size: 26px; font-weight: 800; color: #1a2340; letter-spacing: -0.02em; }
        .summary-desc { margin-top: 8px; color: #64708f; font-size: 14px; line-height: 1.55; }

        .summary-right { flex: 0 0 320px; display: flex; flex-direction: column; justify-content: center; gap: 12px; }
        .summary-pct { font-size: 18px; font-weight: 800; color: #4a6cf7; }
        .summary-bar { height: 8px; border-radius: 999px; background: #eef1f8; overflow: hidden; }
        .summary-bar > span { display: block; height: 100%; border-radius: inherit; background: #4a6cf7; }
        .summary-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; color: #7a85a3; font-size: 12.5px; font-weight: 600; }

        .tree-card { border: 1px solid #e9ecf4; border-radius: 16px; background: #fff; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04); overflow: hidden; }
        .tree-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 22px; border-bottom: 1px solid #e9ecf4; }
        .tree-head h2 { font-size: 17px; font-weight: 800; color: #1a2340; }
        .tree-filters { display: flex; gap: 8px; }
        .tree-filter { height: 34px; padding: 0 12px; border: 1px solid #e9ecf4; border-radius: 9px; background: #f8f9fc; color: #64708f; font-size: 12.5px; font-weight: 600; cursor: pointer; }
        .tree-filter.is-active { background: #4a6cf7; border-color: #4a6cf7; color: #fff; }

        .tree-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px 14px 16px; border-bottom: 1px solid #f0f2f7; transition: background 0.15s; }
        .tree-row:hover { background: #fafbff; }
        .tree-row.is-phase { background: #f8fafe; font-weight: 700; }
        .tree-node.is-collapsed > .tree-children { display: none; }
        .tree-node.is-collapsed > .tree-row .tree-toggle i { transform: rotate(-90deg); }
        .tree-node.is-hidden { display: none; }

        .tree-main { display: flex; align-items: flex-start; gap: 10px; flex: 1 1 auto; min-width: 0; }
        .tree-toggle { width: 22px; height: 22px; border: 0; border-radius: 6px; background: rgba(74, 108, 247, 0.1); color: #4a6cf7; cursor: pointer; display: grid; place-items: center; flex: 0 0 22px; margin-top: 1px; }
        .tree-toggle i { font-size: 11px; transition: transform 0.2s; }
        .tree-spacer { width: 22px; flex: 0 0 22px; }

        .tree-icon { color: #9aa6bf; font-size: 15px; margin-top: 2px; flex: 0 0 18px; }
        .tree-icon.is-phase { color: #4a6cf7; }
        .tree-icon.is-done { color: #2fb67c; }

        .tree-text { min-width: 0; }
        .tree-name { font-size: 14px; font-weight: 600; color: #1a2340; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .tree-type { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: #eef1f8; color: #64708f; }
        .tree-mine { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: rgba(139, 108, 246, 0.14); color: #7c3aed; }
        .tree-desc { margin-top: 4px; font-size: 12.5px; color: #7a85a3; font-weight: 400; }

        .tree-cols { display: flex; align-items: center; gap: 18px; flex: 0 0 auto; }
        .tree-state { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: #eef1f8; color: #475569; white-space: nowrap; }
        .tree-state.is-done { background: rgba(47, 182, 124, 0.14); color: #167a4d; }

        .tree-pct { display: flex; align-items: center; gap: 8px; width: 105px; font-size: 12.5px; font-weight: 700; color: #1a2340; }
        .tree-pct-bar { flex: 1; height: 6px; border-radius: 999px; background: #eef1f8; overflow: hidden; }
        .tree-pct-bar > span { display: block; height: 100%; background: #4a6cf7; border-radius: inherit; }

        .tree-assignees { width: 170px; font-size: 12.5px; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tree-dates { width: 150px; font-size: 12px; color: #7a85a3; text-align: right; white-space: nowrap; }

        .empty-panel { background: #fff; border: 1px solid #e9ecf4; border-radius: 16px; padding: 56px 24px; text-align: center; }
        .empty-panel h3 { font-size: 19px; color: #1a2340; margin-bottom: 10px; }
        .empty-panel p { font-size: 14px; color: #7a85a3; }

        @media (max-width: 960px) {
            .tree-row { flex-direction: column; align-items: flex-start; }
            .tree-cols { width: 100%; flex-wrap: wrap; justify-content: flex-start; padding-left: 32px; }
            .tree-dates { text-align: left; width: auto; }
        }
    </style>
</head>
<body>
    <?php painel_render_topbar('projetos', $ctx, true, '?project_id=' . $project_id . '&switch_to_admin=1'); ?>

    <div class="container">
        <a href="<?= htmlspecialchars(painel_projects_url()) ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Voltar para Projetos
        </a>

        <?php if ($project === null): ?>
            <div class="empty-panel">
                <h3>Projeto não encontrado</h3>
                <p>Este projeto não existe ou você não tem permissão para acessá-lo.</p>
            </div>
        <?php else:
            $is_done = $project['bucket'] === 'done';
            $state_class = $is_done ? 'is-green' : ($project['bucket'] === 'planning' ? 'is-blue' : 'is-orange');
            $date_text = 'Sem prazo definido';
            if ($project['start_date'] !== '' && $project['end_date'] !== '') {
                $date_text = $project['start_date'] . ' → ' . $project['end_date'];
            } elseif ($project['start_date'] !== '') {
                $date_text = $project['start_date'];
            } elseif ($project['end_date'] !== '') {
                $date_text = 'Até ' . $project['end_date'];
            }
        ?>
            <div class="summary-card">
                <div class="summary-left">
                    <div class="summary-badges">
                        <span class="proj-tag <?= htmlspecialchars($project['priority_tone']) ?>"><?= htmlspecialchars($project['priority_label']) ?></span>
                        <span class="proj-tag <?= $state_class ?>"
                              <?= $project['state_color'] !== '' ? 'style="border-left: 3px solid ' . htmlspecialchars($project['state_color']) . ';"' : '' ?>>
                            <?= htmlspecialchars($project['state']) ?>
                        </span>
                    </div>
                    <h1><?= htmlspecialchars($project['name']) ?></h1>
                    <p class="summary-desc"><?= htmlspecialchars($project['desc'] !== '' ? $project['desc'] : 'Sem descrição cadastrada para este projeto.') ?></p>
                </div>

                <div class="summary-right">
                    <div class="summary-pct"><?= (int) $project['percent'] ?>% concluído</div>
                    <div class="summary-bar">
                        <span style="width: <?= (int) $project['percent'] ?>%; <?= $is_done ? 'background:#2fb67c;' : '' ?>"></span>
                    </div>
                    <div class="summary-meta">
                        <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($date_text) ?></span>
                        <span>· <?= (int) $project['tasks_done'] ?> de <?= (int) $project['tasks_total'] ?> tarefas</span>
                    </div>
                </div>
            </div>

            <div class="tree-card">
                <div class="tree-head">
                    <h2>Estrutura de Tarefas (<?= (int) $project['tasks_total'] ?>)</h2>
                    <div class="tree-filters">
                        <button type="button" class="tree-filter is-active" data-tfilter="all">Todas</button>
                        <button type="button" class="tree-filter" data-tfilter="mine">Minhas tarefas</button>
                        <button type="button" class="tree-filter" data-tfilter="pending">Pendentes</button>
                    </div>
                </div>

                <?php if (empty($project['tree'])): ?>
                    <div class="empty-panel" style="border: 0;">
                        <h3>Nenhuma tarefa cadastrada ainda</h3>
                        <p>As tarefas e fases deste projeto aparecerão aqui assim que forem criadas.</p>
                    </div>
                <?php else: ?>
                    <div id="project-tree-list">
                        <?php painel_render_task_nodes($project['tree']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.tree-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const node = this.closest('.tree-node');
                if (!node) return;
                const collapsed = node.classList.toggle('is-collapsed');
                this.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            });
        });

        const filterBtns = document.querySelectorAll('.tree-filter');
        const rootList = document.getElementById('project-tree-list');

        function matches(node, mode) {
            if (mode === 'all') return true;
            if (mode === 'mine' && node.getAttribute('data-mine') === '1') return true;
            if (mode === 'pending' && node.getAttribute('data-done') === '0') return true;
            const children = node.querySelectorAll(':scope > .tree-children > .tree-node');
            for (let i = 0; i < children.length; i++) {
                if (matches(children[i], mode)) return true;
            }
            return false;
        }

        function applyTaskFilter(mode) {
            if (!rootList) return;
            rootList.querySelectorAll('.tree-node').forEach(node => {
                node.classList.toggle('is-hidden', !matches(node, mode));
            });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const mode = this.getAttribute('data-tfilter');
                filterBtns.forEach(b => b.classList.toggle('is-active', b === this));
                applyTaskFilter(mode);
            });
        });
    });
    </script>
</body>
</html>
