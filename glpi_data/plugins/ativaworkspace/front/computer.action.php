<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\MachineAction;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

// Acoes sobre um computador (POST de formulario; CSRF do GLPI).
Page::requireAccess('computers');
// Desinstalar e destrutivo: exige o direito de gerenciar o provisionamento.
PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, UPDATE);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Html::redirect(Page::href('computers'));
}

$computerId = (int) ($_POST['computer'] ?? 0);
$action = (string) ($_POST['action'] ?? '');
$detailUrl = Page::href('computers', ['computer' => $computerId]);

if ($computerId <= 0) {
    Html::redirect(Page::href('computers'));
}

if ($action === 'uninstall') {
    $id = MachineAction::queueUninstall(
        $computerId,
        (string) ($_POST['target'] ?? ''),
        (string) ($_POST['scope'] ?? ''),
        (string) ($_POST['key'] ?? ''),
        (int) Session::getLoginUserID()
    );
    if ($id > 0) {
        Event::log(Event::LEVEL_INFO, 'computer', 'Desinstalação solicitada: ' . (string) ($_POST['target'] ?? ''), [
            'computador' => $computerId,
            'por'        => (int) Session::getLoginUserID(),
        ]);
        Session::addMessageAfterRedirect('Desinstalação agendada. O computador executará em silêncio no próximo ciclo.', false, INFO);
    } else {
        Session::addMessageAfterRedirect('Não foi possível agendar a desinstalação (dados inválidos).', false, ERROR);
    }
} else {
    Session::addMessageAfterRedirect('Ação desconhecida.', false, ERROR);
}

Html::redirect($detailUrl);
