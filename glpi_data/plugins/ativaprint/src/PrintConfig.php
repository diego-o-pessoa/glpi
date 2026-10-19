<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaprint;

use Config;

/**
 * Configuracao do Ativa Print em glpi_configs (contexto plugin:ativaprint).
 *
 * Nada aqui e segredo de verdade: a community SNMP de leitura e conhecida de
 * qualquer equipamento da rede e so permite ler.
 */
final class PrintConfig
{
    public const CONTEXT = 'plugin:ativaprint';

    /** Valores padrao (gravados na instalacao, sem sobrescrever o que ja existe). */
    public const DEFAULTS = [
        'snmp_community' => 'public',
        'snmp_version'   => '2c',
        'snmp_timeout'   => '1000',   // ms por tentativa
        'toner_warning'  => '15',     // % - faixa de atencao
        'toner_critical' => '5',      // % - estado critico
        'offline_after'  => '15',     // min sem resposta para contar como sem comunicacao
    ];

    public static function get(string $key): string
    {
        $values = Config::getConfigurationValues(self::CONTEXT, [$key]);
        return (string) ($values[$key] ?? self::DEFAULTS[$key] ?? '');
    }

    public static function int(string $key): int
    {
        return (int) self::get($key);
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        $values = Config::getConfigurationValues(self::CONTEXT, array_keys(self::DEFAULTS));
        $out = [];
        foreach (self::DEFAULTS as $key => $default) {
            $out[$key] = (string) ($values[$key] ?? $default);
        }
        return $out;
    }

    /**
     * Valida e grava. Devolve a lista de erros (vazia = gravou).
     *
     * @param array<string, mixed> $input
     * @return list<string>
     */
    public static function save(array $input): array
    {
        $errors = [];
        $values = [];

        $community = trim((string) ($input['snmp_community'] ?? ''));
        if ($community === '' || strlen($community) > 64) {
            $errors[] = 'Informe a community SNMP (até 64 caracteres).';
        }
        $values['snmp_community'] = $community;

        $version = (string) ($input['snmp_version'] ?? '2c');
        $values['snmp_version'] = in_array($version, ['1', '2c'], true) ? $version : '2c';

        $timeout = (int) ($input['snmp_timeout'] ?? 1000);
        if ($timeout < 200 || $timeout > 5000) {
            $errors[] = 'O tempo de espera SNMP deve ficar entre 200 e 5000 ms.';
        }
        $values['snmp_timeout'] = (string) $timeout;

        $warning = (int) ($input['toner_warning'] ?? 15);
        $critical = (int) ($input['toner_critical'] ?? 5);
        if ($critical < 1 || $critical >= $warning || $warning > 50) {
            $errors[] = 'Os limites de toner devem respeitar: 1 ≤ crítico < atenção ≤ 50.';
        }
        $values['toner_warning'] = (string) $warning;
        $values['toner_critical'] = (string) $critical;

        $offline = (int) ($input['offline_after'] ?? 15);
        if ($offline < 5 || $offline > 1440) {
            $errors[] = 'O tempo para considerar sem comunicação deve ficar entre 5 e 1440 minutos.';
        }
        $values['offline_after'] = (string) $offline;

        if ($errors === []) {
            Config::setConfigurationValues(self::CONTEXT, $values);
        }
        return $errors;
    }

    public static function ensureDefaults(): void
    {
        $current = Config::getConfigurationValues(self::CONTEXT);
        $missing = array_diff_key(self::DEFAULTS, $current);
        if ($missing !== []) {
            Config::setConfigurationValues(self::CONTEXT, $missing);
        }
    }

    public static function removeAll(): void
    {
        Config::deleteConfigurationValues(self::CONTEXT, array_keys(self::DEFAULTS));
    }
}

