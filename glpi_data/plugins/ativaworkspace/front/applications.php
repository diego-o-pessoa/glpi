<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Application;
use GlpiPlugin\Ativaworkspace\InstallerStorage;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('applications');

global $DB;

$rows = [];
foreach ($DB->request([
    'FROM'  => Application::getTable(),
    'ORDER' => ['name ASC'],
]) as $row) {
    $row['icon_class']     = Application::iconFor($row);
    $row['category_label'] = Application::CATEGORIES[(string) $row['category']] ?? '—';
    $row['type_label']     = InstallerStorage::TYPES[(string) $row['installer_type']]['label'] ?? (string) $row['installer_type'];
    $row['size_label']     = (int) $row['file_size'] > 0 ? Toolbox::getSize((int) $row['file_size']) : '';
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
