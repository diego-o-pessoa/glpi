<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\DocumentsLibrary;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Biblioteca de documentos no estilo do Ativa Central.
Session::checkCentralAccess();
if (!Document::canView()) {
    throw new AccessDeniedHttpException();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        Session::addMessageAfterRedirect(DocumentsLibrary::handle($_POST, $_FILES));
    } catch (InvalidArgumentException $exception) {
        Session::addMessageAfterRedirect(htmlescape($exception->getMessage()), false, ERROR);
    }
    // Acoes de categoria voltam com a gaveta de categorias aberta.
    Html::redirect(DocumentsLibrary::url() . (str_starts_with((string) ($_POST['doc_action'] ?? ''), 'cat_') ? '?categories=1' : ''));
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'documents';
Html::header('Documentos', '', 'management', 'document');
TemplateRenderer::getInstance()->display('@ativacentral/documents.html.twig', DocumentsLibrary::data((int) ($_GET['edit'] ?? 0), isset($_GET['new'])));
Html::footer();
