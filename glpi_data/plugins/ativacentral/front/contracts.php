<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\PartnersLists;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Lista de Contratos no estilo do Ativa Central.
Session::checkCentralAccess();
if (!Contract::canView()) {
    throw new AccessDeniedHttpException();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        Session::addMessageAfterRedirect(htmlescape(PartnersLists::contractAction($_POST)));
    } catch (InvalidArgumentException $exception) {
        Session::addMessageAfterRedirect(htmlescape($exception->getMessage()), false, ERROR);
    }
    Html::redirect(PartnersLists::contractsUrl());
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'contracts';
Html::header('Contratos', '', 'ac_contract');
TemplateRenderer::getInstance()->display('@ativacentral/contracts.html.twig', PartnersLists::contractsData((int) ($_GET['edit'] ?? 0), isset($_GET['new'])));
Html::footer();
