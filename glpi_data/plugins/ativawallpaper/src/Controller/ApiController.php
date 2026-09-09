<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativawallpaper\ApiConfigBuilder;
use GlpiPlugin\Ativawallpaper\ApiException;
use GlpiPlugin\Ativawallpaper\ClientRepository;
use GlpiPlugin\Ativawallpaper\ConfigService;
use GlpiPlugin\Ativawallpaper\RateLimiter;
use GlpiPlugin\Ativawallpaper\Security;
use GlpiPlugin\Ativawallpaper\Storage;
use GlpiPlugin\Ativawallpaper\WallpaperManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;
use Toolbox;

final class ApiController extends AbstractController
{
    #[Route('/api/v1/health', name: 'ativawallpaper_api_health', methods: ['GET'])]
    public function health(Request $request): Response
    {
        return $this->guard(function () use ($request): Response {
            $this->requireHttps($request);
            return $this->json([
                'status'         => 'ok',
                'plugin_version' => PLUGIN_ATIVAWALLPAPER_VERSION,
                'api_version'    => PLUGIN_ATIVAWALLPAPER_API_VERSION,
            ]);
        });
    }

    #[Route('/api/v1/register', name: 'ativawallpaper_api_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        return $this->guard(function () use ($request): Response {
            $this->requireHttps($request);
            (new RateLimiter())->consume('register:' . ($request->getClientIp() ?? 'unknown'), 120, 300);
            $payload = $this->jsonBody($request);
            $secret = is_string($payload['registration_secret'] ?? null)
                ? $payload['registration_secret']
                : '';
            if (!Security::verifyToken($secret, ConfigService::get('registration_secret_hash'))) {
                throw new ApiException('Segredo de registro invalido', 401, 'INVALID_REGISTRATION_SECRET');
            }
            unset($payload['registration_secret']);

            $registered = (new ClientRepository())->register($payload, $request->getClientIp());
            return $this->json([
                'client_id'    => (int) $registered['client']['id'],
                'client_token' => $registered['token'],
                'token_type'   => 'Bearer',
                'api_version'  => PLUGIN_ATIVAWALLPAPER_API_VERSION,
            ], 201);
        });
    }

    #[Route('/api/v1/config', name: 'ativawallpaper_api_config', methods: ['GET'])]
    public function config(Request $request): Response
    {
        return $this->guard(function () use ($request): Response {
            $this->requireHttps($request);
            $client = $this->authenticatedClient($request);
            (new RateLimiter())->consume('client:' . $client['id'], 180, 300);

            $repository = new ClientRepository();
            $repository->touchCheck((int) $client['id'], $request->getClientIp());
            $config = ApiConfigBuilder::build((new WallpaperManager())->current(), $client);
            $etag = ApiConfigBuilder::etag($config);

            if ($this->etagMatches($request, $etag)) {
                $response = new Response(null, 304);
                $response->setEtag($etag);
                $response->headers->set('Cache-Control', 'private, no-cache');
                return $response;
            }

            return $this->json($config, 200, [], $etag);
        });
    }

    #[Route(
        '/api/v1/wallpaper/{identifier}/download',
        name: 'ativawallpaper_api_download',
        requirements: ['identifier' => '[0-9A-Za-z._+-]+'],
        methods: ['GET']
    )]
    public function download(Request $request, string $identifier): Response
    {
        return $this->guard(function () use ($request, $identifier): Response {
            $this->requireHttps($request);
            $client = $this->authenticatedClient($request);
            (new RateLimiter())->consume('download:' . $client['id'], 20, 300);

            $wallpaper = (new WallpaperManager())->findByIdentifier($identifier);
            if ($wallpaper === null) {
                throw new ApiException('Wallpaper nao encontrado', 404, 'WALLPAPER_NOT_FOUND');
            }
            $etag = (string) $wallpaper['sha256'];
            if ($this->etagMatches($request, $etag)) {
                $response = new Response(null, 304);
                $response->setEtag($etag);
                return $response;
            }

            $path = Storage::wallpaperPath((string) $wallpaper['filename']);
            if (!is_file($path) || !is_readable($path)) {
                throw new ApiException('Arquivo de wallpaper indisponivel', 503, 'WALLPAPER_UNAVAILABLE');
            }

            $response = new BinaryFileResponse($path);
            $response->headers->set('Content-Type', (string) $wallpaper['mime_type']);
            $response->headers->set('Content-Disposition', 'inline; filename="wallpaper-' . $wallpaper['version'] . '.' . pathinfo($wallpaper['filename'], PATHINFO_EXTENSION) . '"');
            $response->headers->set('Cache-Control', 'private, max-age=86400, immutable');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->setEtag($etag);
            return $response;
        });
    }

    #[Route('/api/v1/status', name: 'ativawallpaper_api_status', methods: ['POST'])]
    #[Route('/api/v1/error', name: 'ativawallpaper_api_error', methods: ['POST'])]
    public function status(Request $request): Response
    {
        return $this->guard(function () use ($request): Response {
            $this->requireHttps($request);
            $client = $this->authenticatedClient($request);
            (new RateLimiter())->consume('status:' . $client['id'], 120, 300);
            $updated = (new ClientRepository())->reportStatus(
                $client,
                $this->jsonBody($request),
                $request->getClientIp()
            );
            return $this->json([
                'status'          => 'accepted',
                'client_id'       => (int) $updated['id'],
                'server_time_utc' => gmdate('c'),
            ], 202);
        });
    }

    #[Route(
        '/api/v1/{path}',
        name: 'ativawallpaper_api_fallback',
        requirements: ['path' => '.*'],
        methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        priority: -100
    )]
    public function fallback(Request $request, string $path = ''): Response
    {
        return $this->guard(function () use ($request): Response {
            $this->requireHttps($request);
            throw new ApiException('Endpoint ou metodo nao encontrado', 404, 'NOT_FOUND');
        });
    }

    private function authenticatedClient(Request $request): array
    {
        $authorization = $request->headers->get('Authorization', '');
        if (preg_match('/^Bearer\s+([A-Za-z0-9_-]{32,256})$/', $authorization, $match) !== 1) {
            throw new ApiException('Token ausente ou invalido', 401, 'UNAUTHORIZED', [
                'WWW-Authenticate' => 'Bearer realm="Ativa Wallpaper"',
            ]);
        }
        return (new ClientRepository())->authenticate($match[1]);
    }

    private function jsonBody(Request $request): array
    {
        $contentType = strtolower((string) $request->headers->get('Content-Type', ''));
        if (!str_starts_with($contentType, 'application/json')) {
            throw new ApiException('Content-Type deve ser application/json', 415, 'UNSUPPORTED_MEDIA_TYPE');
        }
        $raw = $request->getContent();
        if (strlen($raw) > 65536) {
            throw new ApiException('Corpo da requisicao excede 64 KiB', 413, 'PAYLOAD_TOO_LARGE');
        }
        try {
            $payload = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ApiException('JSON invalido', 400, 'INVALID_JSON');
        }
        if (!is_array($payload) || array_is_list($payload)) {
            throw new ApiException('O corpo JSON deve ser um objeto', 400, 'INVALID_JSON_OBJECT');
        }
        return $payload;
    }

    private function requireHttps(Request $request): void
    {
        if (!$request->isSecure()) {
            throw new ApiException('HTTPS obrigatorio', 400, 'HTTPS_REQUIRED');
        }
    }

    private function etagMatches(Request $request, string $etag): bool
    {
        $header = $request->headers->get('If-None-Match', '');
        foreach (explode(',', $header) as $candidate) {
            $candidate = trim($candidate);
            if (str_starts_with($candidate, 'W/')) {
                $candidate = substr($candidate, 2);
            }
            if (trim($candidate, '"') === $etag) {
                return true;
            }
        }
        return false;
    }

    private function json(array $data, int $status = 200, array $headers = [], ?string $etag = null): JsonResponse
    {
        $response = new JsonResponse($data, $status, $headers);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'private, no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        if ($etag !== null) {
            $response->setEtag($etag);
        }
        return $response;
    }

    private function guard(callable $callback): Response
    {
        try {
            return $callback();
        } catch (ApiException $exception) {
            return $this->json([
                'error' => [
                    'code'    => $exception->errorCode,
                    'message' => $exception->getMessage(),
                ],
            ], $exception->httpStatus, $exception->headers);
        } catch (Throwable $exception) {
            // Intentionally exclude request bodies and headers: they may contain secrets.
            Toolbox::logInFile('ativawallpaper', sprintf(
                "Unexpected API error (%s, code %s)\n",
                $exception::class,
                (string) $exception->getCode()
            ));
            return $this->json([
                'error' => [
                    'code'    => 'INTERNAL_ERROR',
                    'message' => 'Erro interno do servidor',
                ],
            ], 500);
        }
    }
}
