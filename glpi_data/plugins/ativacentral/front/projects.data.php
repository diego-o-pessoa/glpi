<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Projects;

include '../../../inc/includes.php';

// Dados da tela "Projetos" para o botao Atualizar.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (
    Session::getLoginUserID() === false
    || Session::getCurrentInterface() !== 'central'
    || (!Project::canView() && !ProjectTask::canView())
) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sem permissão.']);
    return;
}

// So leitura: libera a sessao para nao segurar outras paginas.
session_write_close();

try {
    $data = Projects::dashboard();
    echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    Toolbox::logInFile('ativacentral', 'Falha ao carregar projetos: ' . $exception->getMessage() . "\n");
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Não foi possível carregar os projetos. Detalhe em files/_log/ativacentral.log.'], JSON_UNESCAPED_UNICODE);
}
