<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\Storage;
use GlpiPlugin\Ativawallpaper\WallpaperManager;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);
$wallpaper = (new WallpaperManager())->findById((int) ($_GET['id'] ?? 0));
if ($wallpaper === null) {
    http_response_code(404);
    exit;
}

$thumbnail = ($_GET['thumbnail'] ?? '') === '1';
$path = $thumbnail
    ? Storage::thumbnailPath((string) $wallpaper['thumbnail_filename'])
    : Storage::wallpaperPath((string) $wallpaper['filename']);
if (!is_file($path) || !is_readable($path)) {
    http_response_code(404);
    exit;
}

$etag = $thumbnail ? hash_file('sha256', $path) : (string) $wallpaper['sha256'];
$ifNoneMatch = trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''), ' W/"');
header('Cache-Control: private, max-age=3600');
header('ETag: "' . $etag . '"');
header('X-Content-Type-Options: nosniff');
if ($ifNoneMatch === $etag) {
    http_response_code(304);
    exit;
}
header('Content-Type: ' . ($thumbnail ? 'image/jpeg' : $wallpaper['mime_type']));
header('Content-Length: ' . (string) filesize($path));
readfile($path);
exit;
