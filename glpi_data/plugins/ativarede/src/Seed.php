<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

/**
 * Planta inicial: sala principal (Anexo), redesenhada vista de cima a partir
 * do "Layout - Anexo". Criada so na primeira instalacao; depois a T.I. ajusta
 * mesas, nomes e portas pela tela (reinstalar nao sobrescreve nada).
 *
 * Coordenadas em unidades da planta (viewBox 1800 x 420 do anexo.svg), no
 * centro de cada mesa. chair = lado onde fica a cadeira.
 */
final class Seed
{
    public const PLAN_NAME = 'Sala principal - Anexo';
    public const PLAN_BACKGROUND = 'anexo.svg';
    public const PLAN_WIDTH = 1800;
    public const PLAN_HEIGHT = 420;

    public static function install(): void
    {
        global $DB;

        if (countElementsInTable(Settings::TABLE_PLANS) > 0) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $DB->insert(Settings::TABLE_PLANS, [
            'name'          => self::PLAN_NAME,
            'background'    => self::PLAN_BACKGROUND,
            'width'         => self::PLAN_WIDTH,
            'height'        => self::PLAN_HEIGHT,
            'date_creation' => $now,
            'date_mod'      => $now,
        ]);
        $planId = (int) $DB->insertId();
        if ($planId <= 0) {
            return;
        }
        foreach (self::desks() as $desk) {
            $DB->insert(Settings::TABLE_DESKS, $desk + ['plans_id' => $planId, 'date_mod' => $now]);
        }
    }

    /** As 54 mesas do layout, da porta (esquerda) ate os armarios (direita). */
    public static function desks(): array
    {
        $desks = [];
        $add = static function (string $name, float $x, float $y, string $chair, float $w = 42, float $h = 46) use (&$desks): void {
            $desks[] = ['name' => $name, 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'chair' => $chair];
        };

        // Bloco A (junto a porta): 2 fileiras de 3, cadeiras para fora.
        foreach ([150, 194, 238] as $i => $x) {
            $add('A' . ($i + 1), $x, 170, 'up', 44, 42);
            $add('A' . ($i + 4), $x, 212, 'down', 44, 42);
        }
        // Bloco B: 2 mesas lado a lado, cadeiras nas pontas.
        $add('B1', 430, 160, 'left');
        $add('B2', 472, 160, 'right');
        // Bloco C: 2 x 2, cadeiras nas laterais.
        foreach ([173, 219] as $i => $y) {
            $add('C' . ($i + 1), 575, $y, 'left');
            $add('C' . ($i + 3), 617, $y, 'right');
        }
        // Ilhas D a J: 2 colunas x 3 fileiras. 1-3 = coluna da esquerda
        // (de cima para baixo), 4-6 = coluna da direita.
        foreach (range(0, 6) as $island) {
            $letter = chr(ord('D') + $island);
            $left = 700 + $island * 140;
            foreach ([150, 196, 242] as $row => $y) {
                $add($letter . ($row + 1), $left, $y, 'left');
                $add($letter . ($row + 4), $left + 42, $y, 'right');
            }
        }

        usort($desks, static fn(array $a, array $b): int => strnatcmp($a['name'], $b['name']));
        return $desks;
    }
}
