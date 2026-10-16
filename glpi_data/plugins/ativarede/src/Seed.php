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

    /** Setor de cada bloco da sala principal (pela letra da mesa). */
    public const ANEXO_SECTORS = [
        'A' => 'T.I.',
        'B' => 'Estratégia',
        'C' => 'Estratégia',
        'D' => 'Suporte',
        'E' => 'Suporte',
        'F' => 'Comercial',
        'G' => 'Comercial',
        'H' => 'Comercial',
        'I' => 'Backoffice',
        'J' => 'Licitação',
    ];

    /** Excecoes por mesa (vencem a regra da letra). */
    public const ANEXO_DESK_SECTORS = [
        'F1' => 'Suporte',
        'F2' => 'Suporte',
        'F3' => 'Suporte',
    ];

    /**
     * 0.1.9: a coluna F1-F3 passou do Comercial para o Suporte. Roda uma vez
     * (marca em glpi_configs) e so muda mesas que ainda estao como Comercial,
     * para nao desfazer um ajuste posterior feito na tela.
     */
    public static function applyAnexoFixups(): void
    {
        global $DB;

        $done = \Config::getConfigurationValues(Settings::CONTEXT, ['fixup_f1f3_suporte']);
        if (!empty($done['fixup_f1f3_suporte'])) {
            return;
        }
        $plan = $DB->request(['SELECT' => ['id'], 'FROM' => Settings::TABLE_PLANS, 'WHERE' => ['name' => self::PLAN_NAME], 'LIMIT' => 1])->current();
        if ($plan) {
            $DB->update(
                Settings::TABLE_DESKS,
                ['sector' => 'Suporte'],
                ['plans_id' => (int) $plan['id'], 'name' => array_keys(self::ANEXO_DESK_SECTORS), 'sector' => 'Comercial']
            );
        }
        \Config::setConfigurationValues(Settings::CONTEXT, ['fixup_f1f3_suporte' => '1']);
    }

    /**
     * Preenche o setor das mesas da sala principal pela letra do nome
     * ("F3" -> Comercial). So mesas ainda sem setor: o que a T.I. definir na
     * tela nunca e sobrescrito. Mesas renomeadas fora do padrao ficam como estao.
     */
    public static function applyAnexoSectors(): void
    {
        global $DB;

        $plan = $DB->request(['SELECT' => ['id'], 'FROM' => Settings::TABLE_PLANS, 'WHERE' => ['name' => self::PLAN_NAME], 'LIMIT' => 1])->current();
        if (!$plan) {
            return;
        }
        foreach ($DB->request([
            'SELECT' => ['id', 'name'],
            'FROM'   => Settings::TABLE_DESKS,
            'WHERE'  => ['plans_id' => (int) $plan['id'], 'sector' => ''],
        ]) as $desk) {
            $name = strtoupper(trim((string) $desk['name']));
            $sector = self::ANEXO_DESK_SECTORS[$name] ?? null;
            if ($sector === null && preg_match('/^([A-Z])\d+$/', $name, $m)) {
                $sector = self::ANEXO_SECTORS[$m[1]] ?? null;
            }
            if ($sector !== null) {
                $DB->update(Settings::TABLE_DESKS, ['sector' => $sector], ['id' => (int) $desk['id']]);
            }
        }
    }

    // ------------------------------------------------------------------
    // 2º andar da Matriz (o Anexo e o 1º andar)
    // ------------------------------------------------------------------

    public const FLOOR2_NAME = '2º andar - Matriz';
    public const FLOOR2_LABEL = '2º andar';
    public const FLOOR2_BACKGROUND = 'matriz-2andar.svg';
    public const FLOOR2_WIDTH = 1800;
    public const FLOOR2_HEIGHT = 450;
    public const ANEXO_LABEL = '1º andar · Anexo';

    /**
     * Cria a planta do 2º andar com as mesas, as salas do DP e do RH e as
     * portas. Uma vez so: reconhece a planta pelo desenho de fundo, entao
     * renomear ou mexer nas mesas pela tela nunca e desfeito.
     */
    public static function ensureSecondFloor(): void
    {
        global $DB;

        $plans = Settings::TABLE_PLANS;
        // Instalacao nova: o Anexo vem antes (Seed::install), para ser a primeira planta.
        if (!$DB->tableExists($plans) || !$DB->tableExists(Settings::TABLE_WALLS)
            || !$DB->fieldExists($plans, 'label') || countElementsInTable($plans) === 0) {
            return;
        }
        if (countElementsInTable($plans, ['background' => self::FLOOR2_BACKGROUND]) > 0) {
            return;
        }

        // Duas abas abertas ao mesmo tempo logo apos a atualizacao nao criam duas plantas.
        $lock = $DB->doQuery("SELECT GET_LOCK('ativarede_seed_floor2', 5) AS l")->fetch_assoc();
        if ((int) ($lock['l'] ?? 0) !== 1) {
            return;
        }
        try {
            if (countElementsInTable($plans, ['background' => self::FLOOR2_BACKGROUND]) > 0) {
                return;
            }
            $now = date('Y-m-d H:i:s');
            $DB->update($plans, ['label' => self::ANEXO_LABEL], ['name' => self::PLAN_NAME, 'label' => '']);
            $DB->insert($plans, [
                'name'          => self::FLOOR2_NAME,
                'label'         => self::FLOOR2_LABEL,
                'background'    => self::FLOOR2_BACKGROUND,
                'width'         => self::FLOOR2_WIDTH,
                'height'        => self::FLOOR2_HEIGHT,
                'date_creation' => $now,
                'date_mod'      => $now,
            ]);
            $planId = (int) $DB->insertId();
            if ($planId <= 0) {
                return;
            }
            foreach (self::secondFloorDesks() as $desk) {
                $DB->insert(Settings::TABLE_DESKS, $desk + ['plans_id' => $planId, 'date_mod' => $now]);
            }
            foreach (self::secondFloorWalls() as $wall) {
                $DB->insert(Settings::TABLE_WALLS, $wall + ['plans_id' => $planId, 'date_mod' => $now]);
            }
        } finally {
            $DB->doQuery("SELECT RELEASE_LOCK('ativarede_seed_floor2')");
        }
    }

    /**
     * Mesas do 2º andar (viewBox 1800 x 450 do matriz-2andar.svg), no mesmo
     * estilo do Anexo: mesas de 42 x 46 encostadas e os grupos a cada 140
     * unidades (entre um e outro so as cadeiras). As letras continuam as do
     * Anexo (que vai ate J) para nenhuma mesa ter o mesmo nome nos dois
     * andares. Sem porta de switch: a T.I. define pela tela.
     */
    public static function secondFloorDesks(): array
    {
        $desks = [];
        $add = static function (string $name, float $x, float $y, string $chair, string $sector = '') use (&$desks): void {
            $desks[] = ['name' => $name, 'x' => $x, 'y' => $y, 'w' => 42, 'h' => 46, 'chair' => $chair, 'sector' => $sector];
        };

        // Blocos K e L (junto a entrada): 2 x 2, cadeiras nas laterais.
        foreach (['K' => 150, 'L' => 290] as $letter => $left) {
            foreach ([180, 226] as $row => $y) {
                $add($letter . ($row * 2 + 1), $left, $y, 'left');
                $add($letter . ($row * 2 + 2), $left + 42, $y, 'right');
            }
        }
        // Ilhas M, N e O: 2 colunas x 3 fileiras (1-3 a esquerda, 4-6 a direita).
        foreach (['M' => 430, 'N' => 570, 'O' => 710] as $letter => $left) {
            foreach ([160, 206, 252] as $row => $y) {
                $add($letter . ($row + 1), $left, $y, 'left');
                $add($letter . ($row + 4), $left + 42, $y, 'right');
            }
        }
        // Salas do DP (x 830 a 1076) e do RH (x 1076 a 1330): duas mesas em
        // cima, duas no meio e uma centralizada embaixo, com um respiro entre elas.
        foreach (['DP' => 953, 'RH' => 1203] as $sector => $center) {
            $add($sector . '1', $center - 35, 140, 'left', $sector);
            $add($sector . '2', $center + 35, 140, 'right', $sector);
            $add($sector . '3', $center - 35, 200, 'left', $sector);
            $add($sector . '4', $center + 35, 200, 'right', $sector);
            $add($sector . '5', $center, 260, 'down', $sector);
        }

        return $desks;
    }

    /**
     * Salas do DP e do RH logo depois das ilhas, com as portas abrindo para
     * dentro. Da sala do RH ate a parede da direita fica livre para uma
     * proxima sala.
     */
    public static function secondFloorWalls(): array
    {
        $wall = static fn(string $kind, float $x1, float $y1, float $x2, float $y2, int $flip = 0): array
            => ['kind' => $kind, 'x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2, 'flip' => $flip];

        return [
            // DP
            $wall('wall', 830, 40, 830, 325),
            $wall('wall', 830, 325, 860, 325),
            $wall('door', 860, 325, 920, 325, 1),
            $wall('wall', 920, 325, 1076, 325),
            // Entre o DP e o RH
            $wall('wall', 1076, 40, 1076, 325),
            // RH (porta igual a do DP: dobradica a esquerda, abre para dentro)
            $wall('wall', 1076, 325, 1106, 325),
            $wall('door', 1106, 325, 1166, 325, 1),
            $wall('wall', 1166, 325, 1330, 325),
            $wall('wall', 1330, 40, 1330, 325),
        ];
    }

    /**
     * O 2º andar ja criado por uma versao anterior (mesas separadas, coluna N
     * a mais, salas na ponta direita) e refeito no layout atual - mas so se
     * ninguem mexeu nele: qualquer mesa ou divisoria fora do que foi criado
     * deixa a planta como esta.
     */
    public static function applySecondFloorFixups(): void
    {
        global $DB;

        $flag = 'fixup_floor2_v4';
        $done = \Config::getConfigurationValues(Settings::CONTEXT, [$flag]);
        if (!empty($done[$flag])) {
            return;
        }
        $plan = $DB->request(['SELECT' => ['id'], 'FROM' => Settings::TABLE_PLANS,
            'WHERE' => ['background' => self::FLOOR2_BACKGROUND], 'LIMIT' => 1])->current();
        if (!$plan) {
            return; // ainda nao criado: quando for, ja nasce no layout atual
        }
        $planId = (int) $plan['id'];

        $desks = [];
        foreach ($DB->request(['FROM' => Settings::TABLE_DESKS, 'WHERE' => ['plans_id' => $planId]]) as $row) {
            if ((int) $row['switches_id'] > 0 || (string) $row['port'] !== '' || (int) $row['machines_id'] > 0 || (string) $row['comment'] !== '') {
                $desks = null; // mesa ja mapeada: nao refaz
                break;
            }
            $desks[] = self::key($row['name'], $row['x'], $row['y'], $row['w'], $row['h']);
        }
        $walls = [];
        foreach ($DB->request(['FROM' => Settings::TABLE_WALLS, 'WHERE' => ['plans_id' => $planId]]) as $row) {
            $walls[] = self::key($row['kind'], $row['x1'], $row['y1'], $row['x2'], $row['y2'], $row['flip']);
        }
        sort($walls);

        // Cada versao anterior: [mesas, divisorias] como foram criadas.
        $versions = [
            [self::FLOOR2_FIRST_LAYOUT, self::FLOOR2_FIRST_WALLS],
            [self::FLOOR2_SECOND_LAYOUT, self::FLOOR2_FIRST_WALLS],
            [self::FLOOR2_THIRD_LAYOUT, self::FLOOR2_THIRD_WALLS],
        ];
        $untouched = false;
        if ($desks !== null) {
            sort($desks);
            foreach ($versions as [$layout, $oldWallList]) {
                $old = [];
                foreach ($layout as $name => $d) {
                    $old[] = self::key($name, ...$d);
                }
                sort($old);
                $oldWalls = array_map(static fn(array $w): string => self::key(...$w), $oldWallList);
                sort($oldWalls);
                if ($desks === $old && $walls === $oldWalls) {
                    $untouched = true;
                    break;
                }
            }
        }

        if ($untouched) {
            $now = date('Y-m-d H:i:s');
            $DB->delete(Settings::TABLE_DESKS, ['plans_id' => $planId]);
            $DB->delete(Settings::TABLE_WALLS, ['plans_id' => $planId]);
            foreach (self::secondFloorDesks() as $desk) {
                $DB->insert(Settings::TABLE_DESKS, $desk + ['plans_id' => $planId, 'date_mod' => $now]);
            }
            foreach (self::secondFloorWalls() as $wall) {
                $DB->insert(Settings::TABLE_WALLS, $wall + ['plans_id' => $planId, 'date_mod' => $now]);
            }
        }
        \Config::setConfigurationValues(Settings::CONTEXT, [$flag => '1']);
    }

    private static function key(mixed ...$parts): string
    {
        return implode('|', array_map(static fn($p): string => is_numeric($p) ? (string) round((float) $p, 2) : (string) $p, $parts));
    }

    /** Versoes anteriores do 2º andar: [x, y, w, h] de cada mesa. */
    private const FLOOR2_FIRST_LAYOUT = [
        'K1' => [162, 181, 50, 50], 'K2' => [241, 181, 50, 50], 'K3' => [162, 260, 50, 50], 'K4' => [241, 260, 50, 50],
        'L1' => [364, 181, 50, 50], 'L2' => [443, 181, 50, 50], 'L3' => [364, 260, 50, 50], 'L4' => [443, 260, 50, 50],
        'M1' => [571, 153, 50, 50], 'M2' => [571, 220, 50, 50], 'M3' => [571, 287, 50, 50],
        'M4' => [627, 153, 50, 50], 'M5' => [627, 220, 50, 50], 'M6' => [627, 287, 50, 50],
        'O1' => [853, 153, 50, 50], 'O2' => [853, 220, 50, 50], 'O3' => [853, 287, 50, 50],
        'O4' => [909, 153, 50, 50], 'O5' => [909, 220, 50, 50], 'O6' => [909, 287, 50, 50],
        'P1' => [1025, 153, 50, 50], 'P2' => [1025, 220, 50, 50], 'P3' => [1025, 287, 50, 50],
        'P4' => [1081, 153, 50, 50], 'P5' => [1081, 220, 50, 50], 'P6' => [1081, 287, 50, 50],
        'N1' => [738, 153, 50, 50], 'N2' => [738, 220, 50, 50], 'N3' => [738, 287, 50, 50],
        'DP1' => [1201, 153, 50, 50], 'DP2' => [1362, 153, 50, 50], 'DP3' => [1362, 223, 50, 50],
        'RH1' => [1491, 158, 50, 50], 'RH2' => [1547, 158, 50, 50], 'RH3' => [1491, 223, 50, 50], 'RH4' => [1547, 223, 50, 50],
    ];

    private const FLOOR2_SECOND_LAYOUT = [
        'K1' => [150, 180, 42, 46], 'K2' => [192, 180, 42, 46], 'K3' => [150, 226, 42, 46], 'K4' => [192, 226, 42, 46],
        'L1' => [345, 180, 42, 46], 'L2' => [387, 180, 42, 46], 'L3' => [345, 226, 42, 46], 'L4' => [387, 226, 42, 46],
        'M1' => [560, 160, 42, 46], 'M2' => [560, 206, 42, 46], 'M3' => [560, 252, 42, 46],
        'M4' => [602, 160, 42, 46], 'M5' => [602, 206, 42, 46], 'M6' => [602, 252, 42, 46],
        'O1' => [850, 160, 42, 46], 'O2' => [850, 206, 42, 46], 'O3' => [850, 252, 42, 46],
        'O4' => [892, 160, 42, 46], 'O5' => [892, 206, 42, 46], 'O6' => [892, 252, 42, 46],
        'P1' => [1020, 160, 42, 46], 'P2' => [1020, 206, 42, 46], 'P3' => [1020, 252, 42, 46],
        'P4' => [1062, 160, 42, 46], 'P5' => [1062, 206, 42, 46], 'P6' => [1062, 252, 42, 46],
        'N1' => [720, 160, 42, 46], 'N2' => [720, 206, 42, 46], 'N3' => [720, 252, 42, 46],
        'DP1' => [1200, 160, 42, 46], 'DP2' => [1356, 160, 42, 46], 'DP3' => [1356, 206, 42, 46],
        'RH1' => [1500, 160, 42, 46], 'RH2' => [1542, 160, 42, 46], 'RH3' => [1500, 206, 42, 46], 'RH4' => [1542, 206, 42, 46],
    ];

    private const FLOOR2_THIRD_LAYOUT = [
        'K1' => [150, 180, 42, 46], 'K2' => [192, 180, 42, 46], 'K3' => [150, 226, 42, 46], 'K4' => [192, 226, 42, 46],
        'L1' => [290, 180, 42, 46], 'L2' => [332, 180, 42, 46], 'L3' => [290, 226, 42, 46], 'L4' => [332, 226, 42, 46],
        'M1' => [430, 160, 42, 46], 'M2' => [430, 206, 42, 46], 'M3' => [430, 252, 42, 46],
        'M4' => [472, 160, 42, 46], 'M5' => [472, 206, 42, 46], 'M6' => [472, 252, 42, 46],
        'N1' => [570, 160, 42, 46], 'N2' => [570, 206, 42, 46], 'N3' => [570, 252, 42, 46],
        'N4' => [612, 160, 42, 46], 'N5' => [612, 206, 42, 46], 'N6' => [612, 252, 42, 46],
        'O1' => [710, 160, 42, 46], 'O2' => [710, 206, 42, 46], 'O3' => [710, 252, 42, 46],
        'O4' => [752, 160, 42, 46], 'O5' => [752, 206, 42, 46], 'O6' => [752, 252, 42, 46],
        'DP1' => [875, 160, 42, 46], 'DP2' => [1031, 160, 42, 46], 'DP3' => [1031, 206, 42, 46],
        'RH1' => [1182, 160, 42, 46], 'RH2' => [1224, 160, 42, 46], 'RH3' => [1182, 206, 42, 46], 'RH4' => [1224, 206, 42, 46],
    ];

    /** Divisorias das versoes anteriores: [kind, x1, y1, x2, y2, flip]. */
    private const FLOOR2_THIRD_WALLS = [
        ['wall', 830, 40, 830, 325, 0],
        ['wall', 830, 325, 860, 325, 0],
        ['door', 860, 325, 920, 325, 1],
        ['wall', 920, 325, 1076, 325, 0],
        ['wall', 1076, 40, 1076, 325, 0],
        ['door', 1135, 325, 1080, 325, 0],
        ['wall', 1135, 325, 1330, 325, 0],
        ['wall', 1330, 40, 1330, 325, 0],
    ];

    private const FLOOR2_FIRST_WALLS = [
        ['wall', 1155, 40, 1155, 325, 0],
        ['wall', 1155, 325, 1185, 325, 0],
        ['door', 1185, 325, 1245, 325, 1],
        ['wall', 1245, 325, 1401, 325, 0],
        ['wall', 1401, 40, 1401, 325, 0],
        ['door', 1460, 325, 1405, 325, 0],
        ['wall', 1460, 325, 1700, 325, 0],
    ];

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
