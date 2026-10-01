<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

/**
 * Acoes agendadas para uma maquina, executadas pelo servico Ativa Workspace.
 * Fase 2: desinstalar um programa. Fase 3: acoes remotas (REMOTE_ACTIONS).
 *
 * O servidor NUNCA envia um comando: guarda apenas o alvo (nome do programa +
 * a chave do registro de desinstalacao). O cliente resolve o comando de
 * desinstalacao que o proprio Windows registrou (QuietUninstallString) e roda
 * em silencio. Assim nenhum comando arbitrario trafega ou e armazenado.
 */
final class MachineAction
{
    public const TABLE = 'glpi_plugin_ativaworkspace_machineactions';

    public const STATUS_QUEUED  = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DONE    = 'done';
    public const STATUS_FAILED  = 'failed';

    private const SCOPES = ['hklm64', 'hklm32', 'hkcu'];

    public const ACTION_UNINSTALL = 'uninstall';

    /**
     * Acoes remotas (Fase 3): so chaves desta lista. O servico na maquina
     * conhece o comando fixo de cada uma; o servidor nunca envia comando.
     */
    public const REMOTE_ACTIONS = [
        'restart'             => ['label' => 'Reiniciar', 'icon' => 'ti-refresh'],
        'shutdown'            => ['label' => 'Desligar', 'icon' => 'ti-power'],
        'message'             => ['label' => 'Mensagem ao usuário', 'icon' => 'ti-message'],
        'kill_process'        => ['label' => 'Encerrar processo', 'icon' => 'ti-square-x'],
        'clean_temp'          => ['label' => 'Limpar arquivos temporários', 'icon' => 'ti-trash'],
        'gpupdate'            => ['label' => 'Atualizar políticas (gpupdate)', 'icon' => 'ti-file-settings'],
        'flush_dns'           => ['label' => 'Limpar cache de DNS', 'icon' => 'ti-world-x'],
        'restart_spooler'     => ['label' => 'Reiniciar spooler de impressão', 'icon' => 'ti-printer'],
        'sync_time'           => ['label' => 'Sincronizar relógio', 'icon' => 'ti-clock'],
        'windows_update_scan' => ['label' => 'Buscar atualizações do Windows', 'icon' => 'ti-download'],
    ];

    /** Atraso permitido para reiniciar/desligar (segundos). */
    public const POWER_DELAYS = [0, 60, 300, 600, 1800];

    /** Processos que nunca podem ser encerrados (derrubam o Windows ou o proprio servico). */
    public const PROTECTED_PROCESSES = [
        'system', 'smss.exe', 'csrss.exe', 'wininit.exe', 'winlogon.exe', 'services.exe', 'lsass.exe',
        'svchost.exe', 'lsaiso.exe', 'fontdrvhost.exe', 'dwm.exe', 'registry', 'memory compression',
        'ativaworkspace.exe', 'ativaguardian.exe', 'unifiedupdater.exe', 'msmpeng.exe',
    ];

    /**
     * Enfileira uma acao remota. Devolve o id; lanca com a mensagem para o usuario.
     *
     * @param array<string, mixed> $input
     * @throws \RuntimeException
     */
    public static function queueRemote(int $computersId, string $action, array $input, int $userId): int
    {
        global $DB;

        if ($computersId <= 0 || !isset(self::REMOTE_ACTIONS[$action])) {
            throw new \RuntimeException('Ação remota desconhecida.');
        }
        $params = [];
        $target = self::REMOTE_ACTIONS[$action]['label'];
        switch ($action) {
            case 'restart':
            case 'shutdown':
                $delay = (int) ($input['delay'] ?? 60);
                if (!in_array($delay, self::POWER_DELAYS, true)) {
                    throw new \RuntimeException('Tempo de espera inválido.');
                }
                $params = ['delay' => $delay, 'message' => self::cleanText((string) ($input['message'] ?? ''), 200)];
                $target .= $delay > 0 ? ' em ' . intdiv($delay, 60) . ' min' : ' agora';
                break;
            case 'message':
                $text = self::cleanText((string) ($input['message'] ?? ''), 500);
                if ($text === '') {
                    throw new \RuntimeException('Escreva a mensagem.');
                }
                $params = ['message' => $text];
                $target = 'Mensagem: ' . mb_substr($text, 0, 80);
                break;
            case 'kill_process':
                $name = trim((string) ($input['process'] ?? ''));
                if (!preg_match('/^[\w .()+-]{1,80}\.exe$/iDu', $name)) {
                    throw new \RuntimeException('Nome de processo inválido.');
                }
                if (in_array(mb_strtolower($name), self::PROTECTED_PROCESSES, true)) {
                    throw new \RuntimeException($name . ' é um processo do sistema e não pode ser encerrado.');
                }
                $params = ['process' => $name];
                $target = 'Encerrar ' . $name;
                break;
        }

        // Nao duplica a mesma acao (e alvo) ainda pendente.
        $pending = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => self::TABLE,
            'WHERE'  => [
                'computers_id' => $computersId,
                'action'       => $action,
                'target'       => mb_substr($target, 0, 255),
                'status'       => [self::STATUS_QUEUED, self::STATUS_RUNNING],
            ],
            'LIMIT'  => 1,
        ])->current();
        if (is_array($pending)) {
            return (int) $pending['id'];
        }

        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $DB->insert(self::TABLE, [
            'computers_id' => $computersId,
            'action'       => $action,
            'target'       => mb_substr($target, 0, 255),
            'params'       => json_encode($params, JSON_UNESCAPED_UNICODE),
            'status'       => self::STATUS_QUEUED,
            'users_id'     => $userId,
            'requested_at' => $now,
            'date_mod'     => $now,
        ]);
        return (int) $DB->insertId();
    }

    /** Texto de uma linha so (mensagem aceita quebra de linha), sem caracteres de controle. */
    private static function cleanText(string $text, int $max): string
    {
        $text = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', $text) ?? '';
        return mb_substr(trim($text), 0, $max);
    }

    /**
     * Ultimas acoes remotas da maquina (sem as desinstalacoes), para o painel.
     *
     * @return list<array<string, mixed>>
     */
    public static function remoteHistory(int $computersId, int $limit = 10): array
    {
        global $DB;

        $items = [];
        foreach ($DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computersId, 'NOT' => ['action' => self::ACTION_UNINSTALL]],
            'ORDER' => ['id DESC'],
            'LIMIT' => $limit,
        ]) as $row) {
            $meta = self::REMOTE_ACTIONS[(string) $row['action']] ?? ['label' => (string) $row['action'], 'icon' => 'ti-bolt'];
            $items[] = [
                'id'           => (int) $row['id'],
                'action'       => (string) $row['action'],
                'icon'         => $meta['icon'],
                'target'       => (string) $row['target'],
                'status'       => (string) $row['status'],
                'message'      => (string) $row['message'],
                'requested_at' => (string) $row['requested_at'],
                'requester'    => (int) $row['users_id'] > 0 ? getUserName((int) $row['users_id']) : '',
            ];
        }
        return $items;
    }

    /** Enfileira uma desinstalacao. Devolve o id, ou 0 se os dados nao batem. */
    public static function queueUninstall(int $computersId, string $target, string $scope, string $regKey, int $userId): int
    {
        global $DB;

        $scope = mb_strtolower(trim($scope));
        $target = trim($target);
        $regKey = trim($regKey);
        if ($computersId <= 0 || $target === '' || $regKey === '' || !in_array($scope, self::SCOPES, true)) {
            return 0;
        }
        // A chave do registro nao pode conter caminho/curinga: e so o nome da subchave.
        if (!preg_match('/^[^\\\\\\/]{1,255}$/D', $regKey)) {
            return 0;
        }
        // Nao duplica: se ja ha uma desinstalacao pendente do mesmo alvo, reaproveita.
        $pending = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => self::TABLE,
            'WHERE'  => ['computers_id' => $computersId, 'reg_key' => $regKey, 'status' => [self::STATUS_QUEUED, self::STATUS_RUNNING]],
            'LIMIT'  => 1,
        ])->current();
        if (is_array($pending)) {
            return (int) $pending['id'];
        }

        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $DB->insert(self::TABLE, [
            'computers_id' => $computersId,
            'action'       => 'uninstall',
            'target'       => mb_substr($target, 0, 255),
            'scope'        => $scope,
            'reg_key'      => mb_substr($regKey, 0, 255),
            'status'       => self::STATUS_QUEUED,
            'users_id'     => $userId,
            'requested_at' => $now,
            'date_mod'     => $now,
        ]);
        return (int) $DB->insertId();
    }

    /**
     * Proxima acao pendente da maquina; marca como running e devolve. Null se nao ha.
     *
     * @return array<string, mixed>|null
     */
    public static function nextForComputer(int $computersId): ?array
    {
        global $DB;

        $row = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computersId, 'status' => self::STATUS_QUEUED],
            'ORDER' => ['id ASC'],
            'LIMIT' => 1,
        ])->current();
        if (!is_array($row)) {
            return null;
        }
        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $DB->update(self::TABLE, ['status' => self::STATUS_RUNNING, 'date_mod' => $now], ['id' => (int) $row['id']]);
        return [
            'id'      => (int) $row['id'],
            'action'  => (string) $row['action'],
            'target'  => (string) $row['target'],
            'scope'   => (string) $row['scope'],
            'reg_key' => (string) $row['reg_key'],
            'params'  => self::decodeParams($row['params'] ?? null),
        ];
    }

    /** @return array<string, mixed> */
    private static function decodeParams(mixed $raw): array
    {
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** Registra o resultado reportado pelo cliente. */
    public static function report(int $id, int $computersId, bool $ok, string $message): bool
    {
        global $DB;

        $row = $DB->request(['SELECT' => ['id', 'action', 'scope', 'reg_key'], 'FROM' => self::TABLE, 'WHERE' => ['id' => $id, 'computers_id' => $computersId], 'LIMIT' => 1])->current();
        if (!is_array($row)) {
            return false;
        }
        // Desinstalou: some da lista de programas na hora (nao espera o proximo inventario).
        if ($ok && $row['action'] === 'uninstall') {
            Inventory::removeProgram($computersId, (string) $row['scope'], (string) $row['reg_key']);
        }
        $now = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        $DB->update(self::TABLE, [
            'status'   => $ok ? self::STATUS_DONE : self::STATUS_FAILED,
            'message'  => mb_substr($message, 0, 255),
            'date_mod' => $now,
        ], ['id' => $id]);
        return true;
    }

    /**
     * Acoes pendentes/recentes da maquina, para a aba (ultimas primeiro).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function forComputer(int $computersId, int $limit = 20): array
    {
        global $DB;

        $rows = [];
        foreach ($DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computersId, 'action' => self::ACTION_UNINSTALL],
            'ORDER' => ['id DESC'],
            'LIMIT' => $limit,
        ]) as $row) {
            $key = (string) $row['reg_key'];
            // Ordem DESC: a primeira vista de cada chave e a acao mais recente.
            if (isset($rows[$key])) {
                continue;
            }
            $rows[$key] = [
                'id'       => (int) $row['id'],
                'target'   => (string) $row['target'],
                'scope'    => (string) $row['scope'],
                'status'   => (string) $row['status'],
                'message'  => (string) $row['message'],
                'date_mod' => (string) $row['date_mod'],
            ];
        }
        return $rows;
    }

    /**
     * Lista das acoes (mais recentes) + contagem, para a barra de progresso.
     *
     * @return array{items: array<int, array<string, mixed>>, counts: array<string, int>, active: bool}
     */
    public static function progressForComputer(int $computersId, int $limit = 50): array
    {
        global $DB;

        $items = [];
        $counts = ['total' => 0, 'queued' => 0, 'running' => 0, 'done' => 0, 'failed' => 0];
        foreach ($DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['computers_id' => $computersId, 'action' => self::ACTION_UNINSTALL],
            'ORDER' => ['id DESC'],
            'LIMIT' => $limit,
        ]) as $row) {
            $status = (string) $row['status'];
            $items[] = [
                'id'      => (int) $row['id'],
                'target'  => (string) $row['target'],
                'key'     => (string) $row['reg_key'],
                'status'  => $status,
                'message' => (string) $row['message'],
            ];
            $counts['total']++;
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }
        // Ordem cronologica para a lista (mais antigo primeiro).
        $items = array_reverse($items);
        return [
            'items'  => $items,
            'counts' => $counts,
            'active' => ($counts['queued'] + $counts['running']) > 0,
        ];
    }
}
