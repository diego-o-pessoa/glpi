<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;

/**
 * Etapa de um provisionamento (glpi_plugin_ativaworkspace_jobsteps).
 * E um snapshot da etapa do perfil no momento da criacao do job: o job nunca
 * mais le o perfil. Estados e transicoes ficam no ProvisioningEngine.
 */
final class JobStep extends CommonDBTM
{
    public const PENDING       = 'PENDING';
    public const QUEUED        = 'QUEUED';
    public const RUNNING       = 'RUNNING';
    public const WAITING_HUMAN = 'WAITING_HUMAN';
    public const VERIFYING     = 'VERIFYING';
    public const SUCCESS       = 'SUCCESS';
    public const FAILED        = 'FAILED';
    public const SKIPPED       = 'SKIPPED';
    public const CANCELED      = 'CANCELED';

    public const JOB_FK = 'plugin_ativaworkspace_jobs_id';

    /** Estados em que a etapa esta "em andamento" (pode receber resultado). */
    public const ACTIVE = [self::QUEUED, self::RUNNING, self::WAITING_HUMAN, self::VERIFYING];

    /** Estados finais (so saem por retry). */
    public const FINAL = [self::SUCCESS, self::FAILED, self::SKIPPED, self::CANCELED];

    /** status => [rotulo, tom para a tela, icone] */
    public const LABELS = [
        self::PENDING       => ['Pendente', 'secondary', 'ti-circle'],
        self::QUEUED        => ['Aguardando executor', 'blue', 'ti-player-play'],
        self::RUNNING       => ['Em execução', 'blue', 'ti-loader-2'],
        self::WAITING_HUMAN => ['Aguardando intervenção do TI', 'warning', 'ti-hand-stop'],
        self::VERIFYING     => ['Verificando', 'blue', 'ti-search'],
        self::SUCCESS       => ['Concluída', 'success', 'ti-check'],
        self::FAILED        => ['Falhou', 'danger', 'ti-x'],
        self::SKIPPED       => ['Ignorada', 'secondary', 'ti-player-skip-forward'],
        self::CANCELED      => ['Cancelada', 'secondary', 'ti-ban'],
    ];

    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_PROVISION;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Etapas do provisionamento' : 'Etapa do provisionamento';
    }

    /** Etapa que ja conta como resolvida para o progresso/avanco. */
    public static function isResolved(array $step): bool
    {
        return in_array($step['status'], [self::SUCCESS, self::SKIPPED, self::CANCELED], true)
            || ($step['status'] === self::FAILED && (int) $step['continue_on_error'] === 1);
    }

    /**
     * Etapas de um job, em ordem.
     *
     * @return list<array<string, mixed>>
     */
    public static function forJob(int $jobId): array
    {
        global $DB;

        $steps = [];
        foreach ($DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => [self::JOB_FK => $jobId],
            'ORDER' => ['step_order ASC', 'id ASC'],
        ]) as $row) {
            $steps[] = $row;
        }
        return $steps;
    }

    /**
     * Formato exposto para telas/JSON.
     *
     * @return array<string, mixed>
     */
    public static function present(array $row, int $currentStepId = 0): array
    {
        [$label, $tone, $icon] = self::LABELS[$row['status']] ?? [(string) $row['status'], 'secondary', 'ti-circle'];
        $snapshot = json_decode((string) ($row['snapshot'] ?? ''), true);
        $snapshot = is_array($snapshot) ? $snapshot : [];

        return [
            'id'                => (int) $row['id'],
            'name'              => (string) $row['name'],
            'step_type'         => (string) $row['step_type'],
            'type_label'        => StepType::label((string) $row['step_type']),
            'step_order'        => (int) $row['step_order'],
            'status'            => (string) $row['status'],
            'status_label'      => $label,
            'tone'              => $tone,
            'icon'              => $icon,
            'is_current'        => (int) $row['id'] === $currentStepId,
            'message'           => (string) ($row['message'] ?? ''),
            'attempts'          => (int) ($row['attempts'] ?? 0),
            'max_attempts'      => (int) ($row['max_attempts'] ?? 1),
            'timeout_minutes'   => (int) ($row['timeout_minutes'] ?? 0),
            'is_mandatory'      => (int) ($row['is_mandatory'] ?? 1) === 1,
            'continue_on_error' => (int) ($row['continue_on_error'] ?? 0) === 1,
            'runtime'           => (string) ($row['runtime'] ?? ''),
            'application'       => $snapshot['application'] ?? null,
            'date_start'        => $row['date_start'] ?? null,
            'date_end'          => $row['date_end'] ?? null,
        ];
    }
}
