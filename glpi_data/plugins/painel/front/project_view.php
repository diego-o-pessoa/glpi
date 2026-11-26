<?php
if (!defined('GLPI_ROOT')) {
    include("../../../inc/includes.php");
}

require_once __DIR__ . '/_common.php';

$project_id = (int) ($_GET['project_id'] ?? $_GET['id'] ?? 0);
$ctx = painel_user_context($project_id > 0 ? '/front/project.form.php?id=' . $project_id : '/plugins/ativacentral/front/projects.php');
$project = painel_project_detail($project_id, $ctx['uid'], $ctx['my_groups']);
$csrf_token = Session::getNewCSRFToken();
$action_url = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . '/plugins/painel/front/project_task_action.php';

/**
 * Renderiza recursivamente a arvore de tarefas e subtarefas com linhas clicaveis.
 *
 * @param list<array<string, mixed>> $nodes
 */
function painel_render_task_nodes(array $nodes, int $depth = 0): void
{
    foreach ($nodes as $node) {
        $hasChildren = !empty($node['children']);
        $isDone = !empty($node['done']);
        $isMine = !empty($node['mine']);
        $notesCount = (int) ($node['notes_count'] ?? 0);
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
            <div class="tree-row <?= $depth === 0 && $hasChildren ? 'is-phase' : '' ?>" style="padding-left: <?= $pad ?>px;" role="button" tabindex="0">
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
                            <span><?= htmlspecialchars($node['name']) ?></span>
                            <?php if ($node['type'] !== ''): ?>
                                <span class="tree-type"><?= htmlspecialchars($node['type']) ?></span>
                            <?php endif; ?>
                            <?php if ($isMine): ?>
                                <span class="tree-mine">Minha</span>
                            <?php endif; ?>
                            <span class="tree-notes-pill <?= $notesCount === 0 ? 'is-empty' : '' ?>" data-notes-badge>
                                <i class="fa-regular fa-comment-dots"></i>
                                <span data-notes-count><?= $notesCount ?></span> <?= $notesCount === 1 ? 'atualização' : 'atualizações' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="tree-cols">
                    <span class="tree-state <?= $isDone ? 'is-done' : '' ?>" data-row-state
                          <?= $node['state_color'] !== '' ? 'style="border-left: 3px solid ' . htmlspecialchars($node['state_color']) . ';"' : '' ?>>
                        <?= htmlspecialchars($node['state']) ?>
                    </span>

                    <div class="tree-pct" title="<?= (int) $node['percent'] ?>%">
                        <div class="tree-pct-bar"><span data-row-bar style="width: <?= (int) $node['percent'] ?>%"></span></div>
                        <span data-row-pct><?= (int) $node['percent'] ?>%</span>
                    </div>

                    <div class="tree-assignees" title="<?= htmlspecialchars(implode(', ', $node['assignees'])) ?>">
                        <?= !empty($node['assignees']) ? htmlspecialchars(implode(', ', array_slice($node['assignees'], 0, 2))) . (count($node['assignees']) > 2 ? ' +' . (count($node['assignees']) - 2) : '') : '—' ?>
                    </div>

                    <div class="tree-dates"><?= htmlspecialchars($dateStr) ?></div>
                    <span class="tree-open-cue">Ver detalhes <i class="fa-solid fa-chevron-right"></i></span>
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

$client_payload = painel_project_client_payload($project);
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

        .container { max-width: 1320px; margin: 32px auto; padding: 0 30px 48px; }

        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #7a85a3; font-size: 13.5px; font-weight: 600; margin-bottom: 16px; }
        .back-link:hover { color: #0b457e; }

        .summary-card { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 24px; padding: 26px 28px; border: 1px solid #e9ecf4; border-radius: 16px; background: #fff; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04), 0 12px 30px -20px rgba(16, 24, 40, 0.22); margin-bottom: 22px; }
        .summary-left { flex: 1 1 560px; min-width: 0; }
        .summary-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 10px; }
        .proj-tag { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #eef1f8; color: #1a2340; }
        .proj-tag.is-rose { background: rgba(239, 77, 90, 0.12); color: #b93342; }
        .proj-tag.is-red { background: rgba(239, 77, 90, 0.16); color: #b91c1c; }
        .proj-tag.is-orange { background: rgba(245, 159, 50, 0.16); color: #a15c07; }
        .proj-tag.is-blue { background: rgba(74, 108, 247, 0.13); color: #2f4bc7; }
        .proj-tag.is-green { background: rgba(47, 182, 124, 0.14); color: #167a4d; }
        .proj-tag.is-muted { background: #eef1f8; color: #7a85a3; }

        .summary-left h1 { font-size: 26px; font-weight: 800; color: #1a2340; letter-spacing: -0.02em; }
        .summary-desc { margin-top: 10px; color: #52607e; font-size: 14px; line-height: 1.6; white-space: pre-line; }
        .summary-desc.is-clamped { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .summary-more-row { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-top: 10px; }
        .btn-read-more { display: inline-flex; align-items: center; gap: 6px; border: 0; background: rgba(74, 108, 247, 0.1); color: #4a6cf7; padding: 6px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background 0.15s; }
        .btn-read-more:hover { background: rgba(74, 108, 247, 0.18); }

        .summary-right { flex: 0 0 330px; display: flex; flex-direction: column; justify-content: center; gap: 12px; }
        .summary-pct { font-size: 18px; font-weight: 800; color: #4a6cf7; }
        .summary-bar { height: 8px; border-radius: 999px; background: #eef1f8; overflow: hidden; }
        .summary-bar > span { display: block; height: 100%; border-radius: inherit; background: #4a6cf7; transition: width 0.35s ease; }
        .summary-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; color: #7a85a3; font-size: 12.5px; font-weight: 600; }
        .summary-team { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 2px; }
        .team-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px 4px 4px; border: 1px solid #e9ecf4; border-radius: 999px; background: #f8f9fc; font-size: 12px; font-weight: 600; color: #1a2340; }
        .team-chip-av { width: 22px; height: 22px; border-radius: 50%; background: rgba(74, 108, 247, 0.14); color: #4a6cf7; font-size: 10px; font-weight: 800; display: grid; place-items: center; }

        .tree-card { border: 1px solid #e9ecf4; border-radius: 16px; background: #fff; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04); overflow: hidden; }
        .tree-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 22px; border-bottom: 1px solid #e9ecf4; }
        .tree-head-left h2 { font-size: 17px; font-weight: 800; color: #1a2340; }
        .tree-head-sub { font-size: 12.5px; color: #7a85a3; margin-top: 3px; }
        .tree-filters { display: flex; gap: 8px; }
        .tree-filter { height: 34px; padding: 0 12px; border: 1px solid #e9ecf4; border-radius: 9px; background: #f8f9fc; color: #64708f; font-size: 12.5px; font-weight: 600; cursor: pointer; }
        .tree-filter.is-active { background: #4a6cf7; border-color: #4a6cf7; color: #fff; }

        .tree-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px 14px 16px; border-bottom: 1px solid #f0f2f7; cursor: pointer; transition: background 0.15s; }
        .tree-row:hover { background: #f5f8ff; }
        .tree-row.is-phase { background: #f8fafe; font-weight: 700; }
        .tree-row.is-phase:hover { background: #eff4ff; }
        .tree-node.is-collapsed > .tree-children { display: none; }
        .tree-node.is-collapsed > .tree-row .tree-toggle i { transform: rotate(-90deg); }
        .tree-node.is-hidden { display: none; }

        .tree-main { display: flex; align-items: center; gap: 10px; flex: 1 1 auto; min-width: 0; }
        .tree-toggle { width: 22px; height: 22px; border: 0; border-radius: 6px; background: rgba(74, 108, 247, 0.1); color: #4a6cf7; cursor: pointer; display: grid; place-items: center; flex: 0 0 22px; }
        .tree-toggle i { font-size: 11px; transition: transform 0.2s; }
        .tree-spacer { width: 22px; flex: 0 0 22px; }

        .tree-icon { color: #9aa6bf; font-size: 15px; flex: 0 0 18px; }
        .tree-icon.is-phase { color: #4a6cf7; }
        .tree-icon.is-done { color: #2fb67c; }

        .tree-text { min-width: 0; }
        .tree-name { font-size: 14px; font-weight: 600; color: #1a2340; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .tree-type { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: #eef1f8; color: #64708f; }
        .tree-mine { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: rgba(139, 108, 246, 0.14); color: #7c3aed; }
        .tree-notes-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: rgba(74, 108, 247, 0.1); color: #3c5ce8; }
        .tree-notes-pill.is-empty { display: none; }

        .tree-cols { display: flex; align-items: center; gap: 16px; flex: 0 0 auto; }
        .tree-state { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: #eef1f8; color: #475569; white-space: nowrap; }
        .tree-state.is-done { background: rgba(47, 182, 124, 0.14); color: #167a4d; }

        .tree-pct { display: flex; align-items: center; gap: 8px; width: 100px; font-size: 12.5px; font-weight: 700; color: #1a2340; }
        .tree-pct-bar { flex: 1; height: 6px; border-radius: 999px; background: #eef1f8; overflow: hidden; }
        .tree-pct-bar > span { display: block; height: 100%; background: #4a6cf7; border-radius: inherit; transition: width 0.25s; }

        .tree-assignees { width: 165px; font-size: 12.5px; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tree-dates { width: 145px; font-size: 12px; color: #7a85a3; text-align: right; white-space: nowrap; }
        .tree-open-cue { font-size: 12px; font-weight: 700; color: #4a6cf7; opacity: 0; transition: opacity 0.15s; white-space: nowrap; }
        .tree-row:hover .tree-open-cue { opacity: 1; }

        /* Modal Ler Mais */
        .ac-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); z-index: 1000; justify-content: center; align-items: center; padding: 20px; }
        .ac-modal-backdrop.is-open { display: flex; }
        .ac-modal-card { width: 680px; max-width: 100%; max-height: 88vh; overflow-y: auto; background: #fff; border-radius: 18px; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25); padding: 28px 30px; }
        .ac-modal-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
        .ac-modal-head h2 { font-size: 22px; font-weight: 800; color: #1a2340; margin-top: 8px; }
        .ac-close-btn { width: 34px; height: 34px; border: 0; border-radius: 10px; background: #f1f5f9; color: #475569; font-size: 16px; cursor: pointer; flex: 0 0 34px; }
        .ac-close-btn:hover { background: #e2e8f0; color: #1a2340; }
        .ac-modal-section { margin-top: 18px; padding-top: 16px; border-top: 1px solid #eef1f8; }
        .ac-modal-label { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #7a85a3; margin-bottom: 8px; }
        .ac-modal-text { font-size: 14px; color: #334155; line-height: 1.65; white-space: pre-line; }

        /* Gaveta Lateral da Tarefa */
        .drawer-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.42); z-index: 1050; justify-content: flex-end; }
        .drawer-backdrop.is-open { display: flex; }
        .drawer-panel { width: 560px; max-width: 100vw; height: 100%; background: #fff; box-shadow: -12px 0 40px rgba(15, 23, 42, 0.18); display: flex; flex-direction: column; animation: slideIn 0.22s ease; }
        @keyframes slideIn { from { transform: translateX(30px); opacity: 0.85; } to { transform: translateX(0); opacity: 1; } }
        .drawer-head { padding: 20px 24px 16px; border-bottom: 1px solid #e9ecf4; display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; }
        .drawer-trail { font-size: 12px; font-weight: 600; color: #7a85a3; margin-bottom: 6px; }
        .drawer-title { font-size: 20px; font-weight: 800; color: #1a2340; line-height: 1.25; }
        .drawer-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .drawer-body { flex: 1; overflow-y: auto; padding: 20px 24px 28px; display: flex; flex-direction: column; gap: 22px; }

        .drawer-box { background: #f6f8fe; border: 1px solid #e5ebf9; border-radius: 14px; padding: 16px 18px; }
        .drawer-box-title { font-size: 12px; font-weight: 800; color: #3c5ce8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .drawer-box-text { font-size: 13.5px; color: #334155; line-height: 1.6; white-space: pre-line; }
        .drawer-meta-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .drawer-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #334155; }
        .drawer-chip i { color: #64748b; }

        .drawer-sec-title { font-size: 14px; font-weight: 800; color: #1a2340; margin-bottom: 10px; }
        .status-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .status-pill-btn { padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; color: #475569; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all 0.15s; }
        .status-pill-btn:hover { border-color: #4a6cf7; color: #1a2340; }
        .status-pill-btn.is-active { background: #eef2ff; border-color: #4a6cf7; color: #2f4bc7; }
        .status-pill-btn.is-active.is-finished { background: #ecfdf5; border-color: #2fb67c; color: #167a4d; }

        .pct-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .pct-row input[type="range"] { flex: 1; accent-color: #4a6cf7; cursor: pointer; }
        .pct-badge { min-width: 48px; text-align: right; font-weight: 800; font-size: 14px; color: #4a6cf7; }
        .pct-presets-wrap { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
        .pct-presets { display: flex; gap: 6px; flex-wrap: wrap; }
        .pct-btn { padding: 6px 11px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 12px; font-weight: 700; cursor: pointer; }
        .pct-btn.is-active { background: #4a6cf7; border-color: #4a6cf7; color: #fff; }
        .btn-save-progress { border: 0; border-radius: 10px; background: #16a34a; color: #fff; padding: 9px 16px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-save-progress:hover { background: #15803d; }
        .btn-save-progress:disabled { opacity: 0.6; cursor: not-allowed; }

        .subtasks-mini { display: flex; flex-direction: column; gap: 8px; }
        .subtask-mini-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border: 1px solid #e9ecf4; border-radius: 10px; background: #fafbff; cursor: pointer; font-size: 13px; font-weight: 600; color: #1a2340; }
        .subtask-mini-item:hover { border-color: #4a6cf7; }

        .notes-timeline { display: flex; flex-direction: column; gap: 12px; margin-bottom: 14px; }
        .note-item { display: flex; gap: 10px; align-items: flex-start; }
        .note-av { width: 30px; height: 30px; border-radius: 50%; background: rgba(74, 108, 247, 0.14); color: #4a6cf7; font-size: 11px; font-weight: 800; display: grid; place-items: center; flex: 0 0 30px; }
        .note-body { flex: 1; background: #f8fafc; border: 1px solid #e9ecf4; border-radius: 12px; padding: 10px 12px; }
        .note-meta { font-size: 12px; color: #64748b; margin-bottom: 4px; }
        .note-meta strong { color: #1a2340; }
        .note-text { font-size: 13px; color: #334155; line-height: 1.5; white-space: pre-line; }

        .note-form textarea { width: 100%; min-height: 82px; padding: 11px 13px; border: 1px solid #dce2ee; border-radius: 10px; font-size: 13.5px; resize: vertical; outline: none; }
        .note-form textarea:focus { border-color: #4a6cf7; box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.12); }
        .note-form-foot { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 8px; }
        .btn-send-note { border: 0; border-radius: 10px; background: #4a6cf7; color: #fff; padding: 9px 16px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-left: auto; }
        .btn-send-note:hover { background: #3c5ce8; }
        .btn-send-note:disabled { opacity: 0.6; cursor: not-allowed; }
        .drawer-feedback { font-size: 12.5px; font-weight: 600; }
        .drawer-feedback.is-ok { color: #15803d; }
        .drawer-feedback.is-err { color: #dc2626; }

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
            $full_desc = $project['full_desc'] !== '' ? $project['full_desc'] : 'Sem descrição cadastrada para este projeto.';
            $needs_read_more = mb_strlen($full_desc) > 170 || substr_count($full_desc, "\n") >= 2 || !empty($project['team']) || $project['owner_name'] !== '';
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
                    <div class="summary-desc <?= mb_strlen($full_desc) > 190 || substr_count($full_desc, "\n") >= 3 ? 'is-clamped' : '' ?>"><?= htmlspecialchars($full_desc) ?></div>
                    <?php if ($needs_read_more): ?>
                        <div class="summary-more-row">
                            <button type="button" class="btn-read-more" id="open-project-modal">
                                <i class="fa-regular fa-file-lines"></i> Ler mais e ver informações completas
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="summary-right">
                    <div class="summary-pct" id="summary-pct-text"><?= (int) $project['percent'] ?>% concluído</div>
                    <div class="summary-bar">
                        <span id="summary-pct-bar" style="width: <?= (int) $project['percent'] ?>%; <?= $is_done ? 'background:#2fb67c;' : '' ?>"></span>
                    </div>
                    <div class="summary-meta">
                        <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($date_text) ?></span>
                        <span id="summary-tasks-count">· <?= (int) $project['tasks_done'] ?> de <?= (int) $project['tasks_total'] ?> tarefas</span>
                    </div>
                    <?php if (!empty($project['team'])): ?>
                        <div class="summary-team">
                            <?php foreach (array_slice($project['team'], 0, 4) as $m): ?>
                                <span class="team-chip">
                                    <span class="team-chip-av"><?= htmlspecialchars(painel_initials($m['name'])) ?></span>
                                    <?= htmlspecialchars($m['name']) ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($project['team']) > 4): ?>
                                <span class="team-chip">+<?= count($project['team']) - 4 ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tree-card">
                <div class="tree-head">
                    <div class="tree-head-left">
                        <h2>Estrutura de Tarefas (<?= (int) $project['tasks_total'] ?>)</h2>
                        <div class="tree-head-sub">Clique em qualquer tarefa para ver detalhes, atualizar seu progresso e registrar o que já desenvolveu.</div>
                    </div>
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

    <?php if ($project !== null): ?>
    <!-- Modal Ler Mais: Informações completas do projeto -->
    <div class="ac-modal-backdrop" id="project-info-modal" aria-hidden="true">
        <div class="ac-modal-card" role="dialog" aria-modal="true" aria-label="Informações do projeto">
            <div class="ac-modal-head">
                <div>
                    <div class="summary-badges" style="margin-bottom: 4px;">
                        <span class="proj-tag <?= htmlspecialchars($project['priority_tone']) ?>"><?= htmlspecialchars($project['priority_label']) ?></span>
                        <span class="proj-tag <?= $state_class ?>"><?= htmlspecialchars($project['state']) ?></span>
                    </div>
                    <h2><?= htmlspecialchars($project['name']) ?></h2>
                </div>
                <button type="button" class="ac-close-btn" id="close-project-modal" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="ac-modal-section">
                <div class="ac-modal-label">Descrição completa do projeto</div>
                <div class="ac-modal-text"><?= htmlspecialchars($project['full_desc'] !== '' ? $project['full_desc'] : 'Sem descrição cadastrada.') ?></div>
            </div>

            <?php if ($project['comment'] !== ''): ?>
            <div class="ac-modal-section">
                <div class="ac-modal-label">Observações</div>
                <div class="ac-modal-text"><?= htmlspecialchars($project['comment']) ?></div>
            </div>
            <?php endif; ?>

            <div class="ac-modal-section">
                <div class="ac-modal-label">Responsáveis e prazos</div>
                <div class="drawer-meta-chips" style="margin-top: 0;">
                    <?php if ($project['owner_name'] !== ''): ?>
                        <span class="drawer-chip"><i class="fa-solid fa-user-tie"></i> Dono: <?= htmlspecialchars($project['owner_name']) ?></span>
                    <?php endif; ?>
                    <span class="drawer-chip"><i class="fa-regular fa-calendar"></i> Prazo: <?= htmlspecialchars($date_text) ?></span>
                    <?php if ($project['real_start_date'] !== '' || $project['real_end_date'] !== ''): ?>
                        <span class="drawer-chip"><i class="fa-solid fa-clock-rotate-left"></i> Real: <?= htmlspecialchars(($project['real_start_date'] ?: '—') . ' → ' . ($project['real_end_date'] ?: 'em aberto')) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($project['team'])): ?>
                    <div class="summary-team" style="margin-top: 12px;">
                        <?php foreach ($project['team'] as $m): ?>
                            <span class="team-chip">
                                <span class="team-chip-av"><?= htmlspecialchars(painel_initials($m['name'])) ?></span>
                                <?= htmlspecialchars($m['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gaveta Lateral da Tarefa -->
    <div class="drawer-backdrop" id="task-drawer" aria-hidden="true">
        <div class="drawer-panel" role="dialog" aria-modal="true" aria-label="Detalhes da tarefa">
            <div class="drawer-head">
                <div>
                    <div class="drawer-trail" id="drawer-trail"></div>
                    <div class="drawer-title" id="drawer-title"></div>
                    <div class="drawer-badges" id="drawer-badges"></div>
                </div>
                <button type="button" class="ac-close-btn" id="close-task-drawer" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="drawer-body">
                <div class="drawer-box">
                    <div class="drawer-box-title">Detalhes definidos pelo dono do projeto</div>
                    <div class="drawer-box-text" id="drawer-desc"></div>
                    <div class="drawer-box-text" id="drawer-comment" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #d5dff5; display: none;"></div>
                    <div class="drawer-meta-chips" id="drawer-meta"></div>
                </div>

                <div id="drawer-subtasks-wrap" style="display: none;">
                    <div class="drawer-sec-title">Subtarefas desta etapa</div>
                    <div class="subtasks-mini" id="drawer-subtasks"></div>
                </div>

                <div class="drawer-readonly-hint" id="drawer-readonly-hint" style="display: none; font-size: 13px; color: #7a85a3; background: #f4f6fb; border-radius: 10px; padding: 10px 12px;">
                    <i class="fa-solid fa-lock"></i> Esta tarefa é de outra pessoa: você pode acompanhar, mas só quem está atribuído a ela atualiza o andamento.
                </div>

                <div id="drawer-progress-wrap">
                    <div class="drawer-sec-title">Andamento desta etapa</div>
                    <div class="status-pills" id="drawer-status-pills"></div>
                    <div class="pct-row">
                        <input type="range" min="0" max="100" step="5" id="drawer-pct-range">
                        <span class="pct-badge" id="drawer-pct-label">0%</span>
                    </div>
                    <div class="pct-presets-wrap">
                        <div class="pct-presets" id="drawer-pct-presets">
                            <button type="button" class="pct-btn" data-pct="0">0%</button>
                            <button type="button" class="pct-btn" data-pct="25">25%</button>
                            <button type="button" class="pct-btn" data-pct="50">50%</button>
                            <button type="button" class="pct-btn" data-pct="75">75%</button>
                            <button type="button" class="pct-btn" data-pct="100">100%</button>
                        </div>
                        <button type="button" class="btn-save-progress" id="btn-save-progress">
                            <i class="fa-solid fa-check"></i> Salvar andamento
                        </button>
                    </div>
                    <div class="drawer-feedback" id="progress-feedback" style="margin-top: 8px;"></div>
                    <div id="auto-pct-hint" style="display: none; margin-top: 6px; font-size: 12px; color: #7a85a3;">
                        O percentual desta fase é calculado automaticamente a partir das subtarefas.
                    </div>
                </div>

                <div>
                    <div class="drawer-sec-title">O que já foi desenvolvido (Atualizações)</div>
                    <div class="notes-timeline" id="drawer-notes-list"></div>
                    <form class="note-form" id="drawer-note-form">
                        <textarea id="drawer-note-input" placeholder="Descreva o que você já desenvolveu nesta etapa para o dono do projeto acompanhar..."></textarea>
                        <div class="note-form-foot">
                            <span class="drawer-feedback" id="note-feedback"></span>
                            <button type="submit" class="btn-send-note" id="btn-send-note">
                                <i class="fa-regular fa-paper-plane"></i> Enviar atualização
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="project-payload"><?= json_encode($client_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.tree-toggle').forEach(btn => {
            btn.addEventListener('click', function(event) {
                event.stopPropagation();
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

        // Modal Ler Mais
        const infoModal = document.getElementById('project-info-modal');
        const openInfo = document.getElementById('open-project-modal');
        const closeInfo = document.getElementById('close-project-modal');
        if (infoModal && openInfo && closeInfo) {
            openInfo.addEventListener('click', () => infoModal.classList.add('is-open'));
            closeInfo.addEventListener('click', () => infoModal.classList.remove('is-open'));
            infoModal.addEventListener('click', (e) => {
                if (e.target === infoModal) infoModal.classList.remove('is-open');
            });
        }

        // Gaveta Lateral da Tarefa
        const payloadEl = document.getElementById('project-payload');
        const drawer = document.getElementById('task-drawer');
        if (!payloadEl || !drawer) return;

        let projectData = JSON.parse(payloadEl.textContent || '{}');
        let csrfToken = <?= json_encode($csrf_token) ?>;
        const actionUrl = <?= json_encode($action_url) ?>;
        let currentTaskId = 0;
        let selectedStateId = 0;
        let selectedPercent = 0;

        const trailEl = document.getElementById('drawer-trail');
        const titleEl = document.getElementById('drawer-title');
        const badgesEl = document.getElementById('drawer-badges');
        const descEl = document.getElementById('drawer-desc');
        const commentEl = document.getElementById('drawer-comment');
        const metaEl = document.getElementById('drawer-meta');
        const subWrap = document.getElementById('drawer-subtasks-wrap');
        const subList = document.getElementById('drawer-subtasks');
        const statusPillsEl = document.getElementById('drawer-status-pills');
        const pctRange = document.getElementById('drawer-pct-range');
        const pctLabel = document.getElementById('drawer-pct-label');
        const pctPresets = document.getElementById('drawer-pct-presets');
        const saveProgressBtn = document.getElementById('btn-save-progress');
        const progressFeedback = document.getElementById('progress-feedback');
        const autoPctHint = document.getElementById('auto-pct-hint');
        const notesList = document.getElementById('drawer-notes-list');
        const noteForm = document.getElementById('drawer-note-form');
        const noteInput = document.getElementById('drawer-note-input');
        const sendNoteBtn = document.getElementById('btn-send-note');
        const noteFeedback = document.getElementById('note-feedback');

        function setPctUI(pct) {
            selectedPercent = Math.max(0, Math.min(100, Number(pct) || 0));
            pctRange.value = String(selectedPercent);
            pctLabel.textContent = selectedPercent + '%';
            pctPresets.querySelectorAll('.pct-btn').forEach(btn => {
                btn.classList.toggle('is-active', Number(btn.getAttribute('data-pct')) === selectedPercent);
            });
        }

        function setStateUI(sid) {
            selectedStateId = Number(sid) || 0;
            statusPillsEl.querySelectorAll('.status-pill-btn').forEach(btn => {
                btn.classList.toggle('is-active', Number(btn.getAttribute('data-sid')) === selectedStateId);
            });
        }

        function renderStateButtons(task) {
            statusPillsEl.innerHTML = '';
            const states = projectData.states || [];
            if (!states.length) {
                const none = document.createElement('span');
                none.style.fontSize = '12.5px';
                none.style.color = '#7a85a3';
                none.textContent = 'Nenhum estado cadastrado em projectstates.';
                statusPillsEl.appendChild(none);
                return;
            }
            states.forEach(st => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'status-pill-btn' + (st.is_finished ? ' is-finished' : '');
                btn.setAttribute('data-sid', String(st.id));
                btn.textContent = st.name;
                btn.addEventListener('click', () => {
                    setStateUI(st.id);
                    if (st.is_finished && selectedPercent < 100 && !task.auto_percent) {
                        setPctUI(100);
                    }
                });
                statusPillsEl.appendChild(btn);
            });
            setStateUI(task.state_id);
        }

        function renderNotes(task) {
            notesList.innerHTML = '';
            const notes = task.notes || [];
            if (!notes.length) {
                const empty = document.createElement('div');
                empty.style.fontSize = '13px';
                empty.style.color = '#7a85a3';
                empty.style.padding = '8px 0';
                empty.textContent = 'Nenhuma atualização registrada ainda nesta etapa.';
                notesList.appendChild(empty);
                return;
            }
            notes.forEach(n => {
                const item = document.createElement('div');
                item.className = 'note-item';

                const av = document.createElement('span');
                av.className = 'note-av';
                av.textContent = n.author_initials || '?';
                item.appendChild(av);

                const body = document.createElement('div');
                body.className = 'note-body';

                const meta = document.createElement('div');
                meta.className = 'note-meta';
                const strong = document.createElement('strong');
                strong.textContent = n.author;
                meta.appendChild(strong);
                meta.appendChild(document.createTextNode(n.date ? ' · ' + n.date : ''));
                body.appendChild(meta);

                const text = document.createElement('div');
                text.className = 'note-text';
                text.textContent = n.content;
                body.appendChild(text);

                item.appendChild(body);
                notesList.appendChild(item);
            });
        }

        function updateDomFromProject(fresh) {
            if (!fresh) return;
            projectData = fresh;
            const pctText = document.getElementById('summary-pct-text');
            const pctBar = document.getElementById('summary-pct-bar');
            const tasksCount = document.getElementById('summary-tasks-count');
            if (pctText) pctText.textContent = fresh.percent + '% concluído';
            if (pctBar) pctBar.style.width = fresh.percent + '%';
            if (tasksCount) tasksCount.textContent = '· ' + fresh.tasks_done + ' de ' + fresh.tasks_total + ' tarefas';

            const tasksMap = fresh.tasks || {};
            Object.keys(tasksMap).forEach(tid => {
                const t = tasksMap[tid];
                const nodeEl = document.querySelector('.tree-node[data-task-id="' + tid + '"]');
                if (!nodeEl) return;
                nodeEl.setAttribute('data-done', t.done ? '1' : '0');

                const stateEl = nodeEl.querySelector('[data-row-state]');
                if (stateEl) {
                    stateEl.textContent = t.state;
                    stateEl.classList.toggle('is-done', !!t.done);
                    if (t.state_color) stateEl.style.borderLeft = '3px solid ' + t.state_color;
                }
                const iconWrap = nodeEl.querySelector(':scope > .tree-row .tree-icon');
                if (iconWrap) {
                    iconWrap.classList.toggle('is-done', !!t.done);
                    const iconI = iconWrap.querySelector('i');
                    if (iconI && !iconI.classList.contains('fa-folder-open')) {
                        iconI.className = 'fa-solid ' + (t.done ? 'fa-circle-check' : 'fa-circle-dot');
                    }
                }
                const barEl = nodeEl.querySelector('[data-row-bar]');
                const pctEl = nodeEl.querySelector('[data-row-pct]');
                if (barEl) barEl.style.width = t.percent + '%';
                if (pctEl) pctEl.textContent = t.percent + '%';

                const notesPill = nodeEl.querySelector('[data-notes-badge]');
                if (notesPill) {
                    const cnt = Number(t.notes_count || 0);
                    notesPill.classList.toggle('is-empty', cnt === 0);
                    notesPill.innerHTML = '<i class="fa-regular fa-comment-dots"></i> <span data-notes-count>' + cnt + '</span> ' + (cnt === 1 ? 'atualização' : 'atualizações');
                }
            });
        }

        function openTask(tid) {
            const task = (projectData.tasks || {})[String(tid)];
            if (!task) return;
            currentTaskId = Number(tid);

            trailEl.textContent = task.path || task.name;
            titleEl.textContent = task.name;

            badgesEl.innerHTML = '';
            if (task.type) {
                const tp = document.createElement('span');
                tp.className = 'tree-type';
                tp.textContent = task.type;
                badgesEl.appendChild(tp);
            }
            if (task.mine) {
                const mn = document.createElement('span');
                mn.className = 'tree-mine';
                mn.textContent = 'Minha tarefa';
                badgesEl.appendChild(mn);
            }

            // Tarefa de outra pessoa: so leitura (o servidor tambem bloqueia).
            const canEdit = !!task.mine;
            document.getElementById('drawer-progress-wrap').style.display = canEdit ? '' : 'none';
            noteForm.style.display = canEdit ? '' : 'none';
            document.getElementById('drawer-readonly-hint').style.display = canEdit ? 'none' : '';

            descEl.textContent = task.full_desc || 'O dono do projeto não adicionou descrição detalhada nesta tarefa.';
            if (task.comment) {
                commentEl.style.display = '';
                commentEl.textContent = 'Observações: ' + task.comment;
            } else {
                commentEl.style.display = 'none';
                commentEl.textContent = '';
            }

            metaEl.innerHTML = '';
            let dateStr = 'Sem prazo definido';
            if (task.start_date && task.end_date) dateStr = task.start_date + ' → ' + task.end_date;
            else if (task.end_date) dateStr = 'Até ' + task.end_date;
            else if (task.start_date) dateStr = task.start_date;

            const dChip = document.createElement('span');
            dChip.className = 'drawer-chip';
            dChip.innerHTML = '<i class="fa-regular fa-calendar"></i> ';
            dChip.appendChild(document.createTextNode('Prazo: ' + dateStr));
            metaEl.appendChild(dChip);

            const rChip = document.createElement('span');
            rChip.className = 'drawer-chip';
            rChip.innerHTML = '<i class="fa-regular fa-user"></i> ';
            rChip.appendChild(document.createTextNode('Responsável: ' + ((task.assignees && task.assignees.length) ? task.assignees.join(', ') : 'Não definido')));
            metaEl.appendChild(rChip);

            const childIds = task.child_ids || [];
            if (childIds.length) {
                subWrap.style.display = '';
                subList.innerHTML = '';
                childIds.forEach(cid => {
                    const ch = (projectData.tasks || {})[String(cid)];
                    if (!ch) return;
                    const row = document.createElement('div');
                    row.className = 'subtask-mini-item';
                    const left = document.createElement('span');
                    left.textContent = ch.name;
                    const right = document.createElement('span');
                    right.style.fontSize = '12px';
                    right.style.color = '#4a6cf7';
                    right.textContent = ch.percent + '% · ' + ch.state;
                    row.appendChild(left);
                    row.appendChild(right);
                    row.addEventListener('click', () => openTask(cid));
                    subList.appendChild(row);
                });
            } else {
                subWrap.style.display = 'none';
                subList.innerHTML = '';
            }

            renderStateButtons(task);
            setPctUI(task.percent);
            pctRange.disabled = !!task.auto_percent;
            pctPresets.style.display = task.auto_percent ? 'none' : '';
            autoPctHint.style.display = task.auto_percent ? '' : 'none';
            progressFeedback.textContent = '';
            noteFeedback.textContent = '';
            noteInput.value = '';
            renderNotes(task);

            drawer.classList.add('is-open');
        }

        document.querySelectorAll('.tree-node .tree-row').forEach(row => {
            row.addEventListener('click', () => {
                const node = row.closest('.tree-node');
                if (node) openTask(node.getAttribute('data-task-id'));
            });
            row.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const node = row.closest('.tree-node');
                    if (node) openTask(node.getAttribute('data-task-id'));
                }
            });
        });

        document.getElementById('close-task-drawer').addEventListener('click', () => drawer.classList.remove('is-open'));
        drawer.addEventListener('click', (e) => {
            if (e.target === drawer) drawer.classList.remove('is-open');
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                drawer.classList.remove('is-open');
                if (infoModal) infoModal.classList.remove('is-open');
            }
        });

        pctRange.addEventListener('input', () => setPctUI(pctRange.value));
        pctPresets.querySelectorAll('.pct-btn').forEach(btn => {
            btn.addEventListener('click', () => setPctUI(btn.getAttribute('data-pct')));
        });

        async function postAction(params) {
            const body = new FormData();
            Object.keys(params).forEach(k => body.append(k, String(params[k])));
            body.append('_glpi_csrf_token', csrfToken);
            const res = await fetch(actionUrl, {
                method: 'POST',
                body: body,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrfToken }
            });
            const data = await res.json();
            if (data && data.csrf) csrfToken = data.csrf;
            return data;
        }

        saveProgressBtn.addEventListener('click', async () => {
            if (!currentTaskId) return;
            saveProgressBtn.disabled = true;
            progressFeedback.className = 'drawer-feedback';
            progressFeedback.textContent = 'Salvando...';
            try {
                const res = await postAction({
                    action: 'update_progress',
                    project_id: projectData.id,
                    task_id: currentTaskId,
                    projectstates_id: selectedStateId,
                    percent_done: selectedPercent
                });
                if (!res || !res.ok) {
                    progressFeedback.className = 'drawer-feedback is-err';
                    progressFeedback.textContent = (res && res.message) || 'Não foi possível salvar.';
                } else {
                    if (res.project) updateDomFromProject(res.project);
                    if (res.task) setPctUI(res.task.percent);
                    progressFeedback.className = 'drawer-feedback is-ok';
                    progressFeedback.textContent = res.message || 'Andamento salvo.';
                }
            } catch (err) {
                progressFeedback.className = 'drawer-feedback is-err';
                progressFeedback.textContent = 'Erro de comunicação ao salvar.';
            } finally {
                saveProgressBtn.disabled = false;
            }
        });

        noteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentTaskId) return;
            const text = noteInput.value.trim();
            if (text.length < 2) {
                noteFeedback.className = 'drawer-feedback is-err';
                noteFeedback.textContent = 'Escreva o que já foi desenvolvido.';
                noteInput.focus();
                return;
            }
            sendNoteBtn.disabled = true;
            noteFeedback.className = 'drawer-feedback';
            noteFeedback.textContent = 'Enviando...';
            try {
                const res = await postAction({
                    action: 'add_note',
                    project_id: projectData.id,
                    task_id: currentTaskId,
                    content: text
                });
                if (!res || !res.ok) {
                    noteFeedback.className = 'drawer-feedback is-err';
                    noteFeedback.textContent = (res && res.message) || 'Não foi possível enviar.';
                } else {
                    noteInput.value = '';
                    if (res.project) updateDomFromProject(res.project);
                    if (res.task) renderNotes(res.task);
                    noteFeedback.className = 'drawer-feedback is-ok';
                    noteFeedback.textContent = res.message || 'Atualização registrada.';
                }
            } catch (err) {
                noteFeedback.className = 'drawer-feedback is-err';
                noteFeedback.textContent = 'Erro de comunicação ao enviar.';
            } finally {
                sendNoteBtn.disabled = false;
            }
        });
    });
    </script>
</body>
</html>
