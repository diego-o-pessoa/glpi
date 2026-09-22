<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\MachinesView;
use GlpiPlugin\Ativaguardian\PageLayout;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}

$data = MachinesView::load();
$metrics = MachinesView::metrics($data);
$canManage = Session::haveRight(PluginAtivaguardianProfile::RIGHT_MANAGE, UPDATE)
    || Session::haveRight('config', UPDATE);
$signature = MachinesView::signature($data);

// Live-refresh endpoint: the dashboard polls this with X-Requested-With and
// only receives new HTML when the signature moved. Same pattern the Ativa
// Updater uses for its clients section.
$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    $previousSignature = (string) ($_GET['signature'] ?? '');
    $changed = $previousSignature !== $signature;
    echo json_encode([
        'signature' => $signature,
        'changed'   => $changed,
        'metrics'   => $metrics,
        'html'      => $changed ? MachinesView::renderTable($data, $canManage) : null,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    return;
}

// A aba "Computadores" foi removida por mostrar a mesma tabela da Visão Geral.
// Esta página continua existindo como endpoint da atualização ao vivo; um
// acesso direto vai para a Visão Geral em vez de exibir a tabela duplicada.
global $CFG_GLPI;
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/ativaguardian/front/dashboard.php');

Html::header(__('Ativa Guardian', 'ativaguardian'), $_SERVER['PHP_SELF'], 'plugins', 'ativaguardian');

echo PageLayout::header('machines', true);
echo MachinesView::renderKpis($metrics);

echo "<section class='ag-card' id='ag-machines-card' data-endpoint='machines.php' data-signature='"
    . htmlescape($signature) . "'>";
echo "<header class='ag-card-header'><h2 class='ag-card-title'><i class='fas fa-desktop'></i>Todos os computadores</h2></header>";
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
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(true); });
    window.setInterval(refresh, 5000);
})();
</script>
HTML;

Html::footer();
