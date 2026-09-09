<?php
function plugin_primeiroacesso_check_location() {
    global $CFG_GLPI;

    // Check if user is logged in
    if (!Session::getLoginUserID() || isCommandLine()) {
        return;
    }

    // Do not redirect if already on the prompt page to avoid loop
    if (strpos($_SERVER['REQUEST_URI'], 'primeiroacesso/front/prompt.php') !== false) {
        return;
    }

    // Do not redirect on logout, login, or ajax calls, or API calls
    if (strpos($_SERVER['REQUEST_URI'], 'logout.php') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'login.php') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'ajax') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'apirest.php') !== false) {
        return;
    }

    $user = new User();
    if ($user->getFromDB(Session::getLoginUserID())) {
        
        // Verifica se o usuário logado está com o perfil "Self-Service" ativo (ID padrão = 1 ou pelo nome)
        $is_self_service = false;
        if (isset($_SESSION['glpiactiveprofile']) && 
           ($_SESSION['glpiactiveprofile']['id'] == 1 || stripos($_SESSION['glpiactiveprofile']['name'], 'self-service') !== false)) {
            $is_self_service = true;
        }

        if ($is_self_service) {
            global $DB;
            // Verifica se Filial, Setor (Grupo) ou Cargo estão vazios
            $loc_empty = empty($user->fields['locations_id']) || $user->fields['locations_id'] == 0;
            
            $has_group = false;
            $iterator = $DB->request(['FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => Session::getLoginUserID()]]);
            if (count($iterator) > 0) {
                $has_group = true;
            }
            $cat_empty = !$has_group;
            
            $title_empty = empty($user->fields['usertitles_id']) || $user->fields['usertitles_id'] == 0;
            
            if ($loc_empty || $cat_empty || $title_empty) {
                file_put_contents('/tmp/primeiroacesso.log', "[".date('Y-m-d H:i:s')."] hook.php intercepting. Missing data: Loc=$loc_empty, Grp=$cat_empty, Title=$title_empty.\n", FILE_APPEND);
                header("Location: " . $CFG_GLPI['root_doc'] . "/plugins/primeiroacesso/front/prompt.php");
                exit();
            }
        }
    }

    
    // Injeta o bloqueio visual nos formulários de usuário/preferências
    if (strpos($_SERVER['REQUEST_URI'], 'front/user.form.php') !== false || 
        strpos($_SERVER['REQUEST_URI'], 'front/preference.php') !== false) {
        
        register_shutdown_function(function() {
            echo "<script type='text/javascript'>
                setTimeout(function() {
                    // Bloqueia a Localização
                    var locField = $('select[name=\"locations_id\"]');
                    if (locField.length) {
                        locField.prop('disabled', true);
                        if (locField.hasClass('select2-hidden-accessible')) {
                            locField.on('select2:opening', function (e) { e.preventDefault(); });
                        }
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'locations_id',
                            value: locField.val()
                        }).appendTo(locField.parent());
                    }
                    
                    // Bloqueia a Entidade Padrão
                    var entField = $('select[name=\"entities_id\"]');
                    if (entField.length) {
                        entField.prop('disabled', true);
                        if (entField.hasClass('select2-hidden-accessible')) {
                            entField.on('select2:opening', function (e) { e.preventDefault(); });
                        }
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'entities_id',
                            value: entField.val()
                        }).appendTo(entField.parent());
                    }
                }, 500);
            </script>";
        });
    }
}
