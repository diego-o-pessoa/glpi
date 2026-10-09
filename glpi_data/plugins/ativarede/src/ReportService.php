<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use InvalidArgumentException;

/**
 * Recebe o relatorio de posicao de uma maquina (enviado pelo Ativa Guardian)
 * e o compara com o que o GLPI ja sabia:
 *
 * - porta do switch diferente em N relatorios seguidos => computador mudou;
 * - monitor (numero de serie) visto em outra maquina   => monitor mudou;
 * - monitor nunca visto em maquina ja conhecida         => monitor novo;
 * - monitor que deixou de aparecer                      => marcado ausente
 *   (o alerta sai pela tarefa automatica, apos alguns dias).
 *
 * O primeiro relatorio de cada maquina so registra a situacao atual (nao ha
 * "de onde" para comparar). Wi-Fi ou cabo sem LLDP mantem a ultima posicao.
 */
final class ReportService
{
    public const MAX_MONITORS = 8;

    private const LINKS = ['wired', 'wifi', 'none', 'unknown'];

    /** Seriais que fabricantes gravam no lugar de um numero real. */
    private const GENERIC_SERIALS = [
        'to be filled by o.e.m.', 'default string', 'system serial number', 'none', 'n/a', 'na',
        'not specified', 'not applicable', 'chassis serial number', '0123456789', '1234567890',
        // Monitores que gravam o nome do campo no lugar do numero (visto em campo).
        'serialnumber', 'serial number', 'serial', 'sn', 's/n',
    ];

    /**
     * Valida o corpo inteiro antes de gravar qualquer coisa.
     *
     * @throws InvalidArgumentException com mensagem segura para devolver ao cliente
     */
    public static function validate(array $payload): array
    {
        $machineId = (string) ($payload['machine_id'] ?? '');
        if (!preg_match('/^[A-Za-z0-9._-]{1,128}$/D', $machineId)) {
            throw new InvalidArgumentException('machine_id invalido.');
        }
        // Nome como o Windows o tem (pode ter acento): recusar aqui deixava a
        // maquina fora do Ativa Rede para sempre.
        $hostname = self::text($payload['hostname'] ?? '', 255);
        $version = (string) ($payload['guardian_version'] ?? '');
        if ($version !== '' && !preg_match('/^[A-Za-z0-9.+_-]{1,64}$/D', $version)) {
            throw new InvalidArgumentException('guardian_version invalido.');
        }

        $network = $payload['network'] ?? [];
        if (!is_array($network)) {
            throw new InvalidArgumentException('network invalido.');
        }
        $link = (string) ($network['link'] ?? 'unknown');
        if (!in_array($link, self::LINKS, true)) {
            throw new InvalidArgumentException('network.link invalido.');
        }

        $lldp = null;
        if (isset($network['lldp']) && $network['lldp'] !== null) {
            if (!is_array($network['lldp'])) {
                throw new InvalidArgumentException('network.lldp invalido.');
            }
            $chassis = self::text($network['lldp']['chassis_id'] ?? '', 64);
            if ($chassis === '') {
                throw new InvalidArgumentException('network.lldp.chassis_id obrigatorio.');
            }
            $lldp = [
                'chassis_id'         => strtoupper($chassis),
                'port_id'            => self::text($network['lldp']['port_id'] ?? '', 128),
                'port_description'   => self::text($network['lldp']['port_description'] ?? '', 128),
                'system_name'        => self::text($network['lldp']['system_name'] ?? '', 255),
                'system_description' => self::text($network['lldp']['system_description'] ?? '', 255),
                'mgmt_ip'            => self::text($network['lldp']['mgmt_ip'] ?? '', 64),
                // Bits LLDP habilitados (Guardian 1.6.9+); -1 = nao informado.
                'capabilities'       => filter_var($network['lldp']['capabilities'] ?? -1, FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 0, 'max_range' => 0xFFFF, 'default' => -1]]),
            ];
        }

        $monitors = $payload['monitors'] ?? [];
        if (!is_array($monitors) || !array_is_list($monitors)) {
            throw new InvalidArgumentException('monitors invalido (lista de ate ' . self::MAX_MONITORS . ').');
        }
        // Relatorio parcial: a maquina nao conseguiu ler os monitores. Nao
        // significa "sem monitores" (nada e marcado como ausente).
        $monitorsKnown = ($payload['monitors_unknown'] ?? false) !== true;
        $cleanMonitors = [];
        foreach (array_slice($monitors, 0, self::MAX_MONITORS) as $monitor) {
            if (!is_array($monitor)) {
                continue;
            }
            if (self::isVirtualMonitor((string) ($monitor['manufacturer'] ?? ''))) {
                continue;
            }
            $cleanMonitors[] = [
                'manufacturer' => self::text($monitor['manufacturer'] ?? '', 32),
                'product_code' => self::text($monitor['product_code'] ?? '', 32),
                'serial'       => self::text($monitor['serial'] ?? '', 128),
                'model'        => self::text($monitor['model'] ?? '', 128),
                'year'         => self::boundedInt($monitor['year'] ?? 0, 0, 2100),
                'week'         => self::boundedInt($monitor['week'] ?? 0, 0, 53),
                'connection'   => self::text($monitor['connection'] ?? '', 32),
            ];
        }

        return [
            'machine_id'       => $machineId,
            'hostname'         => $hostname,
            'bios_serial'      => self::text($payload['bios_serial'] ?? '', 128),
            'guardian_version' => $version,
            'link'             => $link,
            'ip'               => self::text($network['ip'] ?? '', 64),
            'mac'              => strtoupper(self::text($network['mac'] ?? '', 32)),
            'lldp'             => $lldp,
            'monitors'         => $cleanMonitors,
            'monitors_known'   => $monitorsKnown,
            'diagnostic'       => self::diagnostic($payload['problems'] ?? []),
        ];
    }

    /** @return array{events: int} */
    public static function process(array $report): array
    {
        global $DB;

        $now = date('Y-m-d H:i:s');
        $events = 0;
        $machine = $DB->request([
            'FROM'  => Settings::TABLE_MACHINES,
            'WHERE' => ['machine_id' => $report['machine_id']],
            'LIMIT' => 1,
        ])->current();
        $isFirst = !$machine;

        $computersId = (int) ($machine['computers_id'] ?? 0);
        if ($computersId <= 0 || !self::computerExists($computersId)) {
            $computersId = self::matchComputer($report['bios_serial'], $report['hostname']);
        }

        $fields = [
            'computers_id'     => $computersId,
            'hostname'         => $report['hostname'],
            'bios_serial'      => $report['bios_serial'],
            'ip'               => $report['ip'],
            'mac'              => $report['mac'],
            'link'             => $report['link'],
            'guardian_version' => $report['guardian_version'],
            'last_report'      => $now,
        ];
        $hasSchema = $DB->fieldExists(Settings::TABLE_MACHINES, 'monitors_seen');
        if ($hasSchema) {
            $fields['diagnostic'] = $report['diagnostic'];
        }
        // Relatorio parcial nao traz a serie da BIOS: mantem a ja conhecida.
        if (!$report['monitors_known'] && $report['bios_serial'] === '') {
            unset($fields['bios_serial']);
        }
        // Primeira leitura dos monitores desta maquina: so registra (sem "novo").
        $monitorBaseline = $isFirst || ($hasSchema && (int) ($machine['monitors_seen'] ?? 1) === 0);
        if ($hasSchema && $report['monitors_known']) {
            $fields['monitors_seen'] = 1;
        }
        if ($isFirst) {
            $extra = ['machine_id' => $report['machine_id'], 'first_report' => $now];
            if ($hasSchema && !$report['monitors_known']) {
                $extra['monitors_seen'] = 0;
            }
            $DB->insert(Settings::TABLE_MACHINES, $fields + $extra);
            $machineDbId = (int) $DB->insertId();
            $machine = ['id' => $machineDbId, 'switches_id' => 0, 'port' => '', 'pending_switches_id' => 0, 'pending_port' => '', 'pending_count' => 0];
        } else {
            $machineDbId = (int) $machine['id'];
            $DB->update(Settings::TABLE_MACHINES, $fields, ['id' => $machineDbId]);
        }
        if ($machineDbId <= 0) {
            throw new \RuntimeException('Nao foi possivel registrar a maquina.');
        }

        // Situacao "sem relatorio" acaba assim que a maquina volta a enviar.
        Events::clear(Events::MACHINE_SILENT, ['machines_id' => $machineDbId]);

        // Guardian anterior ao 1.6.3 podia mandar o anuncio LLDP do proprio
        // Windows (chassis = MAC da maquina) como se fosse o switch.
        if ($report['lldp'] !== null && self::isOwnAnnouncement($report['lldp']['chassis_id'], $report['mac'])) {
            $report['lldp'] = null;
        }
        // Anuncio de OUTRO PC, repassado por um switchzinho de mesa (Guardian
        // anterior ao 1.6.9 nao filtrava): nao e a porta de switch nenhum.
        if ($report['lldp'] !== null && !self::isSwitchAnnouncement($report['lldp'])) {
            $report['lldp'] = null;
        }

        if ($report['lldp'] !== null && $report['link'] === 'wired') {
            $switchId = self::upsertSwitch($report['lldp'], $now);
            $port = self::portOf($report['lldp']);
            if ($switchId > 0 && $port !== '') {
                $events += self::handlePosition($machine, $switchId, $port, $report['lldp']['port_id'], $now);
            }
        }

        if ($report['monitors_known']) {
            $events += self::handleMonitors($machineDbId, $report['machine_id'], $report['monitors'], $monitorBaseline, $now);
        }
        return ['events' => $events];
    }

    /**
     * Switch/roteador pelas capacidades LLDP; sem elas, switch gerenciavel
     * sempre se descreve (nome, modelo ou IP de gerencia) e o LLDP do Windows
     * nao traz nada disso (visto em campo: "DESKTOP-1IRFUQ2" virou switch).
     */
    public static function isSwitchAnnouncement(array $lldp): bool
    {
        $caps = (int) ($lldp['capabilities'] ?? -1);
        if ($caps >= 0) {
            return ($caps & (0x0004 | 0x0010)) !== 0; // ponte ou roteador
        }
        return ($lldp['system_name'] ?? '') !== '' || ($lldp['system_description'] ?? '') !== ''
            || ($lldp['mgmt_ip'] ?? '') !== '';
    }

    public static function isOwnAnnouncement(string $chassisId, string $machineMac): bool
    {
        $chassis = preg_replace('/[^0-9A-F]/', '', strtoupper($chassisId)) ?? '';
        $mac = preg_replace('/[^0-9A-F]/', '', strtoupper($machineMac)) ?? '';
        return $mac !== '' && $chassis === $mac;
    }

    /**
     * Remove switches "falsos": anuncios do proprio Windows gravados por
     * Guardian antigo (chassis = MAC de uma maquina). Desfaz posicoes e
     * pendencias que apontavam para eles e fecha os alertas gerados.
     */
    public static function cleanupOwnAnnouncements(): int
    {
        global $DB;

        $macs = [];
        foreach ($DB->request(['SELECT' => ['mac'], 'FROM' => Settings::TABLE_MACHINES, 'WHERE' => ['mac' => ['<>', '']]]) as $row) {
            $macs[preg_replace('/[^0-9A-F]/', '', strtoupper((string) $row['mac']))] = true;
        }
        $removed = 0;
        foreach ($DB->request(['SELECT' => ['id', 'chassis_id'], 'FROM' => Settings::TABLE_SWITCHES]) as $switch) {
            $chassis = preg_replace('/[^0-9A-F]/', '', strtoupper((string) $switch['chassis_id']));
            if (!isset($macs[$chassis])) {
                continue;
            }
            $id = (int) $switch['id'];
            $DB->update(Settings::TABLE_MACHINES, ['pending_switches_id' => 0, 'pending_port' => '', 'pending_count' => 0], ['pending_switches_id' => $id]);
            $DB->update(Settings::TABLE_MACHINES, ['switches_id' => 0, 'port' => '', 'port_id' => '', 'since' => null], ['switches_id' => $id]);
            $DB->update(Settings::TABLE_DESKS, ['switches_id' => 0, 'port' => ''], ['switches_id' => $id]);
            $DB->update(
                Settings::TABLE_EVENTS,
                ['status' => Events::CLEARED, 'resolved_at' => date('Y-m-d H:i:s'), 'details' => 'Anúncio do próprio computador (não era o switch).'],
                ['status' => Events::OPEN, 'OR' => ['from_switches_id' => $id, 'to_switches_id' => $id]]
            );
            $DB->delete(Settings::TABLE_SWITCHES, ['id' => $id]);
            $removed++;
        }
        return $removed;
    }

    /**
     * Tela virtual do Windows ("MS_0001": acesso remoto, projecao, adaptador
     * basico de video): nao e um monitor fisico e nao muda de mesa.
     */
    public static function isVirtualMonitor(string $manufacturer): bool
    {
        return strtoupper(trim($manufacturer)) === 'MS_';
    }

    /** Remove telas virtuais gravadas antes do filtro, fechando os alertas delas. */
    public static function cleanupVirtualMonitors(): int
    {
        global $DB;

        $removed = 0;
        foreach ($DB->request(['SELECT' => ['id', 'manufacturer'], 'FROM' => Settings::TABLE_MONITORS]) as $row) {
            if (!self::isVirtualMonitor((string) $row['manufacturer'])) {
                continue;
            }
            $DB->update(
                Settings::TABLE_EVENTS,
                ['status' => Events::CLEARED, 'resolved_at' => date('Y-m-d H:i:s')],
                ['status' => Events::OPEN, 'monitors_id' => (int) $row['id']]
            );
            $DB->delete(Settings::TABLE_MONITORS, ['id' => (int) $row['id']]);
            $removed++;
        }
        return $removed;
    }

    /**
     * Monitores gravados com serie generica antes do filtro (ex.: "SerialNumber"):
     * apagados, com os alertas deles fechados. O proximo relatorio da maquina
     * os registra de novo, sem serie (reconhecidos so dentro da maquina).
     */
    public static function cleanupGenericSerials(): int
    {
        global $DB;

        $removed = 0;
        foreach ($DB->request(['SELECT' => ['id', 'serial'], 'FROM' => Settings::TABLE_MONITORS, 'WHERE' => ['has_serial' => 1]]) as $row) {
            if (self::realSerial((string) $row['serial']) !== '') {
                continue;
            }
            $DB->update(
                Settings::TABLE_EVENTS,
                ['status' => Events::CLEARED, 'resolved_at' => date('Y-m-d H:i:s')],
                ['status' => Events::OPEN, 'monitors_id' => (int) $row['id']]
            );
            $DB->delete(Settings::TABLE_MONITORS, ['id' => (int) $row['id']]);
            $removed++;
        }
        return $removed;
    }

    /** Porta = descricao (o numero que aparece no switch); senao o Port ID. */
    public static function portOf(array $lldp): string
    {
        $port = trim($lldp['port_description'] !== '' ? $lldp['port_description'] : $lldp['port_id']);
        return mb_substr($port, 0, 64);
    }

    private static function handlePosition(array $machine, int $switchId, string $port, string $portId, string $now): int
    {
        global $DB;

        $machineDbId = (int) $machine['id'];
        $currentSwitch = (int) $machine['switches_id'];
        $currentPort = (string) $machine['port'];
        $events = 0;

        if ($currentSwitch === 0 || $currentPort === '') {
            // Primeira posicao conhecida: so registra.
            $DB->update(Settings::TABLE_MACHINES, [
                'switches_id' => $switchId, 'port' => $port, 'port_id' => $portId, 'since' => $now,
                'pending_switches_id' => 0, 'pending_port' => '', 'pending_count' => 0,
            ], ['id' => $machineDbId]);
            $events += self::promoteFixedDesk($machineDbId, $switchId, $port);
        } elseif ($currentSwitch === $switchId && $currentPort === $port) {
            if ((int) $machine['pending_count'] > 0) {
                $DB->update(Settings::TABLE_MACHINES, [
                    'pending_switches_id' => 0, 'pending_port' => '', 'pending_count' => 0, 'port_id' => $portId,
                ], ['id' => $machineDbId]);
            }
        } else {
            $count = ((int) $machine['pending_switches_id'] === $switchId && (string) $machine['pending_port'] === $port)
                ? (int) $machine['pending_count'] + 1
                : 1;
            if ($count >= Settings::int('confirm_reports')) {
                $desk = Inventory::deskAt($switchId, $port);
                Events::record(Events::COMPUTER_MOVED, [
                    'machines_id'      => $machineDbId,
                    'desks_id'         => (int) ($desk['id'] ?? 0),
                    'from_switches_id' => $currentSwitch,
                    'from_port'        => $currentPort,
                    'to_switches_id'   => $switchId,
                    'to_port'          => $port,
                ]);
                $events++;
                $DB->update(Settings::TABLE_MACHINES, [
                    'switches_id' => $switchId, 'port' => $port, 'port_id' => $portId, 'since' => $now,
                    'pending_switches_id' => 0, 'pending_port' => '', 'pending_count' => 0,
                ], ['id' => $machineDbId]);
                self::refreshSharedPort($currentSwitch, $currentPort);
                $events += self::promoteFixedDesk($machineDbId, $switchId, $port);
            } else {
                $DB->update(Settings::TABLE_MACHINES, [
                    'pending_switches_id' => $switchId, 'pending_port' => $port, 'pending_count' => $count,
                ], ['id' => $machineDbId]);
                return 0;
            }
        }

        // A maquina esta na porta: a mesa daquela porta deixou de estar vazia.
        $desk = Inventory::deskAt($switchId, $port);
        if ($desk) {
            Events::clear(Events::DESK_EMPTY, ['desks_id' => (int) $desk['id']]);
        }
        $events += self::refreshSharedPort($switchId, $port);
        return $events;
    }

    /**
     * Maquina que estava vinculada direto a uma mesa (Wi-Fi) apareceu no cabo:
     * - porta sem mesa: a porta passa a ser da mesa dela (automatico);
     * - porta de outra mesa: levou a maquina para la -> "Computador mudou de
     *   mesa" e o vinculo antigo e desfeito (a porta passa a identificar).
     *
     * @return int alertas criados
     */
    private static function promoteFixedDesk(int $machineDbId, int $switchId, string $port): int
    {
        global $DB;

        $fixed = $DB->request([
            'FROM'  => Settings::TABLE_DESKS,
            'WHERE' => ['machines_id' => $machineDbId],
            'LIMIT' => 1,
        ])->current();
        if (!$fixed) {
            return 0;
        }

        $owner = Inventory::deskAt($switchId, $port);
        $now = date('Y-m-d H:i:s');
        if (!$owner) {
            $DB->update(Settings::TABLE_DESKS, [
                'switches_id' => $switchId, 'port' => $port, 'machines_id' => 0, 'date_mod' => $now,
            ], ['id' => (int) $fixed['id']]);
            Inventory::resetCache();
            return 0;
        }
        $DB->update(Settings::TABLE_DESKS, ['machines_id' => 0, 'date_mod' => $now], ['id' => (int) $fixed['id']]);
        if ((int) $owner['id'] === (int) $fixed['id']) {
            return 0;
        }
        Events::record(Events::COMPUTER_MOVED, [
            'machines_id'    => $machineDbId,
            'desks_id'       => (int) $owner['id'],
            'to_switches_id' => $switchId,
            'to_port'        => $port,
            'details'        => 'mesa ' . $fixed['name'] . ' (Wi-Fi, sem porta)',
        ]);
        return 1;
    }

    /** Mais de uma maquina ativa na mesma porta = mini switch (ou cabo trocado). */
    private static function refreshSharedPort(int $switchId, string $port): int
    {
        if ($switchId <= 0 || $port === '') {
            return 0;
        }
        $recent = date('Y-m-d H:i:s', time() - DAY_TIMESTAMP);
        $count = countElementsInTable(Settings::TABLE_MACHINES, [
            'switches_id' => $switchId,
            'port'        => $port,
            'last_report' => ['>=', $recent],
        ]);
        if ($count > 1) {
            $before = countElementsInTable(Settings::TABLE_EVENTS, [
                'type' => Events::SHARED_PORT, 'status' => Events::OPEN, 'to_switches_id' => $switchId, 'to_port' => $port,
            ]);
            Events::record(Events::SHARED_PORT, [
                'to_switches_id' => $switchId,
                'to_port'        => $port,
                'details'        => $count . ' máquinas na mesma porta',
            ]);
            return $before > 0 ? 0 : 1;
        }
        Events::clear(Events::SHARED_PORT, ['to_switches_id' => $switchId, 'to_port' => $port]);
        return 0;
    }

    private static function handleMonitors(int $machineDbId, string $machineUid, array $monitors, bool $isFirst, string $now): int
    {
        global $DB;

        $events = 0;
        $seen = [];
        foreach ($monitors as $index => $monitor) {
            $serial = self::realSerial($monitor['serial']);
            $hasSerial = $serial !== '';
            // Sem serie, o monitor so pode ser reconhecido dentro da propria
            // maquina: nunca gera "mudou de mesa" por engano.
            $fingerprint = $hasSerial
                ? sha1('edid|' . strtoupper($monitor['manufacturer']) . '|' . strtoupper($monitor['product_code']) . '|' . strtoupper($serial))
                : sha1('noserial|' . $machineUid . '|' . strtoupper($monitor['manufacturer']) . '|' . strtoupper($monitor['product_code']) . '|' . $index);

            $info = [
                'manufacturer' => $monitor['manufacturer'],
                'product_code' => $monitor['product_code'],
                'model'        => $monitor['model'],
                'serial'       => $serial,
                'has_serial'   => $hasSerial ? 1 : 0,
                'year'         => $monitor['year'],
                'week'         => $monitor['week'],
                'connection'   => $monitor['connection'],
                'last_seen'    => $now,
                'missing_since' => null,
            ];

            $row = $DB->request(['FROM' => Settings::TABLE_MONITORS, 'WHERE' => ['fingerprint' => $fingerprint], 'LIMIT' => 1])->current();
            if (!$row) {
                $DB->insert(Settings::TABLE_MONITORS, $info + [
                    'fingerprint' => $fingerprint,
                    'machines_id' => $machineDbId,
                    'since'       => $now,
                    'first_seen'  => $now,
                ]);
                $monitorId = (int) $DB->insertId();
                if (!$isFirst && $hasSerial) {
                    Events::record(Events::MONITOR_NEW, ['monitors_id' => $monitorId, 'to_machines_id' => $machineDbId, 'machines_id' => $machineDbId]);
                    $events++;
                }
            } else {
                $monitorId = (int) $row['id'];
                $previous = (int) $row['machines_id'];
                if ($previous !== $machineDbId) {
                    $info['machines_id'] = $machineDbId;
                    $info['since'] = $now;
                    if ($previous > 0) {
                        // De onde -> para onde, gravado agora (a posicao ja foi
                        // atualizada acima): continua certo mesmo que as
                        // maquinas mudem de mesa depois.
                        Inventory::resetCache();
                        Events::record(Events::MONITOR_MOVED, [
                            'monitors_id'      => $monitorId,
                            'machines_id'      => $machineDbId,
                            'from_machines_id' => $previous,
                            'to_machines_id'   => $machineDbId,
                            'details'          => 'Estava em ' . Inventory::machineWhere(Inventory::machine($previous))
                                . '; foi para ' . Inventory::machineWhere(Inventory::machine($machineDbId)) . '.',
                        ]);
                        $events++;
                    }
                }
                $DB->update(Settings::TABLE_MONITORS, $info, ['id' => $monitorId]);
                Events::clear(Events::MONITOR_MISSING, ['monitors_id' => $monitorId]);
            }
            $seen[] = $monitorId;
        }

        // Os que estavam nesta maquina e nao vieram agora: ausentes desde ja.
        $where = ['machines_id' => $machineDbId, 'missing_since' => null];
        if ($seen !== []) {
            $where['NOT'] = ['id' => $seen];
        }
        $DB->update(Settings::TABLE_MONITORS, ['missing_since' => $now], $where);
        return $events;
    }

    private static function upsertSwitch(array $lldp, string $now): int
    {
        global $DB;

        $fields = [
            'system_name' => $lldp['system_name'],
            'description' => $lldp['system_description'],
            'mgmt_ip'     => $lldp['mgmt_ip'],
            'last_seen'   => $now,
        ];
        $row = $DB->request(['FROM' => Settings::TABLE_SWITCHES, 'WHERE' => ['chassis_id' => $lldp['chassis_id']], 'LIMIT' => 1])->current();
        if ($row) {
            $DB->update(Settings::TABLE_SWITCHES, $fields, ['id' => (int) $row['id']]);
            // Marcado como "nao e switch": nao define posicao (fica a anterior).
            return (int) ($row['ignored'] ?? 0) === 1 ? 0 : (int) $row['id'];
        }
        $DB->insert(Settings::TABLE_SWITCHES, $fields + ['chassis_id' => $lldp['chassis_id'], 'first_seen' => $now]);
        return (int) $DB->insertId();
    }

    /**
     * Computador do GLPI desta maquina: pelo numero de serie da BIOS (vindo
     * do inventario do GLPI Agent) e, na falta, pelo nome. So vincula quando
     * ha exatamente um candidato.
     */
    private static function matchComputer(string $biosSerial, string $hostname): int
    {
        global $DB;

        $base = ['is_deleted' => 0, 'is_template' => 0];
        $serial = self::realSerial($biosSerial);
        if ($serial !== '') {
            $rows = iterator_to_array($DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_computers', 'WHERE' => $base + ['serial' => $serial], 'LIMIT' => 2]));
            if (count($rows) === 1) {
                return (int) reset($rows)['id'];
            }
        }
        if ($hostname !== '') {
            $rows = iterator_to_array($DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_computers', 'WHERE' => $base + ['name' => $hostname], 'LIMIT' => 2]));
            if (count($rows) === 1) {
                return (int) reset($rows)['id'];
            }
        }
        return 0;
    }

    private static function computerExists(int $id): bool
    {
        return countElementsInTable('glpi_computers', ['id' => $id, 'is_deleted' => 0]) > 0;
    }

    /** Serie util para identificar; vazio quando e generica ("0000", "Default string"...). */
    public static function realSerial(string $serial): string
    {
        $serial = trim($serial);
        if (strlen($serial) < 3 || preg_match('/^(.)\1+$/D', $serial) || in_array(strtolower($serial), self::GENERIC_SERIALS, true)) {
            return '';
        }
        return $serial;
    }

    /** Problemas da coleta informados pela maquina, numa linha. */
    private static function diagnostic(mixed $problems): string
    {
        if (!is_array($problems)) {
            return '';
        }
        $lines = [];
        foreach (array_slice($problems, 0, 5) as $problem) {
            $line = self::text($problem, 200);
            if ($line !== '') {
                $lines[] = $line;
            }
        }
        return mb_substr(implode(' | ', $lines), 0, 500);
    }

    private static function text(mixed $value, int $max): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string) $value) ?? '';
        return mb_substr(trim($value), 0, $max);
    }

    private static function boundedInt(mixed $value, int $min, int $max): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        return $int === false ? 0 : max($min, min($max, (int) $int));
    }
}
