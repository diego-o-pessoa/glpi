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

// Modal de operação: fica travado enquanto a ação roda, como no Ativa Updater.
echo <<<'HTML'
<div class="ag-modal-layer" id="ag-modal" role="dialog" aria-modal="true" aria-labelledby="ag-modal-title" hidden>
    <section class="ag-modal">
        <header class="ag-modal-head">
            <div class="ag-modal-head-text">
                <h3 id="ag-modal-title">Ativa Guardian</h3>
                <p id="ag-modal-subtitle"></p>
            </div>
            <button type="button" class="ag-modal-close" id="ag-modal-close" aria-label="Fechar" hidden>&times;</button>
        </header>
        <div id="ag-modal-body"></div>
        <footer class="ag-modal-foot" id="ag-modal-foot" hidden>
            <button type="button" class="btn btn-primary" id="ag-modal-done">Fechar</button>
        </footer>
    </section>
</div>
HTML;

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
    // --- Modal de operação (mesmo comportamento do Ativa Updater) ------------
    const layer = document.getElementById('ag-modal');
    const modalTitle = document.getElementById('ag-modal-title');
    const modalSubtitle = document.getElementById('ag-modal-subtitle');
    const modalBody = document.getElementById('ag-modal-body');
    const modalClose = document.getElementById('ag-modal-close');
    const modalFoot = document.getElementById('ag-modal-foot');
    const modalDone = document.getElementById('ag-modal-done');
    let locked = false;

    const openModal = (title, subtitle) => {
        locked = true;                 // travado: não dá para fechar durante a execução
        modalTitle.textContent = title;
        modalSubtitle.textContent = subtitle;
        modalBody.innerHTML = '';
        modalClose.hidden = true;
        modalFoot.hidden = true;
        layer.hidden = false;
        document.body.classList.add('ag-modal-open');
    };
    const unlockModal = () => { locked = false; modalClose.hidden = false; modalFoot.hidden = false; };
    const closeModal = () => {
        if (locked) return;
        layer.hidden = true;
        document.body.classList.remove('ag-modal-open');
    };
    const renderOp = (percent, title, note, state = 'run') => {
        const icon = state === 'done' ? 'fa-check' : state === 'err' ? 'fa-triangle-exclamation' : 'fa-arrows-rotate';
        modalBody.innerHTML =
            `<div class="ag-op"><div class="ag-op-icon is-${state}"><i class="fas ${icon}"></i></div>` +
            '<h4></h4><p></p><div class="ag-op-bar"><span></span></div>' +
            '<div class="ag-op-note">Não feche esta janela enquanto a operação estiver em andamento.</div></div>';
        modalBody.querySelector('h4').textContent = title;
        modalBody.querySelector('p').textContent = note;
        const bar = modalBody.querySelector('.ag-op-bar span');
        bar.style.width = percent + '%';
        bar.textContent = percent + '%';
    };

    // O menu mostra só "Corrigir"; aqui o título diz o que está sendo feito de
    // fato, que depende do estado em que o componente estava.
    const LABELS = {
        START_COMPONENT: ['Corrigindo: iniciando o serviço', 'Correção'],
        RESTART_COMPONENT: ['Corrigindo: reiniciando o serviço', 'Correção'],
        REPAIR_COMPONENT: ['Corrigindo: reinstalando o componente', 'Correção'],
        CHECK_COMPONENT: ['Verificando o componente', 'Verificação'],
    };
    const delay = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

    const followAction = async (actionId, verb) => {
        // O Guardian busca ações a cada 30 s, e o reparo baixa um pacote: por
        // isso a espera é longa (até 20 min) antes de desistir.
        const started = Date.now();
        for (let attempt = 0; attempt < 800; attempt += 1) {
            let data = null;
            try {
                const response = await fetch('action_status.php?id=' + encodeURIComponent(actionId), {
                    credentials: 'same-origin', cache: 'no-store',
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                });
                data = await response.json().catch(() => null);
            } catch (error) { /* rede instável: tenta de novo */ }

            if (data?.finished) {
                const ok = data.status === 'success';
                renderOp(100, ok ? verb + ' concluído' : 'Não foi possível concluir',
                    ok ? 'A máquina confirmou a execução.' : (data.message || 'A máquina reportou falha.'),
                    ok ? 'done' : 'err');
                unlockModal();
                refresh(true);
                return;
            }
            if (data?.status === 'running') {
                renderOp(Math.min(92, 45 + Math.floor((Date.now() - started) / 4000)),
                    'Executando na máquina', 'O Guardian recebeu a ação e está executando…');
            } else {
                renderOp(Math.min(40, 8 + Math.floor((Date.now() - started) / 1500)),
                    'Aguardando a máquina', 'A ação está na fila; o Guardian busca a cada 30 segundos.');
            }
            await delay(1500);
        }
        renderOp(92, 'Sem resposta da máquina',
            'A ação continua registrada no histórico. Verifique se o computador está ligado e com o Guardian em execução.', 'err');
        unlockModal();
    };

    modalClose.addEventListener('click', closeModal);
    modalDone.addEventListener('click', closeModal);
    layer.addEventListener('click', (event) => { if (event.target === layer) closeModal(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-ag-action]');
        if (!button || button.disabled) return;
        event.preventDefault();
        closeMenus(null);

        const [title, verb] = LABELS[button.dataset.agAction] || ['Executando ação', 'Ação'];
        const machine = button.closest('tr')?.querySelector('.ag-machine strong')?.textContent || '';
        openModal(title, machine + ' · ' + button.dataset.agComponent);
        renderOp(5, 'Registrando a solicitação', 'Enviando a ação ao servidor…');

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
            if (!response.ok || !data?.ok) throw new Error(data?.message || 'Falha ao enviar a ação.');
            await followAction(data.action_id, verb);
        } catch (error) {
            renderOp(0, 'Não foi possível solicitar', error.message, 'err');
            unlockModal();
        }
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
