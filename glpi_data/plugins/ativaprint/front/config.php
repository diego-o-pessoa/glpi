<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaprint\PrintConfig;

include '../../../inc/includes.php';

Session::checkRight('config', UPDATE);

global $CFG_GLPI;

require_once dirname(__DIR__) . '/setup.php';
require_once dirname(__DIR__) . '/inc/menu.class.php';

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

TemplateRenderer::getInstance()->display('@ativaprint/config.html.twig', [
    'config'  => PrintConfig::all(),
    'errors'  => $errors,
    'success' => $success,
    'urls'    => [
        'dashboard' => $CFG_GLPI['root_doc'] . '/plugins/ativaprint/front/dashboard.php',
    ],
]);

Html::footer();
