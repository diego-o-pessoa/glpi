<?php

declare(strict_types=1);

include '../../../inc/includes.php';

// Botao "Finalizar chamado" do Ativa Central.
//
// GET  ?id=N                  -> o que este usuario pode fazer com o chamado.
// POST id, mode, content      -> registra a solucao (mode=solve) e, se pedido
//                                (mode=close), fecha o chamado.
//
// Tudo passa pelas classes do proprio GLPI (ITILSolution e Ticket): valem os
// mesmos direitos, regras, campos obrigatorios e notificacoes de quando se
// usa "Adicionar uma solucao" na tela nativa. O token CSRF vem no cabecalho
// X-Glpi-Csrf-Token e e conferido pelo GLPI antes deste script.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$respond = static function (array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
};

if (Session::getLoginUserID() === false || Session::getCurrentInterface() !== 'central') {
    $respond(['ok' => false, 'message' => 'Sem permissão.'], 403);
    return;
}

$id = (int) ($_REQUEST['id'] ?? 0);
$ticket = new Ticket();
if ($id <= 0 || !$ticket->getFromDB($id) || !$ticket->can($id, READ)) {
    $respond(['ok' => false, 'message' => 'Chamado não encontrado.'], 404);
    return;
}

/** O que da para fazer agora com o chamado. */
$state = static function (Ticket $ticket): array {
    $id = (int) $ticket->fields['id'];
    $status = (int) $ticket->fields['status'];
    $closed = in_array($status, Ticket::getClosedStatusArray(), true);
    $solved = in_array($status, Ticket::getSolvedStatusArray(), true);

    // canSolve() e a regra do proprio chamado (direito de atualizar ou ser o
    // tecnico atribuido, e o ciclo de vida do perfil permitir "solucionado").
    // A checagem generica do ITILSolution nao serve aqui: para um item novo
    // ela nao enxerga o chamado e sempre devolve "nao pode".
    $canSolve = !$closed && !$solved && $ticket->canSolve();
    $canClose = !$closed && $ticket->canUpdateItem() && Ticket::isAllowedStatus($status, Ticket::CLOSED);

    return ['status' => $status, 'solved' => $solved, 'closed' => $closed, 'can_solve' => (bool) $canSolve, 'can_close' => (bool) $canClose];
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    $respond(['ok' => true] + $state($ticket));
    return;
}

$mode = (string) ($_POST['mode'] ?? '');
$text = trim((string) ($_POST['content'] ?? ''));
$now = $state($ticket);

try {
    if (!in_array($mode, ['solve', 'close'], true)) {
        $respond(['ok' => false, 'message' => 'Ação inválida.'], 422);
        return;
    }
    if ($mode === 'close' && !$now['can_close']) {
        $respond(['ok' => false, 'message' => 'Seu perfil não pode fechar este chamado.'], 403);
        return;
    }

    // Chamado ainda em aberto: a solucao e obrigatoria (e o que fica no historico).
    if (!$now['solved']) {
        if (!$now['can_solve']) {
            $respond(['ok' => false, 'message' => 'Seu perfil não pode solucionar este chamado.'], 403);
            return;
        }
        if (mb_strlen($text) < 3) {
            $respond(['ok' => false, 'message' => 'Descreva o que foi feito para resolver o chamado.'], 422);
            return;
        }
        $solution = new ITILSolution();
        $added = $solution->add([
            'itemtype' => Ticket::class,
            'items_id' => $id,
            // Texto simples do tecnico -> conteudo do GLPI (HTML), sem aceitar marcacao.
            'content'  => nl2br(htmlescape(mb_substr($text, 0, 20000))),
        ]);
        if (!$added) {
            // O motivo exato (ex.: campo obrigatorio do modelo) fica nas
            // mensagens do GLPI, mostradas quando a pagina recarregar.
            $respond(['ok' => false, 'message' => 'O GLPI não aceitou a solução. Confira os campos obrigatórios do chamado (categoria, tipo, etc.).', 'reload' => true], 422);
            return;
        }
        $ticket->getFromDB($id);
    }

    if ($mode === 'close') {
        $status = (int) $ticket->fields['status'];
        if (!in_array($status, Ticket::getClosedStatusArray(), true)) {
            if (!Ticket::isAllowedStatus($status, Ticket::CLOSED) || !$ticket->update(['id' => $id, 'status' => Ticket::CLOSED])) {
                $respond(['ok' => false, 'message' => 'A solução foi registrada, mas o chamado não pôde ser fechado. Feche pelo campo Status.', 'reload' => true], 422);
                return;
            }
        }
        $respond(['ok' => true, 'message' => 'Chamado fechado.']);
        return;
    }

    $respond(['ok' => true, 'message' => 'Chamado solucionado.']);
} catch (Throwable $exception) {
    Toolbox::logInFile('ativacentral', 'Falha ao finalizar o chamado ' . $id . ': ' . $exception->getMessage() . "\n");
    $respond(['ok' => false, 'message' => 'Não foi possível finalizar o chamado. Detalhe em files/_log/ativacentral.log.'], 500);
}
