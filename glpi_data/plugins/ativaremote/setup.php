<?php

declare(strict_types=1);

define('PLUGIN_ATIVAREMOTE_VERSION', '1.0.0');
define('PLUGIN_ATIVAREMOTE_MIN_GLPI', '10.0.0');
define('PLUGIN_ATIVAREMOTE_MAX_GLPI', '12.0.0');

/**
 * Init the hooks of the plugin
 */
function plugin_init_ativaremote(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['ativaremote'] = true;
    
    Plugin::registerClass('PluginAtivaremoteMenu');
    $PLUGIN_HOOKS['menu_toadd']['ativaremote']['admin'] = 'PluginAtivaremoteMenu';
    $PLUGIN_HOOKS['config_page']['ativaremote'] = 'front/dashboard.php';

    // For GLPI API
    $PLUGIN_HOOKS['item_get_events_and_emails_hooks']['ativaremote'] = true;
}

/**
 * GLPI builds the side menu once per login and keeps it in $_SESSION['glpimenu'];
 * deploying and clearing the cache does not rebuild it. Sessions opened before a
 * menu change kept the old link. When the cached Ativa Remote entry differs from
 * the current one, drop the cached menu so GLPI rebuilds it on this same request.
 */
function plugin_ativaremote_refresh_cached_menu(): void
{
    global $CFG_GLPI;

    $marker = PLUGIN_ATIVAREMOTE_VERSION . '|dashboard';
    if (!isset($_SESSION['glpimenu']) || !is_array($_SESSION['glpimenu'])
        || ($_SESSION['plugin_ativaremote_menu_checked'] ?? '') === $marker) {
        return;
    }
    $_SESSION['plugin_ativaremote_menu_checked'] = $marker;

    $expectedPage = $CFG_GLPI['root_doc'] . '/plugins/ativaremote/front/dashboard.php';
    $expectedTitle = PluginAtivaremoteMenu::getMenuName();
    foreach ($_SESSION['glpimenu'] as $sector) {
        $entry = is_array($sector) ? ($sector['content'][strtolower(PluginAtivaremoteMenu::class)] ?? null) : null;
        if (is_array($entry) && (($entry['page'] ?? '') !== $expectedPage || ($entry['title'] ?? '') !== $expectedTitle)) {
            unset($_SESSION['glpimenu']);
            return;
        }
    }
}

/**
 * Get the name and the version of the plugin
 */
function plugin_version_ativaremote(): array
{
    return [
        'name'           => 'Ativa Remote',
        'version'        => PLUGIN_ATIVAREMOTE_VERSION,
        'author'         => 'Ativa',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_ATIVAREMOTE_MIN_GLPI,
                'max' => PLUGIN_ATIVAREMOTE_MAX_GLPI,
            ]
        ]
    ];
}

/**
 * Check prerequisites before install
 */
function plugin_ativaremote_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_ATIVAREMOTE_MIN_GLPI, '<') || version_compare(GLPI_VERSION, PLUGIN_ATIVAREMOTE_MAX_GLPI, '>=')) {
        echo "Este plugin requer o GLPI >= " . PLUGIN_ATIVAREMOTE_MIN_GLPI . " e < " . PLUGIN_ATIVAREMOTE_MAX_GLPI;
        return false;
    }
    return true;
}

/**
 * Check config before install
 */
function plugin_ativaremote_check_config(): bool
{
    return true;
}
