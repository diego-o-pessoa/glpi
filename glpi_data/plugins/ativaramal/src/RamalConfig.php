<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use Config;
use GLPIKey;
use RuntimeException;
use Throwable;

/**
 * Configuracao da integracao com a TW Solutions, em glpi_configs
 * (contexto plugin:ativaramal).
 *
 * client_secret, access_token, refresh_token e webhook_token estao em
 * Hooks::SECURED_CONFIGS (setup.php): o GLPI criptografa na gravacao com a
 * glpicrypt.key. Na leitura o valor vem criptografado e e decifrado aqui,
 * so dentro do processo. Nenhum segredo e devolvido para a tela ou para log.
 */
final class RamalConfig
{
    public const CONTEXT = 'plugin:ativaramal';

    /** Campos guardados criptografados (espelha o registerSecureConfigs). */
    public const SECRET_KEYS = ['client_secret', 'access_token', 'refresh_token', 'webhook_token'];

    public const CALLBACK_PATH = '/plugins/ativaramal/oauth/callback';
    public const WEBHOOK_PATH  = '/plugins/ativaramal/api/webhook';

    private const KEYS = [
        'base_url', 'authorize_url', 'token_url', 'scope', 'client_id', 'client_secret',
        'access_token', 'refresh_token', 'token_type', 'token_expires_at', 'token_obtained_at',
        'last_refresh_at', 'last_refresh_error', 'webhook_token', 'webhook_last_at',
    ];

    // ------------------------------------------------------------ leitura/gravacao

    public static function get(string $key, string $default = ''): string
    {
        $values = Config::getConfigurationValues(self::CONTEXT, [$key]);
        return (string) ($values[$key] ?? $default);
    }

    /** @param array<string, scalar|null> $values */
    public static function set(array $values): void
    {
        $values = array_intersect_key($values, array_flip(self::KEYS));
        if ($values === []) {
            return;
        }
        foreach ($values as $key => $value) {
            if (in_array($key, self::SECRET_KEYS, true) && (string) $value !== '') {
                self::assertCanEncrypt($key);
            }
        }
        Config::setConfigurationValues(self::CONTEXT, array_map(static fn ($v): string => (string) $v, $values));
    }

    /**
     * Grava um valor NAO secreto sem passar pelo historico de configuracao
     * (ex.: horario do ultimo webhook, que mudaria a cada evento).
     */
    public static function touch(string $key, string $value): void
    {
        global $DB;

        if (!in_array($key, self::KEYS, true) || in_array($key, self::SECRET_KEYS, true)) {
            return;
        }
        $DB->update('glpi_configs', ['value' => $value], ['context' => self::CONTEXT, 'name' => $key]);
    }

    /** Valor decifrado de um segredo; vazio se nao houver ou nao der para decifrar. */
    public static function secret(string $key): string
    {
        if (!in_array($key, self::SECRET_KEYS, true)) {
            return '';
        }
        $stored = self::get($key);
        if ($stored === '') {
            return '';
        }
        $glpiKey = new GLPIKey();
        if ($glpiKey->hasReadErrors()) {
            return '';
        }
        try {
            return (string) $glpiKey->decrypt($stored);
        } catch (Throwable) {
            // Nunca devolve o texto armazenado como se fosse o segredo real.
            return '';
        }
    }

    public static function hasSecret(string $key): bool
    {
        return self::get($key) !== '';
    }

    private static function assertCanEncrypt(string $key): void
    {
        $glpiKey = new GLPIKey();
        if ($glpiKey->hasReadErrors() || !$glpiKey->isConfigSecured(self::CONTEXT, $key)) {
            throw new RuntimeException(
                'O GLPI não conseguiu acessar a chave criptográfica (glpicrypt.key). Nenhum segredo foi salvo.'
            );
        }
    }

    // ------------------------------------------------------------ instalacao

    /** Valores iniciais. Reinstalar nao apaga credenciais nem tokens. */
    public static function ensureDefaults(): void
    {
        $current = Config::getConfigurationValues(self::CONTEXT);
        $defaults = [];
        foreach (self::KEYS as $key) {
            if (!array_key_exists($key, $current)) {
                $defaults[$key] = '';
            }
        }
        if ($defaults !== []) {
            Config::setConfigurationValues(self::CONTEXT, $defaults);
        }
        if (self::get('webhook_token') === '') {
            self::regenerateWebhookToken();
        }
    }

    public static function removeAll(): void
    {
        Config::deleteConfigurationValues(self::CONTEXT, self::KEYS);
    }

    // ------------------------------------------------------------ URLs

    public static function baseUrl(): string
    {
        return rtrim(self::get('base_url'), '/');
    }

    /** Endpoint de autorizacao; padrao {base}/oauth/authorize se nao informado. */
    public static function authorizeUrl(): string
    {
        $url = self::get('authorize_url');
        return $url !== '' ? $url : (self::baseUrl() !== '' ? self::baseUrl() . '/oauth/authorize' : '');
    }

    /** Endpoint de token; padrao {base}/oauth/token se nao informado. */
    public static function tokenUrl(): string
    {
        $url = self::get('token_url');
        return $url !== '' ? $url : (self::baseUrl() !== '' ? self::baseUrl() . '/oauth/token' : '');
    }

    /** URL publica do GLPI (Configurar > Geral > URL da aplicacao). */
    public static function glpiUrl(): string
    {
        global $CFG_GLPI;
        return rtrim((string) ($CFG_GLPI['url_base'] ?? ''), '/');
    }

    /** Redirect URL a cadastrar na TW Solutions. */
    public static function redirectUri(): string
    {
        return self::glpiUrl() . self::CALLBACK_PATH;
    }

    public static function webhookUrl(): string
    {
        return self::glpiUrl() . self::WEBHOOK_PATH;
    }

    public static function isHttpsUrl(string $url): bool
    {
        return $url !== ''
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    }

    // ------------------------------------------------------------ tokens

    /**
     * Grava a resposta do endpoint de token (authorization_code ou refresh).
     * Se a TW nao devolver refresh_token novo, mantem o atual.
     *
     * @param array<string, mixed> $response
     */
    public static function storeTokens(array $response): void
    {
        $access = (string) ($response['access_token'] ?? '');
        if ($access === '') {
            throw new RuntimeException('A TW Solutions não devolveu access_token.');
        }
        $now = time();
        $expiresIn = (int) ($response['expires_in'] ?? 0);
        $values = [
            'access_token'       => $access,
            'token_type'         => mb_substr((string) ($response['token_type'] ?? 'Bearer'), 0, 32),
            'token_expires_at'   => $expiresIn > 0 ? (string) ($now + $expiresIn) : '',
            'token_obtained_at'  => (string) $now,
            'last_refresh_error' => '',
        ];
        $refresh = (string) ($response['refresh_token'] ?? '');
        if ($refresh !== '') {
            $values['refresh_token'] = $refresh;
        }
        self::set($values);
    }

    public static function clearTokens(): void
    {
        self::set([
            'access_token'      => '',
            'refresh_token'     => '',
            'token_type'        => '',
            'token_expires_at'  => '',
            'token_obtained_at' => '',
        ]);
    }

    public static function tokenExpiresAt(): int
    {
        return (int) self::get('token_expires_at', '0');
    }

    public static function regenerateWebhookToken(): void
    {
        self::set(['webhook_token' => bin2hex(random_bytes(32))]);
    }

    /**
     * Situacao para a tela, sem nenhum segredo (so "configurado" ou nao).
     *
     * @return array<string, mixed>
     */
    public static function status(): array
    {
        $expires = self::tokenExpiresAt();
        $hasAccess = self::hasSecret('access_token');
        return [
            'base_url'          => self::baseUrl(),
            'authorize_url'     => self::get('authorize_url'),
            'token_url'         => self::get('token_url'),
            'effective_authorize_url' => self::authorizeUrl(),
            'effective_token_url'     => self::tokenUrl(),
            'scope'             => self::get('scope'),
            'client_id'         => self::get('client_id'),
            'has_client_secret' => self::hasSecret('client_secret'),
            'has_access_token'  => $hasAccess,
            'has_refresh_token' => self::hasSecret('refresh_token'),
            'token_expires_at'  => $expires,
            'token_expired'     => $hasAccess && $expires > 0 && $expires <= time(),
            'token_obtained_at' => (int) self::get('token_obtained_at', '0'),
            'last_refresh_at'   => (int) self::get('last_refresh_at', '0'),
            'last_refresh_error' => self::get('last_refresh_error'),
            'webhook_last_at'   => (int) self::get('webhook_last_at', '0'),
            'redirect_uri'      => self::redirectUri(),
            'webhook_url'       => self::webhookUrl(),
            'glpi_https'        => self::isHttpsUrl(self::glpiUrl()),
            'encryption_ready'  => !(new GLPIKey())->hasReadErrors(),
        ];
    }
}
