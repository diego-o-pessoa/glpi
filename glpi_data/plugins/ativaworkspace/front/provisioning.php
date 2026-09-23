<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\Overview;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningProfile;

include('../../../inc/includes.php');

Page::requireAccess('provisioning');

$jobsLimit = 100;
$filters   = Job::filtersFrom($_GET);

$canProvision = (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, CREATE);

$statuses = ['ACTIVE' => 'Ativos (fila, andamento, intervenção)'];
foreach (Job::LABELS as $key => [$label]) {
    $statuses[$key] = $label;
}

Page::render('provisioning', 'provisioning.html.twig', [
    'live'          => Page::liveConfig(Overview::payload($jobsLimit, 0, $filters), $jobsLimit, 0, $filters),
    'filters'       => $filters,
    'statuses'      => $statuses,
    'all_profiles'  => ProvisioningProfile::allChoices(),
    'can_provision' => $canProvision,
    'profiles'      => $canProvision ? ProvisioningProfile::activeChoices() : [],
    'upn_profiles'  => $canProvision ? ProvisioningProfile::entraProfileIds() : [],
    'profiles_url'  => Page::href('profiles'),
    'base_url'      => Page::href('provisioning'),
]);
