<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use Glpi\DBAL\QueryExpression;

final class DashboardService
{
    private const TABLE = 'glpi_plugin_ativawallpaper_clients';

    public function summary(?array $current): array
    {
        global $DB;
        $offline = max(120, ConfigService::getInt('offline_after_seconds'));
        $currentVersion = $DB->quoteValue((string) ($current['version'] ?? ''));
        // Compared with a server-clock string, not NOW(): the database session timezone
        // follows the GLPI user and differs from the clock used to store the dates.
        $threshold = $DB->quoteValue(ServerClock::format(time() - $offline));
        $online = "last_check IS NOT NULL AND last_check >= {$threshold}";
        $offlineSql = "last_check IS NULL OR last_check < {$threshold}";

        $iterator = $DB->request([
            'SELECT' => [
                new QueryExpression('COUNT(*) AS managed'),
                new QueryExpression("SUM(CASE WHEN {$online} AND status = 'success' AND wallpaper_version = {$currentVersion} THEN 1 ELSE 0 END) AS updated"),
                new QueryExpression("SUM(CASE WHEN {$online} AND status = 'error' THEN 1 ELSE 0 END) AS errors"),
                new QueryExpression("SUM(CASE WHEN {$offlineSql} THEN 1 ELSE 0 END) AS offline"),
                new QueryExpression("SUM(CASE WHEN {$online} AND status <> 'error' AND (status <> 'success' OR wallpaper_version IS NULL OR wallpaper_version <> {$currentVersion}) THEN 1 ELSE 0 END) AS pending"),
            ],
            'FROM'  => self::TABLE,
            'WHERE' => ['revoked_at' => null],
        ]);
        $row = $iterator->current();
        $summary = is_array($row) ? $row : [];
        foreach (['managed', 'updated', 'pending', 'offline', 'errors'] as $key) {
            $summary[$key] = (int) ($summary[$key] ?? 0);
        }
        $summary['percentage'] = $summary['managed'] > 0
            ? round(($summary['updated'] / $summary['managed']) * 100, 1)
            : 0.0;
        return $summary;
    }

    /** @return array{rows:array,total:int,page:int,pages:int,limit:int} */
    public function clients(array $filters, ?array $current, int $page, int $limit): array
    {
        global $DB;
        $page = max(1, $page);
        $limit = max(10, min(100, $limit));
        $status = (string) ($filters['status'] ?? 'all');
        // "Todos" also lists revoked computers: hiding them made a revocation look
        // like computers that silently stopped reporting.
        $where = match ($status) {
            'all'     => [],
            'revoked' => ['NOT' => ['revoked_at' => null]],
            default   => ['revoked_at' => null],
        };

        $query = Security::cleanText($filters['q'] ?? '', 255);
        if ($query !== '') {
            $where['OR'] = [
                'hostname'          => ['LIKE', '%' . $query . '%'],
                'username'          => ['LIKE', '%' . $query . '%'],
                'wallpaper_version' => ['LIKE', '%' . $query . '%'],
                'client_version'    => ['LIKE', '%' . $query . '%'],
            ];
        }

        $statusExpression = $this->statusExpression($status, $current);
        if ($statusExpression !== null) {
            $where[] = new QueryExpression($statusExpression);
        }

        $countIterator = $DB->request([
            'SELECT' => [new QueryExpression('COUNT(*) AS total')],
            'FROM'   => self::TABLE,
            'WHERE'  => $where,
        ]);
        $countRow = $countIterator->current();
        $total = is_array($countRow) ? (int) $countRow['total'] : 0;
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);

        $allowedSort = ['hostname', 'username', 'client_version', 'wallpaper_version', 'last_check', 'last_apply', 'status'];
        $sort = in_array($filters['sort'] ?? '', $allowedSort, true) ? $filters['sort'] : 'last_check';
        $direction = strtolower((string) ($filters['direction'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $rows = [];
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => $where,
            // Active computers first (NULL sorts first in MySQL).
            'ORDER' => ['revoked_at ASC', sprintf('%s %s', $sort, $direction), 'id DESC'],
            'START' => ($page - 1) * $limit,
            'LIMIT' => $limit,
        ]);
        foreach ($iterator as $row) {
            $row['computed_status'] = $this->computeStatus($row, $current);
            $rows[] = $row;
        }
        $updater = $this->updaterContacts(array_column($rows, 'machine_guid'));
        foreach ($rows as &$row) {
            $row['updater'] = $updater[strtolower((string) $row['machine_guid'])] ?? null;
        }
        unset($row);

        return compact('rows', 'total', 'page', 'pages', 'limit');
    }

    public function revokedCount(): int
    {
        return (int) countElementsInTable(self::TABLE, ['NOT' => ['revoked_at' => null]]);
    }

    /**
     * Last contact of the same computers in the Ativa Updater (SYSTEM service), keyed by
     * lowercase MachineGuid. Lets the dashboard tell "computer online, wallpaper client
     * not reporting" apart from "computer off".
     *
     * @return array<string, array{last_check:string, age_seconds:int|null, online:bool}>
     */
    public function updaterContacts(array $machineGuids): array
    {
        global $DB;
        $guids = array_values(array_unique(array_filter(array_map(
            static fn($guid): string => strtolower(trim((string) $guid)),
            $machineGuids
        ))));
        if ($guids === [] || !$DB->tableExists('glpi_plugin_ativaupdater_clients')) {
            return [];
        }
        // Ativa Updater stores its dates with the same php.ini-timezone clock.
        $contacts = [];
        $iterator = $DB->request([
            'SELECT' => ['machine_guid', 'last_check'],
            'FROM'   => 'glpi_plugin_ativaupdater_clients',
            'WHERE'  => ['machine_guid' => $guids],
        ]);
        foreach ($iterator as $row) {
            $timestamp = ServerClock::toTimestamp((string) $row['last_check']);
            $age = $timestamp > 0 ? max(0, time() - $timestamp) : null;
            $contacts[strtolower((string) $row['machine_guid'])] = [
                'last_check'  => (string) $row['last_check'],
                'age_seconds' => $age,
                // Same rule as the Ativa Updater dashboard: offline after 2 h without contact.
                'online'      => $age !== null && $age < 7200,
            ];
        }
        return $contacts;
    }

    public function computeStatus(array $client, ?array $current): string
    {
        if (!empty($client['revoked_at'])) {
            return 'revoked';
        }
        $lastCheck = ServerClock::toTimestamp((string) ($client['last_check'] ?? ''));
        if ($lastCheck === 0 || $lastCheck < time() - max(120, ConfigService::getInt('offline_after_seconds'))) {
            return 'offline';
        }
        if (($client['status'] ?? '') === 'error') {
            return 'error';
        }
        if ($current !== null && ($client['status'] ?? '') === 'success'
            && ($client['wallpaper_version'] ?? '') === $current['version']) {
            return 'updated';
        }
        return 'pending';
    }

    public function clientVersionCounts(): array
    {
        global $DB;
        $rows = [];
        $iterator = $DB->request([
            'SELECT' => [
                'client_version',
                new QueryExpression('COUNT(*) AS total'),
            ],
            'FROM'    => self::TABLE,
            'WHERE'   => ['revoked_at' => null],
            'GROUPBY' => ['client_version'],
            'ORDER'   => ['total DESC', 'client_version DESC'],
        ]);
        foreach ($iterator as $row) {
            $rows[] = ['version' => (string) $row['client_version'], 'total' => (int) $row['total']];
        }
        return $rows;
    }

    public function rolloutProgress(): ?array
    {
        global $DB;

        $rolloutId = ConfigService::get('active_rollout_id');
        if ($rolloutId === '') {
            return null;
        }

        $counts = [
            'pending'  => 0,
            'applying' => 0,
            'success'  => 0,
            'error'    => 0,
        ];
        $machines = [];
        $now = time();
        $offlineAfter = max(120, ConfigService::getInt('offline_after_seconds'));
        $defaultCycleSeconds = max(5, ConfigService::getInt('poll_interval_seconds') + ConfigService::getInt('poll_jitter_seconds'));
        $iterator = $DB->request([
            'SELECT' => [
                'hostname', 'rollout_status', 'last_error', 'last_check',
                'next_check_at', 'check_interval_seconds', 'last_cycle_action', 'last_cycle_at',
            ],
            'FROM'   => self::TABLE,
            'WHERE'  => ['rollout_id' => $rolloutId, 'revoked_at' => null],
            'ORDER'  => ['hostname ASC'],
        ]);
        foreach ($iterator as $row) {
            $status = (string) ($row['rollout_status'] ?? 'pending');
            if (!array_key_exists($status, $counts)) {
                $status = 'pending';
            }
            $counts[$status]++;
            $cycleSeconds = max(5, (int) ($row['check_interval_seconds'] ?? $defaultCycleSeconds));
            $nextCheckTimestamp = ServerClock::toTimestamp((string) ($row['next_check_at'] ?? '')) ?: false;
            $lastCheckTimestamp = ServerClock::toTimestamp((string) ($row['last_check'] ?? '')) ?: false;
            if ($nextCheckTimestamp === false && $lastCheckTimestamp !== false) {
                $nextCheckTimestamp = $lastCheckTimestamp + $cycleSeconds;
            }
            $machines[] = [
                'hostname'   => (string) $row['hostname'],
                'status'     => $status,
                'last_error' => $status === 'error' ? (string) ($row['last_error'] ?? '') : '',
                'last_check' => (string) ($row['last_check'] ?? ''),
                'next_check_at' => $nextCheckTimestamp === false ? '' : ServerClock::format($nextCheckTimestamp),
                'seconds_until_check' => $nextCheckTimestamp === false ? null : max(0, $nextCheckTimestamp - $now),
                'cycle_seconds' => $cycleSeconds,
                'online' => $lastCheckTimestamp !== false && $lastCheckTimestamp >= $now - $offlineAfter,
                'last_cycle_action' => (string) ($row['last_cycle_action'] ?? ''),
                'last_cycle_at' => (string) ($row['last_cycle_at'] ?? ''),
            ];
        }

        $total = array_sum($counts);
        if ($total === 0) {
            return null;
        }
        $processed = $counts['success'] + $counts['error'];
        $remaining = $counts['pending'] + $counts['applying'];
        usort($machines, static function (array $left, array $right): int {
            $priority = ['applying' => 0, 'pending' => 1, 'error' => 2, 'success' => 3];
            return ($priority[$left['status']] <=> $priority[$right['status']])
                ?: strcasecmp($left['hostname'], $right['hostname']);
        });

        $countdownCandidates = array_values(array_filter(
            $machines,
            static fn(array $machine): bool => $machine['seconds_until_check'] !== null
                && $machine['online']
                && ($remaining === 0 || $machine['status'] === 'pending')
        ));
        usort($countdownCandidates, static fn(array $left, array $right): int =>
            ($left['seconds_until_check'] <=> $right['seconds_until_check'])
                ?: strcasecmp($left['hostname'], $right['hostname'])
        );
        $nextMachine = $countdownCandidates[0] ?? null;

        $latestCycle = null;
        foreach ($machines as $machine) {
            if ($machine['last_cycle_at'] === '') {
                continue;
            }
            if ($latestCycle === null || strcmp($machine['last_cycle_at'], $latestCycle['last_cycle_at']) > 0) {
                $latestCycle = $machine;
            }
        }
        $recentApplication = false;
        if ($latestCycle !== null) {
            $latestTimestamp = ServerClock::toTimestamp($latestCycle['last_cycle_at']);
            $recentApplication = $latestTimestamp > 0
                && $latestTimestamp >= $now - 5
                && in_array($latestCycle['last_cycle_action'], [
                    'initial_applied', 'configuration_applied', 'forced_applied', 'drift_corrected',
                ], true);
        }

        $phase = $counts['applying'] > 0
            ? 'applying'
            : ($remaining > 0 ? 'waiting' : ($recentApplication ? 'cycle_complete' : 'monitoring'));
        $countdownSeconds = $nextMachine === null ? null : (int) $nextMachine['seconds_until_check'];
        $countdownTotal = $nextMachine === null ? $defaultCycleSeconds : max(5, (int) $nextMachine['cycle_seconds']);
        $countdownPercentage = $countdownSeconds === null
            ? 0.0
            : round(min(100, ($countdownSeconds / $countdownTotal) * 100), 1);

        return [
            'id'                => $rolloutId,
            'version'           => ConfigService::get('active_rollout_version'),
            'started_at'        => ConfigService::get('active_rollout_started_at'),
            'total'             => $total,
            'processed'         => $processed,
            'remaining'         => $remaining,
            'pending'           => $counts['pending'],
            'applying'          => $counts['applying'],
            'success'           => $counts['success'],
            'error'             => $counts['error'],
            'percentage'        => round(($processed / $total) * 100, 1),
            'phase'             => $phase,
            'monitoring'        => $remaining === 0,
            'countdown_seconds' => $countdownSeconds,
            'countdown_total_seconds' => $countdownTotal,
            'countdown_percentage' => $countdownPercentage,
            'countdown_until'   => $countdownSeconds === null ? null : $now + $countdownSeconds,
            'next_hostname'     => $nextMachine['hostname'] ?? '',
            'last_cycle_action' => $latestCycle['last_cycle_action'] ?? '',
            'last_cycle_at'     => $latestCycle['last_cycle_at'] ?? '',
            'last_cycle_hostname' => $latestCycle['hostname'] ?? '',
            'server_time'       => date(DATE_ATOM),
            'active'            => $remaining > 0,
            'complete'          => $remaining === 0,
            'completed_with_error' => $remaining === 0 && $counts['error'] > 0,
            'machines'          => $machines,
        ];
    }

    /** Seconds since the most recent client contact (computed by the database), or null. */
    public function lastCheckAgeSeconds(): ?int
    {
        global $DB;
        $row = $DB->request([
            'SELECT' => [new QueryExpression('MAX(last_check) AS last_check')],
            'FROM'   => self::TABLE,
            'WHERE'  => ['revoked_at' => null],
        ])->current();
        $timestamp = is_array($row) ? ServerClock::toTimestamp((string) ($row['last_check'] ?? '')) : 0;
        return $timestamp > 0 ? max(0, time() - $timestamp) : null;
    }

    public static function describeAge(?int $seconds): string
    {
        if ($seconds === null) {
            return 'Nenhum computador fez contato ainda';
        }
        if ($seconds < 60) {
            return 'Última verificação agora mesmo';
        }
        if ($seconds < 3600) {
            $minutes = intdiv($seconds, 60);
            return sprintf('Última verificação há %d %s', $minutes, $minutes === 1 ? 'minuto' : 'minutos');
        }
        if ($seconds < 86400) {
            $hours = intdiv($seconds, 3600);
            return sprintf('Última verificação há %d %s', $hours, $hours === 1 ? 'hora' : 'horas');
        }
        $days = intdiv($seconds, 86400);
        return sprintf('Última verificação há %d %s', $days, $days === 1 ? 'dia' : 'dias');
    }

    /**
     * Recent activity for the overview: administrative actions and what the clients applied.
     *
     * @return list<array{at:string,time:string,message:string,tone:string}>
     */
    public function activity(?array $rollout, int $limit = 6): array
    {
        global $DB;
        $items = [];

        $auditMessages = [
            'rollback'                   => 'Versão %s definida como atual',
            'enable'                     => 'Distribuição ativada',
            'disable'                    => 'Distribuição desativada',
            'settings_update'            => 'Configurações atualizadas',
            'registration_secret_rotate' => 'Segredo de registro renovado',
        ];
        $audits = $DB->request([
            'SELECT' => ['action', 'new_value', 'created_at'],
            'FROM'   => 'glpi_plugin_ativawallpaper_audits',
            'WHERE'  => ['action' => array_merge(['publish'], array_keys($auditMessages))],
            'ORDER'  => ['created_at DESC', 'id DESC'],
            'LIMIT'  => $limit,
        ]);
        foreach ($audits as $audit) {
            $value = json_decode((string) ($audit['new_value'] ?? ''), true);
            if ($audit['action'] === 'publish') {
                $message = sprintf('Novo wallpaper publicado (%s)', is_array($value) ? (string) ($value['version'] ?? '') : '');
            } else {
                $message = sprintf($auditMessages[$audit['action']], is_string($value) ? $value : '');
            }
            $items[] = $this->activityItem((string) $audit['created_at'], $message, 'primary');
        }

        if ($DB->tableExists('glpi_plugin_ativawallpaper_client_events')) {
            $eventMessages = [
                'initial_applied'       => ['atualizado', 'primary'],
                'configuration_applied' => ['atualizado', 'primary'],
                'forced_applied'        => ['reaplicação concluída', 'primary'],
                'drift_corrected'       => ['alteração detectada e wallpaper restaurado', 'warning'],
            ];
            $events = $DB->request([
                'SELECT' => ['hostname', 'event_type', 'occurred_at'],
                'FROM'   => 'glpi_plugin_ativawallpaper_client_events',
                'ORDER'  => ['occurred_at DESC', 'id DESC'],
                'LIMIT'  => $limit,
            ]);
            foreach ($events as $event) {
                [$label, $tone] = $eventMessages[$event['event_type']] ?? ['wallpaper aplicado', 'primary'];
                $items[] = $this->activityItem((string) $event['occurred_at'], $event['hostname'] . ': ' . $label, $tone);
            }

            if ($rollout !== null && $rollout['complete']) {
                $finished = $DB->request([
                    'SELECT' => [new QueryExpression('MAX(occurred_at) AS finished_at')],
                    'FROM'   => 'glpi_plugin_ativawallpaper_client_events',
                    'WHERE'  => ['rollout_id' => $rollout['id']],
                ])->current();
                if (is_array($finished) && !empty($finished['finished_at'])) {
                    $items[] = $this->activityItem(
                        (string) $finished['finished_at'],
                        $rollout['error'] > 0
                            ? sprintf('Aplicação encerrada com %d erro(s)', $rollout['error'])
                            : 'Aplicação concluída em todos os computadores',
                        $rollout['error'] > 0 ? 'danger' : 'success'
                    );
                }
            }
        }

        // Newest first; on the same second the rollout summary comes before the hosts.
        $order = ['success' => 0, 'danger' => 0, 'warning' => 1, 'primary' => 2];
        usort($items, static fn(array $left, array $right): int =>
            strcmp($right['at'], $left['at']) ?: ($order[$left['tone']] <=> $order[$right['tone']]));
        return array_slice($items, 0, $limit);
    }

    private function activityItem(string $at, string $message, string $tone): array
    {
        // Plugin dates are server-clock strings: format them without timezone conversion.
        $time = substr($at, 0, 10) === substr(ServerClock::now(), 0, 10)
            ? substr($at, 11, 8)
            : substr($at, 8, 2) . '/' . substr($at, 5, 2) . ' ' . substr($at, 11, 5);
        return ['at' => $at, 'time' => $time, 'message' => $message, 'tone' => $tone];
    }

    private function statusExpression(string $status, ?array $current): ?string
    {
        global $DB;
        if (!in_array($status, ['updated', 'pending', 'offline', 'error'], true)) {
            return null;
        }
        $seconds = max(120, ConfigService::getInt('offline_after_seconds'));
        $threshold = $DB->quoteValue(ServerClock::format(time() - $seconds));
        $online = "last_check IS NOT NULL AND last_check >= {$threshold}";
        $offline = "(last_check IS NULL OR last_check < {$threshold})";
        $version = $DB->quoteValue((string) ($current['version'] ?? ''));
        return match ($status) {
            'offline' => $offline,
            'error'   => "({$online}) AND status = 'error'",
            'updated' => "({$online}) AND status = 'success' AND wallpaper_version = {$version}",
            'pending' => "({$online}) AND status <> 'error' AND (status <> 'success' OR wallpaper_version IS NULL OR wallpaper_version <> {$version})",
        };
    }
}
