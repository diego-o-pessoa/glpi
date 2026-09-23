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

    private static function get(string $key, mixed $default = null): mixed
    {
        $values = Config::getConfigurationValues(self::CONTEXT, [$key]);
        return $values[$key] ?? $default;
    }

    /** @param array<string, mixed> $values */
    public static function set(array $values): void
    {
        Config::setConfigurationValues(self::CONTEXT, $values);
    }

    public static function simulationEnabled(): bool
    {
        return (int) self::get('simulation_enabled', 0) === 1;
    }

    public static function setSimulation(bool $enabled): void
    {
        self::set(['simulation_enabled' => $enabled ? 1 : 0]);
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

    // ------------------------------------------------------------- Entra

    /** Dominio do tenant Entra esperado (ex.: ativalocacao.com.br). */
    public static function entraDomain(): string
    {
        return mb_strtolower(trim((string) self::get('entra_domain', '')));
    }

    /**
     * Tenant ID (GUID) esperado. Mais confiavel que o dominio: quando
     * preenchido, o ingresso so e aceito se o TenantId do dispositivo bater.
     */
    public static function entraTenantId(): string
    {
        $value = mb_strtolower(trim((string) self::get('entra_tenant_id', '')));
        return self::isGuid($value) ? $value : '';
    }

    public static function isGuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/D', $value);
    }

    // --------------------------------------------------------------- API

    public static function apiEnabled(): bool
    {
        return (int) self::get('api_enabled', 0) === 1;
    }

    /** Token Bearer que o servico nas maquinas usa na API do Workspace. */
    public static function apiToken(): string
    {
        return (string) self::get('api_token', '');
    }

    public static function regenerateApiToken(): string
    {
        $token = bin2hex(random_bytes(32));
        self::set(['api_token' => $token]);
        return $token;
    }
}
