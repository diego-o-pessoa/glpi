<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use Config;

/**
 * Parametros do Ativa Rede (glpi_configs, contexto plugin:ativarede).
 * Valores pequenos e sem segredo; o token da API e o do Ativa Guardian.
 */
final class Settings
{
    public const CONTEXT = 'plugin:ativarede';

    public const TABLE_SWITCHES = 'glpi_plugin_ativarede_switches';
    public const TABLE_PLANS    = 'glpi_plugin_ativarede_plans';
    public const TABLE_DESKS    = 'glpi_plugin_ativarede_desks';
    public const TABLE_MACHINES = 'glpi_plugin_ativarede_machines';
    public const TABLE_MONITORS = 'glpi_plugin_ativarede_monitors';
    public const TABLE_EVENTS   = 'glpi_plugin_ativarede_events';
    public const TABLE_REJECTIONS = 'glpi_plugin_ativarede_rejections';

    /** Tabela do Ativa Guardian (ultimo heartbeat = maquina ligada agora). */
    public const TABLE_GUARDIAN_MACHINES = 'glpi_plugin_ativaguardian_machines';

    public static function defaults(): array
    {
        return [
            // Relatorios seguidos na porta nova antes de registrar a mudanca
            // (evita alarme quando o cabo e so desconectado por um instante).
            'confirm_reports'      => '2',
            // Monitor que sumiu da maquina (ligada) por mais que isto vira alerta.
            'monitor_missing_days' => '2',
            // Porta com mesa e sem nenhuma maquina por mais que isto vira alerta.
            'desk_empty_days'      => '3',
            // Maquina sem relatorio do Ativa Rede por mais que isto vira alerta.
            'machine_silent_days'  => '7',
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

    public static function int(string $key): int
    {
        $values = Config::getConfigurationValues(self::CONTEXT);
        $value = filter_var($values[$key] ?? self::defaults()[$key] ?? 0, FILTER_VALIDATE_INT);
        return $value === false ? (int) (self::defaults()[$key] ?? 0) : max(1, (int) $value);
    }

    /** Segundos sem heartbeat do Guardian para a maquina contar como desligada. */
    public static function guardianOfflineSeconds(): int
    {
        $values = Config::getConfigurationValues('plugin:ativaguardian');
        $value = filter_var($values['offline_after_seconds'] ?? 7200, FILTER_VALIDATE_INT);
        return $value === false || $value < 60 ? 7200 : (int) $value;
    }
}
