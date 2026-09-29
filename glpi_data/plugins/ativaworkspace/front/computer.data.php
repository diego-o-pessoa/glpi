<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\MachineAction;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

// Progresso das acoes (desinstalacoes) de um computador, para a barra ao vivo.
Page::requireAccess('computers');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$computerId = (int) ($_GET['computer'] ?? 0);
if ($computerId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    return;
}

echo json_encode(['ok' => true] + MachineAction::progressForComputer($computerId), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
