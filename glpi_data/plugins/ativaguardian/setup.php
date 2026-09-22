<?php

declare(strict_types=1);

use Glpi\Http\SessionManager;
use Glpi\Plugin\Hooks;

define('PLUGIN_ATIVAGUARDIAN_VERSION', '1.2.0');
define('PLUGIN_ATIVAGUARDIAN_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVAGUARDIAN_MAX_GLPI', '11.1.0');
define('PLUGIN_ATIVAGUARDIAN_DIR', __DIR__);

function plugin_version_ativaguardian(): array
{
    return [
        'name'         => 'Ativa Guardian',
        'version'      => PLUGIN_ATIVAGUARDIAN_VERSION,
        'author'       => 'Ativa Locacao',
        'license'      => 'GPLv3+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ATIVAGUARDIAN_MIN_GLPI,
                'max' => PLUGIN_ATIVAGUARDIAN_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_init_ativaguardian(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['ativaguardian'] = true;

    // The heartbeat API authenticates with a bearer token, not a GLPI session.
    // Same stateless-path registration the Ativa Updater uses for its service API.
    SessionManager::registerPluginStatelessPath('ativaguardian', '#^/api/v1(?:/|$)#');

    $plugin = new Plugin();
    if (!$plugin->isActivated('ativaguardian')) {
        return;
    }

    Plugin::registerClass(PluginAtivaguardianMenu::class);
    Plugin::registerClass(PluginAtivaguardianProfile::class, ['addtabon' => [Profile::class]]);

    $isPluginAdministrator = Session::haveRight('config', UPDATE);

    if (Session::haveRight(PluginAtivaguardianProfile::RIGHT_VIEW, READ) || $isPluginAdministrator) {
        $PLUGIN_HOOKS['menu_toadd']['ativaguardian']['admin'] = PluginAtivaguardianMenu::class;
    }

    if (Session::haveRight(PluginAtivaguardianProfile::RIGHT_CONFIG, UPDATE) || $isPluginAdministrator) {
        $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['ativaguardian'] = 'front/settings.php';
    }
}

function plugin_ativaguardian_check_prerequisites(): bool
{
    if (version_compare(PHP_VERSION, '8.2.0', '<')) {
        echo 'Ativa Guardian requer PHP 8.2 ou superior.';
        return false;
    }

    if (!defined('GLPI_VERSION') || version_compare(GLPI_VERSION, PLUGIN_ATIVAGUARDIAN_MIN_GLPI, '<')) {
        echo 'Ativa Guardian requer GLPI 11.0 ou superior.';
        return false;
    }

    return true;
}

function plugin_ativaguardian_check_config(): bool
{
    return true;
}
