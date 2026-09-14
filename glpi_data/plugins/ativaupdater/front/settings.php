<?php

declare(strict_types=1);

use GlpiPlugin\Ativaupdater\ConfigService;

include('../../../inc/includes.php');

Session::checkRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, READ);

if (isset($_POST['download_service_config'])) {
    Session::checkRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, UPDATE);
    $payload = [
        'api_url'                => rtrim((string) ConfigService::get('api_base_url', ''), '/'),
        'api_token'              => (string) ConfigService::get('api_token', ''),
        'check_interval_seconds' => max(300, min(86400, ConfigService::getInt('check_interval_seconds', 3600))),
        'verify_tls'             => true,
    ];
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="ativaupdater-service-config.json"');
    header('Cache-Control: no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

if (isset($_POST['update'])) {
    Session::checkRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, UPDATE);
    $apiUrl = rtrim(trim((string) ($_POST['api_base_url'] ?? '')), '/');
    $interval = filter_var($_POST['check_interval_seconds'] ?? null, FILTER_VALIDATE_INT);
    $maxUpload = filter_var($_POST['max_upload_mb'] ?? null, FILTER_VALIDATE_INT);
    if (filter_var($apiUrl, FILTER_VALIDATE_URL) === false
        || !str_starts_with($apiUrl, 'https://')
        || !str_ends_with($apiUrl, '/plugins/ativaupdater/api/v1')
        || $interval === false || $interval < 300 || $interval > 86400
        || $maxUpload === false || $maxUpload < 50 || $maxUpload > 2048
    ) {
        Session::addMessageAfterRedirect('Configuração inválida. Use HTTPS, intervalo entre 300 e 86400 segundos e limite entre 50 e 2048 MB.', false, ERROR);
        Html::redirect($_SERVER['PHP_SELF']);
    }
    ConfigService::set([
        'api_enabled'            => isset($_POST['api_enabled']) ? '1' : '0',
        'api_base_url'           => $apiUrl,
        'check_interval_seconds' => (string) $interval,
        'max_upload_mb'          => (string) $maxUpload,
    ]);
    Session::addMessageAfterRedirect('Configurações atualizadas.', true, INFO);
    Html::redirect($_SERVER['PHP_SELF']);
}

if (isset($_POST['generate_token'])) {
    Session::checkRight(PluginAtivaupdaterProfile::RIGHT_CONFIG, UPDATE);
    ConfigService::set(['api_token' => ConfigService::generateToken()]);
    Session::addMessageAfterRedirect('Novo token gerado. Gere outro arquivo de configuração e atualize os instaladores.', true, INFO);
    Html::redirect($_SERVER['PHP_SELF']);
}

Html::header(__('Ativa Updater', 'ativaupdater'), $_SERVER['PHP_SELF'], 'plugins', 'ativaupdater');

$enabled = ConfigService::getBool('api_enabled');
$apiUrl = (string) ConfigService::get('api_base_url', '');
$interval = ConfigService::getInt('check_interval_seconds', 3600);
$maxUpload = ConfigService::getInt('max_upload_mb', 500);
$token = (string) ConfigService::get('api_token', '');
$maskedToken = strlen($token) >= 8
    ? substr($token, 0, 4) . str_repeat('*', strlen($token) - 8) . substr($token, -4)
    : 'não configurado';
$self = Html::clean($_SERVER['PHP_SELF']);

echo "<div class='container-fluid mt-3'>";
echo "<h2>Configurações do Ativa Updater</h2>";
echo "<div class='alert alert-info'>O serviço Windows consulta esta API no intervalo configurado. O padrão é 3600 segundos (1 hora).</div>";
echo "<div class='card mb-4'><div class='card-header'><h3>API e serviço</h3></div><div class='card-body'>";
echo "<form method='post' action='{$self}'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<div class='form-check form-switch mb-3'><input class='form-check-input' type='checkbox' name='api_enabled' id='api_enabled' " . ($enabled ? 'checked' : '') . "><label class='form-check-label' for='api_enabled'>API habilitada</label></div>";
echo "<div class='mb-3'><label class='form-label' for='api_base_url'>URL pública da API</label><input class='form-control' type='url' name='api_base_url' id='api_base_url' value='" . Html::clean($apiUrl) . "' required></div>";
echo "<div class='row'><div class='col-md-6 mb-3'><label class='form-label' for='check_interval_seconds'>Intervalo de consulta (segundos)</label><input class='form-control' type='number' min='300' max='86400' name='check_interval_seconds' id='check_interval_seconds' value='{$interval}' required><div class='form-text'>3600 segundos = 1 hora.</div></div>";
echo "<div class='col-md-6 mb-3'><label class='form-label' for='max_upload_mb'>Tamanho máximo do instalador (MB)</label><input class='form-control' type='number' min='50' max='2048' name='max_upload_mb' id='max_upload_mb' value='{$maxUpload}' required></div></div>";
echo "<button type='submit' name='update' class='btn btn-primary'>Salvar</button></form>";

echo "<hr><h4>Configuração protegida do serviço</h4>";
echo "<p>Token atual: <code>" . Html::clean($maskedToken) . "</code></p>";
echo "<p class='text-muted'>Baixe o JSON e entregue-o ao gerador do instalador. Ele contém o token da API e não deve ser enviado por canais públicos.</p>";
echo "<div class='d-flex gap-2'>";
echo "<form method='post' action='{$self}'>" . Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]) . "<button type='submit' name='download_service_config' class='btn btn-success'><i class='fas fa-download'></i> Baixar configuração do serviço</button></form>";
echo "<form method='post' action='{$self}' onsubmit='return confirm(\"O token atual deixará de funcionar em todas as máquinas. Continuar?\");'>" . Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]) . "<button type='submit' name='generate_token' class='btn btn-outline-warning'>Gerar novo token</button></form>";
echo "</div></div></div></div>";

Html::footer();
