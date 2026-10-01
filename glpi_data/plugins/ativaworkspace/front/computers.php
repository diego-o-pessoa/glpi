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

    // Desinstalacao concluida x inventario: se o snapshot e anterior a ela, o
    // programa ja saiu (esconde). Se um inventario mais novo ainda o lista, ele
    // esta mesmo instalado (reinstalado ou outra entrada): mostra o botao de novo.
    $actions = MachineAction::forComputer($computerId);
    $programs = [];
    foreach ($inventory['programs'] as $prog) {
        $key = (string) ($prog['key'] ?? '');
        $action = $actions[$key] ?? null;
        if ($action !== null && $action['status'] === MachineAction::STATUS_DONE) {
            if ($action['scope'] === (string) ($prog['scope'] ?? '') && $inventory['reported_at'] <= $action['date_mod']) {
                continue;
            }
            unset($actions[$key]);
        }
        $programs[] = $prog;
    }
    $inventory['programs'] = $programs;

    Page::render('computers', 'computer_detail.html.twig', [
        'inv'        => $inventory,
        'name'       => $name,
        'back_url'   => Page::href('computers'),
        'can_manage' => (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, UPDATE),
        'actions'    => $actions,
        // Acoes remotas: botoes, historico e processos que nao podem ser encerrados.
        'remote_actions'      => MachineAction::REMOTE_ACTIONS,
        'remote_history'      => MachineAction::remoteHistory($computerId),
        'protected_processes' => MachineAction::PROTECTED_PROCESSES,
        // O servico envia inventario a cada minuto: sem relato ha 3 min, esta offline.
        'online'              => $inventory['reported_at'] !== ''
            && strtotime((string) $inventory['reported_at']) >= strtotime((string) ($_SESSION['glpi_currenttime'] ?? 'now')) - 180,
        'action_url' => Page::href('computer_action'),
        'data_url'   => Page::href('computer_data'),
        'csrf'       => Session::getNewCSRFToken(),
        'computers_id' => $computerId,
    ]);
    return;
}

Page::render('computers', 'computers.html.twig', [
    'machines' => Inventory::all(),
    'base_url' => Page::href('computers'),
]);
