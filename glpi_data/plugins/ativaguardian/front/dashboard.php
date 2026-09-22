<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\MachinesView;
use GlpiPlugin\Ativaguardian\PageLayout;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}

Html::header(__('Ativa Guardian', 'ativaguardian'), $_SERVER['PHP_SELF'], 'plugins', 'ativaguardian');

$data = MachinesView::load();
$metrics = MachinesView::metrics($data);
$canManage = Session::haveRight(PluginAtivaguardianProfile::RIGHT_MANAGE, UPDATE)
    || Session::haveRight('config', UPDATE);

echo PageLayout::header('overview', true);
echo MachinesView::renderKpis($metrics);

echo "<section class='ag-card' id='ag-machines-card' data-endpoint='machines.php' data-signature='"
    . htmlescape(MachinesView::signature($data)) . "'>";
echo "<header class='ag-card-head'>"
    . "<div class='ag-card-head-text'><h2><i class='fas fa-desktop'></i>Máquinas</h2>"
    . '<p>Status dos componentes Ativa instalados nas máquinas Windows</p></div>'
    . "<div class='ag-search'><i class='fas fa-magnifying-glass'></i>"
    . "<input type='search' id='ag-search' placeholder='Buscar máquina...' autocomplete='off'></div>"
    . '</header>';
echo "<div class='ag-card-body' id='ag-machines-body'>" . MachinesView::renderTable($data, $canManage) . '</div></section>';

echo PageLayout::footer();

echo <<<'HTML'
<script>
(() => {
    const card = document.getElementById('ag-machines-card');
    if (!card) return;
    const body = document.getElementById('ag-machines-body');
    const indicator = document.getElementById('ag-live-indicator');
    let signature = card.dataset.signature || '';
    let refreshing = false;
    let failures = 0;

    const setIndicator = (text, ok) => {
        if (!indicator) return;
        indicator.innerHTML = '';
        const dot = document.createElement('i');
        dot.className = 'fas fa-circle';
        dot.style.color = ok ? 'var(--ag-green)' : 'var(--ag-red)';
        indicator.append(dot, document.createTextNode(' ' + text));
    };
    const refresh = async (force = false) => {
        if (refreshing || (document.hidden && !force)) return;
        refreshing = true;
        try {
            const response = await fetch(card.dataset.endpoint + '?signature=' + encodeURIComponent(force ? '' : signature), {
                credentials: 'same-origin', cache: 'no-store',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            if (data.metrics) {
                Object.entries(data.metrics).forEach(([key, value]) => {
                    const el = document.querySelector('[data-ag-metric="' + key + '"]');
                    if (el) el.textContent = value;
                });
            }
            if (data.changed && typeof data.html === 'string') body.innerHTML = data.html;
            signature = data.signature || signature;
            failures = 0;
            setIndicator('Ao vivo · ' + new Date().toLocaleTimeString('pt-BR'), true);
        } catch (error) {
            failures += 1;
            setIndicator(failures > 2 ? 'Sem conexão; tentando novamente' : 'Atualizando...', failures <= 2);
        } finally { refreshing = false; }
    };

    const csrf = () => document.querySelector('meta[property="glpi:csrf_token"]')?.getAttribute('content') || '';
    const notify = (text, ok) => {
        let box = document.getElementById('ag-message');
        if (!box) { box = document.createElement('div'); box.id = 'ag-message'; card.parentNode.insertBefore(box, card); }
        box.innerHTML = '';
        const alert = document.createElement('div');
        alert.className = 'ag-alert ' + (ok ? 'ag-alert-info' : 'ag-alert-danger');
        alert.textContent = text;
        box.append(alert);
    };
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-ag-action]');
        if (!button || button.disabled) return;
        event.preventDefault();
        button.disabled = true;
        try {
            const form = new FormData();
            form.append('machines_id', button.dataset.agMachine);
            form.append('component', button.dataset.agComponent);
            form.append('action', button.dataset.agAction);
            const response = await fetch('action.php', {
                method: 'POST', body: form, credentials: 'same-origin',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrf()},
            });
            const data = await response.json().catch(() => null);
            if (!response.ok || !data?.ok) throw new Error(data?.message || 'Falha ao enviar a acao.');
            notify(data.message, true);
        } catch (error) {
            notify(error.message, false);
            button.disabled = false;
        }
        refresh(true);
    });
    // --- Busca, ordenação e menu de ações: tudo no cliente. A tabela tem uma
    // linha por máquina, então filtrar/ordenar aqui evita ida ao servidor e
    // sobrevive às trocas de HTML feitas pelo refresh ao vivo.
    const rows = () => Array.from(body.querySelectorAll('tbody tr'));
    const applyFilter = () => {
        const term = (document.getElementById('ag-search')?.value || '').trim().toLowerCase();
        let shown = 0;
        rows().forEach((row) => {
            const match = !term || (row.dataset.agName || '').includes(term);
            row.hidden = !match;
            if (match) shown += 1;
        });
        const counter = body.querySelector('[data-ag-count]');
        const total = rows().length;
        if (counter) counter.textContent = `Mostrando ${shown} de ${total} ` + (total === 1 ? 'máquina' : 'máquinas');
    };
    document.getElementById('ag-search')?.addEventListener('input', applyFilter);

    let sortState = {index: null, asc: true};
    const sortBy = (index) => {
        const table = body.querySelector('table');
        const tbody = table?.querySelector('tbody');
        if (!tbody) return;
        sortState = {index, asc: sortState.index === index ? !sortState.asc : true};
        const text = (row) => (row.children[index]?.innerText || '').trim().toLowerCase();
        Array.from(tbody.querySelectorAll('tr'))
            .sort((a, b) => text(a).localeCompare(text(b), 'pt-BR', {numeric: true}) * (sortState.asc ? 1 : -1))
            .forEach((row) => tbody.append(row));
    };

    const closeMenus = (except) => body.querySelectorAll('.ag-menu-list').forEach((list) => {
        if (list !== except) list.hidden = true;
    });

    body.addEventListener('click', (event) => {
        const sorter = event.target.closest('.ag-sort');
        if (sorter) {
            sortBy(Array.from(sorter.closest('tr').children).indexOf(sorter.closest('th')));
            return;
        }
        const kebab = event.target.closest('[data-ag-menu]');
        if (kebab) {
            const list = kebab.parentElement.querySelector('.ag-menu-list');
            closeMenus(list);
            list.hidden = !list.hidden;
        }
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.ag-menu')) closeMenus(null);
    });
    // O refresh ao vivo troca o HTML da tabela: reaplica o filtro e a hora.
    const observer = new MutationObserver(() => {
        applyFilter();
        const stamp = body.querySelector('[data-ag-updated]');
        if (stamp) stamp.textContent = new Date().toLocaleString('pt-BR').replace(',', '');
    });
    observer.observe(body, {childList: true});

    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(true); });
    window.setInterval(refresh, 5000);
})();
</script>
HTML;

Html::footer();
