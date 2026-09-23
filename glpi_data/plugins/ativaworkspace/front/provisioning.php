<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Overview;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningProfile;

include('../../../inc/includes.php');

Page::requireAccess('provisioning');

$jobsLimit = 50;

$canProvision = (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, CREATE);

Page::render('provisioning', 'provisioning.html.twig', [
    'live'          => Page::liveConfig(Overview::payload($jobsLimit, 0), $jobsLimit, 0),
    'can_provision' => $canProvision,
    'profiles'      => $canProvision ? ProvisioningProfile::activeChoices() : [],
    'profiles_url'  => Page::href('profiles'),
]);
