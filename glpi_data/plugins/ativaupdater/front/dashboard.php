<?php

use GlpiPlugin\Ativaupdater\ClientsView;
use GlpiPlugin\Ativaupdater\ReleasePolicy;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaupdaterProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}

Html::header(
    __('Ativa Updater', 'ativaupdater'),
    $_SERVER['PHP_SELF'],
    'plugins',
    'ativaupdater'
);

$release = new PluginAtivaupdaterRelease();
$activeRelease = $release->getActiveRelease();
$canManage = Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)
    || Session::haveRight('config', UPDATE);

// Get all releases
global $DB;
$releases = [];
$iterator = $DB->request([
    'FROM'  => 'glpi_plugin_ativaupdater_releases',
    'ORDER' => 'version DESC'
]);
foreach ($iterator as $row) {
    $releases[] = $row;
}
usort($releases, static fn(array $left, array $right): int => version_compare($right['version'], $left['version']));

echo "<div class='container-fluid mt-3'>";
echo "<h2>" . __('Ativa Updater Dashboard', 'ativaupdater') . "</h2>";

// Display Active Release
echo "<div class='card mb-4'>";
echo "<div class='card-header'><h3>" . __('Versão Atual', 'ativaupdater') . "</h3></div>";
echo "<div class='card-body'>";
if ($activeRelease) {
    echo "<p><strong>Versão:</strong> " . htmlescape((string) $activeRelease['version']) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlescape((string) $activeRelease['original_filename']) . "</p>";
    echo "<p><strong>Tamanho:</strong> " . htmlescape(Toolbox::getSize((int) $activeRelease['file_size'])) . "</p>";
    echo "<p><strong>SHA-256:</strong> <code>" . htmlescape((string) $activeRelease['sha256']) . "</code></p>";
    echo "<p><strong>Publicado em:</strong> " . Html::convDateTime($activeRelease['created_at']) . "</p>";
    if ((int) ($activeRelease['allow_downgrade'] ?? 0) === 1) {
        echo "<p><strong>Política:</strong> <span class='badge bg-warning text-dark'>Rollback autorizado</span> "
            . "<span class='text-muted'>Computadores em versões maiores voltarão para esta versão.</span></p>";
    } else {
        echo "<p><strong>Política:</strong> <span class='badge bg-success'>Somente atualização</span></p>";
    }
    if (!empty($activeRelease['activated_at'])) {
        $activatedBy = (int) ($activeRelease['activated_by'] ?? 0);
        echo "<p><strong>Ativada em:</strong> " . Html::convDateTime($activeRelease['activated_at'])
            . ($activatedBy > 0 ? ' por ' . htmlescape(getUserName($activatedBy)) : '') . "</p>";
    }
} else {
    echo "<p>" . __('Nenhuma versão ativa no momento.', 'ativaupdater') . "</p>";
}
echo "</div></div>";

// Upload Form
if ($canManage) {
    echo "<div class='card mb-4'>";
    echo "<div class='card-header'><h3>" . __('Publicar nova versão', 'ativaupdater') . "</h3></div>";
    echo "<div class='card-body'>";
    echo "<form method='post' action='action.php' enctype='multipart/form-data'>";
    echo Html::hidden('action', ['value' => 'upload']);
    echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
    
    echo "<div class='mb-3'>";
    echo "<label for='version' class='form-label'>" . __('Versão (ex: 1.4.4)', 'ativaupdater') . "</label>";
    echo "<input type='text' class='form-control' id='version' name='version' required pattern='^\\d+\\.\\d+\\.\\d+$'>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label for='installer' class='form-label'>" . __('Instalador (.exe)', 'ativaupdater') . "</label>";
    echo "<input type='file' class='form-control' id='installer' name='installer' accept='.exe' required>";
    echo "<div class='form-text'>Envie o arquivo Ativa-Unified-Agent-Setup-X.Y.Z.exe. Ele atualiza GLPI Agent, Wallpaper Client e Ativa Updater juntos.</div>";
    echo "</div>";
    
    echo "<button type='submit' class='btn btn-primary'>" . __('Enviar e publicar', 'ativaupdater') . "</button>";
    
    echo "</form>";
    echo "</div></div>";
}

// History Table
echo "<div class='card mb-4'>";
echo "<div class='card-header'><h3>" . __('Histórico de Versões', 'ativaupdater') . "</h3></div>";
echo "<div class='card-body'>";
if (count($releases) > 0) {
    echo "<table class='table table-striped'>";
    echo "<thead><tr>";
    echo "<th>" . __('Versão', 'ativaupdater') . "</th>";
    echo "<th>" . __('Arquivo', 'ativaupdater') . "</th>";
    echo "<th>" . __('Tamanho', 'ativaupdater') . "</th>";
    echo "<th>" . __('Data', 'ativaupdater') . "</th>";
    echo "<th>" . __('Status', 'ativaupdater') . "</th>";
    if ($canManage) {
        echo "<th>" . __('Ações', 'ativaupdater') . "</th>";
    }
    echo "</tr></thead><tbody>";
    
    $setActiveForm = static function (int $id, bool $allowDowngrade, string $label, string $buttonClass, string $icon, ?string $confirm = null): string {
        $onsubmit = $confirm !== null
            ? ' onsubmit="return confirm(' . htmlescape(json_encode($confirm, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) . ');"'
            : '';
        return "<form method='post' action='action.php' style='display:inline;'" . $onsubmit . ">"
            . Html::hidden('action', ['value' => 'set_active'])
            . Html::hidden('id', ['value' => $id])
            . Html::hidden('allow_downgrade', ['value' => $allowDowngrade ? '1' : '0'])
            . Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()])
            . "<button type='submit' class='btn btn-sm " . $buttonClass . " me-2' title='" . htmlescape($label) . "'>"
            . "<i class='" . $icon . "'></i> " . htmlescape($label) . "</button>"
            . "</form>";
    };
    $rollbackUnsupported = 'Pacotes anteriores a ' . ReleasePolicy::ROLLBACK_MIN_VERSION . ' não suportam downgrade.';

    foreach ($releases as $rel) {
        $releaseId = (int) $rel['id'];
        $releaseVersion = (string) $rel['version'];
        $isActive = (int) $rel['active'] === 1;
        $allowsDowngrade = $isActive && (int) ($rel['allow_downgrade'] ?? 0) === 1;
        $canRollback = ReleasePolicy::canRollbackTo($releaseVersion);

        $status = $isActive ? "<span class='badge bg-success'>" . __('Atual', 'ativaupdater') . "</span>" : "<span class='badge bg-secondary'>" . __('Anterior', 'ativaupdater') . "</span>";
        if ($allowsDowngrade) {
            $status .= " <span class='badge bg-warning text-dark'>Rollback</span>";
        }
        echo "<tr>";
        echo "<td>" . htmlescape($releaseVersion) . "</td>";
        echo "<td>" . htmlescape((string) $rel['original_filename']) . "</td>";
        echo "<td>" . htmlescape(Toolbox::getSize((int) $rel['file_size'])) . "</td>";
        echo "<td>" . Html::convDateTime($rel['created_at']) . "</td>";
        echo "<td>" . $status . "</td>";
        if ($canManage) {
            echo "<td class='text-nowrap'>";
            $rollbackConfirm = 'Rollback para a versão ' . $releaseVersion . ': TODOS os computadores em versões maiores '
                . 'reinstalarão esta versão na próxima consulta. Continuar?';
            if (!$isActive) {
                echo $setActiveForm($releaseId, false, __('Definir como atual', 'ativaupdater'), 'btn-outline-primary', 'fas fa-check');
                if ($canRollback) {
                    echo $setActiveForm($releaseId, true, 'Rollback', 'btn-outline-warning', 'fas fa-undo', $rollbackConfirm);
                } else {
                    echo "<span class='d-inline-block me-2' tabindex='0' title='" . htmlescape($rollbackUnsupported) . "'>"
                        . "<button type='button' class='btn btn-sm btn-outline-secondary' disabled><i class='fas fa-undo'></i> Rollback</button></span>";
                }
            } elseif ($allowsDowngrade) {
                echo $setActiveForm($releaseId, false, 'Revogar downgrade', 'btn-outline-secondary', 'fas fa-ban');
            } elseif ($canRollback) {
                echo $setActiveForm($releaseId, true, 'Autorizar downgrade', 'btn-outline-warning', 'fas fa-undo', $rollbackConfirm);
            }

            echo "<form method='post' action='action.php' style='display:inline;' onsubmit='return confirm(\"" . __('Tem certeza que deseja excluir esta versão?', 'ativaupdater') . "\");'>";
            echo Html::hidden('action', ['value' => 'delete']);
            echo Html::hidden('id', ['value' => $rel['id']]);
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
            echo "<button type='submit' class='btn btn-sm btn-outline-danger' title='" . __('Excluir', 'ativaupdater') . "'><i class='fas fa-trash'></i></button>";
            echo "</form>";
            
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>" . __('Nenhum histórico disponível.', 'ativaupdater') . "</p>";
}
echo "</div></div>";

// Clients reporting through the Windows service. The section is refreshed in
// place by polling front/clients.php; only changes are re-rendered.
$clientsData = ClientsView::load();
$clientsNow = time();
echo "<div class='card mb-4' id='ativaupdater-clients-card' data-endpoint='clients.php'"
    . " data-signature='" . htmlescape(ClientsView::signature($clientsData, $clientsNow)) . "'>";
echo "<div class='card-header d-flex justify-content-between align-items-center flex-wrap gap-2'>";
echo "<h3 class='mb-0'>Computadores</h3>";
echo "<div class='d-flex align-items-center gap-3'>";
echo "<span id='ativaupdater-live-indicator' class='text-muted small'><i class='fas fa-circle text-success me-1' style='font-size: .6rem'></i>Ao vivo</span>";
if ($canManage) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-ativaupdater-command='check_now'"
        . " data-confirm='" . htmlescape('Cancelar o que está em andamento em todos os computadores e recomeçar a verificação agora?') . "'>"
        . "<i class='fas fa-sync-alt me-1'></i>Verificar agora</button>";
}
echo "</div></div>";
echo "<div class='card-body'>";
echo "<div id='ativaupdater-live-message'></div>";
echo "<div id='ativaupdater-clients-body'>" . ClientsView::render($clientsData, $canManage, $clientsNow) . "</div>";
echo "</div></div>";

echo "</div>";

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
        indicator.innerHTML = '';
        const dot = document.createElement('i');
        dot.className = 'fas fa-circle me-1 ' + (ok ? 'text-success' : 'text-danger');
        dot.style.fontSize = '.6rem';
        indicator.append(dot, document.createTextNode(text));
    };

    const showMessage = (text, ok) => {
        messageBox.innerHTML = '';
        if (!text) return;
        const alert = document.createElement('div');
        alert.className = 'alert alert-dismissible ' + (ok ? 'alert-info' : 'alert-danger');
        alert.textContent = text;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close';
        close.addEventListener('click', () => alert.remove());
        alert.append(close);
        messageBox.append(alert);
    };

    const refresh = async (force = false) => {
        if (refreshing || (document.hidden && !force)) return;
        refreshing = true;
        try {
            const url = card.dataset.endpoint + '?signature=' + encodeURIComponent(force ? '' : signature);
            const response = await fetch(url, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            if (data.changed && typeof data.html === 'string') {
                // Keep opened logs open across updates.
                const opened = Array.from(body.querySelectorAll('details[open][data-key]')).map((item) => item.dataset.key);
                body.innerHTML = data.html;
                opened.forEach((key) => body.querySelector('details[data-key="' + CSS.escape(key) + '"]')?.setAttribute('open', ''));
            }
            signature = data.signature || signature;
            failures = 0;
            setIndicator('Ao vivo · ' + new Date().toLocaleTimeString('pt-BR'), true);
        } catch (error) {
            failures += 1;
            setIndicator(failures > 2 ? 'Sem conexão com o GLPI; tentando novamente' : 'Atualizando...', failures <= 2);
        } finally {
            refreshing = false;
        }
    };

    const sendCommand = async (button) => {
        const command = button.dataset.ativaupdaterCommand;
        if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) return;
        const form = new FormData();
        if (command === 'check_now') {
            form.append('action', 'check_now');
        } else {
            form.append('action', 'client_command');
            form.append('command', command);
            form.append('id', button.dataset.clientId);
        }
        button.disabled = true;
        try {
            const response = await fetch('action.php', {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrfToken()},
            });
            const data = await response.json().catch(() => null);
            if (!data) {
                showMessage('A sessão pode ter expirado. Recarregue a página e tente novamente.', false);
            } else {
                showMessage(data.message, data.ok);
            }
        } catch (error) {
            showMessage('Não foi possível enviar o comando: ' + error.message, false);
        } finally {
            button.disabled = false;
            refresh(true);
        }
    };

    card.addEventListener('click', (event) => {
        const button = event.target.closest('[data-ativaupdater-command]');
        if (!button || button.disabled) return;
        event.preventDefault();
        sendCommand(button);
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(true); });
    window.setInterval(refresh, 3000);
})();
</script>
HTML;

Html::footer();
