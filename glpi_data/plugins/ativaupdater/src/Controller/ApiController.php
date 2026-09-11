<?php

namespace GlpiPlugin\Ativaupdater\Controller;

use GlpiPlugin\Ativaupdater\ConfigService;
use PluginAtivaupdaterRelease;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController
{
    private function checkAuth(Request $request): ?JsonResponse
    {
        if (!ConfigService::getBool('api_enabled')) {
            return new JsonResponse(['error' => 'API is disabled'], 403);
        }

        $authHeader = $request->headers->get('Authorization', '');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or invalid Authorization header'], 401);
        }

        $token = substr($authHeader, 7);
        $expectedToken = ConfigService::get('api_token');

        if (!hash_equals($expectedToken, $token)) {
            return new JsonResponse(['error' => 'Invalid token'], 403);
        }

        return null;
    }

    private function getReleaseArray($release, string $baseUrl): array
    {
        return [
            'version'      => $release['version'],
            'file_name'    => $release['original_filename'],
            'size'         => (int)$release['file_size'],
            'sha256'       => $release['sha256'],
            'published_at' => gmdate('Y-m-d\TH:i:sP', strtotime($release['created_at'])),
            'download_url' => $baseUrl . '/api/v1/download/' . urlencode($release['version'])
        ];
    }

    #[Route('/api/v1/latest', name: 'ativaupdater_api_latest', methods: ['GET'])]
    public function latest(Request $request): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        $release = new PluginAtivaupdaterRelease();
        $active = $release->getActiveRelease();

        if (!$active) {
            return new JsonResponse(['error' => 'No active release found'], 404);
        }

        $baseUrl = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/plugins/ativaupdater';
        return new JsonResponse($this->getReleaseArray($active, $baseUrl));
    }

    #[Route('/api/v1/releases/{version}', name: 'ativaupdater_api_release_version', methods: ['GET'])]
    public function getVersion(Request $request, string $version): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        global $DB;
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativaupdater_releases',
            'WHERE' => ['version' => $version],
            'LIMIT' => 1
        ]);

        if (count($iterator) === 0) {
            return new JsonResponse(['error' => 'Release not found'], 404);
        }

        $rel = $iterator->current();
        $baseUrl = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/plugins/ativaupdater';
        return new JsonResponse($this->getReleaseArray($rel, $baseUrl));
    }

    #[Route('/api/v1/download/{version}', name: 'ativaupdater_api_download', methods: ['GET'])]
    public function download(Request $request, string $version): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        global $DB;
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativaupdater_releases',
            'WHERE' => ['version' => $version],
            'LIMIT' => 1
        ]);

        if (count($iterator) === 0) {
            return new JsonResponse(['error' => 'Release not found'], 404);
        }

        $rel = $iterator->current();
        $path = $rel['file_path'];

        $storageDir = GLPI_PLUGIN_DOC_DIR . '/ativaupdater/releases';
        if (!str_starts_with(realpath($path), realpath($storageDir)) || !file_exists($path)) {
            return new JsonResponse(['error' => 'File not found on server'], 404);
        }

        $response = new StreamedResponse(function () use ($path) {
            $stream = fopen($path, 'rb');
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Length', (string)filesize($path));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $rel['original_filename'] . '"');

        return $response;
    }
}
