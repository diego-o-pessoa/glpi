<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaprint\PrintConfig;

include '../../../inc/includes.php';

Session::checkRight('config', UPDATE);

global $CFG_GLPI;

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

