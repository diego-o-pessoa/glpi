<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

final class MachineRepository
{
    public const MACHINES_TABLE   = 'glpi_plugin_ativaguardian_machines';
    public const COMPONENTS_TABLE = 'glpi_plugin_ativaguardian_components';

    /**
     * Record one heartbeat. Input is already validated by the API controller:
     * machine_id/hostname/guardian_version/antivirus/last_ip are strings and
     * components is a map of name => ['version' => string, 'status' => string]
     * with every name and status already checked against HealthStatus.
     *
     * Returns the machine row id, or 0 on failure.
     */
    public static function recordHeartbeat(array $data): int
    {
        global $DB;

        $now = ServerClock::now();
        $components = $data['components'] ?? [];
        $overall = HealthStatus::overall(
            array_map(static fn(array $c): string => (string) $c['status'], $components),
            false // it just contacted us, so it cannot be offline
        );

        $existing = $DB->request([
            'FROM'  => self::MACHINES_TABLE,
            'WHERE' => ['machine_id' => $data['machine_id']],
            'LIMIT' => 1,
        ]);

        $row = [
            'hostname'         => $data['hostname'],
            'username'         => $data['username'] ?? '',
            'guardian_version' => $data['guardian_version'],
            'antivirus'        => $data['antivirus'],
            'overall_status'   => $overall,
            'last_ip'          => $data['last_ip'],
            'last_contact'     => $now,
            'date_mod'         => $now,
        ];

        if (count($existing) === 1) {
            $machinesId = (int) $existing->current()['id'];
            if (!$DB->update(self::MACHINES_TABLE, $row, ['id' => $machinesId])) {
                return 0;
            }
        } else {
            $inserted = $DB->insert(self::MACHINES_TABLE, [
                'machine_id'    => $data['machine_id'],
                'first_contact' => $now,
                'date_creation' => $now,
            ] + $row);
            if (!$inserted) {
                return 0;
            }
            $machinesId = (int) $DB->insertId();
        }

        self::replaceComponents($machinesId, $components, $now);
        return $machinesId;
    }

    /**
     * A heartbeat carries the full component set, so the row set is replaced
     * wholesale: a component that stops being reported must not linger. The
     * count is tiny (a handful per machine), so delete-then-insert is fine.
     */
    private static function replaceComponents(int $machinesId, array $components, string $now): void
    {
        global $DB;

        $DB->delete(self::COMPONENTS_TABLE, ['machines_id' => $machinesId]);
        foreach ($components as $name => $component) {
            $DB->insert(self::COMPONENTS_TABLE, [
                'machines_id' => $machinesId,
                'component'   => (string) $name,
                'version'     => (string) ($component['version'] ?? ''),
                'status'      => (string) $component['status'],
                'date_mod'    => $now,
            ]);
        }
    }

    /**
     * Every machine with its components, ordered by most recent contact.
     * Each machine gets a computed `offline` flag and an `effective_status`
     * (offline wins over the stored overall) for the dashboard.
     *
     * @return array<int, array>
     */
    public static function loadAll(): array
    {
        global $DB;

        $offlineAfter = max(300, ConfigService::getInt('offline_after_seconds', 7200));
        $now = time();

        $machines = [];
        foreach ($DB->request([
            'FROM'  => self::MACHINES_TABLE,
            'ORDER' => ['last_contact DESC', 'id ASC'],
        ]) as $row) {
            $row['components'] = [];
            $machines[(int) $row['id']] = $row;
        }

        if ($machines !== []) {
            foreach ($DB->request([
                'FROM'  => self::COMPONENTS_TABLE,
                'WHERE' => ['machines_id' => array_keys($machines)],
            ]) as $component) {
                $machinesId = (int) $component['machines_id'];
                if (isset($machines[$machinesId])) {
                    $machines[$machinesId]['components'][(string) $component['component']] = [
                        'version' => (string) $component['version'],
                        'status'  => (string) $component['status'],
                    ];
                }
            }
        }

        foreach ($machines as &$machine) {
            $age = $now - ServerClock::toTimestamp((string) $machine['last_contact']);
            $machine['offline'] = $age > $offlineAfter;
            $machine['effective_status'] = $machine['offline']
                ? HealthStatus::OFFLINE
                : (string) $machine['overall_status'];
        }
        unset($machine);

        return array_values($machines);
    }

    /**
     * Dashboard counters: total, healthy, with a problem, offline.
     *
     * @param array<int, array> $machines
     * @return array{total:int, healthy:int, problem:int, offline:int}
     */
    public static function metrics(array $machines): array
    {
        $total = count($machines);
        $healthy = 0;
        $offline = 0;
        $problem = 0;
        foreach ($machines as $machine) {
            switch ($machine['effective_status']) {
                case HealthStatus::OFFLINE:
                    $offline++;
                    break;
                case HealthStatus::HEALTHY:
                    $healthy++;
                    break;
                case HealthStatus::UNKNOWN:
                    break;
                default:
                    $problem++;
            }
        }

        return [
            'total'   => $total,
            'healthy' => $healthy,
            'problem' => $problem,
            'offline' => $offline,
        ];
    }
}
