<?php

use GlpiPlugin\Ativaupdater\ConfigService;
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

$clients = [];
if ($DB->tableExists('glpi_plugin_ativaupdater_clients')) {
    $clientIterator = $DB->request([
        'FROM'  => 'glpi_plugin_ativaupdater_clients',
        'ORDER' => ['last_check DESC'],
    ]);
    foreach ($clientIterator as $row) {
        $clients[] = $row;
    }
}

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

// Clients reporting through the Windows service
$activeVersion = $activeRelease ? (string) $activeRelease['version'] : '';
$activeAllowsDowngrade = $activeRelease !== null && (int) ($activeRelease['allow_downgrade'] ?? 0) === 1;
$activePublishedAt = $activeRelease
    ? strtotime((string) (($activeRelease['activated_at'] ?? '') ?: $activeRelease['created_at']))
    : false;
$checkInterval = max(300, min(86400, ConfigService::getInt('check_interval_seconds', 3600)));
$totalClients = count($clients);
$updatedClients = 0;
$errorClients = 0;
foreach ($clients as $client) {
    if ($activeVersion !== '' && ReleasePolicy::isOnTarget((string) $client['installed_version'], $activeVersion, $activeAllowsDowngrade)) {
        $updatedClients++;
    }
    $noReleaseYet = str_contains((string) ($client['message'] ?? ''), 'NO_RELEASE');
    if ((string) $client['status'] === 'error' && !$noReleaseYet) {
        $errorClients++;
    }
}
$progress = $totalClients > 0 ? (int) round(($updatedClients / $totalClients) * 100) : 0;

echo "<div class='card mb-4'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h3 class='mb-0'>Computadores</h3>";
echo "<div class='d-flex align-items-center gap-3'>";
echo "<span id='refresh-countdown' class='text-muted'>Atualização da tela em 15 s</span>";
if ($canManage && $totalClients > 0) {
    echo "<form method='post' action='action.php' class='m-0'>";
    echo Html::hidden('action', ['value' => 'check_now']);
    echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
    echo "<button type='submit' class='btn btn-primary btn-sm'><i class='fas fa-sync-alt me-1'></i>Verificar agora</button>";
    echo "</form>";
}
echo "</div></div>";
echo "<div class='card-body'>";
if ($activeVersion === '') {
    echo "<div class='alert alert-info mb-3'>{$totalClients} computador(es) identificado(s). Publique o primeiro instalador unificado para iniciar a distribuição automática.</div>";
} else {
    echo "<div class='d-flex justify-content-between mb-1'><span>{$updatedClients} de {$totalClients} computador(es) na versão atual</span><strong>{$progress}%</strong></div>";
    echo "<div class='progress mb-3' style='height: 20px'><div class='progress-bar bg-success' role='progressbar' style='width: {$progress}%' aria-valuenow='{$progress}' aria-valuemin='0' aria-valuemax='100'>{$progress}%</div></div>";
}
if ($errorClients > 0) {
    echo "<div class='alert alert-danger'>{$errorClients} computador(es) informaram erro. Consulte o motivo na tabela.</div>";
}
if ($totalClients === 0) {
    echo "<p class='text-muted mb-0'>Nenhum serviço se identificou ainda. Depois da instalação, a primeira consulta acontece imediatamente.</p>";
} else {
    echo "<div class='table-responsive'><table class='table table-striped align-middle'><thead><tr><th>Computador</th><th>Pacote unificado</th><th>Wallpaper</th><th>GLPI Agent</th><th>Disponível</th><th>Status</th><th>Última consulta</th><th>Próxima consulta</th><th>Detalhes</th></tr></thead><tbody>";
    $labels = [
        'checking' => ['Consultando', 'bg-info'],
        'waiting_release' => ['Aguardando publicação', 'bg-info'],
        'waiting_check' => ['Aguardando próxima consulta', 'bg-info'],
        'manual_check' => ['Verificando agora', 'bg-primary'],
        'current' => ['Atualizado', 'bg-success'],
        'downloading' => ['Baixando', 'bg-primary'],
        'installing' => ['Instalando', 'bg-warning text-dark'],
        'updated' => ['Atualizado', 'bg-success'],
        'error' => ['Erro', 'bg-danger'],
    ];
    foreach ($clients as $client) {
        $statusKey = (string) $client['status'];
        $lastCheck = (string) $client['last_check'];
        $lastCheckAt = strtotime($lastCheck) ?: 0;
        $requestedAt = strtotime((string) ($client['check_requested_at'] ?? '')) ?: 0;
        $acknowledgedAt = strtotime((string) ($client['check_acknowledged_at'] ?? '')) ?: 0;
        $manualPending = $requestedAt > $acknowledgedAt;
        $noReleaseMessage = str_contains((string) ($client['message'] ?? ''), 'NO_RELEASE');
        $clientAction = $activeVersion !== ''
            ? ReleasePolicy::clientAction((string) $client['installed_version'], $activeVersion, $activeAllowsDowngrade)
            : ReleasePolicy::ACTION_CURRENT;
        $needsActiveVersion = in_array($clientAction, [ReleasePolicy::ACTION_UPGRADE, ReleasePolicy::ACTION_DOWNGRADE], true);
        $releaseIsNewerThanReport = $activePublishedAt !== false && $activePublishedAt > $lastCheckAt;
        $waitingForNextCheck = $needsActiveVersion
            && ($statusKey === 'waiting_release' || $noReleaseMessage || $releaseIsNewerThanReport);
        $displayMessage = (string) ($client['message'] ?: '-');
        $displayAvailable = (string) $client['available_version'];
        if ($manualPending) {
            $supportsManualCheck = version_compare((string) $client['updater_version'], '1.2.0', '>=');
            $statusKey = $supportsManualCheck ? 'manual_check' : 'waiting_check';
            $displayAvailable = $activeVersion ?: $displayAvailable;
            $displayMessage = $supportsManualCheck
                ? 'Comando enviado; o serviço iniciará a consulta em até 15 segundos.'
                : 'Comando registrado. Será atendido após este computador receber o pacote 1.5.1.';
        } elseif ($waitingForNextCheck) {
            $statusKey = 'waiting_check';
            $displayAvailable = $activeVersion;
            $nextCheckAt = $lastCheckAt + $checkInterval;
            $publishedLabel = $clientAction === ReleasePolicy::ACTION_DOWNGRADE ? 'Rollback autorizado.' : 'Versão publicada.';
            $displayMessage = $nextCheckAt > time()
                ? $publishedLabel . ' Consulta automática prevista até ' . Html::convDateTime(date('Y-m-d H:i:s', $nextCheckAt)) . '.'
                : $publishedLabel . ' Aguardando o próximo contato automático do serviço.';
        } elseif ($statusKey === 'error' && $noReleaseMessage) {
            $statusKey = 'waiting_release';
            $displayMessage = 'Computador registrado; aguardando a primeira versão publicada.';
        }
        [$statusLabel, $statusClass] = $labels[$statusKey] ?? [$statusKey, 'bg-secondary'];
        $offline = $lastCheckAt < time() - 7200;
        $nextRegularCheckAt = $lastCheckAt + $checkInterval;
        $nextCheckLabel = $manualPending
            ? 'Após a verificação atual'
            : ($nextRegularCheckAt > time()
                ? Html::convDateTime(date('Y-m-d H:i:s', $nextRegularCheckAt))
                : 'A qualquer momento');
        echo '<tr>';
        echo '<td><strong>' . htmlescape((string) $client['hostname']) . '</strong>' . ($offline ? " <span class='badge bg-secondary'>Sem contato há mais de 2 h</span>" : '') . '</td>';
        echo '<td>' . htmlescape((string) $client['installed_version'])
            . ($clientAction === ReleasePolicy::ACTION_BLOCKED_DOWNGRADE
                ? " <span class='badge bg-secondary' title='Downgrade não autorizado para a versão publicada'>Acima da versão publicada</span>"
                : '')
            . ($clientAction === ReleasePolicy::ACTION_DOWNGRADE
                ? " <span class='badge bg-warning text-dark'>Rollback pendente</span>"
                : '')
            . '</td>';
        echo '<td>' . htmlescape((string) ($client['wallpaper_client_version'] ?: '-')) . '</td>';
        echo '<td>' . htmlescape((string) ($client['glpi_agent_version'] ?: '-')) . '</td>';
        echo '<td>' . htmlescape($displayAvailable ?: '-') . '</td>';
        echo "<td><span class='badge {$statusClass}'>" . htmlescape($statusLabel) . '</span></td>';
        echo '<td>' . Html::convDateTime($lastCheck) . '</td>';
        echo '<td>' . htmlescape($nextCheckLabel) . '</td>';
        echo '<td>' . htmlescape($displayMessage) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
echo "</div></div>";

echo "</div>";

echo <<<'HTML'
<script>
(() => {
    let remaining = 15;
    const target = document.getElementById('refresh-countdown');
    window.setInterval(() => {
        remaining -= 1;
        if (target) target.textContent = `Atualização da tela em ${remaining} s`;
        if (remaining <= 0) window.location.reload();
    }, 1000);
})();
</script>
HTML;

Html::footer();
