<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\TicketView;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include '../../../inc/includes.php';

// Tela do chamado para os atendentes (conversa, situacao, aprovacao).
Session::checkCentralAccess();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$ticket = new Ticket();
if ($id <= 0 || !$ticket->getFromDB($id)) {
    throw new NotFoundHttpException();
}
if (!$ticket->canViewItem()) {
    throw new AccessDeniedHttpException();
}
$tab = (string) ($_GET['tab'] ?? 'chat');
if (!isset(TicketView::TABS[$tab])) {
    $tab = 'chat';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        Session::addMessageAfterRedirect(htmlescape(TicketView::handle($ticket, $_POST)));
    } catch (InvalidArgumentException $exception) {
        Session::addMessageAfterRedirect(htmlescape($exception->getMessage()), false, ERROR);
    }
    Html::redirect(TicketView::url($id, $tab));
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'tickets';
Html::header('Chamado #' . $id, '', 'helpdesk', 'ticket');
TemplateRenderer::getInstance()->display('@ativacentral/ticket_view.html.twig', TicketView::viewData($ticket, $tab));
Html::footer();
