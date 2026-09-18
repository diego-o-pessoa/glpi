// GLPI Ativa Splash Screen - Refatorado para Vídeo

(function() {
    // 1. Verificação de Sessão já manipulada parcialmente no inline script do hook.php
    // Aqui garantimos a redundância caso o JS carregue antes ou em outro contexto.
    if (sessionStorage.getItem("ativaSplashViewed") === "true") {
        var splashEl = document.getElementById('ativa-splash');
        if (splashEl) splashEl.remove();
        return;
    }

    var splash = document.getElementById('ativa-splash');
    var video = document.getElementById('ativa-intro-video');
    var logo = document.getElementById('ativa-final-logo');
    var isFinished = false;

    if (!splash || !video || !logo) return;

    // Timeout máximo de segurança (ex: 7 segundos). O vídeo tem 9s? 
    // Ajuste o timeout se o vídeo for mais longo. Colocarei 11 segundos por segurança máxima.
    var fallbackTimeout = setTimeout(function() {
        if (!isFinished) finishSplash();
    }, 12000);

    // Preferência do usuário por movimento reduzido
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        // Se o usuário não gosta de animações, pulamos o vídeo
        video.style.display = 'none';
        finishSplash(true); 
    } else {
        // Inicializa o vídeo
        var playPromise = video.play();

        if (playPromise !== undefined) {
            playPromise.catch(function(error) {
                // Autoplay bloqueado ou erro de carregamento
                console.warn("Ativa Splash: Autoplay bloqueado ou falha no vídeo.", error);
                finishSplash();
            });
        }

        // Eventos do vídeo
        video.addEventListener("ended", function() {
            finishSplash();
        });

        video.addEventListener("error", function() {
            finishSplash();
        });
    }

    // Função central que gerencia o término
    function finishSplash(skipVideoFade) {
        if (isFinished) return;
        isFinished = true;
        clearTimeout(fallbackTimeout);

        // Marca a sessão
        sessionStorage.setItem("ativaSplashViewed", "true");

        // 1. Ocultar o vídeo e exibir a logo (Crossfade)
        if (!skipVideoFade) {
            video.style.opacity = '0';
        }
        logo.classList.add('ativa-show-logo');

        // 2. Pequena permanência da logo oficial (200ms)
        setTimeout(function() {
            
            // 3. Fade da splash inteira (350ms definidos no CSS)
            splash.classList.add('ativa-fade-out');

            // 4. Remove do DOM após a transição
            setTimeout(function() {
                splash.remove();
            }, 400); // 400ms para garantir que o fade-out do CSS completou

        }, 200); 
    }
})();
