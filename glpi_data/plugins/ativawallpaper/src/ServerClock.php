<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * Wall-clock dates shared by the client API and the dashboard (same rule as the
 * Ativa Updater plugin).
 *
 * date() follows PHP's default timezone, which GLPI changes per context (user
 * preference, stateless API). The same client row was seen written in UTC and,
 * later the same day, in local time, which breaks "seconds until the next check".
 * Plugin dates therefore always use the timezone configured in php.ini.
 */
final class ServerClock
{
    public static function timezone(): DateTimeZone
    {
        $name = (string) ini_get('date.timezone');
        try {
            return new DateTimeZone($name !== '' ? $name : 'UTC');
        } catch (Throwable) {
            return new DateTimeZone('UTC');
        }
    }

    public static function now(): string
    {
        return self::format(time());
    }

    public static function format(int $timestamp): string
    {
        return (new DateTimeImmutable('@' . $timestamp))
            ->setTimezone(self::timezone())
            ->format('Y-m-d H:i:s');
    }

    /** Unix timestamp of a plugin date; 0 when empty or invalid. */
    public static function toTimestamp(?string $value): int
    {
        if ($value === null || trim($value) === '') {
            return 0;
        }
        try {
            return (new DateTimeImmutable($value, self::timezone()))->getTimestamp();
        } catch (Throwable) {
            return 0;
        }
    }
}
