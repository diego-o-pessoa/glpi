<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Environment;
use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\WorkspaceConfig;

include('../../../inc/includes.php');

Page::requireAccess('settings');

// Liga/desliga o modo de simulacao do Job Engine (CSRF validado pelo GLPI).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulation'])) {
    PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, UPDATE);
    $enabled = (string) $_POST['simulation'] === '1';
    WorkspaceConfig::setSimulation($enabled);
    Event::log(Event::LEVEL_SECURITY, 'settings', $enabled ? 'Modo de simulação LIGADO' : 'Modo de simulação desligado');
    Session::addMessageAfterRedirect($enabled ? 'Modo de simulação ligado.' : 'Modo de simulação desligado.', false, INFO);
    Html::redirect(Page::href('settings'));
}

Page::render('settings', 'settings.html.twig', [
    'versions'           => Environment::versions(),
    'plugins'            => Environment::relatedPlugins(),
    'storage'            => Environment::storage(),
    'simulation_enabled' => WorkspaceConfig::simulationEnabled(),
    'can_configure'      => (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, UPDATE),
    'action_url'         => Page::href('settings'),
]);
