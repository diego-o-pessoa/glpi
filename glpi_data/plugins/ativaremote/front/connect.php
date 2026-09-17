<?php

declare(strict_types=1);

use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaremote\ProtectionPolicy;
use GlpiPlugin\Ativaremote\TiPasswordGate;

include '../../../inc/includes.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

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

$protected = ProtectionPolicy::isProtected($client);
if ($protected && !TiPasswordGate::isVerified($client)) {
    $password = (string) ($_POST['ti_password'] ?? '');
    if ($password === '') {
        $respond(401, ['ok' => false, 'password_required' => true, 'message' => 'Informe a senha do T.I.']);
    }
    try {
        TiPasswordGate::check($password, $client, 'conectar');
    } catch (RuntimeException $e) {
        $respond($e->getCode() ?: 403, ['ok' => false, 'password_required' => true, 'message' => $e->getMessage()]);
    }
    TiPasswordGate::markVerified($client);
}

try {
    $connection = $repo->connectionFor($id);
} catch (Exception $e) {
    $respond(409, ['ok' => false, 'message' => $e->getMessage()]);
}

TiPasswordGate::log($protected ? 'Conexao liberada (computador protegido)' : 'Conexao liberada', $client);
$respond(200, ['ok' => true] + $connection);
