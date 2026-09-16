<?php

declare(strict_types=1);

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_ativaremote_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAREMOTE_VERSION);
    $clientsTable = 'glpi_plugin_ativaremote_clients';

    if (!$DB->tableExists($clientsTable)) {
        $query = "CREATE TABLE `$clientsTable` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `computers_id` int unsigned DEFAULT NULL,
            `hostname` varchar(255) NOT NULL,
            `machine_guid` varchar(128) NOT NULL,
            `rustdesk_id` varchar(64) DEFAULT NULL,
            `rustdesk_password` varchar(64) DEFAULT NULL,
            `require_consent` tinyint(1) NOT NULL DEFAULT '1',
            `remote_access_status` varchar(16) DEFAULT NULL,
            `client_version` varchar(32) DEFAULT NULL,
            `last_check` datetime DEFAULT NULL,
            `last_ip` varchar(45) DEFAULT NULL,
            `registered_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            `token_hash` char(64) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `machine_guid` (`machine_guid`),
            UNIQUE KEY `token_hash` (`token_hash`),
            KEY `computers_id` (`computers_id`),
            KEY `hostname` (`hostname`),
            KEY `last_check` (`last_check`),
            KEY `updated_at` (`updated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;";

        $DB->queryOrDie($query, $DB->error());
    }

    $migration->executeMigration();
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
