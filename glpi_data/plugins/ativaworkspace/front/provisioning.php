<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('provisioning');

global $CFG_GLPI;

// "Novo provisionamento" so abre o aviso nesta etapa. Mesmo assim, o direito
// de provisionar e exigido no backend, nao so escondendo o botao.
$showNew = isset($_GET['new']);
if ($showNew) {
    PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, CREATE);
}

Page::render('provisioning', 'provisioning.html.twig', [
    'jobs'          => Job::listForPage(50),
    'can_provision' => (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, CREATE),
    'show_new'      => $showNew,
    'new_url'       => Page::href('provisioning', ['new' => 1]),
    'list_url'      => Page::href('provisioning'),
    'computer_url'  => $CFG_GLPI['root_doc'] . '/front/computer.form.php',
]);
