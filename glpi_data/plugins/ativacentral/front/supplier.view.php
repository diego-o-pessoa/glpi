<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\SupplierPage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include '../../../inc/includes.php';

// Fornecedor: novo (sem id) ou tela do fornecedor com abas.
Session::checkCentralAccess();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$supplier = null;
if ($id > 0) {
    $supplier = new Supplier();
    if (!$supplier->getFromDB($id)) {
        throw new NotFoundHttpException();
    }
    if (!$supplier->can($id, READ)) {
        throw new AccessDeniedHttpException();
    }
} elseif (!Supplier::canCreate()) {
    throw new AccessDeniedHttpException();
}

$tab = (string) ($_GET['tab'] ?? 'main');
if ($supplier === null || !isset(SupplierPage::TABS[$tab])) {
    $tab = 'main';
}

$error = '';
$old = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        if ($supplier !== null && isset($_POST['contact_action'])) {
            if (!$supplier->can($id, UPDATE)) {
                throw new AccessDeniedHttpException();
            }
            Session::addMessageAfterRedirect(SupplierPage::contactAction($supplier, $_POST));
            Html::redirect(SupplierPage::url($id, 'contacts'));
        }
        if ($supplier !== null && isset($_POST['delete'])) {
            if (!$supplier->can($id, DELETE)) {
                throw new AccessDeniedHttpException();
            }
            $supplier->delete(['id' => $id]);
            Session::addMessageAfterRedirect('Fornecedor movido para a lixeira.');
            Html::redirect(SupplierPage::listUrl());
        }
        if ($supplier !== null && !$supplier->can($id, UPDATE)) {
            throw new AccessDeniedHttpException();
        }
        $savedId = SupplierPage::save($supplier, $_POST);
        Session::addMessageAfterRedirect($supplier === null ? 'Fornecedor criado.' : 'Fornecedor salvo.');
        Html::redirect(SupplierPage::url($savedId));
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
        $old = $_POST;
    }
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'suppliers';
Html::header($supplier !== null ? (string) $supplier->fields['name'] : 'Novo fornecedor', '', 'management', 'supplier');
TemplateRenderer::getInstance()->display('@ativacentral/supplier_view.html.twig', SupplierPage::viewData($supplier, $tab, $old, $error));
Html::footer();
