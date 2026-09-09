<?php
include ("inc/includes.php");

// Verificação de segurança
if (!Session::getLoginUserID()) {
    Html::redirect($CFG_GLPI['root_doc'] . "/index.php");
}

$user = new User();
$user->getFromDB(Session::getLoginUserID());
$firstname = $user->fields['firstname'];
$realname = $user->fields['realname'];
$display_name = trim($firstname) ? $firstname : (trim($realname) ? $realname : $user->fields['name']);

// Busca os chamados do usuário logado usando a classe Ticket
global $DB;
$uid = Session::getLoginUserID();

$tickets_abertos = [];
$tickets_fechados = [];

// Usar query nativa para velocidade e flexibilidade
$query = "
    SELECT t.id, t.name, t.status, t.date, t.date_mod
    FROM glpi_tickets t
    JOIN glpi_tickets_users tu ON tu.tickets_id = t.id
    WHERE tu.users_id = $uid AND tu.type = 1 
    ORDER BY t.date DESC
";
$result = $DB->query($query);
if ($result) {
    while ($row = $DB->fetchAssoc($result)) {
        if ($row['status'] < 5) {
            $tickets_abertos[] = $row;
        } else {
            $tickets_fechados[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamados - Ativa Locação</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #F7F8FA; color: #333; }
        a { text-decoration: none; color: inherit; }
        .topbar { display: flex; justify-content: space-between; align-items: center; background-color: #FFFFFF; padding: 0 40px; height: 60px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .topbar-left { display: flex; align-items: center; }
        .logo { display: flex; align-items: center; font-weight: 800; font-size: 20px; color: #0b457e; margin-right: 40px; }
        .nav-links a { display: flex; align-items: center; padding: 20px 0; color: #555; font-weight: 500; font-size: 14px; border-bottom: 2px solid transparent; }
        .nav-links a.active { color: #333; border-bottom: 2px solid #333; }
        .nav-links a i { margin-right: 8px; }
        .topbar-right { display: flex; align-items: center; }
        .search-icon { font-size: 16px; color: #666; margin-right: 20px; cursor: pointer; }
        .user-profile { display: flex; align-items: center; position: relative; cursor: pointer; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background-color: #E2E4E8; display: flex; align-items: center; justify-content: center; color: #666; margin-right: 10px; }
        .user-name { font-size: 14px; color: #333; }
        .dropdown-menu { display: none; position: absolute; top: 40px; right: 0; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 6px; padding: 10px 0; min-width: 200px; z-index: 100; }
        .user-profile:hover .dropdown-menu { display: block; }
        .dropdown-menu a { display: block; padding: 10px 20px; font-size: 14px; color: #444; }
        .dropdown-menu a:hover { background-color: #F7F8FA; color: #0b457e; }
        .dropdown-menu i { margin-right: 10px; width: 15px; text-align: center; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-action h1 { font-size: 24px; font-weight: 700; color: #1A1F36; }
        .btn-novo-chamado { background-color: #555A6B; color: #FFF; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; transition: background 0.2s; }
        .btn-novo-chamado:hover { background-color: #3E4352; }
        .btn-novo-chamado-small { background-color: #555A6B; color: #FFF; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; transition: background 0.2s; display: inline-block; margin-top: 20px; }
        .btn-novo-chamado-small:hover { background-color: #3E4352; }
        .content-box { background-color: transparent; }
        .tabs { display: flex; margin-bottom: 20px; padding-left: 20px; }
        .tab { padding: 10px 15px; font-size: 12px; font-weight: 700; color: #888; cursor: pointer; margin-right: 10px; border-radius: 4px 4px 0 0; transition: all 0.2s; }
        .tab.active { background-color: #FFFFFF; color: #333; box-shadow: 0 -2px 5px rgba(0,0,0,0.02); }
        .tab-content { display: none; background-color: #FFFFFF; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); padding: 40px; min-height: 400px; }
        .tab-content.active { display: block; }
        .empty-state { text-align: center; padding: 40px 0; }
        .empty-state .illustration { margin-bottom: 30px; }
        .empty-state h3 { font-size: 18px; font-weight: 600; color: #333; margin-bottom: 10px; }
        .empty-state p { font-size: 14px; color: #666; line-height: 1.5; }
        .tickets-list { width: 100%; }
        .ticket-header { display: flex; padding: 15px 20px; border-bottom: 1px solid #EEE; font-weight: 600; font-size: 12px; color: #888; text-transform: uppercase; }
        .ticket-card { display: flex; align-items: center; padding: 15px 20px; border-bottom: 1px solid #F0F0F0; transition: background 0.2s; }
        .ticket-card:hover { background-color: #F9FAFB; }
        .ticket-card:last-child { border-bottom: none; }
        .t-id { width: 80px; font-weight: 600; color: #555; font-size: 13px; }
        .t-title { flex: 1; font-weight: 500; color: #333; font-size: 14px; }
        .t-date { width: 150px; color: #777; font-size: 13px; }
        .t-status { width: 150px; font-size: 12px; font-weight: 600; text-align: right; }
        .status-1, .status-2 { color: #f59e0b; }
        .status-3, .status-4 { color: #3b82f6; }
        .status-5, .status-6 { color: #10b981; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">
            <div class="logo">
                <i class="fa-solid fa-mountain-sun" style="color: #0b457e; font-size: 24px; margin-right: 5px;"></i> Ativa
            </div>
            <nav class="nav-links">
                <a href="painel.php" class="active"><i class="fa-regular fa-window-maximize"></i> Chamados</a>
            </nav>
        </div>
        <div class="topbar-right">
            <div class="search-icon">
                <i class="fa-solid fa-search"></i>
            </div>
            <div class="user-profile">
                <div class="avatar"><i class="fa-solid fa-user"></i></div>
                <span class="user-name">Olá, <strong><?= htmlspecialchars($display_name) ?></strong> <i class="fa-solid fa-caret-down" style="font-size: 10px; margin-left: 5px;"></i></span>
                
                <div class="dropdown-menu">
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/preference.php"><i class="fa-solid fa-gear"></i> Minhas configurações</a>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header-action">
            <h1>Chamados</h1>
            <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?create_ticket=1" class="btn-novo-chamado">Novo Chamado</a>
        </div>

        <div class="content-box">
            <div class="tabs">
                <div class="tab active" data-target="abertos">ABERTOS</div>
                <div class="tab" data-target="fechados">FECHADOS</div>
            </div>

            <!-- TAB ABERTOS -->
            <div class="tab-content active" id="tab-abertos">
                <?php if (count($tickets_abertos) == 0): ?>
                    <div class="empty-state">
                        <div class="illustration">
                            <!-- SVG minimalista simulando árvores e personagens -->
                            <svg width="250" height="180" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="120" cy="70" r="18" fill="#FF5C77"/>
                                <path d="M150 160 C 130 110, 170 80, 150 40 C 120 70, 100 130, 150 160" fill="#E2E4E8"/>
                                <path d="M220 160 C 200 110, 240 80, 220 50 C 190 80, 170 130, 220 160" fill="#E2E4E8"/>
                                <path d="M150 160 L150 40" stroke="#464F60" stroke-width="2.5"/>
                                <path d="M150 100 L120 70" stroke="#464F60" stroke-width="2.5"/>
                                <path d="M220 160 L220 50" stroke="#464F60" stroke-width="2.5"/>
                                <path d="M220 120 L190 90" stroke="#464F60" stroke-width="2.5"/>
                                <circle cx="110" cy="120" r="14" fill="#464F60"/>
                                <circle cx="115" cy="115" r="4" fill="#FFFFFF"/>
                                <path d="M90 160 Q 110 115 130 160" fill="#6B7280"/>
                                <circle cx="190" cy="125" r="14" fill="#464F60"/>
                                <circle cx="185" cy="120" r="4" fill="#FFFFFF"/>
                                <path d="M170 160 Q 190 125 210 160" fill="#6B7280"/>
                                <path d="M140 160 Q 160 135 180 160" fill="#464F60"/>
                                <path d="M60 160 L 260 160" stroke="#464F60" stroke-width="3" stroke-linecap="round"/>
                                <path d="M100 167 L 220 167" stroke="#464F60" stroke-width="3" stroke-linecap="round"/>
                            </svg>
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
                                <div class="t-status status-<?=intval($t['status'])?>"><?=Ticket::getStatus($t['status'])?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB FECHADOS -->
            <div class="tab-content" id="tab-fechados">
                <?php if (count($tickets_fechados) == 0): ?>
                    <div class="empty-state">
                        <div class="illustration">
                            <svg width="250" height="180" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="150" cy="70" r="18" fill="#D1D5DB"/>
                                <path d="M150 160 C 130 110, 170 80, 150 40 C 120 70, 100 130, 150 160" fill="#E2E4E8"/>
                                <path d="M150 160 L150 40" stroke="#464F60" stroke-width="2.5"/>
                                <path d="M60 160 L 260 160" stroke="#464F60" stroke-width="3" stroke-linecap="round"/>
                            </svg>
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
                                <div class="t-status status-<?=intval($t['status'])?>"><?=Ticket::getStatus($t['status'])?></div>
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
