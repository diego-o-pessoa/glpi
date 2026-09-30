<?php

declare(strict_types=1);

use GlpiPlugin\Ativaremote\DashboardData;

include '../../../inc/includes.php';

// Lista ao vivo do painel: o navegador consulta a cada 2 s e so redesenha
// quando a assinatura muda.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!Session::haveRight('plugin_ativaremote', READ) && !Session::haveRight('config', UPDATE)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sem permissão.']);
    return;
}

$payload = DashboardData::payload((bool) Session::haveRight('plugin_ativaremote', UPDATE));
if (($_GET['signature'] ?? '') === $payload['signature']) {
    // Nada mudou: resposta curta (o navegador so atualiza o "ha X min").
    echo json_encode(['ok' => true, 'unchanged' => true, 'now' => $payload['now'], 'signature' => $payload['signature']]);
    return;
}
echo json_encode(['ok' => true] + $payload);
