<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Plugin;

/**
 * Liga a maquina (machine_guid do servico) ao computador do GLPI.
 *
 * O Ativa Updater nao guarda computers_id; o Ativa Remote guarda. Como a etapa
 * ENTRA_LOGIN depende do Ativa Remote para a sessao, usamos a tabela dele para
 * o mapeamento. So leitura: o Workspace nunca escreve nas tabelas dos outros.
 */
final class MachineIdentity
{
    /** computers_id a partir do machine_guid, ou 0 se nao encontrado/ambiguo. */
    public static function computerFromGuid(string $machineGuid): int
    {
        global $DB;

        $guid = mb_strtolower(trim($machineGuid));
        if ($guid === '' || !Plugin::isPluginActive('ativaremote')) {
            return 0;
        }
        if (!$DB->tableExists('glpi_plugin_ativaremote_clients')) {
            return 0;
        }

        $row = $DB->request([
            'SELECT' => ['computers_id'],
            'FROM'   => 'glpi_plugin_ativaremote_clients',
            'WHERE'  => ['machine_guid' => $guid],
            'LIMIT'  => 1,
        ])->current();

        return is_array($row) ? (int) ($row['computers_id'] ?? 0) : 0;
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
