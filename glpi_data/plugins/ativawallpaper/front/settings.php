<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\ConfigService;
use GlpiPlugin\Ativawallpaper\DashboardService;
use GlpiPlugin\Ativawallpaper\WallpaperManager;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ);

global $CFG_GLPI, $DB;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper';
$inventoryPlugin = new Plugin();
$inventoryActive = $inventoryPlugin->isActivated('glpiinventory');
$inventoryVersion = defined('PLUGIN_GLPIINVENTORY_VERSION') ? PLUGIN_GLPIINVENTORY_VERSION : null;

$cron = null;
if ($DB->tableExists('glpi_crontasks')) {
    $iterator = $DB->request([
        'FROM'  => 'glpi_crontasks',
        'WHERE' => ['itemtype' => 'PluginGlpiinventoryTask', 'name' => 'taskscheduler'],
        'LIMIT' => 1,
    ]);
    $row = $iterator->current();
    $cron = is_array($row) ? $row : null;
}

$settings = ConfigService::all();
$secretReady = strlen((string) $settings['registration_secret_hash']) === 64;
$settings['registration_secret_hash'] = '';
$cronRecent = $cron !== null && !empty($cron['lastrun']) && strtotime((string) $cron['lastrun']) >= time() - 3600;

Html::header('Configuracoes - Ativa Wallpaper', '', 'admin', 'pluginativawallpapermenu', 'settings');
TemplateRenderer::getInstance()->display('@ativawallpaper/settings.html.twig', [
    'settings'          => $settings,
    'inventory_active'  => $inventoryActive,
    'inventory_version' => $inventoryVersion,
    'inventory_endpoint'=> 'https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/',
    'cron'              => $cron,
    'cron_recent'       => $cronRecent,
    'secret_ready'      => $secretReady,
    'current_wallpaper' => (new WallpaperManager())->current(),
    'client_versions'   => (new DashboardService())->clientVersionCounts(),
    'can_configure'     => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE),
    'urls'              => [
        'action'    => $base . '/front/action.php',
        'dashboard' => $base . '/front/dashboard.php',
        'history'   => $base . '/front/history.php',
        'events'    => $base . '/front/events.php',
        'settings'  => $base . '/front/settings.php',
    ],
]);
Html::footer();
