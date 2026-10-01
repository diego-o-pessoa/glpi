<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

/**
 * Filial e setor de cada ramal (a API da TW nao tem esses campos).
 *
 * Ordem de prioridade:
 *   1. ajuste manual do ramal (tela "Ramais", glpi_plugin_ativaramal_extensions);
 *   2. usuario do GLPI com o ramal no Telefone/Telefone 2/Celular:
 *      Localizacao = filial, Grupo (padrao ou primeiro) = setor;
 *   3. grupo de captura (callgroup) cadastrado na tela "Ramais"
 *      (glpi_plugin_ativaramal_groups);
 *   4. "Sem filial" / "Sem setor".
 */
final class ExtensionDirectory
{
    public const GROUPS_TABLE     = 'glpi_plugin_ativaramal_groups';
    public const EXTENSIONS_TABLE = 'glpi_plugin_ativaramal_extensions';

    public const NO_BRANCH = 'Sem filial';
    public const NO_SECTOR = 'Sem setor';

    /** @var array<string, array{filial: string, setor: string}>|null */
    private static ?array $groups = null;
    /** @var array<string, array{filial: string, setor: string}>|null */
    private static ?array $overrides = null;
    /** @var array<string, array{pessoa: string, filial: string, setor: string, users_id: int}>|null */
    private static ?array $glpiUsers = null;

    /**
     * Filial/setor de um ramal (registro da TW ja filtrado pelo TwApi).
     *
     * @param array<string, mixed> $extension
     * @return array{filial: string, setor: string, fonte: string, pessoa: string, users_id: int}
     */
    public static function resolve(array $extension): array
    {
        $ramal = (string) ($extension['ramal'] ?? '');
        $alias = (string) ($extension['alias'] ?? '');
        $group = trim((string) ($extension['callgroup'] ?? ''));

        $manual = self::overrides()[$ramal] ?? null;
        $user = self::glpiUsers()[$ramal] ?? ($alias !== '' ? (self::glpiUsers()[$alias] ?? null) : null);
        $byGroup = $group !== '' ? (self::groups()[$group] ?? null) : null;

        $filial = $setor = '';
        $fonte = [];
        foreach ([['manual', $manual], ['glpi', $user], ['grupo', $byGroup]] as [$source, $data]) {
            if (!is_array($data)) {
                continue;
            }
            if ($filial === '' && ($data['filial'] ?? '') !== '') {
                $filial = $data['filial'];
                $fonte['filial'] = $source;
            }
            if ($setor === '' && ($data['setor'] ?? '') !== '') {
                $setor = $data['setor'];
                $fonte['setor'] = $source;
            }
        }

        return [
            'filial'   => $filial !== '' ? $filial : self::NO_BRANCH,
            'setor'    => $setor !== '' ? $setor : self::NO_SECTOR,
            'fonte'    => $fonte === [] ? 'nenhuma' : implode('/', array_unique($fonte)),
            'pessoa'   => is_array($user) ? $user['pessoa'] : '',
            'users_id' => is_array($user) ? $user['users_id'] : 0,
        ];
    }

    // ------------------------------------------------------------ cadastro do plugin

    /** @return array<string, array{filial: string, setor: string}> */
    public static function groups(): array
    {
        return self::$groups ??= self::load(self::GROUPS_TABLE, 'callgroup');
    }

    /** @return array<string, array{filial: string, setor: string}> */
    public static function overrides(): array
    {
        return self::$overrides ??= self::load(self::EXTENSIONS_TABLE, 'ramal');
    }

    /** @return array<string, array{filial: string, setor: string}> */
    private static function load(string $table, string $keyField): array
    {
        global $DB;

        $map = [];
        if (!$DB->tableExists($table)) {
            return $map;
        }
        foreach ($DB->request(['FROM' => $table]) as $row) {
            $map[(string) $row[$keyField]] = ['filial' => (string) $row['filial'], 'setor' => (string) $row['setor']];
        }
        return $map;
    }

    /**
     * Grava o cadastro (grupos de captura ou ramais). Linha com filial e setor
     * vazios e removida (volta ao automatico).
     *
     * @param array<string, array{filial?: string, setor?: string}> $rows chave => valores
     */
    public static function save(string $table, string $keyField, array $rows): int
    {
        global $DB;

        if (!in_array($table, [self::GROUPS_TABLE, self::EXTENSIONS_TABLE], true)) {
            return 0;
        }
        $changed = 0;
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $key => $values) {
            $key = mb_substr(trim((string) $key), 0, 32);
            if ($key === '' || !preg_match('/^[\w.-]+$/u', $key)) {
                continue;
            }
            $filial = mb_substr(trim((string) ($values['filial'] ?? '')), 0, 255);
            $setor = mb_substr(trim((string) ($values['setor'] ?? '')), 0, 255);
            $current = $DB->request(['FROM' => $table, 'WHERE' => [$keyField => $key], 'LIMIT' => 1])->current();
            if ($filial === '' && $setor === '') {
                if (is_array($current)) {
                    $DB->delete($table, ['id' => (int) $current['id']]);
                    $changed++;
                }
                continue;
            }
            if (is_array($current)) {
                if ($current['filial'] !== $filial || $current['setor'] !== $setor) {
                    $DB->update($table, ['filial' => $filial, 'setor' => $setor, 'date_mod' => $now], ['id' => (int) $current['id']]);
                    $changed++;
                }
            } else {
                $DB->insert($table, [$keyField => $key, 'filial' => $filial, 'setor' => $setor, 'date_mod' => $now]);
                $changed++;
            }
        }
        self::$groups = self::$overrides = null;
        return $changed;
    }

    // ------------------------------------------------------------ GLPI

    /**
     * Usuarios ativos do GLPI por numero de ramal (so os digitos do Telefone,
     * Telefone 2 ou Celular, comparados por igualdade: "Ramal 1001" casa com
     * 1001, mas 5519...1001 nao).
     *
     * @return array<string, array{pessoa: string, filial: string, setor: string, users_id: int}>
     */
    public static function glpiUsers(): array
    {
        global $DB, $GLPI_CACHE;

        if (self::$glpiUsers !== null) {
            return self::$glpiUsers;
        }
        $cached = $GLPI_CACHE?->get('ativaramal_glpi_users');
        if (is_array($cached)) {
            return self::$glpiUsers = $cached;
        }

        $map = [];
        $iterator = $DB->request([
            'SELECT'    => [
                'glpi_users.id', 'glpi_users.name', 'glpi_users.realname', 'glpi_users.firstname',
                'glpi_users.phone', 'glpi_users.phone2', 'glpi_users.mobile', 'glpi_users.groups_id',
                'glpi_locations.name AS location_name',
            ],
            'FROM'      => 'glpi_users',
            'LEFT JOIN' => ['glpi_locations' => ['ON' => ['glpi_locations' => 'id', 'glpi_users' => 'locations_id']]],
            'WHERE'     => [
                'glpi_users.is_deleted' => 0,
                'glpi_users.is_active'  => 1,
                // "<> ''" tambem descarta NULL.
                'OR'                    => [
                    ['glpi_users.phone' => ['<>', '']],
                    ['glpi_users.phone2' => ['<>', '']],
                    ['glpi_users.mobile' => ['<>', '']],
                ],
            ],
        ]);
        $users = [];
        foreach ($iterator as $row) {
            $users[(int) $row['id']] = $row;
        }
        $groups = self::userGroups(array_keys($users), $users);

        foreach ($users as $id => $row) {
            $person = trim((string) $row['firstname'] . ' ' . (string) $row['realname']) ?: (string) $row['name'];
            foreach (['phone', 'phone2', 'mobile'] as $field) {
                $digits = preg_replace('/\D+/', '', (string) $row[$field]) ?? '';
                // Ramal: numero curto (ate 6 digitos). Telefones completos nao entram.
                if ($digits === '' || strlen($digits) > 6 || isset($map[$digits])) {
                    continue;
                }
                $map[$digits] = [
                    'pessoa'   => $person,
                    'filial'   => (string) ($row['location_name'] ?? ''),
                    'setor'    => $groups[$id] ?? '',
                    'users_id' => $id,
                ];
            }
        }
        $GLPI_CACHE?->set('ativaramal_glpi_users', $map, 300);
        return self::$glpiUsers = $map;
    }

    /**
     * Setor = grupo padrao do usuario; sem padrao, o primeiro grupo dele.
     *
     * @param list<int> $ids
     * @param array<int, array<string, mixed>> $users
     * @return array<int, string>
     */
    private static function userGroups(array $ids, array $users): array
    {
        global $DB;

        if ($ids === []) {
            return [];
        }
        $names = [];
        $groupIds = array_values(array_unique(array_filter(array_map(static fn ($u) => (int) $u['groups_id'], $users))));
        $byId = [];
        if ($groupIds !== []) {
            foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_groups', 'WHERE' => ['id' => $groupIds]]) as $group) {
                $byId[(int) $group['id']] = (string) $group['name'];
            }
        }
        foreach ($users as $id => $user) {
            if (isset($byId[(int) $user['groups_id']])) {
                $names[$id] = $byId[(int) $user['groups_id']];
            }
        }
        foreach ($DB->request([
            'SELECT'     => ['glpi_groups_users.users_id', 'glpi_groups.name'],
            'FROM'       => 'glpi_groups_users',
            'INNER JOIN' => ['glpi_groups' => ['ON' => ['glpi_groups' => 'id', 'glpi_groups_users' => 'groups_id']]],
            'WHERE'      => ['glpi_groups_users.users_id' => $ids],
            'ORDER'      => ['glpi_groups_users.id ASC'],
        ]) as $row) {
            $names[(int) $row['users_id']] ??= (string) $row['name'];
        }
        return $names;
    }
}
