<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaremote\ApiException;
use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaupdater\ConfigService as UpdaterConfig;
use Plugin;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;
use Toolbox;

/**
 * Called by the Ativa Updater service on every computer, with the updater's API token.
 */
final class ApiController extends AbstractController
{
    #[Route('/api/v1/report', name: 'ativaremote_api_report', methods: ['POST'])]
    public function report(Request $request): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        if (strlen($request->getContent()) > 16384) {
            return $this->error('PAYLOAD_TOO_LARGE', 'Relatorio muito grande.', 413);
        }
        try {
            $payload = json_decode($request->getContent(), true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->error('INVALID_JSON', 'JSON invalido.', 400);
        }
        if (!is_array($payload)) {
            return $this->error('INVALID_PAYLOAD', 'Conteudo invalido.', 400);
        }

        try {
            return new JsonResponse(
                (new ClientRepository())->report($payload, (string) ($request->getClientIp() ?? ''))
            );
        } catch (ApiException $e) {
            return $this->error($e->errorCode, $e->getMessage(), $e->status);
        } catch (Throwable $e) {
            Toolbox::logInFile('ativaremote', 'Falha na API: ' . $e->getMessage() . PHP_EOL);
            return $this->error('SERVER_ERROR', 'Erro interno.', 500);
        }
    }

    /** Same credentials as the Ativa Updater API, which ships them to every computer. */
    private function checkAuth(Request $request): ?JsonResponse
    {
        if (!Plugin::isPluginActive('ativaupdater') || !class_exists(UpdaterConfig::class)) {
            return $this->error('UPDATER_UNAVAILABLE', 'O plugin Ativa Updater precisa estar ativo.', 503);
        }
        if (!UpdaterConfig::getBool('api_enabled')) {
            return $this->error('API_DISABLED', 'A API esta desabilitada.', 503);
        }
        $header = trim((string) $request->headers->get('Authorization', ''));
        if (!preg_match('/^Bearer\s+([^\s]+)$/D', $header, $matches)) {
            return $this->error('UNAUTHORIZED', 'Token Bearer ausente ou invalido.', 401);
        }
        $expected = (string) UpdaterConfig::get('api_token', '');
        if ($expected === '' || !hash_equals($expected, $matches[1])) {
            return $this->error('FORBIDDEN', 'Token sem permissao para esta API.', 403);
        }
        return null;
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return new JsonResponse(['error' => ['code' => $code, 'message' => $message]], $status);
    }
}
