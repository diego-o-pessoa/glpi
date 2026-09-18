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

    // Utiliza a versão estrita do plugin para cache. Ao atualizar a versão em setup.php, o cache de todos os PCs é limpo.
    $version = PLUGIN_ATIVASPLASH_VERSION;

    // Inject CSS
    echo '<link rel="stylesheet" type="text/css" href="' . $CFG_GLPI["root_doc"] . '/plugins/ativasplash/css/splash.css?v=' . $version . '">';

    // Inject HTML structure for the splash screen
    echo '
    <div id="ativa-splash" aria-hidden="true">
        <video 
            id="ativa-intro-video" 
            autoplay 
            muted 
            playsinline 
            preload="auto"
        >
            <source src="' . $CFG_GLPI["root_doc"] . '/plugins/ativasplash/assets/video/ativa-intro.mp4?v=2.0.1" type="video/mp4">
        </video>
        <img 
            id="ativa-final-logo" 
            src="' . $CFG_GLPI["root_doc"] . '/plugins/ativasplash/assets/img/ativa-logo.webp?v=' . $version . '" 
            alt="Ativa Locação"
        >
    </div>
    <script>
        if (sessionStorage.getItem("ativaSplashViewed") === "true") {
            document.getElementById("ativa-splash").style.display = "none";
        }
    </script>';

    // Inject JS
    echo '<script type="text/javascript" src="' . $CFG_GLPI["root_doc"] . '/plugins/ativasplash/js/splash.js?v=' . $version . '"></script>';
}
