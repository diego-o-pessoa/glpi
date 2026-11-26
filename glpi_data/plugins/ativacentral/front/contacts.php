<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\PartnersLists;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Lista de Contatos no estilo do Ativa Central.
Session::checkCentralAccess();
if (!Contact::canView()) {
    throw new AccessDeniedHttpException();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        Session::addMessageAfterRedirect(htmlescape(PartnersLists::contactAction($_POST)));
    } catch (InvalidArgumentException $exception) {
        Session::addMessageAfterRedirect(htmlescape($exception->getMessage()), false, ERROR);
    }
    Html::redirect(PartnersLists::contactsUrl());
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'contacts';
Html::header('Contatos', '', 'ac_contact');
TemplateRenderer::getInstance()->display('@ativacentral/contacts.html.twig', PartnersLists::contactsData((int) ($_GET['edit'] ?? 0), isset($_GET['new'])));
Html::footer();
