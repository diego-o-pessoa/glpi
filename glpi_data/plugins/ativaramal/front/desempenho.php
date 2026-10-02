<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaramal\AccessScope;
use GlpiPlugin\Ativaramal\Performance;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Ranking e tempo medio de atendimento por periodo. Mesmo escopo do
// dashboard: gestor ve so a filial e o setor dele.
if (!PluginAtivaramalProfile::canView()) {
    throw new AccessDeniedHttpException();
}

global $CFG_GLPI;
$period = (string) ($_GET['periodo'] ?? 'hoje');

Html::header('Ativa Ramal - Desempenho', '', 'ativaramal', 'desempenho');
TemplateRenderer::getInstance()->display('@ativaramal/desempenho.html.twig', [
    'data'     => Performance::payload($period, AccessScope::current()),
    'periods'  => Performance::PERIODS,
    'self_url' => $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/desempenho.php',
]);
Html::footer();
