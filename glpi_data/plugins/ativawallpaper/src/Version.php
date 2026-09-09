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
}
