/* Ativa Rede - Equipamentos: abas, busca e filtros da lista de maquinas
 * (filial e situacao) e a edicao do nome de quem usa cada maquina. Sem
 * bibliotecas externas; sem JS a pagina continua mostrando tudo.
 */
(function () {
    'use strict';

    var app = document.getElementById('ar-equip');
    if (!app) { return; }

    var all = function (sel, root) { return Array.prototype.slice.call((root || app).querySelectorAll(sel)); };
    var STORE = 'ativarede.equip.branch';

    // ------------------------------------------------------------------
    // Abas (o endereco #switches, #missing... abre a aba certa)
    // ------------------------------------------------------------------
    var tabs = all('[data-ar-tab]');
    var panes = all('[data-ar-pane]');

    function showTab(name) {
        if (!panes.some(function (p) { return p.getAttribute('data-ar-pane') === name; })) { name = 'maquinas'; }
        tabs.forEach(function (t) {
            var on = t.getAttribute('data-ar-tab') === name;
            t.classList.toggle('active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panes.forEach(function (p) { p.hidden = p.getAttribute('data-ar-pane') !== name; });
    }

    tabs.forEach(function (t) {
        t.addEventListener('click', function (ev) {
            ev.preventDefault();
            var name = t.getAttribute('data-ar-tab');
            showTab(name);
            if (window.history && history.replaceState) { history.replaceState(null, '', '#' + name); }
        });
    });

    function openFromHash() {
        var hash = (location.hash || '').replace('#', '');
        if (hash === 'branches' || hash === 'ar-branch-rules') {
            showTab('maquinas');
            var panel = document.getElementById('ar-branch-rules');
            if (panel) {
                panel.hidden = false;
                panel.scrollIntoView({ block: 'nearest' });
            }
            return;
        }
        if (/^machine-\d+$/.test(hash)) {
            // Volta de "salvar nome": mostra a linha, mesmo se um filtro a escondia.
            showTab('maquinas');
            var row = document.getElementById(hash);
            if (row) {
                setBranch('');
                state = '';
                search.value = '';
                apply();
                row.classList.add('is-flash');
                row.scrollIntoView({ block: 'center' });
            }
            return;
        }
        showTab({ missing: 'missing', switches: 'switches', monitores: 'monitores' }[hash] || 'maquinas');
    }

    // ------------------------------------------------------------------
    // Filtros: busca + filial + situacao (blocos do resumo)
    // ------------------------------------------------------------------
    var search = app.querySelector('[data-ar-search]') || { value: '' };
    var rows = all('[data-ar-row]');
    var sections = all('[data-ar-section]');
    var nothing = app.querySelector('[data-ar-nothing]');
    var branch = '';
    var state = '';

    function matchesState(row) {
        switch (state) {
            case 'online': return row.getAttribute('data-online') === '1';
            case 'offline': return row.getAttribute('data-online') === '0';
            case 'nodesk': return row.getAttribute('data-nodesk') === '1';
            case 'diagnostic': return row.getAttribute('data-diag') === '1';
            default: return true;
        }
    }

    function apply() {
        var terms = search.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
        var total = 0;
        sections.forEach(function (section) {
            var inBranch = !branch || section.getAttribute('data-ar-section') === branch;
            var shown = 0;
            all('[data-ar-row]', section).forEach(function (row) {
                var text = row.getAttribute('data-search') || '';
                var ok = inBranch && matchesState(row) && terms.every(function (t) { return text.indexOf(t) !== -1; });
                row.hidden = !ok;
                if (ok) { shown++; }
            });
            section.hidden = shown === 0;
            var counter = section.querySelector('[data-ar-shown]');
            if (counter) { counter.textContent = String(shown); }
            total += shown;
        });
        if (nothing) { nothing.hidden = total > 0 || !rows.length; }
        all('[data-ar-state]').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-ar-state') === state && state !== '');
            b.setAttribute('aria-pressed', b.getAttribute('data-ar-state') === state ? 'true' : 'false');
        });
    }

    function setBranch(value) {
        branch = value;
        all('[data-ar-branch]').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-ar-branch') === value);
        });
        try { localStorage.setItem(STORE, value); } catch (e) { /* sem armazenamento: so nao lembra */ }
    }

    all('[data-ar-branch]').forEach(function (b) {
        b.addEventListener('click', function () {
            setBranch(b.getAttribute('data-ar-branch'));
            apply();
        });
    });

    all('[data-ar-state]').forEach(function (b) {
        b.addEventListener('click', function () {
            var value = b.getAttribute('data-ar-state');
            state = state === value ? '' : value;
            showTab('maquinas');
            apply();
        });
    });

    if (search.addEventListener) {
        search.addEventListener('input', apply);
        search.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') { search.value = ''; apply(); }
        });
    }

    // ------------------------------------------------------------------
    // Editar o nome da pessoa (lapis ao lado do nome)
    // ------------------------------------------------------------------
    all('[data-ar-edit]').forEach(function (b) {
        b.addEventListener('click', function () {
            var id = b.getAttribute('data-ar-edit');
            var form = app.querySelector('[data-ar-edit-form="' + id + '"]');
            if (!form) { return; }
            form.hidden = !form.hidden;
            var row = form.closest('tr');
            if (row) { row.classList.toggle('is-editing', !form.hidden); }
            if (!form.hidden) {
                var input = form.querySelector('input[name="user_label"]');
                if (input) { input.focus(); input.select(); }
            }
        });
    });

    all('[data-ar-edit-form]').forEach(function (form) {
        form.addEventListener('keydown', function (ev) {
            if (ev.key !== 'Escape') { return; }
            form.hidden = true;
            var row = form.closest('tr');
            if (row) { row.classList.remove('is-editing'); }
        });
    });

    // Mostrar/esconder (ex.: "Filiais por rede").
    all('[data-ar-toggle]').forEach(function (b) {
        b.addEventListener('click', function () {
            var target = document.querySelector(b.getAttribute('data-ar-toggle'));
            if (target) { target.hidden = !target.hidden; }
        });
    });

    // Select de filial na tabela de roteadores/redes: alterna campo de texto customizado
    all('select[data-ar-branch-select]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var form = sel.closest('form');
            if (!form) { return; }
            var custom = form.querySelector('input[data-ar-branch-custom]');
            if (custom) {
                var isCustom = sel.value === '__custom__';
                custom.style.display = isCustom ? 'block' : 'none';
                if (isCustom) {
                    custom.value = '';
                    custom.focus();
                }
            }
        });
    });

    // Filial escolhida da ultima vez (se ainda existir).
    try {
        var saved = localStorage.getItem(STORE) || '';
        if (saved && all('[data-ar-branch]').some(function (b) { return b.getAttribute('data-ar-branch') === saved; })) {
            setBranch(saved);
        }
    } catch (e) { /* sem armazenamento */ }

    apply();
    openFromHash();
    window.addEventListener('hashchange', openFromHash);
})();
