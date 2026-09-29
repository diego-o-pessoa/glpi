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

$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

if ($action === 'uninstall') {
    // Um alvo (formulario simples) ou varios (lote): arrays paralelos.
    if (isset($_POST['keys']) && is_array($_POST['keys'])) {
        $targets = (array) ($_POST['targets'] ?? []);
        $scopes  = (array) ($_POST['scopes'] ?? []);
        $keys    = (array) $_POST['keys'];
    } else {
        $targets = [(string) ($_POST['target'] ?? '')];
        $scopes  = [(string) ($_POST['scope'] ?? '')];
        $keys    = [(string) ($_POST['key'] ?? '')];
    }

    $queued = 0;
    $userId = (int) Session::getLoginUserID();
    foreach ($keys as $i => $key) {
        $id = MachineAction::queueUninstall(
            $computerId,
            (string) ($targets[$i] ?? ''),
            (string) ($scopes[$i] ?? ''),
            (string) $key,
            $userId
        );
        if ($id > 0) {
            $queued++;
        }
    }
    if ($queued > 0) {
        Event::log(Event::LEVEL_INFO, 'computer', 'Desinstalação solicitada (' . $queued . ' app)', [
            'computador' => $computerId,
            'por'        => $userId,
        ]);
    }
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $queued > 0, 'queued' => $queued]);
        return;
    }
    Session::addMessageAfterRedirect(
        $queued > 0 ? $queued . ' desinstalação(ões) agendada(s).' : 'Nada agendado (dados inválidos).',
        false,
        $queued > 0 ? INFO : ERROR
    );
} elseif ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'message' => 'Ação desconhecida.']);
    return;
} else {
    Session::addMessageAfterRedirect('Ação desconhecida.', false, ERROR);
}

Html::redirect($detailUrl);
