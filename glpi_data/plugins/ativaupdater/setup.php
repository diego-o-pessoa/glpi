<?php

declare(strict_types=1);

use Glpi\Http\SessionManager;
use Glpi\Plugin\Hooks;

define('PLUGIN_ATIVAUPDATER_VERSION', '1.0.0');
define('PLUGIN_ATIVAUPDATER_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVAUPDATER_MAX_GLPI', '11.1.0');
define('PLUGIN_ATIVAUPDATER_DIR', __DIR__);

function plugin_version_ativaupdater(): array
{
    return [
        'name'         => 'Ativa Updater',
        'version'      => PLUGIN_ATIVAUPDATER_VERSION,
        'author'       => 'Ativa Locacao',
        'license'      => 'GPLv3+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ATIVAUPDATER_MIN_GLPI,
                'max' => PLUGIN_ATIVAUPDATER_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_init_ativaupdater(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['ativaupdater'] = true;

    // Register API stateless path for token authentication
    SessionManager::registerPluginStatelessPath('ativaupdater', '#^/api/v1(?:/|$)#');

    $plugin = new Plugin();
    if (!$plugin->isActivated('ativaupdater')) {
        return;
    }

    Plugin::registerClass(PluginAtivaupdaterMenu::class);
    Plugin::registerClass(PluginAtivaupdaterRelease::class);
    Plugin::registerClass(PluginAtivaupdaterProfile::class, ['addtabon' => [Profile::class]]);

    if (Session::haveRight(PluginAtivaupdaterProfile::RIGHT_VIEW, READ)) {
        $PLUGIN_HOOKS['menu_toadd']['ativaupdater']['admin'] = PluginAtivaupdaterMenu::class;
    }

    if (Session::haveRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, UPDATE)) {
        $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['ativaupdater'] = 'front/settings.php';
    }
}

function plugin_ativaupdater_check_prerequisites(): bool
{
    if (version_compare(PHP_VERSION, '8.2.0', '<')) {
        echo 'Ativa Updater requer PHP 8.2 ou superior.';
        return false;
    }

    if (!defined('GLPI_VERSION') || version_compare(GLPI_VERSION, PLUGIN_ATIVAUPDATER_MIN_GLPI, '<')) {
        echo 'Ativa Updater requer GLPI 11.0 ou superior.';
        return false;
    }

    return true;
}

function plugin_ativaupdater_check_config(): bool
{
    return true;
}
