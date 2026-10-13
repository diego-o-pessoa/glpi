<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Config;

/**
 * Opcoes do Ativa Central (glpi_configs, contexto proprio).
 */
final class Settings
{
    public const CONTEXT = 'plugin:ativacentral';

    /** Periodos do painel, em dias. */
    public const PERIODS = [7, 30, 90];

    public static function defaults(): array
    {
        return [
            // 1 = visual novo ligado para todos da interface de administracao.
            'enabled'              => '1',
            // Periodo inicial do painel (dias).
            'period_days'          => '7',
            // Computador com inventario mais antigo que isto conta como desatualizado.
            'inventory_fresh_days' => '30',
        ];
    }

    public static function installDefaults(): void
    {
        $current = Config::getConfigurationValues(self::CONTEXT);
        $missing = array_diff_key(self::defaults(), $current);
        if ($missing !== []) {
            Config::setConfigurationValues(self::CONTEXT, $missing);
        }
    }

    public static function removeAll(): void
    {
        Config::deleteConfigurationValues(self::CONTEXT, array_keys(self::defaults()));
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        $values = Config::getConfigurationValues(self::CONTEXT);
        $out = [];
        foreach (self::defaults() as $key => $default) {
            $out[$key] = (string) ($values[$key] ?? $default);
        }
        return $out;
    }

    public static function enabled(): bool
    {
        return self::all()['enabled'] === '1';
    }

    public static function int(string $key, int $min = 1, int $max = 3650): int
    {
        $value = filter_var(self::all()[$key] ?? '', FILTER_VALIDATE_INT);
        if ($value === false) {
            $value = (int) (self::defaults()[$key] ?? $min);
        }
        return max($min, min($max, (int) $value));
    }

    public static function periodDays(): int
    {
        $days = self::int('period_days');
        return in_array($days, self::PERIODS, true) ? $days : self::PERIODS[0];
    }

    /** @param array<string, mixed> $input */
    public static function save(array $input): void
    {
        $period = (int) ($input['period_days'] ?? 7);
        $fresh = (int) ($input['inventory_fresh_days'] ?? 30);
        Config::setConfigurationValues(self::CONTEXT, [
            'enabled'              => !empty($input['enabled']) ? '1' : '0',
            'period_days'          => (string) (in_array($period, self::PERIODS, true) ? $period : self::PERIODS[0]),
            'inventory_fresh_days' => (string) max(1, min(365, $fresh)),
        ]);
    }
}
