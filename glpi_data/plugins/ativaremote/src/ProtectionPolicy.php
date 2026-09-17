<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use Computer;

/**
 * Computers of the protected groups (e.g. Diretoria and T.I.) always ask the user
 * before a remote access and require the T.I. password to connect.
 *
 * A computer belongs to a group through its "Grupo" or "Grupo técnico" in the
 * GLPI inventory; sub-groups of a protected group are protected too.
 */
final class ProtectionPolicy
{
    /** @var int[]|null */
    private static ?array $groupIds = null;

    /** @return int[] Protected groups and all their sub-groups. */
    public static function groupIds(): array
    {
        if (self::$groupIds === null) {
            $ids = [];
            foreach (Settings::protectedGroupIds() as $groupId) {
                foreach (getSonsOf('glpi_groups', $groupId) as $id) {
                    $ids[(int) $id] = true;
                }
            }
            self::$groupIds = array_keys($ids);
        }
        return self::$groupIds;
    }

    public static function reset(): void
    {
        self::$groupIds = null;
    }

    /**
     * @param int[] $computerIds
     * @return array<int, true> Protected computer ids.
     */
    public static function protectedComputers(array $computerIds): array
    {
        global $DB;

        $computerIds = array_values(array_filter(array_map('intval', $computerIds)));
        $groupIds = self::groupIds();
        if ($computerIds === [] || $groupIds === []) {
            return [];
        }
        $protected = [];
        foreach ($DB->request([
            'SELECT'   => ['items_id'],
            'DISTINCT' => true,
            'FROM'     => 'glpi_groups_items',
            'WHERE'    => [
                'itemtype'  => Computer::class,
                'items_id'  => $computerIds,
                'groups_id' => $groupIds,
            ],
        ]) as $row) {
            $protected[(int) $row['items_id']] = true;
        }
        return $protected;
    }

    public static function isProtected(array $client): bool
    {
        $computerId = (int) ($client['computers_id'] ?? 0);
        return $computerId > 0 && self::protectedComputers([$computerId]) !== [];
    }
}
