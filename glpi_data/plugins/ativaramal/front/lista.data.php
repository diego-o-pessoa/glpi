<?php

declare(strict_types=1);

use GlpiPlugin\Ativaramal\Directory;

include '../../../inc/includes.php';

// Dados ao vivo da lista de ramais. "unchanged" quando nada mudou desde a
// assinatura que o navegador ja tem (a tela nao redesenha).
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!PluginAtivaramalProfile::canView()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sem permissão.']);
    return;
}
// A consulta a TW pode levar alguns segundos: nao trava as outras abas.
session_write_close();

$payload = Directory::payload();
if (($_GET['signature'] ?? '') === $payload['signature']) {
    echo json_encode(['ok' => true, 'unchanged' => true, 'updated' => $payload['updated'], 'signature' => $payload['signature']]);
    return;
}
echo json_encode(['ok' => true] + $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
