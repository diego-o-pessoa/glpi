<?php
if (!defined('GLPI_ROOT')) {
    include("../../../inc/includes.php");
}

require_once __DIR__ . '/_common.php';

$ctx = painel_user_context();
$uid = $ctx['uid'];

global $DB;

$tickets_abertos = [];
$tickets_fechados = [];
$waiting_first_count = 0;
$answered_count = 0;

$iterator = $DB->request([
    'SELECT' => ['t.id', 't.name', 't.status', 't.date', 't.date_mod', 't.is_deleted'],
    'FROM'   => 'glpi_tickets AS t',
    'INNER JOIN' => [
        'glpi_tickets_users AS tu' => [
            'ON' => [
                'tu' => 'tickets_id',
                't'  => 'id',
            ],
        ],
    ],
    'WHERE'  => [
        'tu.users_id' => $uid,
        'tu.type'     => 1,
    ],
    'ORDER'  => 't.date DESC',
]);

foreach ($iterator as $row) {
    if ((int) $row['is_deleted'] === 1) {
        $row['status_display'] = 'Excluído';
        $row['status_class'] = 'status-deleted';
        $tickets_fechados[] = $row;
        continue;
    }

    $status = (int) $row['status'];
    $row['status_display'] = Ticket::getStatus($status);
    $row['status_class'] = 'status-' . $status;

    if ($status < 5) {
        if ($status === 1) {
            $waiting_first_count++;
        }
        $last_followup = $DB->request([
            'SELECT' => ['users_id'],
            'FROM'   => 'glpi_itilfollowups',
            'WHERE'  => [
                'itemtype' => 'Ticket',
                'items_id' => $row['id'],
            ],
            'ORDER'  => 'date DESC',
            'LIMIT'  => 1,
        ])->current();

        if ($last_followup && (int) $last_followup['users_id'] !== $uid) {
            $row['status_display'] = 'Respondida';
            $row['status_class'] = 'status-answered';
            $answered_count++;
        }

        $tickets_abertos[] = $row;
    } else {
        $tickets_fechados[] = $row;
    }
}

$open_count = count($tickets_abertos);
$closed_count = count($tickets_fechados);
$total_tickets = $open_count + $closed_count;

$projects_data = painel_user_projects_data($uid, $ctx['my_groups']);
$projects_total = count($projects_data['projects']);
$projects_active = (int) ($projects_data['kpis']['active']['value'] ?? 0);
$my_pending_tasks = (int) ($projects_data['kpis']['my_tasks']['value'] ?? 0);

$open_progress = $total_tickets > 0 ? (int) round(100 * $open_count / $total_tickets) : 0;
$answered_progress = $open_count > 0 ? (int) round(100 * $answered_count / $open_count) : 0;
$closed_progress = $total_tickets > 0 ? (int) round(100 * $closed_count / $total_tickets) : 0;
$projects_progress = $projects_total > 0 ? (int) round(100 * $projects_active / $projects_total) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Chamados - Ativa Locação</title>
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

        .btn-novo-chamado-small { background-color: #555A6B; color: #FFF; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: background 0.2s; display: inline-block; margin-top: 20px; }
        .btn-novo-chamado-small:hover { background-color: #3E4352; }

        .tabs { display: flex; gap: 6px; margin-bottom: 18px; }
        .tab { padding: 11px 20px; font-size: 13px; font-weight: 700; color: #7a85a3; cursor: pointer; border-radius: 10px; transition: all 0.2s; }
        .tab:hover { color: #1a2340; background: rgba(74, 108, 247, 0.06); }
        .tab.active { background-color: #FFFFFF; color: #1a2340; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #e9ecf4; }
        .tab-content { display: none; background-color: #FFFFFF; border-radius: 16px; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04), 0 12px 30px -20px rgba(16, 24, 40, 0.22); padding: 32px; min-height: 440px; border: 1px solid #e9ecf4; }
        .tab-content.active { display: block; }
        .empty-state { text-align: center; padding: 50px 0; }
        .empty-state .illustration { margin-bottom: 24px; }
        .empty-state h3 { font-size: 20px; font-weight: 700; color: #1A1F36; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; color: #666; line-height: 1.6; }

        .tickets-list { width: 100%; }
        .ticket-header { display: flex; padding: 12px 20px; border-bottom: 2px solid #F4F5F7; text-transform: uppercase; letter-spacing: 0.5px; }
        .ticket-header > div { font-size: 12px !important; font-weight: 700 !important; color: #6B7280 !important; }
        .ticket-card { display: flex; align-items: center; padding: 18px 20px; border-bottom: 1px solid #F4F5F7; transition: all 0.2s; text-decoration: none; }
        .ticket-card:hover { background-color: #FAFBFC; transform: translateX(2px); }
        .ticket-card:last-child { border-bottom: none; }
        .t-id { width: 90px; font-weight: 700; color: #555A6B; font-size: 14px; }
        .t-title { flex: 1; font-weight: 600; color: #222; font-size: 15px; padding-right: 20px; }
        .t-date { width: 160px; color: #666; font-size: 14px; }
        .t-status { width: 160px; font-size: 13px; font-weight: 700; text-align: right; }
        .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 11px; background: #F3F4F6; color: #4B5563; }
        .status-1 .status-badge, .status-2 .status-badge { background: #FEF3C7; color: #D97706; }
        .status-3 .status-badge, .status-4 .status-badge { background: #DBEAFE; color: #2563EB; }
        .status-5 .status-badge { background: #D1FAE5; color: #059669; }
        .status-6 .status-badge { background: #FEF3C7; color: #D97706; }
        .status-deleted .status-badge { background: #FEE2E2; color: #DC2626; }
        .status-answered .status-badge { background: #D1FAE5; color: #059669; }
    </style>
</head>
<body>
    <?php painel_render_topbar('chamados', $ctx, true, '?switch_to_admin=1'); ?>

    <div class="container">
        <div class="header-action">
            <h1>Chamados</h1>
            <p class="header-subtitle">Acompanhe seus chamados e projetos em um só lugar.</p>
        </div>

        <div class="ac-kpi-grid">
            <button type="button" class="ac-kpi-card is-blue" data-jump-tab="abertos">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Chamados em aberto</span>
                    <span class="ac-kpi-tile"><i class="fa-solid fa-ticket"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $open_count ?></div>
                <div class="ac-kpi-note">
                    <?php if ($waiting_first_count > 0): ?>
                        <b><?= $waiting_first_count ?></b> aguardando atendimento
                    <?php elseif ($open_count > 0): ?>
                        todos em atendimento
                    <?php else: ?>
                        nenhum chamado aberto
                    <?php endif; ?>
                </div>
                <div class="ac-kpi-bar"><span style="width: <?= $open_progress ?>%"></span></div>
            </button>

            <button type="button" class="ac-kpi-card is-orange" data-jump-tab="abertos">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Respondidos pela T.I.</span>
                    <span class="ac-kpi-tile"><i class="fa-regular fa-comment-dots"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $answered_count ?></div>
                <div class="ac-kpi-note">
                    <?= $answered_count > 0 ? 'aguardando sua resposta' : 'nenhum aguardando resposta' ?>
                </div>
                <div class="ac-kpi-bar"><span style="width: <?= $answered_progress ?>%"></span></div>
            </button>

            <button type="button" class="ac-kpi-card is-green" data-jump-tab="fechados">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Chamados concluídos</span>
                    <span class="ac-kpi-tile"><i class="fa-regular fa-circle-check"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $closed_count ?></div>
                <div class="ac-kpi-note">
                    <?= $total_tickets > 0 ? 'de ' . $total_tickets . ' registrados' : 'finalizados até hoje' ?>
                </div>
                <div class="ac-kpi-bar"><span style="width: <?= $closed_progress ?>%"></span></div>
            </button>

            <a href="<?= htmlspecialchars(painel_projects_url()) ?>" class="ac-kpi-card is-violet">
                <div class="ac-kpi-top">
                    <span class="ac-kpi-label">Seus projetos</span>
                    <span class="ac-kpi-tile"><i class="fa-solid fa-diagram-project"></i></span>
                </div>
                <div class="ac-kpi-value"><?= $projects_total ?></div>
                <div class="ac-kpi-note">
                    <?php if ($my_pending_tasks > 0): ?>
                        <b><?= $my_pending_tasks ?></b> <?= $my_pending_tasks === 1 ? 'tarefa pendente' : 'tarefas pendentes' ?>
                    <?php elseif ($projects_active > 0): ?>
                        <?= $projects_active ?> em execução
                    <?php else: ?>
                        nenhum projeto vinculado
                    <?php endif; ?>
                </div>
                <div class="ac-kpi-bar"><span style="width: <?= $projects_progress ?>%"></span></div>
            </a>
        </div>

        <div class="content-box" id="chamados-box">
            <div class="tabs">
                <div class="tab active" data-target="abertos">ABERTOS (<?= $open_count ?>)</div>
                <div class="tab" data-target="fechados">FECHADOS (<?= $closed_count ?>)</div>
            </div>

            <div class="tab-content active" id="tab-abertos">
                <?php if ($open_count === 0): ?>
                    <div class="empty-state">
                        <div class="illustration">
                            <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="max-width: 230px; height: auto; opacity: 0.85; margin-bottom: 16px;">
                        </div>
                        <h3>Nenhum chamado encontrado...</h3>
                        <p>Você ainda não cadastrou nenhum chamado aberto.<br>Cadastre um novo chamado para começar.</p>
                        <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?create_ticket=1" class="btn-novo-chamado-small">Criar Chamado Agora</a>
                    </div>
                <?php else: ?>
                    <div class="tickets-list">
                        <div class="ticket-header">
                            <div class="t-id">ID</div>
                            <div class="t-title">Título</div>
                            <div class="t-date">Data de Abertura</div>
                            <div class="t-status">Status</div>
                        </div>
                        <?php foreach ($tickets_abertos as $t): ?>
                            <a href="<?=$CFG_GLPI['root_doc']?>/front/ticket.form.php?id=<?=$t['id']?>" class="ticket-card">
                                <div class="t-id">#<?=$t['id']?></div>
                                <div class="t-title"><?=htmlspecialchars($t['name'])?></div>
                                <div class="t-date"><?=date('d/m/Y H:i', strtotime($t['date']))?></div>
                                <div class="t-status <?=$t['status_class']?>"><span class="status-badge"><?=$t['status_display']?></span></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-content" id="tab-fechados">
                <?php if ($closed_count === 0): ?>
                    <div class="empty-state">
                        <div class="illustration">
                            <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="max-width: 230px; height: auto; opacity: 0.85; margin-bottom: 16px;">
                        </div>
                        <h3>Nenhum chamado fechado...</h3>
                        <p>Você não possui chamados finalizados ainda.</p>
                    </div>
                <?php else: ?>
                    <div class="tickets-list">
                        <div class="ticket-header">
                            <div class="t-id">ID</div>
                            <div class="t-title">Título</div>
                            <div class="t-date">Data de Abertura</div>
                            <div class="t-status">Status</div>
                        </div>
                        <?php foreach ($tickets_fechados as $t): ?>
                            <a href="<?=$CFG_GLPI['root_doc']?>/front/ticket.form.php?id=<?=$t['id']?>" class="ticket-card">
                                <div class="t-id">#<?=$t['id']?></div>
                                <div class="t-title"><?=htmlspecialchars($t['name'])?></div>
                                <div class="t-date"><?=date('d/m/Y H:i', strtotime($t['date']))?></div>
                                <div class="t-status <?=$t['status_class']?>"><span class="status-badge"><?=$t['status_display']?></span></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');

        function activateTab(target) {
            tabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-target') === target));
            tabContents.forEach(c => c.classList.toggle('active', c.id === 'tab-' + target));
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                activateTab(this.getAttribute('data-target'));
            });
        });

        document.querySelectorAll('[data-jump-tab]').forEach(card => {
            card.addEventListener('click', function() {
                activateTab(this.getAttribute('data-jump-tab'));
                const box = document.getElementById('chamados-box');
                if (box) {
                    box.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    });
    </script>
</body>
</html>
