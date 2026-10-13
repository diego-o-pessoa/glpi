/*
 * Ativa Central - telas proprias: "Visao geral" (painel) e telas de modulo.
 *
 * O servidor envia os dados em data-ac (JSON) e este arquivo desenha a tela.
 * Sem bibliotecas: o grafico e SVG puro.
 */
(function () {
    'use strict';

    var root = document.getElementById('ac-app');
    if (!root) {
        return;
    }
    var view = root.getAttribute('data-ac-view');
    var data;
    try {
        data = JSON.parse(root.getAttribute('data-ac') || '{}');
    } catch (error) {
        root.textContent = 'Não foi possível ler os dados desta tela. Recarregue a página.';
        return;
    }
    root.removeAttribute('data-ac');

    var SVG = 'http://www.w3.org/2000/svg';
    var TONES = ['is-blue', 'is-green', 'is-orange', 'is-violet', 'is-teal', 'is-red'];
    var numberFormat = new Intl.NumberFormat('pt-BR');
    var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ------------------------------------------------------------------
    // Utilitarios
    // ------------------------------------------------------------------
    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) { node.className = className; }
        if (text !== undefined && text !== null) { node.textContent = text; }
        return node;
    }

    function icon(name) {
        // Do GLPI vem a classe completa ("ti ti-users"); daqui, so o nome.
        return el('i', name.indexOf(' ') === -1 ? 'ti ' + name : name);
    }

    function anchor(href, className, text) {
        var node = el('a', className, text);
        node.href = href || '#';
        return node;
    }

    function svg(tag, attrs) {
        var node = document.createElementNS(SVG, tag);
        Object.keys(attrs || {}).forEach(function (key) { node.setAttribute(key, attrs[key]); });
        return node;
    }

    function num(value) {
        return numberFormat.format(Number(value) || 0);
    }

    function percent(value) {
        return Math.abs(Number(value)).toFixed(1).replace('.', ',').replace(',0', '') + '%';
    }

    function rise(node, index) {
        node.classList.add('ac-rise');
        node.style.setProperty('--ac-i', String(index));
        return node;
    }

    function initials(name) {
        var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) { return '?'; }
        var first = parts[0].charAt(0);
        var last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : '';
        return (first + last).toUpperCase();
    }

    /* Numero que sobe ate o valor (so na primeira vez que a tela aparece). */
    function countUp(node, value, quiet) {
        value = Number(value) || 0;
        if (quiet || calm || value === 0) {
            node.textContent = num(value);
            return;
        }
        var started = null;
        var duration = 900;
        function frame(now) {
            if (started === null) { started = now; }
            var progress = Math.min(1, (now - started) / duration);
            var eased = 1 - Math.pow(1 - progress, 3);
            node.textContent = num(Math.round(value * eased));
            if (progress < 1) { requestAnimationFrame(frame); }
        }
        node.textContent = '0';
        requestAnimationFrame(frame);
    }

    /* Titulo como <div role="heading">, nao <h1>-<h3>: no GLPI ha regras de
     * estilo (tema, CSS personalizado da entidade) que mexem nessas tags e
     * chegaram a esconder os nomes dos cartoes. */
    function heading(level, className, text) {
        var node = el('div', className, text);
        node.setAttribute('role', 'heading');
        node.setAttribute('aria-level', String(level));
        return node;
    }

    function panel(className) {
        return el('div', 'ac-panel' + (className ? ' ' + className : ''));
    }

    function panelHead(title, subtitle, extra) {
        var head = el('div', 'ac-panel-head');
        var text = el('div');
        text.appendChild(heading(2, 'ac-panel-title', title));
        if (subtitle) { text.appendChild(el('div', 'ac-panel-sub', subtitle)); }
        head.appendChild(text);
        if (extra) { head.appendChild(extra); }
        return head;
    }

    function smallButton(label, href, iconName) {
        var button = anchor(href, 'ac-btn is-small', label);
        if (iconName) { button.appendChild(icon(iconName)); }
        return button;
    }

    function empty(text, iconName) {
        var box = el('div', 'ac-empty');
        if (iconName) { box.appendChild(icon(iconName)); }
        box.appendChild(document.createTextNode(text));
        return box;
    }

    function pageHead(eyebrow, titleNode, subtitle, actions) {
        var head = el('div', 'ac-head');
        var text = rise(el('div'), 0);
        text.appendChild(el('div', 'ac-eyebrow', eyebrow));
        text.appendChild(titleNode);
        if (subtitle) { text.appendChild(el('div', 'ac-subtitle', subtitle)); }
        head.appendChild(text);
        if (actions) { head.appendChild(rise(actions, 1)); }
        return head;
    }

    // ------------------------------------------------------------------
    // Visao geral
    // ------------------------------------------------------------------
    var homeBody = null;
    var refreshButton = null;
    var chartObserver = null;

    function greeting() {
        var hour = new Date().getHours();
        return hour < 12 ? 'Bom dia' : (hour < 18 ? 'Boa tarde' : 'Boa noite');
    }

    function home() {
        var urls = data.urls || {};
        var name = (data.user && data.user.name) || '';

        var title = heading(1, 'ac-title', greeting() + (name ? ', ' + name : ''));
        title.appendChild(el('span', 'ac-spark', '✦'));

        var actions = el('div', 'ac-actions');
        var select = el('select', 'ac-select');
        select.setAttribute('aria-label', 'Período do painel');
        (data.periods || [7, 30, 90]).forEach(function (days) {
            var option = el('option', '', 'Últimos ' + days + ' dias');
            option.value = String(days);
            option.selected = Number(data.days) === Number(days);
            select.appendChild(option);
        });
        select.addEventListener('change', function () { refresh(Number(select.value), false); });
        actions.appendChild(select);

        refreshButton = el('button', 'ac-btn');
        refreshButton.type = 'button';
        refreshButton.appendChild(icon('ti-refresh'));
        refreshButton.appendChild(document.createTextNode('Atualizar'));
        refreshButton.addEventListener('click', function () { refresh(Number(select.value), false); });
        actions.appendChild(refreshButton);

        if (data.can && data.can.create && urls['new']) {
            var create = anchor(urls['new'], 'ac-btn is-primary');
            create.appendChild(icon('ti-plus'));
            create.appendChild(document.createTextNode('Novo chamado'));
            actions.appendChild(create);
        }

        root.innerHTML = '';
        root.appendChild(pageHead('Painel operacional · TI', title,
            'Acompanhe a operação, seus chamados e a saúde do ambiente em um só lugar.', actions));
        homeBody = el('div');
        root.appendChild(homeBody);
        drawHome(false);

        // Atualiza sozinho a cada 2 minutos, sem refazer as animacoes.
        window.setInterval(function () {
            if (document.visibilityState === 'visible') { refresh(Number(select.value), true); }
        }, 120000);
    }

    function refresh(days, quiet) {
        var urls = data.urls || {};
        if (!urls.data) {
            return;
        }
        if (refreshButton && !quiet) { refreshButton.classList.add('is-loading'); }
        fetch(urls.data + '?days=' + encodeURIComponent(days), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            return response.json();
        }).then(function (fresh) {
            if (!fresh || !fresh.ok) {
                throw new Error((fresh && fresh.message) || 'resposta inválida');
            }
            fresh.urls = urls;
            data = fresh;
            drawHome(quiet);
        }).catch(function (error) {
            if (!quiet && window.glpi_toast_error) {
                window.glpi_toast_error('Não foi possível atualizar o painel: ' + error.message);
            }
        }).then(function () {
            if (refreshButton) { refreshButton.classList.remove('is-loading'); }
        });
    }

    function drawHome(quiet) {
        root.classList.toggle('is-quiet', !!quiet);
        homeBody.innerHTML = '';
        homeBody.appendChild(kpiRow(quiet));

        var main = el('div', 'ac-grid is-main');
        main.appendChild(rise(chartPanel(quiet), 6));
        main.appendChild(rise(queuePanel(), 7));
        homeBody.appendChild(main);

        var lower = el('div', 'ac-grid is-three');
        lower.appendChild(rise(categoryPanel(), 8));
        lower.appendChild(rise(mePanel(quiet), 9));
        lower.appendChild(rise(teamPanel(), 10));
        homeBody.appendChild(lower);

        var foot = el('div', 'ac-foot');
        foot.appendChild(el('span', '', 'Atualizado às ' + (data.updated || '--:--') + ' · atualiza sozinho a cada 2 minutos'));
        if (data.urls && data.urls['native']) {
            var native = anchor(data.urls['native'], '', 'Painéis nativos do GLPI ');
            native.appendChild(icon('ti-arrow-narrow-right'));
            foot.appendChild(native);
        }
        homeBody.appendChild(foot);
    }

    function trend(delta, suffix, goodWhenUp) {
        var note = el('div', 'ac-kpi-note');
        if (delta === null || delta === undefined) {
            note.textContent = 'sem base de comparação no período anterior';
            return note;
        }
        var up = delta >= 0;
        var cls = goodWhenUp === null ? 'ac-tone-text' : (up === goodWhenUp ? 'ac-up' : 'ac-down');
        note.appendChild(el('b', cls, (up ? '↑ ' : '↓ ') + percent(delta)));
        note.appendChild(document.createTextNode(suffix));
        return note;
    }

    function kpi(index, tone, label, iconName, value, note, progress, href, quiet, hint) {
        var card = rise(href ? anchor(href, 'ac-panel ac-kpi ' + tone) : el('div', 'ac-panel ac-kpi ' + tone), index + 2);
        if (hint) { card.title = hint; }
        var top = el('div', 'ac-kpi-top');
        top.appendChild(el('span', 'ac-kpi-label', label));
        var tile = el('span', 'ac-tile');
        tile.appendChild(icon(iconName));
        top.appendChild(tile);
        card.appendChild(top);

        var number = el('div', 'ac-kpi-value');
        if (value === null) {
            number.textContent = '—';
        } else {
            countUp(number, value, quiet);
        }
        card.appendChild(number);
        card.appendChild(note);

        var bar = el('div', 'ac-bar');
        var fill = el('span');
        fill.style.setProperty('--ac-w', Math.max(0, Math.min(100, Number(progress) || 0)) + '%');
        fill.style.setProperty('--ac-i', String(index + 2));
        bar.appendChild(fill);
        card.appendChild(bar);
        return card;
    }

    function kpiRow(quiet) {
        var k = data.kpis || {};
        var urls = data.urls || {};
        var tickets = !!(data.can && data.can.tickets);
        var row = el('div', 'ac-grid is-kpis');

        var open = k.open || {};
        row.appendChild(kpi(0, 'is-blue', 'Chamados em aberto', 'ti-clock', tickets ? open.value : null,
            trend(tickets ? open.delta : null, ' recebidos vs. período anterior', null), open.progress, tickets ? urls.open : '',
            quiet, tickets ? num(open.received) + ' chamados recebidos no período. Barra: parte da fila que já tem atendimento.' : 'Seu perfil não vê os chamados.'));

        var waiting = k.waiting || {};
        var waitingNote = el('div', 'ac-kpi-note');
        if (tickets && waiting.priority > 0) {
            waitingNote.appendChild(el('b', 'ac-warn', num(waiting.priority) + (waiting.priority === 1 ? ' prioritário' : ' prioritários')));
            waitingNote.appendChild(document.createTextNode('na fila'));
        } else {
            waitingNote.textContent = tickets ? 'nenhum prioritário na fila' : '';
        }
        row.appendChild(kpi(1, 'is-orange', 'Aguardando atendimento', 'ti-hourglass-high', tickets ? waiting.value : null,
            waitingNote, waiting.progress, tickets ? urls.waiting : '', quiet, 'Chamados novos, ainda sem atendimento. Barra: parte da fila em aberto.'));

        var resolved = k.resolved || {};
        row.appendChild(kpi(2, 'is-green', 'Resolvidos no período', 'ti-check', tickets ? resolved.value : null,
            trend(tickets ? resolved.delta : null, ' vs. período anterior', true), resolved.progress, tickets ? urls.solved : '',
            quiet, 'Chamados solucionados no período. Barra: resolvidos em relação aos recebidos.'));

        var computers = k.computers || {};
        var computersNote = el('div', 'ac-kpi-note');
        if (computers.visible && computers.fresh !== null && computers.fresh !== undefined) {
            computersNote.appendChild(el('b', computers.fresh >= 90 ? 'ac-up' : 'ac-warn', computers.fresh + '%'));
            computersNote.appendChild(document.createTextNode('inventário atualizado'));
        }
        row.appendChild(kpi(3, 'is-violet', 'Computadores ativos', 'ti-device-desktop', computers.visible ? computers.value : null,
            computersNote, computers.fresh, computers.visible ? urls.computers : '', quiet,
            'Computadores cadastrados. Inventário atualizado = recebido nos últimos ' + (computers.days || 30) + ' dias.'));

        return row;
    }

    // ---- grafico ------------------------------------------------------
    function niceTop(max) {
        var steps = [1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 5000, 10000];
        for (var i = 0; i < steps.length; i++) {
            if (steps[i] * 4 >= max) { return steps[i] * 4; }
        }
        return Math.ceil(max / 4) * 4;
    }

    function chartPanel(quiet) {
        var box = panel();
        var legend = el('div', 'ac-legend');
        legend.appendChild(el('span', 'is-blue', 'Recebidos'));
        legend.appendChild(el('span', 'is-green', 'Resolvidos'));
        box.appendChild(panelHead('Evolução dos chamados', 'Volume recebido e resolvido ao longo do período', legend));

        var body = el('div', 'ac-panel-body');
        var chart = el('div', 'ac-chart');
        body.appendChild(chart);
        box.appendChild(body);

        var series = data.series || { labels: [], received: [], resolved: [] };
        if (!series.labels.length) {
            chart.appendChild(empty('Sem dados de chamados para o período.'));
            return box;
        }

        // Desenha depois de entrar na pagina (precisa da largura real) e
        // refaz, sem animar, quando o tamanho muda.
        var width = 0;
        requestAnimationFrame(function () {
            width = chart.clientWidth;
            drawChart(chart, series, quiet);
        });
        if (chartObserver) { chartObserver.disconnect(); }
        if (window.ResizeObserver) {
            chartObserver = new ResizeObserver(function () {
                if (width && Math.abs(chart.clientWidth - width) > 4) {
                    width = chart.clientWidth;
                    drawChart(chart, series, true);
                }
            });
            chartObserver.observe(chart);
        }
        return box;
    }

    function drawChart(box, series, quiet) {
        box.innerHTML = '';
        var W = Math.max(280, box.clientWidth);
        var H = Math.max(180, box.clientHeight);
        var pad = { left: 36, right: 16, top: 16, bottom: 32 };
        var count = series.labels.length;
        var top = niceTop(Math.max(1, Math.max.apply(null, series.received.concat(series.resolved))));
        var innerW = W - pad.left - pad.right;
        var innerH = H - pad.top - pad.bottom;
        var base = pad.top + innerH;

        function x(i) { return pad.left + (count === 1 ? innerW / 2 : (i * innerW) / (count - 1)); }
        function y(v) { return pad.top + (1 - v / top) * innerH; }
        function path(values) {
            return values.map(function (v, i) { return (i ? 'L' : 'M') + x(i).toFixed(1) + ' ' + y(v).toFixed(1); }).join(' ');
        }

        var id = 'ac-grad-' + Math.random().toString(36).slice(2, 8);
        var chart = svg('svg', { viewBox: '0 0 ' + W + ' ' + H, role: 'img', 'aria-label': 'Chamados recebidos e resolvidos por período' });
        var defs = svg('defs');
        var gradient = svg('linearGradient', { id: id, x1: '0', y1: '0', x2: '0', y2: '1' });
        gradient.appendChild(svg('stop', { offset: '0%', 'stop-color': '#4a6cf7', 'stop-opacity': '0.22' }));
        gradient.appendChild(svg('stop', { offset: '100%', 'stop-color': '#4a6cf7', 'stop-opacity': '0' }));
        defs.appendChild(gradient);
        chart.appendChild(defs);

        for (var tick = 0; tick <= 4; tick++) {
            var value = (top / 4) * tick;
            chart.appendChild(svg('line', { x1: pad.left, x2: W - pad.right, y1: y(value), y2: y(value), 'class': 'ac-chart-grid' }));
            if (tick > 0) {
                var label = svg('text', { x: pad.left - 10, y: y(value) + 4, 'text-anchor': 'end', 'class': 'ac-chart-axis' });
                label.textContent = num(value);
                chart.appendChild(label);
            }
        }

        var every = Math.max(1, Math.ceil(count / 8));
        series.labels.forEach(function (text, i) {
            // Sempre o ultimo ponto; os demais, espacados para nao encavalar.
            var last = i === count - 1;
            if (!last && (i % every !== 0 || count - 1 - i < every)) {
                return;
            }
            var anchorAt = i === 0 ? 'start' : (last ? 'end' : 'middle');
            var tickLabel = svg('text', { x: x(i), y: H - 8, 'text-anchor': count === 1 ? 'middle' : anchorAt, 'class': 'ac-chart-axis' });
            tickLabel.textContent = text;
            chart.appendChild(tickLabel);
        });

        var area = svg('path', {
            d: path(series.received) + ' L' + x(count - 1).toFixed(1) + ' ' + base + ' L' + x(0).toFixed(1) + ' ' + base + ' Z',
            fill: 'url(#' + id + ')', 'class': 'ac-chart-area'
        });
        chart.appendChild(area);

        var guide = svg('line', { x1: 0, x2: 0, y1: pad.top, y2: base, 'class': 'ac-chart-guide' });
        chart.appendChild(guide);

        var lines = [
            { values: series.resolved, color: '#2fb67c' },
            { values: series.received, color: '#4a6cf7' }
        ].map(function (item) {
            var line = svg('path', { d: path(item.values), stroke: item.color, 'class': 'ac-chart-line' });
            chart.appendChild(line);
            return line;
        });

        if (count <= 16) {
            series.received.forEach(function (v, i) {
                var dot = svg('circle', { cx: x(i), cy: y(v), r: 4.5, fill: '#4a6cf7', 'class': 'ac-chart-dot' });
                dot.style.setProperty('--ac-i', String(i));
                chart.appendChild(dot);
            });
        }

        box.appendChild(chart);
        lines.forEach(function (line) {
            var length = 2000;
            try { length = Math.ceil(line.getTotalLength()) + 2; } catch (error) { /* SVG fora da pagina */ }
            line.style.setProperty('--ac-len', String(length));
            if (quiet || calm) { line.style.strokeDashoffset = '0'; line.style.animation = 'none'; }
        });
        if (quiet || calm) {
            area.style.opacity = '1';
            Array.prototype.forEach.call(chart.querySelectorAll('.ac-chart-dot'), function (dot) { dot.style.opacity = '1'; });
        }

        // Dica ao passar o mouse: o ponto mais proximo do cursor.
        var tip = el('div', 'ac-tip');
        box.appendChild(tip);
        function show(event) {
            var rect = box.getBoundingClientRect();
            var ratio = W / rect.width;
            var px = (event.clientX - rect.left) * ratio;
            var index = count === 1 ? 0 : Math.round(((px - pad.left) / innerW) * (count - 1));
            index = Math.max(0, Math.min(count - 1, index));
            guide.setAttribute('x1', x(index));
            guide.setAttribute('x2', x(index));
            tip.innerHTML = '';
            tip.appendChild(el('strong', '', series.labels[index]));
            [['Recebidos', series.received[index], 'is-blue'], ['Resolvidos', series.resolved[index], 'is-green']].forEach(function (rowData) {
                var line = el('div');
                line.appendChild(el('span', rowData[2], rowData[0]));
                line.appendChild(el('b', '', num(rowData[1])));
                tip.appendChild(line);
            });
            var edge = 76;
            var left = Math.max(edge, Math.min(rect.width - edge, x(index) / ratio));
            tip.style.left = left + 'px';
            tip.style.top = Math.max(18, Math.min(y(series.received[index]), y(series.resolved[index])) / ratio) + 'px';
            box.classList.add('is-hover');
        }
        box.onmousemove = show;
        box.onmouseleave = function () { box.classList.remove('is-hover'); };
    }

    // ---- fila, categorias e desempenho --------------------------------
    var PILL_TONES = { urgent: 'is-red', 'new': 'is-orange', progress: 'is-blue', pending: 'is-violet' };

    function queuePanel() {
        var urls = data.urls || {};
        var box = panel();
        box.appendChild(panelHead('Fila de chamados', 'Chamados que precisam de atenção', smallButton('Ver todos', urls.open, 'ti-arrow-up-right')));
        var body = el('div', 'ac-panel-body');
        var list = el('div', 'ac-list');
        (data.queue || []).forEach(function (ticket) {
            var row = anchor(ticket.url, 'ac-row');
            var main = el('div', 'ac-row-main');
            main.appendChild(el('div', 'ac-row-title', ticket.title));
            main.appendChild(el('div', 'ac-row-meta', [ticket.requester, ticket.category].filter(Boolean).join('  ·  ') || 'Sem requerente informado'));
            row.appendChild(main);
            var side = el('div', 'ac-row-side');
            side.appendChild(el('span', 'ac-pill ' + (PILL_TONES[ticket.badge.key] || 'is-blue'), ticket.badge.label));
            side.appendChild(el('div', 'ac-row-id', '#' + ticket.id));
            row.appendChild(side);
            row.title = ticket.title;
            list.appendChild(row);
        });
        if (!list.children.length) {
            list.appendChild(empty(data.can && data.can.tickets ? 'Fila zerada: nenhum chamado em aberto.' : 'Seu perfil não vê os chamados.', 'ti-circle-check'));
        }
        body.appendChild(list);
        box.appendChild(body);

        if (urls.helpdesk) {
            var foot = el('div', 'ac-panel-foot');
            foot.appendChild(anchor(urls.helpdesk, 'ac-btn', 'Abrir central de chamados'));
            box.appendChild(foot);
        }
        return box;
    }

    function categoryPanel() {
        var urls = data.urls || {};
        var box = panel();
        box.appendChild(panelHead('Chamados por categoria', 'Distribuição dos chamados em aberto', smallButton('Detalhes', urls.open)));
        var body = el('div', 'ac-panel-body');
        var categories = data.categories || [];
        var max = categories.reduce(function (m, c) { return Math.max(m, c.total); }, 1);
        categories.forEach(function (category, index) {
            var row = anchor(category.url, 'ac-cat ' + TONES[index % TONES.length]);
            var top = el('div', 'ac-cat-top');
            top.appendChild(el('span', 'ac-cat-name', category.name));
            top.appendChild(el('span', 'ac-cat-total', num(category.total)));
            row.appendChild(top);
            var bar = el('div', 'ac-bar');
            var fill = el('span');
            fill.style.setProperty('--ac-w', Math.round((100 * category.total) / max) + '%');
            fill.style.setProperty('--ac-i', String(index + 8));
            bar.appendChild(fill);
            row.appendChild(bar);
            body.appendChild(row);
        });
        if (!categories.length) {
            body.appendChild(empty('Nenhum chamado em aberto.', 'ti-circle-check'));
        }
        box.appendChild(body);
        return box;
    }

    function mePanel(quiet) {
        var urls = data.urls || {};
        var me = data.me || {};
        var name = (data.user && data.user.name) || '';
        var badge = me.rank && me.rank <= 3 ? el('span', 'ac-pill is-blue', 'Top ' + me.rank + ' da equipe') : null;
        var box = panel();
        box.appendChild(panelHead('Meu desempenho', 'Resumo pessoal neste período', badge));
        var body = el('div', 'ac-panel-body');

        var hasData = me.pct !== null && me.pct !== undefined;
        var pct = hasData ? Number(me.pct) : 0;
        var wrap = el('div', 'ac-me');
        var ring = el('div', 'ac-ring');
        var radius = 42;
        var length = 2 * Math.PI * radius;
        var chart = svg('svg', { viewBox: '0 0 100 100', 'aria-hidden': 'true' });
        chart.appendChild(svg('circle', { cx: 50, cy: 50, r: radius, 'class': 'ac-ring-track' }));
        var arc = svg('circle', { cx: 50, cy: 50, r: radius, 'class': 'ac-ring-value', 'stroke-dasharray': length.toFixed(2), 'stroke-dashoffset': length.toFixed(2) });
        chart.appendChild(arc);
        ring.appendChild(chart);
        ring.appendChild(el('div', 'ac-ring-label', hasData ? pct + '%' : '—'));
        wrap.appendChild(ring);
        var target = (length * (1 - pct / 100)).toFixed(2);
        if (quiet || calm) {
            arc.style.transition = 'none';
            arc.setAttribute('stroke-dashoffset', target);
        } else {
            requestAnimationFrame(function () { requestAnimationFrame(function () { arc.setAttribute('stroke-dashoffset', target); }); });
        }

        var text = el('div', 'ac-me-text');
        var headline = !hasData ? 'Nada atribuído a você'
            : (pct >= 80 ? 'Ótimo ritmo' : (pct >= 50 ? 'Bom andamento' : 'Fila acumulando')) + (name ? ', ' + name : '') + (pct >= 80 ? '!' : '');
        text.appendChild(el('strong', '', headline));
        text.appendChild(el('span', '', hasData
            ? 'Do que esteve com você no período, ' + pct + '% já foi resolvido.'
            : 'Quando houver chamados atribuídos a você, o resumo aparece aqui.'));
        wrap.appendChild(text);
        body.appendChild(wrap);

        var stats = el('div', 'ac-stats');
        var solved = el('div', 'ac-stat');
        solved.appendChild(el('b', '', num(me.solved)));
        solved.appendChild(el('span', '', 'resolvidos por mim'));
        stats.appendChild(solved);
        var open = anchor(urls.mine, 'ac-stat');
        open.appendChild(el('b', '', num(me.open)));
        open.appendChild(el('span', '', 'em aberto comigo'));
        stats.appendChild(open);
        body.appendChild(stats);
        box.appendChild(body);
        return box;
    }

    function teamPanel() {
        var urls = data.urls || {};
        var box = panel();
        box.appendChild(panelHead('Desempenho da equipe', 'Chamados resolvidos no período', smallButton('Equipe', urls.stats, 'ti-arrow-up-right')));
        var body = el('div', 'ac-panel-body');
        var list = el('div', 'ac-list');
        (data.team || []).forEach(function (member, index) {
            var row = el('div', 'ac-row');
            var person = el('div', 'ac-person');
            person.appendChild(el('span', 'ac-avatar ' + TONES[index % TONES.length], initials(member.name)));
            var main = el('div', 'ac-row-main');
            main.appendChild(el('div', 'ac-row-title', member.name + (member.me ? ' (você)' : '')));
            main.appendChild(el('div', 'ac-row-meta', num(member.open) + ' em aberto'));
            person.appendChild(main);
            row.appendChild(person);
            row.appendChild(el('div', 'ac-score', num(member.solved)));
            list.appendChild(row);
        });
        if (!list.children.length) {
            list.appendChild(empty('Nenhum chamado resolvido no período.'));
        }
        body.appendChild(list);
        box.appendChild(body);
        return box;
    }

    // ------------------------------------------------------------------
    // Tela de modulo
    // ------------------------------------------------------------------
    function hub() {
        var groups = data.groups || [];
        var total = groups.reduce(function (sum, group) { return sum + group.cards.length; }, 0);

        var actions = el('div', 'ac-actions');
        var filter = null;
        if (total > 8) {
            var wrap = el('label', 'ac-filter');
            wrap.appendChild(icon('ti-search'));
            filter = el('input');
            filter.type = 'search';
            filter.placeholder = 'Filtrar nesta tela…';
            filter.setAttribute('aria-label', 'Filtrar nesta tela');
            wrap.appendChild(filter);
            actions.appendChild(wrap);
        }
        (data.actions || []).forEach(function (action) {
            var button = anchor(action.url, 'ac-btn' + (action.primary ? ' is-primary' : ''));
            if (action.icon) { button.appendChild(icon(action.icon)); }
            button.appendChild(document.createTextNode(action.label));
            actions.appendChild(button);
        });

        root.innerHTML = '';
        root.appendChild(pageHead(data.eyebrow || 'Ativa Central · Módulo', heading(1, 'ac-title', data.title || ''),
            data.subtitle || '', actions.children.length ? actions : null));

        var index = 2;
        var cards = [];
        var GROUP_TONES = ['is-blue', 'is-violet', 'is-teal', 'is-orange', 'is-green'];
        groups.forEach(function (group, groupIndex) {
            var section = el('section', 'ac-group');
            if (group.title) {
                section.appendChild(rise(heading(2, 'ac-group-title', group.title), index));
            }
            var grid = el('div', 'ac-cards');
            group.cards.forEach(function (card) {
                var node = hubCard(card, GROUP_TONES[groupIndex % GROUP_TONES.length]);
                grid.appendChild(rise(node, Math.min(index++, 18)));
                cards.push({ node: node, section: section, text: (card.title + ' ' + (card.desc || '')).toLowerCase() });
            });
            section.appendChild(grid);
            root.appendChild(section);
        });

        var nomatch = el('div', 'ac-nomatch', 'Nada encontrado nesta tela.');
        root.appendChild(nomatch);
        if (filter) {
            filter.addEventListener('input', function () {
                var term = filter.value.trim().toLowerCase();
                var shown = 0;
                cards.forEach(function (entry) {
                    var match = term === '' || entry.text.indexOf(term) !== -1;
                    entry.node.classList.toggle('is-hidden', !match);
                    if (match) { shown++; }
                });
                Array.prototype.forEach.call(root.querySelectorAll('.ac-group'), function (section) {
                    section.classList.toggle('is-hidden', !section.querySelector('.ac-card:not(.is-hidden)'));
                });
                nomatch.classList.toggle('is-shown', shown === 0);
            });
        }
    }

    function hubCard(card, tone) {
        var node = panel('ac-card ' + tone);
        var cover = anchor(card.url, 'ac-card-link');
        cover.setAttribute('aria-label', card.title);
        node.appendChild(cover);

        var top = el('div', 'ac-card-top');
        var tile = el('span', 'ac-tile');
        tile.appendChild(icon(card.icon || 'ti-circle-dot'));
        top.appendChild(tile);
        if (card.count !== null && card.count !== undefined) {
            var count = el('div', 'ac-count');
            var number = el('b');
            countUp(number, card.count, false);
            count.appendChild(number);
            count.appendChild(el('span', '', card.unit || 'itens'));
            top.appendChild(count);
        }
        node.appendChild(top);

        node.appendChild(heading(3, 'ac-card-title', card.title));
        node.appendChild(el('div', 'ac-card-desc', card.desc || ''));

        var foot = el('div', 'ac-card-foot');
        var cta = el('span', 'ac-cta', card.cta || 'Abrir');
        cta.appendChild(icon('ti-arrow-narrow-right'));
        foot.appendChild(cta);
        if (card.add) {
            var add = anchor(card.add, 'ac-card-add');
            add.appendChild(icon('ti-plus'));
            add.appendChild(document.createTextNode('Novo'));
            add.title = 'Adicionar em ' + card.title;
            foot.appendChild(add);
        }
        node.appendChild(foot);
        return node;
    }

    try {
        if (view === 'home') {
            home();
        } else if (view === 'hub') {
            hub();
        }
    } catch (error) {
        root.textContent = 'Não foi possível montar esta tela. Recarregue a página.';
        if (window.console) { console.error('Ativa Central:', error); }
    }
})();
