<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Branch;

include '../../../inc/includes.php';

// Coluna "Filial" da lista de chamados: ?ids=8,7,1 -> {"8":"Matriz",...}.
// So os chamados que a pessoa pode ver entram na resposta.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (Session::getLoginUserID() === false || Session::getCurrentInterface() !== 'central') {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    return;
}

session_write_close();

$ids = array_filter(array_map('intval', explode(',', (string) ($_GET['ids'] ?? ''))));
$ids = array_slice(array_values(array_unique($ids)), 0, 200);

$branches = [];
foreach ($ids as $id) {
    $ticket = new Ticket();
    if (!$ticket->getFromDB($id) || !$ticket->canViewItem()) {
        continue;
    }
    $branches[(string) $id] = Branch::of((int) $ticket->fields['entities_id'], (int) $ticket->fields['locations_id'], $id);
}

echo json_encode(['ok' => true, 'branches' => (object) $branches], JSON_UNESCAPED_UNICODE);
