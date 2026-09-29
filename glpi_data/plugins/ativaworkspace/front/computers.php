<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Inventory;
use GlpiPlugin\Ativaworkspace\MachineAction;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('computers');

$computerId = (int) ($_GET['computer'] ?? 0);

if ($computerId > 0) {
    $inventory = Inventory::forComputer($computerId);
    if ($inventory === null) {
        Html::redirect(Page::href('computers'));
        return;
    }
    $computer = new Computer();
    $name = $computer->getFromDB($computerId) ? (string) $computer->fields['name'] : (string) $inventory['hostname'];

    Page::render('computers', 'computer_detail.html.twig', [
        'inv'        => $inventory,
        'name'       => $name,
        'back_url'   => Page::href('computers'),
        'can_manage' => (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, UPDATE),
        'actions'    => MachineAction::forComputer($computerId),
        'action_url' => Page::href('computer_action'),
        'csrf'       => Session::getNewCSRFToken(),
        'computers_id' => $computerId,
    ]);
    return;
}

Page::render('computers', 'computers.html.twig', [
    'machines' => Inventory::all(),
    'base_url' => Page::href('computers'),
]);
