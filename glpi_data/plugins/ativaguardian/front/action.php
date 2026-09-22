<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\ActionQueue;

include('../../../inc/includes.php');

// Enfileirar uma correção exige direito próprio: ver o painel (RIGHT_VIEW) nao
// autoriza agir sobre a maquina.
if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_MANAGE, UPDATE)) {
    Session::checkRight('config', UPDATE);
}

// Sem Session::checkCSRF aqui: o CheckCsrfListener do GLPI ja validou este POST
// (para AJAX ele le o cabecalho X-Glpi-Csrf-Token). Checar de novo recusaria.
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metodo nao permitido.']);
    return;
}

$machinesId = (int) ($_POST['machines_id'] ?? 0);
$component  = strtolower(trim((string) ($_POST['component'] ?? '')));
$action     = strtoupper(trim((string) ($_POST['action'] ?? '')));

// Dupla validação fechada: nada aqui vem de texto livre do cliente.
if ($machinesId <= 0
    || !in_array($action, ActionQueue::ACTIONS, true)
    || !ActionQueue::supports($component, $action)
) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Componente ou acao invalida.']);
    return;
}

$actionId = ActionQueue::enqueue($machinesId, $component, $action, (int) Session::getLoginUserID());
if ($actionId <= 0) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Nao foi possivel registrar a acao.']);
    return;
}

$labels = [
    ActionQueue::CHECK   => 'Verificação solicitada',
    ActionQueue::START   => 'Início solicitado',
    ActionQueue::RESTART => 'Reinício solicitado',
];

echo json_encode([
    'ok'        => true,
    'action_id' => $actionId,
    'message'   => ($labels[$action] ?? 'Ação solicitada') . '. O Guardian executa na próxima coleta (até 30 s).',
], JSON_UNESCAPED_UNICODE);
