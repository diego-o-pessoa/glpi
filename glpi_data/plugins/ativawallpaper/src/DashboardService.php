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
        $online = "last_check IS NOT NULL AND last_check >= DATE_SUB(NOW(), INTERVAL {$offline} SECOND)";
        $offlineSql = "last_check IS NULL OR last_check < DATE_SUB(NOW(), INTERVAL {$offline} SECOND)";

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
        $where = ['revoked_at' => null];

        $query = Security::cleanText($filters['q'] ?? '', 255);
        if ($query !== '') {
            $where['OR'] = [
                'hostname'          => ['LIKE', '%' . $query . '%'],
                'username'          => ['LIKE', '%' . $query . '%'],
                'wallpaper_version' => ['LIKE', '%' . $query . '%'],
                'client_version'    => ['LIKE', '%' . $query . '%'],
            ];
        }

        $status = (string) ($filters['status'] ?? 'all');
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
            'ORDER' => [sprintf('%s %s', $sort, $direction), 'id DESC'],
            'START' => ($page - 1) * $limit,
            'LIMIT' => $limit,
        ]);
        foreach ($iterator as $row) {
            $row['computed_status'] = $this->computeStatus($row, $current);
            $rows[] = $row;
        }

        return compact('rows', 'total', 'page', 'pages', 'limit');
    }

    public function computeStatus(array $client, ?array $current): string
    {
        $lastCheck = !empty($client['last_check']) ? strtotime((string) $client['last_check']) : false;
        if ($lastCheck === false || $lastCheck < time() - max(120, ConfigService::getInt('offline_after_seconds'))) {
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
            $nextCheckTimestamp = !empty($row['next_check_at']) ? strtotime((string) $row['next_check_at']) : false;
            if ($nextCheckTimestamp === false && !empty($row['last_check'])) {
                $lastCheckTimestamp = strtotime((string) $row['last_check']);
                $nextCheckTimestamp = $lastCheckTimestamp === false ? false : $lastCheckTimestamp + $cycleSeconds;
            }
            $lastCheckTimestamp = !empty($row['last_check']) ? strtotime((string) $row['last_check']) : false;
            $machines[] = [
                'hostname'   => (string) $row['hostname'],
                'status'     => $status,
                'last_error' => $status === 'error' ? (string) ($row['last_error'] ?? '') : '',
                'last_check' => (string) ($row['last_check'] ?? ''),
                'next_check_at' => $nextCheckTimestamp === false ? '' : date('Y-m-d H:i:s', $nextCheckTimestamp),
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
            $latestTimestamp = strtotime($latestCycle['last_cycle_at']);
            $recentApplication = $latestTimestamp !== false
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

    private function statusExpression(string $status, ?array $current): ?string
    {
        global $DB;
        if (!in_array($status, ['updated', 'pending', 'offline', 'error'], true)) {
            return null;
        }
        $seconds = max(120, ConfigService::getInt('offline_after_seconds'));
        $online = "last_check IS NOT NULL AND last_check >= DATE_SUB(NOW(), INTERVAL {$seconds} SECOND)";
        $offline = "(last_check IS NULL OR last_check < DATE_SUB(NOW(), INTERVAL {$seconds} SECOND))";
        $version = $DB->quoteValue((string) ($current['version'] ?? ''));
        return match ($status) {
            'offline' => $offline,
            'error'   => "({$online}) AND status = 'error'",
            'updated' => "({$online}) AND status = 'success' AND wallpaper_version = {$version}",
            'pending' => "({$online}) AND status <> 'error' AND (status <> 'success' OR wallpaper_version IS NULL OR wallpaper_version <> {$version})",
        };
    }
}
