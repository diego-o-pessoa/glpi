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

Html::header('GLPI Agent', '', 'admin', 'pluginativawallpapermenu', 'glpi_agent');
TemplateRenderer::getInstance()->display('@ativawallpaper/glpi_agent.html.twig', [
    'rights'        => [
        'config'  => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ),
        'publish' => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_PUBLISH, READ),
    ],
    'urls'          => [
        'action'     => $base . '/front/action.php',
        'dashboard'  => $base . '/front/dashboard.php',
        'glpi_agent' => $base . '/front/glpi_agent.php',
        'history'    => $base . '/front/history.php',
        'events'     => $base . '/front/events.php',
        'updates'    => $base . '/front/updates.php',
        'settings'   => $base . '/front/settings.php',
    ],
]);
Html::footer();
