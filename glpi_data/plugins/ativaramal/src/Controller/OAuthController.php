<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaramal\Logger;
use GlpiPlugin\Ativaramal\OAuthClient;
use GlpiPlugin\Ativaramal\RamalConfig;
use PluginAtivaramalProfile;
use RuntimeException;
use Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Callback OAuth da TW Solutions: https://<glpi>/plugins/ativaramal/oauth/callback
 *
 * Rota com sessao (fallback "autenticado" do firewall do GLPI): quem chega
 * aqui e o tecnico que clicou em "Conectar" na tela de configuracao. O
 * "state" gerado nessa tela (sessao, uso unico, 10 min) protege contra CSRF.
 */
final class OAuthController extends AbstractController
{
    public const STATE_SESSION_KEY = 'plugin_ativaramal_oauth_state';
    public const STATE_TTL = 600;

    #[Route('/oauth/callback', name: 'ativaramal_oauth_callback', methods: ['GET'])]
    public function callback(Request $request): Response
    {
        if (!Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, UPDATE)) {
            Logger::warning('Callback OAuth sem permissão', ['ip' => $request->getClientIp()]);
            throw new AccessDeniedHttpException();
        }

        // O state vale uma vez so: e removido da sessao antes de qualquer validacao.
        $expected = $_SESSION[self::STATE_SESSION_KEY] ?? null;
        unset($_SESSION[self::STATE_SESSION_KEY]);
        $state = (string) $request->query->get('state', '');
        $valid = is_array($expected)
            && is_string($expected['value'] ?? null)
            && $state !== ''
            && hash_equals($expected['value'], $state)
            && time() - (int) ($expected['created'] ?? 0) <= self::STATE_TTL;

        if (!$valid) {
            Logger::warning('Callback OAuth com state inválido ou expirado', ['ip' => $request->getClientIp()]);
            return $this->back('O retorno da TW Solutions não corresponde a um pedido de conexão válido (ou expirou). Clique em "Conectar" novamente.', false);
        }

        $error = (string) $request->query->get('error', '');
        if ($error !== '') {
            $description = mb_substr((string) $request->query->get('error_description', ''), 0, 160);
            Logger::warning('Autorização negada pela TW Solutions', ['erro' => mb_substr($error, 0, 60)]);
            return $this->back('A TW Solutions não autorizou a conexão: ' . mb_substr($error, 0, 60) . ($description !== '' ? ' — ' . $description : '') . '.', false);
        }

        $code = (string) $request->query->get('code', '');
        if ($code === '' || strlen($code) > 2048) {
            Logger::warning('Callback OAuth sem código de autorização');
            return $this->back('A TW Solutions não devolveu o código de autorização.', false);
        }

        try {
            RamalConfig::storeTokens(OAuthClient::exchangeCode($code));
        } catch (RuntimeException $exception) {
            return $this->back($exception->getMessage(), false);
        }

        $expires = RamalConfig::tokenExpiresAt();
        Logger::info('Conectado à TW Solutions (OAuth)', [
            'refresh' => RamalConfig::hasSecret('refresh_token') ? 'sim' : 'nao',
            'expira'  => $expires > 0 ? date('Y-m-d H:i:s', $expires) : 'sem validade informada',
        ]);
        return $this->back('Conectado à TW Solutions. Token de acesso salvo com segurança.', true);
    }

    private function back(string $message, bool $ok): RedirectResponse
    {
        global $CFG_GLPI;

        Session::addMessageAfterRedirect($message, false, $ok ? INFO : ERROR);
        return new RedirectResponse($CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/config.php');
    }
}
