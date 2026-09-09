<?php
include ("../../../inc/includes.php");

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
    <!-- FontAwesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/painel.css?v=1.0">
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

    <script src="../js/painel.js?v=1.0"></script>
</body>
</html>
