<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\WallpaperManager;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 24;
$manager = new WallpaperManager();
$rows = $manager->history(($page - 1) * $limit, $limit);
foreach ($rows as &$row) {
    $row['created_by_name'] = getUserName((int) $row['created_by']);
}
unset($row);
$total = $manager->count();

global $CFG_GLPI, $DB;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper';
$audits = [];
$iterator = $DB->request([
    'FROM'  => 'glpi_plugin_ativawallpaper_audits',
    'ORDER' => ['created_at DESC', 'id DESC'],
    'LIMIT' => 100,
]);
foreach ($iterator as $audit) {
    $audit['user_name'] = getUserName((int) $audit['users_id']);
    $audits[] = $audit;
}

Html::header('Historico - Ativa Wallpaper', '', 'admin', 'pluginativawallpapermenu', 'history');
TemplateRenderer::getInstance()->display('@ativawallpaper/history.html.twig', [
    'wallpapers' => $rows,
    'audits'     => $audits,
    'page'       => $page,
    'pages'      => max(1, (int) ceil($total / $limit)),
    'can_publish'=> Session::haveRight(PluginAtivawallpaperProfile::RIGHT_PUBLISH, UPDATE),
    'can_config' => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ),
    'urls'       => [
        'action'    => $base . '/front/action.php',
        'dashboard' => $base . '/front/dashboard.php',
        'history'   => $base . '/front/history.php',
        'events'    => $base . '/front/events.php',
        'updates'   => $base . '/front/updates.php',
        'settings'  => $base . '/front/settings.php',
        'image'     => $base . '/front/image.php',
    ],
]);
Html::footer();
