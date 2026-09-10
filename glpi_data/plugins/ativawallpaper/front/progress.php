<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\DashboardService;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

$rollout = (new DashboardService())->rolloutProgress();
echo json_encode(
    $rollout ?? ['active' => false, 'complete' => false],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);
