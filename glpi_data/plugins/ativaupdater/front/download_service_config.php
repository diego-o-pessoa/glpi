<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ConfigService;

include('../../../inc/includes.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

if (!Session::haveRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, UPDATE)) {
    Session::checkRight('config', UPDATE);
}

$apiUrl = rtrim((string) ConfigService::get('api_base_url', ''), '/');
$apiToken = (string) ConfigService::get('api_token', '');
if ($apiUrl === '' || $apiToken === '') {
    throw new RuntimeException('A API ou o token do Ativa Updater não está configurado.');
}

$payload = [
    'api_url'                => $apiUrl,
    'api_token'              => $apiToken,
    'check_interval_seconds' => max(300, min(86400, ConfigService::getInt('check_interval_seconds', 3600))),
    'verify_tls'             => true,
];
$content = json_encode(
    $payload,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);

session_write_close();
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="ativaupdater-service-config.json"');
header('Content-Length: ' . strlen($content));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
echo $content;
exit;
