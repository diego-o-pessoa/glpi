<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}
Session::checkLoginUser();

function debug_log($msg) {
    file_put_contents('/tmp/debug_ticket.txt', date('Y-m-d H:i:s') . ' - ' . $msg . "\n", FILE_APPEND);
}

debug_log("Script loaded");

if (isset($_POST['add'])) {
    debug_log("Received POST data. Name: " . ($_POST['name'] ?? ''));
    Session::checkCSRF($_POST);
    debug_log("CSRF check passed.");
    
    $ticket = new Ticket();
    
    $input = [
        'name'                => $_POST['name'] ?? 'Chamado sem assunto',
        'content'             => $_POST['content'] ?? '',
        'itilcategories_id'   => isset($_POST['itilcategories_id']) ? intval($_POST['itilcategories_id']) : 0,
        'priority'            => isset($_POST['priority']) ? intval($_POST['priority']) : 3,
        'type'                => isset($_POST['type']) ? intval($_POST['type']) : 1, // Incident defaults to 1
        'users_id_recipient'  => Session::getLoginUserID(),
        '_users_id_requester' => Session::getLoginUserID(),
        'requesttypes_id'     => 1, // Helpdesk
        'entities_id'         => $_SESSION['glpiactive_entity'] ?? 0
    ];
    
    // 1. Cria o chamado
    $new_ticket_id = $ticket->add($input);
    debug_log("Ticket created with ID: " . $new_ticket_id);
    
    // 2. Processa os Anexos (se houver)
    if ($new_ticket_id && isset($_FILES['filename']) && !empty($_FILES['filename']['name'][0])) {
        debug_log("Processing attachments...");
        $doc = new Document();
        $docitem = new Document_Item();
        
        $files = $_FILES['filename'];
        $count = count($files['name']);
        
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] == UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $original_name = $files['name'][$i];
                
                // Gera nome único para evitar colisão na pasta temporária
                $unique_name = uniqid('anexo_') . '_' . $original_name;
                $glpi_tmp_path = GLPI_TMP_DIR . '/' . $unique_name;
                
                if (move_uploaded_file($tmp_name, $glpi_tmp_path)) {
                    $doc_input = [
                        'name' => $original_name, // Nome real para exibir no painel
                        'documentcategories_id' => 0,
                        'entities_id' => 0,
                        '_filename' => [$unique_name] // GLPI vai buscar em GLPI_TMP_DIR/unique_name
                    ];
                    
                    $new_doc_id = $doc->add($doc_input);
                    
                    if ($new_doc_id) {
                        $docitem->add([
                            'documents_id' => $new_doc_id,
                            'itemtype'     => 'Ticket',
                            'items_id'     => $new_ticket_id,
                            'users_id'     => Session::getLoginUserID(),
                            'entities_id'  => 0
                        ]);
                    }
                }
            }
        }
    }
    
    global $CFG_GLPI;
    $url = $CFG_GLPI['root_doc'] . '/front/ticket.form.php?id=' . $new_ticket_id;
    debug_log("Returning URL -> " . $url);
    echo $url;
    exit;
} else {
    global $CFG_GLPI;
    debug_log("No add parameter. Redirecting to root.");
    echo $CFG_GLPI['root_doc'] . '/';
    exit;
}
