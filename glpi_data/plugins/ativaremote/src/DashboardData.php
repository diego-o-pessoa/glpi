<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

/**
 * Dados do painel (primeira renderizacao e atualizacao ao vivo por
 * front/clients.php). So campos exibidos: nada de token, GUID ou senha
 * criptografada. A senha da sessao so vai para quem pode gerenciar e nunca
 * para computadores protegidos (esses liberam pela senha do T.I.).
 */
final class DashboardData
{
    /** Tabela do Ativa Wallpaper: usuario logado de cada computador (por MachineGuid). */
    private const WALLPAPER_TABLE = 'glpi_plugin_ativawallpaper_clients';

    /** Teto de computadores no painel (a busca e a paginacao sao no navegador). */
    private const MAX_ROWS = 1000;

    /**
     * @return array{now: int, signature: string, counts: array<string, int>, clients: list<array<string, mixed>>}
     */
    public static function payload(bool $canManage): array
    {
        global $CFG_GLPI;

        $rows = (new ClientRepository())->listForDashboard(0, self::MAX_ROWS, $canManage);
        $users = self::loggedUsers(array_column($rows, 'machine_guid'));
        $computerUrl = $CFG_GLPI['root_doc'] . '/front/computer.form.php?id=';

        $clients = [];
        $counts = ['total' => 0, 'online' => 0, 'offline' => 0, 'session' => 0, 'protected' => 0];
        foreach ($rows as $row) {
            $status = $row['remote_access_status'] ?? null;
            $lastCheck = ServerClock::toTimestamp($row['last_check'] ?? null);
            $client = [
                'id'                => (int) $row['id'],
                'hostname'          => (string) $row['hostname'],
                'computer_url'      => (int) $row['computers_id'] > 0 ? $computerUrl . (int) $row['computers_id'] : '',
                'username'          => $users[strtolower((string) $row['machine_guid'])] ?? '',
                'client_version'    => (string) ($row['client_version'] ?? ''),
                'online'            => (bool) $row['online'],
                'last_check_ts'     => $lastCheck,
                'last_check_label'  => $lastCheck > 0 ? date('d/m/Y H:i', $lastCheck) : '',
                'rustdesk_id'       => (string) ($row['rustdesk_id'] ?? ''),
                'rustdesk_version'  => (string) ($row['rustdesk_version'] ?? ''),
                'rustdesk_message'  => (string) ($row['rustdesk_message'] ?? ''),
                'status'            => $status,
                'status_message'    => (string) ($row['status_message'] ?? ''),
                'protected'         => (bool) $row['protected'],
                'protection_reason' => (string) ($row['protection_reason'] ?? ''),
                'ti_verified'       => (bool) $row['ti_verified'],
                'require_consent'   => (bool) $row['require_consent'],
                'can_connect'       => (bool) $row['can_connect'],
                'password'          => $canManage ? ($row['password'] ?? null) : null,
                'connect_url'       => $canManage ? ($row['connect_url'] ?? null) : null,
                'linked'            => (int) $row['computers_id'] > 0,
            ];
            $clients[] = $client;

            $counts['total']++;
            $counts[$client['online'] ? 'online' : 'offline']++;
            if (in_array($status, [ClientRepository::STATUS_PENDING, ClientRepository::STATUS_ACCEPTED], true)) {
                $counts['session']++;
            }
            if ($client['protected']) {
                $counts['protected']++;
            }
        }

        // A assinatura so muda quando algo exibido muda (o "ha X min" e
        // calculado no navegador), entao a tabela nao pisca a cada consulta.
        $stable = array_map(static function (array $client): array {
            unset($client['last_check_ts'], $client['last_check_label']);
            return $client;
        }, $clients);

        return [
            'now'       => time(),
            'signature' => sha1((string) json_encode([$stable, $counts])),
            'counts'    => $counts,
            'clients'   => $clients,
        ];
    }

    /**
     * Usuario logado informado pelo Ativa Wallpaper, por MachineGuid (minusculo).
     * Sem o Wallpaper instalado, volta vazio e o painel so nao mostra o usuario.
     *
     * @param array<int, mixed> $machineGuids
     * @return array<string, string>
     */
    private static function loggedUsers(array $machineGuids): array
    {
        global $DB;

        $guids = array_values(array_unique(array_filter(array_map(
            static fn ($guid): string => strtolower(trim((string) $guid)),
            $machineGuids
        ))));
        if ($guids === [] || !$DB->tableExists(self::WALLPAPER_TABLE)) {
            return [];
        }
        $users = [];
        foreach ($DB->request([
            'SELECT' => ['machine_guid', 'username'],
            'FROM'   => self::WALLPAPER_TABLE,
            'WHERE'  => ['machine_guid' => $guids],
        ]) as $row) {
            $username = trim((string) ($row['username'] ?? ''));
            if ($username !== '') {
                $users[strtolower(trim((string) $row['machine_guid']))] = $username;
            }
        }
        return $users;
    }
}
