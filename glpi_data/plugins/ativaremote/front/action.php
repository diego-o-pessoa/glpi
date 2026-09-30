<?php

declare(strict_types=1);

use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaremote\ProtectionPolicy;
use GlpiPlugin\Ativaremote\TiPasswordGate;

include '../../../inc/includes.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

Session::checkRight('plugin_ativaremote', UPDATE);

$action = (string) ($_POST['action'] ?? '');
$id = (int) ($_POST['id'] ?? 0);

// O painel ao vivo chama por fetch e quer JSON (sem recarregar a pagina).
$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
$messages = [
    'toggle_rustdesk_consent' => static fn (): string => ($_POST['require'] ?? '1') === '1'
        ? 'O usuário precisará autorizar cada acesso.'
        : 'Acesso sem autorização do usuário ativado.',
    'request_remote_access'   => static fn (): string => 'Acesso solicitado. Aguardando o computador responder.',
    'close_remote_access'     => static fn (): string => 'Sessão encerrada. O computador troca a senha do RustDesk em seguida.',
];
if ($ajax) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
}

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
            $client = $repo->findById($id);
            if ($client === null) {
                throw new Exception('Computador não encontrado.');
            }
            $protected = ProtectionPolicy::isProtected($client);
            if ($protected) {
                TiPasswordGate::check((string) ($_POST['ti_password'] ?? ''), $client, 'solicitar acesso');
            }
            $client = $repo->requestAccess($id, (int) Session::getLoginUserID());
            if ($protected) {
                // The same technician is not asked again to connect to this session.
                TiPasswordGate::markVerified($client);
                TiPasswordGate::log('Acesso solicitado a computador protegido (senha do T.I. conferida)', $client);
            }
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
    if ($ajax) {
        http_response_code(422);
        echo json_encode([
            'ok'                => false,
            'message'           => $e->getMessage(),
            // Senha do T.I. errada/bloqueada: o modal continua aberto.
            'password_required' => $e instanceof RuntimeException && in_array((int) $e->getCode(), [403, 429], true),
        ]);
        exit;
    }
    Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
}

if ($ajax) {
    // A mensagem vai na resposta; nao sobra para a proxima pagina.
    $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
    echo json_encode(['ok' => true, 'message' => ($messages[$action] ?? static fn (): string => 'Feito.')()]);
    exit;
}

Html::back();
