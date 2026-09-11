<?php

use GlpiPlugin\Ativaupdater\ConfigService;

include('../../../inc/includes.php');

Session::checkRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, READ);

if (isset($_POST['update'])) {
    Session::checkRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, UPDATE);
    Session::checkCSRF($_POST);
    
    ConfigService::set([
        'api_enabled' => $_POST['api_enabled'] ?? '0'
    ]);
    
    Session::addMessageAfterRedirect('Configurações atualizadas.', true, INFO);
    Html::redirect($_SERVER['PHP_SELF']);
}

if (isset($_POST['generate_token'])) {
    Session::checkRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, UPDATE);
    Session::checkCSRF($_POST);
    
    ConfigService::set([
        'api_token' => ConfigService::generateToken()
    ]);
    
    Session::addMessageAfterRedirect('Novo token gerado. Atualize os serviços dependentes.', true, INFO);
    Html::redirect($_SERVER['PHP_SELF']);
}

Html::header(
    __('Ativa Updater', 'ativaupdater'),
    $_SERVER['PHP_SELF'],
    'plugins',
    'ativaupdater'
);

$api_enabled = ConfigService::get('api_enabled', '0');
$api_token = ConfigService::get('api_token', '');
$hidden_token = substr($api_token, 0, 4) . str_repeat('*', max(0, strlen($api_token) - 8)) . substr($api_token, -4);

echo "<div class='container-fluid mt-3'>";
echo "<h2>" . __('Configurações Ativa Updater', 'ativaupdater') . "</h2>";

echo "<div class='card mb-4'>";
echo "<div class='card-header'><h3>" . __('API', 'ativaupdater') . "</h3></div>";
echo "<div class='card-body'>";

echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<div class='mb-3'>";
echo "<label for='api_enabled' class='form-label'>" . __('API Habilitada', 'ativaupdater') . "</label>";
echo "<select name='api_enabled' id='api_enabled' class='form-select w-25'>";
echo "<option value='1' ".($api_enabled == '1' ? 'selected' : '').">" . __('Sim') . "</option>";
echo "<option value='0' ".($api_enabled == '0' ? 'selected' : '').">" . __('Não') . "</option>";
echo "</select>";
echo "</div>";
echo "<button type='submit' name='update' class='btn btn-primary'>" . __('Salvar', 'ativaupdater') . "</button>";
echo "</form>";

echo "<hr>";

echo "<h4>" . __('Token da API', 'ativaupdater') . "</h4>";
echo "<p><strong>Token atual:</strong> <code>" . $hidden_token . "</code></p>";

echo "<form method='post' action='".$_SERVER['PHP_SELF']."' onsubmit='return confirm(\"" . __('Tem certeza que deseja rotacionar o token? Os serviços atuais perderão o acesso.', 'ativaupdater') . "\");'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<button type='submit' name='generate_token' class='btn btn-warning'>" . __('Gerar Novo Token', 'ativaupdater') . "</button>";
echo "</form>";

echo "</div></div>";
echo "</div>";

Html::footer();
