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
 *
 * Primeiro as maquinas que ainda nao estao em Equipamentos (o motivo de quem
 * clica e achar o equipamento novo); sem nenhuma nova, todas as ligadas. O
 * pedido fica guardado na sessao para a tela acompanhar quem ja respondeu e
 * parar assim que todas responderem (antes eram 4 minutos fixos).
 */
final class Collector
{
    /** Primeira versao do Guardian que entende a acao. */
    public const MIN_GUARDIAN = '1.6.4';
    public const COMPONENT = 'ativarede';
    public const ACTION = 'NETWORK_REPORT';

    public const SCOPE_NEW = 'new';
    public const SCOPE_ALL = 'all';

    /**
     * "Ligada agora": heartbeat recente (sai a cada 30 s). O limite de
     * offline do Guardian (2 h) incluia maquinas desligadas, que nunca
     * respondiam e faziam a tela esperar por elas.
     */
    public const RESPONSIVE_SECONDS = 180;
    /** Depois disso o acompanhamento termina e diz quem nao respondeu. */
    public const WATCH_SECONDS = 210;
    private const SESSION_KEY = 'plugin_ativarede_collect';

    /** @return array<string, mixed> */
    public static function request(string $scope = self::SCOPE_NEW): array
    {
        global $DB;

        if (!Plugin::isPluginActive('ativaguardian') || !class_exists(ActionQueue::class)
            || !in_array(self::ACTION, ActionQueue::ACTIONS, true)) {
            return ['ok' => false, 'message' => 'O Ativa Guardian precisa estar ativo e atualizado no GLPI para pedir a coleta.'];
        }
        if (!$DB->tableExists(Settings::TABLE_GUARDIAN_MACHINES)) {
            return ['ok' => false, 'message' => 'Tabela do Ativa Guardian não encontrada.'];
        }

        // Posicao ja conhecida no Ativa Rede (machine_id => ultimo relatorio).
        $known = [];
        foreach ($DB->request(['SELECT' => ['machine_id', 'last_report'], 'FROM' => Settings::TABLE_MACHINES]) as $row) {
            $known[(string) $row['machine_id']] = (string) ($row['last_report'] ?? '');
        }

        // Todas as maquinas ligadas com Guardian, inclusive as que ainda nao
        // entraram no Ativa Rede: o pedido forcado tambem tira o Guardian da
        // espera apos um erro do GLPI.
        // Mesmo relogio com que o Guardian grava last_contact (o fuso do
        // usuario logado pode ser outro).
        $online = class_exists(\GlpiPlugin\Ativaguardian\ServerClock::class)
            ? \GlpiPlugin\Ativaguardian\ServerClock::format(time() - self::RESPONSIVE_SECONDS)
            : date('Y-m-d H:i:s', time() - self::RESPONSIVE_SECONDS);
        $where = [Settings::TABLE_GUARDIAN_MACHINES . '.last_contact' => ['>=', $online]];
        if ($DB->fieldExists(Settings::TABLE_GUARDIAN_MACHINES, 'hidden_at')) {
            $where[Settings::TABLE_GUARDIAN_MACHINES . '.hidden_at'] = null;
        }
        $all = [];
        $fresh = [];
        $outdated = 0;
        foreach ($DB->request([
            'SELECT' => ['id', 'machine_id', 'hostname', 'guardian_version'],
            'FROM'   => Settings::TABLE_GUARDIAN_MACHINES,
            'WHERE'  => $where,
        ]) as $row) {
            $version = (string) $row['guardian_version'];
            if ($version === '' || version_compare($version, self::MIN_GUARDIAN, '<')) {
                $outdated++;
                continue;
            }
            $all[] = $row;
            if (!isset($known[(string) $row['machine_id']])) {
                $fresh[] = $row;
            }
        }

        $usedScope = $scope === self::SCOPE_ALL || $fresh === [] ? self::SCOPE_ALL : self::SCOPE_NEW;
        $targets = $usedScope === self::SCOPE_NEW ? $fresh : $all;

        $rejections = Schema::rejections();
        $userId = (int) Session::getLoginUserID();
        $watch = [];
        foreach ($targets as $row) {
            if (ActionQueue::enqueue((int) $row['id'], self::COMPONENT, self::ACTION, $userId) > 0) {
                $machineId = (string) $row['machine_id'];
                $watch[$machineId] = [
                    'host'     => (string) $row['hostname'],
                    'before'   => $known[$machineId] ?? null,
                    'rejected' => $rejections[$machineId]['last_at'] ?? null,
                ];
            }
        }
        $_SESSION[self::SESSION_KEY] = ['at' => time(), 'machines' => $watch];

        $requested = count($watch);
        $old = $outdated > 0 ? ' ' . $outdated . ' máquina(s) com Guardian antigo ficaram de fora.' : '';
        if ($requested === 0) {
            return [
                'ok'        => true,
                'message'   => ($outdated > 0
                    ? 'Nenhuma máquina com Guardian ' . self::MIN_GUARDIAN . ' ou mais novo está ligada agora (' . $outdated . ' com versão antiga).'
                    : 'Nenhuma máquina ligada agora para coletar.'),
                'requested' => 0,
                'outdated'  => $outdated,
                'scope'     => $usedScope,
            ];
        }
        if ($usedScope === self::SCOPE_NEW) {
            $hosts = array_column(array_slice($fresh, 0, 5), 'hostname');
            $message = 'Procurando ' . $requested . ' máquina(s) nova(s): ' . implode(', ', $hosts)
                . ($requested > 5 ? '…' : '') . '. A posição costuma chegar em 1 a 2 minutos.';
        } else {
            $message = ($scope === self::SCOPE_NEW ? 'Nenhuma máquina nova ligada; ' : '')
                . 'Coleta pedida a ' . $requested . ' máquina(s) ligada(s). As posições chegam em 1 a 2 minutos.';
        }
        return [
            'ok'        => true,
            'message'   => $message . $old,
            'requested' => $requested,
            'outdated'  => $outdated,
            'scope'     => $usedScope,
            'watch_seconds' => self::WATCH_SECONDS,
        ];
    }

    /**
     * Quantas das maquinas do ultimo pedido (desta sessao) ja responderam:
     * relatorio novo no Ativa Rede, ou recusado pelo GLPI (com o motivo).
     *
     * @return array<string, mixed>
     */
    public static function status(): array
    {
        global $DB;

        $request = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($request) || !is_array($request['machines'] ?? null) || $request['machines'] === []) {
            return ['ok' => true, 'total' => 0, 'done' => 0, 'finished' => true, 'waiting' => [], 'rejected' => []];
        }
        $machines = $request['machines'];
        $ids = array_map('strval', array_keys($machines));

        $now = [];
        foreach ($DB->request([
            'SELECT' => ['machine_id', 'last_report'],
            'FROM'   => Settings::TABLE_MACHINES,
            'WHERE'  => ['machine_id' => $ids],
        ]) as $row) {
            $now[(string) $row['machine_id']] = (string) ($row['last_report'] ?? '');
        }
        $rejections = Schema::rejections();
        $startedAt = (int) ($request['at'] ?? 0);

        $done = 0;
        $waiting = [];
        $rejected = [];
        foreach ($machines as $machineId => $info) {
            $machineId = (string) $machineId;
            $before = $info['before'] ?? null;
            if (isset($now[$machineId]) && ($before === null || $now[$machineId] !== $before)) {
                $done++;
                continue;
            }
            $rejection = $rejections[$machineId] ?? null;
            // Recusa nova (depois do pedido): a maquina respondeu, o GLPI recusou.
            if ($rejection !== null && $rejection['last_at'] !== ($info['rejected'] ?? null)) {
                $done++;
                $rejected[] = ['host' => (string) $info['host'], 'message' => $rejection['message']];
                continue;
            }
            $waiting[] = (string) $info['host'];
        }
        $total = count($machines);
        sort($waiting);
        return [
            'ok'       => true,
            'total'    => $total,
            'done'     => $done,
            'finished' => $done >= $total || time() - $startedAt >= self::WATCH_SECONDS,
            'waiting'  => array_slice($waiting, 0, 8),
            'waiting_count' => count($waiting),
            'rejected' => array_slice($rejected, 0, 5),
        ];
    }
}
