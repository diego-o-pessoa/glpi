<?php

declare(strict_types=1);

use GlpiPlugin\Ativaprint\PrinterRepository;

include '../../../inc/includes.php';

require_once dirname(__DIR__) . '/setup.php';
require_once dirname(__DIR__) . '/src/PrintConfig.php';
require_once dirname(__DIR__) . '/src/PrinterRepository.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!Session::haveRight('plugin_ativaprint', READ) && !Session::haveRight('config', UPDATE)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sem permissão.']);
    exit;
}

$days = isset($_GET['days']) ? (int) $_GET['days'] : 7;
if (!in_array($days, [7, 30, 90], true)) {
    $days = 7;
}

$filters = [
    'search'        => trim((string) ($_GET['search'] ?? '')),
    'status'        => trim((string) ($_GET['status'] ?? '')),
    'critical_only' => !empty($_GET['critical_only']),
];

try {
    $kpis = PrinterRepository::getKpis($days);
    $printers = PrinterRepository::getAll($filters);
    $supplies = PrinterRepository::getSupplies();

    echo json_encode([
        'ok'       => true,
        'days'     => $days,
        'kpis'     => $kpis,
        'printers' => $printers,
        'supplies' => $supplies,
        'updated'  => date('H:i:s'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
