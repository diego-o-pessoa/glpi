<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\ProjectPage;
use GlpiPlugin\Ativacentral\TaskForm;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include '../../../inc/includes.php';

// Tela simplificada de tarefa do projeto: criar (?projects_id=N) e editar (?id=N).
Session::checkCentralAccess();

$taskId = (int) ($_GET['id'] ?? 0);
$task = null;
$project = new Project();
if ($taskId > 0) {
    $task = new ProjectTask();
    if (!$task->getFromDB($taskId) || !$project->getFromDB((int) $task->fields['projects_id'])) {
        throw new NotFoundHttpException();
    }
    if (!$task->can($taskId, READ)) {
        throw new AccessDeniedHttpException();
    }
    $canSave = $task->can($taskId, UPDATE);
} else {
    $projectId = (int) ($_GET['projects_id'] ?? 0);
    if ($projectId <= 0 || !$project->getFromDB($projectId)) {
        throw new NotFoundHttpException();
    }
    $canSave = ProjectTask::canCreate() && $project->can($projectId, READ);
    if (!$canSave) {
        throw new AccessDeniedHttpException();
    }
}

$error = '';
$old = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!$canSave) {
        throw new AccessDeniedHttpException();
    }
    if ($task !== null && isset($_POST['delete'])) {
        if (!$task->can($taskId, DELETE)) {
            throw new AccessDeniedHttpException();
        }
        $task->delete(['id' => $taskId]);
        Session::addMessageAfterRedirect('Tarefa excluída.');
        Html::redirect(ProjectPage::url((int) $project->getID(), 'tasks'));
    }
    try {
        TaskForm::save($project, $task, $_POST);
        Session::addMessageAfterRedirect($task === null ? 'Tarefa criada.' : 'Tarefa salva.');
        Html::redirect(ProjectPage::url((int) $project->getID(), 'tasks'));
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
        $old = $_POST + ['team' => []];
    }
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'projects';
Html::header($task === null ? 'Nova tarefa' : (string) $task->fields['name'], '', 'ac_project');
TemplateRenderer::getInstance()->display('@ativacentral/task_form.html.twig', TaskForm::viewData($project, $task, $old, $error) + ['can_save' => $canSave]);
Html::footer();
