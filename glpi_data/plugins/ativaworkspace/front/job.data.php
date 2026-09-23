<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

// Detalhes de um provisionamento (etapas + log) para o modal "Ver log".
Page::requireAccess('provisioning');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Job::details() ja restringe as entidades do usuario: fora delas vira 404.
$details = Job::details((int) ($_GET['id'] ?? 0));
if ($details === null) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Provisionamento não encontrado.']);
    return;
}

echo json_encode(['ok' => true] + $details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
