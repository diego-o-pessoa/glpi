<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Plugin;

/**
 * Liga a maquina (machine_guid do servico) ao computador do GLPI.
 *
 * O Workspace faz apenas leitura nas fontes de identidade ja existentes. Ele
 * nao pode depender do Ativa Remote: o executor e instalado pelo pacote
 * unificado e precisa funcionar mesmo quando o acesso remoto estiver inativo.
 * IDs conflitantes ou hostnames ambiguos sao recusados para nunca entregar uma
 * etapa de provisionamento ao computador errado.
 */
final class MachineIdentity
{
    /** computers_id a partir do machine_guid, ou 0 se nao encontrado/ambiguo. */
    public static function computerFromGuid(string $machineGuid): int
    {
        global $DB;

        $guid = mb_strtolower(trim($machineGuid));
        if (!preg_match('/^[a-f0-9-]{16,64}$/D', $guid)) {
            return 0;
        }

        $computerIds = [];
        $hostnames = [];

        foreach (self::identitySources() as $source) {
            if (!Plugin::isPluginActive($source['plugin']) || !$DB->tableExists($source['table'])) {
                continue;
            }

            $columns = ['hostname'];
            if ($source['has_computer_id']) {
                $columns[] = 'computers_id';
            }
            $row = $DB->request([
                'SELECT' => $columns,
                'FROM'   => $source['table'],
                'WHERE'  => ['machine_guid' => $guid],
                'LIMIT'  => 1,
            ])->current();
            if (!is_array($row)) {
                continue;
            }

            $hostname = trim((string) ($row['hostname'] ?? ''));
            if ($hostname !== '') {
                $hostnames[mb_strtoupper($hostname)] = $hostname;
            }

            $computersId = (int) ($row['computers_id'] ?? 0);
            if ($computersId > 0 && self::validComputer($computersId)) {
                $computerIds[$computersId] = true;
            }
        }

        // Fontes diferentes apontando para computadores diferentes significam
        // identidade inconsistente. O executor deve esperar a reconciliacao.
        if (count($computerIds) > 1) {
            return 0;
        }
        if (count($computerIds) === 1) {
            return (int) array_key_first($computerIds);
        }

        // O Updater nao armazena computers_id. Nesse caso usamos somente um
        // nome que corresponda de forma unica ao inventario do GLPI.
        foreach ($hostnames as $hostname) {
            $computersId = self::computerFromHostname($hostname);
            if ($computersId > 0) {
                $computerIds[$computersId] = true;
            }
        }

        return count($computerIds) === 1 ? (int) array_key_first($computerIds) : 0;
    }

    /**
     * @return list<array{plugin:string,table:string,has_computer_id:bool}>
     */
    private static function identitySources(): array
    {
        return [
            [
                'plugin'          => 'ativawallpaper',
                'table'           => 'glpi_plugin_ativawallpaper_clients',
                'has_computer_id' => true,
            ],
            [
                'plugin'          => 'ativaremote',
                'table'           => 'glpi_plugin_ativaremote_clients',
                'has_computer_id' => true,
            ],
            [
                'plugin'          => 'ativaupdater',
                'table'           => 'glpi_plugin_ativaupdater_clients',
                'has_computer_id' => false,
            ],
        ];
    }

    private static function validComputer(int $computersId): bool
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_computers',
            'WHERE'  => [
                'id'          => $computersId,
                'is_deleted'  => 0,
                'is_template' => 0,
            ],
            'LIMIT' => 1,
        ]);

        return count($iterator) === 1;
    }

    /** Retorna somente quando o hostname identifica um unico computador. */
    private static function computerFromHostname(string $hostname): int
    {
        global $DB;

        $hostname = trim($hostname);
        if ($hostname === '') {
            return 0;
        }

        // Primeiro preserva o hostname completo. Se o cliente reportar FQDN e
        // nao houver resultado, tenta o nome NetBIOS antes do primeiro ponto.
        $candidates = [$hostname];
        $short = explode('.', $hostname, 2)[0];
        if ($short !== '' && strcasecmp($short, $hostname) !== 0) {
            $candidates[] = $short;
        }

        foreach ($candidates as $candidate) {
            $iterator = $DB->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_computers',
                'WHERE'  => [
                    'name'        => $candidate,
                    'is_deleted'  => 0,
                    'is_template' => 0,
                ],
                'LIMIT' => 2,
            ]);
            if (count($iterator) === 1) {
                $row = $iterator->current();
                return is_array($row) ? (int) ($row['id'] ?? 0) : 0;
            }
            if (count($iterator) > 1) {
                return 0;
            }
        }

        return 0;
    }

    /**
     * URL do painel do Ativa Remote quando o computador esta registrado la.
     * O tecnico solicita o acesso e conecta pelo proprio painel (mecanismo
     * existente; nao duplicamos o fluxo). Vazio se indisponivel.
     */
    public static function remoteDashboardUrl(int $computersId): string
    {
        global $DB, $CFG_GLPI;

        if ($computersId <= 0 || !Plugin::isPluginActive('ativaremote')
            || !$DB->tableExists('glpi_plugin_ativaremote_clients')) {
            return '';
        }
        $found = countElementsInTable('glpi_plugin_ativaremote_clients', ['computers_id' => $computersId]) > 0;
        return $found ? $CFG_GLPI['root_doc'] . '/plugins/ativaremote/front/dashboard.php' : '';
    }
}
