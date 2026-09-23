<?php

declare(strict_types=1);

define('PLUGIN_ATIVASPLASH_VERSION', '2.2.1');

/**
 * Init the hooks of the plugin
 */
function plugin_init_ativasplash(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['ativasplash'] = true;

    // Use display_login hook to inject our splash HTML/CSS/JS only on the login screen
    $PLUGIN_HOOKS['display_login']['ativasplash'] = 'plugin_ativasplash_display_login';

    // <head> das paginas anonimas: vale desde o primeiro paint (logo Ativa no
    // lugar da GLPI + cobertura branca ate a splash assumir). O display_login
    // so e chamado no meio do formulario, tarde demais para isso.
    $PLUGIN_HOOKS[\Glpi\Plugin\Hooks::ADD_CSS_ANONYMOUS_PAGE]['ativasplash'] = ['css/splash-head.css'];
    $PLUGIN_HOOKS[\Glpi\Plugin\Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE]['ativasplash'] = ['js/splash-head.js'];
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
