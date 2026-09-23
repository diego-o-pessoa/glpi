<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('overview');

Page::render('overview', 'overview.html.twig', [
    'counts'       => Job::overviewCounts(),
    'events'       => Event::recent(10),
    'provisioning' => Page::href('provisioning'),
    'logs'         => Page::href('logs'),
]);
