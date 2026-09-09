<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}

Session::checkLoginUser();
$user_id = Session::getLoginUserID();
$user = new User();
if (!$user->getFromDB($user_id)) {
    die("Usuário não encontrado.");
}

global $DB, $CFG_GLPI;

// Verifica se o usuário é Super-Admin
$has_super_admin = false;
$super_admin_prof_id = 0;
$prof_it = $DB->request([
    'SELECT' => ['glpi_profiles.id'],
    'FROM' => 'glpi_profiles_users',
    'INNER JOIN' => [
        'glpi_profiles' => ['ON' => ['glpi_profiles_users' => 'profiles_id', 'glpi_profiles' => 'id']]
    ],
    'WHERE' => [
        'glpi_profiles_users.users_id' => Session::getLoginUserID(),
        'glpi_profiles.name' => 'Super-Admin'
    ]
]);
foreach ($prof_it as $row) {
    $has_super_admin = true;
    $super_admin_prof_id = $row['id'];
}

if (isset($_GET['switch_to_admin']) && $has_super_admin) {
    Session::changeProfile($super_admin_prof_id);
    header("Location: " . $CFG_GLPI['root_doc'] . "/front/central.php");
    exit();
}

// Message handling
$success_msg = "";
$error_msg = "";
if (isset($_SESSION['profile_success'])) {
    $success_msg = $_SESSION['profile_success'];
    unset($_SESSION['profile_success']);
}
if (isset($_SESSION['profile_error'])) {
    $error_msg = $_SESSION['profile_error'];
    unset($_SESSION['profile_error']);
}

// User Data
$firstname = $user->fields['firstname'];
$realname = $user->fields['realname'];
$display_name = trim($firstname) ? trim($firstname) : (trim($realname) ? trim($realname) : $user->fields['name']);
$primeiro_nome = explode(' ', $display_name)[0];
$primeiro_nome = explode('-', $primeiro_nome)[0];

$mobile = $user->fields['mobile'] ?? '';
$locations_id = $user->fields['locations_id'] ?? 0;
$usertitles_id = $user->fields['usertitles_id'] ?? 0;
$picture = $user->fields['picture'] ?? '';

$cargo = '';
if ($usertitles_id > 0) {
    $title_iterator = $DB->request(['FROM' => 'glpi_usertitles', 'WHERE' => ['id' => $usertitles_id]]);
    if (count($title_iterator) > 0) {
        $cargo = $title_iterator->current()['name'];
    }
}

// Puxa o Setor (Grupo)
$groups_id = 0;
$grp_iter = $DB->request([
    'SELECT' => ['g.id'],
    'FROM'   => 'glpi_groups_users AS gu',
    'INNER JOIN' => [
        'glpi_groups AS g' => ['ON' => ['gu' => 'groups_id', 'g' => 'id']]
    ],
    'WHERE'  => ['gu.users_id' => $user_id],
    'LIMIT'  => 1
])->current();
if ($grp_iter) {
    $groups_id = $grp_iter['id'];
}

// Fetch locations
$locations = [];
$loc_iterator = $DB->request(['FROM' => 'glpi_locations', 'ORDER' => 'completename ASC']);
foreach ($loc_iterator as $row) {
    $locations[$row['id']] = $row['completename'];
}

// Fetch groups (Setor)
$groups = [];
$g_iterator = $DB->request(['FROM' => 'glpi_groups', 'WHERE' => ['is_usergroup' => 1], 'ORDER' => 'completename ASC']);
foreach ($g_iterator as $row) {
    $groups[$row['id']] = $row['completename'];
}

$avatar_url = $picture ? $CFG_GLPI['root_doc'] . '/front/document.send.php?file=_pictures/' . $picture : '';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Configurações - Ativa Locação</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #F7F8FA; color: #333; }
        a { text-decoration: none; color: inherit; }
        .topbar { display: flex; justify-content: space-between; align-items: center; background-color: #FFFFFF; padding: 0 40px; height: 70px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .topbar-left { display: flex; align-items: center; }
        .logo { display: flex; align-items: center; font-weight: 800; font-size: 20px; color: #0b457e; margin-right: 40px; }
        .nav-links a { display: flex; align-items: center; padding: 25px 0; color: #555; font-weight: 600; font-size: 14px; border-bottom: 2px solid transparent; }
        .nav-links a.active { color: #333; border-bottom: 2px solid #333; }
        .nav-links a i { margin-right: 8px; }
        .topbar-right { display: flex; align-items: center; }
        
        .user-profile { display: flex; align-items: center; position: relative; cursor: pointer; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #E2E4E8; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: #666; margin-right: 12px; font-size: 14px; overflow: hidden; }
        .user-name { font-size: 14px; color: #333; }
        .dropdown-menu { display: none; position: absolute; top: 45px; right: 0; background-color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-radius: 8px; padding: 10px 0; min-width: 220px; z-index: 100; border: 1px solid #EEE; }
        .dropdown-menu::before { content: ''; position: absolute; top: -20px; left: 0; width: 100%; height: 20px; }
        .user-profile:hover .dropdown-menu { display: block; }
        .dropdown-menu a { display: block; padding: 12px 20px; font-size: 14px; color: #444; font-weight: 500; }
        .dropdown-menu a:hover { background-color: #F7F8FA; color: #0b457e; }
        .dropdown-menu i { margin-right: 12px; width: 16px; text-align: center; color: #888; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 0 30px; }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-action h1 { font-size: 28px; font-weight: 800; color: #1A1F36; letter-spacing: -0.5px; }
        
        .content-box { background-color: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); padding: 40px; border: 1px solid #F0F0F0; }
        
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #4B5563; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s; background: #F9FAFB; }
        .form-control:focus { border-color: #0b457e; box-shadow: 0 0 0 3px rgba(11, 69, 126, 0.1); background: #FFF; }
        
        .picture-upload { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .pic-preview { width: 80px; height: 80px; border-radius: 50%; background-color: #E2E4E8; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #888; overflow: hidden; border: 2px solid #E5E7EB; }
        .pic-actions { display: flex; flex-direction: column; gap: 8px; }
        .btn-upload { background: #F3F4F6; color: #4B5563; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid #E5E7EB; transition: all 0.2s; display: inline-block; text-align: center; }
        .btn-upload:hover { background: #E5E7EB; }
        input[type="file"] { display: none; }
        
        .btn-submit { background-color: #0b457e; color: #FFF; padding: 14px 24px; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.2s; width: 100%; margin-top: 10px; }
        .btn-submit:hover { background-color: #08335e; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 14px; font-weight: 500; }
        .alert-success { background-color: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        .alert-error { background-color: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">
            <div class="logo">
                <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="height: 45px;">
            </div>
            <nav class="nav-links">
                <a href="<?=$CFG_GLPI['root_doc']?>/"><i class="fa-regular fa-window-maximize"></i> Chamados</a>
            </nav>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <div class="avatar" style="<?= $avatar_url ? 'background-image: url('.$avatar_url.');' : '' ?>"><?= $avatar_url ? '' : '<i class="fa-solid fa-user"></i>' ?></div>
                <span class="user-name">Olá, <strong><?= htmlspecialchars($primeiro_nome) ?></strong> <i class="fa-solid fa-caret-down" style="font-size: 10px; margin-left: 5px; color: #888;"></i></span>
                
                <div class="dropdown-menu">
                    <?php if ($has_super_admin): ?>
                        <a href="?custom_profile=1&switch_to_admin=1"><i class="fa-solid fa-user-shield"></i> Trocar para Admin</a>
                    <?php endif; ?>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?custom_profile=1"><i class="fa-solid fa-gear"></i> Minhas configurações</a>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header-action">
            <h1>Minhas Configurações</h1>
        </div>

        <div class="content-box">
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <form action="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?custom_profile=1" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">
                <input type="hidden" name="_glpi_csrf_token" value="<?=Session::getNewCSRFToken()?>">
                
                <input type="hidden" name="remove_picture" id="remove_picture_input" value="0">
                
                <div class="picture-upload">
                    <div class="pic-preview" id="pic-preview" style="<?= $avatar_url ? 'background-image: url('.$avatar_url.');' : '' ?>">
                        <?= $avatar_url ? '' : '<i class="fa-solid fa-user"></i>' ?>
                    </div>
                    <div class="pic-actions">
                        <label for="picture_file" class="btn-upload"><i class="fa-solid fa-camera"></i> Alterar Foto</label>
                        <input type="file" id="picture_file" name="picture" accept="image/jpeg, image/png, image/gif">
                        <?php if ($avatar_url): ?>
                            <button type="button" class="btn-upload" style="background: transparent; border: 1px solid #FEE2E2; color: #DC2626;" onclick="removePicture()"><i class="fa-solid fa-trash"></i> Remover Foto</button>
                        <?php endif; ?>
                        <span style="font-size: 12px; color: #888;">Formatos aceitos: JPG, PNG, GIF. Max: 2MB.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Número de Celular</label>
                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($mobile) ?>" placeholder="(00) 00000-0000">
                </div>

                <div class="form-group">
                    <label>Cargo</label>
                    <input type="text" name="cargo" class="form-control" value="<?= htmlspecialchars($cargo) ?>" placeholder="Digite seu cargo (Ex: Analista de Suporte)">
                </div>
                
                <div class="form-group">
                    <label>Setor</label>
                    <select name="groups_id" class="form-control">
                        <option value="0">Selecione o setor...</option>
                        <?php foreach($groups as $id => $name): ?>
                            <option value="<?=$id?>" <?= ($id == $groups_id) ? 'selected' : '' ?>><?=htmlspecialchars($name)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Filial</label>
                    <select name="locations_id" class="form-control">
                        <option value="0">Selecione a filial...</option>
                        <?php foreach($locations as $id => $name): ?>
                            <option value="<?=$id?>" <?= ($id == $locations_id) ? 'selected' : '' ?>><?=htmlspecialchars($name)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Salvar Configurações</button>
            </form>
        </div>
    </div>
    
    <script>
        function removePicture() {
            var preview = document.getElementById('pic-preview');
            preview.style.backgroundImage = 'none';
            preview.innerHTML = '<i class="fa-solid fa-user"></i>';
            document.getElementById('remove_picture_input').value = '1';
            document.getElementById('picture_file').value = ''; // clear file input
        }

        document.getElementById('picture_file').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('pic-preview');
                    preview.style.backgroundImage = 'url(' + e.target.result + ')';
                    preview.innerHTML = '';
                    document.getElementById('remove_picture_input').value = '0';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>
