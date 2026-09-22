<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\ActionQueue;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Identificador invalido.']);
    return;
}

// Fecha o que ficou preso antes de responder: sem isso o modal ficaria girando
// para uma máquina que desligou no meio da ação.
ActionQueue::expireStale();

global $DB;
$iterator = $DB->request([
    'SELECT' => ['id', 'component', 'action', 'status', 'error_message'],
    'FROM'   => ActionQueue::TABLE,
    'WHERE'  => ['id' => $id],
    'LIMIT'  => 1,
]);

if (count($iterator) !== 1) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Ação não encontrada.']);
    return;
}

$row = $iterator->current();
echo json_encode([
    'ok'        => true,
    'id'        => (int) $row['id'],
    'component' => (string) $row['component'],
    'action'    => (string) $row['action'],
    'status'    => (string) $row['status'],
    'message'   => (string) ($row['error_message'] ?? ''),
    'finished'  => in_array((string) $row['status'], [ActionQueue::SUCCESS, ActionQueue::FAILED], true),
], JSON_UNESCAPED_UNICODE);
