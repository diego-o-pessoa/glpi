<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Config;
use PluginAtivaworkspaceProfile;
use Session;

/**
 * Configuracoes do Workspace (glpi_configs, contexto plugin:ativaworkspace).
 */
final class WorkspaceConfig
{
    public const CONTEXT = 'plugin:ativaworkspace';

    public static function simulationEnabled(): bool
    {
        $values = Config::getConfigurationValues(self::CONTEXT, ['simulation_enabled']);
        return (int) ($values['simulation_enabled'] ?? 0) === 1;
    }

    public static function setSimulation(bool $enabled): void
    {
        Config::setConfigurationValues(self::CONTEXT, ['simulation_enabled' => $enabled ? 1 : 0]);
    }

    /**
     * Pode usar as ferramentas de simulacao: modo ligado + gerenciar
     * provisionamentos + administrar as configuracoes do Workspace.
     */
    public static function canSimulate(): bool
    {
        return self::simulationEnabled()
            && Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, UPDATE)
            && Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, UPDATE);
    }
}
