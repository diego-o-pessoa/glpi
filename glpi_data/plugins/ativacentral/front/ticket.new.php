<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\TicketForm;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Tela "Novo chamado" do Super-Admin (estilo do Self-Service).
Session::checkCentralAccess();
if (!Session::haveRight('ticket', CREATE)) {
    throw new AccessDeniedHttpException();
}

$error = '';
$old = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old = $_POST;
    try {
        $id = TicketForm::create($_POST);
        Session::addMessageAfterRedirect(htmlescape('Chamado #' . $id . ' aberto.'));
        Html::redirect(Ticket::getFormURLWithID($id));
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    }
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'tickets';
Html::header('Novo chamado', '', 'helpdesk', 'ticket');
TemplateRenderer::getInstance()->display('@ativacentral/ticket_new.html.twig', TicketForm::viewData($old, $error));
Html::footer();
