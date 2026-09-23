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

    // CSS critico INLINE: o overlay precisa cobrir a tela ja no primeiro paint.
    // Sem isso, o splash.css externo entra na fila atras de todo o CSS do GLPI
    // e, ate baixar, o login aparece normal (com a logo GLPI) por alguns
    // segundos antes da animacao. Inline aplica na hora de parsear a marcacao.
    echo '<style>'
        . '#ativa-splash{position:fixed;inset:0;width:100vw;height:100vh;'
        . 'z-index:2147483647;background:#fff;display:flex;align-items:center;'
        . 'justify-content:center;overflow:hidden}'
        . '#ativa-intro-video{position:absolute;inset:0;width:100%;height:100%;'
        . 'object-fit:contain;background:#fff}'
        . '#ativa-stage{opacity:0}'
        . '</style>';

    // CSS completo (transicoes, estados da animacao, logo permanente).
    echo '<link rel="stylesheet" type="text/css" href="' . $plugin_url . '/css/splash.css?v=' . $version . '">';

    // Inject HTML structure for the splash screen.
    // O palco (#ativa-stage) tem o simbolo e o wordmark como elementos separados,
    // para a animacao horizontal -> vertical mover o texto de verdade (nao crossfade).
    echo '
    <div id="ativa-splash" aria-hidden="true">
        <video
            id="ativa-intro-video"
            autoplay
            muted
            playsinline
            preload="auto"
        >
            <source src="' . $plugin_url . '/assets/video/ativa-intro.mp4?v=' . $version . '" type="video/mp4">
        </video>
        <div id="ativa-stage">
            <img id="ativa-symbol" src="' . $plugin_url . '/assets/img/ativa-symbol.png?v=' . $version . '" alt="">
            <img id="ativa-wordmark" src="' . $plugin_url . '/assets/img/ativa-wordmark.png?v=' . $version . '" alt="Ativa Locação">
        </div>
    </div>
    <script>
        if (sessionStorage.getItem("ativaSplashViewed") === "true") {
            var s = document.getElementById("ativa-splash");
            if (s) s.style.display = "none";
        }
    </script>';

    // Inject JS
    echo '<script type="text/javascript" src="' . $plugin_url . '/js/splash.js?v=' . $version . '"></script>';
}
