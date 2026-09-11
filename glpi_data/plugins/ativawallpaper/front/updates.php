<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\UpdateManager;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ);

global $CFG_GLPI;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper';
$manager = new UpdateManager();
$packages = $manager->packages();
foreach ($packages as &$package) {
    $package['created_by_name'] = getUserName((int) $package['created_by']);
    $package['released_by_name'] = !empty($package['released_by']) ? getUserName((int) $package['released_by']) : '';
}
unset($package);
$focusPackage = null;
foreach ($packages as $package) {
    if (in_array((string) $package['release_stage'], ['pilot', 'all'], true)) {
        $focusPackage = $package;
        break;
    }
}
$focusPackage ??= $packages[0] ?? null;

Html::header('Atualizacoes - Ativa Wallpaper', '', 'admin', 'pluginativawallpapermenu', 'updates');
TemplateRenderer::getInstance()->display('@ativawallpaper/updates.html.twig', [
    'packages'      => $packages,
    'focus_package' => $focusPackage,
    'installations' => $manager->recentInstallations(),
    'can_configure' => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE),
    'urls'          => [
        'action'    => $base . '/front/action.php',
        'dashboard' => $base . '/front/dashboard.php',
        'history'   => $base . '/front/history.php',
        'events'    => $base . '/front/events.php',
        'updates'   => $base . '/front/updates.php',
        'progress'  => $base . '/front/update_progress.php',
        'settings'  => $base . '/front/settings.php',
    ],
]);
Html::footer();
