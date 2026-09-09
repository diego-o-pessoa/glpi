<?php
function plugin_painel_install() {
    return true;
}

function plugin_painel_uninstall() {
    return true;
}

function plugin_painel_pre_item_add_followup(CommonDBTM $fup) {
    if (isset($_SESSION['glpiactiveprofile']) && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
        if (!isset($fup->input['content'])) return;
        if (strpos($fup->input['content'], '<!-- ATENDENTE_REPLY -->') === false) {
            $fup->input['content'] .= "\n<!-- ATENDENTE_REPLY -->";
        }
    } else {
        if (!isset($fup->input['content'])) return;
        if (strpos($fup->input['content'], '<!-- USER_REPLY -->') === false) {
            $fup->input['content'] .= "\n<!-- USER_REPLY -->";
        }
    }
}

function plugin_painel_post_init() {
    global $CFG_GLPI;
    if (isset($_SESSION['glpiactiveprofile']) && $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
        $uri = $_SERVER['REQUEST_URI'];

        // Se estiver acessando telas padrão, joga para a raiz limpa do GLPI (/Helpdesk/)
        if (strpos($uri, 'front/central.php') !== false || 
            strpos($uri, 'front/helpdesk.php') !== false ||
            strpos($uri, '/Helpdesk') !== false ||
            strpos($uri, '/ServiceCatalog') !== false ||
            (strpos($uri, 'front/ticket.php') !== false && strpos($uri, 'id=') === false) ||
            (strpos($uri, 'front/helpdesk.public.php') !== false && strpos($uri, 'create_ticket') === false && strpos($uri, 'show_form') === false && strpos($uri, 'custom_profile') === false)) {
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                header("Location: " . $CFG_GLPI['root_doc'] . "/");
                exit();
            }
        }

        // Se a requisição já for na raiz limpa do GLPI (/Helpdesk/ ou /Helpdesk/index.php)
        $clean_uri = strtok($uri, '?'); // Remove query strings para a verificação
        
        if (strpos($clean_uri, 'front/ticket.form.php') !== false) {
            // Renderiza a visualização do chamado internamente mantendo a URL original
            if (strpos($uri, 'front/ticket.form.php') !== false && isset($_GET['id'])) {
                if (isset($_POST['add_reply'])) {
                    include GLPI_ROOT . '/plugins/painel/front/ticket_reply.php';
                    exit;
                }
                include GLPI_ROOT . "/plugins/painel/front/ticket.php";
                exit();
            }
        }
        
        if (strpos($uri, 'front/helpdesk.public.php') !== false && strpos($uri, 'create_ticket') !== false) {
            if (isset($_POST['add'])) {
                include GLPI_ROOT . '/plugins/painel/front/create_ticket_submit.php';
                exit;
            }
            include GLPI_ROOT . "/plugins/painel/front/create_ticket.php";
            exit();
        }
        
        if (strpos($uri, 'front/helpdesk.public.php') !== false && strpos($uri, 'custom_profile') !== false) {
            if (isset($_POST['update_profile'])) {
                include GLPI_ROOT . '/plugins/painel/front/profile_submit.php';
                exit;
            }
            include GLPI_ROOT . "/plugins/painel/front/profile.php";
            exit();
        }
        
        if ($clean_uri === $CFG_GLPI['root_doc'] . '/' || 
            $clean_uri === $CFG_GLPI['root_doc'] || 
            strpos($clean_uri, '/index.php') !== false) {
            
            // Renderiza o painel internamente mantendo a URL raiz bonita
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                include GLPI_ROOT . "/plugins/painel/front/painel.php";
                exit();
            }
        }
    }
}
