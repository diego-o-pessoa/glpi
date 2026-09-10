<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\ConfigService;
use GlpiPlugin\Ativawallpaper\DashboardService;
use GlpiPlugin\Ativawallpaper\WallpaperManager;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);

global $CFG_GLPI;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper';

$wallpaperManager = new WallpaperManager();
$current = $wallpaperManager->current();
if ($current !== null) {
    $current['created_by_name'] = getUserName((int) $current['created_by']);
}

$filters = [
    'q'         => $_GET['q'] ?? '',
    'status'    => $_GET['status'] ?? 'all',
    'sort'      => $_GET['sort'] ?? 'last_check',
    'direction' => $_GET['direction'] ?? 'desc',
];
$dashboard = new DashboardService();
$clients = $dashboard->clients($filters, $current, (int) ($_GET['page'] ?? 1), 25);

Html::header('Ativa Wallpaper', '', 'admin', 'pluginativawallpapermenu', 'dashboard');
TemplateRenderer::getInstance()->display('@ativawallpaper/dashboard.html.twig', [
    'current'       => $current,
    'enabled'       => ConfigService::getBool('enabled'),
    'summary'       => $dashboard->summary($current),
    'rollout'       => $dashboard->rolloutProgress(),
    'clients'       => $clients,
    'filters'       => $filters,
    'max_upload_mb' => ConfigService::getInt('max_upload_mb'),
    'urls'          => [
        'action'    => $base . '/front/action.php',
        'dashboard' => $base . '/front/dashboard.php',
        'history'   => $base . '/front/history.php',
        'events'    => $base . '/front/events.php',
        'settings'  => $base . '/front/settings.php',
        'image'     => $base . '/front/image.php',
        'progress'  => $base . '/front/progress.php',
    ],
    'rights' => [
        'publish' => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_PUBLISH, UPDATE),
        'config'  => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE),
        'clients' => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CLIENTS, UPDATE),
    ],
]);
Html::footer();
