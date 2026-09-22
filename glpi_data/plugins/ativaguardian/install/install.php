<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\ConfigService;

function plugin_ativaguardian_do_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAGUARDIAN_VERSION);
    $migration->displayMessage('Instalando Ativa Guardian ' . PLUGIN_ATIVAGUARDIAN_VERSION);

    try {
        $DB->runFile(__DIR__ . '/schema.sql');

        require_once PLUGIN_ATIVAGUARDIAN_DIR . '/src/ConfigService.php';
        ConfigService::installDefaults();
        ConfigService::set(['schema_version' => PLUGIN_ATIVAGUARDIAN_VERSION]);

        require_once PLUGIN_ATIVAGUARDIAN_DIR . '/inc/profile.class.php';
        PluginAtivaguardianProfile::installRights();

        $migration->executeMigration();
    } catch (Throwable $exception) {
        $migration->displayMessage('Falha na instalacao: ' . $exception->getMessage());
        return false;
    }

    return true;
}

function plugin_ativaguardian_do_uninstall(): bool
{
    global $DB;

    require_once PLUGIN_ATIVAGUARDIAN_DIR . '/inc/profile.class.php';
    PluginAtivaguardianProfile::uninstallRights();

    // Filhas primeiro: ambas referenciam machines por id.
    $tables = [
        'glpi_plugin_ativaguardian_actions',
        'glpi_plugin_ativaguardian_components',
        'glpi_plugin_ativaguardian_machines',
    ];
    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->doQuery('DROP TABLE `' . $table . '`');
        }
    }

    require_once PLUGIN_ATIVAGUARDIAN_DIR . '/src/ConfigService.php';
    Config::deleteConfigurationValues(ConfigService::CONTEXT, array_keys(ConfigService::defaults()));
    Config::deleteConfigurationValues(ConfigService::CONTEXT, ['schema_version']);

    return true;
}
