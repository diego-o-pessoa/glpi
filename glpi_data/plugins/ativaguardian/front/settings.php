<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\ConfigService;
use GlpiPlugin\Ativaguardian\PageLayout;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_CONFIG, UPDATE)) {
    Session::checkRight('config', UPDATE);
}

$self = $_SERVER['PHP_SELF'];

// Sem Session::checkCSRF aqui: o CheckCsrfListener do GLPI ja validou este POST
// e consumiu o token (validateCSRF faz unset). Checar de novo recusaria o envio
// com "A acao que voce requisitou nao e permitida". Os formularios continuam
// enviando _glpi_csrf_token, que e o que o listener exige.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_maintenance_password'])) {
        $password = (string) ($_POST['maintenance_password'] ?? '');
        $confirmation = (string) ($_POST['maintenance_confirmation'] ?? '');
        try {
            if (!hash_equals($password, $confirmation)) {
                throw new InvalidArgumentException('As senhas nao coincidem.');
            }
            ConfigService::set(['maintenance_password_hash' => ConfigService::passwordVerifier($password)]);
            Session::addMessageAfterRedirect('Senha salva. Baixe a configuracao do Guardian e gere um novo instalador unificado para aplicar nas maquinas.', false, INFO);
        } catch (InvalidArgumentException $exception) {
            Session::addMessageAfterRedirect($exception->getMessage(), false, ERROR);
        }
        unset($password, $confirmation, $_POST['maintenance_password'], $_POST['maintenance_confirmation']);
    } elseif (isset($_POST['generate_token'])) {
        ConfigService::set(['api_token' => ConfigService::generateToken()]);
        Session::addMessageAfterRedirect('Novo token gerado. Atualize o serviço nas máquinas.', false, INFO);
    } elseif (isset($_POST['update'])) {
        $offline = filter_var($_POST['offline_after_seconds'] ?? null, FILTER_VALIDATE_INT);
        $enabled = isset($_POST['api_enabled']) ? '1' : '0';
        if ($offline !== false && $offline >= 300 && $offline <= 604800) {
            ConfigService::set([
                'offline_after_seconds' => (string) $offline,
                'api_enabled'           => $enabled,
            ]);
            Session::addMessageAfterRedirect('Configurações salvas.', false, INFO);
        } else {
            Session::addMessageAfterRedirect('Valor inválido. O limite de offline deve ficar entre 300 e 604800 segundos.', false, ERROR);
        }
    }

    Html::redirect($self);
}

Html::header(__('Ativa Guardian', 'ativaguardian'), $self, 'config', 'plugins');

$token = (string) ConfigService::get('api_token', '');
$maskedToken = $token !== '' ? substr($token, 0, 6) . str_repeat('•', 20) . substr($token, -4) : '(vazio)';
$offline = ConfigService::getInt('offline_after_seconds', 7200);
$apiEnabled = ConfigService::getBool('api_enabled', true);
$checked = $apiEnabled ? ' checked' : '';

global $CFG_GLPI;
$apiBase = $CFG_GLPI['url_base'] . '/plugins/ativaguardian/api/v1';

echo PageLayout::header('overview');

echo "<article class='ag-card'><header class='ag-card-header'><h2 class='ag-card-title'><i class='fas fa-cog'></i>Configurações da API</h2></header><div class='ag-card-body' style='padding:16px'>";
echo "<form method='post' action='" . htmlescape($self) . "'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<div class='mb-3'><label class='form-check'>"
    . "<input type='checkbox' class='form-check-input' name='api_enabled'{$checked}> "
    . "<span class='form-check-label'>API de heartbeat habilitada</span></label></div>";
echo "<div class='mb-3' style='max-width:420px'><label class='form-label' for='offline_after_seconds'>Considerar offline após (segundos)</label>"
    . "<input class='form-control' type='number' min='300' max='604800' name='offline_after_seconds' id='offline_after_seconds' value='{$offline}' required>"
    . "<div class='form-text'>7200 segundos = 2 horas sem heartbeat.</div></div>";
echo "<button type='submit' name='update' class='btn btn-primary'><i class='fas fa-save me-2'></i>Salvar</button>";
echo '</form></div></article>';

echo "<article class='ag-card'><header class='ag-card-header'><h2 class='ag-card-title'><i class='fas fa-key'></i>Token da API</h2></header><div class='ag-card-body' style='padding:16px'>";
echo "<p>Endpoint base: <code>" . htmlescape($apiBase) . "</code></p>";
echo "<p>Token atual: <code>" . htmlescape($maskedToken) . "</code></p>";
echo "<p class='text-muted'>O serviço Windows autentica com <code>Authorization: Bearer &lt;token&gt;</code>. O token nunca é exibido por completo nesta tela.</p>";
echo "<div class='d-flex gap-2 flex-wrap'>";
echo "<form method='post' action='download_token.php'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<button type='submit' class='btn btn-success'><i class='fas fa-download me-2'></i>Baixar configuração do serviço</button>";
echo '</form>';
echo "<form method='post' action='" . htmlescape($self) . "' onsubmit='return confirm(\"O token atual deixará de funcionar em todas as máquinas. Continuar?\");'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<button type='submit' name='generate_token' class='btn btn-outline-warning'><i class='fas fa-rotate me-2'></i>Gerar novo token</button>";
echo '</form></div>';
echo "<hr><p class='small text-muted mb-0'><i class='fas fa-lock me-1'></i>O arquivo baixado é o <code>config.json</code> pronto do serviço (use com <code>AtivaGuardian.exe --configure</code>). Ele contém o token em texto e não deve ser enviado por canais públicos.</p>";
echo '</div></article>';

$hasMaintenancePassword = (string) ConfigService::get('maintenance_password_hash', '') !== '';
echo "<article class='ag-card'><header class='ag-card-header'><h2 class='ag-card-title'>Senha de manutencao Ativa</h2></header><div class='ag-card-body' style='padding:16px'>";
echo '<p>' . ($hasMaintenancePassword ? 'Senha configurada.' : 'Protecao por senha ainda nao configurada.') . '</p>';
echo '<p>A senha libera uma janela de 15 minutos pelo atalho <strong>Manutencao Ativa</strong> no Windows. Atualizacoes automaticas executadas pelo sistema continuam autorizadas.</p>';
echo '<p>Usuarios comuns recebem acesso negado ao parar servicos ou remover executaveis. O Windows nao exibe nossa senha no Gerenciador de Tarefas; utilize o atalho para manutencao. Administradores locais podem alterar permissoes do sistema.</p>';
echo "<form method='post' action='" . htmlescape($self) . "'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo "<label class='form-label' for='maintenance_password'>Nova senha (minimo 12 caracteres)</label><input id='maintenance_password' name='maintenance_password' class='form-control mb-3' type='password' autocomplete='new-password' minlength='12' maxlength='128' required>";
echo "<label class='form-label' for='maintenance_confirmation'>Confirmar senha</label><input id='maintenance_confirmation' name='maintenance_confirmation' class='form-control mb-3' type='password' autocomplete='new-password' minlength='12' maxlength='128' required>";
echo "<button class='btn btn-primary' type='submit' name='set_maintenance_password'>Salvar senha</button>";
echo '<p class="form-text">Depois de salvar ou trocar, baixe novamente a configuracao do servico e gere o pacote unificado. Maquinas antigas usam a senha anterior ate receberem o pacote. O arquivo inclui somente o verificador da senha, nunca a senha original.</p></form></div></article>';
echo PageLayout::footer();

Html::footer();
