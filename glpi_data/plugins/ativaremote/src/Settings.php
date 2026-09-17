<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use Config;

/**
 * Plugin settings stored in glpi_configs (context plugin:ativaremote).
 *
 * The T.I. password is kept only as a password_hash(); it protects the
 * "Conectar" button of computers in protected groups.
 */
final class Settings
{
    public const CONTEXT = 'plugin:ativaremote';
    public const DEFAULT_TI_PASSWORD = 'mudar123';
    public const MIN_PASSWORD_LENGTH = 8;

    public static function ensureDefaults(): void
    {
        $current = Config::getConfigurationValues(self::CONTEXT);
        $missing = [];
        if (!isset($current['protected_groups'])) {
            $missing['protected_groups'] = '[]';
        }
        if (empty($current['ti_password_hash'])) {
            $missing['ti_password_hash'] = password_hash(self::DEFAULT_TI_PASSWORD, PASSWORD_DEFAULT);
            $missing['ti_password_is_default'] = '1';
        }
        if ($missing !== []) {
            Config::setConfigurationValues(self::CONTEXT, $missing);
        }
    }

    /** @return int[] */
    public static function protectedGroupIds(): array
    {
        $raw = (string) (Config::getConfigurationValues(self::CONTEXT, ['protected_groups'])['protected_groups'] ?? '[]');
        $ids = json_decode($raw, true);
        return is_array($ids) ? array_values(array_unique(array_filter(array_map('intval', $ids)))) : [];
    }

    /** @param int[] $ids */
    public static function setProtectedGroupIds(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id) => $id > 0)));
        Config::setConfigurationValues(self::CONTEXT, ['protected_groups' => json_encode($ids)]);
    }

    public static function verifyTiPassword(string $password): bool
    {
        $hash = (string) (Config::getConfigurationValues(self::CONTEXT, ['ti_password_hash'])['ti_password_hash'] ?? '');
        return $hash !== '' && $password !== '' && password_verify($password, $hash);
    }

    public static function setTiPassword(string $password): void
    {
        Config::setConfigurationValues(self::CONTEXT, [
            'ti_password_hash'       => password_hash($password, PASSWORD_DEFAULT),
            'ti_password_is_default' => $password === self::DEFAULT_TI_PASSWORD ? '1' : '0',
        ]);
    }

    public static function isDefaultTiPassword(): bool
    {
        return (Config::getConfigurationValues(self::CONTEXT, ['ti_password_is_default'])['ti_password_is_default'] ?? '0') === '1';
    }

    public static function removeAll(): void
    {
        Config::deleteConfigurationValues(self::CONTEXT, ['protected_groups', 'ti_password_hash', 'ti_password_is_default']);
    }
}
