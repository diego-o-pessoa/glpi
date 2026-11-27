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

/** Icone da tarefa conforme o status. */
function painel_task_icon(array $node): array
{
    if (!empty($node['children'])) {
        return [!empty($node['done']) ? 'is-done' : 'is-phase', !empty($node['done']) ? 'fa-circle-check' : 'fa-folder-open'];
    }
    return match ($node['kind'] ?? 'todo') {
        'done'      => ['is-done', 'fa-circle-check'],
        'running'   => ['is-running', 'fa-circle-half-stroke'],
        'cancelled' => ['is-cancelled', 'fa-circle-xmark'],
        default     => ['', 'fa-circle'],
    };
}

/** Texto do prazo da linha ("Até 27/11/2026", "Atrasada · 25/11/2026"). */
function painel_task_due_text(array $node): string
{
    if (($node['done_date'] ?? '') !== '') {
        return 'Concluída em ' . $node['done_date'];
    }
    $end = (string) ($node['end_date'] ?? '');
    if ($end === '') {
        return $node['start_date'] !== '' ? 'Início ' . $node['start_date'] : 'Sem prazo';
    }
    return match ($node['due_tone'] ?? '') {
        'late'  => 'Atrasada · ' . $end,
        'soon'  => 'Vence ' . $end,
        default => 'Até ' . $end,
    };
}

/** Bolinhas com as iniciais dos responsaveis (nome completo no title). */
function painel_render_avatars(array $names, int $max = 3): void
{
    if ($names === []) {
        echo '<span class="av-none">—</span>';
        return;
    }
    foreach (array_slice($names, 0, $max) as $name) {
        echo '<span class="av" title="' . htmlspecialchars($name) . '">' . htmlspecialchars(painel_initials($name)) . '</span>';
    }
    if (count($names) > $max) {
        echo '<span class="av av-more" title="' . htmlspecialchars(implode(', ', array_slice($names, $max))) . '">+' . (count($names) - $max) . '</span>';
    }
}

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
        [$iconTone, $iconClass] = painel_task_icon($node);
        ?>
        <div class="tree-node" data-task-id="<?= (int) $node['id'] ?>" data-mine="<?= $isMine ? '1' : '0' ?>" data-done="<?= $isDone || $node['kind'] === 'cancelled' ? '1' : '0' ?>">
            <div class="tree-row <?= $hasChildren ? 'is-phase' : '' ?> <?= $isMine && !$hasChildren ? 'is-mine' : '' ?>" style="padding-left: <?= $pad ?>px;" role="button" tabindex="0">
                <div class="tree-main">
                    <?php if ($hasChildren): ?>
                        <button type="button" class="tree-toggle" aria-expanded="true" title="Expandir/recolher">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    <?php else: ?>
                        <span class="tree-spacer"></span>
                    <?php endif; ?>

                    <span class="tree-icon <?= $iconTone ?>" data-row-icon><i class="fa-solid <?= $iconClass ?>"></i></span>

                    <div class="tree-text">
                        <div class="tree-name">
                            <span class="<?= $node['kind'] === 'cancelled' ? 'is-struck' : '' ?>"><?= htmlspecialchars($node['name']) ?></span>
                            <?php if ($isMine && !$hasChildren): ?>
                                <span class="tree-mine">Minha</span>
                            <?php endif; ?>
                            <span class="tree-notes-pill <?= $notesCount === 0 ? 'is-empty' : '' ?>" data-notes-badge title="Atualizações registradas">
                                <i class="fa-regular fa-comment-dots"></i> <span data-notes-count><?= $notesCount ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="tree-cols">
                    <?php if ($hasChildren): ?>
                        <div class="tree-phase-prog">
                            <span class="tree-phase-text" data-phase-text><?= (int) $node['leaf_done'] ?> de <?= (int) $node['leaf_total'] ?> concluídas</span>
                            <div class="tree-pct-bar"><span data-row-bar class="<?= $isDone ? 'is-done' : '' ?>" style="width: <?= (int) $node['percent'] ?>%"></span></div>
                            <span class="tree-phase-pct" data-row-pct><?= (int) $node['percent'] ?>%</span>
                        </div>
                    <?php else: ?>
                        <span class="tree-state kind-<?= htmlspecialchars($node['kind']) ?>" data-row-state><?= htmlspecialchars($node['kind_label']) ?></span>
                    <?php endif; ?>

                    <div class="tree-assignees"><?php painel_render_avatars($node['assignees']); ?></div>

                    <div class="tree-dates due-<?= ($node['done_date'] ?? '') !== '' ? 'done' : htmlspecialchars($node['due_tone'] ?: 'none') ?>" data-row-due><?= htmlspecialchars(painel_task_due_text($node)) ?></div>
                    <i class="fa-solid fa-chevron-right tree-open-cue"></i>
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
        .ac-modal-text.is-rich { white-space: normal; }
        .ac-modal-text.is-rich p { margin: 0 0 8px; }
        .ac-modal-text.is-rich ul, .ac-modal-text.is-rich ol { margin: 0 0 8px; padding-left: 22px; }
        .ac-modal-text.is-rich img { max-width: 100%; height: auto; border-radius: 8px; }
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

        /* Lista de tarefas: status, avatares e prazos */
        .tree-row.is-mine { box-shadow: inset 3px 0 0 #8b6cf6; }
        .tree-icon.is-running { color: #f59f32; }
        .tree-icon.is-cancelled { color: #c4cbd8; }
        .is-struck { text-decoration: line-through; color: #9aa6bf; }
        .tree-state { min-width: 112px; text-align: center; border-left: 0 !important; }
        .tree-state.kind-todo { background: #eef1f8; color: #64708f; }
        .tree-state.kind-running { background: rgba(245, 159, 50, 0.16); color: #a15c07; }
        .tree-state.kind-done { background: rgba(47, 182, 124, 0.14); color: #167a4d; }
        .tree-state.kind-cancelled { background: #f4f5f8; color: #9aa6bf; }
        .tree-phase-prog { display: flex; align-items: center; gap: 10px; width: 260px; }
        .tree-phase-text { font-size: 12.5px; font-weight: 700; color: #475569; white-space: nowrap; min-width: 112px; }
        .tree-phase-prog .tree-pct-bar { flex: 1; }
        .tree-pct-bar > span.is-done { background: #2fb67c; }
        .tree-phase-pct { font-size: 12.5px; font-weight: 800; color: #1a2340; min-width: 36px; text-align: right; }
        .tree-assignees { width: 96px; display: flex; align-items: center; }
        .av { width: 28px; height: 28px; border-radius: 50%; background: rgba(74, 108, 247, 0.14); color: #3c5ce8; font-size: 10.5px; font-weight: 800; display: grid; place-items: center; border: 2px solid #fff; margin-left: -6px; cursor: default; }
        .av:first-child { margin-left: 0; }
        .av-more { background: #eef1f8; color: #64708f; }
        .av-none { color: #9aa6bf; }
        .tree-dates { width: 160px; font-weight: 600; }
        .tree-dates.due-late { color: #dc2626; }
        .tree-dates.due-done { color: #167a4d; }
        .phase-status { display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 10px 12px; border-radius: 10px; background: #f4f6fb; color: #52607e; font-size: 13px; font-weight: 600; }
        .phase-status.is-done { background: #ecfdf5; color: #167a4d; }
        .tree-dates.due-soon { color: #d97706; }
        .tree-open-cue { font-size: 12px; color: #b4bdd0; opacity: 1; }
        .tree-row:hover .tree-open-cue { color: #4a6cf7; }
        .tree-notes-pill { padding: 2px 7px; }

        /* Resumo do usuario no topo */
        .my-banner { display: flex; align-items: center; gap: 10px; margin-top: 14px; padding: 10px 14px; border-radius: 12px; background: rgba(139, 108, 246, 0.1); color: #5b3fd1; font-size: 13.5px; font-weight: 600; }
        .my-banner.is-late { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .my-banner.is-clear { background: rgba(47, 182, 124, 0.12); color: #167a4d; }
        .summary-desc.is-clamped { -webkit-line-clamp: 2; }

        /* Gaveta: status em 3 botoes grandes */
        .drawer-meta-chips.in-head { margin-top: 10px; }
        /* Iniciar / Finalizar tarefa */
        .step-box { display: flex; flex-direction: column; gap: 12px; padding: 16px; border: 1px solid #e5ebf9; border-radius: 14px; background: #f8faff; }
        .step-box.is-running { border-color: rgba(245, 159, 50, 0.4); background: #fffaf2; }
        .step-box.is-done { border-color: rgba(47, 182, 124, 0.4); background: #f0fbf6; }
        .step-info { display: flex; flex-direction: column; gap: 4px; font-size: 13px; color: #52607e; }
        .step-info b { font-size: 14.5px; color: #1a2340; }
        .step-info i { width: 16px; text-align: center; margin-right: 4px; }
        .step-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; border: 0; border-radius: 12px; font-size: 14.5px; font-weight: 800; color: #fff; cursor: pointer; background: linear-gradient(95deg, #4a6cf7, #5d7dfb); box-shadow: 0 12px 22px -12px rgba(74, 108, 247, 0.95); }
        .step-btn.is-finish { background: linear-gradient(95deg, #22a06b, #2fb67c); box-shadow: 0 12px 22px -12px rgba(47, 182, 124, 0.95); }
        .step-btn:disabled { opacity: 0.6; cursor: wait; }
        .step-btn[hidden] { display: none; }
        .status-big { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .status-big-btn { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 14px 8px; border: 2px solid #e2e8f0; border-radius: 14px; background: #fff; color: #475569; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s; }
        .status-big-btn i { font-size: 20px; color: #9aa6bf; }
        .status-big-btn:hover { border-color: #b8c4f5; }
        .status-big-btn:disabled { cursor: wait; opacity: 0.7; }
        .status-big-btn.is-active[data-status="todo"] { border-color: #64708f; background: #f4f6fb; color: #1a2340; }
        .status-big-btn.is-active[data-status="todo"] i { color: #64708f; }
        .status-big-btn.is-active[data-status="running"] { border-color: #f59f32; background: #fff8ee; color: #a15c07; }
        .status-big-btn.is-active[data-status="running"] i { color: #f59f32; }
        .status-big-btn.is-active[data-status="done"] { border-color: #2fb67c; background: #ecfdf5; color: #167a4d; }
        .status-big-btn.is-active[data-status="done"] i { color: #2fb67c; }
        .drawer-hint { font-size: 12.5px; color: #7a85a3; margin-top: 8px; }
        .phase-prog-big { display: flex; align-items: center; gap: 12px; }
        .phase-prog-big .summary-bar { flex: 1; }
        .phase-prog-big b { font-size: 15px; color: #1a2340; }
        .note-form.is-highlight textarea { border-color: #2fb67c; box-shadow: 0 0 0 3px rgba(47, 182, 124, 0.15); }
        .notes-history-title { font-size: 12px; font-weight: 800; color: #7a85a3; text-transform: uppercase; letter-spacing: 0.05em; margin: 16px 0 10px; }

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
            $has_mine = false;
            foreach ($project['tasks_map'] as $t) {
                if (!empty($t['mine']) && empty($t['children'])) {
                    $has_mine = true;
                    break;
                }
            }
            $default_filter = $project['my_pending'] > 0 ? 'mine' : 'all';
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
                    <?php if ($project['my_pending'] > 0): ?>
                        <div class="my-banner <?= $project['my_next_due_late'] ? 'is-late' : '' ?>" id="my-banner">
                            <i class="fa-solid fa-list-check"></i>
                            <span>
                                Você tem <b><?= (int) $project['my_pending'] ?></b> <?= $project['my_pending'] === 1 ? 'tarefa pendente' : 'tarefas pendentes' ?> neste projeto<?php if ($project['my_next_due'] !== ''): ?>
                                    · <?= $project['my_next_due_late'] ? 'uma está atrasada desde' : 'a próxima vence em' ?> <?= htmlspecialchars($project['my_next_due']) ?><?php endif; ?>
                            </span>
                        </div>
                    <?php elseif ($has_mine): ?>
                        <div class="my-banner is-clear" id="my-banner"><i class="fa-solid fa-circle-check"></i> Você concluiu todas as suas tarefas neste projeto.</div>
                    <?php endif; ?>
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
                        <span id="summary-tasks-count"><i class="fa-solid fa-list-check"></i> <?= (int) $project['tasks_done'] ?> de <?= (int) $project['tasks_total'] ?> tarefas concluídas</span>
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
                        <h2>Tarefas do projeto</h2>
                        <div class="tree-head-sub">Clique numa tarefa para ver os detalhes. Nas suas tarefas você marca o status e conta o que fez.</div>
                    </div>
                    <div class="tree-filters" data-default-filter="<?= $default_filter ?>">
                        <button type="button" class="tree-filter <?= $default_filter === 'all' ? 'is-active' : '' ?>" data-tfilter="all">Todas</button>
                        <?php if ($has_mine): ?>
                            <button type="button" class="tree-filter <?= $default_filter === 'mine' ? 'is-active' : '' ?>" data-tfilter="mine">Minhas tarefas</button>
                        <?php endif; ?>
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
                <?php if (($project['full_html'] ?? '') !== ''): ?>
                    <div class="ac-modal-text is-rich"><?= $project['full_html'] ?></div>
                <?php else: ?>
                    <div class="ac-modal-text"><?= htmlspecialchars($project['full_desc'] !== '' ? $project['full_desc'] : 'Sem descrição cadastrada.') ?></div>
                <?php endif; ?>
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
                    <div class="drawer-meta-chips in-head" id="drawer-meta"></div>
                </div>
                <button type="button" class="ac-close-btn" id="close-task-drawer" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="drawer-body">
                <div class="drawer-readonly-hint" id="drawer-readonly-hint" style="display: none; font-size: 13px; color: #7a85a3; background: #f4f6fb; border-radius: 10px; padding: 10px 12px;">
                    <i class="fa-solid fa-lock"></i> Esta tarefa é de outra pessoa: você pode acompanhar, mas só quem está atribuído a ela muda o status.
                </div>

                <div id="drawer-status-wrap">
                    <div class="drawer-sec-title">Andamento</div>
                    <div class="step-box" id="drawer-step-box">
                        <div class="step-info" id="drawer-step-info"></div>
                        <button type="button" class="step-btn" id="drawer-step-btn"></button>
                    </div>
                    <div class="drawer-feedback" id="status-feedback" style="margin-top: 8px;"></div>
                </div>

                <div id="drawer-phase-wrap" style="display: none;">
                    <div class="drawer-sec-title">Andamento da principal</div>
                    <div class="phase-prog-big">
                        <b id="drawer-phase-text"></b>
                        <div class="summary-bar"><span id="drawer-phase-bar"></span></div>
                    </div>
                    <div class="phase-status" id="drawer-phase-status"></div>
                    <div class="subtasks-mini" id="drawer-subtasks" style="margin-top: 12px;"></div>
                </div>

                <div class="drawer-box" id="drawer-about">
                    <div class="drawer-box-title">Sobre esta tarefa</div>
                    <div class="drawer-box-text" id="drawer-desc"></div>
                    <div class="drawer-box-text" id="drawer-comment" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #d5dff5; display: none;"></div>
                </div>

                <div>
                    <div class="drawer-sec-title">Atualizações</div>
                    <form class="note-form" id="drawer-note-form">
                        <textarea id="drawer-note-input" placeholder="O que você fez nesta tarefa? Conte para o dono do projeto acompanhar."></textarea>
                        <div class="note-form-foot">
                            <span class="drawer-feedback" id="note-feedback"></span>
                            <button type="submit" class="btn-send-note" id="btn-send-note">
                                <i class="fa-regular fa-paper-plane"></i> Enviar atualização
                            </button>
                        </div>
                    </form>
                    <div class="notes-history-title" id="drawer-notes-title">Histórico</div>
                    <div class="notes-timeline" id="drawer-notes-list"></div>
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
        const filtersEl = document.querySelector('.tree-filters');
        if (filtersEl && filtersEl.getAttribute('data-default-filter') !== 'all') {
            applyTaskFilter(filtersEl.getAttribute('data-default-filter'));
        }

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

        const $ = (id) => document.getElementById(id);
        const trailEl = $('drawer-trail');
        const titleEl = $('drawer-title');
        const badgesEl = $('drawer-badges');
        const metaEl = $('drawer-meta');
        const readonlyHint = $('drawer-readonly-hint');
        const statusWrap = $('drawer-status-wrap');
        const statusFeedback = $('status-feedback');
        const phaseWrap = $('drawer-phase-wrap');
        const phaseText = $('drawer-phase-text');
        const phaseBar = $('drawer-phase-bar');
        const subList = $('drawer-subtasks');
        const aboutBox = $('drawer-about');
        const descEl = $('drawer-desc');
        const commentEl = $('drawer-comment');
        const notesList = $('drawer-notes-list');
        const notesTitle = $('drawer-notes-title');
        const noteForm = $('drawer-note-form');
        const noteInput = $('drawer-note-input');
        const sendNoteBtn = $('btn-send-note');
        const noteFeedback = $('note-feedback');
        const notePlaceholder = noteInput.getAttribute('placeholder');

        const ICONS = {
            todo: ['', 'fa-circle'],
            running: ['is-running', 'fa-circle-half-stroke'],
            done: ['is-done', 'fa-circle-check'],
            cancelled: ['is-cancelled', 'fa-circle-xmark']
        };

        function isPhase(t) { return (t.child_ids || []).length > 0; }

        function dueText(t) {
            if (t.done_date) return 'Concluída em ' + t.done_date;
            if (!t.end_date) return t.start_date ? 'Início ' + t.start_date : 'Sem prazo';
            if (t.due_tone === 'late') return 'Atrasada · ' + t.end_date;
            if (t.due_tone === 'soon') return 'Vence ' + t.end_date;
            return 'Até ' + t.end_date;
        }

        function chip(icon, text) {
            const el = document.createElement('span');
            el.className = 'drawer-chip';
            const i = document.createElement('i');
            i.className = icon;
            el.appendChild(i);
            el.appendChild(document.createTextNode(' ' + text));
            return el;
        }

        function renderNotes(task) {
            notesList.innerHTML = '';
            const notes = task.notes || [];
            notesTitle.style.display = notes.length ? '' : 'none';
            if (!notes.length) {
                const empty = document.createElement('div');
                empty.style.fontSize = '13px';
                empty.style.color = '#7a85a3';
                empty.style.padding = '8px 0';
                empty.textContent = 'Ninguém registrou atualizações nesta tarefa ainda.';
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

        // Tarefa: "Iniciar tarefa" -> "Finalizar tarefa" -> concluida (com data e hora reais).
        const stepBox = $('drawer-step-box');
        const stepInfo = $('drawer-step-info');
        const stepBtn = $('drawer-step-btn');
        function setStatusUI(kind, task) {
            task = task || (projectData.tasks || {})[String(currentTaskId)] || {};
            stepBox.className = 'step-box' + (kind === 'running' ? ' is-running' : (kind === 'done' ? ' is-done' : ''));
            stepInfo.innerHTML = '';
            const line = (icon, strong, text) => {
                const el = document.createElement('div');
                const i = document.createElement('i'); i.className = icon; el.appendChild(i);
                if (strong) { const b = document.createElement('b'); b.textContent = strong; el.appendChild(b); }
                if (text) el.appendChild(document.createTextNode(' ' + text));
                stepInfo.appendChild(el);
            };
            if (kind === 'done') {
                line('fa-solid fa-circle-check', 'Tarefa concluída', '');
                if (task.real_start_dt) line('fa-regular fa-clock', '', 'Iniciada em ' + task.real_start_dt);
                if (task.real_end_dt) line('fa-regular fa-flag', '', 'Finalizada em ' + task.real_end_dt);
                stepBtn.hidden = true;
            } else if (kind === 'running') {
                line('fa-solid fa-circle-half-stroke', 'Em andamento', '');
                if (task.real_start_dt) line('fa-regular fa-clock', '', 'Iniciada em ' + task.real_start_dt);
                stepBtn.hidden = false;
                stepBtn.className = 'step-btn is-finish';
                stepBtn.innerHTML = '<i class="fa-solid fa-flag-checkered"></i> Finalizar tarefa';
                stepBtn.setAttribute('data-status', 'done');
            } else {
                line('fa-regular fa-circle', 'Ainda não iniciada', '');
                stepBtn.hidden = false;
                stepBtn.className = 'step-btn';
                stepBtn.innerHTML = '<i class="fa-solid fa-play"></i> Iniciar tarefa';
                stepBtn.setAttribute('data-status', 'running');
            }
        }

        function updateDomFromProject(fresh) {
            if (!fresh) return;
            projectData = fresh;
            const pctText = $('summary-pct-text');
            const pctBar = $('summary-pct-bar');
            const tasksCount = $('summary-tasks-count');
            if (pctText) pctText.textContent = fresh.percent + '% concluído';
            if (pctBar) pctBar.style.width = fresh.percent + '%';
            if (tasksCount) tasksCount.textContent = fresh.tasks_done + ' de ' + fresh.tasks_total + ' tarefas concluídas';

            const banner = $('my-banner');
            if (banner) {
                banner.className = 'my-banner' + (fresh.my_pending === 0 ? ' is-clear' : (fresh.my_next_due_late ? ' is-late' : ''));
                if (fresh.my_pending === 0) {
                    banner.textContent = 'Você concluiu todas as suas tarefas neste projeto.';
                } else {
                    let txt = 'Você tem ' + fresh.my_pending + (fresh.my_pending === 1 ? ' tarefa pendente' : ' tarefas pendentes') + ' neste projeto';
                    if (fresh.my_next_due) txt += ' · ' + (fresh.my_next_due_late ? 'uma está atrasada desde ' : 'a próxima vence em ') + fresh.my_next_due;
                    banner.textContent = txt;
                }
            }

            Object.keys(fresh.tasks || {}).forEach(tid => {
                const t = fresh.tasks[tid];
                const nodeEl = document.querySelector('.tree-node[data-task-id="' + tid + '"]');
                if (!nodeEl) return;
                nodeEl.setAttribute('data-done', (t.done || t.kind === 'cancelled') ? '1' : '0');
                const row = nodeEl.querySelector(':scope > .tree-row');

                const iconWrap = row.querySelector('[data-row-icon]');
                if (iconWrap) {
                    const ic = isPhase(t) ? (t.done ? ICONS.done : ['is-phase', 'fa-folder-open']) : (ICONS[t.kind] || ICONS.todo);
                    iconWrap.className = 'tree-icon ' + ic[0];
                    iconWrap.querySelector('i').className = 'fa-solid ' + ic[1];
                }
                const stateEl = row.querySelector('[data-row-state]');
                if (stateEl) {
                    stateEl.textContent = t.kind_label;
                    stateEl.className = 'tree-state kind-' + t.kind;
                }
                const phaseEl = row.querySelector('[data-phase-text]');
                if (phaseEl) phaseEl.textContent = t.leaf_done + ' de ' + t.leaf_total + ' concluídas';
                const barEl = row.querySelector('[data-row-bar]');
                if (barEl) {
                    barEl.style.width = t.percent + '%';
                    barEl.classList.toggle('is-done', !!t.done);
                }
                const pctEl = row.querySelector('[data-row-pct]');
                if (pctEl) pctEl.textContent = t.percent + '%';
                const dueEl = row.querySelector('[data-row-due]');
                if (dueEl) {
                    dueEl.textContent = dueText(t);
                    dueEl.className = 'tree-dates due-' + (t.done_date ? 'done' : (t.due_tone || 'none'));
                }
                const cnt = Number(t.notes_count || 0);
                const notesPill = row.querySelector('[data-notes-badge]');
                if (notesPill) notesPill.classList.toggle('is-empty', cnt === 0);
                const notesCount = row.querySelector('[data-notes-count]');
                if (notesCount) notesCount.textContent = String(cnt);
            });
        }

        function openTask(tid) {
            const task = (projectData.tasks || {})[String(tid)];
            if (!task) return;
            currentTaskId = Number(tid);
            const phase = isPhase(task);
            const canEdit = !!task.mine;

            trailEl.textContent = task.path || task.name;
            titleEl.textContent = task.name;

            badgesEl.innerHTML = '';
            const st = document.createElement('span');
            st.className = 'tree-state kind-' + (phase ? (task.done ? 'done' : 'todo') : task.kind);
            st.textContent = phase ? (task.done ? 'Principal concluída' : 'Principal · ' + task.percent + '%') : task.kind_label;
            badgesEl.appendChild(st);
            if (task.type) {
                const tp = document.createElement('span');
                tp.className = 'tree-type';
                tp.textContent = task.type;
                badgesEl.appendChild(tp);
            }
            if (canEdit && !phase) {
                const mn = document.createElement('span');
                mn.className = 'tree-mine';
                mn.textContent = 'Minha tarefa';
                badgesEl.appendChild(mn);
            }

            metaEl.innerHTML = '';
            let dateStr = 'Sem prazo definido';
            if (task.start_date && task.end_date) dateStr = task.start_date + ' → ' + task.end_date;
            else if (task.end_date) dateStr = 'Até ' + task.end_date;
            else if (task.start_date) dateStr = 'Início ' + task.start_date;
            const dChip = chip('fa-regular fa-calendar', 'Prazo: ' + dateStr + (task.due_tone === 'late' ? ' (atrasada)' : ''));
            if (task.due_tone === 'late') dChip.style.color = '#dc2626';
            metaEl.appendChild(dChip);
            metaEl.appendChild(chip('fa-regular fa-user', (task.assignees && task.assignees.length) ? task.assignees.join(', ') : 'Sem responsável'));

            // Status: so tarefas (fase e calculada) e so as do proprio usuario.
            statusWrap.style.display = (!phase && canEdit && task.kind !== 'cancelled') ? '' : 'none';
            readonlyHint.style.display = (!phase && !canEdit) ? '' : 'none';
            if (task.kind === 'cancelled' && !phase) {
                readonlyHint.style.display = '';
                readonlyHint.textContent = 'Esta tarefa foi cancelada pelo dono do projeto e não conta no andamento.';
            } else {
                readonlyHint.innerHTML = '<i class="fa-solid fa-lock"></i> Esta tarefa é de outra pessoa: você pode acompanhar, mas só quem está atribuído a ela muda o status.';
            }
            setStatusUI(task.kind, task);
            statusFeedback.textContent = '';

            phaseWrap.style.display = phase ? '' : 'none';
            subList.innerHTML = '';
            if (phase) {
                phaseText.textContent = task.leaf_done + ' de ' + task.leaf_total + ' concluídas · ' + task.percent + '%';
                phaseBar.style.width = task.percent + '%';
                phaseBar.style.background = task.done ? '#2fb67c' : '';
                // Situacao da principal: concluiu tudo ou quantas faltam.
                const phaseStatus = $('drawer-phase-status');
                const missing = Math.max(0, (task.leaf_total || 0) - (task.leaf_done || 0));
                phaseStatus.className = 'phase-status' + (missing === 0 && task.leaf_total > 0 ? ' is-done' : '');
                phaseStatus.innerHTML = '';
                const icon = document.createElement('i');
                const text = document.createElement('span');
                if (!task.leaf_total) {
                    icon.className = 'fa-regular fa-circle';
                    text.textContent = 'Ainda não há tarefas nesta principal.';
                } else if (missing === 0) {
                    icon.className = 'fa-solid fa-circle-check';
                    text.textContent = 'Todas as tarefas foram concluídas' + (task.done_date ? ' em ' + task.done_date : '') + '.';
                } else {
                    icon.className = 'fa-regular fa-hourglass-half';
                    text.textContent = 'Falta' + (missing === 1 ? '' : 'm') + ' ' + missing + ' de ' + task.leaf_total + (task.leaf_total === 1 ? ' tarefa' : ' tarefas') + ' para concluir.';
                }
                phaseStatus.appendChild(icon);
                phaseStatus.appendChild(text);
                (task.child_ids || []).forEach(cid => {
                    const ch = (projectData.tasks || {})[String(cid)];
                    if (!ch) return;
                    const row = document.createElement('div');
                    row.className = 'subtask-mini-item';
                    const left = document.createElement('span');
                    left.textContent = ch.name;
                    const right = document.createElement('span');
                    right.className = 'tree-state kind-' + (isPhase(ch) ? (ch.done ? 'done' : 'todo') : ch.kind);
                    right.textContent = isPhase(ch) ? ch.percent + '%' : ch.kind_label;
                    row.appendChild(left);
                    row.appendChild(right);
                    row.addEventListener('click', () => openTask(cid));
                    subList.appendChild(row);
                });
            }

            descEl.textContent = task.full_desc || '';
            descEl.style.display = task.full_desc ? '' : 'none';
            commentEl.style.display = task.comment ? '' : 'none';
            commentEl.textContent = task.comment ? 'Observações: ' + task.comment : '';
            aboutBox.style.display = (task.full_desc || task.comment) ? '' : 'none';

            noteForm.style.display = canEdit ? '' : 'none';
            noteForm.classList.remove('is-highlight');
            noteInput.setAttribute('placeholder', notePlaceholder);
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

        $('close-task-drawer').addEventListener('click', () => drawer.classList.remove('is-open'));
        drawer.addEventListener('click', (e) => {
            if (e.target === drawer) drawer.classList.remove('is-open');
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                drawer.classList.remove('is-open');
                if (infoModal) infoModal.classList.remove('is-open');
            }
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

        // Iniciar / Finalizar salva direto no clique.
        stepBtn.addEventListener('click', async () => {
            const task = (projectData.tasks || {})[String(currentTaskId)];
            const status = stepBtn.getAttribute('data-status');
            if (!task || !status) return;
            if (status === 'done' && !confirm('Finalizar esta tarefa? Ela será marcada como concluída agora.')) return;
            stepBtn.disabled = true;
            statusFeedback.className = 'drawer-feedback';
            statusFeedback.textContent = 'Salvando...';
            try {
                const res = await postAction({ action: 'set_status', project_id: projectData.id, task_id: currentTaskId, status: status });
                if (!res || !res.ok) {
                    statusFeedback.className = 'drawer-feedback is-err';
                    statusFeedback.textContent = (res && res.message) || 'Não foi possível salvar.';
                    return;
                }
                updateDomFromProject(res.project);
                const fresh = res.task || task;
                setStatusUI(fresh.kind, fresh);
                const badge = badgesEl.querySelector('.tree-state');
                if (badge) {
                    badge.className = 'tree-state kind-' + fresh.kind;
                    badge.textContent = fresh.kind_label;
                }
                statusFeedback.className = 'drawer-feedback is-ok';
                statusFeedback.textContent = status === 'done' ? 'Tarefa finalizada!' : 'Tarefa iniciada!';
                if (status === 'done') {
                    // Sugere (sem obrigar) contar o que foi entregue.
                    noteInput.setAttribute('placeholder', 'O que foi entregue? (opcional) Conte em poucas palavras para o dono do projeto.');
                    noteForm.classList.add('is-highlight');
                    noteInput.focus();
                }
            } catch (err) {
                statusFeedback.className = 'drawer-feedback is-err';
                statusFeedback.textContent = 'Erro de comunicação ao salvar.';
            } finally {
                stepBtn.disabled = false;
            }
        });

        noteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentTaskId) return;
            const text = noteInput.value.trim();
            if (text.length < 2) {
                noteFeedback.className = 'drawer-feedback is-err';
                noteFeedback.textContent = 'Escreva o que você fez antes de enviar.';
                noteInput.focus();
                return;
            }
            sendNoteBtn.disabled = true;
            noteFeedback.className = 'drawer-feedback';
            noteFeedback.textContent = 'Enviando...';
            try {
                const res = await postAction({ action: 'add_note', project_id: projectData.id, task_id: currentTaskId, content: text });
                if (!res || !res.ok) {
                    noteFeedback.className = 'drawer-feedback is-err';
                    noteFeedback.textContent = (res && res.message) || 'Não foi possível enviar.';
                } else {
                    noteInput.value = '';
                    noteForm.classList.remove('is-highlight');
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
