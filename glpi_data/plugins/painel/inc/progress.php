<?php

declare(strict_types=1);

/**
 * Andamento automatico dos projetos (regra da Ativa):
 * - tarefa sem subtarefas: 100% se o estado e "finalizado", senao 0%;
 * - fase (tarefa com subtarefas): media das subtarefas, ou seja,
 *   concluidas / total (cada tarefa vale 100 / numero de tarefas);
 * - projeto: media das fases/tarefas de primeiro nivel;
 * - tarefas canceladas nao entram na conta.
 * O resultado e gravado em percent_done, entao a tela do GLPI e a do
 * Super-Admin mostram o mesmo numero.
 */

if (!function_exists('painel_state_kind')) {
    /** Tipo do estado: todo, running, done ou cancelled. */
    function painel_state_kind(string $name, bool $isFinished): string
    {
        $lower = mb_strtolower(trim($name));
        if ($lower !== '' && preg_match('/cancel/u', $lower) === 1) {
            return 'cancelled';
        }
        if ($isFinished) {
            return 'done';
        }
        if ($lower !== '' && preg_match('/execu|andamento|progress|fazendo/u', $lower) === 1) {
            return 'running';
        }
        return 'todo';
    }
}

if (!function_exists('painel_state_kinds')) {
    /** @return array<int, string> id do estado => tipo */
    function painel_state_kinds(): array
    {
        global $DB;
        $kinds = [];
        try {
            foreach ($DB->request(['SELECT' => ['id', 'name', 'is_finished'], 'FROM' => 'glpi_projectstates']) as $row) {
                $kinds[(int) $row['id']] = painel_state_kind((string) ($row['name'] ?? ''), (int) ($row['is_finished'] ?? 0) === 1);
            }
        } catch (Throwable $e) {
            // Sem tabela de estados: tudo conta como "nao iniciada".
        }
        return $kinds;
    }
}

if (!function_exists('painel_state_for_kind')) {
    /** Estado do GLPI usado para cada botao (0 = sem estado). */
    function painel_state_for_kind(string $kind): ?int
    {
        global $DB;
        $preferred = [
            'todo'    => '/backlog|n[aã]o inici|a fazer|novo|planej/u',
            'running' => '/execu|andamento/u',
            'done'    => '/conclu|finaliz|feito/u',
        ];
        if (!isset($preferred[$kind])) {
            return null;
        }
        $fallback = null;
        foreach ($DB->request(['SELECT' => ['id', 'name', 'is_finished'], 'FROM' => 'glpi_projectstates', 'ORDER' => 'id ASC']) as $row) {
            $name = mb_strtolower((string) ($row['name'] ?? ''));
            if (painel_state_kind($name, (int) ($row['is_finished'] ?? 0) === 1) !== $kind) {
                continue;
            }
            if (preg_match($preferred[$kind], $name) === 1) {
                return (int) $row['id'];
            }
            $fallback ??= (int) $row['id'];
        }
        if ($fallback === null && $kind === 'todo') {
            return 0;
        }
        return $fallback;
    }
}

if (!function_exists('painel_project_recalc')) {
    /**
     * Recalcula e grava o andamento do projeto e das tarefas.
     *
     * @return array{percent: int|null, tasks_total: int, tasks_done: int, nodes: array<int, array{kind: string, percent: int|null, leaf_total: int, leaf_done: int}>, parents: array<int, bool>}
     */
    function painel_project_recalc(int $projectId): array
    {
        global $DB;

        $result = ['percent' => null, 'tasks_total' => 0, 'tasks_done' => 0, 'nodes' => [], 'parents' => []];
        if ($projectId <= 0) {
            return $result;
        }

        $kinds = painel_state_kinds();
        $tasks = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'projecttasks_id', 'projectstates_id', 'percent_done'],
            'FROM'   => 'glpi_projecttasks',
            'WHERE'  => ['projects_id' => $projectId, 'is_deleted' => 0, 'is_template' => 0],
        ]) as $row) {
            $tasks[(int) $row['id']] = $row;
        }

        $children = [];
        $roots = [];
        foreach ($tasks as $tid => $t) {
            $parent = (int) $t['projecttasks_id'];
            if ($parent > 0 && $parent !== $tid && isset($tasks[$parent])) {
                $children[$parent][] = $tid;
            } else {
                $roots[] = $tid;
            }
        }

        $nodes = [];
        $compute = static function (int $tid, int $depth) use (&$compute, &$nodes, $tasks, $children, $kinds): ?float {
            $kind = $kinds[(int) $tasks[$tid]['projectstates_id']] ?? 'todo';
            $values = [];
            $leafTotal = 0;
            $leafDone = 0;
            if ($depth < 20) {
                foreach ($children[$tid] ?? [] as $cid) {
                    $value = $compute($cid, $depth + 1);
                    if ($value !== null) {
                        $values[] = $value;
                    }
                    $leafTotal += $nodes[$cid]['leaf_total'];
                    $leafDone += $nodes[$cid]['leaf_done'];
                }
            }
            if ($kind === 'cancelled') {
                $nodes[$tid] = ['kind' => $kind, 'percent' => null, 'leaf_total' => 0, 'leaf_done' => 0];
                return null;
            }
            if ($values === []) {
                $value = $kind === 'done' ? 100.0 : 0.0;
                $leafTotal = 1;
                $leafDone = $kind === 'done' ? 1 : 0;
            } else {
                $value = array_sum($values) / count($values);
            }
            $nodes[$tid] = ['kind' => $kind, 'percent' => (int) round($value), 'leaf_total' => $leafTotal, 'leaf_done' => $leafDone];
            return $value;
        };

        $rootValues = [];
        foreach ($roots as $tid) {
            $value = $compute($tid, 0);
            if ($value !== null) {
                $rootValues[] = $value;
            }
            $result['tasks_total'] += $nodes[$tid]['leaf_total'];
            $result['tasks_done'] += $nodes[$tid]['leaf_done'];
        }

        foreach ($nodes as $tid => $node) {
            if ($node['percent'] !== null && $node['percent'] !== (int) $tasks[$tid]['percent_done']) {
                $DB->update('glpi_projecttasks', ['percent_done' => $node['percent']], ['id' => $tid]);
            }
        }

        // Sem tarefas validas: mantem o percentual que o dono definiu.
        if ($rootValues !== []) {
            $percent = (int) round(array_sum($rootValues) / count($rootValues));
            $DB->update('glpi_projects', ['percent_done' => $percent], ['id' => $projectId]);
            $result['percent'] = $percent;
        }

        $result['nodes'] = $nodes;
        $result['parents'] = array_fill_keys(array_keys($children), true);
        return $result;
    }
}
