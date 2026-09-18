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
    $plugin_url = Plugin::getWebDir('ativasplash');

    // Inject CSS
    echo '<link rel="stylesheet" type="text/css" href="' . $plugin_url . '/css/splash.css?v=' . $version . '">';

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
            <source src="' . $plugin_url . '/assets/video/ativa-intro.mp4?v=2.0.2" type="video/mp4">
        </video>
        <div id="ativa-animated-logo" class="horizontal">
            <div class="ativa-clip ativa-symbol"></div>
            <div class="ativa-clip ativa-ativa"></div>
            <div class="ativa-clip ativa-locacao"></div>
        </div>
    </div>
    <script>
        if (sessionStorage.getItem("ativaSplashViewed") === "true") {
            document.getElementById("ativa-splash").style.display = "none";
        } else {
            // Hide the login card immediately if splash is running to prepare for fade in
            var style = document.createElement("style");
            style.innerHTML = ".main-content-card { opacity: 0; transition: opacity 1s ease; }";
            document.head.appendChild(style);
        }
    </script>';

    // Inject JS
    echo '<script type="text/javascript" src="' . $plugin_url . '/js/splash.js?v=' . $version . '"></script>';
}
