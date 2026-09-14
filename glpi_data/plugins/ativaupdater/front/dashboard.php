<?php

use Glpi\Application\View\TemplateRenderer;

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
    echo "<p><strong>Tamanho:</strong> " . Html::formatSize($activeRelease['file_size']) . "</p>";
    echo "<p><strong>SHA-256:</strong> <code>" . htmlescape((string) $activeRelease['sha256']) . "</code></p>";
    echo "<p><strong>Publicado em:</strong> " . Html::convDateTime($activeRelease['created_at']) . "</p>";
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
    echo "<div class='form-text'>Envie o arquivo completo Ativa-Wallpaper-Client-Setup-X.Y.Z.exe gerado pelo builder.</div>";
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
    
    foreach ($releases as $rel) {
        $status = $rel['active'] ? "<span class='badge bg-success'>" . __('Atual', 'ativaupdater') . "</span>" : "<span class='badge bg-secondary'>" . __('Anterior', 'ativaupdater') . "</span>";
        echo "<tr>";
        echo "<td>" . htmlescape((string) $rel['version']) . "</td>";
        echo "<td>" . htmlescape((string) $rel['original_filename']) . "</td>";
        echo "<td>" . Html::formatSize($rel['file_size']) . "</td>";
        echo "<td>" . Html::convDateTime($rel['created_at']) . "</td>";
        echo "<td>" . $status . "</td>";
        if ($canManage) {
            echo "<td>";
            if (!$rel['active']) {
                echo "<form method='post' action='action.php' style='display:inline;'>";
                echo Html::hidden('action', ['value' => 'set_active']);
                echo Html::hidden('id', ['value' => $rel['id']]);
                echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
                echo "<button type='submit' class='btn btn-sm btn-outline-primary me-2' title='" . __('Definir como atual', 'ativaupdater') . "'><i class='fas fa-check'></i></button>";
                echo "</form>";
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
$totalClients = count($clients);
$updatedClients = 0;
$errorClients = 0;
foreach ($clients as $client) {
    if ($activeVersion !== '' && version_compare((string) $client['installed_version'], $activeVersion, '>=')) {
        $updatedClients++;
    }
    if ((string) $client['status'] === 'error') {
        $errorClients++;
    }
}
$progress = $totalClients > 0 ? (int) round(($updatedClients / $totalClients) * 100) : 0;

echo "<div class='card mb-4'>";
echo "<div class='card-header d-flex justify-content-between'><h3>Computadores</h3><span id='refresh-countdown' class='text-muted'>Atualização da tela em 15 s</span></div>";
echo "<div class='card-body'>";
echo "<div class='d-flex justify-content-between mb-1'><span>{$updatedClients} de {$totalClients} computador(es) na versão atual</span><strong>{$progress}%</strong></div>";
echo "<div class='progress mb-3' style='height: 20px'><div class='progress-bar bg-success' role='progressbar' style='width: {$progress}%' aria-valuenow='{$progress}' aria-valuemin='0' aria-valuemax='100'>{$progress}%</div></div>";
if ($errorClients > 0) {
    echo "<div class='alert alert-danger'>{$errorClients} computador(es) informaram erro. Consulte o motivo na tabela.</div>";
}
if ($totalClients === 0) {
    echo "<p class='text-muted mb-0'>Nenhum serviço se identificou ainda. Depois da instalação, a primeira consulta acontece imediatamente.</p>";
} else {
    echo "<div class='table-responsive'><table class='table table-striped align-middle'><thead><tr><th>Computador</th><th>Instalada</th><th>Disponível</th><th>Status</th><th>Última consulta</th><th>Detalhes</th></tr></thead><tbody>";
    $labels = [
        'checking' => ['Consultando', 'bg-info'],
        'current' => ['Atualizado', 'bg-success'],
        'downloading' => ['Baixando', 'bg-primary'],
        'installing' => ['Instalando', 'bg-warning text-dark'],
        'updated' => ['Atualizado', 'bg-success'],
        'error' => ['Erro', 'bg-danger'],
    ];
    foreach ($clients as $client) {
        $statusKey = (string) $client['status'];
        [$statusLabel, $statusClass] = $labels[$statusKey] ?? [$statusKey, 'bg-secondary'];
        $lastCheck = (string) $client['last_check'];
        $offline = strtotime($lastCheck) < time() - 7200;
        echo '<tr>';
        echo '<td><strong>' . htmlescape((string) $client['hostname']) . '</strong>' . ($offline ? " <span class='badge bg-secondary'>Sem contato há mais de 2 h</span>" : '') . '</td>';
        echo '<td>' . htmlescape((string) $client['installed_version']) . '</td>';
        echo '<td>' . htmlescape((string) $client['available_version']) . '</td>';
        echo "<td><span class='badge {$statusClass}'>" . htmlescape($statusLabel) . '</span></td>';
        echo '<td>' . Html::convDateTime($lastCheck) . '</td>';
        echo '<td>' . htmlescape((string) ($client['message'] ?: '-')) . '</td>';
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
