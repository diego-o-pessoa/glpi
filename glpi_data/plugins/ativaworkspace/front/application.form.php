<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Application;
use GlpiPlugin\Ativaworkspace\FormHandler;
use GlpiPlugin\Ativaworkspace\Page;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include('../../../inc/includes.php');

Page::requireAccess('applications');

$item = new Application();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    FormHandler::handlePost($item, ['name', 'comment', 'provider', 'desired_version', 'is_active'], 'applications');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    if (!$item->getFromDB($id)) {
        throw new NotFoundHttpException();
    }
} else {
    PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS, CREATE);
    $item->getEmpty();
    $item->fields['provider']  = 'manual';
    $item->fields['is_active'] = 1;
}

Page::render('applications', 'application_form.html.twig', [
    'item'       => $item->fields,
    'is_new'     => $id === 0,
    'can_edit'   => $id === 0 || Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS, UPDATE),
    'providers'  => Application::PROVIDERS,
    'action_url' => Page::href('application_form'),
    'list_url'   => Page::href('applications'),
]);
