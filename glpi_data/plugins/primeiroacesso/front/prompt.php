<?php
include("../../../inc/includes.php");
file_put_contents('/tmp/primeiroacesso.log', "[".date('Y-m-d H:i:s')."] prompt.php accessed. Method: " . $_SERVER['REQUEST_METHOD'] . "\nPOST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Must be logged in
if (!Session::getLoginUserID()) {
    header("Location: ../../../index.php");
    exit();
}

global $DB, $CFG_GLPI;
$user_id = Session::getLoginUserID();

// Process Form Submission
$error_msg = "";
if (isset($_POST['update_primeiro_acesso'])) {
    $loc_id = (int)($_POST['locations_id'] ?? 0);
    $grp_id = (int)($_POST['groups_id'] ?? 0);
    $cargo_texto = trim($_POST['cargo_texto'] ?? '');

    if ($loc_id > 0 && $grp_id > 0 && !empty($cargo_texto)) {
        file_put_contents('/tmp/primeiroacesso.log', "[".date('Y-m-d H:i:s')."] Valid POST received. User: $user_id, Loc: $loc_id, Grp: $grp_id, Cargo: $cargo_texto\n", FILE_APPEND);
        
        try {
            // Verifica/Cria o Cargo em glpi_usertitles
            $title_id = 0;
            $title_iterator = $DB->request(['FROM' => 'glpi_usertitles', 'WHERE' => ['name' => $cargo_texto]]);
            if (count($title_iterator) > 0) {
                $title_id = $title_iterator->current()['id'];
            } else {
                // Cria novo cargo
                $DB->insert('glpi_usertitles', [
                    'name' => $cargo_texto,
                    'date_mod' => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s'),
                    'date_creation' => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s')
                ]);
                $title_id = $DB->insertId();
            }

            // Atualiza Entidade (Autorizações) baseado na Filial
            $ent_id = -1;
            
            // Tenta usar o mesmo ID da Localização, pois na estrutura eles são iguais
            $ent_iterator = $DB->request(['FROM' => 'glpi_entities', 'WHERE' => ['id' => $loc_id]]);
            if (count($ent_iterator) > 0) {
                $ent_id = $loc_id;
            } else {
                // Fallback: Busca pelo nome usando strpos no PHP para evitar problemas de acentuação no LIKE do SQL
                $loc_name = '';
                $loc_iterator = $DB->request(['FROM' => 'glpi_locations', 'WHERE' => ['id' => $loc_id]]);
                foreach ($loc_iterator as $row) {
                    $loc_name = $row['completename'];
                }

                if ($loc_name) {
                    $all_ents = $DB->request(['FROM' => 'glpi_entities']);
                    foreach ($all_ents as $row) {
                        if (strpos($row['completename'], $loc_name) !== false) {
                            $ent_id = $row['id'];
                            break;
                        }
                    }
                }
            }
            
            // Update user location, title and default entity
            $update_data = [
                'locations_id' => $loc_id,
                'usertitles_id' => $title_id
            ];
            
            if ($ent_id >= 0) {
                $update_data['entities_id'] = $ent_id;
            }
            $DB->update('glpi_users', $update_data, ['id' => $user_id]);

            // Assign user to group (Departamento)
            $iterator = $DB->request(['FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $user_id, 'groups_id' => $grp_id]]);
            if (count($iterator) == 0) {
                $DB->insert('glpi_groups_users', [
                    'users_id'  => $user_id,
                    'groups_id' => $grp_id,
                    'is_dynamic' => 0
                ]);
            }
                
                if ($ent_id >= 0) {
                    $profiles = [];
                    $pu_iterator = $DB->request(['FROM' => 'glpi_profiles_users', 'WHERE' => ['users_id' => $user_id]]);
                    foreach ($pu_iterator as $row) {
                        $profiles[$row['profiles_id']] = $row['is_recursive'];
                    }
                    
                    if (empty($profiles)) {
                        $profiles[1] = 0; // Self-Service por padrão
                    }
                    
                    $DB->delete('glpi_profiles_users', ['users_id' => $user_id]);
                    
                    foreach ($profiles as $prof_id => $is_rec) {
                        $DB->insert('glpi_profiles_users', [
                            'users_id'     => $user_id,
                            'profiles_id'  => $prof_id,
                            'entities_id'  => $ent_id,
                            'is_dynamic'   => 0,
                            'is_recursive' => $is_rec
                        ]);
                    }
                }

            // Força a atualização da sessão do GLPI para carregar a nova entidade
            if (class_exists('Session')) {
                Session::initEntityProfiles($user_id);
                if (isset($_SESSION['glpiprofiles']) && count($_SESSION['glpiprofiles']) > 0) {
                    reset($_SESSION['glpiprofiles']);
                    $prof_id = key($_SESSION['glpiprofiles']);
                    Session::changeProfile($prof_id);
                }
            }

            // Redirect to GLPI home
            file_put_contents('/tmp/primeiroacesso.log', "[".date('Y-m-d H:i:s')."] DB Update SUCCESS! Redirecting...\n", FILE_APPEND);
            header("Location: " . $CFG_GLPI['root_doc'] . "/");
            exit();
        } catch (Exception $e) {
            file_put_contents('/tmp/primeiroacesso.log', "[".date('Y-m-d H:i:s')."] DB Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            $error_msg = "Erro ao gravar no banco de dados. " . $e->getMessage();
        }
    } else {
        $error_msg = "Por favor, preencha todos os campos corretamente.";
    }
}

// Fetch locations
$locations = [];
$loc_iterator = $DB->request(['FROM' => 'glpi_locations', 'ORDER' => 'completename ASC']);
foreach ($loc_iterator as $row) {
    $locations[$row['id']] = $row['completename'];
}

// Fetch groups (Setor/Departamento)
$groups = [];
$grp_iterator = $DB->request(['FROM' => 'glpi_groups', 'WHERE' => ['is_usergroup' => 1], 'ORDER' => 'completename ASC']);
foreach ($grp_iterator as $row) {
    $groups[$row['id']] = $row['completename'];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Primeiro Acesso - Configuração</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            transform: translateY(0);
            animation: slideUp 0.6s ease-out forwards;
            opacity: 0;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1e3c72;
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .header p {
            color: #666;
            font-size: 15px;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: 15px;
            color: #333;
            background-color: #fff;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 15px top 50%;
            background-size: 12px auto;
        }
        .form-select:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .error-msg {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Bem-vindo! 👋</h1>
        <p>Para continuarmos, por favor preencha os dados do seu perfil.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="_glpi_csrf_token" value="<?php echo Session::getNewCSRFToken(); ?>" />
        
        <div class="form-group">
            <label for="locations_id">Qual a sua Filial?</label>
            <select name="locations_id" id="locations_id" class="form-select" required>
                <option value="">Selecione a filial...</option>
                <?php foreach ($locations as $id => $name): ?>
                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="groups_id">Qual o seu Setor?</label>
            <select name="groups_id" id="groups_id" class="form-select" required>
                <option value="">Selecione o setor...</option>
                <?php foreach ($groups as $id => $name): ?>
                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="cargo_texto">Qual o seu Cargo?</label>
            <input type="text" name="cargo_texto" id="cargo_texto" class="form-select" placeholder="Digite seu cargo..." style="background-image: none;" required>
        </div>

        <button type="submit" name="update_primeiro_acesso" class="btn-submit">Salvar e Entrar</button>
    </form>
</div>

</body>
</html>
