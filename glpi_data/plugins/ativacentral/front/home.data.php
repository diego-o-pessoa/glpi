<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Metrics;

include '../../../inc/includes.php';

// Dados da "Visao geral" para outro periodo ou para o botao Atualizar.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (Session::getLoginUserID() === false || Session::getCurrentInterface() !== 'central') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sem permissão.']);
    return;
}

// So leitura: libera a sessao para nao segurar outras paginas.
session_write_close();

try {
    $data = Metrics::dashboard((int) ($_GET['days'] ?? 7));
    echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    Toolbox::logInFile('ativacentral', 'Falha ao calcular o painel: ' . $exception->getMessage() . "\n");
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Não foi possível calcular o painel. Detalhe em files/_log/ativacentral.log.'], JSON_UNESCAPED_UNICODE);
}
