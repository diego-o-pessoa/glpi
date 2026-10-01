<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaramal\ApiDiagnostics;
use GlpiPlugin\Ativaramal\Controller\OAuthController;
use GlpiPlugin\Ativaramal\Logger;
use GlpiPlugin\Ativaramal\OAuthClient;
use GlpiPlugin\Ativaramal\RamalConfig;
use GlpiPlugin\Ativaramal\TokenManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Integracao com a TW Solutions. Ler exige o direito de configuracao do
// plugin; alterar (credenciais, conectar, renovar) exige UPDATE.
if (!Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, READ)) {
    throw new AccessDeniedHttpException();
}
$canEdit = (bool) Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, UPDATE);

global $CFG_GLPI;
$selfUrl = $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!$canEdit) {
        Logger::warning('Alteração da integração sem permissão');
        throw new AccessDeniedHttpException();
    }
    $action = (string) ($_POST['action'] ?? '');
    try {
        switch ($action) {
            case 'save':
                $values = [
                    'base_url'      => rtrim(trim((string) ($_POST['base_url'] ?? '')), '/'),
                    'authorize_url' => trim((string) ($_POST['authorize_url'] ?? '')),
                    'token_url'     => trim((string) ($_POST['token_url'] ?? '')),
                    'scope'         => mb_substr(trim((string) ($_POST['scope'] ?? '')), 0, 255),
                    'client_id'     => mb_substr(trim((string) ($_POST['client_id'] ?? '')), 0, 255),
                ];
                foreach (['base_url' => 'URL base da API', 'authorize_url' => 'URL de autorização', 'token_url' => 'URL do token'] as $key => $label) {
                    if ($values[$key] !== '' && !RamalConfig::isHttpsUrl($values[$key])) {
                        throw new RuntimeException($label . ' precisa ser um endereço HTTPS válido.');
                    }
                }
                // Segredos: campo vazio mantem o atual; a caixa "remover" apaga.
                foreach (['client_secret', 'access_token', 'refresh_token', 'api_token', 'api_key'] as $key) {
                    $typed = trim((string) ($_POST[$key] ?? ''));
                    if (!empty($_POST['clear_' . $key])) {
                        $values[$key] = '';
                    } elseif ($typed !== '') {
                        if (strlen($typed) > 8192 || preg_match('/\s/', $typed)) {
                            throw new RuntimeException('Valor inválido em ' . $key . '.');
                        }
                        $values[$key] = $typed;
                    }
                }
                // Token colado a mao: validade desconhecida (a renovacao fica com a TW).
                if (isset($values['access_token'])) {
                    $values['token_expires_at'] = '';
                    $values['token_obtained_at'] = $values['access_token'] !== '' ? (string) time() : '';
                }
                RamalConfig::set($values);
                Logger::info('Configuração da integração salva', [
                    'base'     => $values['base_url'],
                    'alterado' => implode(',', array_keys(array_intersect_key($values, array_flip(RamalConfig::SECRET_KEYS)))) ?: 'nenhum segredo',
                ]);
                Session::addMessageAfterRedirect('Configuração salva.', false, INFO);
                break;

            case 'connect':
                // state aleatorio, uso unico, guardado so na sessao (anti-CSRF do OAuth).
                $state = bin2hex(random_bytes(32));
                $url = OAuthClient::authorizationUrl($state);
                $_SESSION[OAuthController::STATE_SESSION_KEY] = ['value' => $state, 'created' => time()];
                Logger::info('Conexão OAuth iniciada (redirecionando para a TW Solutions)');
                Html::redirect($url);

            case 'refresh':
                TokenManager::refresh();
                Session::addMessageAfterRedirect('Token renovado.', false, INFO);
                break;

            case 'disconnect':
                RamalConfig::clearTokens();
                Logger::info('Tokens removidos (desconectado da TW Solutions)');
                Session::addMessageAfterRedirect('Desconectado: access token e refresh token apagados.', false, INFO);
                break;

            case 'regenerate_webhook':
                RamalConfig::regenerateWebhookToken();
                Logger::info('Token do webhook regenerado');
                Session::addMessageAfterRedirect('Novo token do webhook gerado. Atualize a URL no painel da TW Solutions.', false, INFO);
                break;

            default:
                Session::addMessageAfterRedirect('Ação inválida.', false, ERROR);
        }
    } catch (RuntimeException $exception) {
        Session::addMessageAfterRedirect($exception->getMessage(), false, ERROR);
    }
    Html::redirect($selfUrl);
}

RamalConfig::ensureDefaults();

// O token do webhook so aparece para quem pode editar (vai na URL cadastrada na TW).
$webhookToken = $canEdit ? RamalConfig::secret('webhook_token') : '';

Html::header('Ativa Ramal - Integração TW Solutions', '', 'ativaramal', 'config');
TemplateRenderer::getInstance()->display('@ativaramal/config.html.twig', [
    'status'        => RamalConfig::status(),
    'can_edit'      => $canEdit,
    'webhook_token' => $webhookToken,
    'action_url'    => $selfUrl,
    'diag_url'      => $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/diagnostic.php',
    'diag_groups'   => ApiDiagnostics::candidates(),
    'docs_url'      => ApiDiagnostics::DOCS_URL,
    'diag_note'     => ApiDiagnostics::MAPPING_NOTE,
    'apikey_schemes' => ApiDiagnostics::APIKEY_SCHEMES,
    'now'           => time(),
]);
Html::footer();
