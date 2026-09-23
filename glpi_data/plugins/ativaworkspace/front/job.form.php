<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningEngine;

include('../../../inc/includes.php');

// Cria e inicia um provisionamento. Chamado por AJAX pelo modal "Novo
// provisionamento": o CSRF vem no header X-Glpi-Csrf-Token e ja foi validado
// pelo GLPI (CheckCsrfListener) antes de chegar aqui.
Page::requireAccess('provisioning');
PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, CREATE);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
    return;
}

try {
    $id = ProvisioningEngine::create(
        (int) ($_POST['computers_id'] ?? 0),
        (int) ($_POST['plugin_ativaworkspace_provisioningprofiles_id'] ?? 0),
        (string) ($_POST['employee_name'] ?? ''),
        (string) ($_POST['upn'] ?? '')
    );
} catch (RuntimeException $exception) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
    return;
}

echo json_encode([
    'ok'      => true,
    'id'      => $id,
    'url'     => Page::href('job', ['id' => $id]),
    'message' => 'Provisionamento #' . $id . ' iniciado.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
