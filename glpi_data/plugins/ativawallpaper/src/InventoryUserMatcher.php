<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

/**
 * Resolves the interactive Windows account to a unique GLPI user.
 *
 * The inventory protocol may report a domain-qualified account and GLPI users
 * can be duplicated by name. We assign only when one distinct GLPI user
 * matches; choosing the first SQL row would be unsafe.
 */
final class InventoryUserMatcher
{
    /**
     * @return array{status: string, id?: int, name?: string}
     */
    public static function resolve(string $username): array
    {
        global $DB;

        $username = self::normalize($username);
        if ($username === '') {
            return ['status' => 'empty'];
        }

        // Some GLPI Agent versions report the display name (for example
        // "Diego Pessoa") while the Windows account is just "Diego". The
        // first token is considered only as an additional candidate; if it
        // identifies more than one user, the result remains ambiguous.
        $candidates = [$username];
        $firstToken = preg_split('/\s+/', $username, 2)[0] ?? '';
        if ($firstToken !== '' && $firstToken !== $username) {
            $candidates[] = $firstToken;
        }

        $or = [];
        foreach ($candidates as $candidate) {
            $or[] = ['name' => $candidate];
            $or[] = ['firstname' => $candidate];
            $or[] = ['realname' => $candidate];
        }

        $iterator = $DB->request([
            'SELECT' => ['id', 'name', 'firstname', 'realname'],
            'FROM'   => 'glpi_users',
            'WHERE'  => [
                'is_deleted' => 0,
                'is_active'  => 1,
                'OR' => $or,
            ],
        ]);

        $matches = [];
        foreach ($iterator as $row) {
            if (!is_array($row) || !isset($row['id'])) {
                continue;
            }

            $name = self::normalize((string) ($row['name'] ?? ''));
            $firstname = self::normalize((string) ($row['firstname'] ?? ''));
            $realname = self::normalize((string) ($row['realname'] ?? ''));
            $displayName = self::normalize(trim($firstname . ' ' . $realname));

            $fields = [$name, $firstname, $realname, $displayName];
            if (count(array_intersect($candidates, $fields)) === 0) {
                continue;
            }

            $id = (int) $row['id'];
            $matches[$id] = [
                'id'   => $id,
                'name' => (string) ($row['name'] ?? ''),
            ];
        }

        if (count($matches) === 1) {
            return ['status' => 'unique'] + array_values($matches)[0];
        }
        if (count($matches) > 1) {
            return ['status' => 'ambiguous'];
        }
        return ['status' => 'none'];
    }

    public static function normalize(string $username): string
    {
        $username = trim($username);
        if ($username === '') {
            return '';
        }

        $username = str_replace('/', '\\', $username);
        $separator = strrpos($username, '\\');
        if ($separator !== false) {
            $username = substr($username, $separator + 1);
        }

        $at = strpos($username, '@');
        if ($at !== false) {
            $username = substr($username, 0, $at);
        }

        return mb_strtolower(trim($username), 'UTF-8');
    }

    public static function assignToComputer(?int $computerId, string $username): void
    {
        global $DB;

        if ($computerId === null || $computerId < 1) {
            return;
        }

        $resolved = self::resolve($username);
        if (($resolved['status'] ?? '') !== 'unique') {
            return;
        }

        $iterator = $DB->request([
            'SELECT' => ['id', 'users_id'],
            'FROM'   => 'glpi_computers',
            'WHERE'  => ['id' => $computerId, 'is_deleted' => 0],
            'LIMIT'  => 1,
        ]);
        $computer = $iterator->current();
        if (!is_array($computer)) {
            return;
        }

        $userId = (int) $resolved['id'];
        if ((int) ($computer['users_id'] ?? 0) === $userId) {
            return;
        }

        $DB->update('glpi_computers', ['users_id' => $userId], ['id' => $computerId]);
    }

    /**
     * Associate the authenticated GLPI user with the computer that contacted
     * the Ativa API from the same address shortly before the login.
     *
     * This is deliberately conservative: a shared VPN/NAT address can belong
     * to several computers, so an association is made only when exactly one
     * recent active client is found. Existing computer assignments are never
     * overwritten.
     */
    public static function assignFromLogin(int $userId, ?string $ipAddress, int $maxAgeSeconds = 900): void
    {
        global $DB;

        if ($userId < 1 || $ipAddress === null) {
            return;
        }

        $ipAddress = trim($ipAddress);
        if ($ipAddress === '' || filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
            return;
        }

        $maxAgeSeconds = max(60, min($maxAgeSeconds, 3600));
        $now = time();
        $candidates = [];
        $iterator = $DB->request([
            'SELECT' => ['computers_id', 'last_check'],
            'FROM'   => 'glpi_plugin_ativawallpaper_clients',
            'WHERE'  => [
                'last_ip'    => $ipAddress,
                'revoked_at' => null,
            ],
        ]);

        foreach ($iterator as $row) {
            if (!is_array($row)) {
                continue;
            }
            $computerId = (int) ($row['computers_id'] ?? 0);
            $lastCheck = self::dateToTimestamp((string) ($row['last_check'] ?? ''));
            if ($computerId < 1 || $lastCheck < ($now - $maxAgeSeconds)) {
                continue;
            }
            $candidates[$computerId] = true;
        }

        // A shared address is not enough to identify a machine.
        if (count($candidates) !== 1) {
            return;
        }

        $computerId = (int) array_key_first($candidates);
        $computerIterator = $DB->request([
            'SELECT' => ['id', 'users_id'],
            'FROM'   => 'glpi_computers',
            'WHERE'  => ['id' => $computerId, 'is_deleted' => 0],
            'LIMIT'  => 1,
        ]);
        $computer = $computerIterator->current();
        if (!is_array($computer) || (int) ($computer['users_id'] ?? 0) > 0) {
            return;
        }

        $DB->update('glpi_computers', ['users_id' => $userId], ['id' => $computerId]);
    }

    private static function dateToTimestamp(string $value): int
    {
        if ($value === '') {
            return 0;
        }

        return ServerClock::toTimestamp($value);
    }
}
