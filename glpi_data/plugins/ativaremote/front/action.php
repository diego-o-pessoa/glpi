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
            Session::addMessageAfterRedirect(
                $require ? 'O usuário precisará autorizar cada acesso.' : 'Acesso sem autorização do usuário ativado.',
                true,
                INFO
            );
            break;

        case 'request_remote_access':
            $repo->requestAccess($id, (int) Session::getLoginUserID());
            Session::addMessageAfterRedirect('Acesso remoto solicitado. A tela atualiza sozinha quando o computador responder.', true, INFO);
            break;

        case 'close_remote_access':
            $repo->closeAccess($id);
            Session::addMessageAfterRedirect('Sessão encerrada. O computador troca a senha do RustDesk em seguida.', true, INFO);
            break;

        default:
            throw new Exception('Ação inválida.');
    }
} catch (Exception $e) {
    Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
}

Html::back();
