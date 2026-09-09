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

        $storage = GLPI_PLUGIN_DOC_DIR . '/ativawallpaper';
        foreach ([$storage, $storage . '/wallpapers', $storage . '/thumbnails', $storage . '/tmp'] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('Nao foi possivel criar o diretorio privado do plugin.');
            }
        }

        ConfigService::installDefaults();

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
