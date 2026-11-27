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

if (!function_exists('painel_ticket_kinds')) {
    /** Tipos de chamado da Ativa: chave => [nome, tipo do GLPI]. */
    function painel_ticket_kinds(): array
    {
        return [
            'incident'    => ['Incidente', 1],
            'service'     => ['Serviço', 2],
            'improvement' => ['Melhoria', 2],
            'project'     => ['Projeto', 2],
        ];
    }

    function painel_ticket_kind_save(int $ticketId, string $kind): void
    {
        global $DB;
        if ($ticketId > 0 && isset(painel_ticket_kinds()[$kind]) && $DB->tableExists('glpi_plugin_ativacentral_ticketkinds')) {
            $DB->updateOrInsert('glpi_plugin_ativacentral_ticketkinds', ['kind' => $kind], ['tickets_id' => $ticketId]);
        }
    }

    /** Nome do tipo do chamado (ou Incidente/Requisicao do GLPI, se nao houver). */
    function painel_ticket_kind_label(int $ticketId, int $glpiType): string
    {
        global $DB;
        if ($DB->tableExists('glpi_plugin_ativacentral_ticketkinds')) {
            $row = $DB->request(['SELECT' => 'kind', 'FROM' => 'glpi_plugin_ativacentral_ticketkinds', 'WHERE' => ['tickets_id' => $ticketId]])->current();
            if ($row && isset(painel_ticket_kinds()[$row['kind']])) {
                return painel_ticket_kinds()[$row['kind']][0];
            }
        }
        return $glpiType === 2 ? 'Requisição' : 'Incidente';
    }
}
