<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}



// Verificação de segurança nativa do GLPI
Session::checkLoginUser();

$user = new User();
$user->getFromDB(Session::getLoginUserID());
$firstname = $user->fields['firstname'];
$realname = $user->fields['realname'];
$display_name = trim($firstname) ? trim($firstname) : (trim($realname) ? trim($realname) : $user->fields['name']);

// Pega apenas o primeiro nome e remove " - Ativa Locação" caso venha junto
$primeiro_nome = explode(' ', $display_name)[0];
$primeiro_nome = explode('-', $primeiro_nome)[0];

global $DB;
$uid = Session::getLoginUserID();

$tickets_abertos = [];
$tickets_fechados = [];

$iterator = $DB->request([
    'SELECT' => ['t.id', 't.name', 't.status', 't.date', 't.date_mod', 't.is_deleted'],
    'FROM'   => 'glpi_tickets AS t',
    'INNER JOIN' => [
        'glpi_tickets_users AS tu' => [
            'ON' => [
                'tu' => 'tickets_id',
                't'  => 'id'
            ]
        ]
    ],
    'WHERE'  => [
        'tu.users_id' => $uid,
        'tu.type'     => 1
    ],
    'ORDER'  => 't.date DESC'
]);

foreach ($iterator as $row) {
    if ($row['is_deleted'] == 1) {
        $row['status_display'] = 'Excluído';
        $row['status_class'] = 'status-deleted';
        $tickets_fechados[] = $row;
    } else {
        $row['status_display'] = Ticket::getStatus($row['status']);
        $row['status_class'] = 'status-' . intval($row['status']);
        
        if ($row['status'] < 5) {
            // Verifica quem enviou a última mensagem
            $last_followup = $DB->request([
                'SELECT' => ['users_id'],
                'FROM'   => 'glpi_itilfollowups',
                'WHERE'  => [
                    'itemtype' => 'Ticket',
                    'items_id' => $row['id']
                ],
                'ORDER'  => 'date DESC',
                'LIMIT'  => 1
            ])->current();
            
            if ($last_followup && $last_followup['users_id'] != $uid) {
                $row['status_display'] = 'Respondida';
                $row['status_class'] = 'status-answered';
            }
            
            $tickets_abertos[] = $row;
        } else {
            $tickets_fechados[] = $row;
        }
    }
    $firstname = $user->fields['firstname'];
    $realname = $user->fields['realname'];
    $display_name = trim($firstname) ? trim($firstname) : (trim($realname) ? trim($realname) : $user->fields['name']);
    $primeiro_nome = explode(' ', $display_name)[0];
    $primeiro_nome = explode('-', $primeiro_nome)[0];
    
    $picture = $user->fields['picture'] ?? '';
    $avatar_url = $picture ? $CFG_GLPI['root_doc'] . '/front/document.send.php?file=_pictures/' . $picture : '';
}

// Verifica se o usuário tem perfil de Admin na sessão
$has_super_admin = false;
$super_admin_prof_id = 0;
if (isset($_SESSION['glpiprofiles'])) {
    foreach ($_SESSION['glpiprofiles'] as $id => $prof) {
        if (stripos($prof['name'], 'admin') !== false) {
            $has_super_admin = true;
            $super_admin_prof_id = $id;
            break;
        }
    }
}

if (isset($_GET['switch_to_admin']) && $has_super_admin) {
    Session::changeProfile($super_admin_prof_id);
    header("Location: " . $CFG_GLPI['root_doc'] . "/front/central.php");
    exit();
}

// Verifica se os tickets não estão vazios
$total_tickets = count($tickets_abertos) + count($tickets_fechados);
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
        .nav-links a { display: flex; align-items: center; padding: 25px 0; color: #555; font-weight: 600; font-size: 14px; border-bottom: 2px solid transparent; }
        .nav-links a.active { color: #333; border-bottom: 2px solid #333; }
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
        
        .container { max-width: 1300px; margin: 40px auto; padding: 0 30px; }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-action h1 { font-size: 28px; font-weight: 800; color: #1A1F36; letter-spacing: -0.5px; }
        
        .btn-novo-chamado { background-color: #555A6B; color: #FFF; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-novo-chamado:hover { background-color: #3E4352; transform: translateY(-1px); box-shadow: 0 6px 8px rgba(0,0,0,0.08); }
        .btn-novo-chamado-small { background-color: #555A6B; color: #FFF; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: background 0.2s; display: inline-block; margin-top: 20px; }
        .btn-novo-chamado-small:hover { background-color: #3E4352; }
        
        .tickets-list { display: flex; flex-direction: column; gap: 15px; }
        .ticket-card { display: flex; align-items: center; justify-content: space-between; background-color: #FFFFFF; padding: 20px 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #F0F0F0; }
        .ticket-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); border-color: #E2E8F0; }
        
        .ticket-main { flex: 1; }
        .ticket-id { font-size: 12px; font-weight: 700; color: #888; margin-bottom: 5px; }
        .ticket-title { font-size: 16px; font-weight: 700; color: #1A1F36; margin-bottom: 8px; }
        .ticket-meta { display: flex; gap: 20px; font-size: 13px; color: #666; font-weight: 500; }
        .ticket-meta span { display: flex; align-items: center; }
        .ticket-meta i { margin-right: 6px; color: #A0AEC0; }
        
        .ticket-status { font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-new { background-color: #FEF3C7; color: #D97706; }
        .status-processing { background-color: #E0E7FF; color: #4338CA; }
        .status-solved { background-color: #D1FAE5; color: #059669; }
        .status-closed { background-color: #F3F4F6; color: #4B5563; }
        .status-answered { background-color: #059669; color: #FFF; }
        
        .empty-state { text-align: center; padding: 60px 20px; background: #FFF; border-radius: 12px; border: 1px dashed #D1D5DB; }
        .empty-state i { font-size: 48px; color: #D1D5DB; margin-bottom: 20px; }
        .empty-state h3 { font-size: 18px; color: #374151; margin-bottom: 10px; }
        .empty-state p { color: #6B7280; font-size: 14px; }

        .tabs { display: flex; margin-bottom: 20px; }
        .tab { padding: 12px 20px; font-size: 13px; font-weight: 700; color: #888; cursor: pointer; border-radius: 6px; transition: all 0.2s; }
        .tab.active { background-color: #FFFFFF; color: #333; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .tab-content { display: none; background-color: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); padding: 40px; min-height: 600px; border: 1px solid #F0F0F0; }
        .tab-content.active { display: block; }
        .empty-state { text-align: center; padding: 60px 0; }
        .empty-state .illustration { margin-bottom: 30px; }
        .empty-state h3 { font-size: 20px; font-weight: 700; color: #1A1F36; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; color: #666; line-height: 1.6; }
        .tickets-list { width: 100%; }
        .ticket-header { display: flex; padding: 15px 25px; border-bottom: 2px solid #F4F5F7; text-transform: uppercase; letter-spacing: 0.5px; }
        .ticket-header > div { font-size: 13px !important; font-weight: 700 !important; color: #6B7280 !important; }
        .ticket-card { display: flex; align-items: center; padding: 20px 25px; border-bottom: 1px solid #F4F5F7; transition: all 0.2s; text-decoration: none; }
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
    <div class="topbar">
        <div class="topbar-left">
            <div class="logo">
                <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="height: 45px;">
            </div>
            <nav class="nav-links">
                <a href="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/painel.php" class="active"><i class="fa-regular fa-window-maximize"></i> Chamados</a>
            </nav>
        </div>
        <div class="topbar-right">
            <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?create_ticket=1" class="btn-new-ticket">Abrir Chamado</a>
            <div class="user-profile">
                <div class="avatar" style="<?= isset($avatar_url) && $avatar_url ? 'background-image: url('.$avatar_url.');' : '' ?>"><?= isset($avatar_url) && $avatar_url ? '' : '<i class="fa-solid fa-user"></i>' ?></div>
                <span class="user-name">Olá, <strong><?= htmlspecialchars($primeiro_nome) ?></strong> <i class="fa-solid fa-caret-down" style="font-size: 10px; margin-left: 5px; color: #888;"></i></span>
                
                <div class="dropdown-menu">
                    <?php if ($has_super_admin): ?>
                        <a href="?switch_to_admin=1"><i class="fa-solid fa-user-shield"></i> Trocar para Admin</a>
                    <?php endif; ?>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?custom_profile=1"><i class="fa-solid fa-gear"></i> Minhas configurações</a>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header-action">
            <h1>Chamados</h1>
        </div>

        <div class="content-box">
            <div class="tabs">
                <div class="tab active" data-target="abertos">ABERTOS</div>
                <div class="tab" data-target="fechados">FECHADOS</div>
            </div>

            <div class="tab-content active" id="tab-abertos">
                <?php if (count($tickets_abertos) == 0): ?>
                    <div class="empty-state">
                        <div class="illustration">
                            <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="max-width: 250px; height: auto; opacity: 0.8; margin-bottom: 20px;">
                        </div>
                        <h3>Nenhum chamado encontrado...</h3>
                        <p>Você ainda não cadastrou nenhum chamado.<br>Cadastre um novo chamado para começar.</p>
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
                        <?php foreach($tickets_abertos as $t): ?>
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
                <?php if (count($tickets_fechados) == 0): ?>
                    <div class="empty-state">
                        <div class="illustration">
                            <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="max-width: 250px; height: auto; opacity: 0.8; margin-bottom: 20px;">
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
                        <?php foreach($tickets_fechados as $t): ?>
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

        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                const target = this.getAttribute('data-target');
                document.getElementById('tab-' + target).classList.add('active');
            });
        });
    });
    </script>
</body>
</html>
