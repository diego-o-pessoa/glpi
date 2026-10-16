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
                'glpi_computers.contact',
                'glpi_users.firstname',
                'glpi_users.realname',
            ],
            'FROM'     => self::TABLE,
            'LEFT JOIN' => [
                'glpi_computers' => [
                    'ON' => [self::TABLE => 'computers_id', 'glpi_computers' => 'id'],
                ],
                'glpi_users' => [
                    'ON' => ['glpi_computers' => 'users_id', 'glpi_users' => 'id'],
                ],
            ],
        ]);
        $users = self::windowsUsers();
        $rede = self::redeNames();
        // O servico envia inventario a cada minuto: sem relato ha 3 min, esta offline.
        $onlineSince = strtotime((string) ($_SESSION['glpi_currenttime'] ?? 'now')) - 180;
        foreach ($iterator as $row) {
            $data = json_decode((string) ($row['data'] ?? ''), true);
            $system = is_array($data) && is_array($data['system'] ?? null) ? $data['system'] : [];
            $computersId = (int) $row['computers_id'];
            $hostname = (string) $row['hostname'];
            $user = $users['id'][$computersId] ?? $users['host'][mb_strtolower($hostname)] ?? '';
            $user = $user !== '' ? $user : self::shortUser((string) ($row['contact'] ?? ''));
            // Mesmo nome do Ativa Rede: o corrigido a mao em Equipamentos; a conta
            // de uma pessoa; ou o primeiro nome da VPN conectada.
            $named = $rede['id'][$computersId] ?? $rede['host'][mb_strtolower($hostname)] ?? null;
            if ($named !== null) {
                $user = self::resolveName($named['manual'], $user, $named['vpn']);
            }
            $rows[] = [
                'computers_id' => $computersId,
                'name'         => (string) ($row['computer_name'] ?: $hostname ?: '—'),
                'hostname'     => $hostname,
                'username'     => $user,
                'glpi_user'    => trim((string) ($row['firstname'] ?? '') . ' ' . (string) ($row['realname'] ?? '')),
                'agent_version'=> (string) $row['agent_version'],
                'reported_at'  => (string) $row['reported_at'],
                'online'       => $row['reported_at'] && strtotime((string) $row['reported_at']) >= $onlineSince,
                'ram_percent'  => (int) ($system['ram_percent'] ?? 0),
                'cpu_percent'  => (int) ($system['cpu_percent'] ?? 0),
                'programs'     => is_array($data['programs'] ?? null) ? count($data['programs']) : 0,
            ];
        }
        usort($rows, static fn (array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));
        return $rows;
    }

    /**
     * Usuario do Windows conectado em cada maquina, do Ativa Guardian (heartbeat
     * a cada minuto). Por computers_id e, se o Guardian nao o resolveu, por
     * hostname. Vazio se o Guardian nao estiver instalado.
     *
     * @return array{id: array<int, string>, host: array<string, string>}
     */
    private static function windowsUsers(): array
    {
        global $DB;

        $users = ['id' => [], 'host' => []];
        $table = 'glpi_plugin_ativaguardian_machines';
        if (!$DB->tableExists($table) || !$DB->fieldExists($table, 'computers_id')) {
            return $users;
        }
        // Mais antigo primeiro: o heartbeat mais recente sobrescreve.
        $iterator = $DB->request([
            'SELECT' => ['computers_id', 'hostname', 'username'],
            'FROM'   => $table,
            'WHERE'  => ['NOT' => ['username' => '']],
            'ORDER'  => ['last_contact ASC'],
        ]);
        foreach ($iterator as $row) {
            $user = self::shortUser((string) $row['username']);
            if ($user === '') {
                continue;
            }
            if ((int) $row['computers_id'] > 0) {
                $users['id'][(int) $row['computers_id']] = $user;
            }
            $users['host'][mb_strtolower((string) $row['hostname'])] = $user;
        }
        return $users;
    }

    /**
     * Nome corrigido a mao e primeiro nome da VPN de cada maquina, do Ativa
     * Rede (por computers_id e por hostname). Vazio sem o Ativa Rede.
     *
     * @return array{id: array<int, array{manual: string, vpn: string}>, host: array<string, array{manual: string, vpn: string}>}
     */
    private static function redeNames(): array
    {
        global $DB;

        $names = ['id' => [], 'host' => []];
        $table = 'glpi_plugin_ativarede_machines';
        if (!$DB->tableExists($table) || !$DB->fieldExists($table, 'user_label')) {
            return $names;
        }
        $hasVpn = $DB->fieldExists($table, 'vpn_user');
        $iterator = $DB->request([
            'SELECT' => array_merge(['computers_id', 'hostname', 'user_label'], $hasVpn ? ['vpn_user'] : []),
            'FROM'   => $table,
            'ORDER'  => ['last_report ASC'],
        ]);
        foreach ($iterator as $row) {
            $entry = ['manual' => trim((string) $row['user_label']), 'vpn' => trim((string) ($row['vpn_user'] ?? ''))];
            if ($entry['manual'] === '' && $entry['vpn'] === '') {
                continue;
            }
            if ((int) $row['computers_id'] > 0) {
                $names['id'][(int) $row['computers_id']] = $entry;
            }
            $names['host'][mb_strtolower((string) $row['hostname'])] = $entry;
        }
        return $names;
    }

    /** Regra do Ativa Rede (PersonName); sem ele, o nome manual vence. */
    private static function resolveName(string $manual, string $auto, string $vpn): string
    {
        if (class_exists(\GlpiPlugin\Ativarede\PersonName::class)) {
            return \GlpiPlugin\Ativarede\PersonName::resolve($manual, $auto, $vpn);
        }
        return $manual !== '' ? $manual : ($auto !== '' ? $auto : $vpn);
    }

    /** "DOMINIO\usuario" ou "usuario@dominio" -> "usuario". */
    private static function shortUser(string $user): string
    {
        $user = trim($user);
        if (str_contains($user, '\\')) {
            $user = substr($user, strrpos($user, '\\') + 1);
        }
        if (str_contains($user, '@')) {
            $user = substr($user, 0, strpos($user, '@'));
        }
        return $user;
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
            'network'      => is_array($data['network'] ?? null) ? $data['network'] : [],
        ];
    }
}
