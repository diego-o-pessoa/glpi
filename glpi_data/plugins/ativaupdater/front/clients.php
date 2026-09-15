<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ClientsView;

include('../../../inc/includes.php');

// Polled by dashboard.php every few seconds to refresh the "Computadores" section in place.
if (!Session::haveRight(PluginAtivaupdaterProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}
$canManage = Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)
    || Session::haveRight('config', UPDATE);

// Do not hold the PHP session lock while other dashboard requests are running.
session_write_close();

$now = time();
$summary = ClientsView::load(false);
$signature = ClientsView::signature($summary, $now);
$payload = [
    'changed'   => false,
    'signature' => $signature,
    'metrics'   => ClientsView::metrics($summary, $now),
];
if (!hash_equals($signature, (string) ($_GET['signature'] ?? ''))) {
    $payload['changed'] = true;
    $payload['html'] = ClientsView::render(ClientsView::load(true), $canManage, $now);
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
exit;
