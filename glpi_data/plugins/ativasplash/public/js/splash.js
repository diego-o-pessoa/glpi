// GLPI Ativa Splash Screen - Refatorado para Vídeo

(function() {
    // 1. Verificação de Sessão
    if (sessionStorage.getItem("ativaSplashViewed") === "true") {
        var splashEl = document.getElementById('ativa-splash');
        if (splashEl) splashEl.remove();
        // Remove the inline style that hides the card if the splash is skipped
        var formCard = document.querySelector('.main-content-card');
        if (formCard) formCard.style.opacity = '1';
        return;
    }

    var splash = document.getElementById('ativa-splash');
    var video = document.getElementById('ativa-intro-video');
    var animatedLogo = document.getElementById('ativa-animated-logo');
    var isFinished = false;

    if (!splash || !video || !animatedLogo) return;

    // Timeout de fallback de 5 segundos
    var fallbackTimeout = setTimeout(function() {
        if (!isFinished) finishSplash(true);
    }, 5000);

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        video.style.display = 'none';
        finishSplash(true); 
    } else {
        video.muted = true;
        var playPromise = video.play();

        if (playPromise !== undefined) {
            playPromise.catch(function(error) {
                console.warn("Ativa Splash: Autoplay bloqueado ou falha.", error);
                finishSplash(true);
            });
        }

        video.addEventListener("ended", function() {
            finishSplash(false);
        });

        video.addEventListener("error", function() {
            finishSplash(true);
        });
    }

    function finishSplash(fallback) {
        if (isFinished) return;
        isFinished = true;
        clearTimeout(fallbackTimeout);
        sessionStorage.setItem("ativaSplashViewed", "true");

        var formCard = document.querySelector('.main-content-card');
        var targetLogo = document.querySelector('.glpi-logo');

        if (fallback || !targetLogo) {
            // Em caso de falha no video, aborta animação e exibe o login
            if (formCard) {
                formCard.style.opacity = '1';
            }
            splash.remove();
            return;
        }

        // 1. Crossfade do vídeo para o layout horizontal DOM
        video.style.opacity = '0';
        animatedLogo.classList.add('ativa-show-logo');

        // 2. Iniciar reordenação (Horizontal -> Vertical)
        setTimeout(function() {
            animatedLogo.classList.remove('horizontal');
            animatedLogo.classList.add('vertical');

            // 3. Aguardar CSS reordenar a logo (600ms no CSS + folga)
            setTimeout(function() {
                
                // Preparar FLIP (First, Last, Invert, Play)
                var srcRect = animatedLogo.getBoundingClientRect();
                var dstRect = targetLogo.getBoundingClientRect();

                // Calcular escala preservando proporção
                var scaleX = dstRect.width / srcRect.width;
                var scaleY = dstRect.height / srcRect.height;
                var scale = Math.min(scaleX, scaleY);

                var deltaX = dstRect.left + (dstRect.width / 2) - (srcRect.left + (srcRect.width / 2));
                var deltaY = dstRect.top + (dstRect.height / 2) - (srcRect.top + (srcRect.height / 2));

                // Oculta temporariamente a logo nativa para não dar duplo visual
                targetLogo.style.opacity = '0';

                // Voo da Logo
                animatedLogo.style.transition = 'transform 1s cubic-bezier(0.25, 1, 0.5, 1)';
                animatedLogo.style.transform = 'translate3d(' + deltaX + 'px, ' + deltaY + 'px, 0) scale(' + scale + ')';

                // Revelação suave do formulário de login
                if (formCard) {
                    formCard.classList.add('show-login');
                }

                // 4. Chegada ao destino
                setTimeout(function() {
                    targetLogo.style.opacity = '1';
                    splash.classList.add('ativa-fade-out');

                    setTimeout(function() {
                        splash.remove();
                    }, 350);

                }, 1000); // 1000ms do tempo de voo (FLIP)
                
            }, 650); 
            
        }, 150); 
    }
})();
