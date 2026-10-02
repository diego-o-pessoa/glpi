<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaramal\AccessScope;
use GlpiPlugin\Ativaramal\Dashboard;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

if (!PluginAtivaramalProfile::canView()) {
    throw new AccessDeniedHttpException();
}

global $CFG_GLPI;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front';

// Primeira renderizacao com dados; depois o navegador atualiza ao vivo
// (dashboard.data.php), sem recarregar a pagina.
Html::header('Ativa Ramal - Dashboard', '', 'ativaramal', 'dashboard');
TemplateRenderer::getInstance()->display('@ativaramal/dashboard.html.twig', [
    // Sem "ver todos os setores": so a filial e os setores do usuario.
    'initial'    => Dashboard::payload(AccessScope::current()),
    'data_url'   => $base . '/dashboard.data.php',
    'ramais_url' => Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, READ) ? $base . '/ramais.php' : '',
]);
Html::footer();
