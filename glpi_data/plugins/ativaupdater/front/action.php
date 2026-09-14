<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ConfigService;

include('../../../inc/includes.php');

Session::checkLoginUser();
if (!Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)) {
    Session::checkRight('config', UPDATE);
}

$redirectWithError = static function (string $message): never {
    Session::addMessageAfterRedirect($message, false, ERROR);
    Html::redirect('dashboard.php');
};

$storageDirectory = GLPI_PLUGIN_DOC_DIR . '/ativaupdater/releases';
$action = (string) ($_POST['action'] ?? '');

if ($action === 'upload') {
    $version = trim((string) ($_POST['version'] ?? ''));
    if (!preg_match('/^\d{1,5}\.\d{1,5}\.\d{1,5}$/D', $version)) {
        $redirectWithError('Informe uma versão no formato X.Y.Z, por exemplo 1.4.4.');
    }

    $upload = $_FILES['installer'] ?? null;
    if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $redirectWithError('O upload do instalador não foi concluído.');
    }

    $temporary = (string) ($upload['tmp_name'] ?? '');
    $originalName = basename((string) ($upload['name'] ?? 'instalador.exe'));
    $size = (int) ($upload['size'] ?? 0);
    $maxSize = max(50, min(2048, ConfigService::getInt('max_upload_mb', 500))) * 1024 * 1024;
    if (!is_uploaded_file($temporary) || $size < 1024 || $size > $maxSize) {
        $redirectWithError('O arquivo é inválido ou excede o limite configurado.');
    }
    if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'exe') {
        $redirectWithError('Envie somente o instalador unificado no formato .exe.');
    }
    $expectedName = 'Ativa-Unified-Agent-Setup-' . $version . '.exe';
    if (strcasecmp($originalName, $expectedName) !== 0) {
        $redirectWithError('Nome de arquivo inválido. O esperado para essa versão é ' . $expectedName . '.');
    }

    $header = file_get_contents($temporary, false, null, 0, 2);
    if ($header !== 'MZ') {
        $redirectWithError('O arquivo enviado não é um executável Windows válido.');
    }

    global $DB;
    $exists = $DB->request([
        'FROM'  => 'glpi_plugin_ativaupdater_releases',
        'WHERE' => ['version' => $version],
        'LIMIT' => 1,
    ]);
    if (count($exists) !== 0) {
        $redirectWithError('Essa versão já foi publicada.');
    }

    if (!is_dir($storageDirectory)
        && !mkdir($storageDirectory, 0750, true)
        && !is_dir($storageDirectory)
    ) {
        $redirectWithError('Não foi possível preparar o armazenamento privado.');
    }

    $storedFilename = bin2hex(random_bytes(24)) . '.exe';
    $target = $storageDirectory . DIRECTORY_SEPARATOR . $storedFilename;
    if (!move_uploaded_file($temporary, $target)) {
        $redirectWithError('Falha ao mover o upload para o armazenamento privado.');
    }
    @chmod($target, 0640);

    $sha256 = hash_file('sha256', $target);
    $actualSize = filesize($target);
    if (!is_string($sha256) || $actualSize === false || $actualSize !== $size) {
        @unlink($target);
        $redirectWithError('Não foi possível validar o instalador depois do upload.');
    }

    $DB->beginTransaction();
    try {
        $DB->update('glpi_plugin_ativaupdater_releases', ['active' => 0], ['active' => 1]);
        $ok = $DB->insert('glpi_plugin_ativaupdater_releases', [
            'version'           => $version,
            'original_filename' => mb_substr($originalName, 0, 255),
            'stored_filename'   => $storedFilename,
            'file_path'         => $target,
            'file_size'         => $actualSize,
            'sha256'            => strtolower($sha256),
            'created_at'        => date('Y-m-d H:i:s'),
            'created_by'        => Session::getLoginUserID(),
            'active'            => 1,
        ]);
        if (!$ok) {
            throw new RuntimeException('Falha ao registrar a versão no banco de dados.');
        }
        $DB->commit();
    } catch (Throwable $exception) {
        $DB->rollBack();
        @unlink($target);
        $redirectWithError($exception->getMessage());
    }

    Session::addMessageAfterRedirect('Versão ' . $version . ' publicada. Os serviços consultarão a API em até uma hora.', true, INFO);
    Html::redirect('dashboard.php');
}

if ($action === 'set_active') {
    $id = (int) ($_POST['id'] ?? 0);
    $release = new PluginAtivaupdaterRelease();
    if ($id <= 0 || !$release->getFromDB($id) || !$release->setActive($id)) {
        $redirectWithError('Não foi possível ativar a versão selecionada.');
    }
    Session::addMessageAfterRedirect('Versão ativa atualizada.', true, INFO);
    Html::redirect('dashboard.php');
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $release = new PluginAtivaupdaterRelease();
    if ($id <= 0 || !$release->getFromDB($id)) {
        $redirectWithError('Versão não encontrada.');
    }
    if ((int) $release->fields['active'] === 1) {
        $redirectWithError('Ative outra versão antes de excluir a versão atual.');
    }

    $storedFilename = (string) ($release->fields['stored_filename'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9._-]{1,255}\.exe$/D', $storedFilename)) {
        $redirectWithError('Nome interno do arquivo inválido.');
    }
    $path = $storageDirectory . DIRECTORY_SEPARATOR . $storedFilename;
    if (!$release->delete(['id' => $id])) {
        $redirectWithError('Não foi possível excluir o registro da versão.');
    }
    if (is_file($path)) {
        @unlink($path);
    }
    Session::addMessageAfterRedirect('Versão removida.', true, INFO);
    Html::redirect('dashboard.php');
}

Html::displayErrorAndDie('Ação inválida.');
