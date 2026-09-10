<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use Glpi\DBAL\QueryExpression;

final class ClientEventRepository
{
    private const TABLE = 'glpi_plugin_ativawallpaper_client_events';
    private const TYPES = [
        'initial_applied',
        'configuration_applied',
        'forced_applied',
        'drift_corrected',
    ];

    public function record(array $client, array $payload, ?string $ipAddress): void
    {
        global $DB;

        if (!$DB->tableExists(self::TABLE)) {
            return;
        }

        $eventType = strtolower(Security::cleanText($payload['apply_reason'] ?? '', 32));
        $eventKey = strtolower(Security::cleanText($payload['apply_event_id'] ?? '', 32));
        if (!in_array($eventType, self::TYPES, true)
            || preg_match('/^[a-f0-9]{32}$/', $eventKey) !== 1) {
            return;
        }

        if (countElementsInTable(self::TABLE, ['event_key' => $eventKey]) > 0) {
            return;
        }

        $DB->insert(self::TABLE, [
            'event_key'         => $eventKey,
            'clients_id'        => (int) $client['id'],
            'computers_id'      => !empty($client['computers_id']) ? (int) $client['computers_id'] : null,
            'hostname'          => Security::cleanText((string) ($client['hostname'] ?? ''), 255),
            'username'          => ($client['username'] ?? '') !== '' ? Security::cleanText((string) $client['username'], 255) : null,
            'event_type'        => $eventType,
            'wallpaper_version' => ($client['wallpaper_version'] ?? '') !== '' ? Security::cleanText((string) $client['wallpaper_version'], 32) : null,
            'rollout_id'        => ($payload['rollout_id'] ?? '') !== '' ? Security::cleanText((string) $payload['rollout_id'], 64) : null,
            'lock_requested'    => !empty($payload['lock_change']) ? 1 : 0,
            'policy_enforced'   => !empty($payload['policy_enforced']) ? 1 : 0,
            'ip_address'        => Security::cleanText($ipAddress ?? '', 45),
            'occurred_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return array{rows:array,total:int,page:int,pages:int,limit:int} */
    public function search(array $filters, int $page = 1, int $limit = 50): array
    {
        global $DB;

        $page = max(1, $page);
        $limit = max(10, min(100, $limit));
        $where = [];
        $query = Security::cleanText($filters['q'] ?? '', 255);
        if ($query !== '') {
            $where['OR'] = [
                'hostname' => ['LIKE', '%' . $query . '%'],
                'username' => ['LIKE', '%' . $query . '%'],
            ];
        }
        $type = Security::cleanText($filters['type'] ?? '', 32);
        if (in_array($type, self::TYPES, true)) {
            $where['event_type'] = $type;
        }

        $count = $DB->request([
            'SELECT' => [new QueryExpression('COUNT(*) AS total')],
            'FROM'   => self::TABLE,
            'WHERE'  => $where,
        ])->current();
        $total = is_array($count) ? (int) $count['total'] : 0;
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);

        $rows = [];
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => $where,
            'ORDER' => ['occurred_at DESC', 'id DESC'],
            'START' => ($page - 1) * $limit,
            'LIMIT' => $limit,
        ]);
        foreach ($iterator as $row) {
            $rows[] = $row;
        }

        return compact('rows', 'total', 'page', 'pages', 'limit');
    }

    public function summary(): array
    {
        global $DB;

        $row = $DB->request([
            'SELECT' => [
                new QueryExpression('COUNT(*) AS total'),
                new QueryExpression("SUM(CASE WHEN event_type = 'drift_corrected' THEN 1 ELSE 0 END) AS corrected"),
                new QueryExpression("SUM(CASE WHEN event_type = 'forced_applied' THEN 1 ELSE 0 END) AS forced"),
                new QueryExpression("SUM(CASE WHEN event_type IN ('initial_applied', 'configuration_applied') THEN 1 ELSE 0 END) AS published"),
            ],
            'FROM' => self::TABLE,
        ])->current();

        return [
            'total'     => is_array($row) ? (int) ($row['total'] ?? 0) : 0,
            'corrected' => is_array($row) ? (int) ($row['corrected'] ?? 0) : 0,
            'forced'    => is_array($row) ? (int) ($row['forced'] ?? 0) : 0,
            'published' => is_array($row) ? (int) ($row['published'] ?? 0) : 0,
        ];
    }
}
