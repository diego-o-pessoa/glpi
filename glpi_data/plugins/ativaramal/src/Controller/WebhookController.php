<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaramal\Logger;
use GlpiPlugin\Ativaramal\RamalConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Webhook da TW Solutions: https://<glpi>/plugins/ativaramal/api/webhook
 *
 * Servidor -> servidor, sem sessao (stateless, registrado no boot do
 * plugin). Autenticado pelo token do webhook (gerado na configuracao):
 * cabecalho "X-Ativa-Token" ou "Authorization: Bearer <token>"; como
 * alternativa, ?token= na URL (aparece nos logs do servidor web: prefira o
 * cabecalho). Nesta etapa so valida, registra o recebimento e responde.
 */
final class WebhookController extends AbstractController
{
    private const MAX_BYTES = 1024 * 1024;

    #[Route('/api/webhook', name: 'ativaramal_api_webhook', methods: ['GET', 'POST'])]
    public function webhook(Request $request): Response
    {
        $expected = RamalConfig::secret('webhook_token');
        $given = $this->tokenFrom($request);
        if ($expected === '' || $given === '' || !hash_equals($expected, $given)) {
            Logger::warning('Webhook recusado (token ausente ou inválido)', ['ip' => $request->getClientIp()]);
            return new JsonResponse(['ok' => false, 'error' => 'UNAUTHORIZED'], 401);
        }

        // GET: teste de conectividade (ex.: validacao da URL no painel da TW).
        if ($request->isMethod('GET')) {
            return new JsonResponse(['ok' => true, 'service' => 'ativaramal', 'webhook' => 'ready']);
        }

        $body = $request->getContent();
        if (strlen($body) > self::MAX_BYTES) {
            Logger::warning('Webhook recusado (corpo grande demais)', ['bytes' => strlen($body)]);
            return new JsonResponse(['ok' => false, 'error' => 'TOO_LARGE'], 413);
        }
        $payload = json_decode($body, true);
        $event = is_array($payload) ? (string) ($payload['event'] ?? $payload['type'] ?? $payload['evento'] ?? '') : '';

        RamalConfig::touch('webhook_last_at', (string) time());
        // So metadados: o conteudo do evento (numeros, nomes) nao vai para o log.
        Logger::info('Webhook recebido', [
            'evento' => mb_substr($event, 0, 60) ?: '(sem tipo)',
            'bytes'  => strlen($body),
            'json'   => is_array($payload) ? 'sim' : 'nao',
            'ip'     => $request->getClientIp(),
        ]);
        return new JsonResponse(['ok' => true], 202);
    }

    private function tokenFrom(Request $request): string
    {
        $header = trim((string) $request->headers->get('X-Ativa-Token', ''));
        if ($header !== '') {
            return $header;
        }
        if (preg_match('/^Bearer\s+(\S+)$/i', trim((string) $request->headers->get('Authorization', '')), $matches)) {
            return $matches[1];
        }
        return trim((string) $request->query->get('token', ''));
    }
}
