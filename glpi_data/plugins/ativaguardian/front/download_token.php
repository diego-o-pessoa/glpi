<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\ConfigService;

include('../../../inc/includes.php');

// The token is a secret: only a profile that may configure the plugin can pull it,
// only via POST + CSRF, and it is served as an attachment (never rendered in a page).
if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_CONFIG, UPDATE)) {
    Session::checkRight('config', UPDATE);
}
Session::checkCSRF($_POST);

global $CFG_GLPI;
$payload = [
    'api_base' => $CFG_GLPI['url_base'] . '/plugins/ativaguardian/api/v1',
    'api_token' => (string) ConfigService::get('api_token', ''),
];

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="ativaguardian-token.json"');
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
