<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

final class Version
{
    public static function next(?string $latest, ?string $date = null): string
    {
        $prefix = $date ?? date('Ymd');
        $sequence = 1;
        if ($latest !== null && preg_match('/^' . preg_quote($prefix, '/') . '-(\d{3,})$/', $latest, $match) === 1) {
            $sequence = ((int) $match[1]) + 1;
        }

        return sprintf('%s-%03d', $prefix, $sequence);
    }

    /** Highest automatic version of the day (YYYYMMDD-NNN) among $versions, if any. */
    public static function latestInSequence(array $versions, string $date): ?string
    {
        $latest = null;
        $highest = 0;
        foreach ($versions as $version) {
            if (preg_match('/^' . preg_quote($date, '/') . '-(\d{3,})$/', (string) $version, $match) === 1
                && (int) $match[1] > $highest) {
                $highest = (int) $match[1];
                $latest = (string) $version;
            }
        }
        return $latest;
    }

    /**
     * Name typed by the administrator when publishing ("Nome / Versao").
     * Only digits would be read as a wallpaper id by the image endpoint.
     */
    public static function isValidCustom(string $version): bool
    {
        return Security::isValidVersion($version) && !ctype_digit($version);
    }
}
