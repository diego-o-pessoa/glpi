<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

use Config;

class ConfigService
{
    public const CONTEXT = 'plugin:ativaguardian';

    public static function defaults(): array
    {
        return [
            'api_enabled'            => '1',
            'api_token'              => self::generateToken(),
            // Machines silent for longer than this are shown as offline.
            'offline_after_seconds'  => '7200',
        ];
    }

    public static function installDefaults(): void
    {
        $current = Config::getConfigurationValues(self::CONTEXT);
        $defaults = self::defaults();
        $missing = array_diff_key($defaults, $current);

        if ($missing !== []) {
            Config::setConfigurationValues(self::CONTEXT, $missing);
        }
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function get(string $key, $default = null)
    {
        $config = Config::getConfigurationValues(self::CONTEXT);
        return $config[$key] ?? $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);
        return in_array($value, [1, '1', true, 'true', 'on', 'yes'], true);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = filter_var(self::get($key, $default), FILTER_VALIDATE_INT);
        return $value === false ? $default : (int) $value;
    }

    public static function set(array $values): void
    {
        Config::setConfigurationValues(self::CONTEXT, $values);
    }
}
