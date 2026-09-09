<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}

Session::checkLoginUser();
$user_id = Session::getLoginUserID();
global $DB, $CFG_GLPI;

try {
    $mobile = trim($_POST['mobile'] ?? '');
    $cargo_texto = trim($_POST['cargo'] ?? '');
    $groups_id = (int)($_POST['groups_id'] ?? 0);
    $locations_id = (int)($_POST['locations_id'] ?? 0);
    
    // Process Cargo
    $title_id = 0;
    if (!empty($cargo_texto)) {
        $title_iterator = $DB->request(['FROM' => 'glpi_usertitles', 'WHERE' => ['name' => $cargo_texto]]);
        if (count($title_iterator) > 0) {
            $title_id = $title_iterator->current()['id'];
        } else {
            $DB->insert('glpi_usertitles', [
                'name' => $cargo_texto,
                'date_mod' => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s'),
                'date_creation' => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s')
            ]);
            $title_id = $DB->insertId();
        }
    }
    
    // Process File Upload for Picture
    $picture_filename = null;
    $remove_picture = (int)($_POST['remove_picture'] ?? 0);
    
    if ($remove_picture === 1) {
        $old_user = new User();
        if ($old_user->getFromDB($user_id)) {
            $old_pic = $old_user->fields['picture'];
            if (!empty($old_pic) && file_exists(GLPI_PICTURE_DIR . '/' . $old_pic)) {
                unlink(GLPI_PICTURE_DIR . '/' . $old_pic);
            }
        }
        $picture_filename = ''; // Empty string removes it in the update below
    } elseif (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['picture']['tmp_name'];
        $name = $_FILES['picture']['name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $new_filename = uniqid('profile_' . $user_id . '_') . '.' . $ext;
            $dest_path = GLPI_PICTURE_DIR . '/' . $new_filename;
            
            if (move_uploaded_file($tmp_name, $dest_path)) {
                $picture_filename = $new_filename;
                
                // Remove old picture if exists to save space
                $old_user = new User();
                if ($old_user->getFromDB($user_id)) {
                    $old_pic = $old_user->fields['picture'];
                    if (!empty($old_pic) && file_exists(GLPI_PICTURE_DIR . '/' . $old_pic)) {
                        unlink(GLPI_PICTURE_DIR . '/' . $old_pic);
                    }
                }
            }
        } else {
            throw new Exception("Formato de imagem inválido. Apenas JPG, PNG e GIF são permitidos.");
        }
    }
    
    // Update User Core Fields
    $update_data = [
        'mobile' => $mobile,
        'locations_id' => $locations_id,
        'usertitles_id' => $title_id
    ];
    if ($picture_filename !== null) {
        $update_data['picture'] = $picture_filename;
    }
    $DB->update('glpi_users', $update_data, ['id' => $user_id]);
    
    // Update Groups (Setor)
    if ($groups_id > 0) {
        // Remove existing is_usergroup groups for this user
        $g_iterator = $DB->request(['FROM' => 'glpi_groups', 'WHERE' => ['is_usergroup' => 1]]);
        $user_groups = [];
        foreach ($g_iterator as $row) {
            $user_groups[] = $row['id'];
        }
        
        if (!empty($user_groups)) {
            $DB->delete('glpi_groups_users', [
                'users_id' => $user_id,
                'groups_id' => $user_groups
            ]);
        }
        
        // Insert new group
        $DB->insert('glpi_groups_users', [
            'users_id'  => $user_id,
            'groups_id' => $groups_id,
            'is_dynamic' => 0
        ]);
    }
    
    $_SESSION['profile_success'] = "Configurações atualizadas com sucesso!";
    
} catch (Exception $e) {
    $_SESSION['profile_error'] = "Erro: " . $e->getMessage();
}

header("Location: " . $CFG_GLPI['root_doc'] . "/front/helpdesk.public.php?custom_profile=1");
exit();
