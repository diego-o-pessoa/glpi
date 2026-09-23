<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;

/**
 * Provisionamento de um computador com um perfil (glpi_plugin_ativaworkspace_jobs).
 * Nesta etapa so e lido (listagem e indicadores); nenhum job e executado.
 */
final class Job extends CommonDBTM
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_WAITING   = 'waiting_intervention';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_PENDING   => ['Na fila', 'bg-secondary'],
        self::STATUS_RUNNING   => ['Em execução', 'bg-blue'],
        self::STATUS_WAITING   => ['Aguardando intervenção', 'bg-warning'],
        self::STATUS_FAILED    => ['Falha', 'bg-danger'],
        self::STATUS_COMPLETED => ['Concluído', 'bg-success'],
        self::STATUS_CANCELLED => ['Cancelado', 'bg-secondary'],
    ];

    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_PROVISION;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Provisionamentos' : 'Provisionamento';
    }

    /**
     * Indicadores da Visao Geral, restritos as entidades ativas do usuario.
     *
     * @return array{active: int, waiting: int, failed: int, completed_today: int}
     */
    public static function overviewCounts(): array
    {
        $table  = self::getTable();
        $entity = getEntitiesRestrictCriteria($table, '', '', false);
        $today  = date('Y-m-d 00:00:00', strtotime($_SESSION['glpi_currenttime'] ?? 'now'));

        return [
            'active'          => countElementsInTable($table, $entity + ['status' => [self::STATUS_PENDING, self::STATUS_RUNNING]]),
            'waiting'         => countElementsInTable($table, $entity + ['status' => self::STATUS_WAITING]),
            'failed'          => countElementsInTable($table, $entity + ['status' => self::STATUS_FAILED]),
            'completed_today' => countElementsInTable($table, $entity + [
                'status'   => self::STATUS_COMPLETED,
                'date_end' => ['>=', $today],
            ]),
        ];
    }

    /**
     * Ultimos provisionamentos, com nomes resolvidos para a tela.
     *
     * @return list<array<string, mixed>>
     */
    public static function listForPage(int $limit): array
    {
        global $DB;

        $table = self::getTable();
        $rows  = [];
        foreach ($DB->request([
            'SELECT'    => [
                "$table.*",
                'glpi_computers.name AS computer_name',
                ProvisioningProfile::getTable() . '.name AS profile_name',
            ],
            'FROM'      => $table,
            'LEFT JOIN' => [
                'glpi_computers' => [
                    'ON' => ['glpi_computers' => 'id', $table => 'computers_id'],
                ],
                ProvisioningProfile::getTable() => [
                    'ON' => [ProvisioningProfile::getTable() => 'id', $table => 'plugin_ativaworkspace_provisioningprofiles_id'],
                ],
            ],
            'WHERE'     => getEntitiesRestrictCriteria($table, '', '', false),
            'ORDER'     => ["$table.id DESC"],
            'LIMIT'     => max(1, min(200, $limit)),
        ]) as $row) {
            [$label, $badge] = self::STATUS_LABELS[$row['status']] ?? [$row['status'], 'bg-secondary'];
            $row['status_label'] = $label;
            $row['status_badge'] = $badge;
            $rows[] = $row;
        }
        return $rows;
    }
}
