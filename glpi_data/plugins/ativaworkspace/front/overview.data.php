<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Overview;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

// JSON consultado a cada poucos segundos pela Visao Geral e pelo Provisionamento.
Page::requireAccess('overview');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

echo json_encode(
    Overview::payload((int) ($_GET['jobs'] ?? 10), (int) ($_GET['events'] ?? 0)),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
