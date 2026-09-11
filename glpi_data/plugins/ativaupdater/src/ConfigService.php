<?php

namespace GlpiPlugin\Ativaupdater;

use Config;

class ConfigService
{
    public const CONTEXT = 'plugin:ativaupdater';

    public static function defaults(): array
    {
        return [
            'api_enabled' => '1',
            'api_token'   => self::generateToken(),
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

    public static function set(array $values): void
    {
        Config::setConfigurationValues(self::CONTEXT, $values);
    }
}
