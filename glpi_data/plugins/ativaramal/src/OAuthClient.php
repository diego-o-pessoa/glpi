<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use RuntimeException;
use Throwable;
use Toolbox;

/**
 * OAuth 2.0 (authorization code + refresh token) com a TW Solutions.
 *
 * Credenciais do cliente vao no corpo (client_secret_post), sempre por HTTPS.
 * Mensagens de erro devolvidas para a tela/log trazem so o codigo e a
 * descricao do erro OAuth: nunca o corpo da resposta, tokens ou o segredo.
 */
final class OAuthClient
{
    private const TIMEOUT = 20;

    /** Link para a tela de autorizacao da TW Solutions. */
    public static function authorizationUrl(string $state): string
    {
        $authorize = RamalConfig::authorizeUrl();
        $clientId = RamalConfig::get('client_id');
        if ($clientId === '' || !RamalConfig::isHttpsUrl($authorize)) {
            throw new RuntimeException('Preencha o Client ID e a URL base (HTTPS) antes de conectar.');
        }
        if (!RamalConfig::isHttpsUrl(RamalConfig::redirectUri())) {
            throw new RuntimeException('A URL do GLPI (Configurar > Geral) precisa ser HTTPS para o callback OAuth.');
        }
        $query = [
            'response_type' => 'code',
            'client_id'     => $clientId,
            'redirect_uri'  => RamalConfig::redirectUri(),
            'state'         => $state,
        ];
        if (RamalConfig::get('scope') !== '') {
            $query['scope'] = RamalConfig::get('scope');
        }
        return $authorize . (str_contains($authorize, '?') ? '&' : '?') . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Troca o code do callback pelos tokens.
     *
     * @return array<string, mixed> resposta do endpoint de token
     */
    public static function exchangeCode(string $code): array
    {
        return self::tokenRequest([
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => RamalConfig::redirectUri(),
        ]);
    }

    /**
     * Token "de sistema" (client credentials), so para o diagnostico: nao e
     * salvo e nao substitui o token da conexao OAuth.
     *
     * @return array<string, mixed>
     */
    public static function clientCredentials(): array
    {
        $params = ['grant_type' => 'client_credentials'];
        if (RamalConfig::get('scope') !== '') {
            $params['scope'] = RamalConfig::get('scope');
        }
        return self::tokenRequest($params);
    }

    /** @return array<string, mixed> */
    public static function refresh(string $refreshToken): array
    {
        return self::tokenRequest([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    private static function tokenRequest(array $params): array
    {
        $tokenUrl = RamalConfig::tokenUrl();
        $clientId = RamalConfig::get('client_id');
        $secret = RamalConfig::secret('client_secret');
        if (!RamalConfig::isHttpsUrl($tokenUrl) || $clientId === '' || $secret === '') {
            throw new RuntimeException('Integração incompleta: confira URL (HTTPS), Client ID e Client Secret.');
        }

        try {
            $response = Toolbox::getGuzzleClient([
                'timeout'     => self::TIMEOUT,
                'http_errors' => false,
            ])->post($tokenUrl, [
                'headers'     => ['Accept' => 'application/json'],
                'form_params' => $params + ['client_id' => $clientId, 'client_secret' => $secret],
            ]);
        } catch (Throwable $exception) {
            // A mensagem do Guzzle pode conter a URL/corpo: registra so a classe.
            Logger::error('Falha de conexão com o endpoint de token', ['erro' => get_class($exception)]);
            throw new RuntimeException('Não foi possível conectar à TW Solutions (rede, TLS ou URL do token).');
        }

        $status = $response->getStatusCode();
        $data = json_decode((string) $response->getBody(), true);
        $data = is_array($data) ? $data : [];
        if ($status !== 200 || !isset($data['access_token'])) {
            $error = mb_substr((string) ($data['error'] ?? 'resposta_invalida'), 0, 60);
            $description = mb_substr((string) ($data['error_description'] ?? ''), 0, 160);
            Logger::warning('Endpoint de token recusou o pedido', [
                'grant'  => $params['grant_type'],
                'status' => $status,
                'erro'   => $error,
            ]);
            throw new OAuthException(
                sprintf('A TW Solutions recusou o pedido (HTTP %d, %s)%s', $status, $error, $description !== '' ? ': ' . $description : '.'),
                $error
            );
        }
        return $data;
    }
}
