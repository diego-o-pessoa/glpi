<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use CronTask;
use RuntimeException;

/**
 * Ciclo de vida do token OAuth da TW Solutions.
 *
 * - accessToken(): token valido para as futuras chamadas da API; renova
 *   antes de expirar quando ha refresh token.
 * - Tarefa automatica "RefreshToken" (a cada 5 min): renova quando faltam
 *   menos de 10 min para expirar, para o token nunca vencer sem uso.
 */
final class TokenManager
{
    /** Renova quando faltar menos que isto para expirar. */
    public const REFRESH_MARGIN = 600;

    /** Token de acesso valido, renovando se preciso. Vazio se nao conectado. */
    public static function accessToken(): string
    {
        $token = RamalConfig::secret('access_token');
        if ($token === '') {
            return '';
        }
        $expires = RamalConfig::tokenExpiresAt();
        if ($expires > 0 && $expires - time() < 60 && RamalConfig::hasSecret('refresh_token')) {
            self::refresh();
            $token = RamalConfig::secret('access_token');
        }
        return $token;
    }

    public static function needsRefresh(): bool
    {
        $expires = RamalConfig::tokenExpiresAt();
        return RamalConfig::hasSecret('refresh_token') && $expires > 0 && $expires - time() < self::REFRESH_MARGIN;
    }

    /**
     * Renova o token pelo refresh_token.
     *
     * @throws RuntimeException mensagem segura para a tela
     */
    public static function refresh(): void
    {
        $refreshToken = RamalConfig::secret('refresh_token');
        if ($refreshToken === '') {
            throw new RuntimeException('Não há refresh token salvo. Conecte novamente à TW Solutions.');
        }
        try {
            $response = OAuthClient::refresh($refreshToken);
            RamalConfig::storeTokens($response);
        } catch (RuntimeException $exception) {
            $message = $exception instanceof OAuthException && $exception->oauthError === 'invalid_grant'
                ? 'O refresh token foi recusado (expirado ou revogado). Conecte novamente à TW Solutions.'
                : $exception->getMessage();
            RamalConfig::set(['last_refresh_error' => mb_substr($message, 0, 250)]);
            Logger::warning('Renovação do token falhou', ['motivo' => $message]);
            throw new RuntimeException($message);
        }
        RamalConfig::set(['last_refresh_at' => (string) time(), 'last_refresh_error' => '']);
        Logger::info('Token OAuth renovado', ['expira_em' => self::expiresLabel()]);
    }

    private static function expiresLabel(): string
    {
        $expires = RamalConfig::tokenExpiresAt();
        return $expires > 0 ? date('Y-m-d H:i:s', $expires) : 'sem validade informada';
    }

    // ---------------------------------------------------------------- cron

    public static function cronInfo(string $name): array
    {
        return $name === 'RefreshToken'
            ? ['description' => 'Ativa Ramal: renova o token OAuth da TW Solutions antes de expirar']
            : [];
    }

    /** 1 = renovou, 0 = nada a fazer, -1 = falhou (detalhe no ativaramal.log). */
    public static function cronRefreshToken(CronTask $task): int
    {
        if (!self::needsRefresh()) {
            return 0;
        }
        try {
            self::refresh();
        } catch (RuntimeException $exception) {
            $task->log('Falha ao renovar o token: ' . $exception->getMessage());
            return -1;
        }
        $task->addVolume(1);
        return 1;
    }
}
