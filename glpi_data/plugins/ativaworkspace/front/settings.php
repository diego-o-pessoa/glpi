<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Environment;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('settings');

Page::render('settings', 'settings.html.twig', [
    'versions' => Environment::versions(),
    'plugins'  => Environment::relatedPlugins(),
]);
