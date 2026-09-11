<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use Glpi\DBAL\QueryExpression;
use RuntimeException;
use Throwable;

final class UpdateManager
{
    private const PACKAGES = 'glpi_plugin_ativawallpaper_update_packages';
    private const INSTALLATIONS = 'glpi_plugin_ativawallpaper_update_installations';
    private const COMPONENTS = ['wallpaper_client', 'glpi_agent'];
    private const MAX_PACKAGE_BYTES = 250 * 1024 * 1024;

    public function publishUploaded(array $upload, string $component, string $version, int $userId): array
    {
        global $DB;

        $component = strtolower(Security::cleanText($component, 32));
        $version = Security::cleanText($version, 32);
        if (!in_array($component, self::COMPONENTS, true) || !Security::isValidVersion($version)) {
            throw new RuntimeException('Componente ou versao invalida.');
        }
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->uploadError((int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE)));
        }
        $temporaryUpload = (string) ($upload['tmp_name'] ?? '');
        if (!is_uploaded_file($temporaryUpload)) {
            throw new RuntimeException('O pacote nao foi recebido por upload HTTP valido.');
        }
        $actualSize = filesize($temporaryUpload);
        $size = $actualSize === false ? 0 : (int) $actualSize;
        if ($size < 1024 || $size > self::MAX_PACKAGE_BYTES) {
            throw new RuntimeException('O pacote deve possuir entre 1 KiB e 250 MB.');
        }

        $extension = $component === 'wallpaper_client' ? 'exe' : 'msi';
        if (strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION)) !== $extension) {
            throw new RuntimeException($component === 'wallpaper_client'
                ? 'O Wallpaper Client deve ser enviado como arquivo .exe.'
                : 'O GLPI Agent deve ser enviado como arquivo .msi.');
        }
        if (countElementsInTable(self::PACKAGES, ['component' => $component, 'version' => $version]) > 0) {
            throw new RuntimeException('Ja existe um pacote deste componente com a mesma versao.');
        }

        Storage::prepare($size);
        $internal = bin2hex(random_bytes(24)) . '.' . $extension;
        $staging = Storage::temporaryPath('.tmp');
        $destination = Storage::updatePath($internal);
        if (!move_uploaded_file($temporaryUpload, $staging)) {
            throw new RuntimeException('Falha ao mover o pacote para o armazenamento privado. Verifique espaco e permissoes.');
        }

        try {
            $this->validateBinary($staging, $component);
            $sha256 = hash_file('sha256', $staging);
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($staging);
            if ($sha256 === false || !is_string($mime)) {
                throw new RuntimeException('Nao foi possivel identificar o pacote de atualizacao.');
            }
            if (!rename($staging, $destination)) {
                throw new RuntimeException('Falha ao publicar o pacote de forma atomica.');
            }
            @chmod($destination, 0640);

            $now = date('Y-m-d H:i:s');
            try {
                $DB->insert(self::PACKAGES, [
                    'component'         => $component,
                    'version'           => $version,
                    'filename'          => $internal,
                    'original_filename' => $this->safeOriginalName((string) ($upload['name'] ?? 'update.' . $extension)),
                    'mime_type'         => $mime,
                    'filesize'          => $size,
                    'sha256'            => $sha256,
                    'release_stage'     => 'draft',
                    'pilot_hostname'    => null,
                    'created_at'        => $now,
                    'created_by'        => $userId,
                    'released_at'       => null,
                    'released_by'       => null,
                ]);
            } catch (Throwable $exception) {
                @unlink($destination);
                throw $exception;
            }
            $id = (int) $DB->insertId();
            Audit::record('update_package_publish', 'update_package', $id, null, [
                'component' => $component,
                'version'   => $version,
                'sha256'    => $sha256,
            ]);
            return $this->findById($id) ?? throw new RuntimeException('Pacote salvo, mas nao foi possivel rele-lo.');
        } finally {
            @unlink($staging);
        }
    }

    public function release(int $id, string $stage, ?string $pilotHostname, int $userId): array
    {
        global $DB;

        $package = $this->findById($id);
        if ($package === null) {
            throw new RuntimeException('Pacote de atualizacao nao encontrado.');
        }
        $stage = strtolower(Security::cleanText($stage, 16));
        if (!in_array($stage, ['pilot', 'all', 'paused'], true)) {
            throw new RuntimeException('Etapa de liberacao invalida.');
        }
        $pilot = strtoupper(Security::cleanText($pilotHostname ?? '', 255));
        if ($stage === 'pilot' && !Security::isValidHostname($pilot)) {
            throw new RuntimeException('Informe um hostname piloto valido.');
        }

        $DB->beginTransaction();
        try {
            if ($stage !== 'paused') {
                $DB->update(self::PACKAGES, ['release_stage' => 'paused'], [
                    'component'     => (string) $package['component'],
                    'release_stage' => ['pilot', 'all'],
                ]);
            }
            $values = [
                'release_stage'  => $stage,
                'pilot_hostname' => $stage === 'pilot' ? $pilot : null,
                'released_at'    => $stage === 'paused' ? $package['released_at'] : date('Y-m-d H:i:s'),
                'released_by'    => $stage === 'paused' ? $package['released_by'] : $userId,
            ];
            $DB->update(self::PACKAGES, $values, ['id' => $id]);
            if ($stage !== 'paused') {
                $this->seedEligibleInstallations(array_merge($package, $values));
            }
            if ($stage !== 'paused' && $package['component'] === 'wallpaper_client'
                && version_compare((string) $package['version'], ConfigService::get('latest_client_version'), '>')) {
                ConfigService::set(['latest_client_version' => (string) $package['version']]);
            }
            Audit::record('update_package_release', 'update_package', $id, $package['release_stage'], [
                'stage' => $stage,
                'pilot' => $values['pilot_hostname'],
            ]);
            $DB->commit();
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw $exception;
        }
        return $this->findById($id) ?? $package;
    }

    public function deletePackage(int $id, int $userId): void
    {
        global $DB;

        $package = $this->findById($id);
        if ($package === null) {
            throw new RuntimeException('Pacote de atualizacao nao encontrado.');
        }

        $DB->beginTransaction();
        try {
            $DB->delete(self::INSTALLATIONS, ['update_packages_id' => $id]);
            $DB->delete(self::PACKAGES, ['id' => $id]);
            
            $path = Storage::updatePath((string)$package['filename']);
            @unlink($path);

            Audit::record('update_package_delete', 'update_package', $id, null, [
                'component' => $package['component'],
                'version'   => $package['version'],
            ]);
            $DB->commit();
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw $exception;
        }
    }

    public function checkForUpdates(array $client, array $payload): array
    {
        global $DB;

        $payloadGuid = Security::cleanText($payload['machine_guid'] ?? '', 128);
        if ($payloadGuid === '' || !hash_equals((string) $client['machine_guid'], $payloadGuid)) {
            throw new ApiException('machine_guid nao corresponde ao token', 403, 'IDENTITY_MISMATCH');
        }

        $versions = [
            'wallpaper_client' => Security::cleanText($payload['wallpaper_client_version'] ?? '', 32),
            'glpi_agent'       => Security::cleanText($payload['glpi_agent_version'] ?? '', 32),
        ];
        $updaterVersion = Security::cleanText($payload['updater_version'] ?? '', 32);
        foreach ($versions as $version) {
            if ($version !== '' && !Security::isValidVersion($version)) {
                throw new ApiException('Versao instalada invalida', 422, 'INVALID_INSTALLED_VERSION');
            }
        }
        if ($updaterVersion !== '' && !Security::isValidVersion($updaterVersion)) {
            throw new ApiException('Versao do atualizador invalida', 422, 'INVALID_UPDATER_VERSION');
        }

        $DB->update('glpi_plugin_ativawallpaper_clients', [
            'glpi_agent_version' => $versions['glpi_agent'] !== '' ? $versions['glpi_agent'] : null,
            'updater_version'    => $updaterVersion !== '' ? $updaterVersion : null,
            'last_update_check'  => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ], ['id' => (int) $client['id']]);

        $updates = [];
        foreach (self::COMPONENTS as $component) {
            $currentVersion = $versions[$component] !== '' ? $versions[$component] : '0.0.0';
            $package = $this->bestEligiblePackage($component, (string) $client['hostname']);
            if ($package === null) {
                continue;
            }
            if (version_compare((string) $package['version'], $currentVersion, '<=')) {
                $this->markAlreadyCurrent($package, $client, $currentVersion);
                continue;
            }
            $this->recordOffer($package, $client, $currentVersion);
            $updates[] = [
                'id'           => (int) $package['id'],
                'component'    => (string) $package['component'],
                'version'      => (string) $package['version'],
                'filesize'     => (int) $package['filesize'],
                'sha256'       => (string) $package['sha256'],
                'download_url' => rtrim(ConfigService::get('server_url'), '/') . '/updates/' . $package['id'] . '/download',
            ];
        }
        return $updates;
    }

    public function reportStatus(array $client, array $payload): void
    {
        global $DB;

        $package = $this->findById((int) ($payload['update_id'] ?? 0));
        if ($package === null) {
            throw new ApiException('Pacote de atualizacao nao encontrado', 404, 'UPDATE_NOT_FOUND');
        }
        $status = strtolower(Security::cleanText($payload['status'] ?? '', 24));
        if (!in_array($status, ['offered', 'downloading', 'installing', 'success', 'error', 'restart_required'], true)) {
            throw new ApiException('Status de atualizacao invalido', 422, 'INVALID_UPDATE_STATUS');
        }
        $fromVersion = Security::cleanText($payload['from_version'] ?? '', 32);
        if ($fromVersion !== '' && !Security::isValidVersion($fromVersion)) {
            throw new ApiException('Versao de origem invalida', 422, 'INVALID_INSTALLED_VERSION');
        }
        $message = Security::cleanText($payload['message'] ?? '', 1000);
        $trackingWhere = [
            'update_packages_id' => (int) $package['id'],
            'clients_id'         => (int) $client['id'],
        ];
        if (countElementsInTable(self::INSTALLATIONS, $trackingWhere) === 0
            && !$this->isEligible($package, (string) $client['hostname'])) {
            throw new ApiException('Atualizacao nao liberada para este computador', 403, 'UPDATE_NOT_ALLOWED');
        }
        $this->recordOffer($package, $client, $fromVersion !== '' ? $fromVersion : '0.0.0');
        $now = date('Y-m-d H:i:s');
        $values = [
            'status'     => $status,
            'message'    => $message !== '' ? $message : null,
            'updated_at' => $now,
        ];
        if (in_array($status, ['downloading', 'installing'], true)) {
            $values['started_at'] = $now;
        }
        if (in_array($status, ['success', 'error', 'restart_required'], true)) {
            $values['finished_at'] = $now;
        }
        $DB->update(self::INSTALLATIONS, $values, $trackingWhere);

        if (in_array($status, ['success', 'restart_required'], true)) {
            $field = $package['component'] === 'wallpaper_client' ? 'client_version' : 'glpi_agent_version';
            $DB->update('glpi_plugin_ativawallpaper_clients', [
                $field       => (string) $package['version'],
                'updated_at' => $now,
            ], ['id' => (int) $client['id']]);
        }
    }

    public function findDownloadForClient(int $id, array $client): ?array
    {
        $package = $this->findById($id);
        return $package !== null && $this->isEligible($package, (string) $client['hostname']) ? $package : null;
    }

    public function findById(int $id): ?array
    {
        global $DB;
        $row = $DB->request(['FROM' => self::PACKAGES, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current();
        return is_array($row) ? $row : null;
    }

    public function packages(): array
    {
        global $DB;
        $rows = [];
        foreach ($DB->request(['FROM' => self::PACKAGES, 'ORDER' => ['created_at DESC', 'id DESC']]) as $row) {
            $summary = ['offered' => 0, 'downloading' => 0, 'installing' => 0, 'success' => 0, 'error' => 0, 'restart_required' => 0];
            $counts = $DB->request([
                'SELECT' => ['status', new QueryExpression('COUNT(*) AS total')],
                'FROM' => self::INSTALLATIONS,
                'WHERE' => ['update_packages_id' => (int) $row['id']],
                'GROUPBY' => ['status'],
            ]);
            foreach ($counts as $count) {
                if (array_key_exists((string) $count['status'], $summary)) {
                    $summary[(string) $count['status']] = (int) $count['total'];
                }
            }
            $summary['total'] = array_sum($summary);
            $summary['processed'] = $summary['success'] + $summary['error'] + $summary['restart_required'];
            $summary['percentage'] = $summary['total'] > 0
                ? round(($summary['processed'] / $summary['total']) * 100, 1)
                : 0.0;
            $row['summary'] = $summary;
            $rows[] = $row;
        }
        return $rows;
    }

    public function recentInstallations(int $limit = 100): array
    {
        global $DB;
        $rows = [];
        foreach ($DB->request([
            'FROM' => self::INSTALLATIONS,
            'ORDER' => ['updated_at DESC', 'id DESC'],
            'LIMIT' => max(1, min(500, $limit)),
        ]) as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    private function bestEligiblePackage(string $component, string $hostname): ?array
    {
        global $DB;
        $best = null;
        foreach ($DB->request([
            'FROM' => self::PACKAGES,
            'WHERE' => ['component' => $component, 'release_stage' => ['pilot', 'all']],
        ]) as $row) {
            if (!$this->isEligible($row, $hostname)) {
                continue;
            }
            if ($best === null || version_compare((string) $row['version'], (string) $best['version'], '>')) {
                $best = $row;
            }
        }
        return $best;
    }

    private function isEligible(array $package, string $hostname): bool
    {
        return $package['release_stage'] === 'all'
            || ($package['release_stage'] === 'pilot'
                && strcasecmp((string) ($package['pilot_hostname'] ?? ''), $hostname) === 0);
    }

    private function recordOffer(array $package, array $client, string $fromVersion): void
    {
        global $DB;
        $where = ['update_packages_id' => (int) $package['id'], 'clients_id' => (int) $client['id']];
        if (countElementsInTable(self::INSTALLATIONS, $where) > 0) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $DB->insert(self::INSTALLATIONS, [
            'update_packages_id' => (int) $package['id'],
            'clients_id'         => (int) $client['id'],
            'hostname'           => (string) $client['hostname'],
            'component'          => (string) $package['component'],
            'from_version'       => $fromVersion,
            'to_version'         => (string) $package['version'],
            'status'             => 'offered',
            'message'            => null,
            'offered_at'         => $now,
            'started_at'         => null,
            'finished_at'        => null,
            'updated_at'         => $now,
        ]);
    }

    private function seedEligibleInstallations(array $package): void
    {
        global $DB;

        $versionField = $package['component'] === 'wallpaper_client' ? 'client_version' : 'glpi_agent_version';
        foreach ($DB->request([
            'FROM'  => 'glpi_plugin_ativawallpaper_clients',
            'WHERE' => ['revoked_at' => null],
        ]) as $client) {
            if (!$this->isEligible($package, (string) $client['hostname'])) {
                continue;
            }
            $installedVersion = Security::cleanText($client[$versionField] ?? '', 32);
            if ($installedVersion !== '' && Security::isValidVersion($installedVersion)
                && version_compare((string) $package['version'], $installedVersion, '<=')) {
                $this->markAlreadyCurrent($package, $client, $installedVersion);
                continue;
            }
            $fromVersion = $installedVersion !== '' && Security::isValidVersion($installedVersion)
                ? $installedVersion
                : '0.0.0';
            $this->recordOffer($package, $client, $fromVersion);
            if (empty($client['updater_version'])) {
                $now = date('Y-m-d H:i:s');
                $DB->update(self::INSTALLATIONS, [
                    'status'      => 'error',
                    'message'     => 'Atualizador automatico ausente. Execute o instalador unificado 1.4.0 uma vez neste computador.',
                    'finished_at' => $now,
                    'updated_at'  => $now,
                ], [
                    'update_packages_id' => (int) $package['id'],
                    'clients_id'         => (int) $client['id'],
                ]);
            }
        }
    }

    private function markAlreadyCurrent(array $package, array $client, string $installedVersion): void
    {
        global $DB;

        $this->recordOffer($package, $client, $installedVersion);
        $where = [
            'update_packages_id' => (int) $package['id'],
            'clients_id'         => (int) $client['id'],
        ];
        $tracking = $DB->request(['FROM' => self::INSTALLATIONS, 'WHERE' => $where, 'LIMIT' => 1])->current();
        if (is_array($tracking) && in_array((string) $tracking['status'], ['success', 'restart_required'], true)) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $DB->update(self::INSTALLATIONS, [
            'from_version' => $installedVersion,
            'status'       => 'success',
            'message'      => 'A versao solicitada ja estava instalada; nenhuma alteracao foi necessaria.',
            'finished_at'  => $now,
            'updated_at'   => $now,
        ], $where);
    }

    private function validateBinary(string $path, string $component): void
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Nao foi possivel ler o pacote recebido.');
        }
        try {
            $magic = fread($handle, 8);
        } finally {
            fclose($handle);
        }
        $expected = $component === 'wallpaper_client' ? "MZ" : "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        if (!is_string($magic) || !str_starts_with($magic, $expected)) {
            throw new RuntimeException('O conteudo do arquivo nao corresponde ao componente selecionado.');
        }
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        return Security::cleanText($name, 255) ?: 'update.bin';
    }

    private function uploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O pacote excede o limite configurado no PHP.',
            UPLOAD_ERR_PARTIAL => 'O upload foi interrompido antes de terminar.',
            UPLOAD_ERR_NO_FILE => 'Nenhum pacote foi selecionado.',
            default => 'Falha ao receber o pacote de atualizacao.',
        };
    }
}
