<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\ClientEventRepository;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);

$filters = [
    'q'    => $_GET['q'] ?? '',
    'type' => $_GET['type'] ?? '',
];
$repository = new ClientEventRepository();
$events = $repository->search($filters, (int) ($_GET['page'] ?? 1), 50);

global $CFG_GLPI;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper';

Html::header('Alteracoes detectadas - Ativa Wallpaper', '', 'admin', 'pluginativawallpapermenu', 'events');
TemplateRenderer::getInstance()->display('@ativawallpaper/events.html.twig', [
    'events'     => $events,
    'summary'    => $repository->summary(),
    'filters'    => $filters,
    'can_config' => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ),
    'urls'       => [
        'dashboard' => $base . '/front/dashboard.php',
        'history'   => $base . '/front/history.php',
        'events'    => $base . '/front/events.php',
        'updates'   => $base . '/front/updates.php',
        'settings'  => $base . '/front/settings.php',
    ],
]);
Html::footer();
