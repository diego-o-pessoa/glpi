/**
 * plugins/ativasplash/js/splash.js
 * Lógica de controle da Splash Screen corporativa.
 */

document.addEventListener("DOMContentLoaded", function() {
    const splash = document.getElementById("ativa-splash");
    if (!splash) return;

    const SESSION_KEY = "ativaSplashViewed";
    const ANIMATION_DURATION = 2700; // Tempo até iniciar o fade-out
    const EXIT_DURATION = 600; // Tempo do CSS transition de saída
    const FALLBACK_TIMEOUT = 5000; // Segurança extrema

    // Função para remover completamente a splash do DOM
    function destroySplash() {
        if (splash && splash.parentNode) {
            splash.parentNode.removeChild(splash);
        }
        document.body.classList.remove("ativa-splash-active");
    }

    // 1. Verificação de sessão (Apenas uma vez por aba/sessão)
    if (sessionStorage.getItem(SESSION_KEY)) {
        // Já viu a splash nesta sessão, destroi imediatamente
        destroySplash();
        return;
    }

    // Marca como visualizada para os próximos F5 ou navegações
    sessionStorage.setItem(SESSION_KEY, "true");

    // 2. Trava o scroll da página enquanto a splash acontece
    document.body.classList.add("ativa-splash-active");

    // 3. Agendar fim da animação
    setTimeout(() => {
        if (!splash.parentNode) return;
        
        // Adiciona a classe que engatilha o fade-out e o zoom-out no CSS
        splash.classList.add("splash-hide");

        // Aguarda a transição CSS terminar para remover do DOM
        setTimeout(destroySplash, EXIT_DURATION);
    }, ANIMATION_DURATION);

    // 4. Fallback de Segurança
    // Garante que, independentemente de abas inativas ou erros de timer, 
    // a splash jamais travará a tela de login permanentemente.
    setTimeout(destroySplash, FALLBACK_TIMEOUT);
});
