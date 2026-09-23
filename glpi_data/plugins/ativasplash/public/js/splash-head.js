// Ativa Splash - JS do <head> (paginas anonimas), roda antes do primeiro paint.
// Marca o <html> quando a splash vai rodar, para o splash-head.css cobrir a
// tela de branco: assim o login nao aparece antes da animacao.

(function () {
    "use strict";

    var root = document.documentElement;

    // Mesmas condicoes em que o splash.js pula a animacao: nao cobre.
    try {
        if (sessionStorage.getItem("ativaSplashViewed") === "true") return;
        if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    } catch (e) {
        return;
    }

    root.classList.add("ativa-splash-pending");

    function release() {
        root.classList.remove("ativa-splash-pending");
    }

    // Pagina anonima sem splash (ex.: esqueci minha senha): libera ja.
    document.addEventListener("DOMContentLoaded", function () {
        if (!document.getElementById("ativa-splash")) release();
    });

    // Nunca deixa a tela coberta (o splash.js encerra em no maximo ~12s).
    setTimeout(release, 13000);
})();
