<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;
use RuntimeException;
use Throwable;

/**
 * Etapa de um perfil de provisionamento (glpi_plugin_ativaworkspace_profilesteps).
 *
 * Ordem: step_order vai de 1 a N, sem buracos nem repeticao. Toda operacao
 * que mexe na ordem roda numa transacao com o perfil pai travado (FOR UPDATE),
 * entao duas requisicoes ao mesmo tempo nao deixam a sequencia inconsistente.
 */
final class ProfileStep extends CommonDBTM
{
    public const PROFILE_FK     = 'plugin_ativaworkspace_provisioningprofiles_id';
    public const APPLICATION_FK = 'plugin_ativaworkspace_applications_id';

    public const TIMEOUT_MAX_MINUTES = 240;
    public const ATTEMPTS_MAX        = 10;

    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_PROFILES;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Etapas do perfil' : 'Etapa do perfil';
    }

    /**
     * Etapas de um perfil, em ordem, com o aplicativo resolvido.
     *
     * @return list<array<string, mixed>>
     */
    public static function forProfile(int $profileId): array
    {
        global $DB;

        $table = self::getTable();
        $apps  = Application::getTable();
        $steps = [];
        foreach ($DB->request([
            'SELECT'    => [
                "$table.*",
                "$apps.name AS application_name",
                "$apps.is_active AS application_active",
                "$apps.file_stored_name AS application_file",
                "$apps.icon AS application_icon",
                "$apps.category AS application_category",
            ],
            'FROM'      => $table,
            'LEFT JOIN' => [
                $apps => ['ON' => [$apps => 'id', $table => self::APPLICATION_FK]],
            ],
            'WHERE'     => ["$table." . self::PROFILE_FK => $profileId],
            'ORDER'     => ["$table.step_order ASC", "$table.id ASC"],
        ]) as $row) {
            $config = json_decode((string) ($row['config'] ?? ''), true);
            $config = is_array($config) ? $config : [];
            $row['config_values'] = $config;
            $row['type_label']    = StepType::label((string) $row['step_type']);
            $row['type_icon']     = (string) $row['step_type'] === StepType::SOFTWARE && (int) $row[self::APPLICATION_FK] > 0
                ? Application::iconFor(['icon' => $row['application_icon'], 'category' => $row['application_category']])
                : StepType::icon((string) $row['step_type']);
            $row['summary']       = StepType::summary((string) $row['step_type'], $config);
            $row['warning']       = self::warningFor($row);
            $steps[] = $row;
        }
        return $steps;
    }

    /** Aviso de consistencia para a tela (ex.: aplicativo inativo ou sem arquivo). */
    private static function warningFor(array $row): string
    {
        if ((string) $row['step_type'] !== StepType::SOFTWARE) {
            return '';
        }
        if ($row['application_name'] === null) {
            return 'Aplicativo removido do catálogo.';
        }
        if ((int) $row['application_active'] !== 1) {
            return 'Aplicativo inativo no catálogo.';
        }
        if ((string) $row['application_file'] === '') {
            return 'Aplicativo ainda sem instalador enviado.';
        }
        return '';
    }

    /**
     * Valida os dados do formulario da etapa.
     *
     * @param array<string, mixed> $post
     * @return array<string, mixed> campos prontos para gravar
     * @throws RuntimeException mensagem pronta para o usuario
     */
    public static function inputFromForm(array $post): array
    {
        $type = (string) ($post['step_type'] ?? '');
        if (!StepType::exists($type)) {
            throw new RuntimeException('Tipo de etapa inválido.');
        }
        $meta = StepType::all()[$type];

        $applicationId = 0;
        if ($meta['application']) {
            $applicationId = (int) ($post[self::APPLICATION_FK] ?? 0);
            $application = new Application();
            if ($applicationId <= 0 || !$application->getFromDB($applicationId)) {
                throw new RuntimeException('Escolha um aplicativo do catálogo.');
            }
        }

        $name = trim((string) ($post['name'] ?? ''));
        if ($name === '' && isset($application)) {
            $name = (string) $application->fields['name'];
        }
        if ($name === '') {
            throw new RuntimeException('Informe o nome da etapa.');
        }

        // O formulario manda config[TIPO][campo]: so a do tipo escolhido conta.
        $rawConfig = $post['config'][$type] ?? [];
        try {
            $config = StepType::normalizeConfig($type, is_array($rawConfig) ? $rawConfig : []);
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        $timeout  = (int) ($post['timeout_minutes'] ?? 0);
        $attempts = (int) ($post['max_attempts'] ?? 0);
        if ($timeout < 0 || $timeout > self::TIMEOUT_MAX_MINUTES) {
            throw new RuntimeException('O timeout deve ficar entre 0 (padrão) e ' . self::TIMEOUT_MAX_MINUTES . ' minutos.');
        }
        if ($attempts < 0 || $attempts > self::ATTEMPTS_MAX) {
            throw new RuntimeException('As tentativas devem ficar entre 0 (padrão) e ' . self::ATTEMPTS_MAX . '.');
        }

        return [
            'name'                => mb_substr($name, 0, 255),
            'step_type'           => $type,
            self::APPLICATION_FK  => $applicationId,
            'config'              => $config === [] ? null : json_encode($config, JSON_UNESCAPED_UNICODE),
            'is_mandatory'        => (int) (bool) ($post['is_mandatory'] ?? 0),
            'continue_on_error'   => (int) (bool) ($post['continue_on_error'] ?? 0),
            'timeout_minutes'     => $timeout,
            'max_attempts'        => $attempts,
            'is_active'           => 1,
        ];
    }

    /** Adiciona uma etapa no fim do perfil. */
    public static function append(int $profileId, array $input): int
    {
        return self::locked($profileId, static function () use ($profileId, $input): int {
            global $DB;

            $max = $DB->request([
                'SELECT' => ['MAX' => 'step_order AS last'],
                'FROM'   => self::getTable(),
                'WHERE'  => [self::PROFILE_FK => $profileId],
            ])->current();

            $id = (int) (new self())->add([self::PROFILE_FK => $profileId, 'step_order' => (int) ($max['last'] ?? 0) + 1] + $input);
            if ($id <= 0) {
                throw new RuntimeException('Não foi possível adicionar a etapa.');
            }
            self::normalizeOrder($profileId);
            Event::log(Event::LEVEL_INFO, 'profile', 'Etapa adicionada: ' . $input['name'], ['perfil' => $profileId, 'etapa' => $id, 'tipo' => $input['step_type']]);
            return $id;
        });
    }

    /** Altera os dados de uma etapa (a posicao nao muda aqui). */
    public static function change(int $profileId, int $stepId, array $input): void
    {
        self::locked($profileId, static function () use ($profileId, $stepId, $input): int {
            $step = self::mustFind($profileId, $stepId);
            if (!$step->update(['id' => $stepId] + $input)) {
                throw new RuntimeException('Não foi possível salvar a etapa.');
            }
            Event::log(Event::LEVEL_INFO, 'profile', 'Etapa alterada: ' . $input['name'], ['perfil' => $profileId, 'etapa' => $stepId]);
            return $stepId;
        });
    }

    /** Move uma etapa uma posicao para cima (-1) ou para baixo (+1). */
    public static function move(int $profileId, int $stepId, int $direction): void
    {
        self::locked($profileId, static function () use ($profileId, $stepId, $direction): int {
            global $DB;

            self::mustFind($profileId, $stepId);
            self::normalizeOrder($profileId);

            $ids = self::orderedIds($profileId);
            $index = array_search($stepId, $ids, true);
            $target = $index === false ? false : $index + ($direction < 0 ? -1 : 1);
            if ($target === false || $target < 0 || $target >= count($ids)) {
                return $stepId; // ja esta na ponta: nada a fazer
            }
            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
            foreach ($ids as $position => $id) {
                $DB->update(self::getTable(), ['step_order' => $position + 1], ['id' => $id]);
            }
            return $stepId;
        });
    }

    /** Exclui uma etapa e fecha o buraco na sequencia. */
    public static function remove(int $profileId, int $stepId): void
    {
        self::locked($profileId, static function () use ($profileId, $stepId): int {
            $step = self::mustFind($profileId, $stepId);
            $name = (string) $step->fields['name'];
            if (!$step->delete(['id' => $stepId], true)) {
                throw new RuntimeException('Não foi possível excluir a etapa.');
            }
            self::normalizeOrder($profileId);
            Event::log(Event::LEVEL_WARNING, 'profile', 'Etapa excluída: ' . $name, ['perfil' => $profileId, 'etapa' => $stepId]);
            return $stepId;
        });
    }

    /**
     * Copia todas as etapas de um perfil para outro (novos IDs, mesma ordem).
     * Chamado dentro da transacao da duplicacao do perfil.
     */
    public static function copyAll(int $fromProfileId, int $toProfileId): int
    {
        global $DB;

        $copied = 0;
        $model = new self();
        foreach ($DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => [self::PROFILE_FK => $fromProfileId],
            'ORDER' => ['step_order ASC', 'id ASC'],
        ]) as $row) {
            $copy = $row;
            unset($copy['id'], $copy['date_creation'], $copy['date_mod']);
            $copy[self::PROFILE_FK] = $toProfileId;
            if ((int) $model->add($copy) <= 0) {
                throw new RuntimeException('Não foi possível copiar as etapas do perfil.');
            }
            $copied++;
        }
        self::normalizeOrder($toProfileId);
        return $copied;
    }

    /** Reescreve step_order como 1..N na ordem atual. */
    public static function normalizeOrder(int $profileId): void
    {
        global $DB;

        foreach (self::orderedIds($profileId) as $position => $id) {
            $DB->update(self::getTable(), ['step_order' => $position + 1], ['id' => $id]);
        }
    }

    /**
     * @return list<int>
     */
    private static function orderedIds(int $profileId): array
    {
        global $DB;

        $ids = [];
        foreach ($DB->request([
            'SELECT' => ['id'],
            'FROM'   => self::getTable(),
            'WHERE'  => [self::PROFILE_FK => $profileId],
            'ORDER'  => ['step_order ASC', 'id ASC'],
        ]) as $row) {
            $ids[] = (int) $row['id'];
        }
        return $ids;
    }

    private static function mustFind(int $profileId, int $stepId): self
    {
        $step = new self();
        if (!$step->getFromDB($stepId) || (int) $step->fields[self::PROFILE_FK] !== $profileId) {
            throw new RuntimeException('Etapa não encontrada neste perfil.');
        }
        return $step;
    }

    /**
     * Executa $work numa transacao com o perfil travado. Qualquer erro desfaz tudo.
     *
     * @param callable(): int $work
     */
    public static function locked(int $profileId, callable $work): int
    {
        global $DB;

        $DB->beginTransaction();
        try {
            // Trava a linha do perfil: operacoes concorrentes no mesmo perfil esperam.
            $DB->doQuery(
                'SELECT `id` FROM `' . ProvisioningProfile::getTable() . '` WHERE `id` = ' . (int) $profileId . ' FOR UPDATE'
            );
            $result = $work();
            $DB->commit();
            return $result;
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw $exception;
        }
    }
}
