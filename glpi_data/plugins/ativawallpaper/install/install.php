<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\ConfigService;
use GlpiPlugin\Ativawallpaper\Maintenance;

function plugin_ativawallpaper_do_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAWALLPAPER_VERSION);
    $migration->displayMessage('Instalando Ativa Wallpaper ' . PLUGIN_ATIVAWALLPAPER_VERSION);

    try {
        $DB->runFile(__DIR__ . '/schema.sql');

        $clientsTable = 'glpi_plugin_ativawallpaper_clients';
        $migration->addField($clientsTable, 'next_check_at', 'datetime DEFAULT NULL', ['after' => 'last_check']);
        $migration->addField($clientsTable, 'check_interval_seconds', 'int unsigned DEFAULT NULL', ['after' => 'next_check_at']);
        $migration->addField($clientsTable, 'last_cycle_action', 'varchar(32) DEFAULT NULL', ['after' => 'check_interval_seconds']);
        $migration->addField($clientsTable, 'last_cycle_at', 'datetime DEFAULT NULL', ['after' => 'last_cycle_action']);
        $migration->addKey($clientsTable, ['next_check_at'], 'next_check_at');
        $migration->addField($clientsTable, 'rollout_id', 'varchar(64) DEFAULT NULL', ['after' => 'force_reapply']);
        $migration->addField($clientsTable, 'rollout_status', 'varchar(16) DEFAULT NULL', ['after' => 'rollout_id']);
        $migration->addField($clientsTable, 'rollout_started_at', 'datetime DEFAULT NULL', ['after' => 'rollout_status']);
        $migration->addField($clientsTable, 'rollout_finished_at', 'datetime DEFAULT NULL', ['after' => 'rollout_started_at']);
        $migration->addKey($clientsTable, ['rollout_id', 'rollout_status'], 'rollout');
        $migration->executeMigration();

        $storage = GLPI_PLUGIN_DOC_DIR . '/ativawallpaper';
        foreach ([$storage, $storage . '/wallpapers', $storage . '/thumbnails', $storage . '/tmp'] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('Nao foi possivel criar o diretorio privado do plugin.');
            }
        }

        ConfigService::installDefaults();
        $settings = ConfigService::all();
        $upgradeSettings = [];
        if (($settings['poll_interval_seconds'] ?? '') === '900'
            && ($settings['poll_jitter_seconds'] ?? '') === '120') {
            $upgradeSettings['poll_interval_seconds'] = '60';
            $upgradeSettings['poll_jitter_seconds'] = '10';
        }
        if (version_compare(ConfigService::get('latest_client_version'), '1.3.0', '<')) {
            $upgradeSettings['latest_client_version'] = '1.3.0';
        }
        if ($upgradeSettings !== []) {
            ConfigService::set($upgradeSettings);
        }
        ConfigService::set(['schema_version' => PLUGIN_ATIVAWALLPAPER_VERSION]);

        require_once PLUGIN_ATIVAWALLPAPER_DIR . '/inc/profile.class.php';
        PluginAtivawallpaperProfile::installRights();

        CronTask::Register(
            Maintenance::class,
            'reconcile',
            900,
            [
                'state'     => CronTask::STATE_WAITING,
                'mode'      => CronTask::MODE_EXTERNAL,
                'allowmode' => 3,
                'comment'   => 'Reconcilia clientes Ativa Wallpaper com computadores GLPI e limpa rate limits.',
            ]
        );
    } catch (Throwable $exception) {
        $migration->displayMessage('Falha na instalacao: ' . $exception->getMessage());
        return false;
    }

    return true;
}

function plugin_ativawallpaper_do_uninstall(): bool
{
    global $DB;

    $preserve = ConfigService::getBool('preserve_data_on_uninstall');

    require_once PLUGIN_ATIVAWALLPAPER_DIR . '/inc/profile.class.php';
    PluginAtivawallpaperProfile::uninstallRights();
    CronTask::unregister('Ativawallpaper');

    if (!$preserve) {
        foreach ([
            'glpi_plugin_ativawallpaper_rate_limits',
            'glpi_plugin_ativawallpaper_client_events',
            'glpi_plugin_ativawallpaper_audits',
            'glpi_plugin_ativawallpaper_clients',
            'glpi_plugin_ativawallpaper_wallpapers',
        ] as $table) {
            if ($DB->tableExists($table)) {
                $DB->doQuery('DROP TABLE `' . $table . '`');
            }
        }
        Config::deleteConfigurationValues(ConfigService::CONTEXT, array_keys(ConfigService::defaults()));
    }

    return true;
}
