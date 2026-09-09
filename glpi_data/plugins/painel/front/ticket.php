<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}

Session::checkLoginUser();
global $DB;
$uid = Session::getLoginUserID();
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$ticket_id) {
    Html::redirect($CFG_GLPI["root_doc"] . "/");
}

// Verifica se o usuário tem acesso a este chamado (requester)
$access_check = $DB->request([
    'FROM' => 'glpi_tickets_users',
    'WHERE' => ['tickets_id' => $ticket_id, 'users_id' => $uid, 'type' => 1]
]);
if (count($access_check) == 0) {
    // Redireciona de volta ao painel se não tiver acesso
    Html::redirect($CFG_GLPI["root_doc"] . "/");
}

// Pega dados do usuário atual (para a topbar)
$user = new User();
$user->getFromDB($uid);
$firstname = $user->fields['firstname'];
$realname = $user->fields['realname'];
$display_name = trim($firstname) ? trim($firstname) : (trim($realname) ? trim($realname) : $user->fields['name']);
$primeiro_nome = explode(' ', $display_name)[0];
$primeiro_nome = explode('-', $primeiro_nome)[0];

$picture = $user->fields['picture'] ?? '';
$avatar_url = $picture ? $CFG_GLPI['root_doc'] . '/front/document.send.php?file=_pictures/' . $picture : '';

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

// Fechar Chamado (Encerrar)
if (isset($_POST['close_ticket'])) {
    $ticket = new Ticket();
    if ($ticket->getFromDB($ticket_id)) {
        $ticket->update([
            'id' => $ticket_id,
            'status' => 6 // Fechado/Finalizado
        ]);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Pega dados base do chamado
$ticket_data = [];
$t_iterator = $DB->request([
    'SELECT' => [
        't.id', 't.name', 't.status', 't.date', 't.content', 't.is_deleted',
        'c.completename AS category_name'
    ],
    'FROM' => 'glpi_tickets AS t',
    'LEFT JOIN' => [
        'glpi_itilcategories AS c' => [
            'ON' => ['t' => 'itilcategories_id', 'c' => 'id']
        ]
    ],
    'WHERE' => ['t.id' => $ticket_id]
]);
foreach ($t_iterator as $row) {
    $ticket_data = $row;
}
if (empty($ticket_data)) {
    Html::redirect($CFG_GLPI["root_doc"] . "/");
}

if ($ticket_data['is_deleted'] == 1) {
    $status_str = "Excluído";
    $status_class = "status-deleted";
} else {
    $status_str = Ticket::getStatus($ticket_data['status']);
    if ($ticket_data['status'] == 6) {
        $status_str = "Finalizado";
    }
    $status_class = 'status-' . intval($ticket_data['status']);
}

// Calcula Base URL dinâmica para o Proxy (ex: /Helpdesk)
$base_url = preg_replace('/\/front\/.*$/', '', $_SERVER['REQUEST_URI']);

// Pega o ID do requerente (quem abriu o chamado)
$requester_id = 0;
$req_iter = $DB->request([
    'FROM' => 'glpi_tickets_users',
    'WHERE' => ['tickets_id' => $ticket_id, 'type' => 1]
]);
foreach ($req_iter as $rc) {
    $requester_id = $rc['users_id'];
    break;
}

// Monta a Linha do Tempo
$timeline = [];

// 1. Descrição Inicial
$timeline[] = [
    'type' => 'Ticket',
    'id' => $ticket_data['id'],
    'date' => $ticket_data['date'],
    'content' => $ticket_data['content'],
    'users_id' => $uid, // Assumindo o requester para a abertura original
];

// 2. Acompanhamentos
$f_iterator = $DB->request([
    'SELECT' => ['id', 'date', 'content', 'users_id'],
    'FROM' => 'glpi_itilfollowups',
    'WHERE' => ['itemtype' => 'Ticket', 'items_id' => $ticket_id]
]);
foreach ($f_iterator as $row) {
    $row['type'] = 'ITILFollowup';
    $timeline[] = $row;
}

// 3. Tarefas
$t_iterator = $DB->request([
    'SELECT' => ['id', 'date', 'content', 'users_id'],
    'FROM' => 'glpi_tickettasks',
    'WHERE' => ['tickets_id' => $ticket_id]
]);
foreach ($t_iterator as $row) {
    $row['type'] = 'TicketTask';
    $timeline[] = $row;
}

// 4. Soluções
$s_iterator = $DB->request([
    'SELECT' => ['id', 'date_creation AS date', 'content', 'users_id'],
    'FROM' => 'glpi_itilsolutions',
    'WHERE' => ['itemtype' => 'Ticket', 'items_id' => $ticket_id]
]);
foreach ($s_iterator as $row) {
    $row['type'] = 'ITILSolution';
    $timeline[] = $row;
}

// Ordena a linha do tempo (mais antigas primeiro)
usort($timeline, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

// Cache de usuários
$users_cache = [];
foreach ($timeline as &$item) {
    $author_id = $item['users_id'];
    if (!isset($users_cache[$author_id])) {
        $u = new User();
        if ($u->getFromDB($author_id)) {
            $fname = $u->fields['firstname'];
            $rname = $u->fields['realname'];
            $name = trim($fname) ? trim($fname . ' ' . $rname) : $u->fields['name'];
            
            // Se for e-mail, extrai o nome
            if (strpos($name, '@') !== false) {
                $name = explode('@', $name)[0];
                $name = ucwords(str_replace(['.', '_', '-'], ' ', $name));
            } else {
                // Limpa o nome longo
                $name = str_replace(' - Ativa Locação', '', $name);
                $parts = explode(' ', trim($name));
                if (count($parts) > 1) {
                    $name = $parts[0] . ' ' . end($parts);
                } else {
                    $name = $parts[0];
                }
            }
            
            $cargo = '';
            if ($u->fields['usertitles_id'] > 0) {
                $ut = new UserTitle();
                if ($ut->getFromDB($u->fields['usertitles_id'])) {
                    $cargo = $ut->fields['name'];
                }
            }
            
            $setor = '';
            global $DB;
            $grp_iter = $DB->request([
                'SELECT' => ['g.name'],
                'FROM'   => 'glpi_groups_users AS gu',
                'INNER JOIN' => [
                    'glpi_groups AS g' => ['ON' => ['gu' => 'groups_id', 'g' => 'id']]
                ],
                'WHERE'  => ['gu.users_id' => $author_id],
                'LIMIT'  => 1
            ])->current();
            if ($grp_iter) {
                $setor = $grp_iter['name'];
            }
            
            $filial = '';
            if ($u->fields['locations_id'] > 0) {
                $loc = new Location();
                if ($loc->getFromDB($u->fields['locations_id'])) {
                    $filial = $loc->fields['name'];
                }
            }
            
            $users_cache[$author_id] = [
                'name' => strtoupper($name),
                'cargo' => $cargo,
                'setor' => $setor,
                'filial' => $filial
            ];
        } else {
            $users_cache[$author_id] = [
                'name' => 'SISTEMA',
                'cargo' => '',
                'setor' => '',
                'filial' => ''
            ];
        }
    }
    
    $item['author'] = $users_cache[$author_id]['name'];
    $item['cargo'] = $users_cache[$author_id]['cargo'];
    $item['setor'] = $users_cache[$author_id]['setor'];
    $item['filial'] = $users_cache[$author_id]['filial'];
    
    // Regra de Ouro (Revisada Definitiva):
    // Verifica primeiro se a mensagem possui a nossa tag de perfil inserida no envio
    if (strpos($item['content'], '<!-- ATENDENTE_REPLY -->') !== false) {
        $item['is_requester'] = false;
        $item['content'] = str_replace('<!-- ATENDENTE_REPLY -->', '', $item['content']);
    } elseif (strpos($item['content'], '<!-- USER_REPLY -->') !== false) {
        $item['is_requester'] = true;
        $item['content'] = str_replace('<!-- USER_REPLY -->', '', $item['content']);
    } else {
        // Fallback para mensagens antigas ou do GLPI nativo
        if ($item['type'] == 'Ticket') {
            $item['is_requester'] = true;
        } else {
            $item['is_requester'] = ($author_id == $requester_id);
        }
    }
    
    // Anexos
    $item['documents'] = [];
    $d_iterator = $DB->request([
        'SELECT' => ['d.id', 'd.filename', 'd.filepath'],
        'FROM' => 'glpi_documents_items AS di',
        'INNER JOIN' => [
            'glpi_documents AS d' => ['ON' => ['di' => 'documents_id', 'd' => 'id']]
        ],
        'WHERE' => [
            'di.itemtype' => $item['type'],
            'di.items_id' => $item['id']
        ]
    ]);
    foreach ($d_iterator as $doc) {
        $item['documents'][] = $doc;
    }
}
unset($item);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamado #<?=$ticket_id?> - Ativa Locação</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        html, body { height: 100vh; overflow: hidden; background-color: #F7F8FA; color: #333; }
        a { text-decoration: none; color: inherit; }
        
        /* Topbar reutilizada */
        .topbar { display: flex; justify-content: space-between; align-items: center; background-color: #FFFFFF; padding: 0 40px; height: 70px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .topbar-left { display: flex; align-items: center; }
        .logo { display: flex; align-items: center; font-weight: 800; font-size: 20px; color: #0b457e; margin-right: 40px; }
        .nav-links a { display: flex; align-items: center; padding: 25px 0; color: #555; font-weight: 600; font-size: 14px; border-bottom: 2px solid transparent; }
        .nav-links a.active { color: #333; border-bottom: 2px solid #333; }
        .nav-links a i { margin-right: 8px; }
        .topbar-right { display: flex; align-items: center; }
        .search-icon { font-size: 16px; color: #666; margin-right: 20px; cursor: pointer; }
        .user-profile { display: flex; align-items: center; position: relative; cursor: pointer; padding-bottom: 15px; margin-bottom: -15px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #E2E4E8; display: flex; align-items: center; justify-content: center; color: #666; margin-right: 12px; font-size: 14px; background-size: cover; background-position: center; overflow: hidden; }
        .user-name { font-size: 14px; color: #333; }
        .dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background-color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-radius: 8px; padding: 10px 0; min-width: 220px; z-index: 100; border: 1px solid #EEE; }
        .dropdown-menu::before { content: ''; position: absolute; top: -20px; left: 0; width: 100%; height: 20px; }
        .user-profile:hover .dropdown-menu { display: block; }
        .dropdown-menu a { display: block; padding: 12px 20px; font-size: 14px; color: #444; font-weight: 500; }
        .dropdown-menu a:hover { background-color: #F7F8FA; color: #0b457e; }
        .dropdown-menu i { margin-right: 12px; width: 16px; text-align: center; color: #888; }
        
        /* Container principal */
        .container { display: flex; flex-direction: column; max-width: 1400px; margin: 0 auto; padding: 0 30px; height: calc(100vh - 70px); }
        
        /* Cabeçalho do Chamado */
        .ticket-header-card { flex-shrink: 0; margin-bottom: 0; padding: 25px 0; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: flex-end; }
        .breadcrumb { font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px; }
        .ticket-title { font-size: 26px; font-weight: 800; color: #333; }
        .ticket-title span { color: #555A6B; }
        
        .ticket-meta { display: flex; align-items: center; gap: 15px; }
        .ticket-date { font-size: 14px; font-weight: 600; color: #111; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 12px; background: #F3F4F6; color: #4B5563; }
        .status-1, .status-2 { background: #FEF3C7; color: #D97706; }
        .status-3, .status-4 { background: #DBEAFE; color: #2563EB; }
        .status-5 { background: #D1FAE5; color: #059669; }
        .status-6 { background: #FEF3C7; color: #D97706; } /* Finalizado em Amarelo */
        .status-deleted { background: #FEE2E2; color: #DC2626; }
        
        .btn-close-ticket { background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 12px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; margin-left: 10px; }
        .btn-close-ticket:hover { background: #FECACA; }

        /* Modal Overlay */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        
        /* Modal Content */
        .modal-content { background: #FFF; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; }
        .modal-content h3 { margin-top: 0; margin-bottom: 15px; color: #1A1F36; font-size: 20px; font-weight: 700; }
        .modal-content p { color: #4B5563; font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
        .modal-actions { display: flex; justify-content: center; gap: 15px; }
        .modal-btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; transition: all 0.2s; }
        .modal-btn-cancel { background: #F3F4F6; color: #4B5563; }
        .modal-btn-cancel:hover { background: #E5E7EB; }
        .modal-btn-confirm { background: #DC2626; color: #FFF; }
        .modal-btn-confirm:hover { background: #B91C1C; }
        
        /* Linha do Tempo (Chat) */
        .timeline-box { flex: 1; overflow-y: auto; padding: 25px 15px 25px 0; margin-right: -15px; }
        .timeline-box::-webkit-scrollbar { width: 8px; }
        .timeline-box::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        .timeline-item { display: flex; margin-bottom: 25px; width: 100%; align-items: flex-end; }
        .timeline-item.right { flex-direction: row-reverse; }
        
        .tl-avatar { width: 45px; height: 45px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FFF; font-size: 20px; }
        .timeline-item.left .tl-avatar { background-color: #0b457e; margin-right: 15px; }
        .timeline-item.right .tl-avatar { background-color: #9CA3AF; margin-left: 15px; }
        
        .tl-content-wrap { flex: 1; min-width: 0; max-width: 80%; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .timeline-item.left .tl-content-wrap { background: #FFFFFF; border-radius: 20px 20px 20px 0; }
        .timeline-item.right .tl-content-wrap { background: #E8F0FE; border-radius: 20px 20px 0 20px; }
        
        .tl-header { display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center; }
        .timeline-item.right .tl-header { flex-direction: row-reverse; }
        
        .tl-author { font-size: 14px; font-weight: 700; color: #444; }
        .timeline-item.right .tl-author { color: #0b457e; }
        .tl-role { font-size: 10px; font-weight: 800; background-color: #059669; color: #FFF; padding: 2px 6px; border-radius: 4px; margin-left: 10px; position: relative; cursor: help; }
        
        .tl-role .tooltip-card { display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; background: #1e293b; color: #fff; padding: 12px; border-radius: 8px; width: 250px; z-index: 100; box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-size: 12px; text-transform: none; font-weight: 500; text-align: left; line-height: 1.4; }
        .tl-role:hover .tooltip-card { display: block; }
        .tooltip-card strong { color: #94a3b8; font-size: 10px; text-transform: uppercase; display: block; margin-top: 8px; }
        .tooltip-card strong:first-child { margin-top: 0; }
        .tooltip-card span { display: block; margin-bottom: 2px; }
        
        .tl-date { font-size: 12px; color: #888; font-weight: 500; }
        
        .tl-body { font-size: 14px; color: #4B5563; line-height: 1.6; }
        .tl-body p { margin-bottom: 10px; }
        .tl-body p:last-child { margin-bottom: 0; }
        .tl-body img { max-width: 100%; height: auto !important; border-radius: 6px; display: block; margin: 10px 0; }
        
        /* Anexos */
        .attachments { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .attachment-btn { display: inline-flex; align-items: center; border: 1px solid #D1D5DB; border-radius: 6px; padding: 8px 12px; font-size: 12px; font-weight: 600; color: #4B5563; background: #FFF; transition: all 0.2s; }
        .attachment-btn:hover { background: #F3F4F6; border-color: #9CA3AF; color: #111; }
        .attachment-btn i.fa-image, .attachment-btn i.fa-file { font-size: 14px; color: #6B7280; margin-right: 8px; }
        .attachment-btn i.fa-download { font-size: 12px; color: #9CA3AF; margin-left: 10px; }
        
        /* Formulário de Resposta */
        .reply-container { flex-shrink: 0; background: #FFFFFF; border-radius: 12px; padding: 15px 20px; box-shadow: 0 -4px 20px rgba(0,0,0,0.03); margin-top: 10px; margin-bottom: 15px; border: 1px solid #F3F4F6; }
        .reply-textarea { width: 100%; min-height: 120px; border: 1px solid #E5E7EB; border-radius: 8px; padding: 15px; font-size: 14px; font-family: inherit; resize: vertical; margin-bottom: 15px; outline: none; transition: border-color 0.3s; background: #F9FAFB; }
        .reply-textarea:focus { border-color: #0b457e; background: #FFFFFF; }
        .reply-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 15px; }
        .left-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
        .btn-attach { background: #F3F4F6; color: #4B5563; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.3s; display: inline-flex; align-items: center; gap: 8px; margin: 0; }
        .btn-attach:hover { background: #E5E7EB; }
        .btn-send { background: #0b457e; color: #FFF; border: none; padding: 10px 30px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.3s; display: inline-flex; align-items: center; gap: 8px; margin-left: auto; }
        .btn-send:hover { background: #08335e; }
        .file-list { display: flex; flex-direction: column; gap: 5px; width: 100%; margin-top: 10px; }
        .file-item { display: flex; align-items: center; justify-content: space-between; background: #F9FAFB; padding: 8px 12px; border-radius: 6px; font-size: 13px; color: #374151; border: 1px solid #E5E7EB; }
        .file-item i.remove-file { color: #EF4444; cursor: pointer; margin-left: 10px; }
        
        /* Drag and Drop Global */
        .drag-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 69, 126, 0.9); color: #FFF; z-index: 99999; justify-content: center; align-items: center; font-size: 28px; font-weight: bold; flex-direction: column; pointer-events: none; }
        .drag-overlay.active { display: flex; }
        
        /* Ajustes do TinyMCE */
        .tox-tinymce { border-radius: 8px !important; border-color: #E5E7EB !important; }
    </style>
</head>
<body>
    <div id="drag-overlay" class="drag-overlay">
        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 80px; margin-bottom: 20px;"></i>
        Solte os arquivos para anexar
    </div>

    <div class="topbar">
        <div class="topbar-left">
            <div class="logo">
                <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="height: 45px;">
            </div>
            <nav class="nav-links">
                <a href="<?=$CFG_GLPI['root_doc']?>/" class="active"><i class="fa-regular fa-window-maximize"></i> Chamados</a>
            </nav>
        </div>
        <div class="topbar-right">
            <div class="search-icon">
                <i class="fa-solid fa-search"></i>
            </div>
            <div class="user-profile">
                <div class="avatar" style="<?= isset($avatar_url) && $avatar_url ? 'background-image: url('.$avatar_url.');' : '' ?>"><?= isset($avatar_url) && $avatar_url ? '' : '<i class="fa-solid fa-user"></i>' ?></div>
                <span class="user-name">Olá, <strong><?= htmlspecialchars($primeiro_nome) ?></strong> <i class="fa-solid fa-caret-down" style="font-size: 10px; margin-left: 5px; color: #888;"></i></span>
                
                <div class="dropdown-menu">
                    <?php if ($has_super_admin): ?>
                        <a href="?switch_to_admin=1&id=<?=$ticket_id?>"><i class="fa-solid fa-user-shield"></i> Trocar para Admin</a>
                    <?php endif; ?>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?custom_profile=1"><i class="fa-solid fa-gear"></i> Minhas configurações</a>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="ticket-header-card">
            <div>
                <div class="breadcrumb">
                    CHAMADOS / <?=htmlspecialchars(str_replace('>', '-', $ticket_data['category_name'] ?? 'SEMCATEGORIA'))?>
                </div>
                <div class="ticket-title">
                    <span>#<?=$ticket_data['id']?> -</span> <?=htmlspecialchars($ticket_data['name'])?>
                </div>
            </div>
            <div class="ticket-meta">
                <div style="display: flex; align-items: center;">
                    <div class="status-badge <?=$status_class?>"><?=$status_str?></div>
                    <?php if ($ticket_data['status'] != 5 && $ticket_data['status'] != 6 && $ticket_data['is_deleted'] == 0): ?>
                        <button type="button" class="btn-close-ticket" onclick="openCloseModal()">
                            <i class="fa-solid fa-check-circle"></i> Encerrar Chamado
                        </button>
                    <?php endif; ?>
                </div>
                <div class="ticket-date"><?=date('d/m/Y H:i', strtotime($ticket_data['date']))?></div>
            </div>
        </div>

        <div class="timeline-box">
            <?php foreach($timeline as $item): 
                $alignment = $item['is_requester'] ? 'right' : 'left';
            ?>
                <div class="timeline-item <?=$alignment?>">
                    <div class="tl-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="tl-content-wrap">
                        <div class="tl-header">
                            <div>
                                <span class="tl-author"><?=htmlspecialchars($item['author'])?></span>
                                <?php if(!$item['is_requester']): ?>
                                    <span class="tl-role">
                                        ATENDENTE
                                        <div class="tooltip-card">
                                            <strong>Cargo</strong>
                                            <span><?=htmlspecialchars($item['cargo'] ?: 'Não informado')?></span>
                                            <strong>Setor</strong>
                                            <span><?=htmlspecialchars($item['setor'] ?: 'Não informado')?></span>
                                            <strong>Filial</strong>
                                            <span><?=htmlspecialchars($item['filial'] ?: 'Não informado')?></span>
                                        </div>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="tl-date"><?=date('d/m/Y H:i', strtotime($item['date']))?></div>
                        </div>
                        <div class="tl-body">
                            <!-- Tratando HTML do GLPI (o GLPI armazena followups e tickets com HTML usando o rich text) -->
                            <?=html_entity_decode($item['content'])?>
                        </div>
                        
                        <?php if(!empty($item['documents'])): ?>
                            <div class="attachments">
                                <?php foreach($item['documents'] as $doc): ?>
                                    <a href="<?=$CFG_GLPI['root_doc']?>/front/document.send.php?docid=<?=$doc['id']?>" target="_blank" class="attachment-btn">
                                        <i class="fa-solid fa-file"></i>
                                        <?=htmlspecialchars(strlen($doc['filename']) > 20 ? substr($doc['filename'], 0, 17).'...' : $doc['filename'])?>
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulário de Resposta -->
        <?php if ($ticket_data['status'] != 5 && $ticket_data['status'] != 6 && $ticket_data['is_deleted'] == 0): ?>
        <div class="reply-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tickets_id" value="<?=$ticket_id?>">
                <input type="hidden" name="_glpi_csrf_token" value="<?=Session::getNewCSRFToken()?>">
                
                <textarea id="reply-textarea" name="content" class="reply-textarea" placeholder="Digite sua resposta aqui..." required></textarea>
                
                <div class="file-list" id="file-list"></div>
                
                <div class="reply-actions">
                    <div class="left-actions">
                        <label class="btn-attach" onclick="addFileInput()">
                            <i class="fa-solid fa-paperclip"></i> Adicionar Anexo
                        </label>
                    </div>
                    
                    <button type="submit" name="add_reply" class="btn-send">
                        <i class="fa-solid fa-paper-plane"></i> Enviar
                    </button>
                </div>
                
                <div id="file-inputs-container" style="display:none;"></div>
            </form>
        </div>

        <script>
            let fileInputCounter = 0;
            
            function addFileInput() {
                const container = document.getElementById('file-inputs-container');
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'anexos[]';
                input.multiple = true;
                input.id = 'file-input-' + fileInputCounter;
                input.style.display = 'none';
                
                input.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        const fileList = document.getElementById('file-list');
                        for(let i=0; i<this.files.length; i++) {
                            const fileItem = document.createElement('div');
                            fileItem.className = 'file-item';
                            fileItem.innerHTML = '<span><i class="fa-solid fa-file" style="margin-right:8px;"></i>' + this.files[i].name + '</span> <i class="fa-solid fa-times remove-file" onclick="removeFileInput(\'' + input.id + '\', this)"></i>';
                            fileList.appendChild(fileItem);
                        }
                    }
                });
                
                container.appendChild(input);
                input.click();
                fileInputCounter++;
            }
            
            function removeFileInput(inputId, iconElement) {
                const input = document.getElementById(inputId);
                if (input) input.remove();
                iconElement.parentElement.remove();
            }
            
            // Drag & Drop e CTRL+V Globais
            const dragOverlay = document.getElementById('drag-overlay');
            let dragCounter = 0; // Para evitar piscar ao entrar em filhos
            
            document.addEventListener('dragenter', function(e) {
                e.preventDefault();
                dragCounter++;
                dragOverlay.classList.add('active');
            });
            
            document.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dragCounter--;
                if (dragCounter === 0) {
                    dragOverlay.classList.remove('active');
                }
            });
            
            document.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
            
            document.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dragCounter = 0;
                dragOverlay.classList.remove('active');
                
                if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                    handleGlobalFiles(e.dataTransfer.files);
                }
            });
            
            document.addEventListener('paste', function(e) {
                // Se o TinyMCE estiver focado num texto simples, ele vai processar,
                // mas se houver arquivos (Print Screen), nós capturamos globalmente.
                if (e.clipboardData && e.clipboardData.files.length > 0) {
                    handleGlobalFiles(e.clipboardData.files);
                }
            });
            
            function handleGlobalFiles(files) {
                const container = document.getElementById('file-inputs-container');
                const fileList = document.getElementById('file-list');
                
                const dataTransfer = new DataTransfer();
                for (let i = 0; i < files.length; i++) {
                    dataTransfer.items.add(files[i]);
                }
                
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'anexos[]';
                input.multiple = true;
                input.id = 'file-input-' + fileInputCounter;
                input.style.display = 'none';
                input.files = dataTransfer.files;
                
                for (let i=0; i<files.length; i++) {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    
                    // Mostra thumbnail se for imagem
                    let iconOrThumb = '<i class="fa-solid fa-file" style="margin-right:8px;"></i>';
                    if (files[i].type.startsWith('image/')) {
                        iconOrThumb = '<i class="fa-solid fa-image" style="margin-right:8px; color: #0b457e;"></i>';
                    }
                    
                    fileItem.innerHTML = '<span>' + iconOrThumb + files[i].name + '</span> <i class="fa-solid fa-times remove-file" onclick="removeFileInput(\'' + input.id + '\', this)"></i>';
                    fileList.appendChild(fileItem);
                }
                
                container.appendChild(input);
                fileInputCounter++;
                
                // Rola para a lista de arquivos para o usuário ver
                fileList.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }
        </script>
        
        <!-- Editor Rich Text Nativo (TinyMCE) via CDN (mesma rede dos ícones que já funciona) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
        
        <script>
            window.onload = function() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.init({
                        selector: '#reply-textarea',
                        plugins: 'image link lists table autolink paste',
                        toolbar: 'bold italic underline forecolor | alignleft aligncenter alignright alignjustify | numlist bullist | blocks | link image table | removeformat',
                        toolbar_location: 'bottom',
                        menubar: false,
                        statusbar: false,
                        paste_data_images: false, // Desativa imagens inline nativas
                        height: 140,
                        branding: false,
                        content_style: "html { cursor: text; } body { font-family: Inter, sans-serif; font-size: 14px; } img { max-width: 100%; max-height: 180px; object-fit: contain; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 5px 0; cursor: pointer; }",
                        setup: function (editor) {
                            editor.on('init', function() {
                                editor.getDoc().documentElement.addEventListener('click', function() {
                                    editor.focus();
                                });
                            });
                            
                            editor.on('change', function () {
                                editor.save();
                            });
                            
                            // Redireciona a imagem colada no editor para a lista de anexos global
                            editor.on('paste', function (e) {
                                if (e.clipboardData && e.clipboardData.files.length > 0) {
                                    e.preventDefault();
                                    if (typeof handleGlobalFiles === 'function') {
                                        handleGlobalFiles(e.clipboardData.files);
                                    }
                                }
                            });
                            
                            // Redireciona a imagem arrastada no editor para a lista de anexos global
                            editor.on('drop', function (e) {
                                if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (typeof handleGlobalFiles === 'function') {
                                        handleGlobalFiles(e.dataTransfer.files);
                                    }
                                }
                            });
                        }
                    });
                } else {
                    console.error("TinyMCE não conseguiu ser carregado.");
                }
            };
        </script>
        <?php else: ?>
        <div class="reply-container" style="text-align: center; padding: 40px 20px; background: #F9FAFB; border: 1px dashed #D1D5DB; margin-top: 20px; border-radius: 12px;">
            <i class="fa-solid fa-lock" style="font-size: 28px; color: #9CA3AF; margin-bottom: 12px;"></i>
            <h3 style="margin: 0 0 5px 0; color: #4B5563; font-size: 16px;">Chamado Encerrado</h3>
            <p style="margin: 0; font-size: 14px; color: #6B7280;">Este chamado já foi encerrado e não pode receber novas interações.</p>
        </div>
        <?php endif; ?>

    </div>
    <div class="modal-overlay" id="close-modal">
        <div class="modal-content">
            <h3>Encerrar Chamado</h3>
            <p>Você tem certeza que deseja encerrar este chamado? Esta ação não pode ser desfeita.</p>
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeCloseModal()">Não</button>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="close_ticket" value="1" class="modal-btn modal-btn-confirm">Sim</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCloseModal() {
            document.getElementById('close-modal').classList.add('active');
        }
        function closeCloseModal() {
            document.getElementById('close-modal').classList.remove('active');
        }
    </script>
</body>
</html>
