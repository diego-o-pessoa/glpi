<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use CommonITILActor;
use Entity;
use Location;

/**
 * "Filial" de um chamado, para a coluna da lista de chamados.
 *
 * 1. Entidade do chamado, se nao for a raiz ("Ativa Locacao - Matriz" -> "Matriz").
 * 2. Localizacao do chamado.
 * 3. Localizacao do solicitante ("Matriz - 001001" -> "Matriz").
 * 4. Senao, vazio (a tela mostra um traco).
 */
final class Branch
{
    /** @var array<int, string> */
    private static array $entities = [];

    /** @var array<int, string> */
    private static array $locations = [];

    public static function of(int $entityId, int $locationId, int $ticketId = 0): string
    {
        return self::entityBranch($entityId)
            ?: self::locationBranch($locationId)
            ?: self::locationBranch(self::requesterLocation($ticketId));
    }

    /**
     * Nome curto da filial: separa por " - ", descarta codigos numericos
     * ("001001") e o nome da empresa ("Ativa Locacao"); fica o ultimo que sobrar.
     */
    public static function shortName(string $name): string
    {
        $parts = array_values(array_filter(
            array_map('trim', explode(' - ', trim($name))),
            static fn(string $part): bool => $part !== '' && preg_match('/^[\d.\/-]+$/', $part) !== 1,
        ));
        if ($parts === []) {
            return trim($name);
        }
        $named = array_values(array_filter($parts, static fn(string $part): bool => preg_match('/^ativa\b/iu', $part) !== 1));
        return $named !== [] ? end($named) : end($parts);
    }

    private static function entityBranch(int $id): string
    {
        // A raiz ("Grupo Ativa") nao e filial.
        if ($id <= 0) {
            return '';
        }
        if (!array_key_exists($id, self::$entities)) {
            $entity = new Entity();
            self::$entities[$id] = $entity->getFromDB($id) && (int) $entity->fields['level'] > 1
                ? self::shortName((string) $entity->fields['name'])
                : '';
        }
        return self::$entities[$id];
    }

    private static function locationBranch(int $id): string
    {
        if ($id <= 0) {
            return '';
        }
        if (!array_key_exists($id, self::$locations)) {
            $location = new Location();
            self::$locations[$id] = $location->getFromDB($id) ? self::shortName((string) $location->fields['name']) : '';
        }
        return self::$locations[$id];
    }

    /** Localizacao do primeiro solicitante do chamado que tenha uma. */
    private static function requesterLocation(int $ticketId): int
    {
        global $DB;

        if ($ticketId <= 0) {
            return 0;
        }
        $row = $DB->request([
            'SELECT'     => ['glpi_users.locations_id'],
            'FROM'       => 'glpi_tickets_users',
            'INNER JOIN' => [
                'glpi_users' => ['ON' => ['glpi_tickets_users' => 'users_id', 'glpi_users' => 'id']],
            ],
            'WHERE'      => [
                'glpi_tickets_users.tickets_id' => $ticketId,
                'glpi_tickets_users.type'       => CommonITILActor::REQUESTER,
                'glpi_users.locations_id'       => ['>', 0],
            ],
            'ORDER'      => 'glpi_tickets_users.id',
            'LIMIT'      => 1,
        ])->current();

        return (int) ($row['locations_id'] ?? 0);
    }
}
