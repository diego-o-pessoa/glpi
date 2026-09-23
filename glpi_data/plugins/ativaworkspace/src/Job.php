<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;

/**
 * Provisionamento de um computador com um perfil (glpi_plugin_ativaworkspace_jobs).
 * Estados e transicoes ficam no ProvisioningEngine; aqui ficam consultas e o
 * formato de exibicao.
 */
final class Job extends CommonDBTM
{
    public const QUEUED               = 'QUEUED';
    public const RUNNING              = 'RUNNING';
    public const WAITING_INTERVENTION = 'WAITING_INTERVENTION';
    public const FAILED               = 'FAILED';
    public const COMPLETED            = 'COMPLETED';
    public const CANCELED             = 'CANCELED';

    /** Provisionamento "ativo": o computador esta ocupado. */
    public const ACTIVE = [self::QUEUED, self::RUNNING, self::WAITING_INTERVENTION];

    /** status => [rotulo, estado visual (classes aw-pill-*)] */
    public const LABELS = [
        self::QUEUED               => ['Na fila', 'queued'],
        self::RUNNING              => ['Em andamento', 'running'],
        self::WAITING_INTERVENTION => ['Aguardando intervenção', 'waiting'],
        self::FAILED               => ['Falha', 'failed'],
        self::COMPLETED            => ['Concluído', 'completed'],
        self::CANCELED             => ['Cancelado', 'cancelled'],
    ];

    public const FK = 'plugin_ativaworkspace_jobs_id';

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
            'active'          => countElementsInTable($table, $entity + ['status' => [self::QUEUED, self::RUNNING]]),
            'waiting'         => countElementsInTable($table, $entity + ['status' => self::WAITING_INTERVENTION]),
            'failed'          => countElementsInTable($table, $entity + ['status' => self::FAILED]),
            'completed_today' => countElementsInTable($table, $entity + [
                'status'   => self::COMPLETED,
                'date_end' => ['>=', $today],
            ]),
        ];
    }

    /**
     * Filtros aceitos na listagem (vindos da query string), ja validados.
     *
     * @return array{status: string, computer: string, employee: string, profile: int, date_from: string, date_to: string}
     */
    public static function filtersFrom(array $query): array
    {
        $date = static fn ($value): string => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) ? $value : '';
        $status = strtoupper((string) ($query['status'] ?? ''));

        return [
            'status'    => array_key_exists($status, self::LABELS) || $status === 'ACTIVE' ? $status : '',
            'computer'  => mb_substr(trim((string) ($query['computer'] ?? '')), 0, 100),
            'employee'  => mb_substr(trim((string) ($query['employee'] ?? '')), 0, 100),
            'profile'   => max(0, (int) ($query['profile'] ?? 0)),
            'date_from' => $date($query['date_from'] ?? ''),
            'date_to'   => $date($query['date_to'] ?? ''),
        ];
    }

    /** Escapa curingas do LIKE (o texto do usuario e literal). */
    private static function like(string $value): string
    {
        return '%' . addcslashes($value, '%_\\') . '%';
    }

    /**
     * Provisionamentos para as telas, com nomes e progresso resolvidos.
     * Ordem: ativos primeiro (o que esta acontecendo agora), depois os mais recentes.
     *
     * @param array<string, mixed> $filters saida de filtersFrom()
     * @return list<array<string, mixed>>
     */
    public static function listForPage(int $limit, array $filters = []): array
    {
        global $DB;

        $table = self::getTable();
        $where = getEntitiesRestrictCriteria($table, '', '', false);

        $status = (string) ($filters['status'] ?? '');
        if ($status === 'ACTIVE') {
            $where["$table.status"] = self::ACTIVE;
        } elseif ($status !== '') {
            $where["$table.status"] = $status;
        }
        if (($filters['computer'] ?? '') !== '') {
            $where['glpi_computers.name'] = ['LIKE', self::like($filters['computer'])];
        }
        if (($filters['employee'] ?? '') !== '') {
            $where["$table.employee_name"] = ['LIKE', self::like($filters['employee'])];
        }
        if ((int) ($filters['profile'] ?? 0) > 0) {
            $where["$table." . ProfileStep::PROFILE_FK] = (int) $filters['profile'];
        }
        if (($filters['date_from'] ?? '') !== '') {
            $where[] = ["$table.date_creation" => ['>=', $filters['date_from'] . ' 00:00:00']];
        }
        if (($filters['date_to'] ?? '') !== '') {
            $where[] = ["$table.date_creation" => ['<=', $filters['date_to'] . ' 23:59:59']];
        }

        $rows = [];
        foreach ($DB->request([
            'SELECT'    => ["$table.*", 'glpi_computers.name AS computer_name'],
            'FROM'      => $table,
            'LEFT JOIN' => [
                'glpi_computers' => ['ON' => ['glpi_computers' => 'id', $table => 'computers_id']],
            ],
            'WHERE'     => $where,
            'ORDER'     => ["$table.id DESC"],
            'LIMIT'     => max(1, min(200, $limit)),
        ]) as $row) {
            $rows[] = $row;
        }

        $active = [];
        $others = [];
        foreach ($rows as $row) {
            $item = self::present($row);
            if ($item['is_active']) {
                $active[] = $item;
            } else {
                $others[] = $item;
            }
        }
        return array_merge($active, $others);
    }

    /**
     * Formato exposto para as telas e para o JSON de tempo real.
     *
     * @return array<string, mixed>
     */
    public static function present(array $row): array
    {
        [$label, $state] = self::LABELS[$row['status']] ?? [(string) $row['status'], 'queued'];
        $progress = max(0, min(100, (int) ($row['progress'] ?? 0)));

        return [
            'id'              => (int) $row['id'],
            'computers_id'    => (int) $row['computers_id'],
            'computer_name'   => (string) ($row['computer_name'] ?? '') !== ''
                ? (string) $row['computer_name']
                : ((int) $row['computers_id'] > 0 ? '#' . (int) $row['computers_id'] : '—'),
            'employee_name'   => (string) ($row['employee_name'] ?? ''),
            'upn'             => (string) ($row['upn'] ?? ''),
            'profile_name'    => (string) ($row['profile_name'] ?? ''),
            'status'          => (string) $row['status'],
            'status_label'    => $label,
            'status_state'    => $state,
            'is_active'       => in_array($row['status'], self::ACTIVE, true),
            // Compatibilidade com o JS da Etapa 2 (modal "Ver log").
            'is_open'         => in_array($row['status'], self::ACTIVE, true),
            'progress'        => $progress,
            'current_step_id' => (int) ($row['plugin_ativaworkspace_jobsteps_id'] ?? 0),
            'message'         => (string) ($row['message'] ?? ''),
            'date_start'      => $row['date_start'] ?? null,
            'date_end'        => $row['date_end'] ?? null,
            'date_creation'   => $row['date_creation'] ?? null,
            'date_mod'        => $row['date_mod'] ?? null,
            'requester'       => (int) $row['users_id'] > 0 ? getUserName((int) $row['users_id']) : 'Sistema',
        ];
    }

    /**
     * Detalhes de um job: dados, etapas (snapshot) e eventos.
     * Retorna null se o job nao existe ou esta fora das entidades do usuario.
     *
     * @return array{job: array<string, mixed>, steps: list<array<string, mixed>>, events: list<array<string, mixed>>}|null
     */
    public static function details(int $id): ?array
    {
        global $DB;

        $table = self::getTable();
        $row = $DB->request([
            'SELECT'    => ["$table.*", 'glpi_computers.name AS computer_name'],
            'FROM'      => $table,
            'LEFT JOIN' => [
                'glpi_computers' => ['ON' => ['glpi_computers' => 'id', $table => 'computers_id']],
            ],
            'WHERE'     => ["$table.id" => $id] + getEntitiesRestrictCriteria($table, '', '', false),
            'LIMIT'     => 1,
        ])->current();

        if (!is_array($row)) {
            return null;
        }

        $job = self::present($row);
        $steps = array_map(
            static fn (array $step): array => JobStep::present($step, $job['current_step_id']),
            JobStep::forJob($id)
        );

        return [
            'job'    => $job,
            'steps'  => $steps,
            'events' => Event::recent(200, null, 0, $id),
        ];
    }
}
