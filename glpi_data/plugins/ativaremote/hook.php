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
        $DB->runFile(__DIR__ . '/install/schema.sql');
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
