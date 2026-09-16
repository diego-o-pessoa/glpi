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
    $PLUGIN_HOOKS['menu_toadd']['ativaremote'] = ['plugins' => 'PluginAtivaremoteMenu'];

    // For GLPI API
    $PLUGIN_HOOKS['item_get_events_and_emails_hooks']['ativaremote'] = true;
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
