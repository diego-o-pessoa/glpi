<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\ContractPage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include '../../../inc/includes.php';

// Contrato: novo (sem id) ou tela do contrato com abas.
Session::checkCentralAccess();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$presetSupplierId = max(0, (int) ($_GET['suppliers_id'] ?? 0));
$contract = null;
if ($id > 0) {
    $contract = new Contract();
    if (!$contract->getFromDB($id)) {
        throw new NotFoundHttpException();
    }
    if (!$contract->can($id, READ)) {
        throw new AccessDeniedHttpException();
    }
} elseif (!Contract::canCreate()) {
    throw new AccessDeniedHttpException();
}

$tab = (string) ($_GET['tab'] ?? 'main');
if ($contract === null || !isset(ContractPage::TABS[$tab])) {
    $tab = 'main';
}

$error = '';
$old = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        if ($contract !== null && isset($_POST['supplier_action'])) {
            if (!$contract->can($id, UPDATE)) {
                throw new AccessDeniedHttpException();
            }
            Session::addMessageAfterRedirect(ContractPage::supplierAction($contract, $_POST));
            Html::redirect(ContractPage::url($id, 'suppliers'));
        }
        if ($contract !== null && isset($_POST['delete'])) {
            if (!$contract->can($id, DELETE)) {
                throw new AccessDeniedHttpException();
            }
            $contract->delete(['id' => $id]);
            Session::addMessageAfterRedirect('Contrato movido para a lixeira.');
            Html::redirect(ContractPage::listUrl());
        }
        if ($contract !== null && !$contract->can($id, UPDATE)) {
            throw new AccessDeniedHttpException();
        }
        $savedId = ContractPage::save($contract, $_POST);
        Session::addMessageAfterRedirect($contract === null ? 'Contrato criado.' : 'Contrato salvo.');
        Html::redirect(ContractPage::url($savedId));
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
        $old = $_POST;
    }
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'contracts';
Html::header($contract !== null ? (string) $contract->fields['name'] : 'Novo contrato', '', 'ac_contract');
TemplateRenderer::getInstance()->display(
    '@ativacentral/contract_view.html.twig',
    ContractPage::viewData($contract, $tab, $old, $error, $presetSupplierId)
);
Html::footer();
