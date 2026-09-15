<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\OverviewPage;
use GlpiPlugin\Ativawallpaper\ServerClock;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);
// Polled every few seconds: never keep the user's PHP session locked.
session_write_close();

global $CFG_GLPI;

$context = OverviewPage::context($_GET, $CFG_GLPI['root_doc']);
$renderer = TemplateRenderer::getInstance();
$sections = [];
foreach (['kpis', 'distribution', 'activity', 'computers'] as $section) {
    $sections[$section] = $renderer->render('@ativawallpaper/overview/' . $section . '.html.twig', $context);
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
echo json_encode(
    [
        'time'         => substr(ServerClock::now(), 11, 8),
        // Lets the browser count down against the server clock.
        'server_epoch' => $context['server_epoch'],
        'sections'     => $sections,
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);
