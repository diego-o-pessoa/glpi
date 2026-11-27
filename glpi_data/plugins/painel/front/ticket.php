<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}

Session::checkLoginUser();
global $DB, $CFG_GLPI;
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
        't.id', 't.name', 't.status', 't.date', 't.content', 't.is_deleted', 't.type', 't.priority',
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
                'name' => mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8'),
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
    $ini = preg_split('/\s+/u', trim($item['author'])) ?: ['?'];
    $item['initials'] = mb_strtoupper(mb_substr($ini[0], 0, 1) . (count($ini) > 1 ? mb_substr((string) end($ini), 0, 1) : ''));
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

$is_open = $ticket_data['status'] != 5 && $ticket_data['status'] != 6 && $ticket_data['is_deleted'] == 0;

// Departamento: grupo de quem abriu o chamado
$dept = '';
$dept_row = $DB->request([
    'SELECT' => ['g.name'],
    'FROM' => 'glpi_groups_users AS gu',
    'INNER JOIN' => ['glpi_groups AS g' => ['ON' => ['gu' => 'groups_id', 'g' => 'id']]],
    'WHERE' => ['gu.users_id' => $requester_id],
    'LIMIT' => 1
])->current();
if ($dept_row) {
    $dept = $dept_row['name'];
}

// Atendentes atribuidos
$techs = [];
foreach ($DB->request(['SELECT' => ['users_id'], 'FROM' => 'glpi_tickets_users', 'WHERE' => ['tickets_id' => $ticket_id, 'type' => 2]]) as $tr) {
    $tn = getUserName((int) $tr['users_id']);
    $techs[] = trim(str_replace(' - Ativa Locação', '', $tn));
}

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
        html, body { min-height: 100vh; background-color: #F5F6F8; color: #1F2937; }
        a { text-decoration: none; color: inherit; }

        /* Topbar reutilizada */
        .topbar { display: flex; justify-content: space-between; align-items: center; background-color: #FFFFFF; padding: 0 40px; height: 70px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .topbar-left { display: flex; align-items: center; }
        .logo { display: flex; align-items: center; font-weight: 800; font-size: 20px; color: #0b457e; margin-right: 40px; }
        .nav-links { display: flex; gap: 30px; }
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

        /* Pagina do chamado */
        .container { max-width: 1320px; margin: 0 auto; padding: 24px 40px 40px; }
        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6B7280; margin-bottom: 26px; }
        .breadcrumb a { color: #111827; font-weight: 500; }
        .breadcrumb a:hover { color: #0b457e; }
        .breadcrumb i { font-size: 10px; color: #9CA3AF; }

        .ticket-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; margin-bottom: 24px; }
        .ticket-meta { display: flex; align-items: center; gap: 14px; font-size: 12px; color: #6B7280; margin-bottom: 8px; }
        .ticket-meta b { color: #111827; font-weight: 700; }
        .status-pill { display: inline-flex; align-items: center; gap: 6px; }
        .status-pill::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #9CA3AF; }
        .status-1::before, .status-2::before { background: #F59E0B; }
        .status-3::before, .status-4::before { background: #3B82F6; }
        .status-5::before, .status-6::before { background: #10B981; }
        .status-deleted::before { background: #EF4444; }
        .ticket-title { font-size: 24px; font-weight: 700; color: #111827; line-height: 1.3; }
        .head-actions { display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
        .btn-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: #4B5563; font-size: 16px; transition: background 0.2s; }
        .btn-icon:hover { background: #E5E7EB; }
        .btn-close-ticket { background: #FFF; color: #059669; border: 1px solid #10B981; padding: 11px 18px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; }
        .btn-close-ticket:hover { background: #ECFDF5; }

        .ticket-grid { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 28px; align-items: start; }
        .card { background: #FFF; border-radius: 12px; border: 1px solid #EEF0F3; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .card-title { font-size: 15px; font-weight: 700; color: #111827; padding: 20px 22px; border-bottom: 1px solid #EEF0F3; }

        /* Conversa */
        .conversation { padding: 0 22px; }
        .msg { display: flex; gap: 14px; padding: 22px 0; border-bottom: 1px solid #F1F2F4; }
        .msg-avatar { width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #FFF; font-size: 12px; font-weight: 700; background: #9333EA; }
        .msg.is-staff .msg-avatar { background: #0b457e; }
        .msg-main { flex: 1; min-width: 0; }
        .msg-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 8px; }
        .msg-author { font-size: 14px; font-weight: 600; color: #4B5563; display: flex; align-items: center; gap: 8px; }
        .msg-date { font-size: 12px; color: #6B7280; white-space: nowrap; }
        .tl-role { font-size: 10px; font-weight: 700; background: #E0ECF8; color: #0b457e; padding: 2px 7px; border-radius: 10px; position: relative; cursor: help; }
        .tl-role.is-solution { background: #D1FAE5; color: #047857; }
        .tl-role .tooltip-card { display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; background: #1e293b; color: #fff; padding: 12px; border-radius: 8px; width: 250px; z-index: 100; box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-size: 12px; font-weight: 500; line-height: 1.4; }
        .tl-role:hover .tooltip-card { display: block; }
        .tooltip-card strong { color: #94a3b8; font-size: 10px; text-transform: uppercase; display: block; margin-top: 8px; }
        .tooltip-card strong:first-child { margin-top: 0; }
        .tooltip-card span { display: block; margin-bottom: 2px; }
        .tl-body { font-size: 14px; color: #111827; line-height: 1.6; overflow-wrap: anywhere; }
        .tl-body p { margin-bottom: 8px; }
        .tl-body p:last-child { margin-bottom: 0; }
        .tl-body img { max-width: 100%; height: auto !important; border-radius: 6px; display: block; margin: 10px 0; }

        /* Anexos */
        .attachments { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .attachment-btn { display: inline-flex; align-items: center; border: 1px solid #E5E7EB; border-radius: 8px; padding: 7px 12px; font-size: 12px; font-weight: 500; color: #374151; background: #FFF; transition: all 0.2s; }
        .attachment-btn:hover { background: #F9FAFB; border-color: #D1D5DB; }
        .attachment-btn i.fa-file { font-size: 13px; color: #6B7280; margin-right: 8px; }
        .attachment-btn i.fa-download { font-size: 11px; color: #9CA3AF; margin-left: 10px; }

        /* Resposta */
        .reply-container { padding: 22px; }
        .reply-label { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 10px; }
        .reply-textarea { width: 100%; min-height: 150px; border: 1px solid #D1D5DB; border-radius: 10px; padding: 14px; font-size: 14px; font-family: inherit; resize: vertical; outline: none; background: #FFF; }
        .reply-textarea:focus { border-color: #0b457e; }
        .reply-actions { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 22px; padding-top: 22px; border-top: 1px solid #F1F2F4; }
        .right-actions { display: flex; gap: 10px; }
        .btn-attach, .btn-cancel { background: #FFF; color: #111827; border: 1px solid #D1D5DB; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        .btn-attach:hover, .btn-cancel:hover { background: #F9FAFB; }
        .btn-send { background: #0b457e; color: #FFF; border: none; padding: 10px 22px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.2s; }
        .btn-send:hover { background: #08335e; }
        .file-list { display: flex; flex-direction: column; gap: 6px; margin-top: 10px; }
        .file-list:empty { display: none; }
        .file-item { display: flex; align-items: center; justify-content: space-between; background: #F9FAFB; padding: 8px 12px; border-radius: 8px; font-size: 13px; color: #374151; border: 1px solid #E5E7EB; }
        .file-item i.remove-file { color: #EF4444; cursor: pointer; margin-left: 10px; }
        .closed-box { margin: 22px; text-align: center; padding: 30px 20px; background: #F9FAFB; border: 1px dashed #D1D5DB; border-radius: 10px; color: #6B7280; font-size: 14px; }
        .closed-box i { font-size: 24px; color: #9CA3AF; margin-bottom: 10px; display: block; }
        .closed-box b { display: block; color: #374151; font-size: 15px; margin-bottom: 4px; }

        /* Informacoes */
        .info-card { padding: 20px 22px; }
        .info-card h3 { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 18px; }
        .info-item { margin-bottom: 16px; }
        .info-item small { display: block; font-size: 12px; color: #111827; font-weight: 500; margin-bottom: 5px; }
        .info-item span { font-size: 14px; color: #4B5563; }
        .info-sep { border: 0; border-top: 1px solid #EEF0F3; margin: 18px 0; }
        .info-toggle { width: 100%; display: flex; justify-content: space-between; align-items: center; background: none; border: 0; cursor: pointer; font-size: 14px; font-weight: 600; color: #374151; padding: 0; }
        .info-toggle i { font-size: 12px; color: #9CA3AF; transition: transform 0.2s; }
        .info-more { margin-top: 16px; }
        .info-more[hidden] { display: none; }
        .info-toggle[aria-expanded="false"] i { transform: rotate(180deg); }

        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: #FFF; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; }
        .modal-content h3 { margin-bottom: 15px; color: #1A1F36; font-size: 20px; font-weight: 700; }
        .modal-content p { color: #4B5563; font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
        .modal-actions { display: flex; justify-content: center; gap: 15px; }
        .modal-btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; }
        .modal-btn-cancel { background: #F3F4F6; color: #4B5563; }
        .modal-btn-confirm { background: #059669; color: #FFF; }

        /* Drag and Drop Global */
        .drag-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 69, 126, 0.9); color: #FFF; z-index: 99999; justify-content: center; align-items: center; font-size: 28px; font-weight: bold; flex-direction: column; pointer-events: none; }
        .drag-overlay.active { display: flex; }
        .tox-tinymce { border-radius: 10px !important; border-color: #D1D5DB !important; }

        @media (max-width: 900px) {
            .container { padding: 20px 16px; }
            .ticket-grid { grid-template-columns: 1fr; }
            .ticket-head { flex-direction: column; align-items: flex-start; }
            .topbar { padding: 0 16px; }
            .logo { margin-right: 16px; }
            .nav-links { gap: 16px; }
            .user-name, .search-icon { display: none; }
        }
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
                <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?projects=1"><i class="fa-solid fa-diagram-project"></i> Projetos</a>
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
                    <a href="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/profile.php"><i class="fa-solid fa-gear"></i> Minhas configurações</a>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?=$CFG_GLPI['root_doc']?>/">Chamados</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span><?=htmlspecialchars(str_replace('>', '-', $ticket_data['category_name'] ?? 'Sem categoria'))?></span>
        </div>

        <div class="ticket-head">
            <div>
                <div class="ticket-meta">
                    <b>#<?=$ticket_data['id']?></b>
                    <span class="status-pill <?=$status_class?>"><?=$status_str?></span>
                    <span><?=date('d/m/Y H:i', strtotime($ticket_data['date']))?></span>
                </div>
                <h1 class="ticket-title"><?=htmlspecialchars($ticket_data['name'])?></h1>
            </div>
            <div class="head-actions">
                <a class="btn-icon" href="<?=$CFG_GLPI['root_doc']?>/" title="Voltar para os chamados"><i class="fa-solid fa-reply"></i></a>
                <?php if ($is_open): ?>
                    <button type="button" class="btn-close-ticket" onclick="openCloseModal()">Encerrar Chamado</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="ticket-grid">
        <div class="card">
            <div class="card-title">Conversa</div>
            <div class="conversation">
            <?php foreach($timeline as $item):
                $ts = strtotime($item['date']);
                $when = date('Y-m-d', $ts) === date('Y-m-d') ? date('H:i', $ts) : date('d/m/Y H:i', $ts);
            ?>
                <div class="msg <?= $item['is_requester'] ? '' : 'is-staff' ?>">
                    <div class="msg-avatar"><?=htmlspecialchars($item['initials'])?></div>
                    <div class="msg-main">
                        <div class="msg-head">
                            <div class="msg-author">
                                <?=htmlspecialchars($item['author'])?>
                                <?php if ($item['type'] === 'ITILSolution'): ?>
                                    <span class="tl-role is-solution">Solução</span>
                                <?php elseif(!$item['is_requester']): ?>
                                    <span class="tl-role">
                                        T.I.
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
                            <div class="msg-date" title="<?=date('d/m/Y H:i', $ts)?>"><?=$when?></div>
                        </div>
                        <div class="tl-body">
                            <?=html_entity_decode($item['content'])?>
                        </div>
                        <?php if(!empty($item['documents'])): ?>
                            <div class="attachments">
                                <?php foreach($item['documents'] as $doc): ?>
                                    <a href="<?=$CFG_GLPI['root_doc']?>/front/document.send.php?docid=<?=$doc['id']?>" target="_blank" class="attachment-btn" title="<?=htmlspecialchars($doc['filename'])?>">
                                        <i class="fa-solid fa-file"></i>
                                        <?=htmlspecialchars(mb_strlen($doc['filename']) > 28 ? mb_substr($doc['filename'], 0, 25).'...' : $doc['filename'])?>
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
        <?php if ($is_open): ?>
        <div class="reply-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tickets_id" value="<?=$ticket_id?>">
                <input type="hidden" name="_glpi_csrf_token" value="<?=Session::getNewCSRFToken()?>">

                <div class="reply-label">Sua resposta</div>
                <textarea id="reply-textarea" name="content" class="reply-textarea" placeholder="Responder..." required></textarea>

                <div class="file-list" id="file-list"></div>

                <div class="reply-actions">
                    <label class="btn-attach" onclick="addFileInput()">
                        <i class="fa-regular fa-file-lines"></i> Anexar arquivos
                    </label>
                    <div class="right-actions">
                        <button type="button" class="btn-cancel" onclick="clearReply()">Cancelar</button>
                        <button type="submit" name="add_reply" class="btn-send">Enviar</button>
                    </div>
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
                        toolbar: 'undo redo | bold italic underline | bullist numlist | link',
                        toolbar_location: 'bottom',
                        menubar: false,
                        statusbar: false,
                        paste_data_images: false, // Desativa imagens inline nativas
                        height: 170,
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
        <div class="closed-box">
            <i class="fa-solid fa-lock"></i>
            <b>Chamado encerrado</b>
            Este chamado já foi encerrado e não pode receber novas interações.
        </div>
        <?php endif; ?>
        </div>

        <aside class="card info-card">
            <h3>Informações</h3>
            <div class="info-item"><small>Departamento</small><span><?=htmlspecialchars($dept ?: 'Não informado')?></span></div>
            <div class="info-item"><small>Categoria</small><span><?=htmlspecialchars(str_replace('>', '-', $ticket_data['category_name'] ?? '') ?: 'Sem categoria')?></span></div>
            <div class="info-item"><small>Prioridade</small><span><?=htmlspecialchars(Ticket::getPriorityName((int) $ticket_data['priority']))?></span></div>
            <hr class="info-sep">
            <button type="button" class="info-toggle" aria-expanded="true" onclick="var m=document.getElementById('info-more');m.hidden=!m.hidden;this.setAttribute('aria-expanded', String(!m.hidden));">
                Mais detalhes <i class="fa-solid fa-chevron-up"></i>
            </button>
            <div class="info-more" id="info-more">
                <div class="info-item"><small>Tipo de Chamado:</small><span><?= (int) $ticket_data['type'] === 2 ? 'Requisição' : 'Incidente' ?></span></div>
                <div class="info-item"><small>Aberto em</small><span><?=date('d/m/Y H:i', strtotime($ticket_data['date']))?></span></div>
                <?php if ($techs): ?><div class="info-item"><small>Atendente</small><span><?=htmlspecialchars(implode(', ', $techs))?></span></div><?php endif; ?>
            </div>
        </aside>
        </div>

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
        function clearReply() {
            if (typeof tinymce !== 'undefined' && tinymce.get('reply-textarea')) {
                tinymce.get('reply-textarea').setContent('');
            }
            var ta = document.getElementById('reply-textarea');
            if (ta) { ta.value = ''; }
            var fl = document.getElementById('file-list');
            if (fl) { fl.innerHTML = ''; }
            var fc = document.getElementById('file-inputs-container');
            if (fc) { fc.innerHTML = ''; }
        }
        function openCloseModal() {
            document.getElementById('close-modal').classList.add('active');
        }
        function closeCloseModal() {
            document.getElementById('close-modal').classList.remove('active');
        }
    </script>
</body>
</html>
