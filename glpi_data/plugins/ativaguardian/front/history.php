<?php

declare(strict_types=1);

use GlpiPlugin\Ativaguardian\ActionQueue;
use GlpiPlugin\Ativaguardian\MachineRepository;
use GlpiPlugin\Ativaguardian\PageLayout;

include('../../../inc/includes.php');

if (!Session::haveRight(PluginAtivaguardianProfile::RIGHT_VIEW, READ)) {
    Session::checkRight('config', UPDATE);
}

Html::header(__('Ativa Guardian', 'ativaguardian'), $_SERVER['PHP_SELF'], 'plugins', 'ativaguardian');

global $DB;

// Fecha o que ficou preso antes de exibir, para o historico nao mostrar como
// "em execucao" uma acao de uma maquina que desligou no meio.
ActionQueue::expireStale();

$machinesId = (int) ($_GET['machines_id'] ?? 0);

$names = [];
foreach ($DB->request([
    'SELECT' => ['id', 'hostname', 'machine_id'],
    'FROM'   => MachineRepository::MACHINES_TABLE,
]) as $row) {
    $names[(int) $row['id']] = (string) $row['hostname'] !== ''
        ? (string) $row['hostname']
        : (string) $row['machine_id'];
}

if ($machinesId > 0) {
    $rows = ActionQueue::history($machinesId, 100);
} else {
    $rows = [];
    foreach ($DB->request([
        'FROM'  => ActionQueue::TABLE,
        'ORDER' => ['id DESC'],
        'LIMIT' => 100,
    ]) as $row) {
        $rows[] = $row;
    }
}

$badge = static function (string $status): string {
    $map = [
        ActionQueue::PENDING => ['Na fila', 'bg-secondary'],
        ActionQueue::RUNNING => ['Executando', 'bg-warning'],
        ActionQueue::SUCCESS => ['Sucesso', 'bg-success'],
        ActionQueue::FAILED  => ['Falhou', 'bg-danger'],
    ];
    [$label, $class] = $map[$status] ?? [$status, 'bg-secondary'];
    return "<span class='ag-badge {$class}'>" . htmlescape($label) . '</span>';
};

echo PageLayout::header('history');

echo "<section class='ag-card'><header class='ag-card-header'>"
    . "<h2 class='ag-card-title'><i class='fas fa-clock-rotate-left'></i>Histórico de ações"
    . ($machinesId > 0 && isset($names[$machinesId]) ? ' — ' . htmlescape($names[$machinesId]) : '')
    . '</h2></header><div class=\'ag-card-body\'>';

if ($rows === []) {
    echo "<div class='ag-empty'><i class='fas fa-inbox me-2'></i>Nenhuma ação registrada ainda.</div>";
} else {
    echo "<table class='ag-table'><thead><tr><th>#</th><th>Máquina</th><th>Componente</th>"
        . '<th>Ação</th><th>Status</th><th>Solicitado por</th><th>Criada</th><th>Executada</th><th>Detalhe</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $machineName = $names[(int) $row['machines_id']] ?? ('#' . (int) $row['machines_id']);
        $createdBy = (int) $row['created_by'];
        echo '<tr>'
            . '<td>' . (int) $row['id'] . '</td>'
            . '<td>' . htmlescape($machineName) . '</td>'
            . '<td>' . htmlescape((string) $row['component']) . '</td>'
            . '<td>' . htmlescape((string) $row['action']) . '</td>'
            . '<td>' . $badge((string) $row['status']) . '</td>'
            . '<td>' . ($createdBy > 0 ? htmlescape(getUserName($createdBy)) : '-') . '</td>'
            . '<td>' . Html::convDateTime($row['created_at']) . '</td>'
            . '<td>' . (!empty($row['finished_at']) ? Html::convDateTime($row['finished_at']) : '-') . '</td>'
            . "<td><small>" . htmlescape((string) ($row['error_message'] ?? '')) . '</small></td>'
            . '</tr>';
    }
    echo '</tbody></table>';
}

echo '</div></section>';
echo PageLayout::footer();

Html::footer();
