/*
 * Ativa Central - visual classico do GLPI nesta sessao.
 *
 * Carregado so quando o visual novo esta ligado para todos, mas esta pessoa
 * pediu o classico: acrescenta ao menu do usuario o atalho para voltar.
 */
(function () {
    'use strict';

    function start() {
        var data = {};
        try {
            var meta = document.querySelector('meta[name="ativacentral"]');
            data = meta ? JSON.parse(meta.getAttribute('content') || '{}') : {};
        } catch (error) {
            return;
        }
        if (!data.toggle) {
            return;
        }
        Array.prototype.forEach.call(document.querySelectorAll('.user-menu .dropdown-menu'), function (menu) {
            if (menu.querySelector('.ac-user-item')) {
                return;
            }
            var divider = document.createElement('div');
            divider.className = 'dropdown-divider';
            var link = document.createElement('a');
            link.className = 'dropdown-item ac-user-item';
            link.href = data.toggle;
            var icon = document.createElement('i');
            icon.className = 'ti ti-sparkles';
            link.appendChild(icon);
            link.appendChild(document.createTextNode(' Voltar ao novo visual (Ativa Central)'));
            menu.appendChild(divider);
            menu.appendChild(link);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
