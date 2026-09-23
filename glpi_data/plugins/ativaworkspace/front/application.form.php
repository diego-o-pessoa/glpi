<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Application;
use GlpiPlugin\Ativaworkspace\FormHandler;
use GlpiPlugin\Ativaworkspace\InstallerStorage;
use GlpiPlugin\Ativaworkspace\Page;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include('../../../inc/includes.php');

Page::requireAccess('applications');

$item = new Application();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    FormHandler::handlePost($item, [
        'name', 'comment', 'icon', 'category', 'desired_version', 'installer_type', 'architecture',
        'timeout_minutes', 'max_attempts', 'requires_reboot', 'expected_signer', 'notes', 'is_active',
    ], 'applications', [
        'form_page' => 'application_form',
        // Roda so depois da permissao (criar/editar) validada no backend.
        'prepare'   => static function (array $input): array {
            $upload = $_FILES['installer'] ?? null;
            if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                return $input;
            }
            $type = (string) ($input['installer_type'] ?? '');
            if (!isset(InstallerStorage::TYPES[$type])) {
                throw new RuntimeException('Escolha o tipo do instalador antes de enviar o arquivo.');
            }
            $input['_installer'] = InstallerStorage::store($upload, $type);
            return $input;
        },
        // O banco recusou (ex.: nome vazio): o arquivo recem-gravado nao fica orfao.
        'rollback'  => static function (array $input): void {
            if (isset($input['_installer']['file_stored_name'])) {
                InstallerStorage::delete((string) $input['_installer']['file_stored_name']);
            }
        },
    ]);
}

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    if (!$item->getFromDB($id)) {
        throw new NotFoundHttpException();
    }
} else {
    PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS, CREATE);
    $item->getEmpty();
    $item->fields['category']        = 'other';
    $item->fields['installer_type']  = 'MSI';
    $item->fields['architecture']    = 'any';
    $item->fields['timeout_minutes'] = 30;
    $item->fields['max_attempts']    = 3;
    $item->fields['is_active']       = 1;
}

$right   = PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS;
$maxSize = InstallerStorage::effectiveMaxBytes();
$fields  = $item->fields;

$file = null;
if ((string) ($fields['file_stored_name'] ?? '') !== '') {
    $file = [
        'name'      => (string) $fields['file_name'],
        'size'      => Toolbox::getSize((int) $fields['file_size']),
        'sha256'    => (string) $fields['file_sha256'],
        'signature' => InstallerStorage::SIGNATURE_LABELS[(string) $fields['file_signature_status']] ?? (string) $fields['file_signature_status'],
        'signed'    => (string) $fields['file_signature_status'] === 'signed_unverified',
        'unsigned'  => (string) $fields['file_signature_status'] === 'unsigned',
        'date'      => $fields['file_uploaded_at'],
        'user'      => (int) $fields['file_users_id'] > 0 ? getUserName((int) $fields['file_users_id']) : '',
        // Sumiu do disco (ex.: restauracao de backup sem files/): avisa.
        'missing'   => InstallerStorage::path((string) $fields['file_stored_name']) === null,
    ];
}

$types = [];
foreach (InstallerStorage::TYPES as $key => $type) {
    $types[$key] = $type['label'] . ' (' . implode(', ', array_map(static fn ($e) => '.' . $e, $type['extensions'])) . ')';
}

Page::render('applications', 'application_form.html.twig', [
    'item'         => $fields,
    'is_new'       => $id === 0,
    'can_edit'     => $id === 0 || Session::haveRight($right, UPDATE),
    'can_purge'    => $id > 0 && Session::haveRight($right, PURGE),
    'usage'        => $id > 0 ? Application::usageCount($id) : 0,
    'file'         => $file,
    'categories'   => Application::CATEGORIES,
    'icons'        => Application::ICONS,
    'architectures'=> Application::ARCHITECTURES,
    'types'        => $types,
    'accept'       => InstallerStorage::acceptAttribute(),
    'max_bytes'    => $maxSize,
    'max_label'    => Toolbox::getSize($maxSize),
    'action_url'   => Page::href('application_form'),
    'list_url'     => Page::href('applications'),
]);
