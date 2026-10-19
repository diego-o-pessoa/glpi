<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaprint\PrinterRepository;

include '../../../inc/includes.php';

// Garante o carregamento das classes do plugin
require_once dirname(__DIR__) . '/setup.php';
require_once dirname(__DIR__) . '/inc/menu.class.php';
require_once dirname(__DIR__) . '/inc/profile.class.php';

if (class_exists('Plugin')) {
    Plugin::registerClass('PluginAtivaprintMenu');
    Plugin::registerClass('PluginAtivaprintProfile');
}

// Se o usuário for administrador do sistema, assegura os direitos para a sessão
if (Session::haveRight('config', UPDATE)) {
    PluginAtivaprintProfile::createAdminAccess();
}

if (!Session::haveRight('plugin_ativaprint', READ) && !Session::haveRight('config', UPDATE)) {
    Html::displayRightError();
}

global $CFG_GLPI, $DB;

// Se as tabelas do plugin ainda não existem, executa a instalação automaticamente
if (!$DB->tableExists(PrinterRepository::TABLE)) {
    require_once dirname(__DIR__) . '/hook.php';
    plugin_ativaprint_install();
}

// Garante que o namespace @ativaprint esteja registrado no Twig
$loader = TemplateRenderer::getInstance()->getEnvironment()->getLoader();
if ($loader instanceof \Twig\Loader\FilesystemLoader) {
    try {
        $paths = $loader->getPaths('ativaprint');
        if (empty($paths)) {
            $loader->addPath(dirname(__DIR__) . '/templates', 'ativaprint');
        }
    } catch (\Twig\Error\LoaderError $e) {
        $loader->addPath(dirname(__DIR__) . '/templates', 'ativaprint');
    }
}

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
