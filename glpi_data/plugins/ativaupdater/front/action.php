<?php

declare(strict_types=1);

use Glpi\DBAL\QueryExpression;
use GlpiPlugin\Ativaupdater\ConfigService;
use GlpiPlugin\Ativaupdater\ManualCheck;
use GlpiPlugin\Ativaupdater\ReleasePolicy;
use GlpiPlugin\Ativaupdater\ServerClock;

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
$markClientsAwaiting = static function (string $version, bool $allowDowngrade = false): void {
    global $DB;

    $table = 'glpi_plugin_ativaupdater_clients';
    if (!$DB->tableExists($table)) {
        return;
    }
    $iterator = $DB->request(['FROM' => $table]);
    foreach ($iterator as $client) {
        $clientAction = ReleasePolicy::clientAction((string) $client['installed_version'], $version, $allowDowngrade);
        if (!in_array($clientAction, [ReleasePolicy::ACTION_UPGRADE, ReleasePolicy::ACTION_DOWNGRADE], true)) {
            continue;
        }
        $status = (string) $client['status'];
        if (in_array($status, ['downloading', 'installing'], true)) {
            continue;
        }
        if ($status === 'error' && !str_contains((string) ($client['message'] ?? ''), 'NO_RELEASE')) {
            continue;
        }
        $DB->update($table, [
            'available_version' => $version,
            'status' => 'checking',
            'message' => $clientAction === ReleasePolicy::ACTION_DOWNGRADE
                ? 'Rollback autorizado para ' . $version . '; aguardando a próxima consulta automática do serviço.'
                : 'Nova versão publicada; aguardando a próxima consulta automática do serviço.',
        ], ['id' => (int) $client['id']]);
    }
};
$action = (string) ($_POST['action'] ?? '');
if ($action === '' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $receivedBytes = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($receivedBytes > 0) {
        $redirectWithError('O servidor não recebeu o formulário completo. Verifique os limites upload_max_filesize e post_max_size do PHP.');
    }
}

if ($action === 'check_now') {
    global $DB;
    $table = 'glpi_plugin_ativaupdater_clients';
    $requested = 0;
    $busy = 0;
    $unsupported = 0;
    if ($DB->tableExists($table)) {
        $now = ServerClock::now();
        $iterator = $DB->request(['FROM' => $table]);
        foreach ($iterator as $client) {
            $fields = [
                'check_request_seq'  => new QueryExpression(DBmysql::quoteName('check_request_seq') . ' + 1'),
                'check_requested_at' => $now,
            ];
            // Computers in the middle of an installation also receive the
            // command (they check as soon as the installer finishes), but
            // keep showing their real progress.
            if (in_array((string) $client['status'], ManualCheck::BUSY_STATUSES, true)) {
                $busy++;
            } else {
                $fields['status'] = 'checking';
                $fields['message'] = 'Verificação manual solicitada pelo dashboard.';
            }
            if (!ManualCheck::supports((string) $client['updater_version'])) {
                $unsupported++;
            }
            if ($DB->update($table, $fields, ['id' => (int) $client['id']])) {
                $requested++;
            }
        }
    }
    $message = $requested > 0
        ? 'Verificação imediata solicitada para ' . $requested . ' computador(es). O serviço receberá o comando em até 15 segundos.'
        : 'Nenhum computador identificado para verificar.';
    if ($busy > 0) {
        $message .= ' ' . $busy . ' computador(es) estão baixando ou instalando e verificarão ao terminar.';
    }
    if ($unsupported > 0) {
        $message .= ' ' . $unsupported . ' computador(es) têm serviço anterior a ' . ManualCheck::MIN_SERVICE_VERSION
            . ', que não aceita este comando, e só consultarão no intervalo automático.';
    }
    Session::addMessageAfterRedirect($message, true, INFO);
    Html::redirect('dashboard.php');
}

if ($action === 'upload') {
    $version = trim((string) ($_POST['version'] ?? ''));
    if (!ReleasePolicy::isValidVersion($version)) {
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
    if (!is_writable($storageDirectory)) {
        $redirectWithError('O armazenamento privado do Ativa Updater não permite gravação. Execute novamente a atualização do plugin para corrigir as permissões.');
    }
    $freeBytes = @disk_free_space($storageDirectory);
    if ($freeBytes !== false && $freeBytes < $size + 1024 * 1024) {
        $redirectWithError('Não há espaço livre suficiente para publicar o instalador.');
    }

    $storedFilename = bin2hex(random_bytes(24)) . '.exe';
    $target = $storageDirectory . DIRECTORY_SEPARATOR . $storedFilename;
    $moveWarning = null;
    set_error_handler(static function (int $severity, string $message) use (&$moveWarning): bool {
        $moveWarning = $message;
        return true;
    });
    try {
        $moved = move_uploaded_file($temporary, $target);
    } finally {
        restore_error_handler();
    }
    if (!$moved) {
        $details = trim((string) $moveWarning);
        $redirectWithError('Falha ao mover o upload para o armazenamento privado.' . ($details !== '' ? ' Detalhe: ' . $details : ''));
    }
    @chmod($target, 0640);

    $sha256 = hash_file('sha256', $target);
    $actualSize = filesize($target);
    if (!is_string($sha256) || $actualSize === false || $actualSize !== $size) {
        @unlink($target);
        $redirectWithError('Não foi possível validar o instalador depois do upload.');
    }

    $newerPublished = false;
    foreach ($DB->request(['SELECT' => ['version'], 'FROM' => 'glpi_plugin_ativaupdater_releases']) as $existingRelease) {
        if (version_compare((string) $existingRelease['version'], $version, '>')) {
            $newerPublished = true;
            break;
        }
    }

    $DB->beginTransaction();
    try {
        $DB->update('glpi_plugin_ativaupdater_releases', ['active' => 0, 'allow_downgrade' => 0], [
            'OR' => ['active' => 1, 'allow_downgrade' => 1],
        ]);
        $ok = $DB->insert('glpi_plugin_ativaupdater_releases', [
            'version'           => $version,
            'original_filename' => mb_substr($originalName, 0, 255),
            'stored_filename'   => $storedFilename,
            'file_path'         => $target,
            'file_size'         => $actualSize,
            'sha256'            => strtolower($sha256),
            'created_at'        => ServerClock::now(),
            'created_by'        => Session::getLoginUserID(),
            'active'            => 1,
            'allow_downgrade'   => 0,
            'activated_at'      => ServerClock::now(),
            'activated_by'      => Session::getLoginUserID(),
        ]);
        if (!$ok) {
            throw new RuntimeException('Falha ao registrar a versão no banco de dados.');
        }
        $releaseId = (int) $DB->insertId();
        if ($releaseId <= 0) {
            throw new RuntimeException('A versão não recebeu um identificador no banco de dados.');
        }
        $markClientsAwaiting($version);
        $DB->commit();
    } catch (Throwable $exception) {
        $DB->rollBack();
        @unlink($target);
        $redirectWithError($exception->getMessage());
    }

    Session::addMessageAfterRedirect('Versão ' . $version . ' publicada e ativada. Os serviços consultarão automaticamente em até uma hora.', true, INFO);
    if ($newerPublished) {
        Session::addMessageAfterRedirect(
            'Existe uma versão maior que ' . $version . ' já publicada. Computadores nela não farão downgrade; use "Rollback" para forçar.',
            true,
            WARNING
        );
    }
    Html::redirect('dashboard.php');
}

if ($action === 'set_active') {
    $id = (int) ($_POST['id'] ?? 0);
    $allowDowngrade = ($_POST['allow_downgrade'] ?? '0') === '1';
    $release = new PluginAtivaupdaterRelease();
    if ($id <= 0 || !$release->getFromDB($id)) {
        $redirectWithError('Versão não encontrada.');
    }
    $version = (string) $release->fields['version'];
    if ($allowDowngrade && !ReleasePolicy::canRollbackTo($version)) {
        $redirectWithError(
            'Pacotes anteriores a ' . ReleasePolicy::ROLLBACK_MIN_VERSION . ' não suportam downgrade. Escolha uma versão igual ou superior.'
        );
    }
    if (!$release->setActive($id, $allowDowngrade, (int) Session::getLoginUserID())) {
        $redirectWithError('Não foi possível ativar a versão selecionada.');
    }
    $markClientsAwaiting($version, $allowDowngrade);
    Session::addMessageAfterRedirect(
        $allowDowngrade
            ? 'Rollback autorizado: todos os computadores voltarão para a versão ' . $version . ' na próxima consulta. Use "Verificar agora" para antecipar.'
            : 'Versão ' . $version . ' ativa, somente para atualização. Computadores em versões maiores não farão downgrade.',
        true,
        INFO
    );
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
