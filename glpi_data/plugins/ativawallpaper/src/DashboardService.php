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
