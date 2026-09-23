<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use Computer;
use PluginAtivaworkspaceProfile;
use Session;

/**
 * Provisionamento de um computador com um perfil (glpi_plugin_ativaworkspace_jobs).
 * Criar um job so o coloca na fila: a execucao (Job Engine) entra numa proxima etapa.
 */
final class Job extends CommonDBTM
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_WAITING   = 'waiting_intervention';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Situacoes em que o computador ainda esta "ocupado" com um provisionamento. */
    public const OPEN_STATUSES = [self::STATUS_PENDING, self::STATUS_RUNNING, self::STATUS_WAITING];

    public const STATUS_LABELS = [
        self::STATUS_PENDING   => ['Na fila', 'queued'],
        self::STATUS_RUNNING   => ['Em andamento', 'running'],
        self::STATUS_WAITING   => ['Aguardando intervenção', 'waiting'],
        self::STATUS_FAILED    => ['Falha', 'failed'],
        self::STATUS_COMPLETED => ['Concluído', 'completed'],
        self::STATUS_CANCELLED => ['Cancelado', 'cancelled'],
    ];

    /** Etapas que contam como feitas no progresso. */
    private const DONE_STEP_STATUSES = ['completed', 'skipped'];

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
     * Provisionamentos para as telas, com nomes e progresso resolvidos.
     * Ordem: abertos primeiro (o que esta acontecendo agora), depois os mais recentes.
     *
     * @return list<array<string, mixed>>
     */
    public static function listForPage(int $limit): array
    {
        global $DB;

        $table   = self::getTable();
        $profile = ProvisioningProfile::getTable();
        $rows    = [];
        foreach ($DB->request([
            'SELECT'    => [
                "$table.*",
                'glpi_computers.name AS computer_name',
                "$profile.name AS profile_name",
            ],
            'FROM'      => $table,
            'LEFT JOIN' => [
                'glpi_computers' => [
                    'ON' => ['glpi_computers' => 'id', $table => 'computers_id'],
                ],
                $profile => [
                    'ON' => [$profile => 'id', $table => 'plugin_ativaworkspace_provisioningprofiles_id'],
                ],
            ],
            'WHERE'     => getEntitiesRestrictCriteria($table, '', '', false),
            'ORDER'     => ["$table.id DESC"],
            'LIMIT'     => max(1, min(200, $limit)),
        ]) as $row) {
            $rows[] = $row;
        }

        $progress = self::progressFor(array_map(static fn ($row) => (int) $row['id'], $rows));

        $open = [];
        $closed = [];
        foreach ($rows as $row) {
            $item = self::present($row, $progress[(int) $row['id']] ?? null);
            if (in_array($row['status'], self::OPEN_STATUSES, true)) {
                $open[] = $item;
            } else {
                $closed[] = $item;
            }
        }
        return array_merge($open, $closed);
    }

    /**
     * Formato exposto para as telas e para o JSON de tempo real.
     *
     * @param array{done: int, total: int}|null $progress
     * @return array<string, mixed>
     */
    public static function present(array $row, ?array $progress): array
    {
        [$label, $state] = self::STATUS_LABELS[$row['status']] ?? [(string) $row['status'], 'queued'];

        if ($row['status'] === self::STATUS_COMPLETED) {
            $percent = 100;
        } elseif ($progress !== null && $progress['total'] > 0) {
            $percent = (int) floor(100 * $progress['done'] / $progress['total']);
        } else {
            $percent = 0;
        }

        return [
            'id'            => (int) $row['id'],
            'computers_id'  => (int) $row['computers_id'],
            'computer_name' => (string) ($row['computer_name'] ?? '') !== ''
                ? (string) $row['computer_name']
                : ((int) $row['computers_id'] > 0 ? '#' . (int) $row['computers_id'] : '—'),
            'employee_name' => (string) ($row['employee_name'] ?? ''),
            'profile_name'  => (string) ($row['profile_name'] ?? ''),
            'status'        => (string) $row['status'],
            'status_label'  => $label,
            'status_state'  => $state,
            'is_open'       => in_array($row['status'], self::OPEN_STATUSES, true),
            'progress'      => $percent,
            'steps_done'    => $progress['done'] ?? 0,
            'steps_total'   => $progress['total'] ?? 0,
            'message'       => (string) ($row['message'] ?? ''),
            'date_start'    => $row['date_start'] ?? null,
            'date_end'      => $row['date_end'] ?? null,
            'date_creation' => $row['date_creation'] ?? null,
            'requester'     => (int) $row['users_id'] > 0 ? getUserName((int) $row['users_id']) : 'Sistema',
        ];
    }

    /**
     * Etapas feitas/total por job, numa consulta so.
     *
     * @param list<int> $jobIds
     * @return array<int, array{done: int, total: int}>
     */
    private static function progressFor(array $jobIds): array
    {
        global $DB;

        if ($jobIds === []) {
            return [];
        }

        $result = [];
        foreach ($DB->request([
            'SELECT' => ['plugin_ativaworkspace_jobs_id', 'status'],
            'FROM'   => JobStep::getTable(),
            'WHERE'  => ['plugin_ativaworkspace_jobs_id' => $jobIds],
        ]) as $step) {
            $id = (int) $step['plugin_ativaworkspace_jobs_id'];
            $result[$id] ??= ['done' => 0, 'total' => 0];
            $result[$id]['total']++;
            if (in_array($step['status'], self::DONE_STEP_STATUSES, true)) {
                $result[$id]['done']++;
            }
        }
        return $result;
    }

    /**
     * Detalhes de um job para o modal de log: dados, etapas e eventos.
     * Retorna null se o job nao existe ou esta fora das entidades do usuario.
     *
     * @return array{job: array<string, mixed>, steps: list<array<string, mixed>>, events: list<array<string, mixed>>}|null
     */
    public static function details(int $id): ?array
    {
        global $DB;

        $table   = self::getTable();
        $profile = ProvisioningProfile::getTable();
        $row = $DB->request([
            'SELECT'    => ["$table.*", 'glpi_computers.name AS computer_name', "$profile.name AS profile_name"],
            'FROM'      => $table,
            'LEFT JOIN' => [
                'glpi_computers' => ['ON' => ['glpi_computers' => 'id', $table => 'computers_id']],
                $profile         => ['ON' => [$profile => 'id', $table => 'plugin_ativaworkspace_provisioningprofiles_id']],
            ],
            'WHERE'     => ["$table.id" => $id] + getEntitiesRestrictCriteria($table, '', '', false),
            'LIMIT'     => 1,
        ])->current();

        if (!is_array($row)) {
            return null;
        }

        $steps = [];
        foreach ($DB->request([
            'FROM'  => JobStep::getTable(),
            'WHERE' => ['plugin_ativaworkspace_jobs_id' => $id],
            'ORDER' => ['step_order ASC', 'id ASC'],
        ]) as $step) {
            $steps[] = [
                'name'       => (string) $step['name'],
                'step_type'  => (string) $step['step_type'],
                'status'     => (string) $step['status'],
                'message'    => (string) ($step['message'] ?? ''),
                'date_start' => $step['date_start'],
                'date_end'   => $step['date_end'],
            ];
        }

        $progress = ['done' => 0, 'total' => count($steps)];
        foreach ($steps as $step) {
            if (in_array($step['status'], self::DONE_STEP_STATUSES, true)) {
                $progress['done']++;
            }
        }

        return [
            'job'    => self::present($row, $progress),
            'steps'  => $steps,
            'events' => Event::recent(100, null, 0, $id),
        ];
    }

    /**
     * Coloca um provisionamento na fila. Nao executa nada.
     * As permissoes (direito de provisionar, computador e perfil visiveis)
     * sao conferidas aqui, no backend.
     *
     * @return int id do job criado
     * @throws \RuntimeException mensagem pronta para o usuario
     */
    public static function enqueue(int $computersId, int $profileId, string $employeeName): int
    {
        global $DB;

        if (!Session::haveRight(self::$rightname, CREATE)) {
            throw new \RuntimeException('Você não tem permissão para provisionar.');
        }

        $computer = new Computer();
        if ($computersId <= 0 || !$computer->getFromDB($computersId) || !$computer->can($computersId, READ)) {
            throw new \RuntimeException('Computador não encontrado ou fora das suas entidades.');
        }

        $profile = new ProvisioningProfile();
        if (
            $profileId <= 0
            || !$profile->getFromDB($profileId)
            || (int) $profile->fields['is_active'] !== 1
            || !Session::haveAccessToEntity((int) $profile->fields['entities_id'], (bool) $profile->fields['is_recursive'])
        ) {
            throw new \RuntimeException('Perfil de provisionamento inválido ou inativo.');
        }

        $employeeName = trim($employeeName);
        if (mb_strlen($employeeName) > 255) {
            throw new \RuntimeException('Nome do funcionário muito longo.');
        }

        if (countElementsInTable(self::getTable(), ['computers_id' => $computersId, 'status' => self::OPEN_STATUSES]) > 0) {
            throw new \RuntimeException('Este computador já tem um provisionamento em aberto.');
        }

        $job = new self();
        $jobId = (int) $job->add([
            'entities_id'   => (int) $computer->fields['entities_id'],
            'computers_id'  => $computersId,
            'plugin_ativaworkspace_provisioningprofiles_id' => $profileId,
            'status'        => self::STATUS_PENDING,
            'employee_name' => $employeeName,
            'users_id'      => (int) Session::getLoginUserID(),
            'message'       => 'Na fila: aguardando o executor do Workspace.',
        ]);
        if ($jobId <= 0) {
            throw new \RuntimeException('Não foi possível criar o provisionamento.');
        }

        // Congela as etapas ativas do perfil no job: editar o perfil depois nao
        // muda um provisionamento que ja comecou.
        $stepModel = new JobStep();
        foreach ($DB->request([
            'FROM'  => ProfileStep::getTable(),
            'WHERE' => ['plugin_ativaworkspace_provisioningprofiles_id' => $profileId, 'is_active' => 1],
            'ORDER' => ['step_order ASC', 'id ASC'],
        ]) as $step) {
            $stepModel->add([
                'plugin_ativaworkspace_jobs_id'         => $jobId,
                'plugin_ativaworkspace_profilesteps_id' => (int) $step['id'],
                'name'                                  => (string) $step['name'],
                'step_type'                             => (string) $step['step_type'],
                'step_order'                            => (int) $step['step_order'],
                'status'                                => 'pending',
            ]);
        }

        Event::log(Event::LEVEL_INFO, 'provisioning', 'Provisionamento criado', [
            'computer' => (string) $computer->fields['name'],
            'profile'  => (string) $profile->fields['name'],
            'employee' => $employeeName,
        ], $jobId);

        return $jobId;
    }
}
