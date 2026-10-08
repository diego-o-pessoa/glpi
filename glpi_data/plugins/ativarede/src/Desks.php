<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

/**
 * Edicao das mesas pela tela da planta (direito "Editar planta"). Cada porta
 * de switch pertence a no maximo uma mesa.
 */
final class Desks
{
    private const CHAIRS = ['up', 'down', 'left', 'right'];

    public static function save(array $input): array
    {
        global $DB;

        $desk = self::find((int) ($input['id'] ?? 0));
        if (!$desk) {
            return ['ok' => false, 'message' => 'Mesa não encontrada.'];
        }

        $name = mb_substr(trim((string) ($input['name'] ?? '')), 0, 100);
        if ($name === '') {
            return ['ok' => false, 'message' => 'Informe o nome da mesa.'];
        }
        $switchId = (int) ($input['switches_id'] ?? 0);
        $port = mb_substr(trim((string) ($input['port'] ?? '')), 0, 64);
        if ($switchId > 0 && !isset(Inventory::switches()[$switchId])) {
            return ['ok' => false, 'message' => 'Switch inválido.'];
        }
        if (($switchId > 0) !== ($port !== '')) {
            return ['ok' => false, 'message' => 'Informe o switch e a porta juntos (ou deixe os dois vazios).'];
        }
        if ($switchId > 0) {
            $other = $DB->request([
                'SELECT' => ['name'],
                'FROM'   => Settings::TABLE_DESKS,
                'WHERE'  => ['switches_id' => $switchId, 'port' => $port, 'NOT' => ['id' => (int) $desk['id']]],
                'LIMIT'  => 1,
            ])->current();
            if ($other) {
                return ['ok' => false, 'message' => 'Esta porta já é da mesa ' . $other['name'] . '.'];
            }
        }

        // Maquina sem porta (Wi-Fi) vinculada direto. Porta definida tem
        // prioridade: com porta, o vinculo direto e desfeito.
        $machineId = $switchId > 0 ? 0 : (int) ($input['machines_id'] ?? $desk['machines_id'] ?? 0);
        if ($machineId > 0) {
            if (!Inventory::machine($machineId)) {
                return ['ok' => false, 'message' => 'Máquina não encontrada.'];
            }
            $other = $DB->request([
                'SELECT' => ['name'],
                'FROM'   => Settings::TABLE_DESKS,
                'WHERE'  => ['machines_id' => $machineId, 'NOT' => ['id' => (int) $desk['id']]],
                'LIMIT'  => 1,
            ])->current();
            if ($other) {
                return ['ok' => false, 'message' => 'Esta máquina já está na mesa ' . $other['name'] . '.'];
            }
        }

        $chair = (string) ($input['chair'] ?? $desk['chair']);
        if (!in_array($chair, self::CHAIRS, true)) {
            $chair = (string) $desk['chair'];
        }
        $w = self::bounded((float) ($input['w'] ?? $desk['w']), 16, 200);
        $h = self::bounded((float) ($input['h'] ?? $desk['h']), 16, 200);

        $DB->update(Settings::TABLE_DESKS, [
            'name'        => $name,
            'switches_id' => $switchId,
            'port'        => $port,
            'machines_id' => $machineId,
            'chair'       => $chair,
            'w'           => $w,
            'h'           => $h,
            'comment'     => mb_substr(trim((string) ($input['comment'] ?? '')), 0, 255),
            'date_mod'    => date('Y-m-d H:i:s'),
        ], ['id' => (int) $desk['id']]);

        // Porta nova com maquina: a mesa deixa de estar "vazia".
        if ($switchId > 0 || $machineId > 0) {
            Events::clear(Events::DESK_EMPTY, ['desks_id' => (int) $desk['id']]);
        }
        return ['ok' => true, 'message' => 'Mesa ' . $name . ' salva.', 'desk_id' => (int) $desk['id']];
    }

    public static function move(int $id, float $x, float $y): array
    {
        global $DB;

        $desk = self::find($id);
        if (!$desk) {
            return ['ok' => false, 'message' => 'Mesa não encontrada.'];
        }
        $plan = self::plan((int) $desk['plans_id']);
        $DB->update(Settings::TABLE_DESKS, [
            'x'        => round(self::bounded($x, 0, (float) ($plan['width'] ?? 1800)), 2),
            'y'        => round(self::bounded($y, 0, (float) ($plan['height'] ?? 420)), 2),
            'date_mod' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
        return ['ok' => true, 'message' => 'Posição salva.', 'desk_id' => $id];
    }

    public static function add(int $planId, float $x, float $y): array
    {
        global $DB;

        $plan = self::plan($planId);
        if (!$plan) {
            return ['ok' => false, 'message' => 'Planta não encontrada.'];
        }
        $existing = countElementsInTable(Settings::TABLE_DESKS, ['plans_id' => $planId]);
        $name = 'Nova ' . ($existing + 1);
        $DB->insert(Settings::TABLE_DESKS, [
            'plans_id' => $planId,
            'name'     => $name,
            'x'        => round(self::bounded($x, 0, (float) $plan['width']), 2),
            'y'        => round(self::bounded($y, 0, (float) $plan['height']), 2),
            'w'        => 42,
            'h'        => 46,
            'chair'    => 'down',
            'date_mod' => date('Y-m-d H:i:s'),
        ]);
        return ['ok' => true, 'message' => 'Mesa ' . $name . ' criada. Arraste-a e defina o nome e a porta.', 'desk_id' => (int) $DB->insertId()];
    }

    public static function delete(int $id): array
    {
        global $DB;

        $desk = self::find($id);
        if (!$desk) {
            return ['ok' => false, 'message' => 'Mesa não encontrada.'];
        }
        $DB->delete(Settings::TABLE_DESKS, ['id' => $id]);
        Events::clear(Events::DESK_EMPTY, ['desks_id' => $id]);
        return ['ok' => true, 'message' => 'Mesa ' . $desk['name'] . ' removida.'];
    }

    /** Apelido curto do switch (aparece em cima de cada mesa). */
    public static function saveSwitchLabel(int $id, string $label): array
    {
        global $DB;

        if (!isset(Inventory::switches()[$id])) {
            return ['ok' => false, 'message' => 'Switch não encontrado.'];
        }
        $label = mb_substr(trim($label), 0, 64);
        $DB->update(Settings::TABLE_SWITCHES, ['label' => $label], ['id' => $id]);
        Inventory::resetCache();
        return ['ok' => true, 'message' => 'Switch renomeado.'];
    }

    private static function find(int $id): ?array
    {
        global $DB;

        return $id > 0 ? ($DB->request(['FROM' => Settings::TABLE_DESKS, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() ?: null) : null;
    }

    private static function plan(int $id): ?array
    {
        global $DB;

        return $id > 0 ? ($DB->request(['FROM' => Settings::TABLE_PLANS, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() ?: null) : null;
    }

    private static function bounded(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
