<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

final class RateLimiter
{
    private const TABLE = 'glpi_plugin_ativawallpaper_rate_limits';

    public function consume(string $bucket, int $limit, int $windowSeconds): void
    {
        global $DB;
        $hash = hash('sha256', 'ativawallpaper:' . $bucket);
        $now = time();
        $iterator = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['bucket_hash' => $hash],
            'LIMIT' => 1,
        ]);
        $row = $iterator->current();
        if (!is_array($row)) {
            $DB->insert(self::TABLE, [
                'bucket_hash'       => $hash,
                'window_started_at' => date('Y-m-d H:i:s', $now),
                'hits'              => 1,
                'updated_at'        => date('Y-m-d H:i:s', $now),
            ]);
            return;
        }

        $startedAt = strtotime((string) $row['window_started_at']) ?: 0;
        if ($startedAt + $windowSeconds <= $now) {
            $DB->update(self::TABLE, [
                'window_started_at' => date('Y-m-d H:i:s', $now),
                'hits'              => 1,
                'updated_at'        => date('Y-m-d H:i:s', $now),
            ], ['id' => (int) $row['id']]);
            return;
        }

        if ((int) $row['hits'] >= $limit) {
            $retryAfter = max(1, ($startedAt + $windowSeconds) - $now);
            throw new ApiException(
                'Limite de requisicoes excedido',
                429,
                'RATE_LIMITED',
                ['Retry-After' => (string) $retryAfter]
            );
        }

        $DB->update(self::TABLE, [
            'hits'       => ((int) $row['hits']) + 1,
            'updated_at' => date('Y-m-d H:i:s', $now),
        ], ['id' => (int) $row['id']]);
    }
}
