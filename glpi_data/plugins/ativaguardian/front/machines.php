<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\MachinesView;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}

global $CFG_GLPI;

$data = MachinesView::load();
$metrics = MachinesView::metrics($data);
$canManage = Session::haveRight(PluginAtivaguardianProfile::RIGHT_MANAGE, UPDATE)
    || Session::haveRight('config', UPDATE);
$signature = MachinesView::signature($data);

// Endpoint da atualização ao vivo: a Visão Geral consulta com X-Requested-With
// e só recebe HTML novo quando a assinatura muda. Mesmo padrão que o Ativa
// Updater usa na seção de computadores dele.
$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    $previousSignature = (string) ($_GET['signature'] ?? '');
    $changed = $previousSignature !== $signature;
    echo json_encode([
        'signature' => $signature,
        'changed'   => $changed,
        'metrics'   => $metrics,
        'html'      => $changed ? MachinesView::renderTable($data, $canManage) : null,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    return;
}

// A aba "Computadores" foi removida por mostrar a mesma tabela da Visão Geral.
// Um acesso direto a esta página vai para lá em vez de duplicar a tela.
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/ativaguardian/front/dashboard.php');
