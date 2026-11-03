<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaramal\Directory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Lista de ramais de todas as filiais e setores (so consulta). Primeira
// renderizacao com dados; depois o navegador atualiza ao vivo (lista.data.php).
if (!PluginAtivaramalProfile::canView()) {
    throw new AccessDeniedHttpException();
}

global $CFG_GLPI;
Html::header('Ativa Ramal - Lista de ramais', '', 'ativaramal', 'lista');
TemplateRenderer::getInstance()->display('@ativaramal/lista.html.twig', [
    'initial'  => Directory::payload(),
    'data_url' => $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/lista.data.php',
]);
Html::footer();
