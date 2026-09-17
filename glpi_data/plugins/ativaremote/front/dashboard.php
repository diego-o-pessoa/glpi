<?php

declare(strict_types=1);

include '../../../inc/includes.php';

if (!Session::haveRight('plugin_ativaremote', READ) && !Session::haveRight('config', UPDATE)) {
    Html::displayRightError();
}

Html::header('Ativa Remote', $_SERVER['PHP_SELF'], 'admin', 'pluginativaremotemenu', 'dashboard');

global $DB, $CFG_GLPI;

// Handle pagination
$limit = (int) ($_GET['limit'] ?? 50);
$page = (int) ($_GET['page'] ?? 1);
$start = ($page - 1) * $limit;

// Fetch computers
$where = [];
$total = countElementsInTable('glpi_plugin_ativaremote_clients', $where);
$pages = max(1, (int) ceil($total / $limit));

$iterator = $DB->request([
    'FROM'  => 'glpi_plugin_ativaremote_clients',
    'WHERE' => $where,
    'ORDER' => ['last_check DESC'],
    'START' => $start,
    'LIMIT' => $limit,
]);

$clients = [];
foreach ($iterator as $row) {
    $clients[] = $row;
}

// Render Twig template
$twig = Plugin::getWebDir('ativaremote') . '/templates/dashboard.html.twig';
if (file_exists($CFG_GLPI['root_doc'] . '/plugins/ativaremote/templates/dashboard.html.twig')) {
    // We render using GLPI's internal Twig instance if possible, or include it directly
    // Usually GLPI uses `Html::displayTemplate` but since it might not be available
    // in all GLPI versions we can use direct require if needed, but modern GLPI has Twig.
    global $CFG_GLPI;
    
    $twig_params = [
        'clients' => [
            'rows'  => $clients,
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
            'limit' => $limit
        ],
        'urls' => [
            'action' => $CFG_GLPI['root_doc'] . '/plugins/ativaremote/front/action.php',
            'dashboard' => $CFG_GLPI['root_doc'] . '/plugins/ativaremote/front/dashboard.php',
        ]
    ];
    
    Html::requireJs('ativaremote'); // If we have JS
    TemplateRenderer::getInstance()->display('@ativaremote/dashboard.html.twig', $twig_params);
} else {
    echo "Template not found.";
}

Html::footer();
