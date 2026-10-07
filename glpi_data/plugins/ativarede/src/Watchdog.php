<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use CronTask;

/**
 * Tarefa automatica (a cada hora): transforma em alerta o que so se percebe
 * com o tempo - monitor que nao voltou, mesa que ficou vazia depois que o
 * computador saiu, maquina que parou de enviar relatorio.
 */
final class Watchdog
{
    public static function cronInfo(string $name): array
    {
        return $name === 'Watchdog'
            ? ['description' => 'Ativa Rede: monitores ausentes, mesas vazias e máquinas sem relatório']
            : [];
    }

    /** 1 = gerou alerta, 0 = nada novo. */
    public static function cronWatchdog(CronTask $task): int
    {
        $created = self::monitorsMissing() + self::desksEmpty() + self::machinesSilent();
        $task->addVolume($created);
        return $created > 0 ? 1 : 0;
    }

    private static function monitorsMissing(): int
    {
        global $DB;

        $limit = date('Y-m-d H:i:s', time() - Settings::int('monitor_missing_days') * DAY_TIMESTAMP);
        $created = 0;
        foreach ($DB->request([
            'SELECT'     => [Settings::TABLE_MONITORS . '.id', Settings::TABLE_MONITORS . '.machines_id', Settings::TABLE_MONITORS . '.missing_since'],
            'FROM'       => Settings::TABLE_MONITORS,
            'INNER JOIN' => [
                Settings::TABLE_MACHINES => ['ON' => [Settings::TABLE_MONITORS => 'machines_id', Settings::TABLE_MACHINES => 'id']],
            ],
            'WHERE'      => [
                Settings::TABLE_MONITORS . '.missing_since' => ['<=', $limit],
                // A maquina continuou enviando sem o monitor (nao e so ela desligada).
                new \Glpi\DBAL\QueryExpression(
                    $DB->quoteName(Settings::TABLE_MACHINES . '.last_report') . ' > ' . $DB->quoteName(Settings::TABLE_MONITORS . '.missing_since')
                ),
            ],
        ]) as $row) {
            if (self::isOpen(Events::MONITOR_MISSING, ['monitors_id' => (int) $row['id']])) {
                continue;
            }
            Events::record(Events::MONITOR_MISSING, [
                'monitors_id'      => (int) $row['id'],
                'machines_id'      => (int) $row['machines_id'],
                'from_machines_id' => (int) $row['machines_id'],
                'details'          => 'desde ' . Inventory::date($row['missing_since']),
            ]);
            $created++;
        }
        return $created;
    }

    /** Mesa de onde um computador saiu e que segue sem maquina depois de N dias. */
    private static function desksEmpty(): int
    {
        global $DB;

        $days = Settings::int('desk_empty_days');
        $limit = date('Y-m-d H:i:s', time() - $days * DAY_TIMESTAMP);
        $window = date('Y-m-d H:i:s', time() - 90 * DAY_TIMESTAMP);
        $created = 0;

        foreach ($DB->request(['FROM' => Settings::TABLE_DESKS, 'WHERE' => ['switches_id' => ['>', 0], 'port' => ['<>', '']]]) as $desk) {
            $occupied = countElementsInTable(Settings::TABLE_MACHINES, [
                'switches_id' => (int) $desk['switches_id'],
                'port'        => (string) $desk['port'],
            ]);
            if ($occupied > 0) {
                continue;
            }
            $left = $DB->request([
                'SELECT' => ['date_creation'],
                'FROM'   => Settings::TABLE_EVENTS,
                'WHERE'  => [
                    'type'             => Events::COMPUTER_MOVED,
                    'from_switches_id' => (int) $desk['switches_id'],
                    'from_port'        => (string) $desk['port'],
                    'date_creation'    => ['>=', $window],
                ],
                'ORDER'  => 'id DESC',
                'LIMIT'  => 1,
            ])->current();
            if (!$left || (string) $left['date_creation'] > $limit) {
                continue;
            }
            if (self::isOpen(Events::DESK_EMPTY, ['desks_id' => (int) $desk['id']])) {
                continue;
            }
            Events::record(Events::DESK_EMPTY, [
                'desks_id' => (int) $desk['id'],
                'details'  => 'desde ' . Inventory::date($left['date_creation']),
            ]);
            $created++;
        }
        return $created;
    }

    private static function machinesSilent(): int
    {
        global $DB;

        $limit = date('Y-m-d H:i:s', time() - Settings::int('machine_silent_days') * DAY_TIMESTAMP);
        $created = 0;
        foreach ($DB->request([
            'SELECT' => ['id'],
            'FROM'   => Settings::TABLE_MACHINES,
            'WHERE'  => ['last_report' => ['<', $limit]],
        ]) as $row) {
            if (self::isOpen(Events::MACHINE_SILENT, ['machines_id' => (int) $row['id']])) {
                continue;
            }
            Events::record(Events::MACHINE_SILENT, ['machines_id' => (int) $row['id']]);
            $created++;
        }
        return $created;
    }

    private static function isOpen(string $type, array $where): bool
    {
        return countElementsInTable(Settings::TABLE_EVENTS, ['type' => $type, 'status' => Events::OPEN] + $where) > 0;
    }
}
