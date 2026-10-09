<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

/**
 * Colunas/tabelas adicionadas depois da 0.1.9. Idempotente: roda na instalacao
 * e tambem sob demanda (API e Equipamentos), para nao precisar subir a versao
 * do plugin. Subir a versao deixa o plugin "a atualizar" no GLPI e a API
 * responde 404 ate alguem clicar em Atualizar - e o Guardian antigo, ao ver
 * 404, so tentava de novo depois de 6 h.
 */
final class Schema
{
    private static bool $checked = false;

    public static function upgrade(): void
    {
        global $DB;

        if (self::$checked) {
            return;
        }
        self::$checked = true;

        $machines = Settings::TABLE_MACHINES;
        if (!$DB->tableExists($machines)) {
            return;
        }
        // O que impediu a coleta completa na maquina (PowerShell, pktmon...).
        if (!$DB->fieldExists($machines, 'diagnostic')) {
            $DB->doQuery("ALTER TABLE `{$machines}` ADD `diagnostic` varchar(500) NOT NULL DEFAULT '' AFTER `guardian_version`");
        }
        // 0 = a maquina ainda nao conseguiu ler os monitores: a primeira leitura
        // completa so registra a situacao (nao gera "monitor novo").
        if (!$DB->fieldExists($machines, 'monitors_seen')) {
            $DB->doQuery("ALTER TABLE `{$machines}` ADD `monitors_seen` tinyint NOT NULL DEFAULT '1' AFTER `diagnostic`");
        }

        // "Nao e switch": PC ou switchzinho de mesa visto pelo LLDP. Fica
        // gravado para os relatorios seguintes tambem o ignorarem.
        $switches = Settings::TABLE_SWITCHES;
        if ($DB->tableExists($switches) && !$DB->fieldExists($switches, 'ignored')) {
            $DB->doQuery("ALTER TABLE `{$switches}` ADD `ignored` tinyint NOT NULL DEFAULT '0' AFTER `mgmt_ip`");
            // Anuncios de PC ja gravados (sem nome, modelo nem IP de gerencia).
            foreach ($DB->request(['SELECT' => ['id'], 'FROM' => $switches, 'WHERE' => [
                'system_name' => '', 'description' => '', 'mgmt_ip' => '',
            ]]) as $row) {
                Desks::ignoreSwitch((int) $row['id']);
            }
        }

        // Ultima recusa por maquina: mostra em Equipamentos por que ela nao
        // aparece, sem precisar ir ate ela. Apagada no primeiro envio aceito.
        if (!$DB->tableExists(Settings::TABLE_REJECTIONS)) {
            $sign = \DBConnection::getDefaultPrimaryKeySignOption();
            $charset = \DBConnection::getDefaultCharset();
            $collation = \DBConnection::getDefaultCollation();
            $DB->doQuery("CREATE TABLE `" . Settings::TABLE_REJECTIONS . "` (
                `id` int {$sign} NOT NULL AUTO_INCREMENT,
                `machine_id` varchar(128) NOT NULL,
                `hostname` varchar(255) NOT NULL DEFAULT '',
                `message` varchar(500) NOT NULL DEFAULT '',
                `attempts` int {$sign} NOT NULL DEFAULT '0',
                `first_at` timestamp NULL DEFAULT NULL,
                `last_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `machine_id` (`machine_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation} ROW_FORMAT=DYNAMIC");
        }
    }

    /** Guarda a recusa (machine_id valido apenas; sem ele nao ha a quem atribuir). */
    public static function recordRejection(mixed $machineId, mixed $hostname, string $message): void
    {
        global $DB;

        if (!is_string($machineId) || !preg_match('/^[A-Za-z0-9._-]{1,128}$/D', $machineId)) {
            return;
        }
        try {
            self::upgrade();
            $now = date('Y-m-d H:i:s');
            $host = is_scalar($hostname) ? mb_substr(trim((string) $hostname), 0, 255) : '';
            $row = $DB->request(['FROM' => Settings::TABLE_REJECTIONS, 'WHERE' => ['machine_id' => $machineId], 'LIMIT' => 1])->current();
            $fields = ['hostname' => $host, 'message' => mb_substr($message, 0, 500), 'last_at' => $now];
            if ($row) {
                $DB->update(Settings::TABLE_REJECTIONS, $fields + ['attempts' => (int) $row['attempts'] + 1], ['id' => (int) $row['id']]);
            } else {
                $DB->insert(Settings::TABLE_REJECTIONS, $fields + ['machine_id' => $machineId, 'attempts' => 1, 'first_at' => $now]);
            }
        } catch (\Throwable) {
            // Diagnostico nunca pode piorar a resposta da API.
        }
    }

    public static function clearRejection(string $machineId): void
    {
        global $DB;

        if ($DB->tableExists(Settings::TABLE_REJECTIONS)) {
            $DB->delete(Settings::TABLE_REJECTIONS, ['machine_id' => $machineId]);
        }
    }

    /** @return array<string, array{message: string, attempts: int, first_at: string, last_at: string}> */
    public static function rejections(): array
    {
        global $DB;

        self::upgrade();
        $out = [];
        if (!$DB->tableExists(Settings::TABLE_REJECTIONS)) {
            return $out;
        }
        foreach ($DB->request(['FROM' => Settings::TABLE_REJECTIONS]) as $row) {
            $out[(string) $row['machine_id']] = [
                'message'  => (string) $row['message'],
                'attempts' => (int) $row['attempts'],
                'first_at' => (string) $row['first_at'],
                'last_at'  => (string) $row['last_at'],
            ];
        }
        return $out;
    }
}
