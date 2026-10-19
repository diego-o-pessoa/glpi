<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaprint\PrinterRepository;

include '../../../inc/includes.php';

if (!Session::haveRight('plugin_ativaprint', READ) && !Session::haveRight('config', UPDATE)) {
    Html::displayRightError();
}

global $CFG_GLPI;

$canManage = (bool) (Session::haveRight('plugin_ativaprint', UPDATE) || Session::haveRight('plugin_ativaprint', CREATE) || Session::haveRight('config', UPDATE));
$base = $CFG_GLPI['root_doc'] . '/plugins/ativaprint';

$days = isset($_GET['days']) ? (int) $_GET['days'] : 7;
if (!in_array($days, [7, 30, 90], true)) {
    $days = 7;
}

$initialKpis = PrinterRepository::getKpis($days);
$initialPrinters = PrinterRepository::getAll();
$initialSupplies = PrinterRepository::getSupplies();

Html::header('Ativa Print', '', 'admin', 'pluginativaprintmenu', 'dashboard');

TemplateRenderer::getInstance()->display('@ativaprint/dashboard.html.twig', [
    'can_manage'     => $canManage,
    'days'           => $days,
    'kpis'           => $initialKpis,
    'printers'       => $initialPrinters,
    'supplies'       => $initialSupplies,
    'urls' => [
        'base'          => $base,
        'data'          => $base . '/front/dashboard.data.php',
        'action'        => $base . '/front/action.php',
        'config'        => $base . '/front/config.php',
        'glpi_printers' => $CFG_GLPI['root_doc'] . '/front/printer.php',
    ],
]);

Html::footer();

