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
echo '</div></details>' . PageLayout::footer();

echo <<<'HTML'
<script>
(() => {
    const card = document.getElementById('ativaupdater-clients-card');
    if (!card) return;
    const body = document.getElementById('ativaupdater-clients-body');
    const indicator = document.getElementById('ativaupdater-live-indicator');
    const messageBox = document.getElementById('ativaupdater-live-message');
    const csrfToken = () => document.querySelector('meta[property="glpi:csrf_token"]')?.getAttribute('content') || '';
    let signature = card.dataset.signature || '';
    let refreshing = false;
    let failures = 0;

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
    const sendCommand = async (button) => {
        const command = button.dataset.ativaupdaterCommand;
        if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) return;
        const form = new FormData();
        if (command === 'check_now') form.append('action', 'check_now');
        else { form.append('action', 'client_command'); form.append('command', command); form.append('id', button.dataset.clientId); }
        button.disabled = true;
        try {
            const response = await fetch('action.php', {method: 'POST', body: form, credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrfToken()}});
            const data = await response.json().catch(() => null);
            showMessage(data?.message || 'A sessão pode ter expirado. Recarregue a página.', Boolean(data?.ok));
        } catch (error) { showMessage('Não foi possível enviar o comando: ' + error.message, false); }
        finally { button.disabled = false; refresh(true); }
    };
    document.addEventListener('click', (event) => {
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
