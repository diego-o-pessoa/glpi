<?php

declare(strict_types=1);

define('PLUGIN_ATIVASPLASH_VERSION', '2.1.0');

/**
 * Init the hooks of the plugin
 */
function plugin_init_ativasplash(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['ativasplash'] = true;

    // Use display_login hook to inject our splash HTML/CSS/JS only on the login screen
    $PLUGIN_HOOKS['display_login']['ativasplash'] = 'plugin_ativasplash_display_login';
}

/**
 * Get the name and the version of the plugin
 */
function plugin_version_ativasplash(): array
{
    return [
        'name'           => 'Ativa Splash',
        'version'        => PLUGIN_ATIVASPLASH_VERSION,
        'author'         => 'Ativa',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => '10.0.0'
            ]
        ]
    ];
}

/**
 * Check prerequisites before install
 */
function plugin_ativasplash_check_prerequisites(): bool
{
    return true;
}

/**
 * Check config before install
 */
function plugin_ativasplash_check_config(): bool
{
    return true;
}
