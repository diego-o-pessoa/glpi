/*
 * Ativa Central - acabamento da moldura em todas as paginas.
 *
 * A barra lateral e o topo ja chegam prontos do servidor e o CSS (shell.css)
 * cuida da aparencia sozinho. Este arquivo so acrescenta o que o HTML do GLPI
 * nao permite: selo de chamados novos, item ativo correto, botoes do topo e
 * os atalhos no menu do usuario. Se algo aqui falhar, a pagina segue normal.
 */
(function () {
    'use strict';

    var data = {};
    try {
        var meta = document.querySelector('meta[name="ativacentral"]');
        data = meta ? JSON.parse(meta.getAttribute('content') || '{}') : {};
    } catch (error) {
        data = {};
    }

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) { node.className = className; }
        if (text !== undefined && text !== null) { node.textContent = text; }
        return node;
    }

    function icon(name) {
        return el('i', 'ti ' + name);
    }

    /* "/plugins/ativarede/front/planta.php" -> "ativarede". */
    function pluginOf(path) {
        var match = /\/(?:plugins|marketplace)\/([^/]+)\//.exec(path);
        return match ? match[1].toLowerCase() : '';
    }

    /* "/front/knowbaseitem.form.php" -> "knowbaseitem". */
    function frontBase(path) {
        var match = /\/front\/([a-z0-9_]+?)(?:\.form)?\.php$/i.exec(path);
        return match ? match[1].toLowerCase() : '';
    }

    function fileOf(path) {
        return path.substring(path.lastIndexOf('/') + 1).toLowerCase();
    }

    // ------------------------------------------------------------------
    // Pagina inicial nativa: segue para a "Visao geral" (o servidor ja pede
    // isso numa tag <meta>; aqui e a garantia caso o navegador ignore).
    // ------------------------------------------------------------------
    function redirectHome() {
        if (!data.home || !/\/(front\/central\.php|Central)$/.test(location.pathname)) {
            return false;
        }
        var query = new URLSearchParams(location.search);
        if (query.has('embed') || query.has('redirect') || query.has('ac_native')) {
            return false;
        }
        location.replace(data.home);
        return true;
    }

    // ------------------------------------------------------------------
    // Barra lateral
    // ------------------------------------------------------------------
    function sidebar() {
        var list = document.querySelector('.sidebar #navbar-menu .navbar-nav');
        if (!list) {
            return;
        }
        var links = [];
        Array.prototype.slice.call(list.children).forEach(function (item) {
            // Sobra vazia do template do GLPI e menus originais (escondidos).
            if (!item.children.length || item.querySelector(':scope > .dropdown-toggle')) {
                item.classList.add('ac-native');
                item.setAttribute('aria-hidden', 'true');
                return;
            }
            var link = item.querySelector(':scope > a.nav-link');
            if (!link) {
                return;
            }
            if (link.querySelector('i.' + (data.section || 'ac-section'))) {
                // Titulo de secao: deixa de ser link (teclado e leitor de tela).
                var label = el('div', 'ac-section-label', link.textContent.trim());
                item.classList.remove('dropdown', 'active');
                item.classList.add('ac-nav-section');
                item.replaceChild(label, link);
                return;
            }
            item.classList.add('ac-nav-item');
            link.title = link.textContent.trim();
            links.push({ item: item, link: link });
        });

        badges(links);
        markActive(links);
    }

    function badges(links) {
        var total = data.badges && Number(data.badges.helpdesk);
        if (!total || total < 1) {
            return;
        }
        links.forEach(function (entry) {
            if (entry.link.getAttribute('href').indexOf('hub.php?s=helpdesk') === -1) {
                return;
            }
            var badge = el('span', 'ac-badge', total > 99 ? '99+' : String(total));
            badge.title = total + (total === 1 ? ' chamado novo' : ' chamados novos');
            entry.link.appendChild(badge);
        });
    }

    /* O GLPI marca o item ativo pelo titulo da secao. Isso erra quando a
     * pagina pertence a um item que mora dentro de outra secao (ex.: Ativa
     * Guardian em "Administracao", Base de conhecimento em "Ferramentas"). */
    function markActive(links) {
        var herePlugin = pluginOf(location.pathname);
        var hereFront = frontBase(location.pathname);
        var hereQuery = new URLSearchParams(location.search);
        var best = null;
        var bestScore = 0;

        links.forEach(function (entry) {
            var url;
            try {
                url = new URL(entry.link.href, location.href);
            } catch (error) {
                return;
            }
            var score = 0;
            var plugin = pluginOf(url.pathname);
            if (herePlugin && plugin === herePlugin) {
                if (herePlugin !== 'ativacentral') {
                    score = 80;
                } else if (fileOf(url.pathname) === fileOf(location.pathname)
                    && (url.searchParams.get('s') || '') === (hereQuery.get('s') || '')) {
                    score = 100;
                }
            } else if (!herePlugin && !plugin && hereFront && frontBase(url.pathname) === hereFront) {
                score = 90;
            }
            if (score > bestScore) {
                best = entry;
                bestScore = score;
            }
        });

        if (!best) {
            return;
        }
        links.forEach(function (entry) {
            var on = entry === best;
            entry.item.classList.toggle('active', on);
            entry.link.classList.toggle('active', on);
            entry.item.classList.toggle('ac-inactive', !on);
        });
    }

    // ------------------------------------------------------------------
    // Topo
    // ------------------------------------------------------------------
    function header() {
        var bar = document.querySelector('body.vertical-layout > .page > header.navbar');
        if (!bar) {
            return;
        }

        var first = bar.querySelector('.breadcrumb .breadcrumb-item a');
        if (first && data.home) {
            first.href = data.home;
            first.textContent = 'Ativa Central';
            first.title = 'Visão geral';
        }

        var search = bar.querySelector('#global-search');
        if (search) {
            search.placeholder = 'Buscar chamado, ativo, usuário…';
        }

        var userMenu = bar.querySelector('.user-menu');
        if (userMenu && !bar.querySelector('.ac-top-actions')) {
            var actions = el('div', 'ac-top-actions');

            var helpItem = userMenu.querySelector('.dropdown-menu a.dropdown-item .ti-help');
            var helpLink = helpItem ? helpItem.closest('a') : null;
            if (helpLink && helpLink.getAttribute('href')) {
                var help = el('a', 'ac-top-btn');
                help.href = helpLink.href;
                help.target = '_blank';
                help.rel = 'noopener';
                help.title = 'Ajuda';
                help.setAttribute('aria-label', 'Ajuda');
                help.appendChild(icon('ti-help'));
                actions.appendChild(help);
            }

            if (data.newUrl) {
                var total = data.badges ? Number(data.badges.helpdesk) || 0 : 0;
                var bell = el('a', 'ac-top-btn');
                bell.href = data.newUrl;
                bell.title = total > 0
                    ? total + (total === 1 ? ' chamado novo aguardando atendimento' : ' chamados novos aguardando atendimento')
                    : 'Nenhum chamado novo aguardando atendimento';
                bell.setAttribute('aria-label', bell.title);
                bell.appendChild(icon('ti-bell'));
                if (total > 0) {
                    bell.appendChild(el('span', 'ac-dot'));
                }
                actions.appendChild(bell);
            }

            var holder = userMenu.closest('.btn-group');
            holder = holder && holder.parentNode ? holder.parentNode : null;
            if (holder && holder.parentNode && actions.children.length) {
                holder.parentNode.insertBefore(actions, holder);
            }
        }
    }

    /* Atalhos no menu do usuario (no topo e na versao para telas pequenas). */
    function userMenus() {
        Array.prototype.forEach.call(document.querySelectorAll('.user-menu .dropdown-menu'), function (menu) {
            if (menu.querySelector('.ac-user-item')) {
                return;
            }
            var entries = [];
            if (data.config) {
                entries.push({ href: data.config, icon: 'ti-layout-dashboard', text: 'Opções do Ativa Central' });
            }
            if (data.classic) {
                entries.push({ href: data.classic, icon: 'ti-arrow-back-up', text: 'Usar o visual clássico do GLPI' });
            }
            if (!entries.length) {
                return;
            }
            menu.appendChild(el('div', 'dropdown-divider'));
            entries.forEach(function (entry) {
                var link = el('a', 'dropdown-item ac-user-item');
                link.href = entry.href;
                link.appendChild(icon(entry.icon));
                link.appendChild(document.createTextNode(' ' + entry.text));
                menu.appendChild(link);
            });
        });
    }

    function start() {
        if (redirectHome()) {
            return;
        }
        [sidebar, header, userMenus].forEach(function (step) {
            try {
                step();
            } catch (error) {
                if (window.console) { console.warn('Ativa Central:', error); }
            }
        });
        document.documentElement.classList.add('ac-ready');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
