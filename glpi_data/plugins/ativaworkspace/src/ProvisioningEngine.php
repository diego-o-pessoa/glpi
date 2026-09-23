<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Computer;
use CronTask;
use RuntimeException;
use Session;
use Throwable;

/**
 * Job Engine do Ativa Workspace.
 *
 * Nesta etapa NAO executa nada nas maquinas: so conduz os estados.
 * - create():   cria o job com o snapshot das etapas e ja o inicia;
 * - advance():  unico lugar que decide a etapa atual e o estado do job;
 * - recordResult() / retry() / cancel() / confirmIntervention(): entradas de
 *   resultado; na Etapa 4 o executor remoto usara o mesmo recordResult().
 *
 * Toda mudanca roda numa transacao com a linha do job travada (FOR UPDATE):
 * duas acoes ao mesmo tempo no mesmo job sao serializadas.
 */
final class ProvisioningEngine
{
    /** Tipos que param o job esperando o TI (sem executor). */
    private const HUMAN_TYPES = [StepType::ENTRA_LOGIN, StepType::MANUAL_INTERVENTION];

    /** Resultados aceitos para a etapa atual. */
    public const RESULTS = [JobStep::SUCCESS, JobStep::FAILED, JobStep::WAITING_HUMAN, JobStep::SKIPPED];

    private const DEFAULT_TIMEOUT_MINUTES = 30;
    private const DEFAULT_ATTEMPTS        = 3;

    // ------------------------------------------------------------ criacao

    /**
     * Cria o provisionamento, copia as etapas (snapshot) e inicia.
     *
     * @return int id do job
     * @throws RuntimeException mensagem pronta para o usuario
     */
    public static function create(int $computersId, int $profileId, string $employeeName, string $upn): int
    {
        global $DB;

        if (!Session::haveRight(Job::$rightname, CREATE)) {
            throw new RuntimeException('Você não tem permissão para provisionar.');
        }

        $computer = new Computer();
        if ($computersId <= 0 || !$computer->getFromDB($computersId) || !$computer->can($computersId, READ)) {
            throw new RuntimeException('Computador não encontrado ou fora das suas entidades.');
        }

        $profile = new ProvisioningProfile();
        if (
            $profileId <= 0
            || !$profile->getFromDB($profileId)
            || (int) $profile->fields['is_active'] !== 1
            || !Session::haveAccessToEntity((int) $profile->fields['entities_id'], (bool) $profile->fields['is_recursive'])
        ) {
            throw new RuntimeException('Perfil de provisionamento inválido ou inativo.');
        }

        $employeeName = trim($employeeName);
        if ($employeeName === '' || mb_strlen($employeeName) > 255) {
            throw new RuntimeException('Informe o nome do funcionário.');
        }

        $steps = self::profileSteps($profileId);
        if ($steps === []) {
            throw new RuntimeException('O perfil não tem etapas ativas.');
        }

        // So o UPN/e-mail corporativo: nenhuma senha ou credencial e guardada.
        $upn = mb_strtolower(trim($upn));
        if ($upn !== '' && (mb_strlen($upn) > 255 || filter_var($upn, FILTER_VALIDATE_EMAIL) === false)) {
            throw new RuntimeException('Conta Microsoft inválida (use o formato nome@empresa.com.br).');
        }
        foreach ($steps as $step) {
            if ($step['step_type'] === StepType::ENTRA_LOGIN && $upn === '') {
                throw new RuntimeException('Este perfil tem Autenticação Microsoft: informe a conta Microsoft do funcionário.');
            }
        }

        $DB->beginTransaction();
        try {
            // Trava o computador (so leitura/lock, sem alterar a tabela nativa):
            // dois pedidos simultaneos para a mesma maquina nao passam juntos.
            $DB->doQuery('SELECT `id` FROM `glpi_computers` WHERE `id` = ' . (int) $computersId . ' FOR UPDATE');
            if (countElementsInTable(Job::getTable(), ['computers_id' => $computersId, 'status' => Job::ACTIVE]) > 0) {
                throw new RuntimeException('Este computador já possui um provisionamento ativo.');
            }

            $job = new Job();
            $jobId = (int) $job->add([
                'entities_id'   => (int) $computer->fields['entities_id'],
                'computers_id'  => $computersId,
                ProfileStep::PROFILE_FK => $profileId,
                'profile_name'  => (string) $profile->fields['name'],
                'status'        => Job::QUEUED,
                'employee_name' => $employeeName,
                'upn'           => $upn,
                'users_id'      => (int) Session::getLoginUserID(),
                'progress'      => 0,
                'message'       => 'Na fila.',
            ]);
            if ($jobId <= 0) {
                throw new RuntimeException('Não foi possível criar o provisionamento.');
            }

            self::snapshotSteps($jobId, $profile, $steps);

            Event::log(Event::LEVEL_INFO, 'provisioning', 'Job criado', [
                'computador'  => (string) $computer->fields['name'],
                'perfil'      => (string) $profile->fields['name'],
                'funcionario' => $employeeName,
                'upn'         => $upn,
                'etapas'      => count($steps),
            ], $jobId);

            self::start($jobId);
            $DB->commit();
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw $exception instanceof RuntimeException
                ? $exception
                : new RuntimeException('Não foi possível criar o provisionamento.', 0, $exception);
        }

        return $jobId;
    }

    /**
     * Etapas ativas do perfil com o aplicativo resolvido.
     *
     * @return list<array<string, mixed>>
     */
    private static function profileSteps(int $profileId): array
    {
        return array_values(array_filter(
            ProfileStep::forProfile($profileId),
            static fn (array $step): bool => (int) $step['is_active'] === 1
        ));
    }

    /**
     * Snapshot: cada etapa do perfil vira um JobStep independente, com a
     * configuracao e os metadados do aplicativo copiados e os valores
     * efetivos (timeout/tentativas) ja resolvidos.
     *
     * @param list<array<string, mixed>> $steps
     */
    private static function snapshotSteps(int $jobId, ProvisioningProfile $profile, array $steps): void
    {
        $model = new JobStep();
        $position = 0;
        foreach ($steps as $step) {
            $application = null;
            $appTimeout = self::DEFAULT_TIMEOUT_MINUTES;
            $appAttempts = self::DEFAULT_ATTEMPTS;
            $appId = (int) $step[ProfileStep::APPLICATION_FK];
            if ($step['step_type'] === StepType::SOFTWARE && $appId > 0) {
                $app = new Application();
                if ($app->getFromDB($appId)) {
                    $application = [
                        'id'              => $appId,
                        'name'            => (string) $app->fields['name'],
                        'version'         => (string) $app->fields['desired_version'],
                        'installer_type'  => (string) $app->fields['installer_type'],
                        'architecture'    => (string) $app->fields['architecture'],
                        'file_name'       => (string) $app->fields['file_name'],
                        'file_sha256'     => (string) $app->fields['file_sha256'],
                        'file_size'       => (int) $app->fields['file_size'],
                        'requires_reboot' => (int) $app->fields['requires_reboot'] === 1,
                        'expected_signer' => (string) $app->fields['expected_signer'],
                    ];
                    $appTimeout = max(1, (int) $app->fields['timeout_minutes']);
                    $appAttempts = max(1, (int) $app->fields['max_attempts']);
                }
            }

            $human = in_array($step['step_type'], self::HUMAN_TYPES, true);
            $timeout = (int) $step['timeout_minutes'] > 0 ? (int) $step['timeout_minutes'] : ($human ? 0 : $appTimeout);
            $attempts = (int) $step['max_attempts'] > 0 ? (int) $step['max_attempts'] : ($human ? 1 : $appAttempts);

            $id = (int) $model->add([
                JobStep::JOB_FK                         => $jobId,
                'plugin_ativaworkspace_profilesteps_id' => (int) $step['id'],
                ProfileStep::APPLICATION_FK             => $appId,
                'name'                                  => (string) $step['name'],
                'step_type'                             => (string) $step['step_type'],
                'step_order'                            => ++$position,
                'status'                                => JobStep::PENDING,
                'config'                                => $step['config'],
                'snapshot'                              => json_encode([
                    'profile'     => ['id' => (int) $profile->getID(), 'name' => (string) $profile->fields['name']],
                    'application' => $application,
                    'config'      => $step['config_values'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'is_mandatory'      => (int) $step['is_mandatory'],
                'continue_on_error' => (int) $step['continue_on_error'],
                'timeout_minutes'   => $timeout,
                'max_attempts'      => $attempts,
                'attempts'          => 0,
            ]);
            if ($id <= 0) {
                throw new RuntimeException('Não foi possível copiar as etapas do perfil.');
            }
        }
    }

    /** QUEUED -> RUNNING e primeira etapa. Chamado dentro da transacao da criacao. */
    private static function start(int $jobId): void
    {
        self::updateJob($jobId, [
            'status'     => Job::RUNNING,
            'date_start' => self::now(),
            'message'    => 'Provisionamento iniciado.',
        ]);
        Event::log(Event::LEVEL_INFO, 'provisioning', 'Provisionamento iniciado', [], $jobId);
        self::advance($jobId);
    }

    // --------------------------------------------------------- transicoes

    /**
     * Registra o resultado da etapa atual e avanca o job.
     * Na Etapa 3 so e chamado pela simulacao; o executor usara o mesmo caminho.
     *
     * @param array<string, mixed> $metadata
     */
    public static function recordResult(int $jobId, int $stepId, string $result, string $message = '', array $metadata = []): void
    {
        if (!in_array($result, self::RESULTS, true)) {
            throw new RuntimeException('Resultado de etapa inválido.');
        }

        self::locked($jobId, static function (array $job) use ($jobId, $stepId, $result, $message, $metadata): void {
            if (!in_array($job['status'], [Job::RUNNING, Job::WAITING_INTERVENTION], true)) {
                throw new RuntimeException('O provisionamento não está em andamento.');
            }
            $step = self::currentStep($job, $stepId);
            if ($result === JobStep::WAITING_HUMAN && $step['status'] === JobStep::WAITING_HUMAN) {
                return; // ja esta aguardando
            }

            $name = (string) $step['name'];
            $fields = ['status' => $result, 'message' => mb_substr($message, 0, 2000)];
            if (in_array($result, JobStep::FINAL, true)) {
                $fields['date_end'] = self::now();
            }
            self::updateStep((int) $step['id'], $fields);

            match ($result) {
                JobStep::SUCCESS => Event::log(Event::LEVEL_INFO, 'provisioning', 'Etapa concluída: ' . $name, $metadata, $jobId, (int) $step['id']),
                JobStep::SKIPPED => Event::log(
                    (int) $step['is_mandatory'] === 1 ? Event::LEVEL_WARNING : Event::LEVEL_INFO,
                    'provisioning',
                    'Etapa ignorada: ' . $name,
                    $metadata + ['obrigatoria' => (int) $step['is_mandatory'] === 1],
                    $jobId,
                    (int) $step['id']
                ),
                JobStep::FAILED => Event::log(
                    (int) $step['continue_on_error'] === 1 ? Event::LEVEL_WARNING : Event::LEVEL_ERROR,
                    'provisioning',
                    'Etapa falhou: ' . $name . ((int) $step['continue_on_error'] === 1 ? ' (continuando)' : ''),
                    $metadata + ['motivo' => $message, 'tentativa' => (int) $step['attempts'], 'max_tentativas' => (int) $step['max_attempts']],
                    $jobId,
                    (int) $step['id']
                ),
                JobStep::WAITING_HUMAN => Event::log(Event::LEVEL_WARNING, 'provisioning', 'Aguardando intervenção: ' . $name, $metadata, $jobId, (int) $step['id']),
            };

            self::advance($jobId);
        });
    }

    /** O TI confirma que concluiu a intervencao pedida pela etapa atual. */
    public static function confirmIntervention(int $jobId, int $stepId): void
    {
        self::locked($jobId, static function (array $job) use ($stepId): void {
            $step = self::currentStep($job, $stepId);
            if ($step['status'] !== JobStep::WAITING_HUMAN) {
                throw new RuntimeException('Esta etapa não está aguardando intervenção.');
            }
        });
        self::recordResult($jobId, $stepId, JobStep::SUCCESS, 'Intervenção confirmada pelo TI.', ['confirmado_por' => (int) Session::getLoginUserID()]);
    }

    /**
     * Tentar de novo uma etapa que falhou e parou o job. Respeita max_attempts:
     * a tentativa conta quando a etapa e reiniciada pelo advance().
     */
    public static function retry(int $jobId, int $stepId): void
    {
        self::locked($jobId, static function (array $job) use ($jobId, $stepId): void {
            if ($job['status'] !== Job::FAILED) {
                throw new RuntimeException('Só é possível tentar de novo um provisionamento com falha.');
            }
            $step = self::currentStep($job, $stepId);
            if ($step['status'] !== JobStep::FAILED) {
                throw new RuntimeException('Só etapas com falha podem ser repetidas.');
            }
            if ((int) $step['attempts'] >= (int) $step['max_attempts']) {
                throw new RuntimeException(sprintf(
                    'Limite de tentativas atingido (%d de %d).',
                    (int) $step['attempts'],
                    (int) $step['max_attempts']
                ));
            }

            self::updateStep((int) $step['id'], ['status' => JobStep::PENDING, 'message' => '', 'date_end' => null]);
            self::updateJob($jobId, ['status' => Job::RUNNING, 'date_end' => null, 'message' => 'Nova tentativa: ' . $step['name']]);
            Event::log(Event::LEVEL_INFO, 'provisioning', 'Nova tentativa: ' . $step['name'], [
                'tentativa' => (int) $step['attempts'] + 1,
                'max'       => (int) $step['max_attempts'],
            ], $jobId, (int) $step['id']);

            self::advance($jobId);
        });
    }

    /** Cancela: job -> CANCELED e toda etapa ainda nao finalizada -> CANCELED. */
    public static function cancel(int $jobId): void
    {
        self::locked($jobId, static function (array $job) use ($jobId): void {
            if (!in_array($job['status'], array_merge(Job::ACTIVE, [Job::FAILED]), true)) {
                throw new RuntimeException('Este provisionamento já foi encerrado.');
            }
            $canceled = 0;
            foreach (JobStep::forJob($jobId) as $step) {
                if (!in_array($step['status'], [JobStep::SUCCESS, JobStep::FAILED, JobStep::SKIPPED], true)) {
                    self::updateStep((int) $step['id'], ['status' => JobStep::CANCELED, 'date_end' => self::now()]);
                    $canceled++;
                }
            }
            self::updateJob($jobId, [
                'status'   => Job::CANCELED,
                'date_end' => self::now(),
                'message'  => 'Cancelado por ' . getUserName((int) Session::getLoginUserID()) . '.',
                'plugin_ativaworkspace_jobsteps_id' => 0,
            ]);
            Event::log(Event::LEVEL_WARNING, 'provisioning', 'Provisionamento cancelado', ['etapas_canceladas' => $canceled], $jobId);
        });
    }

    /**
     * Decide a etapa atual e o estado do job a partir das etapas. Idempotente:
     * pode ser chamado a qualquer momento (acoes, cron de reconciliacao).
     * Deve rodar com o job travado.
     */
    public static function advance(int $jobId): void
    {
        $steps = JobStep::forJob($jobId);
        $progress = self::progress($steps);

        $current = null;
        foreach ($steps as $step) {
            if (!JobStep::isResolved($step)) {
                $current = $step;
                break;
            }
        }

        // Todas resolvidas: concluido.
        if ($current === null) {
            self::updateJob($jobId, [
                'status'   => Job::COMPLETED,
                'progress' => 100,
                'date_end' => self::now(),
                'message'  => 'Provisionamento concluído.',
                'plugin_ativaworkspace_jobsteps_id' => 0,
            ]);
            Event::log(Event::LEVEL_INFO, 'provisioning', 'Provisionamento concluído', ['etapas' => count($steps)], $jobId);
            return;
        }

        $currentId = (int) $current['id'];
        $name = (string) $current['name'];

        switch ($current['status']) {
            case JobStep::FAILED:
                // continue_on_error = 0 (senao estaria resolvida): o job para.
                self::updateJob($jobId, [
                    'status'   => Job::FAILED,
                    'progress' => $progress,
                    'date_end' => self::now(),
                    'message'  => 'Falhou em "' . $name . '"' . ((string) $current['message'] !== '' ? ': ' . $current['message'] : '.'),
                    'plugin_ativaworkspace_jobsteps_id' => $currentId,
                ]);
                Event::log(Event::LEVEL_ERROR, 'provisioning', 'Provisionamento falhou: ' . $name, [
                    'motivo' => (string) $current['message'],
                ], $jobId, $currentId);
                return;

            case JobStep::WAITING_HUMAN:
                self::updateJob($jobId, [
                    'status'   => Job::WAITING_INTERVENTION,
                    'progress' => $progress,
                    'message'  => 'Aguardando intervenção do TI: ' . $name,
                    'plugin_ativaworkspace_jobsteps_id' => $currentId,
                ]);
                return;

            case JobStep::PENDING:
                self::startStep($jobId, $current, $progress);
                return;

            default:
                // QUEUED / RUNNING / VERIFYING: em andamento, aguardando resultado.
                self::updateJob($jobId, [
                    'status'   => Job::RUNNING,
                    'progress' => $progress,
                    'plugin_ativaworkspace_jobsteps_id' => $currentId,
                ]);
        }
    }

    /** PENDING -> WAITING_HUMAN (etapas humanas) ou QUEUED (aguarda o executor). */
    private static function startStep(int $jobId, array $step, int $progress): void
    {
        $stepId = (int) $step['id'];
        $human = in_array($step['step_type'], self::HUMAN_TYPES, true);
        $config = json_decode((string) ($step['config'] ?? ''), true);
        $instructions = is_array($config) ? (string) ($config['message'] ?? '') : '';

        self::updateStep($stepId, [
            'status'     => $human ? JobStep::WAITING_HUMAN : JobStep::QUEUED,
            'attempts'   => (int) $step['attempts'] + 1,
            'date_start' => self::now(),
            'date_end'   => null,
            'message'    => $human ? $instructions : 'Aguardando o executor.',
        ]);
        Event::log(Event::LEVEL_INFO, 'provisioning', 'Etapa iniciada: ' . $step['name'], [
            'tipo'      => (string) $step['step_type'],
            'tentativa' => (int) $step['attempts'] + 1,
        ], $jobId, $stepId);

        if ($human) {
            Event::log(Event::LEVEL_WARNING, 'provisioning', 'Aguardando intervenção: ' . $step['name'], [
                'instrucoes' => $instructions,
            ], $jobId, $stepId);
        }

        self::updateJob($jobId, [
            'status'   => $human ? Job::WAITING_INTERVENTION : Job::RUNNING,
            'progress' => $progress,
            'message'  => $human ? 'Aguardando intervenção do TI: ' . $step['name'] : 'Executando: ' . $step['name'],
            'plugin_ativaworkspace_jobsteps_id' => $stepId,
        ]);
    }

    /**
     * Progresso = etapas resolvidas / total, entre 0 e 100.
     *
     * @param list<array<string, mixed>> $steps
     */
    public static function progress(array $steps): int
    {
        if ($steps === []) {
            return 0;
        }
        $done = 0;
        foreach ($steps as $step) {
            if (JobStep::isResolved($step)) {
                $done++;
            }
        }
        return max(0, min(100, (int) floor(100 * $done / count($steps))));
    }

    // ---------------------------------------------------------------- cron

    public static function cronInfo(string $name): array
    {
        return $name === 'ProcessJobs'
            ? ['description' => 'Ativa Workspace: reconcilia os provisionamentos ativos (etapa atual, progresso e timeouts)']
            : [];
    }

    /**
     * Tarefa automatica: percorre os jobs ativos, fecha etapas em execucao que
     * passaram do timeout e reconcilia estado/progresso. Etapas QUEUED (sem
     * executor nesta etapa) nao expiram.
     */
    public static function cronProcessJobs(CronTask $task): int
    {
        global $DB;

        $processed = 0;
        foreach ($DB->request([
            'SELECT' => ['id'],
            'FROM'   => Job::getTable(),
            'WHERE'  => ['status' => Job::ACTIVE],
            'LIMIT'  => 200,
        ]) as $row) {
            try {
                self::reconcile((int) $row['id']);
                $processed++;
            } catch (Throwable $exception) {
                $task->log('Job #' . (int) $row['id'] . ': ' . $exception->getMessage());
            }
        }
        $task->addVolume($processed);
        return $processed > 0 ? 1 : 0;
    }

    private static function reconcile(int $jobId): void
    {
        self::locked($jobId, static function (array $job) use ($jobId): void {
            if (!in_array($job['status'], Job::ACTIVE, true)) {
                return;
            }
            $now = time();
            foreach (JobStep::forJob($jobId) as $step) {
                $timeout = (int) $step['timeout_minutes'];
                $started = strtotime((string) ($step['date_start'] ?? ''));
                if (
                    in_array($step['status'], [JobStep::RUNNING, JobStep::VERIFYING], true)
                    && $timeout > 0 && $started !== false && $now - $started > $timeout * 60
                ) {
                    self::updateStep((int) $step['id'], [
                        'status'   => JobStep::FAILED,
                        'date_end' => self::now(),
                        'message'  => 'Tempo esgotado (' . $timeout . ' min).',
                    ]);
                    Event::log(Event::LEVEL_ERROR, 'provisioning', 'Etapa falhou: ' . $step['name'] . ' (tempo esgotado)', [
                        'timeout_min' => $timeout,
                    ], $jobId, (int) $step['id']);
                }
            }
            self::advance($jobId);
        });
    }

    // ------------------------------------------------------------ apoio

    /**
     * Executa $work com o job travado numa transacao (reentrante: dentro de
     * outra transacao usa savepoint).
     *
     * @param callable(array<string, mixed>): void $work
     */
    private static function locked(int $jobId, callable $work): void
    {
        global $DB;

        $DB->beginTransaction();
        try {
            $DB->doQuery('SELECT `id` FROM `' . Job::getTable() . '` WHERE `id` = ' . (int) $jobId . ' FOR UPDATE');
            $job = $DB->request(['FROM' => Job::getTable(), 'WHERE' => ['id' => $jobId], 'LIMIT' => 1])->current();
            if (!is_array($job)) {
                throw new RuntimeException('Provisionamento não encontrado.');
            }
            $work($job);
            $DB->commit();
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw $exception;
        }
    }

    /** A etapa informada tem que ser a etapa atual do job. */
    private static function currentStep(array $job, int $stepId): array
    {
        global $DB;

        $step = $DB->request([
            'FROM'  => JobStep::getTable(),
            'WHERE' => ['id' => $stepId, JobStep::JOB_FK => (int) $job['id']],
            'LIMIT' => 1,
        ])->current();
        if (!is_array($step)) {
            throw new RuntimeException('Etapa não encontrada neste provisionamento.');
        }
        if ((int) $job['plugin_ativaworkspace_jobsteps_id'] !== $stepId) {
            throw new RuntimeException('Só a etapa atual pode ser alterada.');
        }
        return $step;
    }

    /** @param array<string, mixed> $fields */
    private static function updateJob(int $jobId, array $fields): void
    {
        global $DB;
        $DB->update(Job::getTable(), $fields + ['date_mod' => self::now()], ['id' => $jobId]);
    }

    /** @param array<string, mixed> $fields */
    private static function updateStep(int $stepId, array $fields): void
    {
        global $DB;
        $DB->update(JobStep::getTable(), $fields + ['date_mod' => self::now()], ['id' => $stepId]);
    }

    private static function now(): string
    {
        return $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
    }
}
