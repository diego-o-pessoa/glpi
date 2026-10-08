<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use Computer;
use Session;
use Ticket;

/**
 * Historico e alertas. Mudancas (computador/monitor trocou de lugar, monitor
 * novo) ficam abertas ate a T.I. tratar. Situacoes (monitor ausente, porta
 * compartilhada, mesa vazia, maquina sem relatorio) nao se repetem enquanto
 * houver uma aberta e se fecham sozinhas quando a situacao acaba (cleared).
 */
final class Events
{
    public const COMPUTER_MOVED  = 'computer_moved';
    public const MONITOR_MOVED   = 'monitor_moved';
    public const MONITOR_NEW     = 'monitor_new';
    public const MONITOR_MISSING = 'monitor_missing';
    public const SHARED_PORT     = 'shared_port';
    public const DESK_EMPTY      = 'desk_empty';
    public const MACHINE_SILENT  = 'machine_silent';

    public const OPEN       = 'open';
    public const AUTHORIZED = 'authorized';
    public const IGNORED    = 'ignored';
    public const TICKET     = 'ticket';
    public const CLEARED    = 'cleared';

    /** Situacoes: uma aberta por assunto, fechadas automaticamente. */
    private const CONDITIONS = [self::MONITOR_MISSING, self::SHARED_PORT, self::DESK_EMPTY, self::MACHINE_SILENT];

    public static function labels(): array
    {
        return [
            self::COMPUTER_MOVED  => 'Computador mudou de mesa',
            self::MONITOR_MOVED   => 'Monitor mudou de mesa',
            self::MONITOR_NEW     => 'Monitor novo',
            self::MONITOR_MISSING => 'Monitor ausente',
            self::SHARED_PORT     => 'Porta compartilhada',
            self::DESK_EMPTY      => 'Mesa vazia',
            self::MACHINE_SILENT  => 'Máquina sem relatório',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::OPEN       => 'Aberto',
            self::AUTHORIZED => 'Autorizado',
            self::IGNORED    => 'Ignorado',
            self::TICKET     => 'Chamado aberto',
            self::CLEARED    => 'Resolvido sozinho',
        ];
    }

    /**
     * Registra um evento. Para situacoes, devolve a aberta que ja existir com
     * o mesmo assunto em vez de duplicar.
     */
    public static function record(string $type, array $fields): int
    {
        global $DB;

        if (in_array($type, self::CONDITIONS, true)) {
            $existing = self::openCondition($type, $fields);
            if ($existing > 0) {
                return $existing;
            }
        }

        $allowed = ['machines_id', 'monitors_id', 'desks_id', 'from_switches_id', 'from_port', 'to_switches_id',
            'to_port', 'from_machines_id', 'to_machines_id', 'details'];
        $row = array_intersect_key($fields, array_flip($allowed));
        if (isset($row['details'])) {
            $row['details'] = mb_substr((string) $row['details'], 0, 500);
        }
        $DB->insert(Settings::TABLE_EVENTS, $row + [
            'type'          => $type,
            'status'        => self::OPEN,
            'date_creation' => date('Y-m-d H:i:s'),
        ]);
        return (int) $DB->insertId();
    }

    /** Fecha (cleared) as situacoes abertas daquele assunto. */
    public static function clear(string $type, array $subject): void
    {
        global $DB;

        $where = ['type' => $type, 'status' => self::OPEN] + self::subject($type, $subject);
        $DB->update(Settings::TABLE_EVENTS, ['status' => self::CLEARED, 'resolved_at' => date('Y-m-d H:i:s')], $where);
    }

    private static function openCondition(string $type, array $fields): int
    {
        global $DB;

        $row = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => Settings::TABLE_EVENTS,
            'WHERE'  => ['type' => $type, 'status' => self::OPEN] + self::subject($type, $fields),
            'LIMIT'  => 1,
        ])->current();
        return (int) ($row['id'] ?? 0);
    }

    /** Campos que identificam o assunto de cada situacao. */
    private static function subject(string $type, array $fields): array
    {
        return match ($type) {
            self::MONITOR_MISSING => ['monitors_id' => (int) ($fields['monitors_id'] ?? 0)],
            self::SHARED_PORT     => ['to_switches_id' => (int) ($fields['to_switches_id'] ?? 0), 'to_port' => (string) ($fields['to_port'] ?? '')],
            self::DESK_EMPTY      => ['desks_id' => (int) ($fields['desks_id'] ?? 0)],
            self::MACHINE_SILENT  => ['machines_id' => (int) ($fields['machines_id'] ?? 0)],
            default               => [],
        };
    }

    /**
     * Tratamento pela T.I.: authorize | ignore | ticket. Autorizar a mudanca
     * de um computador atualiza a Localizacao dele no GLPI para a da planta.
     *
     * @return array{ok: bool, message: string, ticket_url?: string}
     */
    public static function resolve(int $eventId, string $action): array
    {
        global $DB, $CFG_GLPI;

        $event = $DB->request(['FROM' => Settings::TABLE_EVENTS, 'WHERE' => ['id' => $eventId], 'LIMIT' => 1])->current();
        if (!$event) {
            return ['ok' => false, 'message' => 'Alerta não encontrado.'];
        }
        if ($event['status'] !== self::OPEN) {
            return ['ok' => false, 'message' => 'Este alerta já foi tratado.'];
        }

        $now = date('Y-m-d H:i:s');
        $userId = (int) Session::getLoginUserID();
        $update = ['resolved_at' => $now, 'users_id' => $userId];
        $message = '';
        $result = ['ok' => true];

        switch ($action) {
            case 'authorize':
                $update['status'] = self::AUTHORIZED;
                $message = 'Mudança autorizada.';
                if ($event['type'] === self::COMPUTER_MOVED) {
                    $message .= self::applyLocation($event);
                }
                break;
            case 'ignore':
                $update['status'] = self::IGNORED;
                $message = 'Alerta ignorado.';
                break;
            case 'ticket':
                $ticketId = self::openTicket($event);
                if ($ticketId <= 0) {
                    return ['ok' => false, 'message' => 'Não foi possível abrir o chamado.'];
                }
                $update['status'] = self::TICKET;
                $update['tickets_id'] = $ticketId;
                $message = 'Chamado #' . $ticketId . ' aberto.';
                $result['ticket_url'] = $CFG_GLPI['root_doc'] . '/front/ticket.form.php?id=' . $ticketId;
                break;
            default:
                return ['ok' => false, 'message' => 'Ação inválida.'];
        }

        $DB->update(Settings::TABLE_EVENTS, $update, ['id' => $eventId, 'status' => self::OPEN]);
        return $result + ['message' => $message];
    }

    /** Localizacao da planta onde a maquina esta agora -> Localizacao do Computador. */
    private static function applyLocation(array $event): string
    {
        $machine = Inventory::machine((int) $event['machines_id']);
        if (!$machine || (int) $machine['computers_id'] <= 0) {
            return ' A máquina ainda não está vinculada a um computador do GLPI; localização não alterada.';
        }
        $desk = Inventory::deskOf($machine);
        $locationId = $desk ? (int) ($desk['locations_id'] ?? 0) : 0;
        if ($locationId <= 0) {
            return ' A planta não tem Localização definida; localização do computador não alterada.';
        }
        $computer = new Computer();
        if ($computer->getFromDB((int) $machine['computers_id'])
            && (int) $computer->fields['locations_id'] !== $locationId
            && $computer->can($computer->getID(), UPDATE)) {
            $computer->update(['id' => $computer->getID(), 'locations_id' => $locationId]);
            return ' Localização do computador atualizada.';
        }
        return '';
    }

    private static function openTicket(array $event): int
    {
        $text = Inventory::describe($event);
        $content = '<p>' . htmlescape($text['title']) . '</p><p>' . htmlescape($text['detail']) . '</p>'
            . '<p><em>Aberto pelo Ativa Rede (alerta #' . (int) $event['id'] . ').</em></p>';
        $input = [
            'name'        => 'Ativa Rede: ' . $text['title'],
            'content'     => $content,
            'entities_id' => (int) ($_SESSION['glpiactive_entity'] ?? 0),
            'type'        => Ticket::INCIDENT_TYPE,
            '_users_id_requester' => (int) Session::getLoginUserID(),
        ];
        $computerId = (int) ($text['computers_id'] ?? 0);
        if ($computerId > 0) {
            $input['items_id'] = [Computer::class => [$computerId]];
        }
        $ticket = new Ticket();
        if (!$ticket->can(-1, CREATE, $input)) {
            return 0;
        }
        return (int) $ticket->add($input);
    }
}
