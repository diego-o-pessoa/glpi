<?php

declare(strict_types=1);

/**
 * Install and upgrade hook
 *
 * @return boolean
 */
function plugin_ativaremote_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAREMOTE_VERSION);
    $clientsTable = 'glpi_plugin_ativaremote_clients';

    if (!$DB->tableExists($clientsTable)) {
        $DB->runFile(__DIR__ . '/install/schema.sql');
    } else {
        // Columns added in 1.1.0 (sessions opened by the Ativa Updater service).
        $fields = [
            'rustdesk_version'   => ['varchar(32) DEFAULT NULL', 'rustdesk_password'],
            'rustdesk_ready'     => ["tinyint(1) NOT NULL DEFAULT '0'", 'rustdesk_version'],
            'rustdesk_message'   => ['varchar(255) DEFAULT NULL', 'rustdesk_ready'],
            'request_seq'        => ["int unsigned NOT NULL DEFAULT '0'", 'remote_access_status'],
            'request_action'     => ['varchar(16) DEFAULT NULL', 'request_seq'],
            'requested_by'       => ["int unsigned NOT NULL DEFAULT '0'", 'request_action'],
            'requested_at'       => ['datetime DEFAULT NULL', 'requested_by'],
            'session_password'   => ['text DEFAULT NULL', 'requested_at'],
            'session_started_at' => ['datetime DEFAULT NULL', 'session_password'],
            'status_message'     => ['varchar(255) DEFAULT NULL', 'session_started_at'],
        ];
        foreach ($fields as $field => [$definition, $after]) {
            if (!$DB->fieldExists($clientsTable, $field)) {
                $migration->addField($clientsTable, $field, $definition, ['after' => $after]);
            }
        }
    }

    $migration->executeMigration();
    require_once __DIR__ . '/inc/profile.class.php';
    PluginAtivaremoteProfile::createAdminAccess();
    return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_ativaremote_uninstall(): bool
{
    global $DB;

    $tables = [
        'glpi_plugin_ativaremote_clients'
    ];

    foreach ($tables as $table) {
        $DB->dropTable($table);
    }

    return true;
}
