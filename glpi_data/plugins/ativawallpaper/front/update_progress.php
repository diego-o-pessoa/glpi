<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\UpdateManager;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ);

$manager = new UpdateManager();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
echo json_encode([
    'packages'      => $manager->packages(),
    'installations' => $manager->recentInstallations(),
    'server_time'   => date(DATE_ATOM),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
