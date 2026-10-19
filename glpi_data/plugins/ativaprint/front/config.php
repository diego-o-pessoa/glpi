<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaprint\PrintConfig;

include '../../../inc/includes.php';

Session::checkRight('config', UPDATE);

global $CFG_GLPI;

require_once dirname(__DIR__) . '/setup.php';
require_once dirname(__DIR__) . '/inc/menu.class.php';
require_once dirname(__DIR__) . '/src/PrintConfig.php';

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

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = PrintConfig::save($_POST);
    if ($errors === []) {
        $success = true;
        Session::addMessageAfterRedirect('Configurações salvas com sucesso.', true, INFO);
    }
}

Html::header('Ativa Print - Configurações', '', 'config', 'pluginativaprintmenu', 'config');

try {
    TemplateRenderer::getInstance()->display('@ativaprint/config.html.twig', [
        'config'  => PrintConfig::all(),
        'errors'  => $errors,
        'success' => $success,
        'urls'    => [
            'dashboard' => $CFG_GLPI['root_doc'] . '/plugins/ativaprint/front/dashboard.php',
        ],
    ]);
} catch (\Throwable $e) {
    echo '<div style="margin: 2rem; padding: 1.5rem; background: #fff; border-radius: 8px; border: 1px solid #fee2e2;">';
    echo '<h3 style="color: #dc2626; margin-top: 0;">Erro nas configurações do Ativa Print</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</div>';
}

Html::footer();
