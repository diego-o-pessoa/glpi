<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ClientsView;
use GlpiPlugin\Ativaupdater\PageLayout;
use GlpiPlugin\Ativaupdater\ReleasePolicy;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaupdaterProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}

Html::header(__('Ativa Updater', 'ativaupdater'), $_SERVER['PHP_SELF'], 'plugins', 'ativaupdater');

$release = new PluginAtivaupdaterRelease();
$activeRelease = $release->getActiveRelease();
$canManage = Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)
    || Session::haveRight('config', UPDATE);

global $DB;
$releases = [];
$iterator = $DB->request(['FROM' => 'glpi_plugin_ativaupdater_releases', 'ORDER' => 'version DESC']);
foreach ($iterator as $row) {
    $releases[] = $row;
}
usort($releases, static fn(array $left, array $right): int => version_compare($right['version'], $left['version']));

$clientsData = ClientsView::load();
$clientsNow = time();
$metrics = ClientsView::metrics($clientsData, $clientsNow);
$activeVersion = $activeRelease ? (string) $activeRelease['version'] : '-';

echo PageLayout::header('overview', $canManage, true);
echo "<section class='aw-kpis' aria-label='Resumo'>";
echo "<article class='aw-kpi'><span class='aw-kpi-icon'><i class='fas fa-cube'></i></span><div><div class='aw-kpi-label'>Versão publicada</div><span class='aw-kpi-value'>"
    . htmlescape($activeVersion) . '</span>' . ($activeRelease ? "<span class='aw-current-badge'>Atual</span>" : '') . '</div></article>';
echo "<article class='aw-kpi'><span class='aw-kpi-icon'><i class='fas fa-desktop'></i></span><div><div class='aw-kpi-label'>Computadores</div><span class='aw-kpi-value' data-aw-metric='total'>{$metrics['total']}</span><span class='aw-kpi-note'>gerenciados</span></div></article>";
echo "<article class='aw-kpi is-green'><span class='aw-kpi-icon'><i class='fas fa-check'></i></span><div><div class='aw-kpi-label'>Saudáveis</div><span class='aw-kpi-value' data-aw-metric='updated'>{$metrics['updated']}</span><span class='aw-kpi-note'>na versão atual</span></div></article>";
echo "<article class='aw-kpi is-red'><span class='aw-kpi-icon'><i class='fas fa-exclamation'></i></span><div><div class='aw-kpi-label'>Com falha</div><span class='aw-kpi-value' data-aw-metric='errors'>{$metrics['errors']}</span><span class='aw-kpi-note'>requer atenção</span></div></article>";
echo '</section>';

echo "<section class='aw-grid'>";
echo "<article class='aw-card'><header class='aw-card-header'><h2 class='aw-card-title'><i class='fas fa-cube'></i>Pacote publicado</h2></header><div class='aw-card-body'>";
if ($activeRelease) {
    $policy = (int) ($activeRelease['allow_downgrade'] ?? 0) === 1
        ? "<span class='badge bg-warning text-dark'><i class='fas fa-undo me-1'></i>Rollback autorizado</span>"
        : "<span class='badge bg-success'><i class='fas fa-plus-circle me-1'></i>Somente atualização</span>";
    $activatedBy = (int) ($activeRelease['activated_by'] ?? 0);
    $activation = !empty($activeRelease['activated_at'])
        ? Html::convDateTime($activeRelease['activated_at']) . ($activatedBy > 0 ? ' por ' . htmlescape(getUserName($activatedBy)) : '')
        : '-';
    echo "<dl class='aw-package-data'>"
        . '<dt>Versão:</dt><dd>' . htmlescape((string) $activeRelease['version']) . '</dd>'
        . '<dt>Arquivo:</dt><dd>' . htmlescape((string) $activeRelease['original_filename']) . '</dd>'
        . '<dt>Tamanho:</dt><dd>' . htmlescape(Toolbox::getSize((int) $activeRelease['file_size'])) . '</dd>'
        . '<dt>Publicado em:</dt><dd>' . Html::convDateTime($activeRelease['created_at']) . '</dd>'
        . '<dt>Política:</dt><dd>' . $policy . '</dd>'
        . "<dt>SHA-256:</dt><dd><span class='aw-hash'><span>" . htmlescape((string) $activeRelease['sha256'])
        . "</span><button type='button' class='btn btn-sm' data-copy-value='" . htmlescape((string) $activeRelease['sha256'])
        . "' title='Copiar SHA-256'><i class='far fa-copy'></i></button></span></dd>"
        . '<dt>Ativada em:</dt><dd>' . $activation . '</dd></dl>';
} else {
    echo "<p class='aw-empty'>Nenhuma versão publicada. Envie o primeiro instalador unificado ao lado.</p>";
}
echo '</div></article>';

echo "<article class='aw-card'><header class='aw-card-header'><h2 class='aw-card-title'><i class='fas fa-upload'></i>Publicar nova versão</h2></header><div class='aw-card-body'>";
if ($canManage) {
    echo "<form method='post' action='action.php' enctype='multipart/form-data' class='aw-form-grid'>";
    echo Html::hidden('action', ['value' => 'upload']);
    echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
    echo "<label for='version'>Versão</label><div><input type='text' class='form-control' id='version' name='version' placeholder='ex.: 1.6.1' required pattern='^\\d+\\.\\d+\\.\\d+$'></div>";
    echo "<label for='installer'>Instalador (.exe)</label><div><input type='file' class='form-control' id='installer' name='installer' accept='.exe' required><div class='form-text'>Envie o Ativa-Unified-Agent-Setup-X.Y.Z.exe. Ele atualiza o GLPI Agent, Wallpaper Client e Ativa Updater juntos.</div></div>";
    echo "<div class='aw-submit-row'><button type='submit' class='btn aw-submit-btn'><i class='fas fa-upload me-2'></i>Enviar e publicar</button></div></form>";
} else {
    echo "<p class='aw-empty'>Seu perfil pode acompanhar a distribuição, mas não possui permissão para publicar versões.</p>";
}
echo '</div></article></section>';

echo "<section class='aw-card' id='ativaupdater-clients-card' data-endpoint='clients.php' data-signature='"
    . htmlescape(ClientsView::signature($clientsData, $clientsNow)) . "'>";
echo "<header class='aw-card-header'><div class='aw-status-head'><h2 class='aw-card-title'><i class='fas fa-desktop'></i>Status dos computadores</h2>"
    . "<div class='aw-progress-summary'><span><b data-aw-progress='updated'>{$metrics['updated']}</b> de <b data-aw-progress='total'>{$metrics['total']}</b> computadores na versão atual</span>"
    . "<div class='aw-progress'><span data-aw-progress='bar' style='width: {$metrics['progress']}%'>{$metrics['progress']}%</span></div>"
    . "<strong data-aw-progress='percent'>{$metrics['progress']}%</strong></div></div></header>";
echo "<div class='aw-card-body'><div id='ativaupdater-live-message'></div><div id='ativaupdater-clients-body'>"
    . ClientsView::render($clientsData, $canManage, $clientsNow) . '</div></div></section>';

$setActiveForm = static function (int $id, bool $allowDowngrade, string $label, string $buttonClass, string $icon, ?string $confirm = null): string {
    $onsubmit = $confirm !== null ? ' onsubmit="return confirm(' . htmlescape(json_encode($confirm, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) . ');"' : '';
    return "<form method='post' action='action.php' class='d-inline'{$onsubmit}>"
        . Html::hidden('action', ['value' => 'set_active']) . Html::hidden('id', ['value' => $id])
        . Html::hidden('allow_downgrade', ['value' => $allowDowngrade ? '1' : '0'])
        . Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()])
        . "<button type='submit' class='btn btn-sm {$buttonClass} me-2' title='" . htmlescape($label) . "'><i class='{$icon}'></i> " . htmlescape($label) . '</button></form>';
};

echo "<details class='aw-card aw-history'><summary><i class='fas fa-history me-2'></i>Versões anteriores e rollback (" . count($releases) . ")</summary><div class='table-responsive'>";
if ($releases) {
    echo "<table class='table table-striped align-middle'><thead><tr><th>Versão</th><th>Arquivo</th><th>Tamanho</th><th>Data</th><th>Status</th>" . ($canManage ? '<th>Ações</th>' : '') . '</tr></thead><tbody>';
    $rollbackUnsupported = 'Pacotes anteriores a ' . ReleasePolicy::ROLLBACK_MIN_VERSION . ' não suportam downgrade.';
    foreach ($releases as $rel) {
        $releaseId = (int) $rel['id'];
        $releaseVersion = (string) $rel['version'];
        $isActive = (int) $rel['active'] === 1;
        $allowsDowngrade = $isActive && (int) ($rel['allow_downgrade'] ?? 0) === 1;
        $canRollback = ReleasePolicy::canRollbackTo($releaseVersion);
        $status = $isActive ? "<span class='badge bg-success'>Atual</span>" : "<span class='badge bg-secondary'>Anterior</span>";
        if ($allowsDowngrade) $status .= " <span class='badge bg-warning text-dark'>Rollback</span>";
        echo '<tr><td>' . htmlescape($releaseVersion) . '</td><td>' . htmlescape((string) $rel['original_filename']) . '</td><td>'
            . htmlescape(Toolbox::getSize((int) $rel['file_size'])) . '</td><td>' . Html::convDateTime($rel['created_at']) . "</td><td>{$status}</td>";
        if ($canManage) {
            echo "<td class='text-nowrap'>";
            $rollbackConfirm = "Rollback para a versão {$releaseVersion}: todos os computadores em versões maiores reinstalarão esta versão. Continuar?";
            if (!$isActive) {
                echo $setActiveForm($releaseId, false, 'Definir como atual', 'btn-outline-primary', 'fas fa-check');
                echo $canRollback ? $setActiveForm($releaseId, true, 'Rollback', 'btn-outline-warning', 'fas fa-undo', $rollbackConfirm)
                    : "<span class='d-inline-block me-2' title='" . htmlescape($rollbackUnsupported) . "'><button type='button' class='btn btn-sm btn-outline-secondary' disabled><i class='fas fa-undo'></i> Rollback</button></span>";
            } elseif ($allowsDowngrade) {
                echo $setActiveForm($releaseId, false, 'Revogar downgrade', 'btn-outline-secondary', 'fas fa-ban');
            } elseif ($canRollback) {
                echo $setActiveForm($releaseId, true, 'Autorizar downgrade', 'btn-outline-warning', 'fas fa-undo', $rollbackConfirm);
            }
            echo "<form method='post' action='action.php' class='d-inline' onsubmit='return confirm(\"Excluir esta versão?\");'>"
                . Html::hidden('action', ['value' => 'delete']) . Html::hidden('id', ['value' => $releaseId])
                . Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()])
                . "<button type='submit' class='btn btn-sm btn-outline-danger' title='Excluir'><i class='fas fa-trash'></i></button></form></td>";
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo "<p class='aw-empty p-3'>Nenhum histórico disponível.</p>";
}
echo '</div></details>';
echo <<<'HTML'
<div class="aw-modal-layer" id="ativaupdater-operation-modal" role="dialog" aria-modal="true" aria-labelledby="ativaupdater-modal-title" hidden>
    <section class="aw-modal">
        <header class="aw-modal-header">
            <div class="aw-modal-heading">
                <i id="ativaupdater-modal-icon" class="fas fa-file-alt"></i>
                <div><h2 id="ativaupdater-modal-title">Ativa Updater</h2><p id="ativaupdater-modal-subtitle"></p></div>
            </div>
            <button type="button" class="aw-modal-close" id="ativaupdater-modal-close" aria-label="Fechar"><i class="fas fa-times"></i></button>
        </header>
        <div class="aw-modal-body" id="ativaupdater-modal-body"></div>
        <footer class="aw-modal-footer" id="ativaupdater-modal-footer" hidden>
            <button type="button" class="btn btn-primary" id="ativaupdater-modal-done">Fechar</button>
        </footer>
    </section>
</div>
HTML;
echo PageLayout::footer();

echo <<<'HTML'
<script>
(() => {
    const card = document.getElementById('ativaupdater-clients-card');
    if (!card) return;
    const body = document.getElementById('ativaupdater-clients-body');
    const indicator = document.getElementById('ativaupdater-live-indicator');
    const messageBox = document.getElementById('ativaupdater-live-message');
    const modalLayer = document.getElementById('ativaupdater-operation-modal');
    const modalTitle = document.getElementById('ativaupdater-modal-title');
    const modalSubtitle = document.getElementById('ativaupdater-modal-subtitle');
    const modalIcon = document.getElementById('ativaupdater-modal-icon');
    const modalBody = document.getElementById('ativaupdater-modal-body');
    const modalClose = document.getElementById('ativaupdater-modal-close');
    const modalFooter = document.getElementById('ativaupdater-modal-footer');
    const modalDone = document.getElementById('ativaupdater-modal-done');
    const csrfToken = () => document.querySelector('meta[property="glpi:csrf_token"]')?.getAttribute('content') || '';
    let signature = card.dataset.signature || '';
    let refreshing = false;
    let failures = 0;
    let modalLocked = false;
    let operationToken = 0;

    const setIndicator = (text, ok) => {
        if (!indicator) return;
        indicator.innerHTML = '';
        const dot = document.createElement('i');
        dot.className = 'fas fa-circle';
        dot.style.color = ok ? 'var(--aw-green)' : 'var(--aw-red)';
        indicator.append(dot, document.createTextNode(text));
    };
    const showMessage = (text, ok) => {
        messageBox.innerHTML = '';
        if (!text) return;
        const alert = document.createElement('div');
        alert.className = 'aw-alert ' + (ok ? 'aw-alert-info' : 'aw-alert-danger');
        alert.textContent = text;
        messageBox.append(alert);
    };
    const refresh = async (force = false) => {
        if (refreshing || (document.hidden && !force)) return;
        refreshing = true;
        try {
            const response = await fetch(card.dataset.endpoint + '?signature=' + encodeURIComponent(force ? '' : signature), {credentials: 'same-origin', cache: 'no-store', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            if (data.metrics) {
                ['total', 'updated', 'errors'].forEach((key) => {
                    const value = document.querySelector('[data-aw-metric="' + key + '"]');
                    if (value && data.metrics[key] !== undefined) value.textContent = data.metrics[key];
                });
                const progressUpdated = document.querySelector('[data-aw-progress="updated"]');
                const progressTotal = document.querySelector('[data-aw-progress="total"]');
                const progressBar = document.querySelector('[data-aw-progress="bar"]');
                const progressPercent = document.querySelector('[data-aw-progress="percent"]');
                if (progressUpdated) progressUpdated.textContent = data.metrics.updated;
                if (progressTotal) progressTotal.textContent = data.metrics.total;
                if (progressBar) {
                    progressBar.style.width = data.metrics.progress + '%';
                    progressBar.textContent = data.metrics.progress + '%';
                }
                if (progressPercent) progressPercent.textContent = data.metrics.progress + '%';
            }
            if (data.changed && typeof data.html === 'string') {
                const opened = Array.from(body.querySelectorAll('details[open][data-key]')).map((item) => item.dataset.key);
                body.innerHTML = data.html;
                opened.forEach((key) => body.querySelector('details[data-key="' + CSS.escape(key) + '"]')?.setAttribute('open', ''));
            }
            signature = data.signature || signature;
            failures = 0;
            setIndicator('Ao vivo · ' + new Date().toLocaleTimeString('pt-BR'), true);
        } catch (error) {
            failures += 1;
            setIndicator(failures > 2 ? 'Sem conexão; tentando novamente' : 'Atualizando...', failures <= 2);
        } finally { refreshing = false; }
    };
    const delay = (milliseconds) => new Promise((resolve) => window.setTimeout(resolve, milliseconds));
    const openModal = (title, subtitle, icon, locked) => {
        operationToken += 1;
        modalLocked = locked;
        modalTitle.textContent = title;
        modalSubtitle.textContent = subtitle;
        modalIcon.className = 'fas ' + icon;
        modalBody.innerHTML = '';
        modalClose.hidden = locked;
        modalFooter.hidden = true;
        modalLayer.hidden = false;
        document.body.classList.add('aw-modal-open');
        return operationToken;
    };
    const unlockModal = () => {
        modalLocked = false;
        modalClose.hidden = false;
        modalFooter.hidden = false;
    };
    const closeModal = () => {
        if (modalLocked) return;
        operationToken += 1;
        modalLayer.hidden = true;
        document.body.classList.remove('aw-modal-open');
    };
    const requestAction = async (fields) => {
        const form = new FormData();
        Object.entries(fields).forEach(([key, value]) => form.append(key, value));
        const response = await fetch('action.php', {method: 'POST', body: form, credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrfToken()}});
        const data = await response.json().catch(() => null);
        if (!response.ok || !data?.ok) throw new Error(data?.message || 'A sessão pode ter expirado. Recarregue a página.');
        return data;
    };
    const requestClientCommand = (id, command) => requestAction({action: 'client_command', command, id});
    const fetchClientDetails = async (id) => {
        const response = await fetch('client_details.php?id=' + encodeURIComponent(id), {credentials: 'same-origin', cache: 'no-store', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
        const data = await response.json().catch(() => null);
        if (!response.ok || !data?.ok) throw new Error(data?.message || 'Não foi possível consultar o computador.');
        return data;
    };
    const sendCommand = async (button) => {
        const command = button.dataset.ativaupdaterCommand;
        if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) return;
        button.disabled = true;
        try {
            const data = command === 'check_now'
                ? await requestAction({action: 'check_now'})
                : await requestClientCommand(button.dataset.clientId, command);
            showMessage(data.message, true);
        } catch (error) { showMessage('Não foi possível enviar o comando: ' + error.message, false); }
        finally { button.disabled = false; refresh(true); }
    };

    const logSections = (data) => {
        const sections = [];
        if ((data.install_log || '').trim()) sections.push({title: 'Log da instalação', content: data.install_log.trim()});
        const diagnostic = (data.diagnostics_log || '').trim();
        if (diagnostic) {
            const headings = Array.from(diagnostic.matchAll(/^== (.+?) ==$/gm));
            if (!headings.length) sections.push({title: 'Diagnóstico completo', content: diagnostic});
            else {
                const introduction = diagnostic.slice(0, headings[0].index).trim();
                if (introduction) sections.push({title: 'Resumo do computador', content: introduction});
                headings.forEach((heading, index) => {
                    const start = heading.index + heading[0].length;
                    const end = index + 1 < headings.length ? headings[index + 1].index : diagnostic.length;
                    sections.push({title: heading[1], content: diagnostic.slice(start, end).trim() || 'Sem conteúdo.'});
                });
            }
        }
        if (!sections.length) sections.push({title: 'Logs', content: 'Nenhum log foi enviado por este computador ainda.'});
        const installerSections = sections.filter((section) => section.title.startsWith('Instalador (')).reverse();
        return sections.filter((section) => !section.title.startsWith('Instalador (')).concat(installerSections);
    };
    const renderLogs = (data, updating, notice = '') => {
        modalBody.innerHTML = '';
        const alert = document.createElement('div');
        alert.className = 'aw-alert ' + (notice ? 'aw-alert-danger' : 'aw-alert-info');
        alert.textContent = notice || (updating ? 'Solicitando uma coleta atualizada ao computador…' : 'Logs atualizados pelo serviço.');
        modalBody.append(alert);
        const layout = document.createElement('div');
        layout.className = 'aw-log-layout';
        const menu = document.createElement('nav');
        menu.className = 'aw-log-menu';
        const content = document.createElement('pre');
        content.className = 'aw-log-content';
        const sections = logSections(data);
        const select = (selected, button) => {
            menu.querySelectorAll('button').forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');
            content.textContent = selected.content;
        };
        sections.forEach((section, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = section.title;
            button.addEventListener('click', () => select(section, button));
            menu.append(button);
            if (index === 0) select(section, button);
        });
        layout.append(menu, content);
        modalBody.append(layout);
    };
    const openLogs = async (button) => {
        const id = button.dataset.clientId;
        const token = openModal('Logs do computador', button.dataset.hostname || '', 'fa-file-alt', false);
        modalBody.innerHTML = "<div class='aw-operation'><div class='aw-operation-icon is-running'><i class='fas fa-sync-alt'></i></div><h3>Carregando logs…</h3></div>";
        let details = {install_log: '', diagnostics_log: ''};
        try {
            details = await fetchClientDetails(id);
            if (token !== operationToken) return;
            renderLogs(details, true);
            const command = await requestClientCommand(id, 'send_logs');
            for (let attempt = 0; attempt < 40 && token === operationToken; attempt += 1) {
                details = await fetchClientDetails(id);
                if (details.ack_seq >= command.request_seq) {
                    renderLogs(details, false);
                    return;
                }
                await delay(1500);
            }
            if (token === operationToken) renderLogs(details, false, 'O computador não respondeu à nova coleta. Os últimos logs recebidos continuam disponíveis abaixo.');
        } catch (error) {
            if (token === operationToken) renderLogs(details, false, error.message);
        }
    };

    const renderRestart = (percent, title, note, state = 'running') => {
        modalBody.innerHTML = "<div class='aw-operation'><div class='aw-operation-icon is-" + state + "'><i class='fas "
            + (state === 'done' ? 'fa-check' : state === 'error' ? 'fa-exclamation' : 'fa-sync-alt')
            + "'></i></div><h3></h3><p></p><div class='aw-operation-progress'><span></span></div><div class='aw-operation-note'>Não feche esta janela enquanto o serviço estiver reiniciando.</div></div>";
        modalBody.querySelector('h3').textContent = title;
        modalBody.querySelector('p').textContent = note;
        const bar = modalBody.querySelector('.aw-operation-progress span');
        bar.style.width = percent + '%';
        bar.textContent = percent + '%';
    };
    const restartService = async (button) => {
        const id = button.dataset.clientId;
        const token = openModal('Reiniciando Ativa Updater', button.dataset.hostname || '', 'fa-sync-alt', true);
        renderRestart(5, 'Preparando reinício', 'Registrando o comando no servidor…');
        try {
            const command = await requestClientCommand(id, 'restart_service');
            const started = Date.now();
            for (let attempt = 0; attempt < 180 && token === operationToken; attempt += 1) {
                const details = await fetchClientDetails(id);
                const acknowledged = details.ack_seq >= command.request_seq;
                if (acknowledged && details.acknowledged_at_ts > 0 && details.last_check_ts > details.acknowledged_at_ts) {
                    renderRestart(100, 'Serviço reiniciado', 'O computador voltou a se comunicar com o servidor.', 'done');
                    unlockModal();
                    refresh(true);
                    return;
                }
                if (acknowledged) {
                    const elapsedAfterAck = Math.max(0, details.server_time_ts - details.acknowledged_at_ts);
                    const progress = Math.min(95, 65 + Math.floor(elapsedAfterAck * 3));
                    renderRestart(progress, 'Iniciando novamente', 'Comando confirmado. Aguardando o novo contato do serviço…');
                } else {
                    const progress = Math.min(55, 10 + Math.floor((Date.now() - started) / 500));
                    renderRestart(progress, 'Enviando comando', 'Aguardando o computador receber a solicitação…');
                }
                await delay(1000);
            }
            if (token === operationToken) {
                renderRestart(95, 'O serviço não confirmou o retorno', 'Verifique se o computador está ligado e se o serviço consegue acessar o GLPI.', 'error');
                unlockModal();
            }
        } catch (error) {
            if (token === operationToken) {
                renderRestart(0, 'Não foi possível reiniciar', error.message, 'error');
                unlockModal();
            }
        }
    };

    modalClose.addEventListener('click', closeModal);
    modalDone.addEventListener('click', closeModal);
    modalLayer.addEventListener('click', (event) => { if (event.target === modalLayer) closeModal(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !modalLayer.hidden) closeModal(); });
    document.addEventListener('click', (event) => {
        const logsButton = event.target.closest('[data-aw-open-logs]');
        if (logsButton && !logsButton.disabled) { event.preventDefault(); openLogs(logsButton); return; }
        const restartButton = event.target.closest('[data-aw-restart-service]');
        if (restartButton && !restartButton.disabled) { event.preventDefault(); restartService(restartButton); return; }
        const commandButton = event.target.closest('[data-ativaupdater-command]');
        if (commandButton && !commandButton.disabled) { event.preventDefault(); sendCommand(commandButton); return; }
        const copyButton = event.target.closest('[data-copy-value]');
        if (copyButton) navigator.clipboard?.writeText(copyButton.dataset.copyValue || '');
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(true); });
    window.setInterval(refresh, 3000);
})();
</script>
HTML;

Html::footer();
