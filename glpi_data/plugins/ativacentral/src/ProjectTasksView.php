<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Html;
use Project;
use ProjectTask;

/**
 * Aba "Tarefas do projeto" no estilo do Ativa Central (Etapa 3): mesma
 * informacao da tabela do GLPI (tipo, status, percentual, datas planejadas,
 * duracoes, pai e equipe), agrupada por fase.
 */
final class ProjectTasksView
{
    private const KIND_LABELS = [
        'todo'      => 'Não iniciada',
        'running'   => 'Em andamento',
        'done'      => 'Concluída',
        'cancelled' => 'Cancelada',
    ];

    /** @return array<string, mixed> */
    public static function data(Project $project): array
    {
        global $DB;

        $projectId = (int) $project->getID();
        $states = [];
        foreach ($DB->request(['SELECT' => ['id', 'name', 'is_finished'], 'FROM' => 'glpi_projectstates']) as $row) {
            $states[(int) $row['id']] = ['name' => (string) $row['name'], 'finished' => (int) $row['is_finished'] === 1];
        }
        $types = [];
        foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_projecttasktypes']) as $row) {
            $types[(int) $row['id']] = (string) $row['name'];
        }

        $rows = [];
        foreach ($DB->request([
            'SELECT' => [
                'id', 'name', 'projecttasks_id', 'projecttasktypes_id', 'projectstates_id', 'percent_done',
                'plan_start_date', 'plan_end_date', 'planned_duration', 'effective_duration', 'users_id',
            ],
            'FROM'   => 'glpi_projecttasks',
            'WHERE'  => ['projects_id' => $projectId, 'is_deleted' => 0, 'is_template' => 0],
            'ORDER'  => ['plan_start_date ASC', 'id ASC'],
        ]) as $row) {
            $rows[(int) $row['id']] = $row;
        }

        // Equipe de cada tarefa (pessoas, grupos, fornecedores e contatos).
        $team = [];
        if ($rows !== []) {
            foreach ($DB->request([
                'SELECT' => ['projecttasks_id', 'itemtype', 'items_id'],
                'FROM'   => 'glpi_projecttaskteams',
                'WHERE'  => ['projecttasks_id' => array_keys($rows)],
            ]) as $m) {
                $name = self::memberName((string) $m['itemtype'], (int) $m['items_id']);
                if ($name !== '') {
                    $team[(int) $m['projecttasks_id']][] = $name;
                }
            }
        }

        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+2 days'));
        $nodes = [];
        foreach ($rows as $id => $row) {
            $state = $states[(int) $row['projectstates_id']] ?? null;
            $kind = self::kind($state);
            $members = $team[$id] ?? [];
            $owner = (int) $row['users_id'] > 0 ? getUserName((int) $row['users_id']) : '';
            if ($owner !== '' && !in_array($owner, $members, true)) {
                array_unshift($members, $owner);
            }
            $end = substr((string) ($row['plan_end_date'] ?? ''), 0, 10);
            $done = $kind === 'done';
            $nodes[$id] = [
                'id'        => $id,
                'parent'    => (int) $row['projecttasks_id'],
                'name'      => (string) ($row['name'] ?: 'Tarefa #' . $id),
                'url'       => ProjectTask::getFormURLWithID($id),
                'type'      => $types[(int) $row['projecttasktypes_id']] ?? '',
                'kind'      => $kind,
                'state'     => $state !== null ? (self::KIND_LABELS[$kind] ?? $state['name']) : 'Não iniciada',
                'percent'   => max(0, min(100, (int) $row['percent_done'])),
                'start'     => self::date((string) ($row['plan_start_date'] ?? '')),
                'end'       => self::date((string) ($row['plan_end_date'] ?? '')),
                'due'       => ($end !== '' && !$done && $kind !== 'cancelled') ? ($end < $today ? 'late' : ($end <= $soon ? 'soon' : '')) : '',
                'planned'   => (int) $row['planned_duration'] > 0 ? Html::timestampToString((int) $row['planned_duration'], false) : '—',
                'effective' => (int) $row['effective_duration'] > 0 ? Html::timestampToString((int) $row['effective_duration'], false) : '—',
                'members'   => array_map(static fn (string $n): array => ['name' => $n, 'initials' => self::initials($n)], $members),
                'children'  => [],
                'leaf_total' => 0,
                'leaf_done' => 0,
            ];
        }

        // Arvore (fase > tarefas) e contagem "X de Y concluidas".
        $roots = [];
        foreach ($nodes as $id => &$node) {
            if ($node['parent'] > 0 && $node['parent'] !== $id && isset($nodes[$node['parent']])) {
                $nodes[$node['parent']]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        unset($node);
        foreach ($roots as &$root) {
            self::countLeaves($root);
        }
        unset($root);

        $total = 0;
        $doneCount = 0;
        foreach ($nodes as $node) {
            if ($node['children'] === [] && $node['kind'] !== 'cancelled') {
                $total++;
                $doneCount += $node['kind'] === 'done' ? 1 : 0;
            }
        }

        $canAdd = ProjectTask::canCreate() && $project->can($projectId, UPDATE);
        return [
            'tasks'          => $roots,
            'tasks_total'    => $total,
            'tasks_done'     => $doneCount,
            'add_task_url'   => $canAdd ? ProjectTask::getFormURL() . '?projects_id=' . $projectId : '',
        ];
    }

    /** Preenche leaf_total/leaf_done (canceladas nao contam). */
    private static function countLeaves(array &$node): void
    {
        if ($node['children'] === []) {
            $node['leaf_total'] = $node['kind'] === 'cancelled' ? 0 : 1;
            $node['leaf_done'] = $node['kind'] === 'done' ? 1 : 0;
            return;
        }
        foreach ($node['children'] as &$child) {
            self::countLeaves($child);
            if ($node['kind'] !== 'cancelled') {
                $node['leaf_total'] += $child['leaf_total'];
                $node['leaf_done'] += $child['leaf_done'];
            }
        }
        unset($child);
    }

    private static function kind(?array $state): string
    {
        if ($state === null) {
            return 'todo';
        }
        $name = mb_strtolower($state['name']);
        if (str_contains($name, 'cancel')) {
            return 'cancelled';
        }
        if ($state['finished']) {
            return 'done';
        }
        return preg_match('/execu|andamento|progress|fazendo/u', $name) === 1 ? 'running' : 'todo';
    }

    private static function memberName(string $itemtype, int $id): string
    {
        if ($id <= 0 || !in_array($itemtype, ['User', 'Group', 'Supplier', 'Contact'], true)) {
            return '';
        }
        if ($itemtype === 'User') {
            return getUserName($id);
        }
        return (string) $itemtype::getFriendlyNameById($id);
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last = count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : '';
        return mb_strtoupper($first . $last) ?: '?';
    }

    private static function date(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}/', $value) === 1 ? date('d/m/Y', strtotime(substr($value, 0, 10))) : '';
    }
}
