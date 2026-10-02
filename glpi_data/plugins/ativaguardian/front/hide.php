<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\MachineRepository;

include('../../../inc/includes.php');

// Oculta (ou reexibe) uma maquina do painel. Nao envia nada ao computador:
// so tira a linha da tela ate a maquina ser reinstalada (outra versao do
// Guardian) ou ate alguem reexibir.
if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_MANAGE, UPDATE)) {
    Session::checkRight('config', UPDATE);
}
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metodo nao permitido.']);
    return;
}

$machinesId = (int) ($_POST['machines_id'] ?? 0);
$hide = ($_POST['hide'] ?? '1') === '1';
if ($machinesId <= 0 || !MachineRepository::setHidden($machinesId, $hide)) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Maquina nao encontrada.']);
    return;
}

Toolbox::logInFile('ativaguardian', sprintf(
    "Maquina %d %s do painel por %s\n",
    $machinesId,
    $hide ? 'ocultada' : 'reexibida',
    getUserName((int) Session::getLoginUserID())
));
echo json_encode([
    'ok'      => true,
    'message' => $hide ? 'Máquina ocultada do painel. Ela volta quando o pacote for reinstalado.' : 'Máquina reexibida no painel.',
], JSON_UNESCAPED_UNICODE);
