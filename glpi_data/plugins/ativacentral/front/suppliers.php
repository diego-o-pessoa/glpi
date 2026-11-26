<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\SupplierPage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Lista de fornecedores no estilo do Ativa Central.
Session::checkCentralAccess();
if (!Supplier::canView()) {
    throw new AccessDeniedHttpException();
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'suppliers';
Html::header('Fornecedores', '', 'management', 'supplier');
TemplateRenderer::getInstance()->display('@ativacentral/suppliers.html.twig', SupplierPage::listData());
Html::footer();
