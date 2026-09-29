<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\JobStep;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningEngine;
use GlpiPlugin\Ativaworkspace\WorkspaceConfig;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include('../../../inc/includes.php');

// Acoes sobre um provisionamento (POST de formulario; CSRF validado pelo GLPI).
Page::requireAccess('provisioning');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Html::redirect(Page::href('provisioning'));
}

$jobId  = (int) ($_POST['job'] ?? 0);
$stepId = (int) ($_POST['step'] ?? 0);
$action = (string) ($_POST['action'] ?? '');

// Exclusao em lote: cada job e validado por entidade (can) antes de excluir.
if ($action === 'delete_bulk') {
    $ids = array_filter(array_map('intval', (array) ($_POST['jobs'] ?? [])));
    $deleted = 0;
    foreach ($ids as $id) {
        $one = new Job();
        if ($id > 0 && $one->can($id, UPDATE)) {
            ProvisioningEngine::deleteJob($id);
            $deleted++;
        }
    }
    Event::log(Event::LEVEL_INFO, 'provisioning', 'Provisionamentos excluídos em lote', ['quantidade' => $deleted]);
    Session::addMessageAfterRedirect($deleted . ' provisionamento(s) excluído(s).', false, INFO);
    Html::redirect(Page::href('provisioning'));
}

// Direito "Gerenciar" do Provisionar + entidade do job (can() carrega o job).
$job = new Job();
if ($jobId <= 0 || !$job->can($jobId, UPDATE)) {
    Event::log(Event::LEVEL_SECURITY, 'permission', 'Ação negada em provisionamento', ['job' => $jobId, 'acao' => $action], $jobId);
    throw new AccessDeniedHttpException();
}

try {
    switch ($action) {
        case 'cancel':
            ProvisioningEngine::cancel($jobId);
            Session::addMessageAfterRedirect('Provisionamento cancelado.', false, INFO);
            break;

        case 'restart':
            ProvisioningEngine::restart($jobId);
            Session::addMessageAfterRedirect('Provisionamento recomeçado.', false, INFO);
            break;

        case 'delete':
            ProvisioningEngine::deleteJob($jobId);
            Event::log(Event::LEVEL_INFO, 'provisioning', 'Provisionamento excluído', ['por' => (int) Session::getLoginUserID()]);
            Session::addMessageAfterRedirect('Provisionamento excluído.', false, INFO);
            Html::redirect(Page::href('provisioning'));
            break;

        case 'retry':
            ProvisioningEngine::retry($jobId, $stepId);
            Session::addMessageAfterRedirect('Etapa colocada para nova tentativa.', false, INFO);
            break;

        case 'confirm':
            ProvisioningEngine::confirmIntervention($jobId, $stepId);
            Session::addMessageAfterRedirect('Intervenção confirmada.', false, INFO);
            break;

        case 'simulate':
            // Ferramenta de desenvolvimento: modo ligado + direitos de gerencia e configuracao.
            if (!WorkspaceConfig::canSimulate()) {
                Event::log(Event::LEVEL_SECURITY, 'permission', 'Simulação negada', ['job' => $jobId], $jobId);
                throw new AccessDeniedHttpException();
            }
            $result = (string) ($_POST['result'] ?? '');
            ProvisioningEngine::recordResult(
                $jobId,
                $stepId,
                $result,
                $result === JobStep::FAILED ? 'Falha simulada pelo modo de desenvolvimento.' : '[simulação]',
                ['simulacao' => true, 'usuario' => (int) Session::getLoginUserID()]
            );
            Session::addMessageAfterRedirect('[Simulação] Etapa marcada como ' . $result . '.', false, INFO);
            break;

        default:
            Session::addMessageAfterRedirect('Ação desconhecida.', false, ERROR);
    }
} catch (RuntimeException $exception) {
    Session::addMessageAfterRedirect($exception->getMessage(), false, ERROR);
}

Html::redirect(Page::href('job', ['id' => $jobId]));
