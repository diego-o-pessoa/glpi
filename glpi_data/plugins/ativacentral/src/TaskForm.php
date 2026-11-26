<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Group;
use Html;
use Project;
use ProjectTask;
use ProjectTaskTeam;
use ProjectTaskType;
use Session;
use User;

/**
 * Tela simplificada de tarefa do projeto (Etapa 4): criar e editar.
 * Estado e percentual sao automaticos (botoes de status na edicao);
 * datas sem hora; equipe a partir da equipe do projeto.
 */
final class TaskForm
{
    public const STATUSES = [
        'todo'    => ['Não iniciada', 'ti-circle'],
        'running' => ['Em andamento', 'ti-circle-half-2'],
        'done'    => ['Concluída', 'ti-circle-check'],
    ];

    public static function newUrl(int $projectId): string
    {
        return Shell::url('/front/task.form.php?projects_id=' . $projectId);
    }

    public static function editUrl(int $taskId): string
    {
        return Shell::url('/front/task.form.php?id=' . $taskId);
    }

    /** @return array<string, mixed> dados do template */
    public static function viewData(Project $project, ?ProjectTask $task, array $old = [], string $error = ''): array
    {
        $projectId = (int) $project->getID();
        $f = $task?->fields ?? [];
        $value = static fn (string $key, mixed $default) => $old[$key] ?? $default;
        $parent = (int) $value('projecttasks_id', $f['projecttasks_id'] ?? 0);
        $selected = self::currentTeam($task, $old);
        $phases = self::phases($projectId, $task !== null ? (int) $task->getID() : 0);

        // Pessoas e grupos da equipe do projeto (mais os que ja estao na tarefa).
        $team = self::projectTeam($project);
        foreach ($selected as $key => $member) {
            $team[$key] ??= $member;
        }
        foreach ($team as $key => &$member) {
            $member['checked'] = isset($selected[$key]);
        }
        unset($member);

        $data = [
            'is_new'       => $task === null,
            'task_id'      => $task !== null ? (int) $task->getID() : 0,
            'project_id'   => $projectId,
            'project_name' => (string) $project->fields['name'],
            'error'        => $error,
            'action'       => $task === null ? self::newUrl($projectId) : self::editUrl((int) $task->getID()),
            'back'         => ProjectPage::url($projectId, 'tasks'),
            'csrf'         => Session::getNewCSRFToken(),
            'name'         => (string) $value('name', $f['name'] ?? ''),
            'kind'         => (string) $value('task_kind', $task === null || $parent > 0 || !self::isPhase($task) ? 'task' : 'phase'),
            // Nova tarefa entra por padrao dentro da tarefa principal (se houver).
            'parent'       => $task === null && !isset($old['projecttasks_id']) && $phases !== [] ? (int) array_key_first($phases) : $parent,
            'phases'       => $phases,
            // Ja existe outra principal: a opcao "Principal" fica bloqueada.
            'main_task'    => $phases !== [] ? (string) reset($phases) : '',
            'start'        => (string) $value('plan_start_date', self::dateOnly((string) ($f['plan_start_date'] ?? ''))),
            'end'          => (string) $value('plan_end_date', self::dateOnly((string) ($f['plan_end_date'] ?? ''))),
            'status'       => (string) $value('status', $task !== null ? self::statusOf($task) : 'todo'),
            'statuses'     => self::STATUSES,
            'team'         => array_values($team),
            'template_id'  => (int) $value('projecttasktemplates_id', $f['projecttasktemplates_id'] ?? 0),
            'templates'    => $task === null ? TaskTemplates::all() : [],
            'templates_url' => TaskTemplates::canManage() ? TaskTemplates::url() : '',
            'can_delete'   => $task !== null && $task->can((int) $task->getID(), DELETE),
            'css_url'      => Shell::assetUrl('css/app.css'),
            'type_html'    => ProjectTaskType::dropdown([
                'name' => 'projecttasktypes_id', 'value' => (int) $value('projecttasktypes_id', $f['projecttasktypes_id'] ?? 0),
                'display' => false, 'width' => '100%', 'rand' => 'actype',
            ]),
            'users_html'   => User::dropdown([
                'name' => 'extra_users[]', 'value' => [], 'multiple' => true, 'right' => 'all',
                'display' => false, 'width' => '100%',
            ]),
            'groups_html'  => Group::dropdown([
                'name' => 'extra_groups[]', 'values' => [], 'multiple' => true,
                'display' => false, 'width' => '100%',
            ]),
            'content_html' => Html::textarea([
                'name' => 'content', 'value' => (string) $value('content', $f['content'] ?? ''),
                'enable_richtext' => true, 'enable_images' => false, 'rows' => 9, 'display' => false,
                'editor_id' => 'ac_task_content',
            ]),
        ];
        return $data;
    }

    /**
     * Cria ou atualiza a tarefa e a equipe dela.
     *
     * @throws \InvalidArgumentException
     */
    public static function save(Project $project, ?ProjectTask $task, array $input): int
    {
        $projectId = (int) $project->getID();
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome à tarefa.');
        }
        $start = self::validDate((string) ($input['plan_start_date'] ?? ''));
        $end = self::validDate((string) ($input['plan_end_date'] ?? ''));
        if ($start !== null && $end !== null && $end < $start) {
            throw new \InvalidArgumentException('A data de fim não pode ser antes da data de início.');
        }

        $parent = 0;
        if (($input['task_kind'] ?? 'task') === 'phase') {
            // So uma tarefa principal por projeto.
            $others = self::phases($projectId, $task !== null ? (int) $task->getID() : 0);
            if ($others !== []) {
                throw new \InvalidArgumentException('Este projeto já tem uma tarefa principal: "' . reset($others) . '".');
            }
        }
        if (($input['task_kind'] ?? 'task') === 'task') {
            $parent = (int) ($input['projecttasks_id'] ?? 0);
            // 0 = tarefa solta no projeto (sem fase).
            if ($parent > 0 && !isset(self::phases($projectId, $task !== null ? (int) $task->getID() : 0)[$parent])) {
                throw new \InvalidArgumentException('A fase escolhida não existe mais neste projeto.');
            }
        }

        $status = $task === null ? 'todo' : (string) ($input['status'] ?? self::statusOf($task));
        if (!isset(self::STATUSES[$status])) {
            $status = 'todo';
        }

        $fields = [
            'name'                    => $name,
            'projects_id'             => $projectId,
            'projecttasks_id'         => $parent,
            // Fase sem tipo recebe o tipo "Fase" (assim continua fase mesmo sem tarefas dentro).
            'projecttasktypes_id'     => ($input['task_kind'] ?? 'task') === 'phase' && (int) ($input['projecttasktypes_id'] ?? 0) <= 0
                ? TaskTemplates::phaseTypeId(true)
                : max(0, (int) ($input['projecttasktypes_id'] ?? 0)),
            'content'                 => (string) ($input['content'] ?? ''),
            'plan_start_date'         => $start !== null ? $start . ' 00:00:00' : 'NULL',
            'plan_end_date'           => $end !== null ? $end . ' 00:00:00' : 'NULL',
            'is_milestone'            => 0,
        ];
        $stateId = self::stateFor($status);
        if ($task === null || $status !== self::statusOf($task)) {
            $fields['projectstates_id'] = $stateId;
            // So tarefas sem subtarefas: a fase e calculada pelas tarefas dela.
            if ($task === null || !self::hasChildren((int) $task->getID())) {
                $fields['percent_done'] = $status === 'done' ? 100 : 0;
            }
        }

        if ($task === null) {
            $templateId = (int) ($input['projecttasktemplates_id'] ?? 0);
            if ($templateId > 0) {
                $fields['projecttasktemplates_id'] = $templateId;
            }
            $fields['entities_id'] = (int) $project->fields['entities_id'];
            $fields['is_recursive'] = (int) $project->fields['is_recursive'];
            $fields['users_id'] = (int) Session::getLoginUserID();
            if ($start === null) {
                unset($fields['plan_start_date']);
            }
            if ($end === null) {
                unset($fields['plan_end_date']);
            }
            $task = new ProjectTask();
            $id = (int) $task->add($fields);
            if ($id <= 0) {
                throw new \InvalidArgumentException('Não foi possível criar a tarefa. Confira os campos e tente de novo.');
            }
            $task->getFromDB($id);
        } else {
            $id = (int) $task->getID();
            if (!$task->update(['id' => $id] + $fields)) {
                throw new \InvalidArgumentException('Não foi possível salvar a tarefa.');
            }
        }

        self::saveTeam($id, $input);
        return $id;
    }

    /** Fases do projeto: tarefas de primeiro nivel (menos a propria tarefa). */
    public static function phases(int $projectId, int $exceptId = 0): array
    {
        global $DB;
        $phases = [];
        $phaseType = TaskTemplates::phaseTypeId(false);
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'projecttasktypes_id'],
            'FROM'   => 'glpi_projecttasks',
            'WHERE'  => ['projects_id' => $projectId, 'projecttasks_id' => 0, 'is_deleted' => 0, 'is_template' => 0],
            'ORDER'  => ['plan_start_date ASC', 'id ASC'],
        ]) as $row) {
            $id = (int) $row['id'];
            // Fase = tipo "Fase" ou ja tem subtarefas (tarefa solta nao entra).
            $isPhase = ($phaseType > 0 && (int) $row['projecttasktypes_id'] === $phaseType) || self::hasChildren($id);
            if ($id !== $exceptId && $isPhase) {
                $phases[$id] = (string) $row['name'];
            }
        }
        return $phases;
    }

    /** @return array<string, array{type: string, id: int, name: string, initials: string}> */
    private static function projectTeam(Project $project): array
    {
        global $DB;
        $team = [];
        $add = static function (string $type, int $id) use (&$team): void {
            if ($id > 0 && !isset($team[$type . ':' . $id])) {
                $team[$type . ':' . $id] = self::member($type, $id);
            }
        };
        $add('User', (int) $project->fields['users_id']);
        $add('Group', (int) $project->fields['groups_id']);
        foreach ($DB->request([
            'SELECT' => ['itemtype', 'items_id'],
            'FROM'   => 'glpi_projectteams',
            'WHERE'  => ['projects_id' => (int) $project->getID(), 'itemtype' => ['User', 'Group']],
        ]) as $row) {
            $add((string) $row['itemtype'], (int) $row['items_id']);
        }
        return array_filter($team, static fn (array $m): bool => $m['name'] !== '');
    }

    /** Membros marcados: do POST (quando volta com erro) ou da tarefa. */
    private static function currentTeam(?ProjectTask $task, array $old): array
    {
        global $DB;
        $selected = [];
        if ($old !== []) {
            foreach ((array) ($old['team'] ?? []) as $key) {
                [$type, $id] = array_pad(explode(':', (string) $key), 2, 0);
                if (in_array($type, ['User', 'Group'], true) && (int) $id > 0) {
                    $selected[$type . ':' . (int) $id] = self::member($type, (int) $id);
                }
            }
            return $selected;
        }
        if ($task === null) {
            return [];
        }
        foreach ($DB->request([
            'SELECT' => ['itemtype', 'items_id'],
            'FROM'   => 'glpi_projecttaskteams',
            'WHERE'  => ['projecttasks_id' => (int) $task->getID(), 'itemtype' => ['User', 'Group']],
        ]) as $row) {
            $selected[$row['itemtype'] . ':' . (int) $row['items_id']] = self::member((string) $row['itemtype'], (int) $row['items_id']);
        }
        return $selected;
    }

    /** Sincroniza a equipe da tarefa: marcados + adicionados - desmarcados. */
    private static function saveTeam(int $taskId, array $input): void
    {
        global $DB;
        $wanted = [];
        foreach ((array) ($input['team'] ?? []) as $key) {
            [$type, $id] = array_pad(explode(':', (string) $key), 2, 0);
            if (in_array($type, ['User', 'Group'], true) && (int) $id > 0) {
                $wanted[$type . ':' . (int) $id] = [$type, (int) $id];
            }
        }
        foreach (['User' => 'extra_users', 'Group' => 'extra_groups'] as $type => $field) {
            foreach ((array) ($input[$field] ?? []) as $id) {
                if ((int) $id > 0) {
                    $wanted[$type . ':' . (int) $id] = [$type, (int) $id];
                }
            }
        }

        $link = new ProjectTaskTeam();
        foreach ($DB->request([
            'SELECT' => ['id', 'itemtype', 'items_id'],
            'FROM'   => 'glpi_projecttaskteams',
            'WHERE'  => ['projecttasks_id' => $taskId, 'itemtype' => ['User', 'Group']],
        ]) as $row) {
            $key = $row['itemtype'] . ':' . (int) $row['items_id'];
            if (isset($wanted[$key])) {
                unset($wanted[$key]);
            } else {
                $link->delete(['id' => (int) $row['id']]);
            }
        }
        foreach ($wanted as [$type, $id]) {
            $link->add(['projecttasks_id' => $taskId, 'itemtype' => $type, 'items_id' => $id]);
        }
    }

    /** @return array{type: string, id: int, key: string, name: string, initials: string} */
    private static function member(string $type, int $id): array
    {
        $name = '';
        if ($type === 'User') {
            $user = new User();
            if ($user->getFromDB($id)) {
                $name = trim((string) $user->fields['firstname'] . ' ' . (string) $user->fields['realname']);
                $name = $name !== '' ? $name : (string) $user->fields['name'];
                $name = trim((string) preg_replace('/\s+-\s+.*$/u', '', $name)) ?: $name;
            }
        } else {
            $name = (string) Group::getFriendlyNameById($id);
        }
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : ''));
        return ['type' => $type, 'id' => $id, 'key' => $type . ':' . $id, 'name' => $name, 'initials' => $initials ?: '?'];
    }

    private static function statusOf(ProjectTask $task): string
    {
        global $DB;
        $state = (int) $task->fields['projectstates_id'];
        if ($state <= 0) {
            return 'todo';
        }
        $row = $DB->request(['SELECT' => ['name', 'is_finished'], 'FROM' => 'glpi_projectstates', 'WHERE' => ['id' => $state]])->current();
        $name = mb_strtolower((string) ($row['name'] ?? ''));
        if ((int) ($row['is_finished'] ?? 0) === 1) {
            return 'done';
        }
        return preg_match('/execu|andamento|progress/u', $name) === 1 ? 'running' : 'todo';
    }

    /** Estado do GLPI para cada botao (Backlog / Em Execucao / Concluido). */
    private static function stateFor(string $status): int
    {
        global $DB;
        $fallback = 0;
        foreach ($DB->request(['SELECT' => ['id', 'name', 'is_finished'], 'FROM' => 'glpi_projectstates', 'ORDER' => 'id ASC']) as $row) {
            $name = mb_strtolower((string) $row['name']);
            $finished = (int) $row['is_finished'] === 1;
            if (str_contains($name, 'cancel')) {
                continue;
            }
            $match = match ($status) {
                'done'    => $finished,
                'running' => !$finished && preg_match('/execu|andamento|progress/u', $name) === 1,
                default   => !$finished && preg_match('/backlog|planej|n[aã]o inici|novo/u', $name) === 1,
            };
            if ($match) {
                if ($status !== 'done' || preg_match('/conclu|finaliz/u', $name) === 1) {
                    return (int) $row['id'];
                }
                $fallback = $fallback ?: (int) $row['id'];
            }
        }
        return $fallback;
    }

    /** Fase = tem subtarefas ou o tipo e "Fase" (tarefa solta nao e fase). */
    private static function isPhase(ProjectTask $task): bool
    {
        $phaseType = TaskTemplates::phaseTypeId(false);
        return self::hasChildren((int) $task->getID())
            || ($phaseType > 0 && (int) $task->fields['projecttasktypes_id'] === $phaseType);
    }

    private static function hasChildren(int $taskId): bool
    {
        return countElementsInTable('glpi_projecttasks', ['projecttasks_id' => $taskId, 'is_deleted' => 0]) > 0;
    }

    private static function dateOnly(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}/', $value) === 1 ? substr($value, 0, 10) : '';
    }

    private static function validDate(string $value): ?string
    {
        $value = trim($value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) === 1 ? $value : null;
    }
}
