<?php

declare(strict_types=1);

use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaremote\ProtectionPolicy;
use GlpiPlugin\Ativaremote\Settings;

include '../../../inc/includes.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$maxFailures = 5;
$lockSeconds = 300;

$respond = static function (int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    $respond(405, ['ok' => false, 'message' => 'Método não permitido.']);
}

Session::checkRight('plugin_ativaremote', UPDATE);

$id = (int) ($_POST['id'] ?? 0);
$repo = new ClientRepository();
$client = $repo->findById($id);
if ($client === null) {
    $respond(404, ['ok' => false, 'message' => 'Computador não encontrado.']);
}

$user = getUserName((int) Session::getLoginUserID());
if (ProtectionPolicy::isProtected($client)) {
    // Attempts are limited per session: 5 wrong passwords lock this check for 5 minutes.
    $failures = $_SESSION['plugin_ativaremote_ti_failures'] ?? ['count' => 0, 'locked_until' => 0];
    $wait = (int) $failures['locked_until'] - time();
    if ($wait > 0) {
        $respond(429, [
            'ok'      => false,
            'message' => sprintf('Muitas tentativas incorretas. Tente novamente em %d minuto(s).', (int) ceil($wait / 60)),
        ]);
    }

    if (!Settings::verifyTiPassword((string) ($_POST['ti_password'] ?? ''))) {
        $failures['count'] = (int) $failures['count'] + 1;
        $message = 'Senha do T.I. incorreta.';
        if ($failures['count'] >= $maxFailures) {
            $failures = ['count' => 0, 'locked_until' => time() + $lockSeconds];
            $message = 'Senha do T.I. incorreta. Novas tentativas bloqueadas por 5 minutos.';
        }
        $_SESSION['plugin_ativaremote_ti_failures'] = $failures;
        Toolbox::logInFile('ativaremote', sprintf(
            "Senha do T.I. incorreta: usuario %s, computador %s\n",
            $user,
            $client['hostname']
        ));
        $respond(403, ['ok' => false, 'message' => $message]);
    }
    unset($_SESSION['plugin_ativaremote_ti_failures']);
}

try {
    $connection = $repo->connectionFor($id);
} catch (Exception $e) {
    $respond(409, ['ok' => false, 'message' => $e->getMessage()]);
}

Toolbox::logInFile('ativaremote', sprintf(
    "Conexao liberada: usuario %s, computador %s%s\n",
    $user,
    $client['hostname'],
    ProtectionPolicy::isProtected($client) ? ' (protegido, senha do T.I. conferida)' : ''
));
$respond(200, ['ok' => true] + $connection);
