<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use Computer;
use Monitor;

/**
 * Leitura para as telas: estado de cada mesa na planta, listas de
 * equipamentos, alertas descritos em portugues e a aba do Computador.
 * Nada aqui grava.
 */
final class Inventory
{
    /** @var array<int, array>|null */
    private static ?array $switchCache = null;

    // ------------------------------------------------------------------
    // Consultas basicas
    // ------------------------------------------------------------------

    public static function machine(int $id): ?array
    {
        global $DB;

        if ($id <= 0) {
            return null;
        }
        return $DB->request(['FROM' => Settings::TABLE_MACHINES, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() ?: null;
    }

    /** Mesa de uma porta (com a Localizacao da planta), ou null. */
    public static function deskAt(int $switchId, string $port): ?array
    {
        global $DB;

        if ($switchId <= 0 || $port === '') {
            return null;
        }
        return $DB->request([
            'SELECT'    => [Settings::TABLE_DESKS . '.*', Settings::TABLE_PLANS . '.locations_id', Settings::TABLE_PLANS . '.name AS plan_name'],
            'FROM'      => Settings::TABLE_DESKS,
            'LEFT JOIN' => [
                Settings::TABLE_PLANS => ['ON' => [Settings::TABLE_DESKS => 'plans_id', Settings::TABLE_PLANS => 'id']],
            ],
            'WHERE'     => [Settings::TABLE_DESKS . '.switches_id' => $switchId, Settings::TABLE_DESKS . '.port' => $port],
            'LIMIT'     => 1,
        ])->current() ?: null;
    }

    /**
     * Mesa de uma maquina: pela porta do switch; sem porta (Wi-Fi), pelo
     * vinculo direto feito na planta.
     */
    public static function deskOf(array $machine): ?array
    {
        global $DB;

        $desk = self::deskAt((int) $machine['switches_id'], (string) $machine['port']);
        if ($desk || (int) ($machine['id'] ?? 0) <= 0) {
            return $desk;
        }
        return $DB->request([
            'SELECT'    => [Settings::TABLE_DESKS . '.*', Settings::TABLE_PLANS . '.locations_id', Settings::TABLE_PLANS . '.name AS plan_name'],
            'FROM'      => Settings::TABLE_DESKS,
            'LEFT JOIN' => [
                Settings::TABLE_PLANS => ['ON' => [Settings::TABLE_DESKS => 'plans_id', Settings::TABLE_PLANS => 'id']],
            ],
            'WHERE'     => [Settings::TABLE_DESKS . '.machines_id' => (int) $machine['id']],
            'LIMIT'     => 1,
        ])->current() ?: null;
    }

    /** @return array<int, array> switches por id, com o rotulo curto pronto. */
    public static function switches(): array
    {
        global $DB;

        if (self::$switchCache !== null) {
            return self::$switchCache;
        }
        $list = [];
        // "Nao e switch" (PC/switchzinho de mesa) fica fora de tudo.
        $where = $DB->fieldExists(Settings::TABLE_SWITCHES, 'ignored') ? ['ignored' => 0] : [];
        foreach ($DB->request(['FROM' => Settings::TABLE_SWITCHES, 'WHERE' => $where, 'ORDER' => ['mgmt_ip', 'id']]) as $row) {
            $row['short'] = self::switchShort($row);
            $row['display'] = self::switchDisplay($row);
            $list[(int) $row['id']] = $row;
        }
        return self::$switchCache = $list;
    }

    /** Aparelhos marcados como "nao e switch" (para poder desfazer). */
    public static function ignoredSwitches(): array
    {
        global $DB;

        if (!$DB->fieldExists(Settings::TABLE_SWITCHES, 'ignored')) {
            return [];
        }
        $list = [];
        foreach ($DB->request(['FROM' => Settings::TABLE_SWITCHES, 'WHERE' => ['ignored' => 1], 'ORDER' => 'id']) as $row) {
            $row['short'] = self::switchShort($row);
            $row['last_seen'] = self::date($row['last_seen']);
            $list[] = $row;
        }
        return $list;
    }

    public static function resetCache(): void
    {
        self::$switchCache = null;
    }

    /** Rotulo curto para caber na mesa: apelido, ".43" (final do IP) ou parte do MAC. */
    public static function switchShort(array $switch): string
    {
        if (trim((string) $switch['label']) !== '') {
            return mb_substr(trim((string) $switch['label']), 0, 8);
        }
        if (preg_match('/\.(\d{1,3})$/', (string) $switch['mgmt_ip'], $m)) {
            return '.' . $m[1];
        }
        return substr(str_replace([':', '-'], '', (string) $switch['chassis_id']), -4);
    }

    public static function switchDisplay(array $switch): string
    {
        $parts = [];
        if (trim((string) $switch['label']) !== '') {
            $parts[] = trim((string) $switch['label']);
        }
        if ((string) $switch['mgmt_ip'] !== '') {
            $parts[] = (string) $switch['mgmt_ip'];
        }
        if ($parts === []) {
            $parts[] = (string) ($switch['system_name'] ?: $switch['chassis_id']);
        }
        return implode(' · ', $parts);
    }

    public static function positionLabel(int $switchId, string $port): string
    {
        if ($switchId <= 0 || $port === '') {
            return 'sem posição';
        }
        $switch = self::switches()[$switchId] ?? null;
        $text = 'switch ' . ($switch ? $switch['display'] : '#' . $switchId) . ', porta ' . $port;
        $desk = self::deskAt($switchId, $port);
        return $desk ? 'mesa ' . $desk['name'] . ' (' . $text . ')' : $text;
    }

    // ------------------------------------------------------------------
    // Planta
    // ------------------------------------------------------------------

    public static function plans(): array
    {
        global $DB;

        $plans = [];
        foreach ($DB->request(['FROM' => Settings::TABLE_PLANS, 'ORDER' => 'id']) as $row) {
            $plans[] = $row;
        }
        return $plans;
    }

    /**
     * Tudo que a planta precisa: mesas com estado, maquina, monitores e
     * alertas; portas vistas sem mesa; contadores da legenda.
     */
    public static function planState(int $planId): array
    {
        global $DB;

        $plan = $DB->request(['FROM' => Settings::TABLE_PLANS, 'WHERE' => ['id' => $planId], 'LIMIT' => 1])->current();
        if (!$plan) {
            return ['plan' => null];
        }

        $desks = iterator_to_array($DB->request([
            'FROM'  => Settings::TABLE_DESKS,
            'WHERE' => ['plans_id' => $planId],
            'ORDER' => 'name',
        ]), false);

        $machines = self::decoratedMachines();
        $byPort = [];
        $byId = [];
        foreach ($machines as $machine) {
            $byId[$machine['id']] = $machine;
            if ((int) $machine['switches_id'] > 0 && $machine['port'] !== '') {
                $byPort[$machine['switches_id'] . '|' . $machine['port']][] = $machine;
            }
        }

        $openEvents = self::openEventsIndex();
        $switches = self::switches();
        $mapped = [];
        $counts = ['online' => 0, 'offline' => 0, 'alert' => 0, 'empty' => 0, 'unmapped' => 0];
        $outDesks = [];

        foreach ($desks as $desk) {
            $key = (int) $desk['switches_id'] . '|' . $desk['port'];
            $hasPort = (int) $desk['switches_id'] > 0 && $desk['port'] !== '';
            if ($hasPort) {
                $mapped[$key] = true;
            }
            // Sem porta (notebook no Wi-Fi): a maquina e vinculada direto a mesa.
            $fixedId = $hasPort ? 0 : (int) ($desk['machines_id'] ?? 0);
            $here = $hasPort ? ($byPort[$key] ?? []) : (isset($byId[$fixedId]) ? [$byId[$fixedId]] : []);

            $alerts = [];
            foreach ($openEvents['desk'][(int) $desk['id']] ?? [] as $event) {
                $alerts[$event['id']] = $event;
            }
            foreach ($here as $machine) {
                foreach ($openEvents['machine'][(int) $machine['id']] ?? [] as $event) {
                    $alerts[$event['id']] = $event;
                }
            }
            if ($hasPort) {
                foreach ($openEvents['port'][$key] ?? [] as $event) {
                    $alerts[$event['id']] = $event;
                }
            }

            if (!$hasPort && $fixedId <= 0) {
                $state = 'unmapped';
            } elseif ($alerts !== []) {
                $state = 'alert';
            } elseif ($here === []) {
                $state = 'empty';
            } else {
                $state = array_filter($here, static fn(array $m): bool => $m['online']) !== [] ? 'online' : 'offline';
            }
            $counts[$state]++;

            $outDesks[] = [
                'id'       => (int) $desk['id'],
                'name'     => (string) $desk['name'],
                'x'        => (float) $desk['x'],
                'y'        => (float) $desk['y'],
                'w'        => (float) $desk['w'],
                'h'        => (float) $desk['h'],
                'chair'    => (string) $desk['chair'],
                'comment'  => (string) $desk['comment'],
                'switches_id' => (int) $desk['switches_id'],
                'port'     => (string) $desk['port'],
                'machines_id' => $fixedId,
                'sector'   => (string) ($desk['sector'] ?? ''),
                'switch'   => $hasPort ? ($switches[(int) $desk['switches_id']]['short'] ?? '?') : '',
                'switch_display' => $hasPort ? ($switches[(int) $desk['switches_id']]['display'] ?? '?') : '',
                'state'    => $state,
                'machines' => array_values($here),
                'alerts'   => array_values(array_map([self::class, 'eventView'], $alerts)),
            ];
        }

        // Portas onde ha maquina e nenhuma mesa (em nenhuma planta): o atalho
        // para a T.I. mapear as mesas.
        $allMapped = [];
        foreach ($DB->request(['SELECT' => ['switches_id', 'port'], 'FROM' => Settings::TABLE_DESKS, 'WHERE' => ['switches_id' => ['>', 0]]]) as $row) {
            $allMapped[(int) $row['switches_id'] . '|' . $row['port']] = true;
        }
        $unmapped = [];
        foreach ($byPort as $key => $list) {
            if (isset($allMapped[$key])) {
                continue;
            }
            [$switchId, $port] = explode('|', $key, 2);
            $unmapped[] = [
                'kind'        => 'port',
                'switches_id' => (int) $switchId,
                'port'        => $port,
                'switch'      => $switches[(int) $switchId]['display'] ?? '#' . $switchId,
                'machines'    => array_map([self::class, 'pickView'], $list),
            ];
        }
        usort($unmapped, static fn(array $a, array $b): int => [$a['switch'], (int) $a['port']] <=> [$b['switch'], (int) $b['port']]);

        // Maquinas sem porta do switch (Wi-Fi ou cabo sem LLDP) e sem mesa:
        // tambem podem ser colocadas na mesa, vinculadas diretamente.
        $assigned = [];
        foreach ($DB->request(['SELECT' => ['machines_id'], 'FROM' => Settings::TABLE_DESKS, 'WHERE' => ['machines_id' => ['>', 0]]]) as $row) {
            $assigned[(int) $row['machines_id']] = true;
        }
        $noPort = array_values(array_filter($machines, static fn(array $m): bool => ((int) $m['switches_id'] === 0 || $m['port'] === '') && !isset($assigned[$m['id']])));
        foreach ($noPort as $machine) {
            $unmapped[] = [
                'kind'        => 'machine',
                'machines_id' => $machine['id'],
                'link'        => $machine['link'],
                'machines'    => [self::pickView($machine)],
            ];
        }

        $wifi = array_values(array_filter($noPort, static fn(array $m): bool => $m['link'] === 'wifi'));

        return [
            'plan'     => [
                'id'     => (int) $plan['id'],
                'name'   => (string) $plan['name'],
                'width'  => (int) $plan['width'],
                'height' => (int) $plan['height'],
                'background' => (string) $plan['background'],
                'locations_id' => (int) $plan['locations_id'],
            ],
            'desks'    => $outDesks,
            'sectors'  => array_values(array_unique(array_filter(array_column($outDesks, 'sector')))),
            'counts'   => $counts,
            'unmapped' => $unmapped,
            'wifi'     => count($wifi),
            'switches' => array_values(array_map(static fn(array $s): array => [
                'id' => (int) $s['id'], 'short' => $s['short'], 'display' => $s['display'],
            ], $switches)),
            'updated'  => date('d/m/Y H:i:s'),
        ];
    }

    /** Resumo da maquina para a lista "Quem senta nesta mesa?". */
    public static function pickView(array $m): array
    {
        return ['hostname' => $m['hostname'], 'computer' => $m['computer'], 'label' => $m['label'], 'windows_user' => $m['windows_user'],
                'person' => $m['person'], 'user_label' => $m['user_label'],
                'user' => $m['user'], 'group' => $m['group'], 'online' => $m['online']];
    }

    // ------------------------------------------------------------------
    // Maquinas e monitores (com dados do GLPI e do Guardian)
    // ------------------------------------------------------------------

    /** Maquinas com computador, usuario, grupo, status online e monitores. */
    public static function decoratedMachines(?array $where = null): array
    {
        global $DB;

        $rows = iterator_to_array($DB->request([
            'FROM'  => Settings::TABLE_MACHINES,
            'WHERE' => $where ?? [],
            'ORDER' => 'hostname',
        ]), false);
        if ($rows === []) {
            return [];
        }

        $online = self::guardianContacts(array_column($rows, 'machine_id'));
        $computers = self::computerInfo(array_map('intval', array_column($rows, 'computers_id')));
        $monitors = self::monitorsByMachine(array_map('intval', array_column($rows, 'id')));
        $switches = self::switches();

        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $computer = $computers[(int) $row['computers_id']] ?? null;
            $contact = $online[$row['machine_id']] ?? null;
            $name = (string) (($computer['name'] ?? '') ?: $row['hostname']);
            // Quem esta usando: o nome corrigido em Equipamentos; senao a conta
            // logada no Windows (Guardian); sem sessao aberta, o usuario
            // atribuido ao computador no GLPI.
            $auto = (string) (($contact['username'] ?? '') ?: ($computer['user'] ?? ''));
            $userLabel = trim((string) ($row['user_label'] ?? ''));
            $who = $userLabel !== '' ? $userLabel : $auto;
            $out[] = [
                'id'           => $id,
                'machine_id'   => (string) $row['machine_id'],
                'hostname'     => (string) $row['hostname'],
                'label'        => $name . ($who !== '' ? ' (' . $who . ')' : ''),
                'person'       => $who,
                'user_label'   => $userLabel,
                'user_auto'    => $auto,
                'windows_user' => (string) ($contact['username'] ?? ''),
                'computers_id' => (int) $row['computers_id'],
                'computer'     => $computer['name'] ?? '',
                'computer_url' => $computer['url'] ?? '',
                'user'         => $computer['user'] ?? '',
                'group'        => $computer['group'] ?? '',
                'ip'           => (string) $row['ip'],
                'mac'          => (string) $row['mac'],
                'link'         => (string) $row['link'],
                'switches_id'  => (int) $row['switches_id'],
                'port'         => (string) $row['port'],
                'switch'       => (int) $row['switches_id'] > 0 ? ($switches[(int) $row['switches_id']]['display'] ?? '#' . $row['switches_id']) : '',
                'since'        => self::date($row['since']),
                'pending'      => (int) $row['pending_count'] > 0
                    ? (($switches[(int) $row['pending_switches_id']]['display'] ?? '#' . $row['pending_switches_id']) . ', porta ' . $row['pending_port'])
                    : '',
                'last_report'  => self::date($row['last_report']),
                'online'       => $contact !== null && $contact['online'],
                'last_contact' => $contact !== null ? self::date($contact['last_contact']) : '',
                'monitors'     => $monitors[$id] ?? [],
                // Coleta incompleta na maquina (PowerShell, pktmon...), informada por ela.
                'diagnostic'   => (string) ($row['diagnostic'] ?? ''),
            ];
        }
        return $out;
    }

    /** @return array<string, array{online: bool, last_contact: ?string, username: string}> por machine_id */
    private static function guardianContacts(array $machineIds): array
    {
        global $DB;

        if ($machineIds === [] || !$DB->tableExists(Settings::TABLE_GUARDIAN_MACHINES)) {
            return [];
        }
        $limit = time() - Settings::guardianOfflineSeconds();
        $hasUser = $DB->fieldExists(Settings::TABLE_GUARDIAN_MACHINES, 'username');
        $out = [];
        foreach ($DB->request([
            'SELECT' => array_merge(['machine_id', 'last_contact'], $hasUser ? ['username'] : []),
            'FROM'   => Settings::TABLE_GUARDIAN_MACHINES,
            'WHERE'  => ['machine_id' => array_values(array_unique($machineIds))],
        ]) as $row) {
            $ts = $row['last_contact'] ? strtotime((string) $row['last_contact']) : false;
            $out[(string) $row['machine_id']] = [
                'online'       => $ts !== false && $ts >= $limit,
                'last_contact' => $row['last_contact'],
                'username'     => self::accountName((string) ($row['username'] ?? '')),
            ];
        }
        return $out;
    }

    /** "DOMINIO\diego.pessoa" -> "diego.pessoa". */
    private static function accountName(string $user): string
    {
        $user = trim($user);
        $pos = strrpos($user, '\\');
        return mb_substr($pos === false ? $user : substr($user, $pos + 1), 0, 64);
    }

    /**
     * Maquinas com o Ativa Guardian que ainda nao mandaram a posicao ao Ativa
     * Rede, com o motivo provavel (versao antiga, desligada, aguardando).
     */
    public static function guardianWithoutReport(): array
    {
        global $DB;

        if (!$DB->tableExists(Settings::TABLE_GUARDIAN_MACHINES)) {
            return [];
        }
        $known = [];
        foreach ($DB->request(['SELECT' => ['machine_id'], 'FROM' => Settings::TABLE_MACHINES]) as $row) {
            $known[(string) $row['machine_id']] = true;
        }
        $hasUser = $DB->fieldExists(Settings::TABLE_GUARDIAN_MACHINES, 'username');
        $hasHidden = $DB->fieldExists(Settings::TABLE_GUARDIAN_MACHINES, 'hidden_at');
        $limit = time() - Settings::guardianOfflineSeconds();
        $rejections = Schema::rejections();
        $out = [];
        foreach ($DB->request([
            'SELECT' => array_merge(['machine_id', 'hostname', 'guardian_version', 'last_contact'], $hasUser ? ['username'] : []),
            'FROM'   => Settings::TABLE_GUARDIAN_MACHINES,
            'WHERE'  => $hasHidden ? ['hidden_at' => null] : [],
            'ORDER'  => 'hostname',
        ]) as $row) {
            if (isset($known[(string) $row['machine_id']])) {
                continue;
            }
            $version = (string) $row['guardian_version'];
            $ts = $row['last_contact'] ? strtotime((string) $row['last_contact']) : false;
            $rejection = $rejections[(string) $row['machine_id']] ?? null;
            if ($rejection !== null) {
                // A maquina tentou e o GLPI recusou: o motivo exato.
                $reason = $rejection['message'] . ' (' . $rejection['attempts'] . ' tentativa(s), a última em '
                    . self::date($rejection['last_at']) . ').';
            } elseif ($version === '' || version_compare($version, '1.6.0', '<')) {
                $reason = 'Guardian ' . ($version ?: 'sem versão') . ': precisa do 1.6.0 ou mais novo (atualizar o pacote unificado).';
            } elseif ($ts === false || $ts < $limit) {
                $reason = 'Desligada ou sem contato desde ' . self::date($row['last_contact']) . '.';
            } elseif ($ts < time() - 10 * MINUTE_TIMESTAMP) {
                // O heartbeat sai a cada 30 s: 10 min calado = servico parado ou travado.
                $reason = 'O serviço do Guardian parou de responder às ' . self::date($row['last_contact'])
                    . ' (parado ou travado). Veja o serviço AtivaGuardian e o guardian.log na máquina.';
            } else {
                $reason = version_compare($version, '1.6.9', '<')
                    ? 'Ligada, mas ainda não enviou a posição. Guardian ' . $version . ' pode ter recebido erro do GLPI e esperar até 6 h; '
                        . 'use "Atualizar agora" na planta ou atualize o pacote unificado (1.6.9+ mostra o motivo aqui).'
                    : 'Ligada, mas ainda não enviou a posição. O primeiro envio sai ~2 min após o serviço iniciar.';
            }
            $user = self::accountName((string) ($row['username'] ?? ''));
            $out[] = [
                'label'        => (string) $row['hostname'] . ($user !== '' ? ' (' . $user . ')' : ''),
                'version'      => $version,
                'last_contact' => self::date($row['last_contact']),
                'reason'       => $reason,
            ];
        }
        return $out;
    }

    /** @return array<int, array{name: string, url: string, user: string, group: string}> */
    private static function computerInfo(array $computerIds): array
    {
        global $DB;

        $computerIds = array_values(array_unique(array_filter($computerIds)));
        if ($computerIds === []) {
            return [];
        }
        $out = [];
        $userIds = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'users_id'],
            'FROM'   => 'glpi_computers',
            'WHERE'  => ['id' => $computerIds],
        ]) as $row) {
            $out[(int) $row['id']] = [
                'name'     => (string) $row['name'],
                'url'      => Computer::getFormURLWithID((int) $row['id']),
                'users_id' => (int) $row['users_id'],
                'user'     => '',
                'group'    => '',
            ];
            if ((int) $row['users_id'] > 0) {
                $userIds[] = (int) $row['users_id'];
            }
        }

        $users = [];
        if ($userIds !== []) {
            foreach ($DB->request([
                'SELECT' => ['id', 'name', 'firstname', 'realname'],
                'FROM'   => 'glpi_users',
                'WHERE'  => ['id' => array_values(array_unique($userIds))],
            ]) as $user) {
                $full = trim($user['firstname'] . ' ' . $user['realname']);
                $users[(int) $user['id']] = ['name' => $full !== '' ? $full : (string) $user['name'], 'group' => ''];
            }
            foreach ($DB->request([
                'SELECT'     => ['glpi_groups_users.users_id', 'glpi_groups.completename'],
                'FROM'       => 'glpi_groups_users',
                'INNER JOIN' => ['glpi_groups' => ['ON' => ['glpi_groups_users' => 'groups_id', 'glpi_groups' => 'id']]],
                'WHERE'      => ['glpi_groups_users.users_id' => array_keys($users)],
                'ORDER'      => 'glpi_groups.completename',
            ]) as $membership) {
                $uid = (int) $membership['users_id'];
                if (isset($users[$uid]) && $users[$uid]['group'] === '') {
                    $users[$uid]['group'] = (string) $membership['completename'];
                }
            }
        }
        foreach ($out as &$computer) {
            if (isset($users[$computer['users_id']])) {
                $computer['user'] = $users[$computer['users_id']]['name'];
                $computer['group'] = $users[$computer['users_id']]['group'];
            }
        }
        return $out;
    }

    /** @return array<int, list<array>> monitores agrupados por maquina */
    private static function monitorsByMachine(array $machineIds): array
    {
        global $DB;

        if ($machineIds === []) {
            return [];
        }
        $rows = iterator_to_array($DB->request([
            'FROM'  => Settings::TABLE_MONITORS,
            'WHERE' => ['machines_id' => $machineIds],
            'ORDER' => ['missing_since', 'model'],
        ]), false);
        $native = self::nativeMonitors(array_column($rows, 'serial'));
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['machines_id']][] = self::monitorView($row, $native);
        }
        return $out;
    }

    /** Monitores do inventario nativo do GLPI com o mesmo numero de serie. */
    private static function nativeMonitors(array $serials): array
    {
        global $DB;

        $serials = array_values(array_unique(array_filter(array_map('strval', $serials))));
        if ($serials === []) {
            return [];
        }
        $out = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'serial'],
            'FROM'   => 'glpi_monitors',
            'WHERE'  => ['serial' => $serials, 'is_deleted' => 0, 'is_template' => 0],
        ]) as $row) {
            $out[strtoupper((string) $row['serial'])] ??= ['id' => (int) $row['id'], 'name' => (string) $row['name'], 'url' => Monitor::getFormURLWithID((int) $row['id'])];
        }
        return $out;
    }

    private static function monitorView(array $row, array $native): array
    {
        $glpi = $row['serial'] !== '' ? ($native[strtoupper((string) $row['serial'])] ?? null) : null;
        $model = trim((string) $row['model']) !== '' ? (string) $row['model'] : trim($row['manufacturer'] . ' ' . $row['product_code']);
        return [
            'id'         => (int) $row['id'],
            'model'      => $model,
            'serial'     => (string) $row['serial'],
            'has_serial' => (bool) $row['has_serial'],
            'year'       => (int) $row['year'],
            'connection' => (string) $row['connection'],
            'since'      => self::date($row['since']),
            'missing'    => $row['missing_since'] !== null,
            'missing_since' => self::date($row['missing_since']),
            'last_seen'  => self::date($row['last_seen']),
            'glpi_url'   => $glpi['url'] ?? '',
            'glpi_name'  => $glpi['name'] ?? '',
        ];
    }

    /** Monitores para a tela de equipamentos (com a maquina/mesa atual). */
    public static function monitors(): array
    {
        global $DB;

        $rows = iterator_to_array($DB->request(['FROM' => Settings::TABLE_MONITORS, 'ORDER' => ['model', 'serial']]), false);
        $native = self::nativeMonitors(array_column($rows, 'serial'));
        $machines = [];
        foreach (self::decoratedMachines() as $machine) {
            $machines[$machine['id']] = $machine;
        }

        // Ultimas mudancas de cada monitor (por onde passou).
        $history = [];
        $ids = array_map('intval', array_column($rows, 'id'));
        if ($ids !== []) {
            foreach ($DB->request([
                'FROM'  => Settings::TABLE_EVENTS,
                'WHERE' => [
                    'monitors_id' => $ids,
                    'type'        => [Events::MONITOR_MOVED, Events::MONITOR_NEW, Events::MONITOR_MISSING],
                ],
                'ORDER' => 'id DESC',
                'LIMIT' => 2000,
            ]) as $event) {
                $monitorId = (int) $event['monitors_id'];
                if (count($history[$monitorId] ?? []) >= 5) {
                    continue;
                }
                $text = self::describe($event);
                $history[$monitorId][] = [
                    'date'   => self::date($event['date_creation']),
                    'label'  => Events::labels()[$event['type']] ?? $event['type'],
                    'detail' => $text['detail'],
                ];
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $view = self::monitorView($row, $native);
            $machine = $machines[(int) $row['machines_id']] ?? null;
            $desk = $machine ? self::deskOf($machine) : null;
            $view['machine'] = $machine ? $machine['label'] : '';
            $view['machine_url'] = $machine['computer_url'] ?? '';
            $view['desk'] = $desk['name'] ?? '';
            $view['history'] = $history[(int) $row['id']] ?? [];
            $out[] = $view;
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Alertas
    // ------------------------------------------------------------------

    /** Indices dos alertas abertos por mesa, por maquina e por porta. */
    private static function openEventsIndex(): array
    {
        global $DB;

        $index = ['desk' => [], 'machine' => [], 'port' => []];
        foreach ($DB->request(['FROM' => Settings::TABLE_EVENTS, 'WHERE' => ['status' => Events::OPEN], 'ORDER' => 'id DESC', 'LIMIT' => 2000]) as $event) {
            if ((int) $event['desks_id'] > 0) {
                $index['desk'][(int) $event['desks_id']][] = $event;
            }
            // Origem e destino: no monitor que mudou de mesa, as duas mesas avisam.
            $seen = [];
            foreach (['machines_id', 'to_machines_id', 'from_machines_id'] as $field) {
                $id = (int) $event[$field];
                if ($id > 0 && !isset($seen[$id])) {
                    $seen[$id] = true;
                    $index['machine'][$id][] = $event;
                }
            }
            if ($event['type'] === Events::COMPUTER_MOVED && (int) $event['from_switches_id'] > 0) {
                // A mesa de onde o computador saiu tambem mostra o aviso.
                $index['port'][(int) $event['from_switches_id'] . '|' . $event['from_port']][] = $event;
            }
            if ($event['type'] === Events::SHARED_PORT) {
                $index['port'][(int) $event['to_switches_id'] . '|' . $event['to_port']][] = $event;
            }
        }
        return $index;
    }

    public static function events(string $status = Events::OPEN, string $type = '', int $limit = 300): array
    {
        global $DB;

        $where = [];
        if ($status !== '' && $status !== 'all') {
            $where['status'] = $status;
        }
        if ($type !== '' && isset(Events::labels()[$type])) {
            $where['type'] = $type;
        }
        $out = [];
        foreach ($DB->request(['FROM' => Settings::TABLE_EVENTS, 'WHERE' => $where, 'ORDER' => 'id DESC', 'LIMIT' => $limit]) as $event) {
            $out[] = self::eventView($event);
        }
        return $out;
    }

    public static function countOpenEvents(): int
    {
        return countElementsInTable(Settings::TABLE_EVENTS, ['status' => Events::OPEN]);
    }

    public static function eventView(array $event): array
    {
        global $CFG_GLPI;

        $text = self::describe($event);
        return [
            'id'      => (int) $event['id'],
            'type'    => (string) $event['type'],
            'label'   => Events::labels()[$event['type']] ?? $event['type'],
            'status'  => (string) $event['status'],
            'status_label' => Events::statusLabels()[$event['status']] ?? $event['status'],
            'title'   => $text['title'],
            'detail'  => $text['detail'],
            'date'    => self::date($event['date_creation']),
            'resolved_at' => self::date($event['resolved_at']),
            'resolved_by' => (int) $event['users_id'] > 0 ? getUserName((int) $event['users_id']) : '',
            'ticket_url'  => (int) $event['tickets_id'] > 0 ? $CFG_GLPI['root_doc'] . '/front/ticket.form.php?id=' . (int) $event['tickets_id'] : '',
            'tickets_id'  => (int) $event['tickets_id'],
            'computer_url' => $text['computer_url'] ?? '',
            'can_authorize' => in_array($event['type'], [Events::COMPUTER_MOVED, Events::MONITOR_MOVED, Events::MONITOR_NEW], true),
        ];
    }

    /** @return array{title: string, detail: string, computers_id: int, computer_url: string} */
    public static function describe(array $event): array
    {
        $machine = self::machine((int) $event['machines_id']);
        $name = self::machineName($machine);
        $computersId = (int) ($machine['computers_id'] ?? 0);
        $detail = (string) $event['details'];

        switch ($event['type']) {
            case Events::COMPUTER_MOVED:
                $title = $name . ' mudou de mesa';
                // Sem porta de origem: a maquina estava vinculada a mesa pelo Wi-Fi.
                $origin = (int) $event['from_switches_id'] > 0
                    ? self::positionLabel((int) $event['from_switches_id'], (string) $event['from_port'])
                    : ($detail !== '' ? $detail : 'sem posição');
                $detail = 'De ' . $origin . ' para ' . self::positionLabel((int) $event['to_switches_id'], (string) $event['to_port']) . '.';
                break;
            case Events::MONITOR_MOVED:
                $title = 'Monitor ' . self::monitorName((int) $event['monitors_id']) . ' mudou de mesa';
                $from = self::machine((int) $event['from_machines_id']);
                $to = self::machine((int) $event['to_machines_id']);
                // Gravado no momento da troca; alertas antigos (sem o texto) montam agora.
                $detail = $detail !== ''
                    ? $detail
                    : 'Estava em ' . self::machineWhere($from) . '; agora está em ' . self::machineWhere($to) . '.';
                $computersId = (int) ($to['computers_id'] ?? $computersId);
                break;
            case Events::MONITOR_NEW:
                $title = 'Monitor novo: ' . self::monitorName((int) $event['monitors_id']);
                $to = self::machine((int) $event['to_machines_id']);
                $detail = 'Apareceu em ' . self::machineWhere($to) . '.';
                break;
            case Events::MONITOR_MISSING:
                $title = 'Monitor ' . self::monitorName((int) $event['monitors_id']) . ' ausente';
                $from = self::machine((int) $event['from_machines_id']);
                $detail = 'Não aparece mais em ' . self::machineWhere($from) . ($detail !== '' ? ' (' . $detail . ')' : '') . '.';
                $computersId = (int) ($from['computers_id'] ?? $computersId);
                break;
            case Events::SHARED_PORT:
                $title = 'Porta compartilhada: ' . self::positionLabel((int) $event['to_switches_id'], (string) $event['to_port']);
                $detail = ($detail !== '' ? $detail : 'Mais de uma máquina') . '. Provável mini switch ou cabo trocado.';
                break;
            case Events::DESK_EMPTY:
                $desk = self::desk((int) $event['desks_id']);
                $title = 'Mesa ' . ($desk['name'] ?? '#' . $event['desks_id']) . ' vazia';
                $detail = 'Nenhuma máquina na porta da mesa' . ($detail !== '' ? ' ' . $detail : '') . '.';
                break;
            case Events::MACHINE_SILENT:
                $title = $name . ' sem relatório';
                $detail = 'Último relatório: ' . self::date($machine['last_report'] ?? null) . '. Desligada, formatada ou retirada?';
                break;
            default:
                $title = (string) $event['type'];
        }

        return [
            'title'        => $title,
            'detail'       => $detail,
            'computers_id' => $computersId,
            'computer_url' => $computersId > 0 ? Computer::getFormURLWithID($computersId) : '',
        ];
    }

    private static function desk(int $id): ?array
    {
        global $DB;

        return $id > 0 ? ($DB->request(['FROM' => Settings::TABLE_DESKS, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() ?: null) : null;
    }

    private static function machineName(?array $machine): string
    {
        if (!$machine) {
            return 'Máquina';
        }
        if ((int) $machine['computers_id'] > 0) {
            $computer = new Computer();
            if ($computer->getFromDB((int) $machine['computers_id'])) {
                return (string) $computer->fields['name'];
            }
        }
        return (string) ($machine['hostname'] ?: $machine['machine_id']);
    }

    /** "PC-045 — mesa D3 (switch .43, porta 15)" ou "... — mesa B2 (Wi-Fi)". */
    public static function machineWhere(?array $machine): string
    {
        if (!$machine) {
            return 'máquina desconhecida';
        }
        $where = self::positionLabel((int) $machine['switches_id'], (string) $machine['port']);
        if ($where === 'sem posição') {
            $desk = self::deskOf($machine);
            $where = $desk ? 'mesa ' . $desk['name'] . ' (Wi-Fi)' : 'sem mesa definida';
        }
        return self::machineName($machine) . ' — ' . $where;
    }

    private static function monitorName(int $id): string
    {
        global $DB;

        $row = $id > 0 ? $DB->request(['FROM' => Settings::TABLE_MONITORS, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() : null;
        if (!$row) {
            return '#' . $id;
        }
        $model = trim((string) $row['model']) !== '' ? (string) $row['model'] : trim($row['manufacturer'] . ' ' . $row['product_code']);
        return $model . ($row['serial'] !== '' ? ' (série ' . $row['serial'] . ')' : '');
    }

    // ------------------------------------------------------------------
    // Aba do Computador
    // ------------------------------------------------------------------

    public static function forComputer(int $computerId): array
    {
        global $DB;

        $machines = self::decoratedMachines(['computers_id' => $computerId]);
        $machine = $machines[0] ?? null;
        $desk = $machine ? self::deskOf($machine) : null;
        $events = [];
        if ($machine) {
            foreach ($DB->request([
                'FROM'  => Settings::TABLE_EVENTS,
                'WHERE' => ['OR' => [
                    'machines_id'      => $machine['id'],
                    'from_machines_id' => $machine['id'],
                    'to_machines_id'   => $machine['id'],
                ]],
                'ORDER' => 'id DESC',
                'LIMIT' => 30,
            ]) as $event) {
                $events[] = self::eventView($event);
            }
        }
        return [
            'machine' => $machine,
            'desk'    => $desk ? ['name' => $desk['name'], 'plan' => $desk['plan_name'] ?? ''] : null,
            'events'  => $events,
        ];
    }

    public static function date(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $ts = strtotime((string) $value);
        return $ts === false ? '' : date('d/m/Y H:i', $ts);
    }
}
