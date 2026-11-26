<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\ProjectPage;
use GlpiPlugin\Ativacentral\Shell;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include '../../../inc/includes.php';

// Tela do projeto no estilo do Ativa Central (Etapa 2 do Super-Admin).
Session::checkCentralAccess();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$project = new Project();
if ($id <= 0 || !$project->getFromDB($id)) {
    throw new NotFoundHttpException();
}
if (!$project->can($id, READ)) {
    throw new AccessDeniedHttpException();
}

// Andamento e datas reais atualizados (calculo do plugin Painel, se estiver ativo).
$progressFile = GLPI_ROOT . '/plugins/painel/inc/progress.php';
if (!function_exists('painel_project_recalc') && is_file($progressFile) && (new Plugin())->isActivated('painel')) {
    require_once $progressFile;
}
if (function_exists('painel_project_recalc')) {
    painel_project_recalc($id);
    $project->getFromDB($id);
}

$tab = (string) ($_GET['tab'] ?? 'main');
if (!isset(ProjectPage::TABS[$tab])) {
    $tab = 'main';
}

$message = '';
$error = '';
$old = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (isset($_POST['delete'])) {
        if (!$project->can($id, DELETE)) {
            throw new AccessDeniedHttpException();
        }
        $project->delete(['id' => $id]);
        Html::redirect(Shell::url('/front/projects.php'));
    }
    if (!$project->can($id, UPDATE)) {
        throw new AccessDeniedHttpException();
    }
    try {
        ProjectPage::save($project, $_POST);
        Session::addMessageAfterRedirect('Projeto salvo.');
        Html::redirect(ProjectPage::url($id));
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
        $old = $_POST;
    }
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'projects';
Html::header((string) $project->fields['name'], '', 'ac_project');
TemplateRenderer::getInstance()->display('@ativacentral/project_view.html.twig', ProjectPage::viewData($project, $tab, $message, $error, $old));
Html::footer();
