<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\WorkspaceConfig;

include('../../../inc/includes.php');

// POST obrigatorio: o CheckCsrfListener do GLPI so valida CSRF em metodos com
// corpo, e um token nao deve sair por uma URL que fique no historico.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// Nao chamamos Session::checkCSRF: o CheckCsrfListener ja validou e consumiu o
// token deste POST (uma segunda checagem recusaria o download).
Page::requireAccess('settings');
PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, UPDATE);

global $CFG_GLPI;

$apiToken = WorkspaceConfig::apiToken();
if ($apiToken === '') {
    $apiToken = WorkspaceConfig::regenerateApiToken();
}

$payload = [
    'api_url'    => rtrim((string) $CFG_GLPI['url_base'], '/') . '/plugins/ativaworkspace/api/v1',
    'api_token'  => $apiToken,
    'verify_tls' => true,
];

$content = json_encode(
    $payload,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);

// Fecha a sessao e limpa buffers: um aviso do PHP no meio corromperia o arquivo.
session_write_close();
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="ativaworkspace-service-config.json"');
header('Content-Length: ' . strlen($content));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
echo $content;
exit;
