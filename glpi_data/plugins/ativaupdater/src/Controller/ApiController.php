<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaupdater\ConfigService;
use GlpiPlugin\Ativaupdater\InstallStatus;
use GlpiPlugin\Ativaupdater\ManualCheck;
use GlpiPlugin\Ativaupdater\ReleasePolicy;
use GlpiPlugin\Ativaupdater\ServerClock;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    private const STATUS_VALUES = [
        'checking', 'waiting_release', 'current', 'downloading', 'installing', 'updated', 'error',
        InstallStatus::STATUS_RETRYING, InstallStatus::STATUS_INSTALL_FAILED,
    ];

    private function checkAuth(Request $request): ?JsonResponse
    {
        if (!ConfigService::getBool('api_enabled')) {
            return $this->error('API_DISABLED', 'A API de atualizacao esta desabilitada.', 503);
        }

        $header = trim((string) $request->headers->get('Authorization', ''));
        if (!preg_match('/^Bearer\s+([^\s]+)$/D', $header, $matches)) {
            return $this->error('UNAUTHORIZED', 'Token Bearer ausente ou invalido.', 401);
        }

        $expected = (string) ConfigService::get('api_token', '');
        if ($expected === '' || !hash_equals($expected, $matches[1])) {
            return $this->error('FORBIDDEN', 'Token sem permissao para esta API.', 403);
        }

        return null;
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return new JsonResponse([
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }

    private function configuredBaseUrl(): string
    {
        return rtrim((string) ConfigService::get('api_base_url', ''), '/');
    }

    #[Route('/api/v1/health', name: 'ativaupdater_api_health', methods: ['GET'])]
    public function health(): Response
    {
        return new JsonResponse([
            'status' => 'ok',
            'plugin_version' => PLUGIN_ATIVAUPDATER_VERSION,
            'api_version' => '1',
        ]);
    }

    private function releaseArray(array $release): array
    {
        return [
            'version'                => (string) $release['version'],
            'file_name'              => (string) $release['original_filename'],
            'size'                   => (int) $release['file_size'],
            'sha256'                 => strtolower((string) $release['sha256']),
            'published_at'           => gmdate('Y-m-d\TH:i:s\Z', ServerClock::toTimestamp((string) $release['created_at'])),
            'download_url'           => $this->configuredBaseUrl() . '/download/' . rawurlencode((string) $release['version']),
            'check_interval_seconds' => max(300, min(86400, ConfigService::getInt('check_interval_seconds', 3600))),
            // Only honoured by services >= 1.3.0; older services ignore unknown keys.
            'allow_downgrade'        => (int) $release['active'] === 1 && (int) ($release['allow_downgrade'] ?? 0) === 1,
        ];
    }

    private function findRelease(string $version): ?array
    {
        if (!ReleasePolicy::isValidVersion($version)) {
            return null;
        }

        global $DB;
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativaupdater_releases',
            'WHERE' => ['version' => $version],
            'LIMIT' => 1,
        ]);

        return count($iterator) === 1 ? $iterator->current() : null;
    }

    private function safeReleasePath(array $release): ?string
    {
        $storage = realpath(GLPI_PLUGIN_DOC_DIR . '/ativaupdater/releases');
        $storedFilename = (string) ($release['stored_filename'] ?? '');
        if ($storage === false || !preg_match('/^[a-zA-Z0-9._-]{1,255}\.exe$/D', $storedFilename)) {
            return null;
        }

        $path = realpath($storage . DIRECTORY_SEPARATOR . $storedFilename);
        $prefix = rtrim($storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($path === false || !is_file($path) || !str_starts_with($path, $prefix)) {
            return null;
        }

        if ((int) filesize($path) !== (int) $release['file_size']) {
            return null;
        }

        return $path;
    }

    #[Route('/api/v1/latest', name: 'ativaupdater_api_latest', methods: ['GET'])]
    public function latest(Request $request): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        global $DB;
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativaupdater_releases',
            'WHERE' => ['active' => 1],
            'LIMIT' => 1,
        ]);
        if (count($iterator) !== 1) {
            return $this->error('NO_RELEASE', 'Nenhuma versao foi publicada.', 404);
        }

        return new JsonResponse($this->releaseArray($iterator->current()));
    }

    #[Route('/api/v1/releases/{version}', name: 'ativaupdater_api_release_version', methods: ['GET'])]
    public function getVersion(Request $request, string $version): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        $release = $this->findRelease($version);
        return $release === null
            ? $this->error('NOT_FOUND', 'Versao nao encontrada.', 404)
            : new JsonResponse($this->releaseArray($release));
    }

    #[Route('/api/v1/download/{version}', name: 'ativaupdater_api_download', methods: ['GET'])]
    public function download(Request $request, string $version): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        $release = $this->findRelease($version);
        if ($release === null) {
            return $this->error('NOT_FOUND', 'Versao nao encontrada.', 404);
        }

        $path = $this->safeReleasePath($release);
        if ($path === null) {
            return $this->error('FILE_UNAVAILABLE', 'O instalador publicado nao esta disponivel.', 404);
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename((string) $release['original_filename'])
        );
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('X-Ativa-SHA256', strtolower((string) $release['sha256']));
        return $response;
    }

    #[Route('/api/v1/status', name: 'ativaupdater_api_status', methods: ['POST'])]
    public function status(Request $request): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        // Failure reports carry the installation log (up to ~24 KB of text).
        if (strlen($request->getContent()) > 131072) {
            return $this->error('PAYLOAD_TOO_LARGE', 'Relatorio de status muito grande.', 413);
        }

        try {
            $payload = json_decode($request->getContent(), true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->error('INVALID_JSON', 'JSON invalido.', 400);
        }
        if (!is_array($payload)) {
            return $this->error('INVALID_PAYLOAD', 'Conteudo invalido.', 400);
        }

        $guid = strtolower(trim((string) ($payload['machine_guid'] ?? '')));
        $hostname = trim((string) ($payload['hostname'] ?? ''));
        $status = strtolower(trim((string) ($payload['status'] ?? '')));
        if (!preg_match('/^[a-f0-9-]{32,64}$/D', $guid)
            || !preg_match('/^[a-zA-Z0-9._-]{1,255}$/D', $hostname)
            || !in_array($status, self::STATUS_VALUES, true)
        ) {
            return $this->error('INVALID_PAYLOAD', 'Identificacao ou status invalido.', 422);
        }

        $versions = [];
        foreach (['updater_version', 'installed_version', 'available_version', 'wallpaper_client_version'] as $field) {
            $value = trim((string) ($payload[$field] ?? ''));
            if ($value !== '' && !ReleasePolicy::isValidVersion($value)) {
                return $this->error('INVALID_VERSION', 'Versao informada invalida.', 422);
            }
            $versions[$field] = $value;
        }
        $agentVersion = trim((string) ($payload['glpi_agent_version'] ?? ''));
        if ($agentVersion !== '' && !preg_match('/^\d{1,5}\.\d{1,5}(?:\.\d{1,5})?$/D', $agentVersion)) {
            return $this->error('INVALID_VERSION', 'Versao do GLPI Agent invalida.', 422);
        }
        $installLog = $payload['install_log'] ?? null;
        if ($installLog !== null && !is_string($installLog)) {
            return $this->error('INVALID_PAYLOAD', 'Log de instalacao invalido.', 422);
        }

        $data = [
            'hostname'          => $hostname,
            'updater_version'   => $versions['updater_version'],
            'installed_version' => $versions['installed_version'],
            'available_version' => $versions['available_version'],
            'wallpaper_client_version' => $versions['wallpaper_client_version'],
            'glpi_agent_version' => $agentVersion,
            'status'            => $status,
            'message'           => mb_substr(trim((string) ($payload['message'] ?? '')), 0, 1000),
            'last_ip'           => mb_substr((string) ($request->getClientIp() ?? ''), 0, 64),
            'last_check'        => ServerClock::now(),
        ];
        if (in_array($status, InstallStatus::FINISHED, true)) {
            $data['install_log'] = null;
        } elseif ($installLog !== null) {
            $data['install_log'] = mb_substr($installLog, 0, InstallStatus::LOG_MAX_CHARS);
        }

        global $DB;
        $existing = $DB->request([
            'FROM'  => 'glpi_plugin_ativaupdater_clients',
            'WHERE' => ['machine_guid' => $guid],
            'LIMIT' => 1,
        ]);
        if (count($existing) === 1) {
            $current = $existing->current();
            $data += ManualCheck::acknowledgement($current, $data['last_check']);
            $data['install_started_at'] = InstallStatus::installStartedAt(
                $status,
                $current['install_started_at'] ?? null,
                (string) $current['available_version'],
                $versions['available_version'],
                $data['last_check']
            );
            $ok = $DB->update('glpi_plugin_ativaupdater_clients', $data, ['machine_guid' => $guid]);
        } else {
            $data['install_started_at'] = InstallStatus::installStartedAt(
                $status, null, '', $versions['available_version'], $data['last_check']
            );
            $ok = $DB->insert('glpi_plugin_ativaupdater_clients', ['machine_guid' => $guid] + $data);
        }

        return $ok
            ? new JsonResponse(['ok' => true], 202)
            : $this->error('DATABASE_ERROR', 'Nao foi possivel registrar o status.', 500);
    }

    #[Route(
        '/api/v1/commands/{machineGuid}',
        name: 'ativaupdater_api_commands',
        requirements: ['machineGuid' => '[a-fA-F0-9-]{32,64}'],
        methods: ['GET']
    )]
    public function commands(Request $request, string $machineGuid): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        global $DB;
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_ativaupdater_clients',
            'WHERE' => ['machine_guid' => strtolower($machineGuid)],
            'LIMIT' => 1,
        ]);
        if (count($iterator) !== 1) {
            return $this->error('CLIENT_NOT_FOUND', 'Computador ainda nao registrado.', 404);
        }
        return new JsonResponse([
            'check_now' => ManualCheck::isPending($iterator->current()),
            'poll_after_seconds' => 15,
        ]);
    }
}
