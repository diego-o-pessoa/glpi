<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;
use Session;
use Throwable;

/**
 * Evento/log do Workspace (glpi_plugin_ativaworkspace_events).
 */
final class Event extends CommonDBTM
{
    public const LEVEL_INFO     = 'INFO';
    public const LEVEL_WARNING  = 'WARNING';
    public const LEVEL_ERROR    = 'ERROR';
    public const LEVEL_SECURITY = 'SECURITY';

    public const LEVELS = [
        self::LEVEL_INFO,
        self::LEVEL_WARNING,
        self::LEVEL_ERROR,
        self::LEVEL_SECURITY,
    ];

    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_VIEW;

    public $dohistory = false;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Eventos do Workspace' : 'Evento do Workspace';
    }

    /**
     * Registra um evento. Nunca lanca excecao: log nao pode derrubar a pagina
     * (ex.: chamado durante uma negacao de acesso com a tabela indisponivel).
     *
     * @param array<string, mixed> $context sem segredos; vai em JSON para a tela
     */
    public static function log(string $level, string $source, string $message, array $context = [], int $jobsId = 0): void
    {
        if (!in_array($level, self::LEVELS, true)) {
            $level = self::LEVEL_INFO;
        }

        try {
            (new self())->add([
                'date'                          => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s'),
                'level'                         => $level,
                'source'                        => mb_substr($source, 0, 64),
                'message'                       => mb_substr($message, 0, 255),
                'context'                       => $context === [] ? null : json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'entities_id'                   => (int) ($_SESSION['glpiactive_entity'] ?? 0),
                'users_id'                      => (int) Session::getLoginUserID(),
                'plugin_ativaworkspace_jobs_id' => $jobsId,
            ]);
        } catch (Throwable $exception) {
            trigger_error('Ativa Workspace: falha ao registrar evento: ' . $exception->getMessage(), E_USER_WARNING);
        }
    }

    /**
     * Eventos mais recentes, opcionalmente filtrados por nivel.
     *
     * @return list<array<string, mixed>>
     */
    public static function recent(int $limit, ?string $level = null, int $offset = 0, int $jobsId = 0): array
    {
        global $DB;

        $table = self::getTable();
        $jobs  = Job::getTable();
        $where = self::where($level);
        if ($jobsId > 0) {
            $where["$table.plugin_ativaworkspace_jobs_id"] = $jobsId;
        }

        $rows = [];
        foreach ($DB->request([
            'SELECT'    => ["$table.*", 'glpi_computers.name AS computer_name'],
            'FROM'      => $table,
            'LEFT JOIN' => [
                $jobs            => ['ON' => [$jobs => 'id', $table => 'plugin_ativaworkspace_jobs_id']],
                'glpi_computers' => ['ON' => ['glpi_computers' => 'id', $jobs => 'computers_id']],
            ],
            'WHERE'     => $where,
            'ORDER'     => ["$table.date DESC", "$table.id DESC"],
            'START'     => max(0, $offset),
            'LIMIT'     => max(1, min(200, $limit)),
        ]) as $row) {
            $rows[] = [
                'id'            => (int) $row['id'],
                'date'          => $row['date'],
                'level'         => (string) $row['level'],
                'source'        => (string) $row['source'],
                'message'       => (string) $row['message'],
                'context'       => $row['context'],
                'jobs_id'       => (int) $row['plugin_ativaworkspace_jobs_id'],
                'computer_name' => (string) ($row['computer_name'] ?? ''),
                'user_name'     => (int) $row['users_id'] > 0 ? getUserName((int) $row['users_id']) : '',
            ];
        }
        return $rows;
    }

    public static function countByLevel(?string $level = null): int
    {
        return countElementsInTable(self::getTable(), self::where($level));
    }

    /**
     * Filtro comum: nivel (opcional) + entidades que o usuario enxerga.
     *
     * @return array<string, mixed>
     */
    private static function where(?string $level): array
    {
        $table = self::getTable();
        $where = getEntitiesRestrictCriteria($table, '', '', false);
        if ($level !== null && in_array($level, self::LEVELS, true)) {
            $where["$table.level"] = $level;
        }
        return $where;
    }
}
