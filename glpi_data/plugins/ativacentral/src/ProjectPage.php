<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Group;
use Html;
use Project;
use ProjectState;
use ProjectType;
use Session;
use User;

/**
 * Tela do projeto no estilo do Ativa Central (Etapa 2): menu lateral com
 * as abas uteis e aba "Projeto" com formulario simplificado. As demais abas
 * mostram o conteudo do proprio GLPI dentro do painel.
 */
final class ProjectPage
{
    /** Abas: chave => [rotulo, icone, aba do GLPI (null = formulario nosso)]. */
    public const TABS = [
        'main'      => ['Projeto', 'ti-clipboard-text', null],
        'tasks'     => ['Tarefas do projeto', 'ti-list-check', null],
        'team'      => ['Equipe', 'ti-users', 'ProjectTeam$1'],
        'kanban'    => ['Kanban', 'ti-layout-kanban', 'Project$3'],
        'children'  => ['Subprojetos', 'ti-sitemap', 'Project$1'],
        'documents' => ['Documentos', 'ti-files', 'Document_Item$1'],
        'contracts' => ['Contratos', 'ti-file-certificate', 'Contract_Item$1'],
        'history'   => ['Histórico', 'ti-history', 'Log$1'],
    ];

    public static function url(int $id, string $tab = 'main'): string
    {
        return Shell::url('/front/project.view.php?id=' . $id . ($tab !== 'main' ? '&tab=' . rawurlencode($tab) : ''));
    }

    /** @return array<string, mixed> */
    public static function viewData(Project $project, string $tab, string $message = '', string $error = '', array $old = []): array
    {
        global $CFG_GLPI;

        $id = (int) $project->getID();
        $f = $project->fields;
        $canUpdate = $project->can($id, UPDATE);
        $priority = (int) $f['priority'];
        $state = (int) $f['projectstates_id'];
        $stateRow = new ProjectState();
        $stateName = ProjectForm::stateLabel(
            $state > 0 && $stateRow->getFromDB($state) ? (string) $stateRow->fields['name'] : '',
            $state > 0 && (int) ($stateRow->fields['is_finished'] ?? 0) === 1
        );

        $tabs = [];
        foreach (self::TABS as $key => [$label, $icon, $glpiTab]) {
            $tabs[] = [
                'key'    => $key,
                'label'  => $label,
                'icon'   => $icon,
                'url'    => self::url($id, $key),
                'count'  => self::count($key, $id),
                'active' => $key === $tab,
            ];
        }

        $data = [
            'id'             => $id,
            'name'           => (string) $f['name'],
            'tab'            => $tab,
            'tabs'           => $tabs,
            'message'        => $message,
            'error'          => $error,
            'back'           => Shell::url('/front/projects.php'),
            'priority_label' => ProjectForm::priorityLabel($priority),
            'priority_tone'  => ProjectForm::priorityTone($priority),
            'state_name'     => $stateName,
            'percent'        => max(0, min(100, (int) $f['percent_done'])),
            'dates'          => self::dateRange((string) ($f['plan_start_date'] ?? ''), (string) ($f['plan_end_date'] ?? '')),
            'created'        => !empty($f['date_creation']) ? date('d/m/Y H:i', strtotime((string) $f['date_creation'])) : '',
            'can_update'     => $canUpdate,
            'css_url'        => Shell::assetUrl('css/app.css'),
        ];

        $glpiTab = self::TABS[$tab][2] ?? null;
        if ($glpiTab !== null) {
            // Conteudo da aba vem do GLPI (mesmo endereco que a tela nativa usa).
            $data['tab_url'] = rtrim((string) $CFG_GLPI['root_doc'], '/') . '/ajax/common.tabs.php?' . http_build_query([
                '_target'   => rtrim((string) $CFG_GLPI['root_doc'], '/') . '/front/project.form.php',
                '_itemtype' => 'Project',
                '_glpi_tab' => $glpiTab,
                'id'        => $id,
            ]);
            return $data;
        }

        if ($tab === 'tasks') {
            return $data + ProjectTasksView::data($project);
        }

        $value = static fn (string $key, mixed $default) => $old[$key] ?? $default;
        $data += [
            'action'      => self::url($id),
            'csrf'        => Session::getNewCSRFToken(),
            'f_name'      => (string) $value('name', $f['name']),
            'f_priority'  => (int) $value('priority', $priority >= 5 ? 5 : max(2, $priority)),
            'priorities'  => ProjectForm::PRIORITIES,
            'f_start'     => (string) $value('plan_start_date', self::dateOnly((string) ($f['plan_start_date'] ?? ''))),
            'f_end'       => (string) $value('plan_end_date', self::dateOnly((string) ($f['plan_end_date'] ?? ''))),
            'can_delete'  => $project->can($id, DELETE),
            'type_html'   => ProjectType::dropdown([
                'name' => 'projecttypes_id', 'value' => (int) $value('projecttypes_id', $f['projecttypes_id']),
                'display' => false, 'width' => '100%',
            ]),
            'state_html'  => ProjectState::dropdown([
                'name' => 'projectstates_id', 'value' => (int) $value('projectstates_id', $state),
                'display' => false, 'width' => '100%',
            ]),
            'user_html'   => User::dropdown([
                'name' => 'users_id', 'value' => (int) $value('users_id', $f['users_id']),
                'right' => 'see_project', 'entity' => (int) $f['entities_id'],
                'display' => false, 'width' => '100%',
            ]),
            'group_html'  => Group::dropdown([
                'name' => 'groups_id', 'value' => (int) $value('groups_id', $f['groups_id']),
                'entity' => (int) $f['entities_id'], 'condition' => ['is_manager' => 1],
                'display' => false, 'width' => '100%',
            ]),
            'content_html' => Html::textarea([
                'name' => 'content', 'value' => (string) $value('content', $f['content'] ?? ''),
                'enable_richtext' => true, 'enable_images' => false, 'rows' => 10, 'display' => false,
            ]),
        ];
        return $data;
    }

    /** @throws \InvalidArgumentException */
    public static function save(Project $project, array $input): void
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('O projeto precisa de um nome.');
        }
        $priority = (int) ($input['priority'] ?? 3);
        if (!isset(ProjectForm::PRIORITIES[$priority])) {
            $priority = 3;
        }
        $start = self::validDate((string) ($input['plan_start_date'] ?? ''));
        $end = self::validDate((string) ($input['plan_end_date'] ?? ''));
        if ($start !== null && $end !== null && $end < $start) {
            throw new \InvalidArgumentException('A data de fim não pode ser antes da data de início.');
        }

        $ok = $project->update([
            'id'               => $project->getID(),
            'name'             => $name,
            'priority'         => $priority,
            'projecttypes_id'  => max(0, (int) ($input['projecttypes_id'] ?? 0)),
            'projectstates_id' => max(0, (int) ($input['projectstates_id'] ?? 0)),
            'users_id'         => max(0, (int) ($input['users_id'] ?? 0)),
            'groups_id'        => max(0, (int) ($input['groups_id'] ?? 0)),
            'content'          => (string) ($input['content'] ?? ''),
            // Datas sem hora (meia-noite); vazio limpa a data.
            'plan_start_date'  => $start !== null ? $start . ' 00:00:00' : 'NULL',
            'plan_end_date'    => $end !== null ? $end . ' 00:00:00' : 'NULL',
        ]);
        if (!$ok) {
            throw new \InvalidArgumentException('Não foi possível salvar o projeto.');
        }
    }

    private static function count(string $tab, int $id): int
    {
        try {
            return match ($tab) {
                'tasks'     => countElementsInTable('glpi_projecttasks', ['projects_id' => $id, 'is_deleted' => 0, 'is_template' => 0]),
                'team'      => countElementsInTable('glpi_projectteams', ['projects_id' => $id]),
                'children'  => countElementsInTable('glpi_projects', ['projects_id' => $id, 'is_deleted' => 0]),
                'documents' => countElementsInTable('glpi_documents_items', ['itemtype' => 'Project', 'items_id' => $id]),
                'contracts' => countElementsInTable('glpi_contracts_items', ['itemtype' => 'Project', 'items_id' => $id]),
                default     => 0,
            };
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function dateOnly(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}/', $value) === 1 ? substr($value, 0, 10) : '';
    }

    private static function dateRange(string $start, string $end): string
    {
        $s = $start !== '' ? date('d/m/Y', strtotime($start)) : '';
        $e = $end !== '' ? date('d/m/Y', strtotime($end)) : '';
        if ($s !== '' && $e !== '') {
            return $s . ' → ' . $e;
        }
        return $s !== '' ? 'Início ' . $s : ($e !== '' ? 'Até ' . $e : 'Sem prazo definido');
    }

    private static function validDate(string $value): ?string
    {
        $value = trim($value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) === 1 ? $value : null;
    }
}
