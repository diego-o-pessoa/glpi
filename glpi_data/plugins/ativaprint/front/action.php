<?php

declare(strict_types=1);

use GlpiPlugin\Ativaprint\Poller;
use GlpiPlugin\Ativaprint\PrinterRepository;

include '../../../inc/includes.php';

require_once dirname(__DIR__) . '/setup.php';
require_once dirname(__DIR__) . '/src/PrintConfig.php';
require_once dirname(__DIR__) . '/src/SnmpClient.php';
require_once dirname(__DIR__) . '/src/Poller.php';
require_once dirname(__DIR__) . '/src/PrinterRepository.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

if (!Session::haveRight('plugin_ativaprint', UPDATE) && !Session::haveRight('plugin_ativaprint', CREATE) && !Session::haveRight('config', UPDATE)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sem permissão.']);
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' || !empty($_POST['ajax']);

if ($ajax) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
}

try {
    switch ($action) {
        case 'add_printer':
            $ip = trim((string) ($_POST['ip'] ?? ''));
            if ($ip === '') {
                throw new Exception('O endereço IP da impressora é obrigatório.');
            }
            $id = PrinterRepository::create([
                'name'           => trim((string) ($_POST['name'] ?? '')),
                'model'          => trim((string) ($_POST['model'] ?? '')),
                'ip'             => $ip,
                'sector'         => trim((string) ($_POST['sector'] ?? 'Geral')),
                'snmp_community' => trim((string) ($_POST['snmp_community'] ?? '')),
            ]);

            $created = PrinterRepository::getById($id);
            if ($created !== null) {
                // Tenta primeira consulta imediata na rede
                Poller::pollAndSave($created);
            }

            $response = ['ok' => true, 'message' => 'Impressora cadastrada com sucesso!', 'id' => $id];
            break;

        case 'poll_printer':
            $id = (int) ($_POST['id'] ?? 0);
            $printer = PrinterRepository::getById($id);
            if ($printer === null) {
                throw new Exception('Impressora não encontrada.');
            }
            $result = Poller::pollAndSave($printer);
            $response = [
                'ok'      => true,
                'message' => 'Status atualizado com sucesso.',
                'status'  => $result['status'] ?? 'offline',
            ];
            break;

        case 'poll_all':
            $count = Poller::pollMany(30);
            $response = [
                'ok'      => true,
                'message' => "Verificação concluída para {$count} impressora(s).",
                'count'   => $count,
            ];
            break;

        case 'delete_printer':
            $id = (int) ($_POST['id'] ?? 0);
            if (!Session::haveRight('plugin_ativaprint', DELETE) && !Session::haveRight('config', UPDATE)) {
                throw new Exception('Sem permissão para excluir impressoras.');
            }
            PrinterRepository::delete($id);
            $response = ['ok' => true, 'message' => 'Impressora removida.'];
            break;

        case 'save_supply':
            $id = PrinterRepository::saveSupply($_POST);
            $response = ['ok' => true, 'message' => 'Insumo salvo no estoque com sucesso.', 'id' => $id];
            break;

        case 'delete_supply':
            $id = (int) ($_POST['id'] ?? 0);
            PrinterRepository::deleteSupply($id);
            $response = ['ok' => true, 'message' => 'Insumo removido do estoque.'];
            break;

        default:
            throw new Exception('Ação inválida.');
    }
} catch (\Throwable $e) {
    if ($ajax) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        exit;
    }
    Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
    Html::back();
    exit;
}

if ($ajax) {
    echo json_encode($response);
    exit;
}

Session::addMessageAfterRedirect($response['message'] ?? 'Feito.', true, INFO);
Html::back();

