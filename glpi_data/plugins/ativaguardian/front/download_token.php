<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\ConfigService;

include('../../../inc/includes.php');

// POST obrigatorio por dois motivos: o CheckCsrfListener do GLPI so valida CSRF
// em metodos com corpo (um GET passaria sem token nenhum), e um segredo nao deve
// sair por uma URL que possa ser linkada ou ficar no historico do navegador.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// Nao chamamos Session::checkCSRF aqui: o CheckCsrfListener do GLPI ja validou
// este POST e CONSUMIU o token (validateCSRF faz unset quando preserve_token e
// falso). Uma segunda checagem nao encontraria mais o token e recusaria o
// download com "A acao que voce requisitou nao e permitida".
if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_CONFIG, UPDATE)) {
    Session::checkRight('config', UPDATE);
}

global $CFG_GLPI;
$apiToken = (string) ConfigService::get('api_token', '');
if ($apiToken === '') {
    throw new RuntimeException('O token do Ativa Guardian ainda nao foi gerado.');
}

$payload = [
    'api_url'                    => rtrim((string) $CFG_GLPI['url_base'], '/') . '/plugins/ativaguardian/api/v1',
    'api_token'                  => $apiToken,
    'verify_tls'                 => true,
    'heartbeat_interval_seconds' => 30,
];

// Coordenadas da API do Ativa Updater, de onde o reparo baixa o pacote oficial.
// Vão no config do Guardian de propósito: o serviço lia isso do service-config.json
// do Updater, mas essa pasta some exatamente no caso em que o reparo é necessário
// (executável removido pelo antivírus, pasta apagada), e aí o reparo falhava.
$updaterConfig = Config::getConfigurationValues('plugin:ativaupdater');
$updaterUrl = rtrim((string) ($updaterConfig['api_base_url'] ?? ''), '/');
$updaterToken = (string) ($updaterConfig['api_token'] ?? '');
if ($updaterUrl !== '' && preg_match('/^[a-fA-F0-9]{64}$/D', $updaterToken)) {
    $payload['updater_api_url'] = $updaterUrl;
    $payload['updater_api_token'] = $updaterToken;
}

$content = json_encode(
    $payload,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);

// Fecha a sessao e limpa qualquer buffer antes de escrever: um aviso do PHP
// impresso no meio do corpo corromperia o arquivo baixado.
session_write_close();
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="ativaguardian-config.json"');
header('Content-Length: ' . strlen($content));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
echo $content;
exit;
