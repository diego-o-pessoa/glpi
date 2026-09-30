<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaremote\DashboardData;
use GlpiPlugin\Ativaremote\Settings;

include '../../../inc/includes.php';

if (!Session::haveRight('plugin_ativaremote', READ) && !Session::haveRight('config', UPDATE)) {
    Html::displayRightError();
}

global $CFG_GLPI;

// haveRight() returns the right bits (int), not a bool.
$canManage = (bool) Session::haveRight('plugin_ativaremote', UPDATE);
$base = $CFG_GLPI['root_doc'] . '/plugins/ativaremote';

Html::header('Ativa Remote', '', 'admin', 'pluginativaremotemenu', 'dashboard');
// GLPI resolves "@ativaremote/..." to plugins/ativaremote/templates/ on disk.
// A lista e desenhada no navegador a partir destes dados e atualizada ao vivo
// por front/clients.php (sem recarregar a pagina).
TemplateRenderer::getInstance()->display('@ativaremote/dashboard.html.twig', [
    'initial'          => DashboardData::payload($canManage),
    'can_manage'       => $canManage,
    'updater_active'   => Plugin::isPluginActive('ativaupdater'),
    'wallpaper_active' => Plugin::isPluginActive('ativawallpaper'),
    'can_configure'    => (bool) Session::haveRight('config', UPDATE),
    'default_password' => Settings::isDefaultTiPassword(),
    'urls' => [
        'action'  => $base . '/front/action.php',
        'connect' => $base . '/front/connect.php',
        'clients' => $base . '/front/clients.php',
        'config'  => $base . '/front/config.php',
    ],
]);
Html::footer();
