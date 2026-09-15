<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ServerClock;

include('../../../inc/includes.php');

Session::checkLoginUser();
if (!Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)) {
    Session::checkRight('config', UPDATE);
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    $payload = ['ok' => false, 'message' => 'Computador inválido.'];
} else {
    global $DB;
    $iterator = $DB->request([
        'SELECT' => [
            'id', 'hostname', 'updater_version', 'status', 'message', 'last_check',
            'check_requested_at', 'check_acknowledged_at', 'check_request_seq', 'check_ack_seq',
            'command', 'install_log', 'diagnostics_log', 'diagnostics_at',
        ],
        'FROM'  => 'glpi_plugin_ativaupdater_clients',
        'WHERE' => ['id' => $id],
        'LIMIT' => 1,
    ]);
    if (count($iterator) !== 1) {
        http_response_code(404);
        $payload = ['ok' => false, 'message' => 'Computador não encontrado.'];
    } else {
        $client = $iterator->current();
        $timestamp = static fn(mixed $value): int => ServerClock::toTimestamp(is_string($value) ? $value : null);
        $payload = [
            'ok'                    => true,
            'id'                    => (int) $client['id'],
            'hostname'              => (string) $client['hostname'],
            'updater_version'       => (string) $client['updater_version'],
            'status'                => (string) $client['status'],
            'message'               => (string) ($client['message'] ?? ''),
            'command'               => (string) ($client['command'] ?? ''),
            'request_seq'           => (int) ($client['check_request_seq'] ?? 0),
            'ack_seq'               => (int) ($client['check_ack_seq'] ?? 0),
            'requested_at'          => (string) ($client['check_requested_at'] ?? ''),
            'requested_at_ts'       => $timestamp($client['check_requested_at'] ?? null),
            'acknowledged_at'       => (string) ($client['check_acknowledged_at'] ?? ''),
            'acknowledged_at_ts'    => $timestamp($client['check_acknowledged_at'] ?? null),
            'last_check'            => (string) $client['last_check'],
            'last_check_ts'         => $timestamp($client['last_check']),
            'diagnostics_at'        => (string) ($client['diagnostics_at'] ?? ''),
            'diagnostics_at_ts'     => $timestamp($client['diagnostics_at'] ?? null),
            'install_log'           => (string) ($client['install_log'] ?? ''),
            'diagnostics_log'       => (string) ($client['diagnostics_log'] ?? ''),
            'server_time_ts'        => time(),
        ];
    }
}

session_write_close();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
exit;
