<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

/**
 * Inventario da maquina reportado pelo servico Ativa Workspace: programas,
 * discos, memoria, CPU e processos. Uma linha por maquina (ultimo snapshot).
 *
 * Nada sensivel: sao os mesmos dados que o inventario do GLPI ja coleta. O
 * `data` guarda o JSON como reportado, com limite de tamanho.
 */
final class Inventory
{
    public const TABLE = 'glpi_plugin_ativaworkspace_inventory';
    private const MAX_BYTES = 512 * 1024;

    /** Grava/atualiza o inventario de uma maquina. Devolve false se o GUID nao casa. */
    public static function store(string $guid, array $data): bool
    {
        global $DB;

        $guid = mb_strtolower(trim($guid));
        if (!preg_match('/^[a-f0-9-]{16,64}$/D', $guid)) {
            return false;
        }
        $computersId = MachineIdentity::computerFromGuid($guid);
        if ($computersId <= 0) {
            return false;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false || strlen($json) > self::MAX_BYTES) {
            return false;
        }

        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $row = [
            'computers_id'  => $computersId,
            'machine_guid'  => $guid,
            'hostname'      => mb_substr((string) ($data['hostname'] ?? ''), 0, 255),
            'agent_version' => mb_substr((string) ($data['agent_version'] ?? ''), 0, 32),
            'data'          => $json,
            'reported_at'   => $now,
            'date_mod'      => $now,
        ];

        $existing = $DB->request(['SELECT' => ['id'], 'FROM' => self::TABLE, 'WHERE' => ['machine_guid' => $guid], 'LIMIT' => 1])->current();
        if (is_array($existing)) {
            $DB->update(self::TABLE, $row, ['id' => (int) $existing['id']]);
        } else {
            $DB->insert(self::TABLE, $row);
        }
        return true;
    }

    /**
     * Maquinas com inventario, para a lista da aba. Resumo leve por linha.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        global $DB;

        $rows = [];
        $iterator = $DB->request([
            'SELECT'   => [
                self::TABLE . '.computers_id',
                self::TABLE . '.hostname',
                self::TABLE . '.agent_version',
                self::TABLE . '.reported_at',
                self::TABLE . '.data',
                'glpi_computers.name AS computer_name',
            ],
            'FROM'     => self::TABLE,
            'LEFT JOIN' => [
                'glpi_computers' => [
                    'ON' => [self::TABLE => 'computers_id', 'glpi_computers' => 'id'],
                ],
            ],
            'ORDER'    => [self::TABLE . '.reported_at DESC'],
        ]);
        foreach ($iterator as $row) {
            $data = json_decode((string) ($row['data'] ?? ''), true);
            $system = is_array($data) && is_array($data['system'] ?? null) ? $data['system'] : [];
            $rows[] = [
                'computers_id' => (int) $row['computers_id'],
                'name'         => (string) ($row['computer_name'] ?: $row['hostname'] ?: '—'),
                'hostname'     => (string) $row['hostname'],
                'agent_version'=> (string) $row['agent_version'],
                'reported_at'  => (string) $row['reported_at'],
                'ram_percent'  => (int) ($system['ram_percent'] ?? 0),
                'cpu_percent'  => (int) ($system['cpu_percent'] ?? 0),
                'programs'     => is_array($data['programs'] ?? null) ? count($data['programs']) : 0,
            ];
        }
        return $rows;
    }

    /**
     * Tira um programa do ultimo inventario salvo (apos desinstalar com sucesso),
     * sem esperar o proximo envio do servico.
     */
    public static function removeProgram(int $computersId, string $scope, string $regKey): void
    {
        global $DB;

        $row = $DB->request(['SELECT' => ['id', 'data'], 'FROM' => self::TABLE, 'WHERE' => ['computers_id' => $computersId], 'LIMIT' => 1])->current();
        if (!is_array($row)) {
            return;
        }
        $data = json_decode((string) ($row['data'] ?? ''), true);
        if (!is_array($data) || !is_array($data['programs'] ?? null)) {
            return;
        }
        $before = count($data['programs']);
        $data['programs'] = array_values(array_filter(
            $data['programs'],
            static fn ($p): bool => !(is_array($p) && ($p['scope'] ?? '') === $scope && ($p['key'] ?? '') === $regKey)
        ));
        if (count($data['programs']) === $before) {
            return;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json !== false) {
            $DB->update(self::TABLE, ['data' => $json], ['id' => (int) $row['id']]);
        }
    }

    /**
     * Inventario completo de uma maquina (JSON decodificado), ou null.
     *
     * @return array<string, mixed>|null
     */
    public static function forComputer(int $computersId): ?array
    {
        global $DB;

        $row = $DB->request(['FROM' => self::TABLE, 'WHERE' => ['computers_id' => $computersId], 'LIMIT' => 1])->current();
        if (!is_array($row)) {
            return null;
        }
        $data = json_decode((string) ($row['data'] ?? ''), true);
        return [
            'computers_id' => $computersId,
            'hostname'     => (string) $row['hostname'],
            'agent_version'=> (string) $row['agent_version'],
            'reported_at'  => (string) $row['reported_at'],
            'system'       => is_array($data['system'] ?? null) ? $data['system'] : [],
            'disks'        => is_array($data['disks'] ?? null) ? $data['disks'] : [],
            'programs'     => is_array($data['programs'] ?? null) ? $data['programs'] : [],
            'processes'    => is_array($data['processes'] ?? null) ? $data['processes'] : [],
        ];
    }
}
