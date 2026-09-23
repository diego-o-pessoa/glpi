<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Environment;
use GlpiPlugin\Ativaworkspace\Overview;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningProfile;

include('../../../inc/includes.php');

Page::requireAccess('overview');

$jobsLimit   = 8;
$eventsLimit = 8;

$plugins      = Environment::relatedPlugins();
$canProvision = (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, CREATE);

Page::render('overview', 'overview.html.twig', [
    'live'          => Page::liveConfig(Overview::payload($jobsLimit, $eventsLimit), $jobsLimit, $eventsLimit),
    'plugins'       => $plugins,
    'all_active'    => Environment::allRelatedActive($plugins),
    'can_provision' => $canProvision,
    'profiles'      => $canProvision ? ProvisioningProfile::activeChoices() : [],
    'upn_profiles'  => $canProvision ? ProvisioningProfile::entraProfileIds() : [],
    'provisioning'  => Page::href('provisioning'),
    'logs'          => Page::href('logs'),
    'profiles_url'  => Page::href('profiles'),
]);
