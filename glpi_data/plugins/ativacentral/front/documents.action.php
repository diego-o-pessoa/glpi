<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\DocumentsPanel;

include '../../../inc/includes.php';

// Painel "Documentos" (qualquer item): enviar, vincular e desvincular.
Session::checkCentralAccess();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        Session::addMessageAfterRedirect(DocumentsPanel::handle($_POST, $_FILES));
    } catch (InvalidArgumentException $exception) {
        Session::addMessageAfterRedirect(htmlescape($exception->getMessage()), false, ERROR);
    }
}
Html::back();
