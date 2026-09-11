<?php

use Glpi\Application\View\TemplateRenderer;

include('../../../inc/includes.php');

Session::checkRight(PluginAtivaupdaterProfile::RIGHT_VIEW, READ);

Html::header(
    __('Ativa Updater', 'ativaupdater'),
    $_SERVER['PHP_SELF'],
    'plugins',
    'ativaupdater'
);

$release = new PluginAtivaupdaterRelease();
$activeRelease = $release->getActiveRelease();
$canManage = Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE);

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

echo "<div class='container-fluid mt-3'>";
echo "<h2>" . __('Ativa Updater Dashboard', 'ativaupdater') . "</h2>";

// Display Active Release
echo "<div class='card mb-4'>";
echo "<div class='card-header'><h3>" . __('Versão Atual', 'ativaupdater') . "</h3></div>";
echo "<div class='card-body'>";
if ($activeRelease) {
    echo "<p><strong>Versão:</strong> " . Html::clean($activeRelease['version']) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . Html::clean($activeRelease['original_filename']) . "</p>";
    echo "<p><strong>Tamanho:</strong> " . Html::formatSize($activeRelease['file_size']) . "</p>";
    echo "<p><strong>SHA-256:</strong> <code>" . Html::clean($activeRelease['sha256']) . "</code></p>";
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
    echo "<input type='text' class='form-control' id='version' name='version' required pattern='^\\d+\\.\\d+\\.\\d+.*$'>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label for='installer' class='form-label'>" . __('Instalador (.exe)', 'ativaupdater') . "</label>";
    echo "<input type='file' class='form-control' id='installer' name='installer' accept='.exe' required>";
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
        echo "<td>" . Html::clean($rel['version']) . "</td>";
        echo "<td>" . Html::clean($rel['original_filename']) . "</td>";
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

echo "</div>";

Html::footer();
