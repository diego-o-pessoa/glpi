<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use Exception;

class ClientRepository
{
    private const TABLE = 'glpi_plugin_ativaremote_clients';

    public function register(array $payload, ?string $ipAddress): array
    {
        global $DB;

        $hostname = strtoupper($payload['hostname'] ?? '');
        $machineGuid = $payload['machine_guid'] ?? '';
        
        if (empty($hostname) || empty($machineGuid)) {
            throw new Exception("Hostname ou Machine GUID invalidos");
        }

        $now = date('Y-m-d H:i:s');
        $token = bin2hex(random_bytes(32));

        $existing = $this->findByMachineGuid($machineGuid);

        $values = [
            'hostname'             => $hostname,
            'client_version'       => $payload['client_version'] ?? '',
            'rustdesk_id'          => $payload['rustdesk_id'] ?? null,
            'rustdesk_password'    => $payload['rustdesk_password'] ?? null,
            'token_hash'           => hash('sha256', $token),
            'last_check'           => $now,
            'last_ip'              => $ipAddress,
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

        return ['id' => $id, 'token' => $token];
    }

    public function authenticate(string $token): ?array
    {
        global $DB;

        $hash = hash('sha256', $token);
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['token_hash' => $hash],
            'LIMIT' => 1,
        ]);
        $client = $iterator->current();
        
        if (!is_array($client)) {
            throw new Exception("Token invalido");
        }

        return $client;
    }

    public function heartbeat(array $client, array $payload, ?string $ipAddress): void
    {
        global $DB;

        $now = date('Y-m-d H:i:s');
        $updates = [
            'last_check'           => $now,
            'last_ip'              => $ipAddress,
            'updated_at'           => $now,
        ];
        
        if (isset($payload['rustdesk_id'])) {
            $updates['rustdesk_id'] = $payload['rustdesk_id'];
        }
        if (isset($payload['rustdesk_password'])) {
            $updates['rustdesk_password'] = $payload['rustdesk_password'];
        }
        
        if (isset($payload['remote_access_status']) && in_array($payload['remote_access_status'], ['accepted', 'rejected', 'null'])) {
            $status = $payload['remote_access_status'] === 'null' ? null : $payload['remote_access_status'];
            $updates['remote_access_status'] = $status;
        }

        $DB->update(self::TABLE, $updates, ['id' => (int) $client['id']]);
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

    public function setRequireConsent(int $id, bool $require): void
    {
        global $DB;
        if ($this->findById($id) === null) {
            throw new Exception('Cliente nao encontrado.');
        }
        $DB->update(self::TABLE, [
            'require_consent' => $require ? 1 : 0,
            'updated_at'      => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }

    public function setRemoteAccessStatus(int $id, ?string $status): void
    {
        global $DB;
        if ($this->findById($id) === null) {
            throw new Exception('Cliente nao encontrado.');
        }
        $DB->update(self::TABLE, [
            'remote_access_status' => $status,
            'updated_at'           => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }
}
