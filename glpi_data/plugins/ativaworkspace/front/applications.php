<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Application;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('applications');

global $DB;

$rows = [];
foreach ($DB->request([
    'FROM'  => Application::getTable(),
    'ORDER' => ['name ASC'],
]) as $row) {
    $row['provider_label'] = Application::providerLabel((string) $row['provider']);
    $rows[] = $row;
}

$right = PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS;
Page::render('applications', 'applications.html.twig', [
    'rows'       => $rows,
    'can_create' => (bool) Session::haveRight($right, CREATE),
    'can_update' => (bool) Session::haveRight($right, UPDATE),
    'can_purge'  => (bool) Session::haveRight($right, PURGE),
    'form_url'   => Page::href('application_form'),
]);
