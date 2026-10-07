<?php

declare(strict_types=1);

use GlpiPlugin\Ativarede\Desks;
use GlpiPlugin\Ativarede\Events;
use GlpiPlugin\Ativarede\Inventory;

include '../../../inc/includes.php';

// GET: estado da planta (atualizacao ao vivo). POST: edicao das mesas e
// tratamento dos alertas - so com o direito "Editar planta". O token CSRF vem
// no cabecalho X-Glpi-Csrf-Token e e conferido pelo GLPI antes deste script.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$respond = static function (array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
};

if (!PluginAtivaredeProfile::canView()) {
    $respond(['ok' => false, 'message' => 'Sem permissão.'], 403);
    return;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    session_write_close();
    $state = Inventory::planState((int) ($_GET['plan'] ?? 0));
    $respond(['ok' => $state['plan'] !== null] + $state);
    return;
}

if (!PluginAtivaredeProfile::canManage()) {
    $respond(['ok' => false, 'message' => 'Seu perfil só pode visualizar a planta.'], 403);
    return;
}

$action = (string) ($_POST['action'] ?? '');
$planId = (int) ($_POST['plan'] ?? 0);

try {
    $result = match ($action) {
        'save_desk'    => Desks::save($_POST),
        'move_desk'    => Desks::move((int) ($_POST['id'] ?? 0), (float) ($_POST['x'] ?? 0), (float) ($_POST['y'] ?? 0)),
        'add_desk'     => Desks::add($planId, (float) ($_POST['x'] ?? 0), (float) ($_POST['y'] ?? 0)),
        'delete_desk'  => Desks::delete((int) ($_POST['id'] ?? 0)),
        'resolve'      => Events::resolve((int) ($_POST['id'] ?? 0), (string) ($_POST['resolution'] ?? '')),
        'switch_label' => Desks::saveSwitchLabel((int) ($_POST['id'] ?? 0), (string) ($_POST['label'] ?? '')),
        default        => ['ok' => false, 'message' => 'Ação inválida.'],
    };
} catch (Throwable $exception) {
    Toolbox::logInFile('ativarede', 'Falha na acao ' . $action . ': ' . $exception->getMessage() . "\n");
    $result = ['ok' => false, 'message' => 'Erro ao salvar. Detalhe registrado em files/_log/ativarede.log.'];
}

Inventory::resetCache();
if ($planId > 0 && ($result['ok'] ?? false)) {
    $result['state'] = Inventory::planState($planId);
}
$respond($result, ($result['ok'] ?? false) ? 200 : 422);
