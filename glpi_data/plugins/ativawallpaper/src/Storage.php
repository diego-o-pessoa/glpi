<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use RuntimeException;

final class Storage
{
    private const DIRECTORIES = ['wallpapers', 'thumbnails', 'updates', 'tmp'];

    public static function root(): string
    {
        return GLPI_PLUGIN_DOC_DIR . '/ativawallpaper';
    }

    public static function prepare(int $requiredBytes = 0): void
    {
        $root = self::root();
        foreach (array_merge([$root], array_map(
            static fn (string $directory): string => $root . '/' . $directory,
            self::DIRECTORIES
        )) as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('Nao foi possivel criar o armazenamento privado do plugin.');
            }
            if (!is_writable($directory)) {
                throw new RuntimeException('O armazenamento privado do plugin nao permite gravacao. Verifique proprietario e permissoes.');
            }
        }

        $freeBytes = @disk_free_space($root);
        $minimumFreeBytes = max(1, $requiredBytes) + 1024 * 1024;
        if ($freeBytes !== false && $freeBytes < $minimumFreeBytes) {
            throw new RuntimeException('Nao ha espaco livre suficiente no armazenamento privado do GLPI.');
        }
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

    public static function updatePath(string $internalFilename): string
    {
        if (preg_match('/^[a-f0-9]{48}\.(?:exe|msi)$/', $internalFilename) !== 1) {
            throw new RuntimeException('Nome interno do pacote de atualizacao invalido.');
        }
        return self::root() . '/updates/' . $internalFilename;
    }

    private static function safePath(string $directory, string $filename): string
    {
        if (preg_match('/^[a-f0-9]{32,64}\.(?:jpg|png|tmp)$/', $filename) !== 1) {
            throw new RuntimeException('Nome interno de arquivo invalido.');
        }
        return $directory . '/' . $filename;
    }
}
