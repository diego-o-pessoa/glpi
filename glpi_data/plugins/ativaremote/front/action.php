<?php

declare(strict_types=1);

use GlpiPlugin\Ativaremote\ClientRepository;

include '../../../inc/includes.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

Session::checkRight('plugin_ativaremote', UPDATE);

$action = (string) ($_POST['action'] ?? '');
$id = (int) ($_POST['id'] ?? 0);

try {
    $repo = new ClientRepository();
    
    switch ($action) {
        case 'toggle_rustdesk_consent':
            $require = ($_POST['require'] ?? '1') === '1';
            $repo->setRequireConsent($id, $require);
            Session::addMessageAfterRedirect('Configuração de consentimento atualizada.', true, INFO);
            break;

        case 'request_remote_access':
            $repo->setRemoteAccessStatus($id, 'pending');
            Session::addMessageAfterRedirect('Acesso remoto solicitado. Aguarde a aprovação do usuário.', true, INFO);
            break;

        case 'cancel_remote_access':
            $repo->setRemoteAccessStatus($id, null);
            Session::addMessageAfterRedirect('Solicitação de acesso cancelada ou encerrada.', true, INFO);
            break;

        default:
            throw new Exception('Ação inválida.');
    }
} catch (Exception $e) {
    Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
}

Html::back();
