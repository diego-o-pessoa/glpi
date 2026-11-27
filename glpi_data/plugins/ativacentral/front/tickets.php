<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\TicketsList;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Tela "Chamados" do Super-Admin no estilo do Ativa Central.
Session::checkCentralAccess();
if (!Session::haveRightsOr('ticket', [Ticket::READALL, Ticket::READMY, Ticket::READGROUP, Ticket::READASSIGN])) {
    throw new AccessDeniedHttpException();
}

$GLOBALS['ATIVACENTRAL_SECTION'] = 'tickets';
Html::header('Chamados', '', 'helpdesk', 'ticket');
TemplateRenderer::getInstance()->display('@ativacentral/tickets.html.twig', TicketsList::data());
Html::footer();
