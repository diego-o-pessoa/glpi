<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ConfigService;
use GlpiPlugin\Ativaupdater\ReleasePolicy;

function plugin_ativaupdater_do_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAUPDATER_VERSION);
    $migration->displayMessage('Instalando Ativa Updater ' . PLUGIN_ATIVAUPDATER_VERSION);

    try {
        $DB->runFile(__DIR__ . '/schema.sql');

        $releasesTable = 'glpi_plugin_ativaupdater_releases';
        if ($DB->tableExists($releasesTable)) {
            if (!$DB->fieldExists($releasesTable, 'allow_downgrade')) {
                $migration->addField($releasesTable, 'allow_downgrade', "tinyint(1) NOT NULL DEFAULT '0'", ['after' => 'active']);
            }
            if (!$DB->fieldExists($releasesTable, 'activated_at')) {
                $migration->addField($releasesTable, 'activated_at', 'datetime NULL', ['after' => 'allow_downgrade']);
            }
            if (!$DB->fieldExists($releasesTable, 'activated_by')) {
                $migration->addField($releasesTable, 'activated_by', "int(11) NOT NULL DEFAULT '0'", ['after' => 'activated_at']);
            }
        }

        $clientsTable = 'glpi_plugin_ativaupdater_clients';
        if ($DB->tableExists($clientsTable)) {
            if (!$DB->fieldExists($clientsTable, 'wallpaper_client_version')) {
                $migration->addField($clientsTable, 'wallpaper_client_version', "varchar(32) NOT NULL DEFAULT ''");
            }
            if (!$DB->fieldExists($clientsTable, 'glpi_agent_version')) {
                $migration->addField($clientsTable, 'glpi_agent_version', "varchar(32) NOT NULL DEFAULT ''");
            }
            if (!$DB->fieldExists($clientsTable, 'check_requested_at')) {
                $migration->addField($clientsTable, 'check_requested_at', 'datetime NULL');
            }
            if (!$DB->fieldExists($clientsTable, 'check_acknowledged_at')) {
                $migration->addField($clientsTable, 'check_acknowledged_at', 'datetime NULL');
            }
        }

        // Create storage directories
        $storage = GLPI_PLUGIN_DOC_DIR . '/ativaupdater';
        foreach ([$storage, $storage . '/releases', $storage . '/tmp'] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('Nao foi possivel criar o diretorio privado do plugin.');
            }
        }

        require_once PLUGIN_ATIVAUPDATER_DIR . '/src/ConfigService.php';
        require_once PLUGIN_ATIVAUPDATER_DIR . '/src/ReleasePolicy.php';
        ConfigService::installDefaults();
        ConfigService::set(['schema_version' => PLUGIN_ATIVAUPDATER_VERSION]);

        require_once PLUGIN_ATIVAUPDATER_DIR . '/inc/profile.class.php';
        PluginAtivaupdaterProfile::installRights();

        $migration->executeMigration();

        // Reconcile reports created before the first release existed. The
        // service will replace this transitional state on its next scheduled
        // contact, but the dashboard must not keep showing a stale 404.
        if ($DB->tableExists('glpi_plugin_ativaupdater_releases') && $DB->tableExists($clientsTable)) {
            $active = $DB->request([
                'FROM' => 'glpi_plugin_ativaupdater_releases',
                'WHERE' => ['active' => 1],
                'LIMIT' => 1,
            ]);
            if (count($active) === 1) {
                $activeRelease = $active->current();
                $activeVersion = (string) $activeRelease['version'];
                $allowDowngrade = (int) ($activeRelease['allow_downgrade'] ?? 0) === 1;
                $reportedClients = $DB->request(['FROM' => $clientsTable]);
                foreach ($reportedClients as $client) {
                    $noReleaseReport = str_contains((string) ($client['message'] ?? ''), 'NO_RELEASE')
                        || (string) $client['status'] === 'waiting_release';
                    if ($noReleaseReport && ReleasePolicy::requiresInstall((string) $client['installed_version'], $activeVersion, $allowDowngrade)) {
                        $DB->update($clientsTable, [
                            'available_version' => $activeVersion,
                            'status' => 'checking',
                            'message' => 'Nova versão publicada; aguardando a próxima consulta automática do serviço.',
                        ], ['id' => (int) $client['id']]);
                    }
                }
            }
        }

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
        'glpi_plugin_ativaupdater_clients',
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
