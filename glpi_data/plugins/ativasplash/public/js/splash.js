// GLPI Ativa Splash Screen
// Sequencia: video -> logo horizontal -> logo vertical -> encolhe/sobe ate o
// lugar da logo GLPI. Motion via transform/opacity. Nunca bloqueia o login:
// qualquer falha cai em finishImmediately(). A logo permanente da Ativa fica
// via CSS (repinta span.glpi-logo), independente desta animacao.

(function () {
    "use strict";

    // Sessao ja vista: nao anima de novo (a logo permanente ja esta via CSS).
    if (sessionStorage.getItem("ativaSplashViewed") === "true") {
        var seen = document.getElementById("ativa-splash");
        if (seen) seen.remove();
        return;
    }

    var splash = document.getElementById("ativa-splash");
    var video = document.getElementById("ativa-intro-video");
    var stage = document.getElementById("ativa-stage");
    var symbol = document.getElementById("ativa-symbol");

    if (!splash || !stage) return;

    var isFinished = false;
    var started = false;

    // Altura, em px de design, do conteudo vertical simetrico no palco (topo
    // -106 -> base +106). Usada para calcular a escala do FLIP.
    var VERTICAL_CONTENT_H = 212;

    // Timeout de seguranca: se qualquer etapa travar, encerra.
    var fallbackTimeout = setTimeout(finishImmediately, 6000);

    var prefersReducedMotion =
        window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (prefersReducedMotion || !video) {
        // Sem animacao: overlay sai, logo permanente (CSS) aparece.
        finishImmediately();
        return;
    }

    // --- Video ---
    video.muted = true;
    var playPromise = video.play();
    if (playPromise !== undefined) {
        playPromise.catch(function (error) {
            console.warn("Ativa Splash: autoplay bloqueado ou falha no video.", error);
            finishImmediately();
        });
    }
    video.addEventListener("ended", startLogoAnimation);
    video.addEventListener("error", finishImmediately);

    // --- Etapa 1: revela o palco horizontal e transforma em vertical ---
    function startLogoAnimation() {
        if (isFinished || started) return;
        started = true;

        stage.classList.add("ativa-show");
        video.style.opacity = "0";

        // Deixa o horizontal visivel por um instante antes de verticalizar.
        setTimeout(function () {
            if (isFinished) return;
            stage.classList.add("ativa-vertical");
            onTransformEnd(symbol, 750, flipToHeader);
        }, 140);
    }

    // --- Etapa 2: FLIP -> encolhe e sobe ate o lugar da logo GLPI ---
    function flipToHeader() {
        if (isFinished) return;

        var target = document.querySelector("body.welcome-anonymous span.glpi-logo");
        var t = target ? target.getBoundingClientRect() : null;

        // Sem alvo valido: nao arrisca coordenada fixa; so encerra suavemente.
        if (!t || t.width === 0 || t.height === 0) {
            finish();
            return;
        }

        var sRect = stage.getBoundingClientRect();
        var scx = sRect.left + sRect.width / 2;
        var scy = sRect.top + sRect.height / 2; // = centro do conteudo (simetrico)
        var tcx = t.left + t.width / 2;
        var tcy = t.top + t.height / 2;

        var scale = t.height / VERTICAL_CONTENT_H;
        var tx = tcx - scx;
        var ty = tcy - scy;

        stage.style.transform =
            "translate3d(" + tx + "px," + ty + "px,0) scale(" + scale + ")";

        onTransformEnd(stage, 600, finish);
    }

    // --- Encerramento normal: fade-out do overlay, revela a logo permanente ---
    function finish() {
        if (isFinished) return;
        isFinished = true;
        clearTimeout(fallbackTimeout);
        sessionStorage.setItem("ativaSplashViewed", "true");

        splash.classList.add("ativa-fade-out");
        setTimeout(function () {
            splash.remove();
        }, 350);
    }

    // --- Encerramento imediato (fallbacks): remove overlay sem animar ---
    function finishImmediately() {
        if (isFinished) return;
        isFinished = true;
        clearTimeout(fallbackTimeout);
        sessionStorage.setItem("ativaSplashViewed", "true");
        splash.remove();
    }

    // Aguarda o fim da transicao de transform, com timeout de seguranca.
    function onTransformEnd(el, timeoutMs, cb) {
        var done = false;
        function handler(e) {
            if (e && e.propertyName && e.propertyName !== "transform") return;
            if (done) return;
            done = true;
            el.removeEventListener("transitionend", handler);
            cb();
        }
        el.addEventListener("transitionend", handler);
        setTimeout(function () {
            if (done) return;
            done = true;
            el.removeEventListener("transitionend", handler);
            cb();
        }, timeoutMs);
    }
})();
