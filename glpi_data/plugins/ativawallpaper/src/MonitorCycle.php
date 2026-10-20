<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

/**
 * Verification cycle shown by the "Distribuicao e monitoramento" progress bar.
 *
 *   waiting   -> every computer was analyzed (green, 100%); "MM:SS para comecar"
 *                counts down the polling interval from the settings.
 *   analyzing -> yellow bar: "Analisando X de N computadores" while a computer checks
 *                in, then the time until the next one, until all of them reported.
 *
 * The clients keep their own schedule (they cannot be called by the server), so the
 * cycle follows what they report: a computer is analyzed when its heartbeat arrives
 * after the cycle started. Computers that stopped reporting stay out of the count.
 * "Aplicar novamente" restarts the cycle immediately, without the waiting time.
 *
 * A computer late beyond the grace time (shut down, lost the VPN in the middle
 * of the cycle) leaves THIS cycle and is reported as "nao respondeu a tempo".
 * The grace is short on purpose: it used to be the offline time (1 h by
 * default), so one computer turned off held the bar at "45 de 45" for an hour.
 */
final class MonitorCycle
{
    public const PHASE_WAITING = 'waiting';
    public const PHASE_ANALYZING = 'analyzing';
    private const TABLE = 'glpi_plugin_ativawallpaper_clients';

    /**
     * Pure state machine (no database).
     *
     * @param array{phase?:string,started_at?:int,completed_at?:int,total?:int} $state
     * @param list<array{hostname:string,last_check:int,next_check:int,cycle_at:int,applying:bool}> $clients
     *        Unix timestamps; 0 when unknown.
     * @param int $applyingGraceSeconds grace for a computer in the middle of applying (download); 0 = $graceSeconds
     * @return array{state:array{phase:string,started_at:int,completed_at:int,total:int,late:int}, view:array}
     */
    public static function evaluate(array $state, array $clients, int $now, int $waitSeconds, int $graceSeconds,
                                    int $applyingGraceSeconds = 0): array
    {
        $phase = (string) ($state['phase'] ?? '');
        $startedAt = (int) ($state['started_at'] ?? 0);
        $completedAt = (int) ($state['completed_at'] ?? 0);
        $total = (int) ($state['total'] ?? 0);
        $late = (int) ($state['late'] ?? 0);
        $applyingGraceSeconds = max($graceSeconds, $applyingGraceSeconds);

        $valid = ($phase === self::PHASE_ANALYZING && $startedAt > 0 && $startedAt <= $now)
            || ($phase === self::PHASE_WAITING && $completedAt > 0 && $completedAt <= $now);
        if (!$valid) {
            $phase = self::PHASE_ANALYZING;
            $startedAt = $now;
        }

        if ($phase === self::PHASE_WAITING && $now >= $completedAt + $waitSeconds) {
            $phase = self::PHASE_ANALYZING;
            $startedAt = $completedAt + $waitSeconds;
            if ($now - $startedAt > $waitSeconds + $graceSeconds) {
                // Nobody had the dashboard open for a while: count from now.
                $startedAt = $now;
            }
        }

        if ($phase === self::PHASE_WAITING) {
            return self::waiting($startedAt, $completedAt, $total, $waitSeconds, $late);
        }

        $late = 0;
        $participants = 0;
        $analyzed = 0;
        $lastAnalyzedAt = 0;
        $current = null;
        $next = null;
        foreach ($clients as $client) {
            $lastCheck = (int) $client['last_check'];
            $analyzedAt = (int) $client['cycle_at'] > 0 ? (int) $client['cycle_at'] : $lastCheck;
            $done = $analyzedAt > 0 && $analyzedAt >= $startedAt;
            $expected = (int) $client['next_check'] > 0
                ? (int) $client['next_check']
                : ($lastCheck > 0 ? $lastCheck + $waitSeconds : 0);
            $grace = $client['applying'] ? $applyingGraceSeconds : $graceSeconds;
            if (!$done && ($expected === 0 || $now > $expected + $grace)) {
                // Due in this cycle and did not answer: left out, but counted.
                if ($expected > 0 && $expected >= $startedAt - $grace) {
                    $late++;
                }
                continue; // Not reporting (turned off, no user session): outside the cycle.
            }
            $participants++;
            if ($done) {
                $analyzed++;
                $lastAnalyzedAt = max($lastAnalyzedAt, $analyzedAt);
                continue;
            }
            $candidate = [
                'hostname' => (string) $client['hostname'],
                'person'   => (string) ($client['person'] ?? ''),
                'at'       => $expected,
                'applying' => (bool) $client['applying'],
            ];
            $inProgress = $candidate['applying'] || ($lastCheck > 0 && $lastCheck >= $startedAt) || $expected <= $now;
            if ($inProgress) {
                if ($current === null || $expected < $current['at']) {
                    $current = $candidate;
                }
            } elseif ($next === null || $expected < $next['at']) {
                $next = $candidate;
            }
        }

        if ($participants > 0 && $analyzed === $participants) {
            // Closed when the last one answered or when the late one was given up.
            // Fechado por desistir de quem atrasou: a espera conta a partir de agora.
            $completedAt = $late > 0 ? $now : min($now, max($lastAnalyzedAt, $startedAt));
            return self::waiting($startedAt, $completedAt, $participants, $waitSeconds, $late);
        }

        return [
            'state' => [
                'phase'        => self::PHASE_ANALYZING,
                'started_at'   => $startedAt,
                'completed_at' => 0,
                'total'        => $participants,
                'late'         => $late,
            ],
            'view' => [
                'phase'           => self::PHASE_ANALYZING,
                'total'           => $participants,
                'analyzed'        => $analyzed,
                'late'            => $late,
                'percentage'      => $participants > 0 ? round($analyzed / $participants * 100, 1) : 0.0,
                'current'         => $current,
                'next'            => $current === null ? $next : null,
                'countdown_until' => $current === null && $next !== null ? $next['at'] : null,
                'wait_seconds'    => $waitSeconds,
            ],
        ];
    }

    /** Current cycle for the dashboard; moves the stored state forward when needed. */
    public static function current(): array
    {
        global $DB;

        $settings = ConfigService::all();
        $waitSeconds = max(60, (int) $settings['poll_interval_seconds']);
        // Time a computer may be late (jitter, network) before it leaves this cycle.
        // Not the offline time: that is 1 h by default and held the bar for an hour.
        $graceSeconds = DashboardService::presence()['grace'];
        // Downloading/applying a new wallpaper can take longer.
        $applyingGraceSeconds = max(300, $graceSeconds);

        $clients = [];
        $iterator = $DB->request([
            'SELECT' => ['hostname', 'username', 'computers_id', 'last_check', 'next_check_at', 'last_cycle_at', 'rollout_status'],
            'FROM'   => self::TABLE,
            'WHERE'  => ['revoked_at' => null],
        ]);
        foreach ($iterator as $row) {
            $clients[] = [
                'hostname'   => (string) $row['hostname'],
                'person'     => DashboardService::personName($row),
                'last_check' => ServerClock::toTimestamp((string) ($row['last_check'] ?? '')),
                'next_check' => ServerClock::toTimestamp((string) ($row['next_check_at'] ?? '')),
                'cycle_at'   => ServerClock::toTimestamp((string) ($row['last_cycle_at'] ?? '')),
                'applying'   => ($row['rollout_status'] ?? '') === 'applying',
            ];
        }

        $stored = [
            'phase'        => (string) $settings['monitor_phase'],
            'started_at'   => (int) $settings['monitor_started_at'],
            'completed_at' => (int) $settings['monitor_completed_at'],
            'total'        => (int) $settings['monitor_total'],
            'late'         => (int) $settings['monitor_late'],
        ];
        $result = self::evaluate($stored, $clients, time(), $waitSeconds, $graceSeconds, $applyingGraceSeconds);
        if ($result['state'] !== $stored) {
            ConfigService::set([
                'monitor_phase'        => $result['state']['phase'],
                'monitor_started_at'   => $result['state']['started_at'],
                'monitor_completed_at' => $result['state']['completed_at'],
                'monitor_total'        => $result['state']['total'],
                'monitor_late'         => $result['state']['late'],
            ]);
        }
        return $result['view'];
    }

    /** "Aplicar novamente": abandon the current cycle and analyze every computer now. */
    public static function restart(): void
    {
        ConfigService::set([
            'monitor_phase'        => self::PHASE_ANALYZING,
            'monitor_started_at'   => time(),
            'monitor_completed_at' => 0,
            'monitor_total'        => 0,
            'monitor_late'         => 0,
        ]);
    }

    private static function waiting(int $startedAt, int $completedAt, int $total, int $waitSeconds, int $late = 0): array
    {
        return [
            'state' => [
                'phase'        => self::PHASE_WAITING,
                'started_at'   => $startedAt,
                'completed_at' => $completedAt,
                'total'        => $total,
                'late'         => $late,
            ],
            'view' => [
                'phase'           => self::PHASE_WAITING,
                'total'           => $total,
                'analyzed'        => $total,
                'late'            => $late,
                'percentage'      => 100.0,
                'current'         => null,
                'next'            => null,
                'countdown_until' => $completedAt + $waitSeconds,
                'wait_seconds'    => $waitSeconds,
            ],
        ];
    }
}
