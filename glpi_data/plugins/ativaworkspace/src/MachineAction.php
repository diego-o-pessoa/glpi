<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

/**
 * Acoes agendadas para uma maquina, executadas pelo servico Ativa Workspace.
 * Fase 2: desinstalar um programa.
 *
 * O servidor NUNCA envia um comando: guarda apenas o alvo (nome do programa +
 * a chave do registro de desinstalacao). O cliente resolve o comando de
 * desinstalacao que o proprio Windows registrou (QuietUninstallString) e roda
 * em silencio. Assim nenhum comando arbitrario trafega ou e armazenado.
 */
final class MachineAction
{
    public const TABLE = 'glpi_plugin_ativaworkspace_machineactions';

    public const STATUS_QUEUED  = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DONE    = 'done';
    public const STATUS_FAILED  = 'failed';

    private const SCOPES = ['hklm64', 'hklm32', 'hkcu'];

    /** Enfileira uma desinstalacao. Devolve o id, ou 0 se os dados nao batem. */
    public static function queueUninstall(int $computersId, string $target, string $scope, string $regKey, int $userId): int
    {
        global $DB;

        $scope = mb_strtolower(trim($scope));
        $target = trim($target);
        $regKey = trim($regKey);
        if ($computersId <= 0 || $target === '' || $regKey === '' || !in_array($scope, self::SCOPES, true)) {
            return 0;
        }
        // A chave do registro nao pode conter caminho/curinga: e so o nome da subchave.
        if (!preg_match('/^[^\\\\\\/]{1,255}$/D', $regKey)) {
            return 0;
        }
        // Nao duplica: se ja ha uma desinstalacao pendente do mesmo alvo, reaproveita.
        $pending = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => self::TABLE,
            'WHERE'  => ['computers_id' => $computersId, 'reg_key' => $regKey, 'status' => [self::STATUS_QUEUED, self::STATUS_RUNNING]],
            'LIMIT'  => 1,
        ])->current();
        if (is_array($pending)) {
            return (int) $pending['id'];
        }

        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $DB->insert(self::TABLE, [
            'computers_id' => $computersId,
            'action'       => 'uninstall',
            'target'       => mb_substr($target, 0, 255),
            'scope'        => $scope,
            'reg_key'      => mb_substr($regKey, 0, 255),
            'status'       => self::STATUS_QUEUED,
            'users_id'     => $userId,
            'requested_at' => $now,
            'date_mod'     => $now,
        ]);
        return (int) $DB->insertId();
    }

    /**
     * Proxima acao pendente da maquina; marca como running e devolve. Null se nao ha.
     *
     * @return array<string, mixed>|null
     */
    public static function nextForComputer(int $computersId): ?array
    {
        global $DB;

        $row = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computersId, 'status' => self::STATUS_QUEUED],
            'ORDER' => ['id ASC'],
            'LIMIT' => 1,
        ])->current();
        if (!is_array($row)) {
            return null;
        }
        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $DB->update(self::TABLE, ['status' => self::STATUS_RUNNING, 'date_mod' => $now], ['id' => (int) $row['id']]);
        return [
            'id'      => (int) $row['id'],
            'action'  => (string) $row['action'],
            'target'  => (string) $row['target'],
            'scope'   => (string) $row['scope'],
            'reg_key' => (string) $row['reg_key'],
        ];
    }

    /** Registra o resultado reportado pelo cliente. */
    public static function report(int $id, int $computersId, bool $ok, string $message): bool
    {
        global $DB;

        $row = $DB->request(['SELECT' => ['id', 'action', 'scope', 'reg_key'], 'FROM' => self::TABLE, 'WHERE' => ['id' => $id, 'computers_id' => $computersId], 'LIMIT' => 1])->current();
        if (!is_array($row)) {
            return false;
        }
        // Desinstalou: some da lista de programas na hora (nao espera o proximo inventario).
        if ($ok && $row['action'] === 'uninstall') {
            Inventory::removeProgram($computersId, (string) $row['scope'], (string) $row['reg_key']);
        }
        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $DB->update(self::TABLE, [
            'status'   => $ok ? self::STATUS_DONE : self::STATUS_FAILED,
            'message'  => mb_substr($message, 0, 255),
            'date_mod' => $now,
        ], ['id' => $id]);
        return true;
    }

    /**
     * Acoes pendentes/recentes da maquina, para a aba (ultimas primeiro).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function forComputer(int $computersId, int $limit = 20): array
    {
        global $DB;

        $rows = [];
        foreach ($DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computersId],
            'ORDER' => ['id DESC'],
            'LIMIT' => $limit,
        ]) as $row) {
            $key = (string) $row['reg_key'];
            // Ordem DESC: a primeira vista de cada chave e a acao mais recente.
            if (isset($rows[$key])) {
                continue;
            }
            $rows[$key] = [
                'id'       => (int) $row['id'],
                'target'   => (string) $row['target'],
                'scope'    => (string) $row['scope'],
                'status'   => (string) $row['status'],
                'message'  => (string) $row['message'],
                'date_mod' => (string) $row['date_mod'],
            ];
        }
        return $rows;
    }

    /**
     * Lista das acoes (mais recentes) + contagem, para a barra de progresso.
     *
     * @return array{items: array<int, array<string, mixed>>, counts: array<string, int>, active: bool}
     */
    public static function progressForComputer(int $computersId, int $limit = 50): array
    {
        global $DB;

        $items = [];
        $counts = ['total' => 0, 'queued' => 0, 'running' => 0, 'done' => 0, 'failed' => 0];
        foreach ($DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computersId],
            'ORDER' => ['id DESC'],
            'LIMIT' => $limit,
        ]) as $row) {
            $status = (string) $row['status'];
            $items[] = [
                'id'      => (int) $row['id'],
                'target'  => (string) $row['target'],
                'key'     => (string) $row['reg_key'],
                'status'  => $status,
                'message' => (string) $row['message'],
            ];
            $counts['total']++;
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }
        // Ordem cronologica para a lista (mais antigo primeiro).
        $items = array_reverse($items);
        return [
            'items'  => $items,
            'counts' => $counts,
            'active' => ($counts['queued'] + $counts['running']) > 0,
        ];
    }
}
