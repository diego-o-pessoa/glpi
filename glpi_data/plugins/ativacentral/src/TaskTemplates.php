<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Config;
use Group_User;
use ProjectTaskTemplate;
use ProjectTaskType;
use Session;

/**
 * Modelos de tarefa (tabela nativa glpi_projecttasktemplates) com tela
 * propria, so para o T.I. Um modelo e "fase" quando o tipo dele e "Fase".
 */
final class TaskTemplates
{
    private const SEED_KEY = 'task_templates_seeded';
    private const PHASE_TYPE = 'Fase';

    public static function url(): string
    {
        return Shell::url('/front/task.templates.php');
    }

    /** So o T.I.: quem e do grupo "T.I." ou pode configurar o GLPI. */
    public static function canManage(): bool
    {
        if (Session::haveRight('config', UPDATE)) {
            return true;
        }
        foreach (Group_User::getUserGroups((int) Session::getLoginUserID()) as $group) {
            $name = mb_strtolower(trim((string) ($group['name'] ?? '')));
            if (preg_match('/^t\.?\s*i\.?$/u', $name) === 1) {
                return true;
            }
        }
        return false;
    }

    /** @return list<array{id: int, name: string, is_phase: bool, type_id: int, type: string, description: string, summary: string}> */
    public static function all(): array
    {
        global $DB;
        self::seed();
        $phaseType = self::phaseTypeId(false);
        $types = [];
        foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_projecttasktypes']) as $row) {
            $types[(int) $row['id']] = (string) $row['name'];
        }
        $list = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'description', 'projecttasktypes_id'],
            'FROM'   => 'glpi_projecttasktemplates',
            'WHERE'  => getEntitiesRestrictCriteria('glpi_projecttasktemplates', '', '', true),
            'ORDER'  => 'name ASC',
        ]) as $row) {
            $typeId = (int) $row['projecttasktypes_id'];
            $text = trim(html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br />', '</li>'], ' ', (string) $row['description'])), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $list[] = [
                'id'          => (int) $row['id'],
                'name'        => (string) $row['name'],
                'is_phase'    => $phaseType > 0 && $typeId === $phaseType,
                'type_id'     => $typeId,
                'type'        => $types[$typeId] ?? '',
                'description' => (string) $row['description'],
                'summary'     => mb_strimwidth((string) preg_replace('/\s+/u', ' ', $text), 0, 110, '…'),
            ];
        }
        return $list;
    }

    /** @throws \InvalidArgumentException */
    public static function save(int $id, array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome ao modelo.');
        }
        $isPhase = ($input['tpl_kind'] ?? 'task') === 'phase';
        $typeId = max(0, (int) ($input['projecttasktypes_id'] ?? 0));
        if (!$isPhase && $typeId > 0 && $typeId === self::phaseTypeId(false)) {
            // Virou tarefa: o tipo "Fase" (que ficava no campo escondido) sai,
            // senao o modelo continuava sendo fase.
            $typeId = 0;
        }
        $fields = [
            'name'                => $name,
            'description'         => (string) ($input['description'] ?? ''),
            'projecttasktypes_id' => $isPhase ? self::phaseTypeId(true) : $typeId,
            'is_milestone'        => 0,
        ];
        $template = new ProjectTaskTemplate();
        if ($id > 0) {
            if (!$template->getFromDB($id) || !$template->update(['id' => $id] + $fields)) {
                throw new \InvalidArgumentException('Não foi possível salvar o modelo.');
            }
            return $id;
        }
        $newId = (int) $template->add($fields + ['entities_id' => 0, 'is_recursive' => 1]);
        if ($newId <= 0) {
            throw new \InvalidArgumentException('Não foi possível criar o modelo.');
        }
        return $newId;
    }

    public static function delete(int $id): void
    {
        $template = new ProjectTaskTemplate();
        if ($id > 0 && $template->getFromDB($id)) {
            $template->delete(['id' => $id], true);
        }
    }

    public static function phaseTypeId(bool $create): int
    {
        global $DB;
        $row = $DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_projecttasktypes', 'WHERE' => ['name' => self::PHASE_TYPE], 'LIMIT' => 1])->current();
        if ($row !== null) {
            return (int) $row['id'];
        }
        if (!$create) {
            return 0;
        }
        $type = new ProjectTaskType();
        return (int) $type->add(['name' => self::PHASE_TYPE, 'entities_id' => 0, 'is_recursive' => 1]);
    }

    /** Cria o modelo "Planejamento" uma unica vez (se apagarem, nao volta). */
    private static function seed(): void
    {
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::SEED_KEY]);
        if (!empty($config[self::SEED_KEY])) {
            return;
        }
        Config::setConfigurationValues(Settings::CONTEXT, [self::SEED_KEY => 1]);
        if (countElementsInTable('glpi_projecttasktemplates', ['name' => 'Planejamento']) > 0) {
            return;
        }
        $template = new ProjectTaskTemplate();
        $template->add([
            'name'                => 'Planejamento',
            'projecttasktypes_id' => self::phaseTypeId(true),
            'entities_id'         => 0,
            'is_recursive'        => 1,
            'is_milestone'        => 0,
            'description'         => '<p>Fase inicial do projeto: entender o que precisa ser feito e organizar o trabalho.</p>'
                . '<ul><li>Levantar com os envolvidos o objetivo e o que entra no projeto</li>'
                . '<li>Definir as tarefas e quem cuida de cada uma</li>'
                . '<li>Combinar prazos e datas de entrega</li>'
                . '<li>Apresentar o plano e alinhar com o responsável</li></ul>',
        ]);
    }
}
