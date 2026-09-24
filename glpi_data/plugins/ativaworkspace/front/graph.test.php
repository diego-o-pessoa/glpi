<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\GraphClient;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('settings');
PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, UPDATE);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$respond = static function (int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, ['ok' => false, 'message' => 'Método não permitido.']);
    return;
}

try {
    $result = GraphClient::testConnection();
    Event::log(Event::LEVEL_SECURITY, 'entra', 'Conexão com o Microsoft Graph validada', [
        'permissao' => $result['permission'],
    ]);
    $respond(200, [
        'ok'         => true,
        'message'    => 'Credencial e permissão validadas. A política do usuário será confirmada ao gerar o TAP.',
        'permission' => $result['permission'],
        'expires_at' => $result['expires_at'],
    ]);
} catch (RuntimeException $exception) {
    Event::log(Event::LEVEL_WARNING, 'entra', 'Teste do Microsoft Graph falhou', [
        'motivo' => mb_substr($exception->getMessage(), 0, 200),
    ]);
    $respond(422, ['ok' => false, 'message' => $exception->getMessage()]);
} catch (Throwable $exception) {
    Event::log(Event::LEVEL_ERROR, 'entra', 'Erro inesperado no teste do Microsoft Graph', [
        'tipo' => $exception::class,
    ]);
    $respond(500, ['ok' => false, 'message' => 'Erro inesperado ao testar o Microsoft Graph. Consulte os logs do GLPI.']);
}
