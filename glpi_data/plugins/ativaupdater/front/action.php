<?php

include('../../../inc/includes.php');

Session::checkLoginUser();
Session::checkRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE);

$action = $_POST['action'] ?? '';

if ($action === 'upload') {
    Session::checkCSRF($_POST);
    
    $version = $_POST['version'] ?? '';
    if (!preg_match('/^\d+\.\d+\.\d+.*$/', $version)) {
        Session::addMessageAfterRedirect('Versão inválida.', false, ERROR);
        Html::back();
    }
    
    if (!isset($_FILES['installer']) || $_FILES['installer']['error'] !== UPLOAD_ERR_OK) {
        Session::addMessageAfterRedirect('Falha no upload.', false, ERROR);
        Html::back();
    }
    
    $file = $_FILES['installer'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($extension !== 'exe') {
        Session::addMessageAfterRedirect('Somente arquivos .exe são permitidos.', false, ERROR);
        Html::back();
    }
    
    $storageDir = GLPI_PLUGIN_DOC_DIR . '/ativaupdater/releases';
    $safeFilename = 'setup_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $version) . '_' . uniqid() . '.exe';
    $targetPath = $storageDir . '/' . $safeFilename;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        Session::addMessageAfterRedirect('Falha ao salvar o arquivo.', false, ERROR);
        Html::back();
    }
    
    $sha256 = hash_file('sha256', $targetPath);
    $size = filesize($targetPath);
    
    $release = new PluginAtivaupdaterRelease();
    
    // Check if version exists
    global $DB;
    $exists = $DB->request([
        'FROM'  => 'glpi_plugin_ativaupdater_releases',
        'WHERE' => ['version' => $version]
    ])->count();
    
    if ($exists > 0) {
        unlink($targetPath);
        Session::addMessageAfterRedirect('A versão informada já existe.', false, ERROR);
        Html::back();
    }
    
    $id = $release->add([
        'version'           => $version,
        'original_filename' => basename($file['name']),
        'stored_filename'   => $safeFilename,
        'file_path'         => $targetPath,
        'file_size'         => $size,
        'sha256'            => $sha256,
        'created_at'        => $_SESSION['glpi_currenttime'],
        'created_by'        => Session::getLoginUserID(),
        'active'            => 0
    ]);
    
    if ($id) {
        $release->setActive((int)$id);
        Session::addMessageAfterRedirect('Release publicada com sucesso!', true, INFO);
    } else {
        unlink($targetPath);
        Session::addMessageAfterRedirect('Falha ao registrar release no banco de dados.', false, ERROR);
    }
    
    Html::redirect('dashboard.php');
}

if ($action === 'set_active') {
    Session::checkCSRF($_POST);
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $release = new PluginAtivaupdaterRelease();
        if ($release->setActive($id)) {
            Session::addMessageAfterRedirect('Versão atual atualizada.', true, INFO);
        } else {
            Session::addMessageAfterRedirect('Falha ao atualizar versão.', false, ERROR);
        }
    }
    Html::redirect('dashboard.php');
}

if ($action === 'delete') {
    Session::checkCSRF($_POST);
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $release = new PluginAtivaupdaterRelease();
        if ($release->getFromDB($id)) {
            $path = $release->fields['file_path'];
            // Prevent path traversal deletion
            $storageDir = GLPI_PLUGIN_DOC_DIR . '/ativaupdater/releases';
            if (str_starts_with(realpath($path), realpath($storageDir))) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            
            $release->delete(['id' => $id]);
            Session::addMessageAfterRedirect('Release removida com sucesso.', true, INFO);
        }
    }
    Html::redirect('dashboard.php');
}

Html::displayErrorAndDie('Ação inválida.');
