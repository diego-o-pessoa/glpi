<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Group;
use Group_User;
use Html;
use Project;
use ProjectTeam;
use ProjectType;
use Session;
use User;

/**
 * Tela simplificada "Novo projeto": so os campos que o gestor precisa.
 * Gerente (usuario e grupo), estado inicial, data de criacao e percentual
 * sao preenchidos sozinhos.
 */
final class ProjectForm
{
    /** Prioridades mostradas (valor gravado no GLPI => rotulo). */
    public const PRIORITIES = [
        2 => 'Baixa',
        3 => 'Normal',
        4 => 'Alta',
        5 => 'Urgente',
    ];

    /** Rotulo das 4 prioridades (valores antigos caem na mais proxima). */
    public static function priorityLabel(int $priority): string
    {
        return match (true) {
            $priority <= 2 => 'Baixa',
            $priority === 3 => 'Normal',
            $priority === 4 => 'Alta',
            default => 'Urgente',
        };
    }

    /** Nome simples do estado do projeto (Backlog -> Nao iniciado, Em Execucao -> Em andamento). */
    public static function stateLabel(string $name, bool $finished): string
    {
        $lower = mb_strtolower(trim($name));
        return match (true) {
            $lower !== '' && str_contains($lower, 'cancel') => 'Cancelado',
            $finished => 'Concluído',
            $lower !== '' && preg_match('/execu|andamento|progress/u', $lower) === 1 => 'Em andamento',
            $lower === '' || preg_match('/backlog|planej|n[aã]o inici|novo|aberto|sem estado/u', $lower) === 1 => 'Não iniciado',
            default => trim($name),
        };
    }

    public static function priorityTone(int $priority): string
    {
        return match (true) {
            $priority <= 2 => 'is-blue',
            $priority === 3 => 'is-muted',
            $priority === 4 => 'is-orange',
            default => 'is-red',
        };
    }

    /** @return array<string, mixed> dados para o template */
    public static function viewData(array $old = [], string $error = ''): array
    {
        $me = (int) Session::getLoginUserID();
        $group = self::managerGroup($me);
        $start = (string) ($old['plan_start_date'] ?? date('Y-m-d'));

        return [
            'error'      => $error,
            'action'     => Shell::url('/front/project.new.php'),
            'back'       => Shell::url('/front/projects.php'),
            'csrf'       => Session::getNewCSRFToken(),
            'name'       => (string) ($old['name'] ?? ''),
            'priority'   => (int) ($old['priority'] ?? 3),
            'priorities' => self::PRIORITIES,
            'start'      => $start,
            'end'        => (string) ($old['plan_end_date'] ?? ''),
            'manager'    => getUserName($me),
            'group'      => $group > 0 ? Group::getFriendlyNameById($group) : '',
            'type_html'  => ProjectType::dropdown([
                'name'    => 'projecttypes_id',
                'value'   => (int) ($old['projecttypes_id'] ?? 0),
                'display' => false,
                'width'   => '100%',
            ]),
            'users_html' => User::dropdown([
                'name'     => 'team_users[]',
                'value'    => array_map('intval', (array) ($old['team_users'] ?? [])),
                'multiple' => true,
                'right'    => 'all',
                'display'  => false,
                'width'    => '100%',
            ]),
            'groups_html' => Group::dropdown([
                'name'     => 'team_groups[]',
                'values'   => array_map('intval', (array) ($old['team_groups'] ?? [])),
                'multiple' => true,
                'display'  => false,
                'width'    => '100%',
            ]),
            'content_html' => Html::textarea([
                'name'            => 'content',
                'value'           => (string) ($old['content'] ?? ''),
                'enable_richtext' => true,
                'enable_images'   => false,
                'rows'            => 8,
                'display'         => false,
            ]),
        ];
    }

    /**
     * Cria o projeto e a equipe.
     *
     * @throws \InvalidArgumentException com a mensagem para o usuario
     */
    public static function create(array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome ao projeto.');
        }
        $priority = (int) ($input['priority'] ?? 3);
        if (!isset(self::PRIORITIES[$priority])) {
            $priority = 3;
        }
        $start = self::date((string) ($input['plan_start_date'] ?? ''));
        $end = self::date((string) ($input['plan_end_date'] ?? ''));
        if ($start !== null && $end !== null && $end < $start) {
            throw new \InvalidArgumentException('A data de fim não pode ser antes da data de início.');
        }

        $me = (int) Session::getLoginUserID();
        $project = new Project();
        $fields = [
            'name'             => $name,
            'priority'         => $priority,
            'projecttypes_id'  => max(0, (int) ($input['projecttypes_id'] ?? 0)),
            'projectstates_id' => self::initialState(),
            'content'          => (string) ($input['content'] ?? ''),
            'users_id'         => $me,
            'groups_id'        => self::managerGroup($me),
            'entities_id'      => (int) ($_SESSION['glpiactive_entity'] ?? 0),
            'percent_done'     => 0,
        ];
        // Datas sem hora: grava meia-noite.
        if ($start !== null) {
            $fields['plan_start_date'] = $start . ' 00:00:00';
        }
        if ($end !== null) {
            $fields['plan_end_date'] = $end . ' 00:00:00';
        }
        // Mede cada etapa: a criacao demorou no servidor de producao.
        $clock = microtime(true);
        $steps = [];
        $id = $project->add($fields);
        $steps[] = sprintf('projeto %.1fs', microtime(true) - $clock);
        if (!$id) {
            throw new \InvalidArgumentException('Não foi possível criar o projeto. Confira os campos e tente de novo.');
        }

        $team = new ProjectTeam();
        foreach (['User' => 'team_users', 'Group' => 'team_groups'] as $itemtype => $field) {
            foreach (array_unique(array_map('intval', (array) ($input[$field] ?? []))) as $itemId) {
                if ($itemId > 0) {
                    $started = microtime(true);
                    $team->add(['projects_id' => $id, 'itemtype' => $itemtype, 'items_id' => $itemId]);
                    $steps[] = sprintf('equipe %s#%d %.1fs', $itemtype, $itemId, microtime(true) - $started);
                }
            }
        }
        $total = microtime(true) - $clock;
        if ($total >= 2.0) {
            \Toolbox::logInFile('ativacentral', sprintf("Novo projeto %d demorou %.1fs: %s\n", $id, $total, implode(', ', $steps)));
        }
        return (int) $id;
    }

    /** Grupo do gestor: o grupo padrao do usuario, senao o primeiro dele. */
    private static function managerGroup(int $userId): int
    {
        $user = new User();
        if ($user->getFromDB($userId) && (int) ($user->fields['groups_id'] ?? 0) > 0) {
            return (int) $user->fields['groups_id'];
        }
        $groups = Group_User::getUserGroups($userId);
        return $groups !== [] ? (int) $groups[0]['id'] : 0;
    }

    /** Estado inicial: "Backlog" (ou outro de planejamento), senao vazio. */
    private static function initialState(): int
    {
        global $DB;
        $fallback = 0;
        foreach ($DB->request(['SELECT' => ['id', 'name', 'is_finished'], 'FROM' => 'glpi_projectstates', 'ORDER' => 'id ASC']) as $row) {
            $name = mb_strtolower((string) $row['name']);
            if ((int) $row['is_finished'] === 1) {
                continue;
            }
            if (str_contains($name, 'backlog')) {
                return (int) $row['id'];
            }
            if ($fallback === 0 && preg_match('/planej|n[aã]o inici|novo|aberto/u', $name) === 1) {
                $fallback = (int) $row['id'];
            }
        }
        return $fallback;
    }

    private static function date(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }
        return $value;
    }
}
