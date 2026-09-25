<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Config;
use GLPIKey;
use PluginAtivaworkspaceProfile;
use RuntimeException;
use Session;
use Throwable;

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

    // --------------------------------------------- Microsoft Graph (TAP)

    /** App registrado no Entra que gera o TAP (client credentials). */
    public static function graphClientId(): string
    {
        return mb_strtolower(trim((string) self::get('graph_client_id', '')));
    }

    /** Segredo do app: guardado criptografado (GLPIKey), como o GLPI faz com LDAP/API. */
    public static function graphClientSecret(): string
    {
        $stored = (string) self::get('graph_client_secret', '');
        if ($stored === '') {
            return '';
        }

        $key = new GLPIKey();
        if ($key->hasReadErrors()) {
            return '';
        }

        try {
            return (string) $key->decrypt($stored);
        } catch (Throwable) {
            // Configuracao corrompida ou criptografada com outra chave: nunca
            // devolve o texto armazenado como se ele fosse o segredo real.
            return '';
        }
    }

    /**
     * @throws RuntimeException se o GLPI nao puder proteger um segredo novo
     */
    public static function setGraph(string $clientId, string $secret, bool $clearSecret = false): void
    {
        $values = ['graph_client_id' => mb_strtolower(trim($clientId))];

        if ($clearSecret) {
            $values['graph_client_secret'] = '';
        } elseif (trim($secret) !== '') {
            $key = new GLPIKey();
            if ($key->hasReadErrors() || !$key->isConfigSecured(self::CONTEXT, 'graph_client_secret')) {
                throw new RuntimeException(
                    'O GLPI não conseguiu acessar a chave criptográfica. Corrija a glpicrypt.key antes de salvar o Client Secret.'
                );
            }
            // O valor segue em claro somente dentro deste processo. O proprio
            // Config::setConfigurationValues() o criptografa porque o campo foi
            // registrado em Hooks::SECURED_CONFIGS. Nao criptografar duas vezes.
            $values['graph_client_secret'] = trim($secret);
        }
        self::set($values);
    }

    /** Situacao da chave, sem revelar caminho interno nem conteudo. */
    public static function encryptionReady(): bool
    {
        return !(new GLPIKey())->hasReadErrors();
    }

    public static function graphSecretStored(): bool
    {
        return (string) self::get('graph_client_secret', '') !== '';
    }

    public static function graphConfigured(): bool
    {
        return self::isGuid(self::graphClientId())
            && self::isGuid(self::entraTenantId())
            && self::graphClientSecret() !== '';
    }

    /** Validade do TAP em minutos (10 a 480). */
    public static function tapLifetimeMinutes(): int
    {
        $value = (int) self::get('tap_lifetime_minutes', 60);
        return max(60, min(480, $value));
    }

    public static function setTapLifetime(int $minutes): void
    {
        self::set(['tap_lifetime_minutes' => max(60, min(480, $minutes))]);
    }
}
