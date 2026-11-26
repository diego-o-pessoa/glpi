<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Page;
use GlpiPlugin\Ativacentral\Projects;
use GlpiPlugin\Ativacentral\Shell;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Nova tela "Projetos" (Etapa 1): lista em cartoes e indicadores no estilo da Visao geral.
Session::checkCentralAccess();

if (!Project::canView() && !ProjectTask::canView()) {
    throw new AccessDeniedHttpException();
}

$data = Projects::dashboard();
$data['urls'] = [
    'data'     => Shell::url('/front/projects.data.php'),
    'new'      => Session::haveRight(Project::$rightname, CREATE) ? Shell::url('/front/project.new.php') : '',
    'my_tasks' => ProjectTask::canView() ? Shell::glpiUrl(ProjectTask::getMyTasksURL(false)) : '',
];

Page::render('Projetos', 'ac_project', 'projects', 'projects', $data);
