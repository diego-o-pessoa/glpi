<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\ContractPage;
use GlpiPlugin\Ativacentral\PartnersLists;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Lista de Contratos no estilo do Ativa Central.
Session::checkCentralAccess();
if (!Contract::canView()) {
    throw new AccessDeniedHttpException();
}

// Links antigos (?edit=N ou ?new=1) seguem para a tela completa do contrato.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $editId = (int) ($_GET['edit'] ?? 0);
    if ($editId > 0) {
        Html::redirect(ContractPage::url($editId));
    }
    if (isset($_GET['new']) && Contract::canCreate()) {
        Html::redirect(ContractPage::newUrl());
    }
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
TemplateRenderer::getInstance()->display('@ativacentral/contracts.html.twig', PartnersLists::contractsData());
Html::footer();
