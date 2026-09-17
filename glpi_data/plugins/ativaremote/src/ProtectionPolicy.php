<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use Computer;

/**
 * Computers of the board and of T.I. always ask the user before a remote access and
 * require the T.I. password.
 *
 * A computer is protected when, in the GLPI inventory:
 * - its "Grupo" or "Grupo técnico" is a protected group (or a sub-group of one), or
 * - its "Usuário" is a member of a protected group;
 * or when an administrator marked it by hand in the settings.
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
     * @param array[] $clients Rows of glpi_plugin_ativaremote_clients
     * @return array<int, string> Reason of the protection, by client id (protected clients only)
     */
    public static function evaluate(array $clients): array
    {
        global $DB;

        $reasonsByComputer = [];
        $groupIds = self::groupIds();
        $computerIds = array_values(array_unique(array_filter(array_map(
            static fn (array $client): int => (int) ($client['computers_id'] ?? 0),
            $clients
        ))));

        if ($groupIds !== [] && $computerIds !== []) {
            foreach ($DB->request([
                'SELECT'     => ['glpi_groups_items.items_id', 'glpi_groups.completename'],
                'FROM'       => 'glpi_groups_items',
                'INNER JOIN' => [
                    'glpi_groups' => ['ON' => ['glpi_groups' => 'id', 'glpi_groups_items' => 'groups_id']],
                ],
                'WHERE'      => [
                    'glpi_groups_items.itemtype'  => Computer::class,
                    'glpi_groups_items.items_id'  => $computerIds,
                    'glpi_groups_items.groups_id' => $groupIds,
                ],
            ]) as $row) {
                $reasonsByComputer[(int) $row['items_id']] ??= 'Grupo do computador: ' . $row['completename'];
            }

            foreach ($DB->request([
                'SELECT'     => ['glpi_computers.id AS computer_id', 'glpi_users.name AS user_name', 'glpi_groups.completename'],
                'FROM'       => 'glpi_computers',
                'INNER JOIN' => [
                    'glpi_groups_users' => ['ON' => ['glpi_groups_users' => 'users_id', 'glpi_computers' => 'users_id']],
                    'glpi_groups'       => ['ON' => ['glpi_groups' => 'id', 'glpi_groups_users' => 'groups_id']],
                    'glpi_users'        => ['ON' => ['glpi_users' => 'id', 'glpi_computers' => 'users_id']],
                ],
                'WHERE'      => [
                    'glpi_computers.id'           => $computerIds,
                    'glpi_groups_users.groups_id' => $groupIds,
                ],
            ]) as $row) {
                $reasonsByComputer[(int) $row['computer_id']] ??= sprintf(
                    'Usuário %s no grupo %s',
                    $row['user_name'],
                    $row['completename']
                );
            }
        }

        $reasons = [];
        foreach ($clients as $client) {
            if (!empty($client['protected_manual'])) {
                $reasons[(int) $client['id']] = 'Marcado manualmente nas configurações';
            } elseif (isset($reasonsByComputer[(int) ($client['computers_id'] ?? 0)])) {
                $reasons[(int) $client['id']] = $reasonsByComputer[(int) $client['computers_id']];
            }
        }
        return $reasons;
    }

    public static function isProtected(array $client): bool
    {
        return self::evaluate([$client]) !== [];
    }
}
