<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}
Session::checkLoginUser();

if (isset($_POST['add_reply']) && isset($_POST['tickets_id']) && !empty($_POST['content'])) {
    
    $ticket_id = intval($_POST['tickets_id']);
    $content = $_POST['content'];
    $uid = Session::getLoginUserID();
    
    // Identifica qual o perfil ativo NO MOMENTO do envio (para separar Atendente e Usuário na mesma conta)
    if (isset($_SESSION['glpiactiveprofile']) && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
        $content .= "\n<!-- ATENDENTE_REPLY -->";
    } else {
        $content .= "\n<!-- USER_REPLY -->";
    }
    
    // 1. Adiciona o Followup
    $fup = new ITILFollowup();
    $fup_id = $fup->add([
        'itemtype' => 'Ticket',
        'items_id' => $ticket_id,
        'content'  => $content,
        'users_id' => $uid,
        'is_private' => 0,
        'requesttypes_id' => 1 // Helpdesk
    ]);
    
    // 2. Processa os Anexos (se houver)
    if ($fup_id && isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
        $doc = new Document();
        $docitem = new Document_Item();
        
        $files = $_FILES['anexos'];
        $count = count($files['name']);
        
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] == UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $original_name = $files['name'][$i];
                
                // Gera nome único para evitar colisão na pasta temporária
                $unique_name = uniqid('anexo_') . '_' . $original_name;
                $glpi_tmp_path = GLPI_TMP_DIR . '/' . $unique_name;
                
                // Move o arquivo PHP temporário para a pasta temporária do GLPI
                if (move_uploaded_file($tmp_name, $glpi_tmp_path)) {
                    // Adiciona o documento no GLPI
                    $doc_input = [
                        'name' => $original_name, // Nome real para exibir no painel
                        'documentcategories_id' => 0,
                        'entities_id' => 0,
                        '_filename' => [$unique_name] // GLPI vai buscar em GLPI_TMP_DIR/unique_name
                    ];
                    
                    $new_doc_id = $doc->add($doc_input);
                    
                    // Vincula o documento ao Followup
                    if ($new_doc_id) {
                        $docitem->add([
                            'documents_id' => $new_doc_id,
                            'itemtype' => 'ITILFollowup',
                            'items_id' => $fup_id,
                            'users_id' => $uid,
                            'entities_id' => 0
                        ]);
                    }
                }
            }
        }
    }
    
    // Redireciona de volta para o chamado
    global $CFG_GLPI;
    header("Location: " . $CFG_GLPI['root_doc'] . "/front/ticket.form.php?id=" . $ticket_id);
    exit;
} else {
    global $CFG_GLPI;
    header("Location: " . $CFG_GLPI['root_doc'] . "/");
    exit;
}
