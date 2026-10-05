<?php

declare(strict_types=1);

use Glpi\Http\SessionManager;
use Glpi\Plugin\Hooks;

define('PLUGIN_ATIVAWALLPAPER_VERSION', '1.5.3');
define('PLUGIN_ATIVAWALLPAPER_API_VERSION', 'v1');
define('PLUGIN_ATIVAWALLPAPER_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVAWALLPAPER_MAX_GLPI', '11.1.0');
define('PLUGIN_ATIVAWALLPAPER_DIR', __DIR__);

function plugin_version_ativawallpaper(): array
{
    return [
        'name'         => 'Ativa Wallpaper',
        'version'      => PLUGIN_ATIVAWALLPAPER_VERSION,
        'author'       => 'Ativa Locacao',
        'license'      => 'GPLv3+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ATIVAWALLPAPER_MIN_GLPI,
                'max' => PLUGIN_ATIVAWALLPAPER_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

/**
 * Roda ANTES de o GLPI iniciar a sessao PHP. A API dos servicos (token
 * Bearer) tem que ser registrada aqui como stateless; registrada no
 * plugin_init (que roda depois), cada chamada criava um arquivo em
 * files/_sessions.
 */
function plugin_ativawallpaper_boot(): void
{
    SessionManager::registerPluginStatelessPath('ativawallpaper', '#^/api/v1(?:/|$)#');
}

function plugin_init_ativawallpaper(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['ativawallpaper'] = true;


    $plugin = new Plugin();
    if (!$plugin->isActivated('ativawallpaper')) {
        return;
    }

    $PLUGIN_HOOKS[Hooks::PRE_INVENTORY]['ativawallpaper'] = 'plugin_ativawallpaper_pre_inventory';
    $PLUGIN_HOOKS[Hooks::INIT_SESSION]['ativawallpaper'] = 'plugin_ativawallpaper_init_session';

    Plugin::registerClass(PluginAtivawallpaperMenu::class);
    Plugin::registerClass(
        PluginAtivawallpaperProfile::class,
        ['addtabon' => [Profile::class]]
    );
    Plugin::registerClass(
        PluginAtivawallpaperClient::class,
        ['addtabon' => [Computer::class]]
    );

    if (Session::haveRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ)) {
        $PLUGIN_HOOKS['menu_toadd']['ativawallpaper']['admin'] = PluginAtivawallpaperMenu::class;
        plugin_ativawallpaper_refresh_cached_menu();
    }

    if (Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE)) {
        $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['ativawallpaper'] = 'front/settings.php';
    }

    $PLUGIN_HOOKS[Hooks::ADD_CSS]['ativawallpaper'][] = 'css/ativawallpaper.css';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['ativawallpaper'][] = 'js/ativawallpaper.js';
}

/**
 * GLPI builds the side menu once per login and keeps it in $_SESSION['glpimenu'];
 * deploying and clearing the cache does not rebuild it. Sessions opened before a
 * menu change kept the old link (Configuracoes) and title (Agent). When the cached
 * Ativa Wallpaper entry differs from the current one, drop the cached menu so GLPI
 * rebuilds it on this same request. Checked once per session.
 */
function plugin_ativawallpaper_refresh_cached_menu(): void
{
    global $CFG_GLPI;

    $marker = PLUGIN_ATIVAWALLPAPER_VERSION . '|dashboard';
    if (!isset($_SESSION['glpimenu']) || !is_array($_SESSION['glpimenu'])
        || ($_SESSION['plugin_ativawallpaper_menu_checked'] ?? '') === $marker) {
        return;
    }
    $_SESSION['plugin_ativawallpaper_menu_checked'] = $marker;

    $expectedPage = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper/front/dashboard.php';
    $expectedTitle = PluginAtivawallpaperMenu::getTypeName();
    foreach ($_SESSION['glpimenu'] as $sector) {
        $entry = is_array($sector) ? ($sector['content'][strtolower(PluginAtivawallpaperMenu::class)] ?? null) : null;
        if (is_array($entry) && (($entry['page'] ?? '') !== $expectedPage || ($entry['title'] ?? '') !== $expectedTitle)) {
            unset($_SESSION['glpimenu']);
            return;
        }
    }
}

function plugin_ativawallpaper_check_prerequisites(): bool
{
    if (version_compare(PHP_VERSION, '8.2.0', '<')) {
        echo 'Ativa Wallpaper requer PHP 8.2 ou superior.';
        return false;
    }

    if (!defined('GLPI_VERSION') || version_compare(GLPI_VERSION, PLUGIN_ATIVAWALLPAPER_MIN_GLPI, '<')) {
        echo 'Ativa Wallpaper requer GLPI 11.0 ou superior.';
        return false;
    }

    foreach (['fileinfo', 'gd', 'openssl'] as $extension) {
        if (!extension_loaded($extension)) {
            echo sprintf('Extensao PHP obrigatoria ausente: %s.', $extension);
            return false;
        }
    }

    return true;
}

function plugin_ativawallpaper_check_config(): bool
{
    return true;
}
