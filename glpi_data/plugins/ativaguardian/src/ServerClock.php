<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * Wall-clock timestamps shared by the dashboard and the heartbeat API.
 *
 * With use_timezones enabled, GLPI switches PHP's default timezone to the
 * logged-in user's preference, while the stateless API keeps the server
 * default. Dates written with date() on each side would then not be
 * comparable. Plugin timestamps therefore always use the server timezone
 * configured in php.ini. Same rule the Ativa Updater uses.
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
