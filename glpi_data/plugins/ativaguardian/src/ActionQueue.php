<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

/**
 * Fila de ações corretivas enviadas do GLPI para o Guardian da máquina.
 *
 * O servidor NUNCA envia comando em texto: ele enfileira apenas um par
 * (componente, ação) escolhido de listas fechadas. Quem sabe traduzir isso em
 * um serviço do Windows é o próprio Guardian, com o mapeamento compilado nele.
 *
 * Uma ação é executada no máximo uma vez: claim() move pending -> running com
 * um UPDATE condicional e só entrega as linhas que ele realmente mudou, então
 * duas coletas simultâneas não pegam a mesma ação.
 */
final class ActionQueue
{
    public const TABLE = 'glpi_plugin_ativaguardian_actions';

    public const CHECK   = 'CHECK_COMPONENT';
    public const START   = 'START_COMPONENT';
    public const RESTART = 'RESTART_COMPONENT';
    public const REPAIR  = 'REPAIR_COMPONENT';
    /**
     * "Corrigir": o servidor não escolhe o procedimento. A máquina verifica o
     * estado no momento da execução e aplica o mínimo necessário — iniciar o
     * serviço se ele só estiver parado, reinstalar apenas se os arquivos
     * faltarem. Evita reinstalação à toa e decisões sobre status velho.
     */
    public const FIX = 'FIX_COMPONENT';

    public const ACTIONS = [self::CHECK, self::START, self::RESTART, self::REPAIR, self::FIX];

    public const PENDING = 'pending';
    public const RUNNING = 'running';
    public const SUCCESS = 'success';
    public const FAILED  = 'failed';

    /**
     * Quais ações fazem sentido por componente.
     *
     * O wallpaper roda por usuário (HKCU), não é serviço: iniciar/reiniciar
     * exigiria lançar processo na sessão do usuário, o que hoje pertence ao
     * Ativa Updater. Por isso ele aceita só verificação nesta etapa.
     */
    public const SUPPORTED = [
        // Todos reinstalam pelo mesmo pacote unificado, que é quem instala os
        // quatro. O Wallpaper não aparece com START/RESTART porque roda na
        // sessão do usuário (HKCU), não como serviço — mas pode ser reinstalado.
        'updater'    => [self::CHECK, self::START, self::RESTART, self::REPAIR, self::FIX],
        'remote'     => [self::CHECK, self::START, self::RESTART, self::REPAIR, self::FIX],
        'glpi_agent' => [self::CHECK, self::START, self::RESTART, self::REPAIR, self::FIX],
        'wallpaper'  => [self::CHECK, self::REPAIR, self::FIX],
    ];

    /** Reparo baixa e instala pacote: leva bem mais que as demais ações. */
    public const REPAIR_STALE_SECONDS = 1800;

    /** Uma ação presa em running por mais que isto é considerada perdida. */
    public const STALE_SECONDS = 600;

    public static function supports(string $component, string $action): bool
    {
        return in_array($action, self::SUPPORTED[$component] ?? [], true);
    }

    /** Enfileira uma ação. Retorna o id, ou 0 se recusada. */
    public static function enqueue(int $machinesId, string $component, string $action, int $userId): int
    {
        global $DB;

        if ($machinesId <= 0 || !self::supports($component, $action)) {
            return 0;
        }

        // Evita empilhar a mesma ação: se já existe uma pendente para este
        // componente, reaproveita em vez de criar uma fila de cliques repetidos.
        $existing = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => self::TABLE,
            'WHERE'  => [
                'machines_id' => $machinesId,
                'component'   => $component,
                'action'      => $action,
                'status'      => self::PENDING,
            ],
            'LIMIT'  => 1,
        ]);
        if (count($existing) === 1) {
            return (int) $existing->current()['id'];
        }

        $ok = $DB->insert(self::TABLE, [
            'machines_id' => $machinesId,
            'component'   => $component,
            'action'      => $action,
            'status'      => self::PENDING,
            'created_at'  => ServerClock::now(),
            'created_by'  => $userId,
        ]);

        return $ok ? (int) $DB->insertId() : 0;
    }

    /**
     * Entrega as ações pendentes da máquina já marcadas como running.
     *
     * @return array<int, array{id:int, component:string, action:string}>
     */
    public static function claim(int $machinesId, int $limit = 10): array
    {
        global $DB;

        self::expireStale();

        $claimed = [];
        $pending = $DB->request([
            'SELECT' => ['id', 'component', 'action'],
            'FROM'   => self::TABLE,
            'WHERE'  => ['machines_id' => $machinesId, 'status' => self::PENDING],
            'ORDER'  => ['id ASC'],
            'LIMIT'  => $limit,
        ]);

        foreach ($pending as $row) {
            $id = (int) $row['id'];
            // O WHERE repete status=pending: se outra coleta tiver pego esta
            // linha no intervalo, o UPDATE não altera nada e a ação é ignorada.
            $DB->update(
                self::TABLE,
                ['status' => self::RUNNING, 'started_at' => ServerClock::now()],
                ['id' => $id, 'status' => self::PENDING]
            );
            if ($DB->affectedRows() === 1) {
                $claimed[] = [
                    'id'        => $id,
                    'component' => (string) $row['component'],
                    'action'    => (string) $row['action'],
                ];
            }
        }

        return $claimed;
    }

    /**
     * Registra o desfecho. Só aceita uma ação que esteja running e pertença a
     * esta máquina, então um resultado repetido ou forjado é descartado.
     */
    public static function complete(int $actionId, int $machinesId, bool $success, string $message): bool
    {
        global $DB;

        $DB->update(
            self::TABLE,
            [
                'status'        => $success ? self::SUCCESS : self::FAILED,
                'result'        => $success ? self::SUCCESS : self::FAILED,
                // Guarda o relato também no sucesso: é ele que diz o que a
                // máquina fez de fato ("já estava rodando", "serviço iniciado",
                // "reinstalado"), e é isso que o modal e o histórico mostram.
                'error_message' => mb_substr(trim($message), 0, 500)
                    ?: ($success ? 'Concluído.' : 'Falha sem detalhe.'),
                'finished_at'   => ServerClock::now(),
            ],
            ['id' => $actionId, 'machines_id' => $machinesId, 'status' => self::RUNNING]
        );

        return $DB->affectedRows() === 1;
    }

    /** Fecha ações que ficaram running além do limite (máquina desligou no meio). */
    public static function expireStale(): void
    {
        global $DB;

        $expire = static function (int $seconds, array $actionFilter) use ($DB): void {
            $cutoff = ServerClock::format(time() - $seconds);
            $DB->update(
                self::TABLE,
                [
                    'status'        => self::FAILED,
                    'result'        => self::FAILED,
                    'error_message' => 'A máquina não devolveu o resultado a tempo.',
                    'finished_at'   => ServerClock::now(),
                ],
                ['status' => self::RUNNING, 'action' => $actionFilter, ['started_at' => ['<', $cutoff]]]
            );
        };

        // O reparo baixa um instalador e reinicia serviços - inclusive o próprio
        // Guardian, que o instalador para para trocar binários. Expirá-lo no
        // mesmo prazo das outras ações marcaria como falha um reparo que ainda
        // está em curso.
        $expire(self::STALE_SECONDS, [self::CHECK, self::START, self::RESTART]);
        $expire(self::REPAIR_STALE_SECONDS, [self::REPAIR]);
    }

    /**
     * Estado corrente por componente, para a tabela desenhar o botão certo.
     *
     * @return array<int, array<string, array{id:int, action:string, status:string}>>
     */
    public static function activeByMachine(array $machinesIds): array
    {
        global $DB;

        if ($machinesIds === []) {
            return [];
        }

        $active = [];
        $iterator = $DB->request([
            'SELECT' => ['id', 'machines_id', 'component', 'action', 'status'],
            'FROM'   => self::TABLE,
            'WHERE'  => ['machines_id' => $machinesIds, 'status' => [self::PENDING, self::RUNNING]],
            'ORDER'  => ['id ASC'],
        ]);
        foreach ($iterator as $row) {
            $active[(int) $row['machines_id']][(string) $row['component']] = [
                'id'     => (int) $row['id'],
                'action' => (string) $row['action'],
                'status' => (string) $row['status'],
            ];
        }

        return $active;
    }

    /** Histórico mais recente de uma máquina. */
    public static function history(int $machinesId, int $limit = 50): array
    {
        global $DB;

        $rows = [];
        foreach ($DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['machines_id' => $machinesId],
            'ORDER' => ['id DESC'],
            'LIMIT' => $limit,
        ]) as $row) {
            $rows[] = $row;
        }

        return $rows;
    }
}
