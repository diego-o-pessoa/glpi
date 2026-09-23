<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Environment;
use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\WorkspaceConfig;

include('../../../inc/includes.php');

Page::requireAccess('settings');

// POST de configuracao (CSRF validado pelo GLPI). Exige administrar o Workspace.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, UPDATE);

    if (isset($_POST['simulation'])) {
        $enabled = (string) $_POST['simulation'] === '1';
        WorkspaceConfig::setSimulation($enabled);
        Event::log(Event::LEVEL_SECURITY, 'settings', $enabled ? 'Modo de simulação LIGADO' : 'Modo de simulação desligado');
        Session::addMessageAfterRedirect($enabled ? 'Modo de simulação ligado.' : 'Modo de simulação desligado.', false, INFO);
    } elseif (isset($_POST['save_entra'])) {
        $domain = mb_strtolower(trim((string) ($_POST['entra_domain'] ?? '')));
        $tenant = mb_strtolower(trim((string) ($_POST['entra_tenant_id'] ?? '')));
        if ($domain !== '' && !preg_match('/^(?=.{1,255}$)([a-z0-9-]+\.)+[a-z]{2,}$/D', $domain)) {
            Session::addMessageAfterRedirect('Domínio inválido.', false, ERROR);
        } elseif ($tenant !== '' && !WorkspaceConfig::isGuid($tenant)) {
            Session::addMessageAfterRedirect('Tenant ID deve ser um GUID (ou vazio).', false, ERROR);
        } else {
            WorkspaceConfig::set(['entra_domain' => $domain, 'entra_tenant_id' => $tenant]);
            Event::log(Event::LEVEL_SECURITY, 'settings', 'Configuração do Entra alterada', ['dominio' => $domain, 'tenant' => $tenant]);
            Session::addMessageAfterRedirect('Configuração do Entra salva.', false, INFO);
        }
    } elseif (isset($_POST['regenerate_token'])) {
        WorkspaceConfig::regenerateApiToken();
        Event::log(Event::LEVEL_SECURITY, 'settings', 'Token da API do Workspace regenerado');
        Session::addMessageAfterRedirect('Token regenerado. Atualize o serviço nas máquinas.', false, WARNING);
    }
    Html::redirect(Page::href('settings'));
}

$canConfigure = (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, UPDATE);
$token = WorkspaceConfig::apiToken();

Page::render('settings', 'settings.html.twig', [
    'versions'           => Environment::versions(),
    'plugins'            => Environment::relatedPlugins(),
    'storage'            => Environment::storage(),
    'simulation_enabled' => WorkspaceConfig::simulationEnabled(),
    'entra_domain'       => WorkspaceConfig::entraDomain(),
    'entra_tenant_id'    => WorkspaceConfig::entraTenantId(),
    'api_enabled'        => WorkspaceConfig::apiEnabled(),
    // O token so aparece por inteiro para quem administra; senao, mascarado.
    'api_token'          => $canConfigure ? $token : ($token !== '' ? '••••••••' : ''),
    'can_configure'      => $canConfigure,
    'action_url'         => Page::href('settings'),
    'config_download_url'=> Page::href('config_download'),
    // Token proprio (standalone): o download devolve um arquivo e nao recarrega
    // a pagina, entao ele nao pode consumir o token compartilhado dos outros forms.
    'download_csrf'      => Session::getNewCSRFToken(true),
]);
