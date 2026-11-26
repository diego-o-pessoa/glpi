<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    include("../../../inc/includes.php");
}

require_once __DIR__ . '/_common.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$respond = static function (array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
};

if (Session::getLoginUserID() === false || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    $respond(['ok' => false, 'message' => 'Requisição inválida ou sem permissão.'], 403);
    return;
}

$uid = (int) Session::getLoginUserID();
$myGroups = array_values(array_filter(array_map('intval', (array) ($_SESSION['glpigroups'] ?? []))));

$projectId = (int) ($_POST['project_id'] ?? 0);
$taskId = (int) ($_POST['task_id'] ?? 0);
$action = trim((string) ($_POST['action'] ?? ''));

$detail = painel_project_detail($projectId, $uid, $myGroups);
if ($detail === null || !isset($detail['tasks_map'][$taskId])) {
    $respond(['ok' => false, 'message' => 'Tarefa não encontrada ou sem acesso ao projeto.', 'csrf' => Session::getNewCSRFToken()], 404);
    return;
}

// Cada um so altera as tarefas em que e responsavel ou esta na equipe
// (usuario ou grupo); as dos outros ficam so para leitura.
if (empty($detail['tasks_map'][$taskId]['mine'])) {
    $respond(['ok' => false, 'message' => 'Você só pode atualizar as tarefas atribuídas a você.', 'csrf' => Session::getNewCSRFToken()], 403);
    return;
}

$task = new ProjectTask();
if (
    !$task->getFromDB($taskId)
    || (int) $task->fields['projects_id'] !== $projectId
    || (int) ($task->fields['is_deleted'] ?? 0) === 1
    || (int) ($task->fields['is_template'] ?? 0) === 1
) {
    $respond(['ok' => false, 'message' => 'Tarefa indisponível.', 'csrf' => Session::getNewCSRFToken()], 404);
    return;
}

try {
    if ($action === 'set_status') {
        // O usuario so escolhe o status; o percentual e automatico
        // (concluida = 100%, senao 0%; fases e projeto pela media).
        if (!empty($detail['tasks_map'][$taskId]['children'])) {
            $respond(['ok' => false, 'message' => 'O andamento desta fase é calculado pelas tarefas dela.', 'csrf' => Session::getNewCSRFToken()], 400);
            return;
        }
        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, ['todo', 'running', 'done'], true)) {
            $respond(['ok' => false, 'message' => 'Status inválido.', 'csrf' => Session::getNewCSRFToken()], 400);
            return;
        }
        $stateId = painel_state_for_kind($status);
        if ($stateId === null) {
            $respond(['ok' => false, 'message' => 'Este status não está cadastrado nos estados de projeto do GLPI.', 'csrf' => Session::getNewCSRFToken()], 400);
            return;
        }

        $ok = $task->update([
            'id'               => $taskId,
            'projectstates_id' => $stateId,
            'percent_done'     => $status === 'done' ? 100 : 0,
        ]);
        if (!$ok) {
            $respond(['ok' => false, 'message' => 'Não foi possível salvar o status.', 'csrf' => Session::getNewCSRFToken()], 500);
            return;
        }
        painel_project_recalc($projectId);

        $fresh = painel_project_detail($projectId, $uid, $myGroups);
        $payload = painel_project_client_payload($fresh);
        $respond([
            'ok'      => true,
            'message' => $status === 'done' ? 'Tarefa concluída!' : 'Status atualizado.',
            'project' => $payload,
            'task'    => $payload['tasks'][(string) $taskId] ?? null,
            'csrf'    => Session::getNewCSRFToken(),
        ]);
        return;
    }

    if ($action === 'add_note') {
        $content = trim((string) ($_POST['content'] ?? ''));
        if (mb_strlen($content) < 2) {
            $respond(['ok' => false, 'message' => 'Escreva o que foi desenvolvido antes de enviar.', 'csrf' => Session::getNewCSRFToken()], 400);
            return;
        }

        $notepad = new Notepad();
        $noteId = $notepad->add([
            'itemtype' => 'ProjectTask',
            'items_id' => $taskId,
            'content'  => $content,
        ]);

        if (!$noteId) {
            $respond(['ok' => false, 'message' => 'Não foi possível registrar a atualização.', 'csrf' => Session::getNewCSRFToken()], 500);
            return;
        }

        $fresh = painel_project_detail($projectId, $uid, $myGroups);
        $payload = painel_project_client_payload($fresh);
        $respond([
            'ok'      => true,
            'message' => 'Atualização registrada para o dono do projeto.',
            'project' => $payload,
            'task'    => $payload['tasks'][(string) $taskId] ?? null,
            'csrf'    => Session::getNewCSRFToken(),
        ]);
        return;
    }

    $respond(['ok' => false, 'message' => 'Ação desconhecida.', 'csrf' => Session::getNewCSRFToken()], 400);
} catch (Throwable $exception) {
    Toolbox::logInFile('painel', 'Erro em project_task_action: ' . $exception->getMessage() . "\n");
    $respond(['ok' => false, 'message' => 'Erro interno ao salvar atualização.', 'csrf' => Session::getNewCSRFToken()], 500);
}
