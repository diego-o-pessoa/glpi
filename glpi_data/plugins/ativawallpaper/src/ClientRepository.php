<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use RuntimeException;

final class ClientRepository
{
    private const TABLE = 'glpi_plugin_ativawallpaper_clients';

    /** @return array{client:array,token:string} */
    public function register(array $payload, ?string $ipAddress): array
    {
        global $DB;

        $hostname = strtoupper(Security::cleanText($payload['hostname'] ?? '', 255));
        $machineGuid = Security::cleanText($payload['machine_guid'] ?? '', 128);
        $clientVersion = Security::cleanText($payload['client_version'] ?? '', 32);
        $agentDeviceId = Security::cleanText($payload['glpi_agent_device_id'] ?? '', 255);
        $osVersion = Security::cleanText($payload['os_version'] ?? '', 255);

        if (!Security::isValidHostname($hostname)) {
            throw new ApiException('hostname invalido', 422, 'INVALID_HOSTNAME');
        }
        if (!Security::isValidMachineGuid($machineGuid)) {
            throw new ApiException('machine_guid invalido', 422, 'INVALID_MACHINE_GUID');
        }
        if (!Security::isValidVersion($clientVersion)) {
            throw new ApiException('client_version invalida', 422, 'INVALID_CLIENT_VERSION');
        }

        $token = Security::randomToken(32);
        $now = date('Y-m-d H:i:s');
        $existing = $this->findByMachineGuid($machineGuid);
        $computerId = $this->reconcileComputer($hostname);

        $values = [
            'computers_id'         => $computerId ?? ($existing['computers_id'] ?? null),
            'hostname'             => $hostname,
            'glpi_agent_device_id' => $agentDeviceId !== '' ? $agentDeviceId : null,
            'client_version'       => $clientVersion,
            'os_version'           => $osVersion !== '' ? $osVersion : null,
            'token_hash'           => Security::hashToken($token),
            'revoked_at'           => null,
            'status'               => 'registered',
            'last_error_code'      => null,
            'last_error'           => null,
            'last_check'           => $now,
            'last_ip'              => Security::cleanText($ipAddress ?? '', 45),
            'updated_at'           => $now,
        ];

        if ($existing === null) {
            $values['machine_guid'] = $machineGuid;
            $values['registered_at'] = $now;
            $DB->insert(self::TABLE, $values);
            $id = (int) $DB->insertId();
        } else {
            $id = (int) $existing['id'];
            $DB->update(self::TABLE, $values, ['id' => $id]);
        }

        $client = $this->findById($id);
        if ($client === null) {
            throw new RuntimeException('Falha ao persistir registro do cliente.');
        }
        return ['client' => $client, 'token' => $token];
    }

    public function authenticate(string $token): array
    {
        global $DB;

        if ($token === '' || strlen($token) > 256) {
            throw new ApiException('Token ausente ou invalido', 401, 'UNAUTHORIZED');
        }
        $hash = Security::hashToken($token);
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['token_hash' => $hash],
            'LIMIT' => 1,
        ]);
        $client = $iterator->current();
        if (!is_array($client) || !Security::verifyToken($token, (string) ($client['token_hash'] ?? ''))) {
            throw new ApiException('Token ausente ou invalido', 401, 'UNAUTHORIZED');
        }
        if (!empty($client['revoked_at'])) {
            throw new ApiException('Cliente revogado', 401, 'CLIENT_REVOKED');
        }
        return $client;
    }

    public function findById(int $id): ?array
    {
        global $DB;
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['id' => $id],
            'LIMIT' => 1,
        ]);
        $row = $iterator->current();
        return is_array($row) ? $row : null;
    }

    public function findByMachineGuid(string $machineGuid): ?array
    {
        global $DB;
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['machine_guid' => $machineGuid],
            'LIMIT' => 1,
        ]);
        $row = $iterator->current();
        return is_array($row) ? $row : null;
    }

    public function findByComputer(int $computerId): ?array
    {
        global $DB;
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computerId],
            'ORDER' => ['updated_at DESC'],
            'LIMIT' => 1,
        ]);
        $row = $iterator->current();
        return is_array($row) ? $row : null;
    }

    public function touchCheck(int $clientId, ?string $ipAddress): void
    {
        global $DB;
        $DB->update(self::TABLE, [
            'last_check' => date('Y-m-d H:i:s'),
            'last_ip'    => Security::cleanText($ipAddress ?? '', 45),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $clientId]);
    }

    public function reportStatus(array $client, array $payload, ?string $ipAddress): array
    {
        global $DB;

        $status = strtolower(Security::cleanText($payload['status'] ?? '', 24));
        if (!in_array($status, ['success', 'error'], true)) {
            throw new ApiException('status deve ser success ou error', 422, 'INVALID_STATUS');
        }

        $payloadGuid = Security::cleanText($payload['machine_guid'] ?? '', 128);
        if ($payloadGuid !== '' && !hash_equals((string) $client['machine_guid'], $payloadGuid)) {
            throw new ApiException('machine_guid nao corresponde ao token', 403, 'IDENTITY_MISMATCH');
        }

        $wallpaperVersion = Security::cleanText($payload['wallpaper_version'] ?? '', 32);
        $clientVersion = Security::cleanText($payload['client_version'] ?? $client['client_version'], 32);
        $hostname = strtoupper(Security::cleanText($payload['hostname'] ?? $client['hostname'], 255));
        $username = Security::cleanText($payload['username'] ?? '', 255);
        $sha256 = strtolower(Security::cleanText($payload['wallpaper_sha256'] ?? '', 64));
        $errorCode = Security::cleanText($payload['error_code'] ?? '', 64);
        $message = Security::cleanText($payload['message'] ?? '', 1000);

        if (!Security::isValidHostname($hostname) || !Security::isValidVersion($clientVersion)) {
            throw new ApiException('Identidade ou versao invalida', 422, 'INVALID_PAYLOAD');
        }
        if ($wallpaperVersion !== '' && !Security::isValidVersion($wallpaperVersion)) {
            throw new ApiException('wallpaper_version invalida', 422, 'INVALID_WALLPAPER_VERSION');
        }
        if ($sha256 !== '' && preg_match('/^[a-f0-9]{64}$/', $sha256) !== 1) {
            throw new ApiException('wallpaper_sha256 invalido', 422, 'INVALID_SHA256');
        }

        if ($status === 'success') {
            $current = (new WallpaperManager())->current();
            if ($current !== null) {
                if ($wallpaperVersion !== (string) $current['version']) {
                    $status = 'error';
                    $errorCode = 'REPORTED_VERSION_MISMATCH';
                    $message = 'A versao reportada nao corresponde ao wallpaper atual.';
                } elseif ($sha256 === '' || !hash_equals((string) $current['sha256'], $sha256)) {
                    $status = 'error';
                    $errorCode = 'REPORTED_HASH_MISMATCH';
                    $message = 'O SHA-256 reportado nao corresponde ao wallpaper atual.';
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        $reportedRolloutId = Security::cleanText($payload['rollout_id'] ?? '', 64);
        $activeRolloutId = (string) ($client['rollout_id'] ?? '');
        $matchesActiveRollout = $reportedRolloutId === ''
            || $activeRolloutId === ''
            || hash_equals($activeRolloutId, $reportedRolloutId);
        $values = [
            'computers_id'     => $this->reconcileComputer($hostname) ?? $client['computers_id'],
            'hostname'         => $hostname,
            'username'         => $username !== '' ? $username : null,
            'client_version'   => $clientVersion,
            'wallpaper_version'=> $wallpaperVersion !== '' ? $wallpaperVersion : null,
            'wallpaper_sha256' => $sha256 !== '' ? $sha256 : null,
            'status'           => $status,
            'last_error_code'  => $status === 'error' ? ($errorCode ?: 'CLIENT_ERROR') : null,
            'last_error'       => $status === 'error' ? ($message ?: 'Erro informado pelo cliente.') : null,
            'last_check'       => $now,
            'last_ip'          => Security::cleanText($ipAddress ?? '', 45),
            'updated_at'       => $now,
        ];
        if ($status === 'success' && $matchesActiveRollout) {
            $values['last_apply'] = $now;
            $values['force_reapply'] = 0;
        }
        if ($activeRolloutId !== '' && $matchesActiveRollout) {
            $values['rollout_status'] = $status;
            $values['rollout_finished_at'] = $now;
        }
        $DB->update(self::TABLE, $values, ['id' => (int) $client['id']]);

        $updated = $this->findById((int) $client['id']) ?? array_replace($client, $values);
        if ($status === 'success') {
            (new ClientEventRepository())->record($updated, $payload, $ipAddress);
        }

        return $updated;
    }

    public function setForceReapply(int $id): void
    {
        global $DB;
        if ($this->findById($id) === null) {
            throw new RuntimeException('Cliente nao encontrado.');
        }
        $DB->update(self::TABLE, [
            'force_reapply' => 1,
            'updated_at'    => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
        Audit::record('force_reapply', 'client', $id, false, true);
    }

    /** @return array{id:string,count:int,started_at:string} */
    public function startRollout(): array
    {
        global $DB;

        $where = ['revoked_at' => null];
        $count = (int) countElementsInTable(self::TABLE, $where);
        if ($count === 0) {
            return ['id' => '', 'count' => 0, 'started_at' => ''];
        }

        $rolloutId = date('YmdHis') . '-' . bin2hex(random_bytes(12));
        $startedAt = date('Y-m-d H:i:s');
        $DB->update(self::TABLE, [
            'force_reapply'      => 1,
            'rollout_id'         => $rolloutId,
            'rollout_status'     => 'pending',
            'rollout_started_at' => $startedAt,
            'rollout_finished_at'=> null,
            'updated_at'         => $startedAt,
        ], $where);
        Audit::record('force_reapply_all', 'client', null, null, [
            'clients'    => $count,
            'rollout_id' => $rolloutId,
        ]);

        return ['id' => $rolloutId, 'count' => $count, 'started_at' => $startedAt];
    }

    public function markRolloutApplying(int $clientId, string $rolloutId): void
    {
        global $DB;
        if ($rolloutId === '') {
            return;
        }
        $DB->update(self::TABLE, [
            'rollout_status'      => 'applying',
            'rollout_finished_at' => null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ], [
            'id'             => $clientId,
            'rollout_id'     => $rolloutId,
            'rollout_status' => ['pending', 'error'],
        ]);
    }

    public function revoke(int $id): void
    {
        global $DB;
        if ($this->findById($id) === null) {
            throw new RuntimeException('Cliente nao encontrado.');
        }
        $DB->update(self::TABLE, [
            'revoked_at' => date('Y-m-d H:i:s'),
            'token_hash' => null,
            'status'     => 'revoked',
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
        Audit::record('revoke_client', 'client', $id, null, 'revoked');
    }

    public function reconcileUnmatched(int $limit = 500): int
    {
        global $DB;
        $updated = 0;
        $iterator = $DB->request([
            'SELECT' => ['id', 'hostname'],
            'FROM'   => self::TABLE,
            'WHERE'  => ['computers_id' => null, 'revoked_at' => null],
            'LIMIT'  => max(1, min(5000, $limit)),
        ]);
        foreach ($iterator as $client) {
            $computerId = $this->reconcileComputer((string) $client['hostname']);
            if ($computerId !== null) {
                $DB->update(self::TABLE, [
                    'computers_id' => $computerId,
                    'updated_at'   => date('Y-m-d H:i:s'),
                ], ['id' => (int) $client['id']]);
                $updated++;
            }
        }
        return $updated;
    }

    private function reconcileComputer(string $hostname): ?int
    {
        global $DB;
        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_computers',
            'WHERE'  => ['name' => $hostname, 'is_deleted' => 0],
            'LIMIT'  => 2,
        ]);
        if ($iterator->count() !== 1) {
            return null;
        }
        $row = $iterator->current();
        return is_array($row) ? (int) $row['id'] : null;
    }
}
