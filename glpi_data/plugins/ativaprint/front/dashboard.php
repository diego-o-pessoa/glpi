<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaprint\PrinterRepository;

include '../../../inc/includes.php';

// Garante o carregamento das classes do plugin
require_once dirname(__DIR__) . '/setup.php';
require_once dirname(__DIR__) . '/inc/menu.class.php';
require_once dirname(__DIR__) . '/inc/profile.class.php';
require_once dirname(__DIR__) . '/src/PrintConfig.php';
require_once dirname(__DIR__) . '/src/SnmpClient.php';
require_once dirname(__DIR__) . '/src/Poller.php';
require_once dirname(__DIR__) . '/src/PrinterRepository.php';

global $CFG_GLPI, $DB;

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

// Se as tabelas do plugin ainda não existem, executa a instalação automaticamente
if (!$DB->tableExists(PrinterRepository::TABLE)) {
    require_once dirname(__DIR__) . '/hook.php';
    plugin_ativaprint_install();
}

// Garante que o plugin esteja ativo no GLPI para menus e hooks globais
if (class_exists('Plugin')) {
    $plugin = new Plugin();
    if (!$plugin->isInstalled('ativaprint') || !$plugin->isActivated('ativaprint')) {
        $plugin->checkStates(true);
        $pId = (int) $plugin->getIDByDir('ativaprint');
        if ($pId > 0) {
            if (!$plugin->isInstalled('ativaprint')) {
                $plugin->install($pId);
            }
            if (!$plugin->isActivated('ativaprint')) {
                $plugin->activate($pId);
            }
        }
    }
}

// Garante que o namespace @ativaprint esteja registrado no Twig
$loader = TemplateRenderer::getInstance()->getEnvironment()->getLoader();
if ($loader instanceof \Twig\Loader\FilesystemLoader) {
    try {
        $paths = $loader->getPaths('ativaprint');
        if (empty($paths)) {
            $loader->addPath(dirname(__DIR__) . '/templates', 'ativaprint');
        }
    } catch (\Throwable $e) {
        $loader->addPath(dirname(__DIR__) . '/templates', 'ativaprint');
    }
}

$canManage = (bool) (Session::haveRight('plugin_ativaprint', UPDATE) || Session::haveRight('plugin_ativaprint', CREATE) || Session::haveRight('config', UPDATE));
$base = $CFG_GLPI['root_doc'] . '/plugins/ativaprint';

$days = isset($_GET['days']) ? (int) $_GET['days'] : 7;
if (!in_array($days, [7, 30, 90], true)) {
    $days = 7;
}

try {
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
} catch (\Throwable $e) {
    if (!headers_sent()) {
        Html::header('Ativa Print', '', 'admin', 'pluginativaprintmenu', 'dashboard');
    }
    echo '<div style="max-width: 1200px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 12px; border: 1px solid #fee2e2; box-shadow: 0 4px 12px rgba(0,0,0,0.05); font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif;">';
    echo '<h3 style="color: #dc2626; margin-top: 0; display: flex; align-items: center; gap: 8px;"><i class="ti ti-alert-triangle"></i> Falha ao carregar Ativa Print</h3>';
    echo '<p style="font-size: 1.05rem; color: #1e293b; background: #fef2f2; padding: 12px 16px; border-radius: 8px; border-left: 4px solid #dc2626;"><strong>Erro:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p style="color: #64748b; font-size: 0.9rem;"><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ' : <strong>Linha ' . $e->getLine() . '</strong></p>';
    echo '<pre style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; overflow-x: auto; font-size: 0.85rem; color: #334155; line-height: 1.5;">' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '</div>';
}

Html::footer();
