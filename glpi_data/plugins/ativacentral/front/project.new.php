<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\ProjectForm;
use GlpiPlugin\Ativacentral\Shell;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Tela simplificada "Novo projeto" (Etapa 1 do Super-Admin).
Session::checkCentralAccess();
if (!Session::haveRight(Project::$rightname, CREATE)) {
    throw new AccessDeniedHttpException();
}

$error = '';
$old = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old = $_POST;
    try {
        $id = ProjectForm::create($_POST);
        Html::redirect(Shell::glpiUrl('/front/project.form.php?id=' . $id));
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    }
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'projects';
Html::header('Novo projeto', '', 'ac_project');
TemplateRenderer::getInstance()->display('@ativacentral/project_new.html.twig', ProjectForm::viewData($old, $error) + [
    'css_url' => Shell::assetUrl('css/app.css'),
]);
Html::footer();
