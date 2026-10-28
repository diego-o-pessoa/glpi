<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

/**
 * Plantas criadas e editadas pela tela (direito "Editar planta"). Planta
 * nova nasce sem desenho de fundo (quadriculado) e com as quatro paredes
 * externas como divisorias, que a T.I. move, estica ou apaga como quiser.
 */
final class Plans
{
    private const MIN_W = 400;
    private const MAX_W = 4000;
    private const MIN_H = 200;
    private const MAX_H = 2000;
    /** Distancia das paredes externas ate a borda. */
    private const MARGIN = 20;

    public static function add(array $input): array
    {
        global $DB;

        $fields = self::clean($input);
        if (is_string($fields)) {
            return ['ok' => false, 'message' => $fields];
        }
        if (countElementsInTable(Settings::TABLE_PLANS, ['name' => $fields['name']]) > 0) {
            return ['ok' => false, 'message' => 'Já existe uma planta chamada ' . $fields['name'] . '.'];
        }
        $now = date('Y-m-d H:i:s');
        $DB->insert(Settings::TABLE_PLANS, $fields + ['background' => '', 'date_creation' => $now, 'date_mod' => $now]);
        $planId = (int) $DB->insertId();
        if ($planId <= 0) {
            return ['ok' => false, 'message' => 'Não foi possível criar a planta.'];
        }
        self::outerWalls($planId, (int) $fields['width'], (int) $fields['height'], $now);
        return ['ok' => true, 'message' => 'Planta ' . $fields['name'] . ' criada. Desenhe as divisórias e adicione as mesas.', 'plan_id' => $planId];
    }

    public static function save(int $id, array $input): array
    {
        global $DB;

        $plan = self::find($id);
        if (!$plan) {
            return ['ok' => false, 'message' => 'Planta não encontrada.'];
        }
        $fields = self::clean($input);
        if (is_string($fields)) {
            return ['ok' => false, 'message' => $fields];
        }
        if (countElementsInTable(Settings::TABLE_PLANS, ['name' => $fields['name'], 'NOT' => ['id' => $id]]) > 0) {
            return ['ok' => false, 'message' => 'Já existe outra planta chamada ' . $fields['name'] . '.'];
        }
        $DB->update(Settings::TABLE_PLANS, $fields + ['date_mod' => date('Y-m-d H:i:s')], ['id' => $id]);
        // Planta menor: mesas e divisorias que ficaram para fora voltam para dentro.
        foreach ($DB->request(['SELECT' => ['id', 'x', 'y'], 'FROM' => Settings::TABLE_DESKS, 'WHERE' => ['plans_id' => $id]]) as $desk) {
            $x = min((float) $desk['x'], (float) $fields['width']);
            $y = min((float) $desk['y'], (float) $fields['height']);
            if ($x !== (float) $desk['x'] || $y !== (float) $desk['y']) {
                $DB->update(Settings::TABLE_DESKS, ['x' => $x, 'y' => $y], ['id' => (int) $desk['id']]);
            }
        }
        if ($DB->tableExists(Settings::TABLE_WALLS)) {
            foreach ($DB->request(['FROM' => Settings::TABLE_WALLS, 'WHERE' => ['plans_id' => $id]]) as $wall) {
                $clamped = [
                    'x1' => min((float) $wall['x1'], (float) $fields['width']), 'x2' => min((float) $wall['x2'], (float) $fields['width']),
                    'y1' => min((float) $wall['y1'], (float) $fields['height']), 'y2' => min((float) $wall['y2'], (float) $fields['height']),
                ];
                if ($clamped !== ['x1' => (float) $wall['x1'], 'x2' => (float) $wall['x2'], 'y1' => (float) $wall['y1'], 'y2' => (float) $wall['y2']]) {
                    $DB->update(Settings::TABLE_WALLS, $clamped, ['id' => (int) $wall['id']]);
                }
            }
        }
        return ['ok' => true, 'message' => 'Planta ' . $fields['name'] . ' salva.', 'plan_id' => $id];
    }

    /** Apaga a planta com as mesas e divisorias dela (o historico de alertas fica). */
    public static function delete(int $id): array
    {
        global $DB;

        $plan = self::find($id);
        if (!$plan) {
            return ['ok' => false, 'message' => 'Planta não encontrada.'];
        }
        if (countElementsInTable(Settings::TABLE_PLANS) <= 1) {
            return ['ok' => false, 'message' => 'Esta é a única planta: crie outra antes de excluir.'];
        }
        $DB->delete(Settings::TABLE_DESKS, ['plans_id' => $id]);
        if ($DB->tableExists(Settings::TABLE_WALLS)) {
            $DB->delete(Settings::TABLE_WALLS, ['plans_id' => $id]);
        }
        $DB->delete(Settings::TABLE_PLANS, ['id' => $id]);
        return ['ok' => true, 'message' => 'Planta ' . $plan['name'] . ' excluída.'];
    }

    /** Quatro paredes externas (divisorias editaveis) de uma planta nova. */
    public static function outerWalls(int $planId, int $width, int $height, string $now): void
    {
        global $DB;

        if (!$DB->tableExists(Settings::TABLE_WALLS)) {
            return;
        }
        $m = self::MARGIN;
        $r = $width - $m;
        $b = $height - $m;
        foreach ([[$m, $m, $r, $m], [$r, $m, $r, $b], [$r, $b, $m, $b], [$m, $b, $m, $m]] as [$x1, $y1, $x2, $y2]) {
            $DB->insert(Settings::TABLE_WALLS, [
                'plans_id' => $planId, 'kind' => 'wall', 'x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2,
                'flip' => 0, 'date_mod' => $now,
            ]);
        }
    }

    /** @return array<string, mixed>|string Campos validados ou a mensagem de erro. */
    private static function clean(array $input): array|string
    {
        $name = mb_substr(trim((string) preg_replace('/\s+/u', ' ', (string) ($input['name'] ?? ''))), 0, 100);
        if ($name === '') {
            return 'Informe o nome da planta.';
        }
        $label = mb_substr(trim((string) ($input['label'] ?? '')), 0, 64);
        $width = (int) ($input['width'] ?? 1600);
        $height = (int) ($input['height'] ?? 500);
        if ($width < self::MIN_W || $width > self::MAX_W || $height < self::MIN_H || $height > self::MAX_H) {
            return 'Tamanho entre ' . self::MIN_W . '–' . self::MAX_W . ' (largura) e ' . self::MIN_H . '–' . self::MAX_H . ' (altura).';
        }
        return ['name' => $name, 'label' => $label, 'width' => $width, 'height' => $height];
    }

    private static function find(int $id): ?array
    {
        global $DB;

        return $id > 0 ? ($DB->request(['FROM' => Settings::TABLE_PLANS, 'WHERE' => ['id' => $id], 'LIMIT' => 1])->current() ?: null) : null;
    }
}
