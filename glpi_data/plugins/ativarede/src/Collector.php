<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use GlpiPlugin\Ativaguardian\ActionQueue;
use Plugin;
use Session;

/**
 * "Atualizar agora" na planta: pede ao Ativa Guardian das maquinas ligadas
 * que colete e envie a posicao ja, sem esperar o ciclo de 15 min. Usa a fila
 * de acoes do Guardian (consultada pelas maquinas a cada 30 s). A acao so le
 * a rede e os monitores; nada e alterado na maquina.
 */
final class Collector
{
    /** Primeira versao do Guardian que entende a acao. */
    public const MIN_GUARDIAN = '1.6.4';
    public const COMPONENT = 'ativarede';
    public const ACTION = 'NETWORK_REPORT';

    /** @return array{ok: bool, message: string, requested?: int, outdated?: int} */
    public static function requestAll(): array
    {
        global $DB;

        if (!Plugin::isPluginActive('ativaguardian') || !class_exists(ActionQueue::class)
            || !in_array(self::ACTION, ActionQueue::ACTIONS, true)) {
            return ['ok' => false, 'message' => 'O Ativa Guardian precisa estar ativo e atualizado no GLPI para pedir a coleta.'];
        }
        if (!$DB->tableExists(Settings::TABLE_GUARDIAN_MACHINES)) {
            return ['ok' => false, 'message' => 'Tabela do Ativa Guardian não encontrada.'];
        }

        $online = date('Y-m-d H:i:s', time() - Settings::guardianOfflineSeconds());
        $requested = 0;
        $outdated = 0;
        $userId = (int) Session::getLoginUserID();
        // Todas as maquinas ligadas com Guardian, inclusive as que ainda nao
        // entraram no Ativa Rede: o pedido forcado tambem tira o Guardian da
        // espera apos um erro do GLPI (antes so ia para as ja conhecidas, e
        // uma maquina que nunca conseguiu enviar ficava de fora para sempre).
        $where = [Settings::TABLE_GUARDIAN_MACHINES . '.last_contact' => ['>=', $online]];
        if ($DB->fieldExists(Settings::TABLE_GUARDIAN_MACHINES, 'hidden_at')) {
            $where[Settings::TABLE_GUARDIAN_MACHINES . '.hidden_at'] = null;
        }
        foreach ($DB->request([
            'SELECT' => [Settings::TABLE_GUARDIAN_MACHINES . '.id', Settings::TABLE_GUARDIAN_MACHINES . '.guardian_version'],
            'FROM'   => Settings::TABLE_GUARDIAN_MACHINES,
            'WHERE'  => $where,
        ]) as $row) {
            $version = (string) $row['guardian_version'];
            if ($version === '' || version_compare($version, self::MIN_GUARDIAN, '<')) {
                $outdated++;
                continue;
            }
            if (ActionQueue::enqueue((int) $row['id'], self::COMPONENT, self::ACTION, $userId) > 0) {
                $requested++;
            }
        }

        if ($requested === 0) {
            return [
                'ok'       => true,
                'message'  => $outdated > 0
                    ? 'Nenhuma máquina com Guardian ' . self::MIN_GUARDIAN . ' ou mais novo está ligada agora (' . $outdated . ' com versão antiga).'
                    : 'Nenhuma máquina ligada agora para coletar.',
                'requested' => 0,
                'outdated'  => $outdated,
            ];
        }
        return [
            'ok'        => true,
            'message'   => 'Coleta pedida a ' . $requested . ' máquina(s) ligada(s). As posições chegam em 1 a 2 minutos; trocas de mesa são confirmadas cerca de 2 minutos depois.'
                . ($outdated > 0 ? ' ' . $outdated . ' máquina(s) com Guardian antigo ficaram de fora.' : ''),
            'requested' => $requested,
            'outdated'  => $outdated,
        ];
    }
}
