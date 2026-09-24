<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use RuntimeException;

/**
 * Cliente minimo do Microsoft Graph para gerar Temporary Access Pass (TAP).
 *
 * Client credentials (app-only): a credencial e do APP registrado no Entra, nao
 * de nenhuma pessoa. O TAP e um codigo temporario que substitui a senha no
 * primeiro acesso; nenhuma senha de funcionario e lida ou guardada.
 */
final class GraphClient
{
    private const LOGIN = 'https://login.microsoftonline.com';
    private const GRAPH = 'https://graph.microsoft.com/v1.0';
    private const GRAPH_AUDIENCES = [
        'https://graph.microsoft.com',
        '00000003-0000-0000-c000-000000000000',
    ];
    private const TAP_APPLICATION_ROLES = [
        // Permissao especifica e preferida.
        'UserAuthMethod-TAP.ReadWrite.All',
        // Compatibilidade com apps que ja receberam a permissao ampla antiga.
        'UserAuthenticationMethod.ReadWrite.All',
    ];

    /**
     * Gera um TAP de uso unico para a conta (UPN).
     *
     * @return array{code: string, lifetime_minutes: int, start: string, expires_at: string}
     * @throws RuntimeException mensagem pronta para o usuario
     */
    public static function createTap(string $upn): array
    {
        $upn = trim($upn);
        if ($upn === '' || filter_var($upn, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Conta Microsoft (UPN) inválida.');
        }
        if (!WorkspaceConfig::graphConfigured()) {
            throw new RuntimeException('Integração com o Microsoft Graph não configurada (Configurações).');
        }

        $token = self::token();
        $lifetime = WorkspaceConfig::tapLifetimeMinutes();
        $endpoint = self::GRAPH . '/users/' . rawurlencode($upn) . '/authentication/temporaryAccessPassMethods';

        // O Entra so permite UM TAP ativo por usuario: se ja existe (de uma
        // execucao anterior), o POST falha. Apaga o anterior antes de criar.
        self::deleteExistingTaps($endpoint, $token);

        [$status, $body] = self::request(
            'POST',
            $endpoint,
            ['isUsableOnce' => true, 'lifetimeInMinutes' => $lifetime],
            $token
        );

        if ($status === 200 || $status === 201) {
            $code = (string) ($body['temporaryAccessPass'] ?? '');
            if ($code === '') {
                throw new RuntimeException('O Graph não retornou o TAP.');
            }
            return [
                'code'             => $code,
                'lifetime_minutes' => (int) ($body['lifetimeInMinutes'] ?? $lifetime),
                'start'            => (string) ($body['startDateTime'] ?? ''),
                'expires_at'       => self::expiration(
                    (string) ($body['startDateTime'] ?? ''),
                    (int) ($body['lifetimeInMinutes'] ?? $lifetime)
                ),
            ];
        }

        // Mensagens uteis para os erros comuns, sem vazar detalhe sensivel.
        $graphMsg = (string) ($body['error']['message'] ?? '');
        $known = match ($status) {
            403 => 'O app não tem a permissão UserAuthMethod-TAP.ReadWrite.All (ou a permissão ampla compatível) com consentimento administrativo.',
            404 => 'Conta não encontrada no Entra: ' . $upn,
            400 => 'O TAP não está habilitado no Entra ou a conta não permite. ' . $graphMsg,
            default => 'Falha ao gerar o TAP (HTTP ' . $status . '). ' . $graphMsg,
        };
        throw new RuntimeException(trim($known));
    }

    /**
     * Valida credencial, tenant, audiencia e a app role do TAP sem criar um TAP.
     *
     * @return array{tenant: string, client_id: string, permission: string, expires_at: string}
     */
    public static function testConnection(): array
    {
        if (!WorkspaceConfig::graphConfigured()) {
            throw new RuntimeException('Preencha Tenant ID, Client ID e Client Secret antes de testar.');
        }

        $claims = self::tokenClaims(self::token());
        $tenant = mb_strtolower((string) ($claims['tid'] ?? ''));
        if ($tenant !== WorkspaceConfig::entraTenantId()) {
            throw new RuntimeException('O token retornado pertence a outro tenant.');
        }

        $audience = (string) ($claims['aud'] ?? '');
        if (!in_array($audience, self::GRAPH_AUDIENCES, true)) {
            throw new RuntimeException('A credencial não retornou um token destinado ao Microsoft Graph.');
        }

        $roleClaim = $claims['roles'] ?? [];
        // O formato documentado e uma lista, mas alguns emissores/proxies podem
        // serializar uma unica app role como string. Aceita ambos sem aceitar
        // o claim `scp`, que representaria permissao Delegada e nao serve para
        // o fluxo client_credentials.
        $roles = is_array($roleClaim)
            ? array_values(array_filter(array_map('strval', $roleClaim)))
            : (is_string($roleClaim) && $roleClaim !== '' ? [$roleClaim] : []);
        $permission = self::tapPermission($roles);
        if ($permission === '') {
            $detected = $roles === []
                ? 'nenhuma app role'
                : implode(', ', array_slice($roles, 0, 8));
            throw new RuntimeException(
                'A autenticação funcionou, mas o token não contém a app role UserAuthMethod-TAP.ReadWrite.All. '
                . 'Confirme que a permissão foi adicionada em “Application permissions” e recebeu consentimento administrativo. '
                . 'Roles recebidas: ' . mb_substr($detected, 0, 400) . '.'
            );
        }

        $expires = (int) ($claims['exp'] ?? 0);
        if ($expires <= time()) {
            throw new RuntimeException('O Microsoft Entra retornou um token já expirado.');
        }

        return [
            'tenant'     => $tenant,
            'client_id'  => WorkspaceConfig::graphClientId(),
            'permission' => $permission,
            'expires_at' => gmdate(DATE_ATOM, $expires),
        ];
    }

    /** Apaga TAPs existentes do usuario (o Entra so aceita um por vez). */
    private static function deleteExistingTaps(string $endpoint, string $token): void
    {
        [$status, $body] = self::request('GET', $endpoint, null, $token);
        if ($status !== 200 || !is_array($body['value'] ?? null)) {
            return; // sem TAP, ou sem permissao de leitura: segue para o POST
        }
        foreach ($body['value'] as $method) {
            $id = is_array($method) ? (string) ($method['id'] ?? '') : '';
            if ($id !== '') {
                self::request('DELETE', $endpoint . '/' . rawurlencode($id), null, $token);
            }
        }
    }

    /** Token app-only (client credentials). */
    private static function token(): string
    {
        $tenant = WorkspaceConfig::entraTenantId();
        $url = self::LOGIN . '/' . rawurlencode($tenant) . '/oauth2/v2.0/token';
        $form = http_build_query([
            'client_id'     => WorkspaceConfig::graphClientId(),
            'client_secret' => WorkspaceConfig::graphClientSecret(),
            'scope'         => 'https://graph.microsoft.com/.default',
            'grant_type'    => 'client_credentials',
        ]);

        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('A extensão cURL não conseguiu iniciar a conexão com a Microsoft.');
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $form,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Sem conexão com o login da Microsoft: ' . $error);
        }
        $data = json_decode((string) $raw, true);
        if ($status !== 200 || !isset($data['access_token'])) {
            $oauthCode = is_array($data) ? (string) ($data['error'] ?? '') : '';
            $suffix = $oauthCode !== '' ? ' Código Microsoft: ' . mb_substr($oauthCode, 0, 80) . '.' : '';
            throw new RuntimeException(
                'Não foi possível autenticar no Microsoft Graph. Verifique Tenant ID, Client ID, Client Secret e consentimento administrativo.' . $suffix
            );
        }
        return (string) $data['access_token'];
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array{0: int, 1: array<string, mixed>}
     */
    private static function request(string $method, string $url, ?array $payload, string $token): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('A extensão cURL não conseguiu iniciar a chamada ao Microsoft Graph.');
        }
        $options = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ];
        // GET/DELETE nao levam corpo.
        if ($payload !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        }
        curl_setopt_array($ch, $options);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Sem conexão com o Microsoft Graph: ' . $error);
        }

        $data = json_decode((string) $raw, true);
        return [$status, is_array($data) ? $data : []];
    }

    /** @return array<string, mixed> */
    private static function tokenClaims(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('O Microsoft Entra retornou um token em formato inesperado.');
        }
        $encoded = strtr($parts[1], '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $json = base64_decode($encoded, true);
        $claims = $json === false ? null : json_decode($json, true);
        if (!is_array($claims)) {
            throw new RuntimeException('Não foi possível validar as informações do token Microsoft.');
        }
        return $claims;
    }

    private static function expiration(string $start, int $lifetime): string
    {
        try {
            $startAt = $start !== '' ? new \DateTimeImmutable($start) : new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        } catch (\Exception) {
            $startAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }
        return $startAt->modify('+' . max(10, $lifetime) . ' minutes')->format(DATE_ATOM);
    }

    /** @param list<mixed> $roles */
    private static function tapPermission(array $roles): string
    {
        foreach (self::TAP_APPLICATION_ROLES as $allowed) {
            foreach ($roles as $role) {
                if (is_string($role) && strcasecmp($allowed, trim($role)) === 0) {
                    return $allowed;
                }
            }
        }
        return '';
    }
}
