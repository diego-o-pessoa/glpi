<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Entity;
use Location;

/**
 * "Filial" de um chamado, para a coluna da lista de chamados.
 *
 * 1. Entidade do chamado, se nao for a raiz: "Ativa Locacao - Matriz" -> "Matriz"
 *    (o que vem depois do ultimo " - "; sem traco, o nome inteiro).
 * 2. Senao, a localizacao do chamado, pela mesma regra.
 * 3. Senao, vazio (a tela mostra um traco).
 */
final class Branch
{
    /** @var array<int, string> */
    private static array $entities = [];

    /** @var array<int, string> */
    private static array $locations = [];

    public static function of(int $entityId, int $locationId): string
    {
        return self::entityBranch($entityId) ?: self::locationBranch($locationId);
    }

    public static function shortName(string $name): string
    {
        $name = trim($name);
        $position = mb_strrpos($name, ' - ');
        return $position === false ? $name : trim(mb_substr($name, $position + 3));
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
}
