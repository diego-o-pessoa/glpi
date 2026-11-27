<?php

/**
 * Status dos chamados como a Ativa usa (Self-Service):
 * 1 Novo -> Aguardando atendente; 2 Atribuido -> Em atendimento;
 * 3 Planejado -> Em execucao; 4 Pendente -> Aguardando usuario/aprovacao/
 * terceiro (pelo motivo de pendencia); 5 e 6 -> Finalizado.
 */

if (!function_exists('painel_ticket_status')) {
    /** @return array{label: string, class: string} */
    function painel_ticket_status(int $ticketId, int $status): array
    {
        global $DB;
        switch ($status) {
            case 1:
                return ['label' => 'Aguardando atendente', 'class' => 'st-gray'];
            case 2:
                return ['label' => 'Em atendimento', 'class' => 'st-blue'];
            case 3:
                return ['label' => 'Em execução', 'class' => 'st-cyan'];
            case 4:
                $reason = $DB->request([
                    'SELECT' => ['r.name'],
                    'FROM' => 'glpi_pendingreasons_items AS pi',
                    'INNER JOIN' => ['glpi_pendingreasons AS r' => ['ON' => ['pi' => 'pendingreasons_id', 'r' => 'id']]],
                    'WHERE' => ['pi.itemtype' => 'Ticket', 'pi.items_id' => $ticketId],
                    'LIMIT' => 1,
                ])->current();
                $name = $reason ? mb_strtolower((string) $reason['name']) : '';
                if (str_contains($name, 'aprova')) {
                    return ['label' => 'Aguardando aprovação', 'class' => 'st-purple'];
                }
                if (str_contains($name, 'terceiro')) {
                    return ['label' => 'Aguardando terceiro', 'class' => 'st-pink'];
                }
                return ['label' => 'Aguardando usuário', 'class' => 'st-orange'];
            default:
                return ['label' => 'Finalizado', 'class' => 'st-green'];
        }
    }
}
