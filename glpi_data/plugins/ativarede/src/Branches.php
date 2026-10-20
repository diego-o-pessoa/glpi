<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use Config;
use Entity;
use Location;

/**
 * Filial de cada maquina, para separar Equipamentos por filial.
 *
 * Prioridade:
 * 1. MAC do roteador (gateway) fisico da filial: diferencia filiais que
 *    compartilham a mesma faixa de IP (Limeira, Campo Grande e Londrina em 192.168.0.1).
 * 2. IP do switch onde a maquina esta ligada.
 * 3. IP do gateway ou da maquina para faixas de IP unicas (Matriz, Uberlandia,
 *    Aracatuba, Ribeirao Preto, Cuiaba).
 * 4. GLPI: entidade ou localizacao do computador.
 * 5. Ultima filial conhecida (para notebooks em home office / em transito).
 * 6. Senao, vazio ("Sem filial").
 */
final class Branches
{
    public const SETTING = 'branch_networks';
    public const DEFAULT_RULES = "192.168.80 = Matriz\n192.168.200 = Uberlandia\n192.168.88 = Araçatuba\n192.168.31 = Ribeirão preto\n192.168.1 = Cuiabá";

    public const KNOWN_BRANCHES = [
        'Matriz',
        'Uberlandia',
        'Limeira',
        'Campo Grande',
        'Araçatuba',
        'Ribeirão preto',
        'Londrina',
        'Cuiabá',
    ];

    /** @var list<array{prefix: string, name: string}>|null */
    private static ?array $rules = null;

    /** @var array<string, string>|null */
    private static ?array $macBranches = null;

    /** @var array<string, string> */
    private static array $glpiNames = [];

    /** @return list<array{prefix: string, name: string}> */
    public static function rules(): array
    {
        if (self::$rules === null) {
            self::$rules = self::parse(self::rulesText())['rules'];
        }
        return self::$rules;
    }

    public static function rulesText(): string
    {
        $values = Config::getConfigurationValues(Settings::CONTEXT, [self::SETTING]);
        return (string) ($values[self::SETTING] ?? self::DEFAULT_RULES);
    }

    public static function save(string $text): array
    {
        $parsed = self::parse($text);
        if ($parsed['errors'] !== []) {
            return ['ok' => false, 'message' => 'Não salvei: ' . implode(' ', $parsed['errors'])];
        }
        $lines = array_map(static fn(array $r): string => $r['prefix'] . ' = ' . $r['name'], $parsed['rules']);
        Config::setConfigurationValues(Settings::CONTEXT, [self::SETTING => implode("\n", $lines)]);
        self::$rules = null;
        return ['ok' => true, 'message' => count($lines) === 0
            ? 'Regras de filial apagadas: a filial vem só do GLPI.'
            : 'Filiais por rede salvas (' . count($lines) . ').'];
    }

    /** @return array{rules: list<array{prefix: string, name: string}>, errors: list<string>} */
    public static function parse(string $text): array
    {
        $rules = [];
        $errors = [];
        foreach (preg_split('/\R/', $text) ?: [] as $number => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/^(\d{1,3}(?:\.\d{1,3}){0,3})\.?\s*(?:=|:)\s*(.{1,64})$/u', $line, $m)) {
                $errors[] = 'linha ' . ($number + 1) . ' ("' . mb_substr($line, 0, 40) . '") deve ser como "192.168.80 = Matriz".';
                continue;
            }
            $prefix = $m[1];
            foreach (explode('.', $prefix) as $octet) {
                if ((int) $octet > 255) {
                    $errors[] = 'linha ' . ($number + 1) . ': "' . $prefix . '" não é uma rede válida.';
                    continue 2;
                }
            }
            $rules[] = ['prefix' => $prefix, 'name' => trim($m[2])];
        }
        // Prefixo mais longo primeiro: "192.168.80.2" vence "192.168.80".
        usort($rules, static fn(array $a, array $b): int => strlen($b['prefix']) <=> strlen($a['prefix']));
        return ['rules' => $rules, 'errors' => $errors];
    }

    public static function byIp(string $ip): string
    {
        $ip = trim($ip);
        if ($ip === '') {
            return '';
        }
        foreach (self::rules() as $rule) {
            if ($ip === $rule['prefix'] || str_starts_with($ip, $rule['prefix'] . '.')) {
                return $rule['name'];
            }
        }
        return '';
    }

    /** Busca o nome da filial pelo MAC do roteador fisico. */
    public static function byRouterMac(string $mac): string
    {
        $mac = strtoupper(trim($mac));
        if ($mac === '' || $mac === '00:00:00:00:00:00') {
            return '';
        }
        if (self::$macBranches === null) {
            self::$macBranches = self::loadMacBranches();
        }
        return self::$macBranches[$mac] ?? '';
    }

    private static function loadMacBranches(): array
    {
        global $DB;

        $map = [];
        if ($DB->tableExists(Settings::TABLE_NETWORKS)) {
            foreach ($DB->request(['FROM' => Settings::TABLE_NETWORKS]) as $row) {
                $gwMac = strtoupper(trim((string) ($row['gateway_mac'] ?? '')));
                $name = trim((string) ($row['name'] ?? ''));
                if ($gwMac !== '' && $name !== '') {
                    $map[$gwMac] = $name;
                }
            }
        }
        return $map;
    }

    /**
     * Registra novo roteador descoberto pelo Guardian no glpi_plugin_ativarede_networks.
     */
    public static function registerDiscoveredNetwork(string $gatewayIp, string $gatewayMac): void
    {
        global $DB;

        $gatewayIp = trim($gatewayIp);
        $gatewayMac = strtoupper(trim($gatewayMac));
        if ($gatewayMac === '' || $gatewayMac === '00:00:00:00:00:00') {
            return;
        }
        if (!$DB->tableExists(Settings::TABLE_NETWORKS)) {
            return;
        }
        $existing = $DB->request([
            'FROM'  => Settings::TABLE_NETWORKS,
            'WHERE' => ['gateway_mac' => $gatewayMac],
            'LIMIT' => 1,
        ])->current();
        if ($existing) {
            if ($gatewayIp !== '' && ($existing['gateway_ip'] ?? '') !== $gatewayIp) {
                $DB->update(Settings::TABLE_NETWORKS, ['gateway_ip' => $gatewayIp], ['id' => (int) $existing['id']]);
            }
            return;
        }
        // Sugere o nome se o IP for de rede unica (ex: 192.168.80 = Matriz).
        // 192.168.0.x fica vazio porque e compartilhado entre Limeira, Campo Grande e Londrina.
        $suggested = '';
        if ($gatewayIp !== '' && !str_starts_with($gatewayIp, '192.168.0.')) {
            $suggested = self::byIp($gatewayIp);
        }
        $DB->insert(Settings::TABLE_NETWORKS, [
            'gateway_ip'  => $gatewayIp,
            'gateway_mac' => $gatewayMac,
            'name'        => $suggested,
            'comment'     => '',
            'date_mod'    => date('Y-m-d H:i:s'),
        ]);
        self::$macBranches = null;
    }

    /** Associa ou atualiza a filial de um roteador fisico. */
    public static function saveNetwork(string $gatewayIp, string $gatewayMac, string $name): array
    {
        global $DB;

        $gatewayIp = trim($gatewayIp);
        $gatewayMac = strtoupper(trim($gatewayMac));
        $name = trim($name);
        if ($gatewayMac === '') {
            return ['ok' => false, 'message' => 'MAC do roteador não informado.'];
        }
        if (!$DB->tableExists(Settings::TABLE_NETWORKS)) {
            Schema::upgrade();
        }
        $existing = $DB->request([
            'FROM'  => Settings::TABLE_NETWORKS,
            'WHERE' => ['gateway_mac' => $gatewayMac],
            'LIMIT' => 1,
        ])->current();
        $now = date('Y-m-d H:i:s');
        if ($existing) {
            $DB->update(Settings::TABLE_NETWORKS, [
                'gateway_ip' => $gatewayIp ?: (string) $existing['gateway_ip'],
                'name'       => $name,
                'date_mod'   => $now,
            ], ['id' => (int) $existing['id']]);
        } else {
            $DB->insert(Settings::TABLE_NETWORKS, [
                'gateway_ip'  => $gatewayIp,
                'gateway_mac' => $gatewayMac,
                'name'        => $name,
                'comment'     => '',
                'date_mod'    => $now,
            ]);
        }
        self::$macBranches = null;
        return [
            'ok'      => true,
            'message' => $name !== ''
                ? "Filial '{$name}' associada à rede {$gatewayIp} (MAC {$gatewayMac})."
                : "Associação do roteador {$gatewayMac} removida.",
        ];
    }

    /** Remove um roteador da tabela de filiais. */
    public static function deleteNetwork(int $id): array
    {
        global $DB;

        if ($id <= 0 || !$DB->tableExists(Settings::TABLE_NETWORKS)) {
            return ['ok' => false, 'message' => 'Roteador não encontrado.'];
        }
        $DB->delete(Settings::TABLE_NETWORKS, ['id' => $id]);
        self::$macBranches = null;
        return ['ok' => true, 'message' => 'Roteador removido da lista de filiais.'];
    }

    /**
     * Lista de redes/roteadores para exibicao em "Filiais por rede".
     *
     * @return list<array{id: int, gateway_ip: string, gateway_mac: string, branch_name: string, machine_count: int, sample_machines: string, is_saved: bool}>
     */
    public static function discoveredNetworks(): array
    {
        global $DB;

        $statsByMac = [];
        $hasGwMac = $DB->tableExists(Settings::TABLE_MACHINES) && $DB->fieldExists(Settings::TABLE_MACHINES, 'gateway_mac');
        if ($hasGwMac) {
            $query = [
                'SELECT' => [
                    'gateway_mac',
                    'gateway_ip',
                    'COUNT(*) AS cnt',
                    'GROUP_CONCAT(COALESCE(NULLIF(hostname, ""), CONCAT("#", id)) ORDER BY id SEPARATOR ", ") AS samples',
                ],
                'FROM' => Settings::TABLE_MACHINES,
                'WHERE' => [
                    'gateway_mac' => ['<>', ''],
                ],
                'GROUPBY' => ['gateway_mac', 'gateway_ip'],
            ];
            foreach ($DB->request($query) as $row) {
                $gwMac = strtoupper(trim((string) $row['gateway_mac']));
                if ($gwMac === '' || $gwMac === '00:00:00:00:00:00') {
                    continue;
                }
                $samples = explode(', ', (string) $row['samples']);
                $statsByMac[$gwMac] = [
                    'gateway_ip' => (string) $row['gateway_ip'],
                    'cnt'        => (int) $row['cnt'],
                    'samples'    => implode(', ', array_slice($samples, 0, 4)) . (count($samples) > 4 ? '...' : ''),
                ];
            }
        }

        $networks = [];
        $seenMacs = [];
        if ($DB->tableExists(Settings::TABLE_NETWORKS)) {
            foreach ($DB->request(['FROM' => Settings::TABLE_NETWORKS, 'ORDER' => ['name', 'gateway_ip']]) as $row) {
                $gwMac = strtoupper(trim((string) $row['gateway_mac']));
                $gwIp = trim((string) $row['gateway_ip']);
                $stat = $statsByMac[$gwMac] ?? null;
                $networks[] = [
                    'id'              => (int) $row['id'],
                    'gateway_ip'      => $gwIp ?: ($stat['gateway_ip'] ?? ''),
                    'gateway_mac'     => $gwMac,
                    'branch_name'     => (string) $row['name'],
                    'machine_count'   => $stat ? $stat['cnt'] : 0,
                    'sample_machines' => $stat ? $stat['samples'] : '',
                    'is_saved'        => true,
                ];
                $seenMacs[$gwMac] = true;
            }
        }

        // Redes vistas pelo Guardian mas ainda nao cadastradas no glpi_plugin_ativarede_networks
        foreach ($statsByMac as $gwMac => $stat) {
            if (isset($seenMacs[$gwMac])) {
                continue;
            }
            $gwIp = $stat['gateway_ip'];
            $suggested = ($gwIp !== '' && !str_starts_with($gwIp, '192.168.0.')) ? self::byIp($gwIp) : '';
            $networks[] = [
                'id'              => 0,
                'gateway_ip'      => $gwIp,
                'gateway_mac'     => $gwMac,
                'branch_name'     => $suggested,
                'machine_count'   => $stat['cnt'],
                'sample_machines' => $stat['samples'],
                'is_saved'        => false,
            ];
        }

        // Ordenacao: redes cadastradas e com mais maquinas primeiro
        usort($networks, static function (array $a, array $b): int {
            if ($a['machine_count'] !== $b['machine_count']) {
                return $b['machine_count'] <=> $a['machine_count'];
            }
            return strnatcasecmp($a['gateway_ip'], $b['gateway_ip']);
        });

        return $networks;
    }

    /**
     * Determina se a rede atual da maquina aponta diretamente para uma filial da empresa.
     */
    public static function resolveCurrentBranch(string $gatewayMac, string $gatewayIp, string $machineIp, int $computersId = 0): string
    {
        $gwMac = strtoupper(trim($gatewayMac));
        if ($gwMac !== '') {
            $byMac = self::byRouterMac($gwMac);
            if ($byMac !== '') {
                return $byMac;
            }
        }
        $gwIp = trim($gatewayIp);
        if ($gwIp !== '' && !str_starts_with($gwIp, '192.168.0.')) {
            $byGw = self::byIp($gwIp);
            if ($byGw !== '') {
                return $byGw;
            }
        }
        $machIp = trim($machineIp);
        if ($machIp !== '' && !str_starts_with($machIp, '192.168.0.')) {
            $byMach = self::byIp($machIp);
            if ($byMach !== '') {
                return $byMach;
            }
        }
        return '';
    }

    /**
     * @param array<string, mixed> $machine Linha de Inventory::decoratedMachines()
     * @param array<int, array<string, mixed>> $switches Inventory::switches()
     */
    public static function ofMachine(array $machine, array $switches): string
    {
        // 1. MAC do roteador/gateway fisico da maquina (diferencia 192.168.0.1)
        $gwMac = strtoupper(trim((string) ($machine['gateway_mac'] ?? '')));
        if ($gwMac !== '') {
            $branch = self::byRouterMac($gwMac);
            if ($branch !== '') {
                return $branch;
            }
        }

        // 2. IP do switch onde esta ligada
        $switchIp = (string) ($switches[(int) ($machine['switches_id'] ?? 0)]['mgmt_ip'] ?? '');
        $branch = self::byIp($switchIp);
        if ($branch !== '') {
            return $branch;
        }

        // 3. IP do gateway da maquina (apenas para faixas unicas, excluindo 192.168.0.x)
        $gwIp = trim((string) ($machine['gateway_ip'] ?? ''));
        if ($gwIp !== '' && !str_starts_with($gwIp, '192.168.0.')) {
            $branch = self::byIp($gwIp);
            if ($branch !== '') {
                return $branch;
            }
        }

        // 4. IP da propria maquina (apenas para faixas unicas, excluindo 192.168.0.x)
        $machIp = trim((string) ($machine['ip'] ?? ''));
        if ($machIp !== '' && !str_starts_with($machIp, '192.168.0.')) {
            $branch = self::byIp($machIp);
            if ($branch !== '') {
                return $branch;
            }
        }

        // 5. Entidade ou Localizacao no GLPI
        $fromGlpi = self::fromGlpi((int) ($machine['computer_entity'] ?? 0), (int) ($machine['computer_location'] ?? 0));
        if ($fromGlpi !== '') {
            return $fromGlpi;
        }

        // 6. Filial de origem conhecida (notebook em home office ou em transito)
        $lastKnown = trim((string) ($machine['last_known_branch'] ?? ''));
        if ($lastKnown !== '') {
            return $lastKnown;
        }

        return '';
    }

    /**
     * Indica se a maquina esta fora de sua filial de origem (remoto ou visitando outra filial).
     */
    public static function isRoaming(array $machine, string $currentBranch): bool
    {
        if ($currentBranch === '') {
            return false;
        }
        $lastKnown = trim((string) ($machine['last_known_branch'] ?? ''));
        $gwMac = strtoupper(trim((string) ($machine['gateway_mac'] ?? '')));
        $gwIp = trim((string) ($machine['gateway_ip'] ?? ''));

        // Caso A: Esta em home office (rede nao reconhecida da empresa, mantida por last_known_branch)
        if ($lastKnown !== '' && $currentBranch === $lastKnown) {
            $netBranch = $gwMac !== '' ? self::byRouterMac($gwMac) : '';
            if ($netBranch === '' && $gwIp !== '') {
                $netBranch = !str_starts_with($gwIp, '192.168.0.') ? self::byIp($gwIp) : '';
            }
            if ($netBranch === '' && $gwIp !== '') {
                return true;
            }
        }

        // Caso B: Visitando outra filial (rede atual e da empresa, mas diferente da origem)
        if ($lastKnown !== '' && $currentBranch !== $lastKnown) {
            return true;
        }

        return false;
    }

    public static function fromGlpi(int $entityId, int $locationId): string
    {
        $key = $entityId . '|' . $locationId;
        if (!array_key_exists($key, self::$glpiNames)) {
            $name = '';
            $entity = new Entity();
            // A raiz ("Grupo Ativa") nao e filial.
            if ($entityId > 0 && $entity->getFromDB($entityId) && (int) $entity->fields['level'] > 1) {
                $name = self::shortName((string) $entity->fields['name']);
            }
            $location = new Location();
            if ($name === '' && $locationId > 0 && $location->getFromDB($locationId)) {
                $name = self::shortName((string) $location->fields['name']);
            }
            self::$glpiNames[$key] = $name;
        }
        return self::$glpiNames[$key];
    }

    /**
     * Nome curto da filial: separa por " - ", descarta codigos ("001001") e o
     * nome da empresa ("Ativa Locacao"); fica o ultimo que sobrar.
     */
    public static function shortName(string $name): string
    {
        $parts = array_values(array_filter(
            array_map('trim', explode(' - ', trim($name))),
            static fn(string $part): bool => $part !== '' && preg_match('/^[\d.\/-]+$/', $part) !== 1,
        ));
        if ($parts === []) {
            return trim($name);
        }
        $named = array_values(array_filter($parts, static fn(string $part): bool => preg_match('/^ativa\b/iu', $part) !== 1));
        return $named !== [] ? end($named) : end($parts);
    }
}
