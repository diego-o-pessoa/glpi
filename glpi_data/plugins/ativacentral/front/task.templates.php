<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\Shell;
use GlpiPlugin\Ativacentral\TaskTemplates;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// "Modelos de tarefa": lista e edicao dos modelos, so para o T.I.
Session::checkCentralAccess();
if (!TaskTemplates::canManage()) {
    throw new AccessDeniedHttpException();
}

$error = '';
$old = [];
$editId = (int) ($_GET['edit'] ?? 0);
$isNew = isset($_GET['new']);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postId = (int) ($_POST['id'] ?? 0);
    if (isset($_POST['delete'])) {
        TaskTemplates::delete($postId);
        Session::addMessageAfterRedirect('Modelo excluído.');
        Html::redirect(TaskTemplates::url());
    }
    try {
        TaskTemplates::save($postId, $_POST);
        Session::addMessageAfterRedirect('Modelo salvo.');
        Html::redirect(TaskTemplates::url());
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
        $old = $_POST;
        $editId = $postId;
        $isNew = $postId === 0;
    }
}

$templates = TaskTemplates::all();
$current = null;
foreach ($templates as $tpl) {
    if ($tpl['id'] === $editId) {
        $current = $tpl;
    }
}
$editing = $current !== null || $isNew;
$typeId = (int) ($old['projecttasktypes_id'] ?? ($current['type_id'] ?? 0));

$GLOBALS['ATIVACENTRAL_SECTION'] = 'projects';
Html::header('Modelos de tarefa', '', 'ac_project');
TemplateRenderer::getInstance()->display('@ativacentral/task_templates.html.twig', [
    'css_url'   => Shell::assetUrl('css/app.css'),
    'url'       => TaskTemplates::url(),
    'back'      => Shell::url('/front/projects.php'),
    'templates' => $templates,
    'editing'   => $editing,
    'error'     => $error,
    'csrf'      => Session::getNewCSRFToken(),
    'tpl'       => [
        'id'       => $current['id'] ?? 0,
        'name'     => (string) ($old['name'] ?? ($current['name'] ?? '')),
        'is_phase' => isset($old['tpl_kind']) ? $old['tpl_kind'] === 'phase' : (bool) ($current['is_phase'] ?? false),
    ],
    'type_html' => $editing ? ProjectTaskType::dropdown([
        'name' => 'projecttasktypes_id', 'value' => $typeId, 'display' => false, 'width' => '100%',
    ]) : '',
    'desc_html' => $editing ? Html::textarea([
        'name' => 'description', 'value' => (string) ($old['description'] ?? ($current['description'] ?? '')),
        'enable_richtext' => true, 'enable_images' => false, 'rows' => 10, 'display' => false,
    ]) : '',
]);
Html::footer();
