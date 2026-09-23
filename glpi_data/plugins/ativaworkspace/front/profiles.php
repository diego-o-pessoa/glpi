<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningProfile;

include('../../../inc/includes.php');

Page::requireAccess('profiles');

global $DB;

$table = ProvisioningProfile::getTable();
$rows  = [];
foreach ($DB->request([
    'FROM'  => $table,
    // So as entidades que o usuario enxerga (perfis recursivos das entidades pai incluidos).
    'WHERE' => getEntitiesRestrictCriteria($table, '', '', true),
    'ORDER' => ['name ASC'],
]) as $row) {
    $row['entity_name'] = Dropdown::getDropdownName('glpi_entities', (int) $row['entities_id']);
    $rows[] = $row;
}

$counts = ProvisioningProfile::stepCounts(array_map(static fn ($row) => (int) $row['id'], $rows));
foreach ($rows as &$row) {
    $row['step_count'] = $counts[(int) $row['id']] ?? 0;
}
unset($row);

$right = PluginAtivaworkspaceProfile::RIGHT_PROFILES;
Page::render('profiles', 'profiles.html.twig', [
    'rows'       => $rows,
    'can_create' => (bool) Session::haveRight($right, CREATE),
    'can_update' => (bool) Session::haveRight($right, UPDATE),
    'can_purge'  => (bool) Session::haveRight($right, PURGE),
    'form_url'   => Page::href('profile_form'),
]);
