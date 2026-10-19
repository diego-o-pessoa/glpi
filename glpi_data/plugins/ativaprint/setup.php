<?php

declare(strict_types=1);

define('PLUGIN_ATIVAPRINT_VERSION', '1.0.0');
define('PLUGIN_ATIVAPRINT_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVAPRINT_MAX_GLPI', '12.0.0');
define('PLUGIN_ATIVAPRINT_DIR', __DIR__);

// Autoloader PSR-4 para classes de GlpiPlugin\Ativaprint\...
spl_autoload_register(static function (string $class): void {
    $prefix = 'GlpiPlugin\\Ativaprint\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/inc/menu.class.php';
require_once __DIR__ . '/inc/profile.class.php';

/**
 * Inicialização dos ganchos e classes do Ativa Print.
 */
function plugin_init_ativaprint(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['ativaprint'] = true;

    Plugin::registerClass('PluginAtivaprintMenu');
    Plugin::registerClass('PluginAtivaprintProfile', ['addtabon' => ['Profile']]);

    $PLUGIN_HOOKS['menu_toadd']['ativaprint']['admin'] = 'PluginAtivaprintMenu';
    $PLUGIN_HOOKS['config_page']['ativaprint'] = 'front/config.php';

    plugin_ativaprint_refresh_cached_menu();
}

/**
 * Se o menu do GLPI foi cacheado na sessão antes do Ativa Print existir,
 * invalida o cache para que o GLPI reconstrua a barra lateral incluindo o Ativa Print.
 */
function plugin_ativaprint_refresh_cached_menu(): void
{
    $marker = PLUGIN_ATIVAPRINT_VERSION . '|ativaprint_v1';
    if (!isset($_SESSION['glpimenu']) || !is_array($_SESSION['glpimenu'])
        || ($_SESSION['plugin_ativaprint_menu_checked'] ?? '') === $marker) {
        return;
    }
    $_SESSION['plugin_ativaprint_menu_checked'] = $marker;

    $hasPrint = false;
    foreach ($_SESSION['glpimenu'] as $sector) {
        if (is_array($sector) && isset($sector['content']['pluginativaprintmenu'])) {
            $hasPrint = true;
            break;
        }
    }
    if (!$hasPrint) {
        unset($_SESSION['glpimenu']);
    }
}

function plugin_version_ativaprint(): array
{
    return [
        'name'           => 'Ativa Print',
        'version'        => PLUGIN_ATIVAPRINT_VERSION,
        'author'         => 'Ativa Locação',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_ATIVAPRINT_MIN_GLPI,
                'max' => PLUGIN_ATIVAPRINT_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_ativaprint_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_ATIVAPRINT_MIN_GLPI, '<') || version_compare(GLPI_VERSION, PLUGIN_ATIVAPRINT_MAX_GLPI, '>=')) {
        echo 'Este plugin requer o GLPI >= ' . PLUGIN_ATIVAPRINT_MIN_GLPI . ' e < ' . PLUGIN_ATIVAPRINT_MAX_GLPI;
        return false;
    }
    return true;
}

function plugin_ativaprint_check_config(): bool
{
    return true;
}

