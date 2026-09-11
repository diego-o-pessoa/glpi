<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ConfigService;

function plugin_ativaupdater_do_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAUPDATER_VERSION);
    $migration->displayMessage('Instalando Ativa Updater ' . PLUGIN_ATIVAUPDATER_VERSION);

    try {
        $DB->runFile(__DIR__ . '/schema.sql');

        // Create storage directories
        $storage = GLPI_PLUGIN_DOC_DIR . '/ativaupdater';
        foreach ([$storage, $storage . '/releases', $storage . '/tmp'] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('Nao foi possivel criar o diretorio privado do plugin.');
            }
        }

        require_once PLUGIN_ATIVAUPDATER_DIR . '/src/ConfigService.php';
        ConfigService::installDefaults();
        ConfigService::set(['schema_version' => PLUGIN_ATIVAUPDATER_VERSION]);

        require_once PLUGIN_ATIVAUPDATER_DIR . '/inc/profile.class.php';
        PluginAtivaupdaterProfile::installRights();

        $migration->executeMigration();

    } catch (Throwable $exception) {
        $migration->displayMessage('Falha na instalacao: ' . $exception->getMessage());
        return false;
    }

    return true;
}

function plugin_ativaupdater_do_uninstall(): bool
{
    global $DB;

    require_once PLUGIN_ATIVAUPDATER_DIR . '/inc/profile.class.php';
    PluginAtivaupdaterProfile::uninstallRights();

    $tables = [
        'glpi_plugin_ativaupdater_releases',
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->doQuery('DROP TABLE `' . $table . '`');
        }
    }
    
    // We do not delete the stored files by default during uninstall to prevent accidental data loss.
    require_once PLUGIN_ATIVAUPDATER_DIR . '/src/ConfigService.php';
    Config::deleteConfigurationValues(ConfigService::CONTEXT, array_keys(ConfigService::defaults()));
    Config::deleteConfigurationValues(ConfigService::CONTEXT, ['schema_version']);

    return true;
}
