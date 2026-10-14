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

    // ------------------------------------------------------------------
    // Chamado: botao "Finalizar chamado"
    //
    // No GLPI, encerrar um chamado fica escondido em "Responder > Adicionar
    // uma solucao". O botao abre uma janela simples: o tecnico escreve o que
    // foi feito e escolhe entre solucionar (o requerente confirma) ou fechar
    // de vez. Quem grava e o proprio GLPI (front/ticket.finish.php).
    // ------------------------------------------------------------------
    function ticketId() {
        if (!/\/front\/ticket\.form\.php$/i.test(location.pathname)) {
            return 0;
        }
        var id = Number(new URLSearchParams(location.search).get('id'));
        return id > 0 ? id : 0;
    }

    function csrfToken() {
        var tag = document.querySelector('meta[property="glpi:csrf_token"]');
        return tag ? tag.getAttribute('content') || '' : '';
    }

    /* A ficha do chamado chega depois da pagina (o GLPI carrega o conteudo da
     * aba numa segunda requisicao) e e refeita a cada troca de aba. Por isso o
     * botao e colocado quando o rodape aparece, e recolocado se ele for
     * redesenhado. */
    var finishState = null;
    var finishPending = false;

    function finishTicket() {
        if (!ticketId() || !data.finish) {
            return;
        }
        placeFinishButton();
        if (!window.MutationObserver) {
            return;
        }
        var scheduled = false;
        new MutationObserver(function () {
            if (scheduled) {
                return;
            }
            scheduled = true;
            window.setTimeout(function () {
                scheduled = false;
                placeFinishButton();
            }, 150);
        }).observe(document.body, { childList: true, subtree: true });
    }

    function placeFinishButton() {
        var id = ticketId();
        var footer = document.querySelector('#itil-footer');
        if (!id || !footer || footer.querySelector('.ac-finish')) {
            return;
        }
        if (finishState) {
            addFinishButton(id, footer, finishState);
            return;
        }
        if (finishPending) {
            return;
        }
        finishPending = true;
        fetch(data.finish + '?id=' + id, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            if (!response.ok) { throw new Error('HTTP ' + response.status); }
            return response.json();
        }).then(function (state) {
            if (!state || !state.ok) {
                return;
            }
            finishState = state;
            // O rodape pode ter sido redesenhado enquanto a resposta vinha.
            var current = document.querySelector('#itil-footer');
            if (current) {
                addFinishButton(id, current, state);
            }
        }).catch(function (error) {
            // Sem o botao a tela segue como a do GLPI; o motivo fica no console.
            finishPending = false;
            if (window.console) { console.warn('Ativa Central: botao Finalizar indisponivel.', error); }
        });
    }

    function addFinishButton(id, footer, state) {
        if (footer.querySelector('.ac-finish') || state.closed || (!state.can_solve && !state.can_close)) {
            return;
        }
        // Ja solucionado: so falta fechar (se o perfil puder).
        if (state.solved && !state.can_close) {
            return;
        }
        var button = el('button', 'btn ac-finish');
        button.type = 'button';
        button.appendChild(icon('ti-circle-check'));
        button.appendChild(el('span', '', state.solved ? 'Fechar chamado' : 'Finalizar chamado'));
        button.addEventListener('click', function () { finishDialog(id, state); });

        var anchorNode = footer.querySelector('.timeline-buttons .main-actions');
        if (anchorNode && anchorNode.parentNode) {
            anchorNode.parentNode.insertBefore(button, anchorNode.nextSibling);
        } else {
            (footer.querySelector('.buttons-bar') || footer).appendChild(button);
        }
    }

    function finishDialog(id, state) {
        var old = document.querySelector('.ac-modal');
        if (old) { old.remove(); }

        var overlay = el('div', 'ac-modal');
        var box = el('div', 'ac-modal-box');
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');
        box.setAttribute('aria-label', 'Finalizar chamado');

        var head = el('div', 'ac-modal-head');
        var badge = el('span', 'ac-modal-icon');
        badge.appendChild(icon('ti-circle-check'));
        head.appendChild(badge);
        var titles = el('div');
        titles.appendChild(el('div', 'ac-modal-title', state.solved ? 'Fechar o chamado #' + id : 'Finalizar o chamado #' + id));
        titles.appendChild(el('div', 'ac-modal-sub', state.solved
            ? 'O chamado já tem solução registrada. Fechar encerra o atendimento de vez.'
            : 'Descreva o que foi feito. O texto fica no histórico como a solução do chamado.'));
        head.appendChild(titles);
        box.appendChild(head);

        var text = null;
        if (!state.solved) {
            text = el('textarea', 'ac-modal-text');
            text.rows = 5;
            text.maxLength = 20000;
            text.placeholder = 'Ex.: Reinstalado o OneDrive e refeito o login com a conta do usuário.';
            text.setAttribute('aria-label', 'Solução do chamado');
            box.appendChild(text);
        }

        var error = el('div', 'ac-modal-error');
        box.appendChild(error);

        var actions = el('div', 'ac-modal-actions');
        var cancel = el('button', 'btn btn-ghost-secondary', 'Cancelar');
        cancel.type = 'button';
        actions.appendChild(cancel);
        var buttons = [cancel];

        function unlock(button) {
            buttons.forEach(function (b) { b.disabled = false; });
            button.classList.remove('is-loading');
        }

        function send(mode, button) {
            var content = text ? text.value.trim() : '';
            if (text && content.length < 3) {
                error.textContent = 'Descreva o que foi feito para resolver o chamado.';
                text.focus();
                return;
            }
            error.textContent = '';
            buttons.forEach(function (b) { b.disabled = true; });
            button.classList.add('is-loading');
            var body = new FormData();
            body.append('id', String(id));
            body.append('mode', mode);
            body.append('content', content);
            fetch(data.finish, {
                method: 'POST', body: body, credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrfToken() }
            }).then(function (response) {
                return response.json().catch(function () { return { ok: false, message: 'Erro ' + response.status + ' no GLPI.' }; });
            }).then(function (result) {
                if (result.ok || result.reload) {
                    // Recarrega: o GLPI mostra o chamado no estado novo (e as
                    // mensagens dele, se recusou algum campo).
                    location.reload();
                    return;
                }
                error.textContent = result.message || 'Não foi possível finalizar o chamado.';
                unlock(button);
            }).catch(function () {
                error.textContent = 'Falha de comunicação com o GLPI.';
                unlock(button);
            });
        }

        if (!state.solved && state.can_solve) {
            var solve = el('button', 'btn ac-finish', 'Solucionar');
            solve.type = 'button';
            solve.title = 'Marca como solucionado. O requerente pode aprovar ou reabrir.';
            solve.addEventListener('click', function () { send('solve', solve); });
            actions.appendChild(solve);
            buttons.push(solve);
        }
        if (state.can_close && (state.solved || state.can_solve)) {
            var close = el('button', state.solved ? 'btn ac-finish' : 'btn btn-outline-secondary', state.solved ? 'Fechar chamado' : 'Solucionar e fechar');
            close.type = 'button';
            close.title = 'Encerra o chamado de vez, sem esperar a aprovação do requerente.';
            close.addEventListener('click', function () { send('close', close); });
            actions.appendChild(close);
            buttons.push(close);
        }
        box.appendChild(actions);
        overlay.appendChild(box);

        function onKey(event) {
            if (event.key === 'Escape') { dismiss(); }
        }
        function dismiss() {
            overlay.remove();
            document.removeEventListener('keydown', onKey);
        }
        cancel.addEventListener('click', dismiss);
        overlay.addEventListener('mousedown', function (event) { if (event.target === overlay) { dismiss(); } });
        document.addEventListener('keydown', onKey);

        document.body.appendChild(overlay);
        (text || cancel).focus();
    }

    // ------------------------------------------------------------------
    // Lista de chamados: coluna "Entidade" vira "Filial"
    //
    // "Grupo Ativa > Ativa Locacao - Matriz" vira "Matriz". Chamado da raiz
    // usa a localizacao; sem nenhuma das duas, um traco. Quem decide e o
    // servidor (front/branch.php). A lista e redesenhada pelo GLPI ao ordenar
    // ou paginar, por isso a troca e refeita quando a tabela muda.
    // ------------------------------------------------------------------
    var branchCache = {};
    var branchLoading = false;

    function branchColumn() {
        if (!data.branch || !/\/front\/ticket\.php$/i.test(location.pathname)) {
            return;
        }
        applyBranches();
        if (!window.MutationObserver) {
            return;
        }
        var scheduled = false;
        new MutationObserver(function () {
            if (scheduled) {
                return;
            }
            scheduled = true;
            window.setTimeout(function () {
                scheduled = false;
                applyBranches();
            }, 150);
        }).observe(document.body, { childList: true, subtree: true });
    }

    function entityColumn(table) {
        var cells = table.querySelectorAll('thead tr:last-child > th');
        for (var i = 0; i < cells.length; i++) {
            var text = cells[i].textContent.trim().toLowerCase();
            if (text === 'entidade' || text === 'filial' || text === 'entity') {
                return { index: i, cell: cells[i] };
            }
        }
        return null;
    }

    function renameHeader(cell) {
        if (cell.getAttribute('data-ac-branch') === '1') {
            return;
        }
        var walker = document.createTreeWalker(cell, NodeFilter.SHOW_TEXT);
        var node;
        while ((node = walker.nextNode())) {
            if (/entidade|entity/i.test(node.nodeValue)) {
                node.nodeValue = node.nodeValue.replace(/entidade|entity/i, 'Filial');
            }
        }
        cell.setAttribute('data-ac-branch', '1');
    }

    function rowTicketId(row) {
        var link = row.querySelector('a[href*="ticket.form.php?id="]');
        var match = link ? /[?&]id=(\d+)/.exec(link.getAttribute('href')) : null;
        return match ? match[1] : '';
    }

    function applyBranches() {
        var table = document.querySelector('table.search-results');
        if (!table) {
            return;
        }
        var column = entityColumn(table);
        if (!column) {
            return;
        }
        renameHeader(column.cell);

        var missing = [];
        var rows = table.querySelectorAll('tbody > tr');
        Array.prototype.forEach.call(rows, function (row) {
            var cell = row.children[column.index];
            var id = rowTicketId(row);
            if (!cell || !id || cell.querySelector('.ac-branch')) {
                return;
            }
            if (Object.prototype.hasOwnProperty.call(branchCache, id)) {
                fillBranch(cell, branchCache[id]);
            } else {
                missing.push(id);
            }
        });
        if (!missing.length || branchLoading) {
            return;
        }
        branchLoading = true;
        fetch(data.branch + '?ids=' + missing.join(','), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            if (!response.ok) { throw new Error('HTTP ' + response.status); }
            return response.json();
        }).then(function (result) {
            var found = (result && result.branches) || {};
            missing.forEach(function (id) {
                // Chamado que nao voltou (sem acesso): fica como o GLPI mostrou.
                if (Object.prototype.hasOwnProperty.call(found, id)) {
                    branchCache[id] = found[id];
                }
            });
            branchLoading = false;
            applyBranches();
        }).catch(function (error) {
            branchLoading = false;
            if (window.console) { console.warn('Ativa Central: coluna Filial indisponivel.', error); }
        });
    }

    function fillBranch(cell, name) {
        cell.innerHTML = '';
        var chip = el('span', 'ac-branch' + (name ? '' : ' is-empty'), name || '—');
        chip.title = name ? 'Filial: ' + name : 'Sem filial na entidade nem na localização';
        cell.appendChild(chip);
    }

    function start() {
        if (redirectHome()) {
            return;
        }
        [sidebar, header, userMenus, finishTicket, branchColumn].forEach(function (step) {
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
