<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use Exception;
use GLPIKey;
use Throwable;

/**
 * Computers reported by the Ativa Updater service and their remote access sessions.
 *
 * A session goes: pending (waiting for the computer / the user) -> accepted (password
 * available to technicians) -> closing (computer replaces the password) -> closed.
 * Every command sent to the computer increments request_seq; the computer answers
 * each sequence number once.
 */
final class ClientRepository
{
    public const TABLE = 'glpi_plugin_ativaremote_clients';
    public const ONLINE_SECONDS = 60;
    public const POLL_SECONDS = 10;
    /** The computer polls every 10 s and the user has 60 s to answer. */
    public const PENDING_TIMEOUT_SECONDS = 180;
    public const CLOSING_TIMEOUT_SECONDS = 180;
    public const SESSION_MAX_SECONDS = 4 * 3600;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_CLOSING = 'closing';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAILED = 'failed';

    public const ACTION_OPEN = 'open';
    public const ACTION_CLOSE = 'close';

    private const RUSTDESK_ID_PATTERN = '/^[A-Za-z0-9_-]{6,32}$/D';
    private const PASSWORD_PATTERN = '/^[A-Za-z0-9]{8,64}$/D';
    private const VERSION_PATTERN = '/^\d{1,5}\.\d{1,5}\.\d{1,5}$/D';

    /**
     * Stores what the computer reported and returns the command it must execute.
     *
     * @throws ApiException
     */
    public function report(array $payload, string $ipAddress): array
    {
        global $DB;

        $guid = strtolower(trim((string) ($payload['machine_guid'] ?? '')));
        $hostname = trim((string) ($payload['hostname'] ?? ''));
        if (!preg_match('/^[a-f0-9-]{32,64}$/D', $guid) || !preg_match('/^[a-zA-Z0-9._-]{1,255}$/D', $hostname)) {
            throw new ApiException('INVALID_PAYLOAD', 'Identificacao do computador invalida.', 422);
        }
        $rustdesk = is_array($payload['rustdesk'] ?? null) ? $payload['rustdesk'] : [];
        $rustdeskId = trim((string) ($rustdesk['id'] ?? ''));
        if ($rustdeskId !== '' && !preg_match(self::RUSTDESK_ID_PATTERN, $rustdeskId)) {
            throw new ApiException('INVALID_PAYLOAD', 'ID do RustDesk invalido.', 422);
        }
        $clientVersion = trim((string) ($payload['client_version'] ?? ''));
        $rustdeskVersion = trim((string) ($rustdesk['version'] ?? ''));
        $installed = ($rustdesk['installed'] ?? false) === true;

        $now = ServerClock::now();
        $data = [
            'hostname'         => strtoupper($hostname),
            'client_version'   => preg_match(self::VERSION_PATTERN, $clientVersion) ? $clientVersion : '',
            'rustdesk_version' => preg_match('/^[0-9A-Za-z.+-]{1,32}$/D', $rustdeskVersion) ? $rustdeskVersion : null,
            'rustdesk_ready'   => ($rustdesk['ready'] ?? false) === true ? 1 : 0,
            'rustdesk_message' => mb_substr(trim((string) ($rustdesk['message'] ?? '')), 0, 255),
            'last_check'       => $now,
            'last_ip'          => mb_substr($ipAddress, 0, 45),
            'updated_at'       => $now,
        ];
        if ($rustdeskId !== '') {
            $data['rustdesk_id'] = $rustdeskId;
        } elseif (!$installed) {
            $data['rustdesk_id'] = null;
        }

        $client = $this->findByMachineGuid($guid);
        if ($client === null) {
            $DB->insert(self::TABLE, $data + [
                'machine_guid'  => $guid,
                'registered_at' => $now,
                'computers_id'  => $this->computerId($data['hostname']),
            ]);
        } else {
            if (empty($client['computers_id'])) {
                $data['computers_id'] = $this->computerId($data['hostname']);
            }
            $DB->update(self::TABLE, $data, ['id' => (int) $client['id']]);
        }
        $client = $this->findByMachineGuid($guid);
        if ($client === null) {
            throw new ApiException('DATABASE_ERROR', 'Nao foi possivel registrar o computador.', 500);
        }

        if (is_array($payload['result'] ?? null)) {
            $this->applyResult($client, $payload['result']);
        }
        $this->expire((int) $client['id']);
        $client = $this->findById((int) $client['id']) ?? $client;

        $request = null;
        if (in_array($client['request_action'], [self::ACTION_OPEN, self::ACTION_CLOSE], true)) {
            $request = [
                'seq'          => (int) $client['request_seq'],
                'action'       => $client['request_action'],
                'requested_by' => $client['requested_by'] ? mb_substr(getUserName((int) $client['requested_by']), 0, 80) : '',
            ];
        }

        return [
            'require_consent'    => self::requiresConsent($client),
            'request'            => $request,
            'poll_after_seconds' => self::POLL_SECONDS,
        ];
    }

    private function applyResult(array $client, array $result): void
    {
        global $DB;

        $seq = $result['seq'] ?? null;
        if (!is_int($seq) || $seq !== (int) $client['request_seq']) {
            return;
        }
        $status = (string) ($result['status'] ?? '');
        $message = mb_substr(trim((string) ($result['message'] ?? '')), 0, 255);
        $now = ServerClock::now();
        $updates = [];

        if ($client['request_action'] === self::ACTION_OPEN && $client['remote_access_status'] === self::STATUS_PENDING) {
            $password = (string) ($result['password'] ?? '');
            if ($status === 'accepted' && preg_match(self::PASSWORD_PATTERN, $password)) {
                $updates = [
                    'remote_access_status' => self::STATUS_ACCEPTED,
                    'session_password'     => (new GLPIKey())->encrypt($password),
                    'session_started_at'   => $now,
                    'status_message'       => $message ?: 'Acesso liberado.',
                ];
            } elseif (in_array($status, ['rejected', 'timeout', 'no_user'], true)) {
                $updates = [
                    'remote_access_status' => self::STATUS_REJECTED,
                    'request_action'       => null,
                    'status_message'       => $message ?: 'Acesso recusado.',
                ];
            } else {
                $updates = [
                    'remote_access_status' => self::STATUS_FAILED,
                    'request_action'       => null,
                    'status_message'       => $message ?: 'O computador nao conseguiu liberar o acesso.',
                ];
            }
        } elseif ($client['request_action'] === self::ACTION_CLOSE) {
            $updates = ['request_action' => null];
            if ($client['remote_access_status'] === self::STATUS_CLOSING) {
                $updates['remote_access_status'] = null;
            }
            $updates['status_message'] = $status === 'closed'
                ? 'Sessao encerrada; a senha foi trocada.'
                : ($message ?: 'O computador vai trocar a senha assim que possivel.');
        }

        if ($updates !== []) {
            $DB->update(self::TABLE, $updates + ['updated_at' => $now], ['id' => (int) $client['id']]);
        }
    }

    /** Gives up on requests the computer did not answer and ends sessions that are too long. */
    private function expire(int $id): void
    {
        $client = $this->findById($id);
        if ($client === null) {
            return;
        }
        $now = time();
        $status = $client['remote_access_status'];
        $requestedAt = ServerClock::toTimestamp($client['requested_at']);
        if ($status === self::STATUS_PENDING && $now - $requestedAt > self::PENDING_TIMEOUT_SECONDS) {
            $this->sendClose($client, self::STATUS_FAILED, 'O computador nao respondeu a solicitacao.');
        } elseif ($status === self::STATUS_ACCEPTED
            && $now - ServerClock::toTimestamp($client['session_started_at']) > self::SESSION_MAX_SECONDS
        ) {
            $this->sendClose($client, self::STATUS_CLOSING, 'Sessao encerrada automaticamente apos 4 horas.');
        } elseif ($status === self::STATUS_CLOSING && $now - $requestedAt > self::CLOSING_TIMEOUT_SECONDS) {
            global $DB;
            // The close command stays queued: the computer replaces the password when it comes back.
            $DB->update(self::TABLE, [
                'remote_access_status' => null,
                'status_message'       => 'Sessao encerrada; a senha sera trocada quando o computador voltar.',
                'updated_at'           => ServerClock::now(),
            ], ['id' => $id]);
        }
    }

    /** @return array The computer after the request (with its current request_seq). */
    public function requestAccess(int $id, int $userId): array
    {
        global $DB;

        $this->expire($id);
        $client = $this->require($id);
        if (in_array($client['remote_access_status'], [self::STATUS_PENDING, self::STATUS_ACCEPTED], true)) {
            return $client;
        }
        if (!self::isOnline($client)) {
            throw new Exception('O computador esta offline.');
        }
        if (empty($client['rustdesk_id'])) {
            throw new Exception('O RustDesk deste computador ainda nao esta pronto.');
        }
        $now = ServerClock::now();
        $DB->update(self::TABLE, [
            'request_seq'          => (int) $client['request_seq'] + 1,
            'request_action'       => self::ACTION_OPEN,
            'remote_access_status' => self::STATUS_PENDING,
            'requested_by'         => $userId,
            'requested_at'         => $now,
            'session_password'     => null,
            'session_started_at'   => null,
            'status_message'       => self::requiresConsent($client)
                ? 'Aguardando a autorizacao do usuario.'
                : 'Aguardando o computador liberar o acesso.',
            'updated_at'           => $now,
        ], ['id' => $id]);
        return $this->require($id);
    }

    public function closeAccess(int $id): void
    {
        $client = $this->require($id);
        if ($client['remote_access_status'] === self::STATUS_CLOSING) {
            return;
        }
        if (in_array($client['remote_access_status'], [self::STATUS_PENDING, self::STATUS_ACCEPTED], true)) {
            $this->sendClose($client, self::STATUS_CLOSING, 'Encerrando a sessao...');
            return;
        }
        global $DB;
        // Clears a refused or failed request from the list.
        $DB->update(self::TABLE, [
            'remote_access_status' => null,
            'status_message'       => null,
            'updated_at'           => ServerClock::now(),
        ], ['id' => $id]);
    }

    private function sendClose(array $client, ?string $status, string $message): void
    {
        global $DB;
        $now = ServerClock::now();
        $DB->update(self::TABLE, [
            'request_seq'          => (int) $client['request_seq'] + 1,
            'request_action'       => self::ACTION_CLOSE,
            'remote_access_status' => $status,
            'requested_at'         => $now,
            'session_password'     => null,
            'session_started_at'   => null,
            'status_message'       => $message,
            'updated_at'           => $now,
        ], ['id' => (int) $client['id']]);
    }

    public function setRequireConsent(int $id, bool $require): void
    {
        global $DB;
        $client = $this->require($id);
        if (!$require && ProtectionPolicy::isProtected($client)) {
            throw new Exception('Este computador pertence a um grupo protegido: a autorização do usuário é obrigatória.');
        }
        $DB->update(self::TABLE, [
            'require_consent' => $require ? 1 : 0,
            'updated_at'      => ServerClock::now(),
        ], ['id' => $id]);
    }

    /**
     * Rows for the dashboard, with the session password only when $withPassword is true.
     * Protected computers never carry the password: it is released by connectionFor()
     * after the T.I. password is checked.
     */
    public function listForDashboard(int $start, int $limit, bool $withPassword): array
    {
        global $DB;

        $rows = [];
        foreach ($DB->request([
            'FROM'  => self::TABLE,
            'ORDER' => ['hostname ASC'],
            'START' => $start,
            'LIMIT' => $limit,
        ]) as $row) {
            $this->expire((int) $row['id']);
            $rows[] = $this->findById((int) $row['id']) ?? $row;
        }
        $reasons = ProtectionPolicy::evaluate($rows);

        foreach ($rows as &$row) {
            $row['protected'] = isset($reasons[(int) $row['id']]);
            $row['protection_reason'] = $reasons[(int) $row['id']] ?? null;
            $row['ti_verified'] = $row['protected'] && TiPasswordGate::isVerified($row);
            $row['require_consent'] = $row['protected'] || (bool) $row['require_consent'];
            $row['online'] = self::isOnline($row);
            $connection = null;
            if ($withPassword && !$row['protected']) {
                $connection = $this->connection($row);
            }
            unset($row['session_password'], $row['rustdesk_password'], $row['token_hash']);
            $row['password'] = $connection['password'] ?? null;
            $row['connect_url'] = $connection['connect_url'] ?? null;
            $row['can_connect'] = $row['remote_access_status'] === self::STATUS_ACCEPTED && !empty($row['rustdesk_id']);
        }
        unset($row);
        return $rows;
    }

    /**
     * ID, session password and rustdesk:// link of an open session.
     *
     * @throws Exception when the session is not open
     */
    public function connectionFor(int $id): array
    {
        $this->expire($id);
        $connection = $this->connection($this->require($id));
        if ($connection === null) {
            throw new Exception('A sessão deste computador não está liberada. Solicite o acesso novamente.');
        }
        return $connection;
    }

    private function connection(array $client): ?array
    {
        if ($client['remote_access_status'] !== self::STATUS_ACCEPTED || empty($client['rustdesk_id'])) {
            return null;
        }
        $password = self::decrypt($client['session_password']);
        if ($password === null) {
            return null;
        }
        $id = (string) $client['rustdesk_id'];
        return [
            'rustdesk_id' => $id,
            'password'    => $password,
            'connect_url' => 'rustdesk://connection/new/' . rawurlencode($id) . '?password=' . rawurlencode($password),
        ];
    }

    /**
     * Computers protected by hand in the settings (for machines the inventory cannot identify).
     *
     * @param int[] $ids
     */
    public function setManualProtection(array $ids): void
    {
        global $DB;
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $now = ServerClock::now();
        $DB->update(self::TABLE, ['protected_manual' => 0, 'updated_at' => $now], [
            'protected_manual' => 1,
        ] + ($ids !== [] ? ['NOT' => ['id' => $ids]] : []));
        if ($ids !== []) {
            $DB->update(self::TABLE, ['protected_manual' => 1, 'updated_at' => $now], ['id' => $ids]);
        }
    }

    /** @return array[] Every computer, for the settings page. */
    public function all(): array
    {
        global $DB;
        return iterator_to_array($DB->request(['FROM' => self::TABLE, 'ORDER' => ['hostname ASC']]), false);
    }

    /** Protected computers always ask the user, whatever the per-computer setting says. */
    public static function requiresConsent(array $client): bool
    {
        return (bool) $client['require_consent'] || ProtectionPolicy::isProtected($client);
    }

    public static function isOnline(array $client): bool
    {
        $lastCheck = ServerClock::toTimestamp($client['last_check'] ?? null);
        return $lastCheck > 0 && time() - $lastCheck <= self::ONLINE_SECONDS;
    }

    private static function decrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return (new GLPIKey())->decrypt($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function computerId(string $hostname): int
    {
        global $DB;
        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_computers',
            'WHERE'  => ['name' => $hostname, 'is_deleted' => 0, 'is_template' => 0],
            'LIMIT'  => 2,
        ]);
        // Only link when the name is unambiguous.
        return count($iterator) === 1 ? (int) $iterator->current()['id'] : 0;
    }

    private function require(int $id): array
    {
        $client = $this->findById($id);
        if ($client === null) {
            throw new Exception('Computador nao encontrado.');
        }
        return $client;
    }

    public function findByMachineGuid(string $machineGuid): ?array
    {
        return $this->findOne(['machine_guid' => $machineGuid]);
    }

    public function findById(int $id): ?array
    {
        return $this->findOne(['id' => $id]);
    }

    private function findOne(array $where): ?array
    {
        global $DB;
        $row = $DB->request(['FROM' => self::TABLE, 'WHERE' => $where, 'LIMIT' => 1])->current();
        return is_array($row) ? $row : null;
    }
}
