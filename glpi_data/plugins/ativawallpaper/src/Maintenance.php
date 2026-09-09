<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use CommonDBTM;
use CronTask;
use Glpi\DBAL\QueryExpression;

final class Maintenance extends CommonDBTM
{
    public static function cronInfo(string $name): array
    {
        if ($name === 'reconcile') {
            return ['description' => 'Reconcilia clientes Ativa Wallpaper e remove contadores antigos.'];
        }
        return [];
    }

    public static function cronReconcile(CronTask $task): int
    {
        global $DB;
        $updated = (new ClientRepository())->reconcileUnmatched();
        $DB->delete('glpi_plugin_ativawallpaper_rate_limits', [
            'updated_at' => ['<', new QueryExpression('DATE_SUB(NOW(), INTERVAL 1 DAY)')],
        ]);
        $task->addVolume($updated);
        return 1;
    }
}
