<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use RuntimeException;

final class Storage
{
    public static function root(): string
    {
        return GLPI_PLUGIN_DOC_DIR . '/ativawallpaper';
    }

    public static function wallpaperPath(string $internalFilename): string
    {
        return self::safePath(self::root() . '/wallpapers', $internalFilename);
    }

    public static function thumbnailPath(string $internalFilename): string
    {
        return self::safePath(self::root() . '/thumbnails', $internalFilename);
    }

    public static function temporaryPath(string $suffix = '.tmp'): string
    {
        return self::safePath(self::root() . '/tmp', bin2hex(random_bytes(20)) . $suffix);
    }

    private static function safePath(string $directory, string $filename): string
    {
        if (preg_match('/^[a-f0-9]{32,64}\.(?:jpg|png|tmp)$/', $filename) !== 1) {
            throw new RuntimeException('Nome interno de arquivo invalido.');
        }
        return $directory . '/' . $filename;
    }
}
