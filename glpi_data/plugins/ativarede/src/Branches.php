<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use Config;
use Entity;
use Location;

/**
 * Filial de cada maquina, para separar Equipamentos por filial.
 *
 * Prioridade (networkBranch + ofMachine):
 * 1. o roteador (MAC do gateway) com nome dado pela T.I.: diferencia filiais
 *    com o mesmo IP (Limeira, Campo Grande e Londrina em 192.168.0.1);
 * 2. o switch da empresa onde a maquina esta ligada (IP de gerencia);
 * 3. roteador ainda sem nome: sugestao pelas regras de IP, so se varias
 *    maquinas usam o mesmo roteador (em casa ha uma so);
 * 4. Guardian antigo (sem MAC do roteador): o IP da maquina, como antes;
 * 5. GLPI: entidade ou localizacao do computador;
 * 6. a ultima filial onde a maquina foi vista (home office);
 * 7. senao, vazio ("Sem filial").
 */
final class Branches
{
    public const SETTING = 'branch_networks';
    // 192.168.0 fica de fora: e de Limeira, Campo Grande e Londrina (so o roteador diferencia).
    public const DEFAULT_RULES = "192.168.80 = Matriz\n192.168.200 = Uberlândia\n192.168.88 = Araçatuba\n192.168.31 = Ribeirão Preto\n192.168.1 = Cuiabá";

    public const KNOWN_BRANCHES = [
        'Matriz',
        'Uberlândia',
        'Limeira',
        'Campo Grande',
        'Araçatuba',
        'Ribeirão Preto',
        'Londrina',
        'Cuiabá',
    ];

    /** @var list<array{prefix: string, name: string}>|null */
    private static ?array $rules = null;

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

    // ------------------------------------------------------------------
    // Roteador da filial (MAC do gateway)
    //
    // Limeira, Campo Grande e Londrina usam o mesmo 192.168.0.1: so o MAC do
    // roteador (aparelho fisico) diz qual e qual. A T.I. da o nome de cada
    // roteador uma vez em Equipamentos > Filiais por rede.
    //
    // Cuidado com o IP sozinho: 192.168.0.1, 192.168.1.1, 192.168.31.1 e
    // 192.168.88.1 sao os IPs de fabrica dos roteadores de casa mais comuns.
    // Um notebook em home office nao pode virar "Cuiaba" so pelo IP. Por isso
    // um roteador novo nunca e nomeado sozinho: o IP so SUGERE o nome, e so
    // quando o mesmo roteador e visto por varias maquinas (uma filial).
    // ------------------------------------------------------------------

    /** Maquinas no mesmo roteador para a sugestao pelo IP valer (casa tem uma). */
    public const SUGGEST_MIN_MACHINES = 2;

    /** @var array<string, string>|null MAC -> filial (roteadores nomeados) */
    private static ?array $macBranches = null;

    /** @var array<string, int>|null MAC -> maquinas vistas nele */
    private static ?array $macCounts = null;

    /** Filial do roteador nomeado pela T.I. ('' se ainda sem nome). */
    public static function byRouterMac(string $mac): string
    {
        $mac = self::normalizeMac($mac);
        if ($mac === '') {
            return '';
        }
        if (self::$macBranches === null) {
            self::$macBranches = [];
            global $DB;
            if ($DB->tableExists(Settings::TABLE_NETWORKS)) {
                foreach ($DB->request(['FROM' => Settings::TABLE_NETWORKS]) as $row) {
                    $rowMac = self::normalizeMac((string) $row['gateway_mac']);
                    $name = trim((string) $row['name']);
                    if ($rowMac !== '' && $name !== '') {
                        self::$macBranches[$rowMac] = $name;
                    }
                }
            }
        }
        return self::$macBranches[$mac] ?? '';
    }

    /** Quantas maquinas usam este roteador (ultimo relatorio de cada uma). */
    public static function machinesOnRouter(string $mac): int
    {
        $mac = self::normalizeMac($mac);
        if ($mac === '') {
            return 0;
        }
        if (self::$macCounts === null) {
            self::$macCounts = [];
            foreach (self::routerMachines() as $routerMac => $info) {
                self::$macCounts[$routerMac] = count($info['hosts']);
            }
        }
        return self::$macCounts[$mac] ?? 0;
    }

    /** Nome sugerido para um roteador ainda sem nome: pelo IP, se for de filial. */
    public static function suggestedForRouter(string $mac, string $gatewayIp): string
    {
        if (self::machinesOnRouter($mac) < self::SUGGEST_MIN_MACHINES) {
            return '';
        }
        return self::byIp($gatewayIp);
    }

    /**
     * Filial pela REDE onde a maquina esta agora ('' = rede desconhecida,
     * ex.: home office). Ordem: roteador nomeado; switch da empresa (IP de
     * gerencia); roteador ainda sem nome, mas sugerido pelo IP; e, so para
     * Guardian antigo (sem MAC do roteador), o IP da maquina.
     */
    public static function networkBranch(string $gatewayMac, string $gatewayIp, string $switchIp, string $machineIp): string
    {
        $gatewayMac = self::normalizeMac($gatewayMac);
        if ($gatewayMac !== '') {
            $named = self::byRouterMac($gatewayMac);
            if ($named !== '') {
                return $named;
            }
        }
        // Switch gerenciavel: equipamento da empresa, nao existe em casa.
        $bySwitch = self::byIp($switchIp);
        if ($bySwitch !== '') {
            return $bySwitch;
        }
        if ($gatewayMac !== '') {
            return self::suggestedForRouter($gatewayMac, $gatewayIp);
        }
        return self::byIp($machineIp);
    }

    /**
     * @param array<string, mixed> $machine Linha de Inventory::decoratedMachines()
     * @param array<int, array<string, mixed>> $switches Inventory::switches()
     */
    public static function ofMachine(array $machine, array $switches): string
    {
        $switchIp = (string) ($switches[(int) ($machine['switches_id'] ?? 0)]['mgmt_ip'] ?? '');
        return self::networkBranch(
            (string) ($machine['gateway_mac'] ?? ''),
            (string) ($machine['gateway_ip'] ?? ''),
            $switchIp,
            (string) ($machine['ip'] ?? '')
        )
            ?: self::fromGlpi((int) ($machine['computer_entity'] ?? 0), (int) ($machine['computer_location'] ?? 0))
            // Fora de uma rede da empresa (home office): a ultima filial onde foi vista.
            ?: trim((string) ($machine['last_known_branch'] ?? ''));
    }

    /**
     * Fora da rede da filial agora (home office, hotel...): a maquina mandou
     * o roteador dela e ele nao e de nenhuma filial.
     */
    public static function isRoaming(array $machine, array $switches = []): bool
    {
        $gatewayMac = self::normalizeMac((string) ($machine['gateway_mac'] ?? ''));
        if ($gatewayMac === '') {
            return false; // Guardian antigo: nao da para saber
        }
        $switchIp = (string) ($switches[(int) ($machine['switches_id'] ?? 0)]['mgmt_ip'] ?? '');
        return self::networkBranch($gatewayMac, (string) ($machine['gateway_ip'] ?? ''), $switchIp, '') === '';
    }

    /** Novo roteador visto pelo Guardian: entra na lista SEM nome (a T.I. confirma). */
    public static function registerDiscoveredNetwork(string $gatewayIp, string $gatewayMac): void
    {
        global $DB;

        $gatewayIp = trim($gatewayIp);
        $gatewayMac = self::normalizeMac($gatewayMac);
        if ($gatewayMac === '' || !$DB->tableExists(Settings::TABLE_NETWORKS)) {
            return;
        }
        $existing = $DB->request([
            'FROM'  => Settings::TABLE_NETWORKS,
            'WHERE' => ['gateway_mac' => $gatewayMac],
            'LIMIT' => 1,
        ])->current();
        if ($existing) {
            if ($gatewayIp !== '' && (string) $existing['gateway_ip'] !== $gatewayIp) {
                $DB->update(Settings::TABLE_NETWORKS, ['gateway_ip' => $gatewayIp], ['id' => (int) $existing['id']]);
            }
            return;
        }
        $DB->insert(Settings::TABLE_NETWORKS, [
            'gateway_ip'  => $gatewayIp,
            'gateway_mac' => $gatewayMac,
            'name'        => '',
            'comment'     => '',
            'date_mod'    => date('Y-m-d H:i:s'),
        ]);
    }

    /** Nome da filial de um roteador (vazio = tira o nome; ele continua na lista). */
    public static function saveNetwork(string $gatewayIp, string $gatewayMac, string $name): array
    {
        global $DB;

        $gatewayIp = mb_substr(trim($gatewayIp), 0, 64);
        $gatewayMac = self::normalizeMac($gatewayMac);
        $name = mb_substr(trim((string) preg_replace('/\s+/u', ' ', $name)), 0, 64);
        if ($gatewayMac === '') {
            return ['ok' => false, 'message' => 'MAC do roteador inválido.'];
        }
        Schema::upgrade();
        $now = date('Y-m-d H:i:s');
        $existing = $DB->request([
            'FROM'  => Settings::TABLE_NETWORKS,
            'WHERE' => ['gateway_mac' => $gatewayMac],
            'LIMIT' => 1,
        ])->current();
        if ($existing) {
            $DB->update(Settings::TABLE_NETWORKS, [
                'gateway_ip' => $gatewayIp !== '' ? $gatewayIp : (string) $existing['gateway_ip'],
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
        // As maquinas que ja estao nesse roteador passam a ter essa filial
        // como "ultima filial conhecida" (vale quando elas sairem dela).
        if ($name !== '' && $DB->fieldExists(Settings::TABLE_MACHINES, 'last_known_branch')) {
            $DB->update(Settings::TABLE_MACHINES, ['last_known_branch' => $name], ['gateway_mac' => $gatewayMac]);
        }
        return [
            'ok'      => true,
            'message' => $name !== ''
                ? 'Roteador ' . $gatewayMac . ' (' . ($gatewayIp ?: 'sem IP') . ') agora é da filial ' . $name . '.'
                : 'O roteador ' . $gatewayMac . ' ficou sem filial.',
        ];
    }

    /** Tira um roteador da lista (volta sozinho, sem nome, se for visto de novo). */
    public static function deleteNetwork(int $id): array
    {
        global $DB;

        if ($id <= 0 || !$DB->tableExists(Settings::TABLE_NETWORKS)) {
            return ['ok' => false, 'message' => 'Roteador não encontrado.'];
        }
        $DB->delete(Settings::TABLE_NETWORKS, ['id' => $id]);
        self::$macBranches = null;
        return ['ok' => true, 'message' => 'Roteador removido da lista.'];
    }

    /**
     * Roteadores para "Filiais por rede": os cadastrados e os vistos pelas
     * maquinas, com quantas maquinas e quais, e o nome sugerido pelo IP.
     *
     * @return list<array{id: int, gateway_ip: string, gateway_mac: string, branch_name: string, suggested: string,
     *                    machine_count: int, sample_machines: string, is_saved: bool}>
     */
    public static function discoveredNetworks(): array
    {
        global $DB;

        $byMac = self::routerMachines();
        $networks = [];
        if ($DB->tableExists(Settings::TABLE_NETWORKS)) {
            foreach ($DB->request(['FROM' => Settings::TABLE_NETWORKS, 'ORDER' => ['name', 'gateway_ip']]) as $row) {
                $mac = self::normalizeMac((string) $row['gateway_mac']);
                if ($mac === '' || isset($networks[$mac])) {
                    continue;
                }
                $networks[$mac] = self::networkRow((int) $row['id'], (string) $row['gateway_ip'], $mac, (string) $row['name'], $byMac[$mac] ?? null);
            }
        }
        // Visto pelas maquinas e ainda fora da tabela (ex.: tabela criada depois).
        foreach ($byMac as $mac => $info) {
            if (!isset($networks[$mac])) {
                $networks[$mac] = self::networkRow(0, $info['ip'], $mac, '', $info);
            }
        }
        $networks = array_values($networks);
        // Sem nome primeiro (o que falta fazer), depois os com mais maquinas.
        usort($networks, static fn(array $a, array $b): int
            => (($a['branch_name'] !== '') <=> ($b['branch_name'] !== ''))
            ?: ($b['machine_count'] <=> $a['machine_count'])
            ?: strnatcasecmp($a['gateway_ip'], $b['gateway_ip']));
        return $networks;
    }

    /** Filiais para escolher na lista: as conhecidas, as das regras e as ja usadas. */
    public static function knownBranches(): array
    {
        global $DB;

        $names = self::KNOWN_BRANCHES;
        foreach (self::rules() as $rule) {
            $names[] = $rule['name'];
        }
        if ($DB->tableExists(Settings::TABLE_NETWORKS)) {
            foreach ($DB->request(['SELECT' => ['name'], 'FROM' => Settings::TABLE_NETWORKS, 'WHERE' => ['NOT' => ['name' => '']]]) as $row) {
                $names[] = (string) $row['name'];
            }
        }
        $unique = [];
        foreach ($names as $name) {
            $name = trim($name);
            if ($name !== '' && !in_array(mb_strtolower($name), array_map('mb_strtolower', $unique), true)) {
                $unique[] = $name;
            }
        }
        natcasesort($unique);
        return array_values($unique);
    }

    /** @param array{ip: string, hosts: list<string>}|null $info */
    private static function networkRow(int $id, string $ip, string $mac, string $name, ?array $info): array
    {
        $ip = trim($ip) !== '' ? trim($ip) : (string) ($info['ip'] ?? '');
        $hosts = $info['hosts'] ?? [];
        $name = trim($name);
        return [
            'id'              => $id,
            'gateway_ip'      => $ip,
            'gateway_mac'     => $mac,
            'branch_name'     => $name,
            'suggested'       => $name === '' ? self::suggestedForRouter($mac, $ip) : '',
            'machine_count'   => count($hosts),
            'sample_machines' => implode(', ', array_slice($hosts, 0, 4)) . (count($hosts) > 4 ? '…' : ''),
            'is_saved'        => $id > 0,
        ];
    }

    /**
     * Maquinas por roteador, do ultimo relatorio de cada uma. Agrupado aqui
     * (e nao com COUNT/GROUP_CONCAT no SQL): o GLPI poe crases em expressoes
     * no SELECT e a consulta quebrava a pagina Equipamentos.
     *
     * @return array<string, array{ip: string, hosts: list<string>}>
     */
    private static function routerMachines(): array
    {
        global $DB;

        $out = [];
        if (!$DB->tableExists(Settings::TABLE_MACHINES) || !$DB->fieldExists(Settings::TABLE_MACHINES, 'gateway_mac')) {
            return $out;
        }
        foreach ($DB->request([
            'SELECT' => ['id', 'hostname', 'gateway_ip', 'gateway_mac'],
            'FROM'   => Settings::TABLE_MACHINES,
            'WHERE'  => ['NOT' => ['gateway_mac' => '']],
            'ORDER'  => 'hostname',
        ]) as $row) {
            $mac = self::normalizeMac((string) $row['gateway_mac']);
            if ($mac === '') {
                continue;
            }
            $out[$mac] ??= ['ip' => (string) $row['gateway_ip'], 'hosts' => []];
            $out[$mac]['hosts'][] = (string) $row['hostname'] !== '' ? (string) $row['hostname'] : '#' . $row['id'];
        }
        return $out;
    }

    /** "6c-3b-6b-11-22-a2" -> "6C:3B:6B:11:22:A2"; invalido/zerado/broadcast -> ''. */
    public static function normalizeMac(string $mac): string
    {
        $hex = strtoupper((string) preg_replace('/[^0-9A-Fa-f]/', '', $mac));
        if (strlen($hex) !== 12 || $hex === '000000000000' || $hex === 'FFFFFFFFFFFF') {
            return '';
        }
        return implode(':', str_split($hex, 2));
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
