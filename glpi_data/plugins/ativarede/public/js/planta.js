/* Ativa Rede - planta das mesas.
 * Desenha as mesas sobre a planta (coordenadas em unidades da planta), mostra
 * os detalhes da mesa escolhida e, para quem pode editar, permite arrastar,
 * criar, renomear e definir a porta de cada mesa. Sem bibliotecas externas.
 */
(function () {
    'use strict';

    var app = document.getElementById('ar-plan-app');
    if (!app) { return; }

    var dataUrl = app.getAttribute('data-url');
    var plansUrl = app.getAttribute('data-plans-url');
    var selfUrl = app.getAttribute('data-self-url');
    var alertsUrl = app.getAttribute('data-alerts-url');
    var canManage = app.getAttribute('data-can-manage') === '1';
    var csrf = (document.querySelector('meta[property="glpi:csrf_token"]') || {}).content || '';

    var q = function (sel) { return app.querySelector(sel); };
    var canvas = q('[data-ar-canvas]');
    var bg = q('[data-ar-bg]');
    var desksLayer = q('[data-ar-desks]');
    var panel = q('[data-ar-panel]');
    var side = q('[data-ar-side]');
    var legend = q('[data-ar-legend]');
    var toast = q('[data-ar-toast]');
    var editBtn = q('[data-ar-edit]');
    var editBar = q('[data-ar-edit-bar]');

    var STATES = {
        online:   { label: 'Ligadas',       hint: 'máquina ligada na porta da mesa' },
        offline:  { label: 'Desligadas',    hint: 'máquina conhecida, sem contato agora' },
        alert:    { label: 'Com alerta',    hint: 'mudança ou monitor pendente' },
        empty:    { label: 'Vazias',        hint: 'porta definida, nenhuma máquina' },
        unmapped: { label: 'Sem porta',     hint: 'mesa ainda sem porta do switch' }
    };
    var CHAIRS = { up: 'Em cima', down: 'Embaixo', left: 'À esquerda', right: 'À direita' };

    var state = null;
    var selectedId = 0;
    var editing = false;
    var filter = '';
    var zoom = 1;
    var busy = false;
    var toastTimer = null;

    try {
        state = JSON.parse(document.getElementById('ar-initial').textContent || '{}');
    } catch (e) {
        state = { plan: null };
    }

    // ------------------------------------------------------------------
    // Utilitarios
    // ------------------------------------------------------------------
    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text !== undefined && text !== null && text !== '') { n.textContent = text; }
        return n;
    }

    function icon(name) { return el('i', 'ti ti-' + name); }

    function link(href, text) {
        if (!href) { return el('span', '', text); }
        var a = el('a', '', text);
        a.href = href;
        return a;
    }

    function showToast(message, isError) {
        if (!toast || !message) { return; }
        toast.textContent = message;
        toast.classList.toggle('is-error', !!isError);
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.hidden = true; }, isError ? 6000 : 3500);
    }

    function post(fields) {
        var body = new FormData();
        body.append('plan', String(state.plan ? state.plan.id : 0));
        Object.keys(fields).forEach(function (k) { body.append(k, fields[k] === null || fields[k] === undefined ? '' : String(fields[k])); });
        busy = true;
        return fetch(dataUrl, {
            method: 'POST', body: body, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrf }
        }).then(function (resp) {
            return resp.json().catch(function () { return { ok: false, message: 'Erro ' + resp.status + ' no GLPI.' }; });
        }).catch(function () {
            return { ok: false, message: 'Falha de comunicação com o GLPI.' };
        }).then(function (result) {
            busy = false;
            if (result.state && result.state.plan) { state = result.state; }
            showToast(result.message, !result.ok);
            if (result.ticket_url) { window.open(result.ticket_url, '_blank', 'noopener'); }
            return result;
        });
    }

    function refresh() {
        if (busy || editing || !state.plan || document.hidden) { return; }
        fetch(dataUrl + '?plan=' + encodeURIComponent(state.plan.id), { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { if (data && data.ok && !editing && !busy) { state = data; render(); } })
            .catch(function () { /* tenta de novo no proximo ciclo */ });
    }

    /* "Atualizar agora": recarrega ja e, para quem edita, pede as maquinas
     * uma coleta imediata. Depois acompanha a cada 10 s por 4 minutos (tempo
     * da coleta + confirmacao de troca de mesa), com contagem no botao. */
    var WATCH_MS = 4 * 60 * 1000;
    var watchUntil = 0;
    var watchTimer = null;
    var refreshBtn = q('[data-ar-refresh]');

    function watchTick() {
        var left = watchUntil - Date.now();
        var label = refreshBtn ? refreshBtn.querySelector('span') : null;
        if (left <= 0) {
            clearInterval(watchTimer);
            watchTimer = null;
            if (label) { label.textContent = 'Atualizar agora'; }
            if (refreshBtn) { refreshBtn.disabled = false; }
            refresh();
            return;
        }
        var sec = Math.ceil(left / 1000);
        if (label) { label.textContent = 'Acompanhando… ' + Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0'); }
        if (Date.now() - lastWatchRefresh >= 10000) {
            lastWatchRefresh = Date.now();
            refresh();
        }
    }
    var lastWatchRefresh = 0;

    function startWatch() {
        watchUntil = Date.now() + WATCH_MS;
        if (refreshBtn) { refreshBtn.disabled = true; }
        if (!watchTimer) { watchTimer = setInterval(watchTick, 1000); }
        watchTick();
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            refresh();
            if (!canManage) { showToast('Planta atualizada.'); return; }
            post({ action: 'collect' }).then(function (r) {
                if (r.ok && r.requested > 0) { startWatch(); }
            });
        });
    }

    function deskById(id) {
        for (var i = 0; i < state.desks.length; i++) {
            if (state.desks[i].id === id) { return state.desks[i]; }
        }
        return null;
    }

    function pct(value, total) { return (value / total * 100) + '%'; }

    // ------------------------------------------------------------------
    // Desenho
    // ------------------------------------------------------------------
    function render() {
        if (!state.plan) {
            panel.innerHTML = '';
            panel.appendChild(el('p', 'ar-empty-hint', 'Nenhuma planta cadastrada.'));
            return;
        }
        q('[data-ar-plan-name]').textContent = state.plan.name;
        q('[data-ar-updated]').textContent = (state.updated || '').split(' ')[1] || state.updated || '—';
        var src = plansUrl + encodeURIComponent(state.plan.id);
        if (bg.getAttribute('src') !== src) { bg.setAttribute('src', src); }
        canvas.style.aspectRatio = state.plan.width + ' / ' + state.plan.height;
        renderLegend();
        renderDesks();
        renderPanel();
        renderSide();
    }

    function renderLegend() {
        legend.innerHTML = '';
        Object.keys(STATES).forEach(function (key) {
            var chip = el('button', 'ar-chip' + (filter === key ? ' is-active' : ''));
            chip.type = 'button';
            chip.title = STATES[key].hint + (filter === key ? ' (clique para mostrar todas)' : ' (clique para destacar)');
            chip.appendChild(el('span', 'ar-swatch s-' + key));
            chip.appendChild(el('span', '', STATES[key].label));
            chip.appendChild(el('strong', '', String(state.counts[key] || 0)));
            chip.addEventListener('click', function () {
                filter = filter === key ? '' : key;
                renderLegend();
                renderDesks();
            });
            legend.appendChild(chip);
        });
        if (state.wifi > 0) {
            var wifi = el('span', 'ar-chip');
            wifi.style.cursor = 'default';
            wifi.title = 'Máquinas que só apareceram pelo Wi-Fi: sem porta de switch, não dá para saber a mesa.';
            wifi.appendChild(icon('wifi'));
            wifi.appendChild(el('span', '', 'Só Wi-Fi'));
            wifi.appendChild(el('strong', '', String(state.wifi)));
            legend.appendChild(wifi);
        }
    }

    function deskTitle(desk) {
        var parts = ['Mesa ' + desk.name];
        if (desk.port) { parts.push('Switch ' + desk.switch_display + ' · porta ' + desk.port); }
        else if (desk.machines_id) { parts.push('Sem porta (máquina no Wi-Fi vinculada à mesa)'); }
        desk.machines.forEach(function (m) {
            parts.push((m.label || m.computer || m.hostname) + (m.group ? ' · ' + m.group : '') + (m.online ? ' — ligada' : ' — desligada'));
        });
        if (desk.alerts.length) { parts.push(desk.alerts.length + ' alerta(s)'); }
        return parts.join('\n');
    }

    /* Areas dos setores: um retangulo colorido com o nome por tras das mesas
     * de cada setor, calculado das proprias mesas (acompanha se elas mudarem). */
    var ZONE_COLORS = ['29, 79, 145', '63, 155, 58', '217, 119, 6', '124, 58, 237', '13, 148, 136', '219, 39, 119', '71, 85, 105'];
    var zonesLayer = q('[data-ar-zones]');

    function sectorColor(name) {
        var list = (state.sectors || []).slice().sort();
        var i = list.indexOf(name);
        return ZONE_COLORS[(i < 0 ? 0 : i) % ZONE_COLORS.length];
    }

    function renderZones() {
        if (!zonesLayer) { return; }
        var W = state.plan.width;
        var H = state.plan.height;
        // Margem que cobre as cadeiras; menor nas laterais para setores vizinhos
        // (ex.: Estrategia e Suporte) nao se sobreporem.
        var PAD_X = 20, PAD_Y = 26;
        var boxes = {};
        state.desks.forEach(function (d) {
            if (!d.sector) { return; }
            var b = boxes[d.sector] || (boxes[d.sector] = { x1: Infinity, y1: Infinity, x2: -Infinity, y2: -Infinity, n: 0 });
            b.x1 = Math.min(b.x1, d.x - d.w / 2);
            b.y1 = Math.min(b.y1, d.y - d.h / 2);
            b.x2 = Math.max(b.x2, d.x + d.w / 2);
            b.y2 = Math.max(b.y2, d.y + d.h / 2);
            b.n++;
        });
        zonesLayer.innerHTML = '';
        Object.keys(boxes).forEach(function (name) {
            var b = boxes[name];
            var x1 = Math.max(0, b.x1 - PAD_X), y1 = Math.max(0, b.y1 - PAD_Y);
            var x2 = Math.min(W, b.x2 + PAD_X), y2 = Math.min(H, b.y2 + PAD_Y);
            var color = sectorColor(name);
            var zone = el('div', 'ar-zone');
            zone.style.left = pct(x1, W);
            zone.style.top = pct(y1, H);
            zone.style.width = pct(x2 - x1, W);
            zone.style.height = pct(y2 - y1, H);
            zone.style.setProperty('--zone', color);
            var label = el('span', 'ar-zone-label', name);
            label.title = name + ': ' + b.n + ' mesa(s)';
            zone.appendChild(label);
            zonesLayer.appendChild(zone);
        });
    }

    function renderDesks() {
        var W = state.plan.width;
        var H = state.plan.height;
        renderZones();
        desksLayer.innerHTML = '';
        state.desks.forEach(function (desk) {
            var node = el('button', 'ar-desk s-' + desk.state);
            node.type = 'button';
            node.setAttribute('data-id', String(desk.id));
            node.style.left = pct(desk.x - desk.w / 2, W);
            node.style.top = pct(desk.y - desk.h / 2, H);
            node.style.width = pct(desk.w, W);
            node.style.height = pct(desk.h, H);
            node.title = deskTitle(desk);
            node.setAttribute('aria-label', deskTitle(desk).replace(/\n/g, '. '));
            if (desk.id === selectedId) { node.classList.add('is-selected'); }
            if (filter && desk.state !== filter) { node.classList.add('is-dim'); }

            var chair = CHAIRS[desk.chair] ? desk.chair : 'down';
            node.classList.add('ch-' + chair);
            node.appendChild(el('span', 'ar-chair c-' + chair));
            var portLine = el('span', 'ar-desk-port' + (desk.machines_id ? ' is-wifi' : ''),
                desk.port || (desk.machines_id ? 'Wi-Fi' : (editing ? '+' : '–')));
            if (desk.switch) { portLine.appendChild(el('span', 'ar-desk-sw', desk.switch)); }
            node.appendChild(portLine);
            node.appendChild(el('span', 'ar-desk-name', desk.name));
            if (desk.alerts.length) { node.appendChild(el('span', 'ar-desk-badge', String(desk.alerts.length))); }

            var mons = [];
            desk.machines.forEach(function (m) { mons = mons.concat(m.monitors); });
            if (mons.length) {
                var bar = el('span', 'ar-desk-mons m-' + chair);
                mons.slice(0, 4).forEach(function (mon) { bar.appendChild(el('i', mon.missing ? 'is-missing' : '')); });
                node.appendChild(bar);
            }

            if (editing) {
                attachDrag(node, desk);
                // Soltar uma pessoa da lista "sem mesa" em cima da mesa.
                node.addEventListener('dragover', function (ev) {
                    if (ev.dataTransfer && Array.prototype.indexOf.call(ev.dataTransfer.types, 'application/x-ativarede-port') !== -1) {
                        ev.preventDefault();
                        node.classList.add('is-drop');
                    }
                });
                node.addEventListener('dragleave', function () { node.classList.remove('is-drop'); });
                node.addEventListener('drop', function (ev) {
                    node.classList.remove('is-drop');
                    var raw = ev.dataTransfer ? ev.dataTransfer.getData('application/x-ativarede-port') : '';
                    if (!raw) { return; }
                    ev.preventDefault();
                    try {
                        var target = JSON.parse(raw);
                        selectedId = desk.id;
                        saveDesk(desk, {
                            switches_id: target.switches_id || 0, port: target.port || '', machines_id: target.machines_id || 0
                        });
                    } catch (e) { /* ignora arraste de outra origem */ }
                });
            } else {
                node.addEventListener('click', function () { select(desk.id); });
            }
            desksLayer.appendChild(node);
        });
    }

    function select(id) {
        selectedId = id;
        Array.prototype.forEach.call(desksLayer.querySelectorAll('.ar-desk'), function (n) {
            n.classList.toggle('is-selected', Number(n.getAttribute('data-id')) === id);
        });
        renderPanel();
        if (editing) { renderSide(); }
    }

    // ------------------------------------------------------------------
    // Painel da mesa
    // ------------------------------------------------------------------
    function stateText(key) {
        return { online: 'Ligada', offline: 'Desligada', alert: 'Com alerta', empty: 'Vazia', unmapped: 'Sem porta' }[key] || key;
    }

    function kv(list, label, value) {
        list.appendChild(el('dt', '', label));
        var dd = el('dd');
        if (value instanceof Node) { dd.appendChild(value); } else { dd.textContent = value || '—'; }
        list.appendChild(dd);
    }

    function renderPanel() {
        panel.innerHTML = '';
        var desk = deskById(selectedId);
        if (!desk) {
            var hint = el('div', 'ar-empty-hint');
            hint.appendChild(icon('hand-click'));
            hint.appendChild(document.createTextNode(editing
                ? ' Clique em uma mesa para editar o nome, a porta e o lado da cadeira.'
                : ' Clique em uma mesa para ver a porta do switch, o computador, os monitores e os alertas.'));
            panel.appendChild(hint);
            return;
        }
        if (editing) { renderEditForm(desk); return; }

        var title = el('div', 'ar-panel-title');
        title.appendChild(el('h3', 'mb-0', 'Mesa ' + desk.name));
        var badge = el('span', 'ar-state s-' + desk.state);
        badge.appendChild(el('span', 'ar-swatch s-' + desk.state));
        badge.appendChild(document.createTextNode(stateText(desk.state)));
        title.appendChild(badge);
        panel.appendChild(title);

        var info = el('dl', 'ar-kv');
        if (desk.sector) { kv(info, 'Setor', desk.sector); }
        if (desk.port) {
            kv(info, 'Porta do switch', 'Switch ' + desk.switch_display + ' · porta ' + desk.port);
        } else if (desk.machines_id) {
            var fixed = el('span', '', 'Sem porta: máquina vinculada direto à mesa (Wi-Fi). Quando ela for ligada no cabo, a porta do switch entra aqui sozinha. ');
            if (canManage) {
                var unlink = el('button', 'btn btn-sm btn-link p-0 align-baseline', 'Desvincular');
                unlink.type = 'button';
                unlink.addEventListener('click', function () { saveDesk(desk, { machines_id: 0 }); });
                fixed.appendChild(unlink);
            }
            kv(info, 'Porta do switch', fixed);
        } else {
            kv(info, 'Porta do switch', 'Ainda não definida');
        }
        if (desk.comment) { kv(info, 'Observação', desk.comment); }
        panel.appendChild(info);

        if (!desk.machines.length) {
            panel.appendChild(el('p', 'ar-empty-hint', desk.port
                ? 'Nenhuma máquina nesta porta agora.'
                : (canManage ? 'Escolha abaixo quem senta nesta mesa.' : 'Esta mesa ainda não tem porta definida.')));
            if (canManage) { panel.appendChild(personPicker(desk)); }
        }

        desk.machines.forEach(function (m) {
            var box = el('div', 'ar-machine');
            var head = el('div', 'd-flex align-items-center gap-2 mb-2');
            head.appendChild(icon('device-desktop'));
            var name = el('strong');
            name.appendChild(link(m.computer_url, m.label || m.computer || m.hostname));
            head.appendChild(name);
            var online = el('span', 'badge ' + (m.online ? 'bg-green-lt' : 'bg-secondary-lt'), m.online ? 'Ligada' : 'Desligada');
            head.appendChild(online);
            box.appendChild(head);

            var list = el('dl', 'ar-kv mb-1');
            if (m.computer && m.hostname && m.computer !== m.hostname) { kv(list, 'Nome na rede', m.hostname); }
            if (!m.computer) { kv(list, 'Computador GLPI', 'não vinculado (série/nome não encontrados no inventário)'); }
            kv(list, 'Usuário (GLPI)', m.user ? m.user + (m.group ? ' · ' + m.group : '') : '');
            kv(list, 'IP', m.ip + (m.link === 'wifi' ? ' (Wi-Fi agora)' : ''));
            kv(list, 'Nesta mesa desde', m.since);
            if (m.pending) { kv(list, 'Possível mudança', 'apareceu em ' + m.pending + ' — aguardando confirmação'); }
            kv(list, 'Último contato', m.online ? 'agora' : (m.last_contact || m.last_report));
            box.appendChild(list);

            var monTitle = el('div', 'fw-semibold small mt-2', 'Monitores (' + m.monitors.length + ')');
            box.appendChild(monTitle);
            if (!m.monitors.length) {
                box.appendChild(el('div', 'ar-empty-hint small', 'Nenhum monitor externo informado.'));
            } else {
                var ul = el('ul', 'ar-monitor-list');
                m.monitors.forEach(function (mon) {
                    var li = el('li', mon.missing ? 'is-missing' : '');
                    li.appendChild(icon(mon.missing ? 'alert-triangle' : 'device-desktop-analytics'));
                    var text = el('span');
                    text.appendChild(link(mon.glpi_url, mon.model || 'Monitor'));
                    text.appendChild(document.createTextNode(
                        (mon.serial ? ' · série ' + mon.serial : ' · sem série') +
                        (mon.connection ? ' · ' + mon.connection : '') +
                        (mon.missing ? ' · AUSENTE desde ' + mon.missing_since : ' · desde ' + mon.since)
                    ));
                    li.appendChild(text);
                    ul.appendChild(li);
                });
                box.appendChild(ul);
            }
            panel.appendChild(box);
        });

        if (desk.alerts.length) {
            panel.appendChild(el('div', 'fw-semibold mb-2', 'Alertas desta mesa'));
            desk.alerts.forEach(function (a) { panel.appendChild(alertItem(a)); });
        }
    }

    /* "Quem senta aqui?": as portas com maquina e sem mesa, pelo nome da
     * pessoa. Um clique define a porta da mesa. */
    function unmappedLabel(u) {
        return u.machines.map(function (m) {
            // Usuario do GLPI; sem ele, a conta logada no Windows agora.
            return (m.user || m.windows_user || 'Sem usuário identificado') + (m.group ? ' · ' + m.group : '');
        }).join(' / ');
    }

    /* Porta do switch (cabo) ou vinculo direto (Wi-Fi / sem porta). */
    function unmappedTag(u) {
        if (u.kind === 'machine') { return u.link === 'wifi' ? 'Wi-Fi · sem porta' : 'sem porta'; }
        return u.switch + ' · porta ' + u.port;
    }
    function unmappedChanges(u) {
        return u.kind === 'machine'
            ? { machines_id: u.machines_id, switches_id: 0, port: '' }
            : { switches_id: u.switches_id, port: u.port, machines_id: 0 };
    }

    function personPicker(desk) {
        var box = el('div', 'ar-picker');
        box.appendChild(el('div', 'fw-semibold mb-1', 'Quem senta nesta mesa?'));
        if (!state.unmapped.length) {
            box.appendChild(el('p', 'ar-empty-hint small mb-0',
                'Todas as máquinas conhecidas já estão em alguma mesa. Máquinas novas aparecem aqui depois do primeiro envio do Ativa Guardian (até 15 min após ligar).'));
            return box;
        }
        box.appendChild(el('div', 'text-muted small mb-2',
            state.unmapped.length + ' máquina(s) sem mesa. Com cabo, a porta do switch vem junto; no Wi-Fi, a máquina fica vinculada direto à mesa.'));
        var search = el('input', 'form-control form-control-sm mb-2');
        search.type = 'search';
        search.placeholder = 'Buscar por pessoa, setor ou computador...';
        box.appendChild(search);
        var list = el('ul', 'ar-picker-list');
        var items = state.unmapped.slice().sort(function (a, b) {
            return unmappedLabel(a).localeCompare(unmappedLabel(b), 'pt-BR');
        });
        items.forEach(function (u) {
            var li = el('li');
            var b = el('button', 'ar-picker-item');
            b.type = 'button';
            var who = el('span', 'ar-picker-who', unmappedLabel(u));
            var what = el('span', 'ar-picker-what', u.machines.map(function (m) { return m.computer || m.hostname; }).join(', '));
            b.appendChild(who);
            b.appendChild(what);
            b.appendChild(el('span', 'ar-port-tag' + (u.kind === 'machine' ? ' is-wifi' : ''), unmappedTag(u)));
            b.setAttribute('data-search', (unmappedLabel(u) + ' ' + what.textContent + ' ' + unmappedTag(u)).toLowerCase());
            b.addEventListener('click', function () {
                b.disabled = true;
                saveDesk(desk, unmappedChanges(u));
            });
            li.appendChild(b);
            list.appendChild(li);
        });
        box.appendChild(list);
        search.addEventListener('input', function () {
            var term = search.value.trim().toLowerCase();
            Array.prototype.forEach.call(list.querySelectorAll('.ar-picker-item'), function (b) {
                b.parentNode.hidden = term !== '' && b.getAttribute('data-search').indexOf(term) === -1;
            });
        });
        return box;
    }

    function alertItem(a) {
        var box = el('div', 'ar-alert-item');
        box.appendChild(el('div', 'fw-semibold', a.title));
        box.appendChild(el('div', 'text-muted', a.detail));
        box.appendChild(el('div', 'small text-muted mt-1', a.date));
        if (canManage) {
            var actions = el('div', 'ar-alert-actions');
            if (a.can_authorize) { actions.appendChild(resolveButton(a, 'authorize', 'check', 'Autorizar', 'btn-success')); }
            actions.appendChild(resolveButton(a, 'ticket', 'ticket', 'Abrir chamado', 'btn-outline-primary'));
            actions.appendChild(resolveButton(a, 'ignore', 'eye-off', 'Ignorar', 'btn-outline-secondary'));
            box.appendChild(actions);
        }
        return box;
    }

    function resolveButton(a, resolution, iconName, label, cls) {
        var b = el('button', 'btn btn-sm ' + cls);
        b.type = 'button';
        b.appendChild(icon(iconName));
        b.appendChild(document.createTextNode(' ' + label));
        b.addEventListener('click', function () {
            b.disabled = true;
            post({ action: 'resolve', id: a.id, resolution: resolution }).then(function (r) {
                if (r.ok) { render(); } else { b.disabled = false; }
            });
        });
        return b;
    }

    // ------------------------------------------------------------------
    // Lateral: alertas recentes (visualizacao) / portas sem mesa (edicao)
    // ------------------------------------------------------------------
    function renderSide() {
        side.innerHTML = '';
        if (editing) {
            side.appendChild(el('h3', 'mb-1', 'Pessoas sem mesa'));
            side.appendChild(el('p', 'text-muted small', 'Arraste a pessoa até a mesa dela na planta (ou selecione a mesa e clique em "Usar na mesa"). A porta do switch é definida automaticamente.'));
            if (!state.unmapped.length) {
                side.appendChild(el('p', 'ar-empty-hint', 'Todas as portas com máquina já têm mesa.'));
                return;
            }
            var ul = el('ul', 'ar-unmapped-list');
            state.unmapped.forEach(function (u) {
                var li = el('li', 'ar-draggable');
                li.draggable = true;
                li.title = 'Arraste até a mesa';
                li.addEventListener('dragstart', function (ev) {
                    ev.dataTransfer.setData('application/x-ativarede-port', JSON.stringify(unmappedChanges(u)));
                    ev.dataTransfer.effectAllowed = 'link';
                });
                li.appendChild(icon('grip-vertical'));
                var who = el('span', 'flex-fill');
                who.appendChild(el('strong', '', unmappedLabel(u)));
                who.appendChild(el('div', 'text-muted', u.machines.map(function (m) { return m.computer || m.hostname; }).join(', ')));
                li.appendChild(who);
                li.appendChild(el('span', 'ar-port-tag' + (u.kind === 'machine' ? ' is-wifi' : ''), unmappedTag(u)));
                var use = el('button', 'btn btn-sm btn-outline-primary', 'Usar na mesa');
                use.type = 'button';
                use.disabled = !selectedId;
                use.title = selectedId ? 'Define esta porta para a mesa selecionada' : 'Selecione uma mesa primeiro';
                use.addEventListener('click', function () {
                    var desk = deskById(selectedId);
                    if (!desk) { return; }
                    saveDesk(desk, unmappedChanges(u));
                });
                li.appendChild(use);
                ul.appendChild(li);
            });
            side.appendChild(ul);
            return;
        }

        var head = el('div', 'd-flex align-items-center mb-2');
        head.appendChild(el('h3', 'mb-0', 'Alertas na planta'));
        var all = link(alertsUrl, 'Ver todos');
        all.className = 'ms-auto small';
        head.appendChild(all);
        side.appendChild(head);
        var alerts = [];
        var seen = {};
        state.desks.forEach(function (d) {
            d.alerts.forEach(function (a) { if (!seen[a.id]) { seen[a.id] = true; alerts.push({ desk: d, alert: a }); } });
        });
        if (!alerts.length) {
            side.appendChild(el('p', 'ar-empty-hint', 'Nenhum alerta aberto nesta planta.'));
        }
        alerts.slice(0, 8).forEach(function (item) {
            var box = alertItem(item.alert);
            var go = el('button', 'btn btn-sm btn-link p-0 mt-1', 'Ver mesa ' + item.desk.name);
            go.type = 'button';
            go.addEventListener('click', function () { select(item.desk.id); });
            box.appendChild(go);
            side.appendChild(box);
        });
        if (state.unmapped.length) {
            var note = el('p', 'small text-muted mt-3 mb-0');
            note.appendChild(icon('plug-connected'));
            note.appendChild(document.createTextNode(' ' + state.unmapped.length + ' porta(s) com máquina ainda sem mesa' + (canManage ? ' — use "Editar planta" para mapear.' : '.')));
            side.appendChild(note);
        }
    }

    // ------------------------------------------------------------------
    // Edicao
    // ------------------------------------------------------------------
    function renderEditForm(desk) {
        var form = el('form');
        form.appendChild(el('h3', 'mb-3', 'Editar mesa ' + desk.name));
        var row = el('div', 'row g-2');

        function field(label, control, col) {
            var wrap = el('div', col || 'col-md-6');
            var l = el('label', 'form-label small mb-1', label);
            wrap.appendChild(l);
            wrap.appendChild(control);
            row.appendChild(wrap);
            return control;
        }

        var name = el('input', 'form-control form-control-sm');
        name.value = desk.name; name.maxLength = 100; name.required = true;
        field('Nome da mesa', name);

        var chair = el('select', 'form-select form-select-sm');
        Object.keys(CHAIRS).forEach(function (k) {
            var o = el('option', '', CHAIRS[k]); o.value = k; o.selected = desk.chair === k; chair.appendChild(o);
        });
        field('Cadeira', chair);

        // Setor: livre, com sugestao dos setores ja usados na planta.
        var sector = el('input', 'form-control form-control-sm');
        sector.value = desk.sector || ''; sector.maxLength = 64; sector.placeholder = 'ex.: Comercial';
        var listId = 'ar-sectors-' + desk.id;
        sector.setAttribute('list', listId);
        var datalist = el('datalist');
        datalist.id = listId;
        (state.sectors || []).forEach(function (s) { var o = el('option'); o.value = s; datalist.appendChild(o); });
        field('Setor', sector);
        row.lastChild.appendChild(datalist);

        var sw = el('select', 'form-select form-select-sm');
        var none = el('option', '', '— sem porta —'); none.value = '0'; sw.appendChild(none);
        state.switches.forEach(function (s) {
            var o = el('option', '', s.display); o.value = String(s.id); o.selected = desk.switches_id === s.id; sw.appendChild(o);
        });
        field('Switch', sw);
        if (!state.switches.length) {
            row.lastChild.appendChild(el('div', 'form-text', 'Os switches aparecem aqui assim que o primeiro computador enviar a posição.'));
        }

        var port = el('input', 'form-control form-control-sm');
        port.value = desk.port; port.maxLength = 64; port.placeholder = 'ex.: 15';
        field('Porta', port);

        var size = el('div', 'd-flex gap-2');
        var w = el('input', 'form-control form-control-sm'); w.type = 'number'; w.min = '16'; w.max = '200'; w.value = String(desk.w);
        var h = el('input', 'form-control form-control-sm'); h.type = 'number'; h.min = '16'; h.max = '200'; h.value = String(desk.h);
        size.appendChild(w); size.appendChild(h);
        field('Tamanho (largura × altura)', size);

        var comment = el('input', 'form-control form-control-sm');
        comment.value = desk.comment; comment.maxLength = 255;
        field('Observação', comment);

        form.appendChild(row);

        var actions = el('div', 'd-flex gap-2 mt-3');
        var save = el('button', 'btn btn-sm btn-primary');
        save.type = 'submit';
        save.appendChild(icon('device-floppy'));
        save.appendChild(document.createTextNode(' Salvar'));
        actions.appendChild(save);
        var del = el('button', 'btn btn-sm btn-outline-danger ms-auto');
        del.type = 'button';
        del.appendChild(icon('trash'));
        del.appendChild(document.createTextNode(' Remover mesa'));
        del.addEventListener('click', function () {
            if (!window.confirm('Remover a mesa ' + desk.name + '? O histórico de alertas é mantido.')) { return; }
            post({ action: 'delete_desk', id: desk.id }).then(function (r) { if (r.ok) { selectedId = 0; render(); } });
        });
        actions.appendChild(del);
        form.appendChild(actions);

        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            saveDesk(desk, {
                name: name.value, chair: chair.value, switches_id: sw.value, port: port.value.trim(),
                w: w.value, h: h.value, comment: comment.value, sector: sector.value.trim()
            });
        });
        panel.appendChild(form);
        var picker = personPicker(desk);
        picker.classList.add('mt-3', 'pt-3', 'border-top');
        panel.appendChild(picker);
    }

    function saveDesk(desk, changes) {
        var fields = {
            action: 'save_desk', id: desk.id, name: desk.name, chair: desk.chair,
            switches_id: desk.switches_id, port: desk.port, machines_id: desk.machines_id || 0,
            w: desk.w, h: desk.h, comment: desk.comment, sector: desk.sector || ''
        };
        Object.keys(changes).forEach(function (k) { fields[k] = changes[k]; });
        post(fields).then(function (r) { if (r.ok) { render(); } });
    }

    function attachDrag(node, desk) {
        node.addEventListener('pointerdown', function (ev) {
            if (ev.button !== 0) { return; }
            ev.preventDefault();
            var rect = canvas.getBoundingClientRect();
            var W = state.plan.width;
            var H = state.plan.height;
            var startX = ev.clientX;
            var startY = ev.clientY;
            var origX = desk.x;
            var origY = desk.y;
            var moved = false;
            node.setPointerCapture(ev.pointerId);
            node.classList.add('is-dragging');

            function onMove(e) {
                var dx = (e.clientX - startX) / rect.width * W;
                var dy = (e.clientY - startY) / rect.height * H;
                if (!moved && Math.abs(e.clientX - startX) + Math.abs(e.clientY - startY) < 4) { return; }
                moved = true;
                desk.x = Math.max(0, Math.min(W, Math.round(origX + dx)));
                desk.y = Math.max(0, Math.min(H, Math.round(origY + dy)));
                node.style.left = pct(desk.x - desk.w / 2, W);
                node.style.top = pct(desk.y - desk.h / 2, H);
            }
            function onUp() {
                node.removeEventListener('pointermove', onMove);
                node.removeEventListener('pointerup', onUp);
                node.removeEventListener('pointercancel', onUp);
                node.classList.remove('is-dragging');
                if (moved) {
                    post({ action: 'move_desk', id: desk.id, x: desk.x, y: desk.y }).then(function (r) {
                        if (!r.ok) { desk.x = origX; desk.y = origY; }
                        select(desk.id);
                        renderDesks();
                    });
                } else {
                    select(desk.id);
                }
            }
            node.addEventListener('pointermove', onMove);
            node.addEventListener('pointerup', onUp);
            node.addEventListener('pointercancel', onUp);
        });
        node.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); select(desk.id); }
        });
    }

    function setEditing(on) {
        editing = on;
        app.classList.toggle('is-editing', on);
        if (editBar) { editBar.hidden = !on; }
        if (editBtn) {
            editBtn.classList.toggle('btn-primary', on);
            editBtn.classList.toggle('btn-outline-primary', !on);
            editBtn.querySelector('span').textContent = on ? 'Concluir edição' : 'Editar planta';
        }
        render();
        if (!on) { refresh(); }
    }

    // ------------------------------------------------------------------
    // Controles
    // ------------------------------------------------------------------
    function applyZoom() {
        canvas.style.width = (zoom * 100) + '%';
        var label = q('[data-ar-zoom-label]');
        if (label) { label.textContent = Math.round(zoom * 100) + '%'; }
    }

    Array.prototype.forEach.call(app.querySelectorAll('[data-ar-zoom]'), function (b) {
        b.addEventListener('click', function () {
            var step = Number(b.getAttribute('data-ar-zoom'));
            zoom = step === 0 ? 1 : Math.max(1, Math.min(3, Math.round((zoom + step * 0.25) * 100) / 100));
            applyZoom();
        });
    });

    if (editBtn && canManage) {
        editBtn.addEventListener('click', function () { setEditing(!editing); });
    }

    var addBtn = q('[data-ar-add]');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            post({ action: 'add_desk', x: Math.round(state.plan.width / 2), y: Math.round(state.plan.height / 2) }).then(function (r) {
                if (r.ok) { selectedId = r.desk_id || 0; render(); }
            });
        });
    }

    var planSelect = q('[data-ar-plan-select]');
    if (planSelect) {
        planSelect.addEventListener('change', function () {
            window.location.href = selfUrl + '?plan=' + encodeURIComponent(planSelect.value);
        });
    }

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && selectedId) { select(0); }
    });

    applyZoom();
    render();
    setInterval(refresh, 60000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { refresh(); } });
})();
