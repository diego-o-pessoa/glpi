<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;

include '../../../inc/includes.php';

if (!Session::haveRight('plugin_ativaremote', READ) && !Session::haveRight('config', UPDATE)) {
    Html::displayRightError();
}

global $DB, $CFG_GLPI;

// Pagination
$limit = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
$page = max(1, (int) ($_GET['page'] ?? 1));
$start = ($page - 1) * $limit;

$table = 'glpi_plugin_ativaremote_clients';
$total = countElementsInTable($table);
$pages = max(1, (int) ceil($total / $limit));

$clients = [];
foreach ($DB->request([
    'FROM'  => $table,
    'ORDER' => ['last_check DESC'],
    'START' => $start,
    'LIMIT' => $limit,
]) as $row) {
    $clients[] = $row;
}

$base = $CFG_GLPI['root_doc'] . '/plugins/ativaremote';

Html::header('Ativa Remote', '', 'admin', 'pluginativaremotemenu', 'dashboard');
// GLPI resolves "@ativaremote/..." to plugins/ativaremote/templates/ on disk.
TemplateRenderer::getInstance()->display('@ativaremote/dashboard.html.twig', [
    'clients' => [
        'rows'  => $clients,
        'total' => $total,
        'page'  => $page,
        'pages' => $pages,
        'limit' => $limit,
    ],
    'urls' => [
        'action'    => $base . '/front/action.php',
        'dashboard' => $base . '/front/dashboard.php',
    ],
]);
Html::footer();
