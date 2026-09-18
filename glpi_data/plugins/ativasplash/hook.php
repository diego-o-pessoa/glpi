<?php

declare(strict_types=1);

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_ativasplash_install(): bool
{
    return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_ativasplash_uninstall(): bool
{
    return true;
}

/**
 * Hook to display splash screen logic only on the login page.
 */
function plugin_ativasplash_display_login(): void
{
    global $CFG_GLPI;

    // Inject CSS
    echo '<link rel="stylesheet" type="text/css" href="' . $CFG_GLPI["root_doc"] . '/plugins/ativasplash/css/splash.css?v=' . PLUGIN_ATIVASPLASH_VERSION . '">';

    // Inject HTML structure for the splash screen
    echo '
    <div id="ativa-splash" aria-hidden="true">
        <div class="ativa-splash-wrapper">
            <div class="ativa-splash-layer ativa-splash-blue"></div>
            <div class="ativa-splash-layer ativa-splash-green"></div>
            <div class="ativa-splash-layer ativa-splash-text-ativa"></div>
            <div class="ativa-splash-layer ativa-splash-text-locacao"></div>
        </div>
    </div>';

    // Inject JS
    echo '<script type="text/javascript" src="' . $CFG_GLPI["root_doc"] . '/plugins/ativasplash/js/splash.js?v=' . PLUGIN_ATIVASPLASH_VERSION . '"></script>';
}
