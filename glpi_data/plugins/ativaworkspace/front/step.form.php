<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Application;
use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProfileStep;
use GlpiPlugin\Ativaworkspace\ProvisioningProfile;
use GlpiPlugin\Ativaworkspace\StepType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include('../../../inc/includes.php');

Page::requireAccess('profiles');

// Toda mexida nas etapas exige poder EDITAR o perfil pai (direito + entidade).
$profileId = (int) ($_POST['profile'] ?? $_GET['profile'] ?? 0);
$profile = new ProvisioningProfile();
if ($profileId <= 0 || !$profile->getFromDB($profileId)) {
    throw new NotFoundHttpException();
}
if (!$profile->can($profileId, UPDATE)) {
    Event::log(Event::LEVEL_SECURITY, 'permission', 'Edição de etapas negada', ['perfil' => $profileId]);
    throw new AccessDeniedHttpException();
}

$profileUrl = Page::href('profile_form', ['id' => $profileId]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stepId = (int) ($_POST['id'] ?? 0);
    try {
        if (isset($_POST['move_up'])) {
            ProfileStep::move($profileId, (int) $_POST['move_up'], -1);
        } elseif (isset($_POST['move_down'])) {
            ProfileStep::move($profileId, (int) $_POST['move_down'], 1);
        } elseif (isset($_POST['delete'])) {
            ProfileStep::remove($profileId, (int) $_POST['delete']);
            Session::addMessageAfterRedirect('Etapa excluída.', false, INFO);
        } elseif (isset($_POST['save'])) {
            $input = ProfileStep::inputFromForm($_POST);
            if ($stepId > 0) {
                ProfileStep::change($profileId, $stepId, $input);
            } else {
                ProfileStep::append($profileId, $input);
            }
            Session::addMessageAfterRedirect('Etapa salva.', false, INFO);
        }
    } catch (RuntimeException $exception) {
        Session::addMessageAfterRedirect($exception->getMessage(), false, ERROR);
        // Erro ao salvar: volta ao formulario da etapa para corrigir.
        if (isset($_POST['save'])) {
            Html::redirect(Page::href('step_form', ['profile' => $profileId] + ($stepId > 0 ? ['id' => $stepId] : [])));
        }
    }
    Html::redirect($profileUrl);
}

// Formulario (nova etapa ou edicao).
$stepId = (int) ($_GET['id'] ?? 0);
$step = new ProfileStep();
if ($stepId > 0) {
    if (!$step->getFromDB($stepId) || (int) $step->fields[ProfileStep::PROFILE_FK] !== $profileId) {
        throw new NotFoundHttpException();
    }
    $values = $step->fields;
    $config = json_decode((string) ($values['config'] ?? ''), true);
    $values['config_values'] = is_array($config) ? $config : [];
} else {
    $values = [
        'name'              => '',
        'step_type'         => StepType::SOFTWARE,
        ProfileStep::APPLICATION_FK => 0,
        'config_values'     => [],
        'is_mandatory'      => 1,
        'continue_on_error' => 0,
        'timeout_minutes'   => 0,
        'max_attempts'      => 0,
    ];
}

$applications = Application::activeChoices();
// Etapa antiga apontando para aplicativo que foi desativado: mantem na lista.
$currentApp = (int) ($values[ProfileStep::APPLICATION_FK] ?? 0);
if ($currentApp > 0 && !isset($applications[$currentApp])) {
    $app = new Application();
    if ($app->getFromDB($currentApp)) {
        $applications[$currentApp] = $app->fields['name'] . ' (inativo)';
    }
}

Page::render('profiles', 'step_form.html.twig', [
    'profile'      => $profile->fields,
    'step'         => $values,
    'is_new'       => $stepId === 0,
    'types'        => StepType::all(),
    'applications' => $applications,
    'app_fk'       => ProfileStep::APPLICATION_FK,
    'action_url'   => Page::href('step_form'),
    'profile_url'  => $profileUrl,
    'catalog_url'  => Page::href('applications'),
    'timeout_max'  => ProfileStep::TIMEOUT_MAX_MINUTES,
    'attempts_max' => ProfileStep::ATTEMPTS_MAX,
]);
