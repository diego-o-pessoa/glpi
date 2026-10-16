<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

/**
 * Divisorias e portas desenhadas na tela da planta (direito "Editar planta").
 * Cada uma e um segmento de (x1, y1) a (x2, y2) em unidades da planta. Na
 * porta, (x1, y1) e a dobradica e flip escolhe o lado para onde ela abre.
 */
final class Walls
{
    public const KINDS = ['wall', 'glass', 'door'];

    private const NAMES = ['wall' => 'Divisória', 'glass' => 'Divisória de vidro', 'door' => 'Porta'];
    private const MIN_LENGTH = 8;
    private const MAX_DOOR = 160;

    /** @return list<array{id: int, kind: string, x1: float, y1: float, x2: float, y2: float, flip: int}> */
    public static function forPlan(int $planId): array
    {
        global $DB;

        if ($planId <= 0 || !$DB->tableExists(Settings::TABLE_WALLS)) {
            return [];
        }
        $out = [];
        foreach ($DB->request(['FROM' => Settings::TABLE_WALLS, 'WHERE' => ['plans_id' => $planId], 'ORDER' => 'id']) as $row) {
            $out[] = [
                'id'   => (int) $row['id'],
                'kind' => in_array($row['kind'], self::KINDS, true) ? (string) $row['kind'] : 'wall',
                'x1'   => (float) $row['x1'],
                'y1'   => (float) $row['y1'],
                'x2'   => (float) $row['x2'],
                'y2'   => (float) $row['y2'],
                'flip' => (int) $row['flip'],
            ];
        }
        return $out;
    }

    public static function add(int $planId, array $input): array
    {
        global $DB;

        $plan = self::plan($planId);
        if (!$plan) {
            return ['ok' => false, 'message' => 'Planta não encontrada.'];
        }
        $fields = self::clean($input, $plan);
        if (is_string($fields)) {
            return ['ok' => false, 'message' => $fields];
        }
        $DB->insert(Settings::TABLE_WALLS, $fields + ['plans_id' => $planId, 'date_mod' => date('Y-m-d H:i:s')]);
        return ['ok' => true, 'message' => self::NAMES[$fields['kind']] . ' adicionada.', 'wall_id' => (int) $DB->insertId()];
    }

    public static function save(array $input): array
    {
        global $DB;

        $wall = self::find((int) ($input['id'] ?? 0));
        $plan = $wall ? self::plan((int) $wall['plans_id']) : null;
        if (!$wall || !$plan) {
            return ['ok' => false, 'message' => 'Divisória não encontrada.'];
        }
        $fields = self::clean($input + $wall, $plan);
        if (is_string($fields)) {
            return ['ok' => false, 'message' => $fields];
        }
        $DB->update(Settings::TABLE_WALLS, $fields + ['date_mod' => date('Y-m-d H:i:s')], ['id' => (int) $wall['id']]);
        return ['ok' => true, 'message' => self::NAMES[$fields['kind']] . ' salva.', 'wall_id' => (int) $wall['id']];
    }

    public static function delete(int $id): array
    {
        global $DB;

        $wall = self::find($id);
        if (!$wall) {
            return ['ok' => false, 'message' => 'Divisória não encontrada.'];
        }
        $DB->delete(Settings::TABLE_WALLS, ['id' => $id]);
        return ['ok' => true, 'message' => (self::NAMES[$wall['kind']] ?? 'Divisória') . ' removida.'];
    }

    /** @return array<string, mixed>|string Campos validados ou a mensagem de erro. */
    private static function clean(array $input, array $plan): array|string
    {
        $kind = (string) ($input['kind'] ?? 'wall');
        if (!in_array($kind, self::KINDS, true)) {
            return 'Tipo inválido.';
        }
        $width = (float) $plan['width'];
        $height = (float) $plan['height'];
        $point = static fn(string $key, float $max): float => round(max(0.0, min($max, (float) ($input[$key] ?? 0))), 2);
        $fields = [
            'kind' => $kind,
            'x1'   => $point('x1', $width),
            'y1'   => $point('y1', $height),
            'x2'   => $point('x2', $width),
            'y2'   => $point('y2', $height),
            'flip' => empty($input['flip']) ? 0 : 1,
        ];
        $length = hypot($fields['x2'] - $fields['x1'], $fields['y2'] - $fields['y1']);
        if ($length < self::MIN_LENGTH) {
            return 'Divisória curta demais: arraste de uma ponta à outra.';
        }
        if ($kind === 'door' && $length > self::MAX_DOOR) {
            return 'Porta larga demais. Desenhe só o vão da porta (de um batente ao outro).';
        }
        return $fields;
    }

    private static function find(int $id): ?array
    {
        global $DB;

        return $id > 0 ? ($DB->request(['FROM' => Settings::TABLE_WALLS, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() ?: null) : null;
    }

    private static function plan(int $id): ?array
    {
        global $DB;

        return $id > 0 ? ($DB->request(['FROM' => Settings::TABLE_PLANS, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() ?: null) : null;
    }
}
