<?php
require_once __DIR__ . '/inc/progress.php';
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
            (strpos($uri, 'front/helpdesk.public.php') !== false && strpos($uri, 'create_ticket') === false && strpos($uri, 'show_form') === false && strpos($uri, 'custom_profile') === false && strpos($uri, 'projects') === false && strpos($uri, 'project_id') === false)) {
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                header("Location: " . $CFG_GLPI['root_doc'] . "/");
                exit();
            }
        }

        // Redireciona telas nativas de projeto no Self-Service para o portal simplificado
        $clean_uri = strtok($uri, '?'); // Remove query strings para a verificação
        if (preg_match('#/front/project\.php$#i', $clean_uri) === 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                header("Location: " . $CFG_GLPI['root_doc'] . "/front/helpdesk.public.php?projects=1");
                exit();
            }
        }
        if (preg_match('#/front/project\.form\.php$#i', $clean_uri) === 1 && isset($_GET['id'])) {
            include GLPI_ROOT . "/plugins/painel/front/project_view.php";
            exit();
        }
        
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

        if (strpos($uri, 'front/helpdesk.public.php') !== false && isset($_GET['projects'])) {
            include GLPI_ROOT . "/plugins/painel/front/projects.php";
            exit();
        }

        if (strpos($uri, 'front/helpdesk.public.php') !== false && isset($_GET['project_id'])) {
            include GLPI_ROOT . "/plugins/painel/front/project_view.php";
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

function plugin_painel_projecttask_changed(CommonDBTM $task) {
    $projectId = (int) ($task->fields['projects_id'] ?? 0);
    if ($projectId <= 0) {
        return;
    }
    try {
        // Principal antiga e nova (quando a tarefa muda de principal).
        $parents = [(int) ($task->fields['projecttasks_id'] ?? 0)];
        if (isset($task->oldvalues['projecttasks_id'])) {
            $parents[] = (int) $task->oldvalues['projecttasks_id'];
        }
        foreach (array_unique($parents) as $parentId) {
            painel_task_extend_parents($parentId);
        }
        painel_project_recalc($projectId);
    } catch (Throwable $e) {
        Toolbox::logInFile('painel', 'Falha ao recalcular projeto ' . $projectId . ': ' . $e->getMessage() . "\n");
    }
}
