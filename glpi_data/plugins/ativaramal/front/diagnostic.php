<?php

declare(strict_types=1);

use GlpiPlugin\Ativaramal\ApiDiagnostics;
use GlpiPlugin\Ativaramal\Logger;

include '../../../inc/includes.php';

// Diagnostico da API da TW: um GET autenticado por chamada (a tela chama um
// caminho de cada vez). Usa o token da integracao, entao exige UPDATE.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
    return;
}
if (!Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, UPDATE)) {
    Logger::warning('Diagnóstico da API sem permissão');
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sem permissão para testar a API.']);
    return;
}

// Sessao liberada: varios testes em sequencia nao travam outras abas do GLPI.
session_write_close();

try {
    $mode = ($_POST['mode'] ?? '') === 'client' ? 'client' : 'user';
    $result = ApiDiagnostics::probe((string) ($_POST['path'] ?? ''), $mode);
    $result['mode'] = $mode;
    echo json_encode(['ok' => true, 'result' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (RuntimeException $exception) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}
