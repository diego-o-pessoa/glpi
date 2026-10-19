<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaprint;

use DateTime;
use Glpi\DBAL\QueryExpression;

/**
 * Repositório e sincronização com os Ativos de Impressoras do GLPI (glpi_printers)
 * e cálculos analíticos para monitoramento, contadores e insumos.
 */
final class PrinterRepository
{
    public const TABLE = 'glpi_plugin_ativaprint_printers';
    public const COUNTERS_TABLE = 'glpi_plugin_ativaprint_counters';
    public const SUPPLIES_TABLE = 'glpi_plugin_ativaprint_supplies';

    /**
     * Retorna a lista de impressoras filtrada.
     *
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public static function getAll(array $filters = []): array
    {
        global $DB;

        if (!$DB->tableExists(self::TABLE)) {
            return [];
        }

        self::syncFromGlpi();

        $where = ['is_active' => 1];

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '' && $status !== 'all') {
            if ($status === 'online') {
                $where['status'] = ['online', 'printing'];
            } else {
                $where['status'] = $status;
            }
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $where['OR'] = [
                ['name'   => ['LIKE', $like]],
                ['model'  => ['LIKE', $like]],
                ['ip'     => ['LIKE', $like]],
                ['sector' => ['LIKE', $like]],
                ['serial' => ['LIKE', $like]],
            ];
        }

        $criticalOnly = !empty($filters['critical_only']);
        $warningLimit = PrintConfig::int('toner_warning');

        $rows = [];
        $order = ['status ASC', 'name ASC', 'id ASC'];

        foreach ($DB->request(['FROM' => self::TABLE, 'WHERE' => $where, 'ORDER' => $order]) as $row) {
            $toner = $row['toner_level'] !== null ? (int) $row['toner_level'] : null;

            if ($criticalOnly && ($toner === null || $toner > $warningLimit)) {
                continue;
            }

            $rows[] = self::decorate($row);
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function decorate(array $row): array
    {
        global $CFG_GLPI;

        $ip = trim((string) ($row['ip'] ?? ''));
        $hasValidIp = ($ip !== '' && $ip !== '0.0.0.0');
        $displayIp = $hasValidIp ? $ip : 'Não configurado';

        $status = (string) ($row['status'] ?? 'offline');
        $label = 'Offline';
        $badgeClass = 'is-offline';

        if (!$hasValidIp) {
            $label = 'Sem IP';
            $badgeClass = 'is-offline';
        } elseif ($status === 'online') {
            $label = 'Online';
            $badgeClass = 'is-online';
        } elseif ($status === 'printing') {
            $label = 'Em uso';
            $badgeClass = 'is-printing';
        } elseif ($status === 'warning') {
            $label = 'Atenção';
            $badgeClass = 'is-warning';
        }

        $toner = $row['toner_level'] !== null ? (int) $row['toner_level'] : null;
        $drum = $row['drum_level'] !== null ? (int) $row['drum_level'] : null;

        $modelLower = mb_strtolower((string) ($row['model'] ?? '') . ' ' . (string) ($row['name'] ?? ''));
        // Modelos HP All-in-One conhecidos (sem tambor/cilindro separado)
        $hasIntegratedDrum = str_contains($modelLower, 'm252')
            || str_contains($modelLower, 'laserjet pro m2')
            || str_contains($modelLower, 'laserjet pro m1')
            || str_contains($modelLower, 'laserjet p1')
            || str_contains($modelLower, 'laserjet pro 400 color');

        $drumLabel = '--';
        $drumTitle = 'Nível não disponível via SNMP';
        if ($drum !== null) {
            $drumLabel = $drum . '%';
            $drumTitle = 'Cilindro: ' . $drum . '%';
        } elseif ($hasIntegratedDrum) {
            $drumLabel = 'N/A';
            $drumTitle = 'Cilindro integrado ao toner (All-in-One)';
        }

        $tonerClass = 'is-good';
        if ($toner !== null) {
            if ($toner <= PrintConfig::int('toner_critical')) {
                $tonerClass = 'is-critical';
            } elseif ($toner <= PrintConfig::int('toner_warning')) {
                $tonerClass = 'is-warning';
            }
        }

        $drumClass = 'is-good';
        if ($drum !== null) {
            if ($drum <= PrintConfig::int('toner_critical')) {
                $drumClass = 'is-critical';
            } elseif ($drum <= PrintConfig::int('toner_warning')) {
                $drumClass = 'is-warning';
            }
        }

        $suppliesList = [];
        if (!empty($row['supplies'])) {
            $suppliesList = json_decode((string) $row['supplies'], true) ?: [];
        }

        $glpiId = (int) ($row['printers_id'] ?? 0);
        $glpiUrl = ($glpiId > 0 && isset($CFG_GLPI['root_doc']))
            ? ($CFG_GLPI['root_doc'] . '/front/printer.form.php?id=' . $glpiId)
            : null;

        $counter = (int) ($row['page_counter'] ?? 0);
        $counterFmt = $counter > 0 ? number_format($counter, 0, ',', '.') . ' págs' : '—';

        return $row + [
            'display_ip'   => $displayIp,
            'glpi_url'     => $glpiUrl,
            'status_label' => $label,
            'status_class' => $badgeClass,
            'toner_class'  => $tonerClass,
            'drum_class'   => $drumClass,
            'drum_label'   => $drumLabel,
            'drum_title'   => $drumTitle,
            'counter_fmt'  => $counterFmt,
            'supplies_list'=> $suppliesList,
            'last_poll_fmt'=> !empty($row['last_poll']) ? date('d/m/Y H:i', strtotime((string) $row['last_poll'])) : '—',
        ];
    }

    public static function getById(int $id): ?array
    {
        global $DB;

        if (!$DB->tableExists(self::TABLE)) {
            return null;
        }

        $row = $DB->request([
            'FROM'  => self::TABLE,
            'WHERE' => ['id' => $id],
            'LIMIT' => 1,
        ])->current();

        return is_array($row) ? self::decorate($row) : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        global $DB;

        $now = date('Y-m-d H:i:s');
        $name = trim((string) ($data['name'] ?? ''));
        $model = trim((string) ($data['model'] ?? ''));
        $ip = trim((string) ($data['ip'] ?? '0.0.0.0'));
        $sector = trim((string) ($data['sector'] ?? 'Geral'));
        $community = trim((string) ($data['snmp_community'] ?? ''));
        $glpiPrinterId = !empty($data['printers_id']) ? (int) $data['printers_id'] : null;

        if ($name === '' && $model !== '') {
            $name = $model;
        } elseif ($name === '') {
            $name = 'Impressora ' . ($ip !== '0.0.0.0' ? $ip : 'Nova');
        }

        $fields = [
            'printers_id'    => $glpiPrinterId,
            'name'           => $name,
            'model'          => $model !== '' ? $model : $name,
            'ip'             => $ip,
            'sector'         => $sector !== '' ? $sector : 'Geral',
            'status'         => ($ip !== '0.0.0.0') ? 'online' : 'offline',
            'status_message' => ($ip !== '0.0.0.0') ? 'Impressora cadastrada' : 'Sem IP configurado',
            'snmp_community' => $community !== '' ? $community : null,
            'is_active'      => 1,
            'date_creation'  => $now,
            'date_mod'       => $now,
        ];

        $DB->insert(self::TABLE, $fields);
        return (int) $DB->insertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        global $DB;

        $fields = [];
        foreach (['name', 'model', 'ip', 'sector', 'snmp_community', 'is_active', 'status', 'status_message'] as $key) {
            if (array_key_exists($key, $data)) {
                $fields[$key] = $data[$key];
            }
        }
        $fields['date_mod'] = date('Y-m-d H:i:s');

        return $DB->update(self::TABLE, $fields, ['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        global $DB;

        return $DB->delete(self::TABLE, ['id' => $id]);
    }

    /**
     * Calcula os 4 KPIs operacionais superiores.
     *
     * @param int $days Período em dias (7, 30 ou 90)
     * @return array<string, mixed>
     */
    public static function getKpis(int $days = 7): array
    {
        global $DB;

        if (!$DB->tableExists(self::TABLE)) {
            return self::emptyKpis();
        }

        self::syncFromGlpi();

        $warningThreshold = PrintConfig::int('toner_warning');
        $criticalThreshold = PrintConfig::int('toner_critical');

        // 1. Impressoras Ativas (Online/Offline)
        $totalPrinters = 0;
        $onlineCount = 0;
        $offlineCount = 0;
        $warningCount = 0;
        $criticalTonerCount = 0;

        foreach ($DB->request(['FROM' => self::TABLE, 'WHERE' => ['is_active' => 1]]) as $p) {
            $totalPrinters++;
            $status = (string) ($p['status'] ?? 'offline');
            $ip = trim((string) ($p['ip'] ?? ''));
            $hasIp = ($ip !== '' && $ip !== '0.0.0.0');

            if ($hasIp && ($status === 'online' || $status === 'printing')) {
                $onlineCount++;
            } elseif ($hasIp && $status === 'warning') {
                $warningCount++;
                $onlineCount++;
            } else {
                $offlineCount++;
            }

            $toner = $p['toner_level'] !== null ? (int) $p['toner_level'] : null;
            if ($toner !== null && $toner <= $warningThreshold) {
                $criticalTonerCount++;
            }
        }

        $onlinePercent = $totalPrinters > 0 ? (int) round(($onlineCount / $totalPrinters) * 100) : 0;

        // 2. Impressões (Total acumulado e Volumetria do período)
        $volumeCurrent = self::calculatePeriodVolume($days, 0);
        $volumePrevious = self::calculatePeriodVolume($days, $days);

        $totalPages = 0;
        foreach ($DB->request(['SELECT' => ['page_counter'], 'FROM' => self::TABLE, 'WHERE' => ['is_active' => 1]]) as $pRow) {
            $totalPages += (int) ($pRow['page_counter'] ?? 0);
        }

        // Se há volume calculado no período, exibe o volume; caso contrário, exibe o total acumulado do parque
        $displayVolume = $volumeCurrent > 0 ? $volumeCurrent : $totalPages;

        $delta = null;
        if ($volumePrevious > 0) {
            $delta = round((($volumeCurrent - $volumePrevious) / $volumePrevious) * 100, 1);
        }

        $periodLabel = $days === 7 ? 'à semana passada' : ($days === 30 ? 'ao mês passado' : 'ao período anterior');
        $deltaFormatted = '';
        if ($delta !== null) {
            $signal = $delta >= 0 ? '+' : '';
            $deltaFormatted = $signal . str_replace('.', ',', (string) $delta) . '% em relação ' . $periodLabel;
        } elseif ($volumeCurrent > 0) {
            $deltaFormatted = 'páginas no período (' . number_format($totalPages, 0, ',', '.') . ' acumuladas)';
        } else {
            $deltaFormatted = 'páginas acumuladas no parque';
        }

        // 3. Insumos em Estoque
        $totalSupplies = 0;
        $lowStockCount = 0;

        if ($DB->tableExists(self::SUPPLIES_TABLE)) {
            foreach ($DB->request(['FROM' => self::SUPPLIES_TABLE]) as $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $min = (int) ($item['minimum_quantity'] ?? 2);
                $totalSupplies += $qty;
                if ($qty <= $min) {
                    $lowStockCount++;
                }
            }
        }

        $suppliesNote = $totalSupplies > 0
            ? ($lowStockCount > 0
                ? ($lowStockCount === 1 ? '1 modelo atingiu estoque mínimo' : $lowStockCount . ' modelos abaixo do mínimo')
                : 'toners e peças disponíveis')
            : '0 insumos cadastrados no armário';

        return [
            'printers' => [
                'total'       => $totalPrinters,
                'online'      => $onlineCount,
                'offline'     => $offlineCount,
                'warning'     => $warningCount,
                'percent'     => $onlinePercent,
                'note'        => $onlinePercent . '% do total',
            ],
            'toners_critical' => [
                'count'       => $criticalTonerCount,
                'threshold'   => $warningThreshold,
                'critical_th' => $criticalThreshold,
                'note'        => $criticalTonerCount > 0 ? 'precisam de substituição' : 'níveis adequados',
            ],
            'volume' => [
                'current'     => $displayVolume,
                'formatted'   => number_format($displayVolume, 0, ',', '.'),
                'total_pages' => $totalPages,
                'previous'    => $volumePrevious,
                'delta'       => $delta,
                'delta_text'  => $deltaFormatted,
                'days'        => $days,
            ],
            'supplies' => [
                'total'       => $totalSupplies,
                'low_stock'   => $lowStockCount,
                'note'        => $suppliesNote,
            ],
        ];
    }

    /**
     * Calcula total de páginas impressas no intervalo especificado para as impressoras ativas.
     */
    private static function calculatePeriodVolume(int $days, int $offsetDays = 0): int
    {
        global $DB;

        if (!$DB->tableExists(self::COUNTERS_TABLE)) {
            return 0;
        }

        $activePrinters = [];
        foreach ($DB->request(['SELECT' => ['id'], 'FROM' => self::TABLE, 'WHERE' => ['is_active' => 1]]) as $pRow) {
            $activePrinters[] = (int) $pRow['id'];
        }

        if ($activePrinters === []) {
            return 0;
        }

        $endDateStr = date('Y-m-d', strtotime("-{$offsetDays} days"));
        $startDateStr = date('Y-m-d', strtotime("-" . ($offsetDays + $days) . " days"));

        $volume = 0;

        $printers = $DB->request([
            'DISTINCT' => true,
            'SELECT'   => ['printers_id'],
            'FROM'     => self::COUNTERS_TABLE,
            'WHERE'    => [
                'printers_id' => $activePrinters,
                ['date' => ['>=', $startDateStr]],
                ['date' => ['<=', $endDateStr]],
            ],
        ]);

        foreach ($printers as $p) {
            $printerId = (int) $p['printers_id'];

            $startRow = $DB->request([
                'SELECT' => ['page_counter'],
                'FROM'   => self::COUNTERS_TABLE,
                'WHERE'  => [
                    'printers_id' => $printerId,
                    ['date' => ['>=', $startDateStr]],
                ],
                'ORDER'  => 'date ASC',
                'LIMIT'  => 1,
            ])->current();

            $endRow = $DB->request([
                'SELECT' => ['page_counter'],
                'FROM'   => self::COUNTERS_TABLE,
                'WHERE'  => [
                    'printers_id' => $printerId,
                    ['date' => ['<=', $endDateStr]],
                ],
                'ORDER'  => 'date DESC',
                'LIMIT'  => 1,
            ])->current();

            if (is_array($startRow) && is_array($endRow)) {
                $diff = (int) $endRow['page_counter'] - (int) $startRow['page_counter'];
                if ($diff > 0) {
                    $volume += $diff;
                }
            }
        }

        return $volume;
    }

    /**
     * Retorna lista de suprimentos em estoque.
     *
     * @return list<array<string, mixed>>
     */
    public static function getSupplies(): array
    {
        global $DB;

        if (!$DB->tableExists(self::SUPPLIES_TABLE)) {
            return [];
        }

        self::syncSuppliesFromGlpi();

        $items = [];
        foreach ($DB->request(['FROM' => self::SUPPLIES_TABLE, 'ORDER' => ['type ASC', 'name ASC']]) as $row) {
            $qty = (int) ($row['quantity'] ?? 0);
            $min = (int) ($row['minimum_quantity'] ?? 2);
            $row['is_low'] = $qty <= $min;
            $items[] = $row;
        }

        return $items;
    }

    /**
     * Cria ou atualiza insumo no armário da TI.
     *
     * @param array<string, mixed> $data
     */
    public static function saveSupply(array $data): int
    {
        global $DB;

        $id = (int) ($data['id'] ?? 0);
        $fields = [
            'name'              => trim((string) ($data['name'] ?? 'Insumo')),
            'type'              => in_array($data['type'] ?? '', ['toner', 'drum', 'waste', 'other'], true) ? $data['type'] : 'toner',
            'compatible_models' => trim((string) ($data['compatible_models'] ?? '')),
            'quantity'          => max(0, (int) ($data['quantity'] ?? 0)),
            'minimum_quantity'  => max(0, (int) ($data['minimum_quantity'] ?? 2)),
            'location'          => trim((string) ($data['location'] ?? 'Armário TI')),
            'date_mod'          => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            $DB->update(self::SUPPLIES_TABLE, $fields, ['id' => $id]);
            return $id;
        }

        $DB->insert(self::SUPPLIES_TABLE, $fields);
        return (int) $DB->insertId();
    }

    public static function deleteSupply(int $id): bool
    {
        global $DB;

        return $DB->delete(self::SUPPLIES_TABLE, ['id' => $id]);
    }

    private static function emptyKpis(): array
    {
        return [
            'printers' => ['total' => 0, 'online' => 0, 'offline' => 0, 'warning' => 0, 'percent' => 0, 'note' => '0% do total'],
            'toners_critical' => ['count' => 0, 'threshold' => 15, 'critical_th' => 5, 'note' => 'todos em nível adequado'],
            'volume' => ['current' => 0, 'formatted' => '0', 'previous' => 0, 'delta' => 0, 'delta_text' => 'sem dados'],
            'supplies' => ['total' => 0, 'low_stock' => 0, 'note' => 'nenhum insumo cadastrado'],
        ];
    }

    /**
     * Sincroniza as impressoras reais cadastradas nos Ativos do GLPI (glpi_printers)
     * e elimina dados fictícios/seeds.
     */
    public static function syncFromGlpi(): void
    {
        global $DB;

        if (!$DB->tableExists(self::TABLE)) {
            return;
        }

        if (!$DB->fieldExists(self::TABLE, 'printers_id')) {
            $DB->query("ALTER TABLE `" . self::TABLE . "` ADD `printers_id` int unsigned DEFAULT NULL AFTER `is_recursive`");
        }

        $hasGlpiPrinters = $DB->tableExists('glpi_printers')
            && countElementsInTable('glpi_printers', ['is_deleted' => 0, 'is_template' => 0]) > 0;

        if ($hasGlpiPrinters) {
            // Remove registros falsos de seed
            $DB->delete(self::TABLE, [
                'OR' => [
                    ['printers_id' => null],
                    ['printers_id' => 0],
                    ['serial' => ['LIKE', 'SN9%']],
                    ['serial' => ['VNB3R92144', 'ZX81B92001', 'RC09918231', 'E78291K201', 'CN99281023', 'KY77123910', 'LX11299301']],
                ],
            ]);

            // Busca as impressoras ativas cadastradas no GLPI
            $glpiPrinters = $DB->request([
                'SELECT' => [
                    'id', 'name', 'serial', 'last_pages_counter', 'init_pages_counter',
                    'comment', 'locations_id', 'printermodels_id', 'states_id',
                    'last_inventory_update', 'date_mod',
                ],
                'FROM'   => 'glpi_printers',
                'WHERE'  => [
                    'is_deleted'  => 0,
                    'is_template' => 0,
                ],
                'ORDER'  => ['name ASC', 'id ASC'],
            ]);

            $now = date('Y-m-d H:i:s');
            $activeIds = [];

            foreach ($glpiPrinters as $p) {
                $glpiId = (int) $p['id'];
                $activeIds[] = $glpiId;

                // 1. Modelo da impressora
                $model = '';
                if (!empty($p['printermodels_id']) && $DB->tableExists('glpi_printermodels')) {
                    $mRow = $DB->request([
                        'SELECT' => ['name'],
                        'FROM'   => 'glpi_printermodels',
                        'WHERE'  => ['id' => (int) $p['printermodels_id']],
                        'LIMIT'  => 1,
                    ])->current();
                    if (is_array($mRow) && !empty($mRow['name'])) {
                        $model = (string) $mRow['name'];
                    }
                }
                if ($model === '') {
                    $model = (string) $p['name'];
                }

                // 2. Setor / Localização
                $sector = '';
                if (!empty($p['locations_id']) && $DB->tableExists('glpi_locations')) {
                    $lRow = $DB->request([
                        'SELECT' => ['name', 'completename'],
                        'FROM'   => 'glpi_locations',
                        'WHERE'  => ['id' => (int) $p['locations_id']],
                        'LIMIT'  => 1,
                    ])->current();
                    if (is_array($lRow)) {
                        $sector = (string) ($lRow['name'] ?? $lRow['completename'] ?? '');
                    }
                }
                if ($sector === '') {
                    $sector = 'Geral';
                }

                // 3. Endereço IP
                $ip = self::findPrinterIp($glpiId, (string) ($p['name'] ?? ''), (string) ($p['comment'] ?? ''));

                // 4. Contador de páginas
                $counter = (int) ($p['last_pages_counter'] ?? 0);
                if ($counter === 0 && !empty($p['init_pages_counter'])) {
                    $counter = (int) $p['init_pages_counter'];
                }
                if ($counter === 0 && $DB->tableExists('glpi_printerlogs')) {
                    $logRow = $DB->request([
                        'SELECT' => ['total_pages'],
                        'FROM'   => 'glpi_printerlogs',
                        'WHERE'  => ['itemtype' => 'Printer', 'items_id' => $glpiId],
                        'ORDER'  => ['date DESC', 'id DESC'],
                        'LIMIT'  => 1,
                    ])->current();
                    if (is_array($logRow) && !empty($logRow['total_pages'])) {
                        $counter = (int) $logRow['total_pages'];
                    }
                }

                // 5. Níveis de suprimento no inventário nativo do GLPI
                $tonerLevel = null;
                $drumLevel = null;
                $wasteLevel = null;
                $suppliesList = [];

                if ($DB->tableExists('glpi_printers_cartridgeinfos')) {
                    $cartRows = $DB->request([
                        'FROM'  => 'glpi_printers_cartridgeinfos',
                        'WHERE' => ['printers_id' => $glpiId],
                    ]);

                    foreach ($cartRows as $cRow) {
                        $prop = strtolower((string) ($cRow['property'] ?? ''));
                        $valStr = trim((string) ($cRow['value'] ?? ''));
                        if ($valStr === '') {
                            continue;
                        }

                        $lvl = null;
                        if (preg_match('/(\d+)/', $valStr, $m)) {
                            $lvl = min(100, max(0, (int) $m[1]));
                        } elseif (preg_match('/ok|good|normal|ready/i', $valStr)) {
                            $lvl = 100;
                        } elseif (preg_match('/low|warning|near/i', $valStr)) {
                            $lvl = 15;
                        } elseif (preg_match('/empty|replace|exhausted|depleted/i', $valStr)) {
                            $lvl = 5;
                        }

                        if ($lvl === null) {
                            continue;
                        }

                        // Determina o tipo do insumo pela propriedade
                        // IMPORTANTE: checar 'drum'/imaging antes de 'black', pois 'drumblack' contém a palavra 'black'
                        if (str_contains($prop, 'drum') || str_contains($prop, 'imaging') || str_contains($prop, 'cilindro') || str_contains($prop, 'opc')) {
                            $drumLevel = ($drumLevel === null) ? $lvl : min($drumLevel, $lvl);
                            $suppliesList[] = [
                                'type'    => 'drum',
                                'name'    => 'Cilindro ' . (str_contains($prop, 'black') ? 'Preto' : ''),
                                'percent' => $lvl,
                            ];
                        } elseif (str_contains($prop, 'waste')) {
                            $wasteLevel = ($wasteLevel === null) ? $lvl : max($wasteLevel, $lvl);
                            $suppliesList[] = [
                                'type'    => 'waste',
                                'name'    => 'Caixa de Resíduos',
                                'percent' => $lvl,
                            ];
                        } elseif (str_contains($prop, 'toner') || str_contains($prop, 'cartridge') || str_contains($prop, 'black') || str_contains($prop, 'cyan') || str_contains($prop, 'magenta') || str_contains($prop, 'yellow')) {
                            $tonerLevel = ($tonerLevel === null) ? $lvl : min($tonerLevel, $lvl);
                            $color = 'Preto';
                            if (str_contains($prop, 'cyan')) $color = 'Ciano';
                            elseif (str_contains($prop, 'magenta')) $color = 'Magenta';
                            elseif (str_contains($prop, 'yellow')) $color = 'Amarelo';

                            $suppliesList[] = [
                                'type'    => 'toner',
                                'name'    => 'Toner ' . $color,
                                'percent' => $lvl,
                            ];
                        } elseif (str_contains($prop, 'fuser') || str_contains($prop, 'transfer')) {
                            $suppliesList[] = [
                                'type'    => 'maintenance',
                                'name'    => str_contains($prop, 'fuser') ? 'Kit Fusor' : 'Kit Transferência',
                                'percent' => $lvl,
                            ];
                        }
                    }
                }

                $existing = $DB->request([
                    'FROM'  => self::TABLE,
                    'WHERE' => ['printers_id' => $glpiId],
                    'LIMIT' => 1,
                ])->current();

                $isValidIp = ($ip !== '0.0.0.0' && $ip !== '');

                if (is_array($existing)) {
                    $update = [
                        'name'     => (string) $p['name'],
                        'model'    => $model,
                        'sector'   => $sector,
                        'serial'   => (string) ($p['serial'] ?? ''),
                        'is_active'=> 1,
                        'date_mod' => $now,
                    ];
                    if ($isValidIp) {
                        $update['ip'] = $ip;
                        $update['status'] = 'online';
                        $update['status_message'] = 'Impressora ativa na rede';
                        $update['last_seen'] = $now;
                    }
                    if ($counter > 0) {
                        $update['page_counter'] = max($counter, (int) ($existing['page_counter'] ?? 0));
                    }
                    if ($tonerLevel !== null) {
                        $update['toner_level'] = $tonerLevel;
                    }
                    if ($drumLevel !== null) {
                        $update['drum_level'] = $drumLevel;
                    }
                    if ($wasteLevel !== null) {
                        $update['waste_level'] = $wasteLevel;
                    }
                    if ($suppliesList !== []) {
                        $update['supplies'] = json_encode($suppliesList, JSON_UNESCAPED_UNICODE);
                    }
                    $lastInventory = !empty($p['last_inventory_update']) ? (string) $p['last_inventory_update'] : (!empty($p['date_mod']) ? (string) $p['date_mod'] : null);
                    if ($lastInventory !== null && (empty($existing['last_poll']) || $existing['last_poll'] === '0000-00-00 00:00:00')) {
                        $update['last_poll'] = $lastInventory;
                    }
                    $DB->update(self::TABLE, $update, ['id' => (int) $existing['id']]);

                    $targetPrinterId = (int) $existing['id'];
                    $targetCounter = (int) ($update['page_counter'] ?? ($existing['page_counter'] ?? $counter));
                } else {
                    $lastInventory = !empty($p['last_inventory_update']) ? (string) $p['last_inventory_update'] : (!empty($p['date_mod']) ? (string) $p['date_mod'] : null);
                    $insert = [
                        'printers_id'    => $glpiId,
                        'name'           => (string) $p['name'],
                        'model'          => $model,
                        'ip'             => $ip,
                        'sector'         => $sector,
                        'serial'         => (string) ($p['serial'] ?? ''),
                        'status'         => $isValidIp ? 'online' : 'offline',
                        'status_message' => $isValidIp ? 'Impressora ativa na rede' : 'Sem IP configurado no GLPI',
                        'toner_level'    => $tonerLevel,
                        'drum_level'     => $drumLevel,
                        'waste_level'    => $wasteLevel,
                        'supplies'       => $suppliesList !== [] ? json_encode($suppliesList, JSON_UNESCAPED_UNICODE) : null,
                        'page_counter'   => $counter,
                        'is_active'      => 1,
                        'last_seen'      => $isValidIp ? $now : null,
                        'last_poll'      => $lastInventory,
                        'date_creation'  => $now,
                        'date_mod'       => $now,
                    ];
                    $DB->insert(self::TABLE, $insert);
                    $targetPrinterId = (int) $DB->insertId();
                    $targetCounter = $counter;
                }

                // Sincroniza contador de páginas na tabela histórica de volumetria
                if ($targetCounter > 0 && $DB->tableExists(self::COUNTERS_TABLE)) {
                    $cRow = $DB->request([
                        'FROM'  => self::COUNTERS_TABLE,
                        'WHERE' => [
                            'printers_id' => $targetPrinterId,
                            'date'        => date('Y-m-d'),
                        ],
                        'LIMIT' => 1,
                    ])->current();

                    if (is_array($cRow)) {
                        if ($targetCounter > (int) $cRow['page_counter']) {
                            $DB->update(self::COUNTERS_TABLE, [
                                'page_counter' => $targetCounter,
                                'date_mod'     => $now,
                            ], ['id' => (int) $cRow['id']]);
                        }
                    } else {
                        $DB->insert(self::COUNTERS_TABLE, [
                            'printers_id'  => $targetPrinterId,
                            'date'         => date('Y-m-d'),
                            'page_counter' => $targetCounter,
                            'date_mod'     => $now,
                        ]);
                    }
                }
            }

            if ($activeIds !== []) {
                $DB->update(self::TABLE, ['is_active' => 0], [
                    'NOT' => ['printers_id' => $activeIds],
                ]);
            }

            // Garante que todas as impressoras ativas com IP válido fiquem com status online
            $DB->update(self::TABLE, [
                'status'         => 'online',
                'status_message' => 'Impressora ativa na rede',
                'last_seen'      => $now,
            ], [
                'is_active' => 1,
                'NOT'       => ['ip' => ['0.0.0.0', '']],
            ]);

            // Remove contadores órfãos de impressoras inexistentes ou de seeds antigos
            if ($DB->tableExists(self::COUNTERS_TABLE)) {
                $validPrinterIds = [];
                foreach ($DB->request(['SELECT' => ['id'], 'FROM' => self::TABLE, 'WHERE' => ['is_active' => 1]]) as $pRow) {
                    $validPrinterIds[] = (int) $pRow['id'];
                }
                if ($validPrinterIds !== []) {
                    $DB->delete(self::COUNTERS_TABLE, [
                        'NOT' => ['printers_id' => $validPrinterIds],
                    ]);
                }
            }

            // Remove registros de demonstração de insumos
            if ($DB->tableExists(self::SUPPLIES_TABLE)) {
                $DB->delete(self::SUPPLIES_TABLE, [
                    'name' => [
                        'Toner HP CF258A (Preto)',
                        'Toner Samsung CLT-K606S',
                        'Toner Brother TN-3472',
                        'Cilindro Brother DR-3440',
                        'Toner Canon GPR-53',
                        'Caixa Coletora Ricoh Waste Box',
                        'Toner Kyocera TK-1175',
                    ],
                ]);
            }

            self::syncSuppliesFromGlpi();
        } else {
            self::seedInitialDataIfEmpty();
        }
    }

    /**
     * Localiza o IP de uma impressora pelos dados e relacionamentos do GLPI.
     */
    private static function findPrinterIp(int $glpiId, string $name, string $comment): string
    {
        global $DB;

        // 1. Relacionamento NetworkPort -> NetworkName -> IPAddress
        if ($DB->tableExists('glpi_networkports') && $DB->tableExists('glpi_networknames') && $DB->tableExists('glpi_ipaddresses')) {
            $portRow = $DB->request([
                'SELECT' => ['glpi_ipaddresses.name AS ip'],
                'FROM'   => 'glpi_networkports',
                'INNER JOIN' => [
                    'glpi_networknames' => [
                        'ON' => [
                            'glpi_networknames' => 'items_id',
                            'glpi_networkports' => 'id', [
                                'AND' => ['glpi_networknames.itemtype' => 'NetworkPort'],
                            ],
                        ],
                    ],
                    'glpi_ipaddresses' => [
                        'ON' => [
                            'glpi_ipaddresses'  => 'items_id',
                            'glpi_networknames' => 'id', [
                                'AND' => ['glpi_ipaddresses.itemtype' => 'NetworkName'],
                            ],
                        ],
                    ],
                ],
                'WHERE'  => [
                    'glpi_networkports.items_id'   => $glpiId,
                    'glpi_networkports.itemtype'   => 'Printer',
                    'glpi_networkports.is_deleted' => 0,
                ],
                'LIMIT'  => 1,
            ])->current();

            if (is_array($portRow) && !empty($portRow['ip'])) {
                return trim((string) $portRow['ip']);
            }
        }

        // 2. Tabela direta glpi_ipaddresses
        if ($DB->tableExists('glpi_ipaddresses')) {
            $directWhere = [
                'OR' => [
                    ['items_id' => $glpiId, 'itemtype' => 'Printer'],
                ],
            ];
            if ($DB->fieldExists('glpi_ipaddresses', 'mainitems_id')) {
                $directWhere['OR'][] = ['mainitems_id' => $glpiId, 'mainitemtype' => 'Printer'];
            }
            $direct = $DB->request([
                'SELECT' => ['name AS ip'],
                'FROM'   => 'glpi_ipaddresses',
                'WHERE'  => $directWhere,
                'LIMIT'  => 1,
            ])->current();

            if (is_array($direct) && !empty($direct['ip'])) {
                return trim((string) $direct['ip']);
            }
        }

        // 3. Regex em campos de texto
        $text = $name . ' ' . $comment;
        if (preg_match('/\b(192\.168\.\d{1,3}\.\d{1,3}|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(?:1[6-9]|2\d|3[0-1])\.\d{1,3}\.\d{1,3})\b/', $text, $match)) {
            return $match[1];
        }

        return '0.0.0.0';
    }

    /**
     * Sincroniza insumos reais do estoque do GLPI (glpi_cartridgeitems / glpi_cartridges).
     */
    public static function syncSuppliesFromGlpi(): void
    {
        global $DB;

        if (!$DB->tableExists(self::SUPPLIES_TABLE) || !$DB->tableExists('glpi_cartridgeitems')) {
            return;
        }

        $items = $DB->request([
            'SELECT' => ['id', 'name', 'ref', 'alarm_threshold'],
            'FROM'   => 'glpi_cartridgeitems',
            'WHERE'  => ['is_deleted' => 0],
        ]);

        if (count($items) === 0) {
            return;
        }

        // Se há registros reais de cartuchos no GLPI, limpa os exemplos manuais de seed
        $DB->delete(self::SUPPLIES_TABLE, [
            'location' => 'Armário TI',
        ]);

        $now = date('Y-m-d H:i:s');

        foreach ($items as $ci) {
            $ciId = (int) $ci['id'];
            $inStock = 0;
            if ($DB->tableExists('glpi_cartridges')) {
                $inStock = countElementsInTable('glpi_cartridges', [
                    'cartridgeitems_id' => $ciId,
                    'printers_id'       => 0,
                ]);
            }

            $models = [];
            if ($DB->tableExists('glpi_cartridgeitems_printermodels') && $DB->tableExists('glpi_printermodels')) {
                $mReq = $DB->request([
                    'SELECT' => ['glpi_printermodels.name'],
                    'FROM'   => 'glpi_cartridgeitems_printermodels',
                    'INNER JOIN' => [
                        'glpi_printermodels' => [
                            'ON' => ['glpi_cartridgeitems_printermodels' => 'printermodels_id', 'glpi_printermodels' => 'id'],
                        ],
                    ],
                    'WHERE'  => ['cartridgeitems_id' => $ciId],
                ]);
                foreach ($mReq as $m) {
                    $models[] = (string) $m['name'];
                }
            }

            $compat = implode(', ', $models) ?: ((string) ($ci['ref'] ?? ''));

            $existing = $DB->request([
                'FROM'  => self::SUPPLIES_TABLE,
                'WHERE' => ['name' => (string) $ci['name']],
                'LIMIT' => 1,
            ])->current();

            if (is_array($existing)) {
                $DB->update(self::SUPPLIES_TABLE, [
                    'quantity'          => $inStock,
                    'minimum_quantity'  => (int) ($ci['alarm_threshold'] ?: 2),
                    'compatible_models' => $compat ?: $existing['compatible_models'],
                    'date_mod'          => $now,
                ], ['id' => (int) $existing['id']]);
            } else {
                $DB->insert(self::SUPPLIES_TABLE, [
                    'name'              => (string) $ci['name'],
                    'type'              => 'toner',
                    'compatible_models' => $compat ?: 'Compatível GLPI',
                    'quantity'          => $inStock,
                    'minimum_quantity'  => (int) ($ci['alarm_threshold'] ?: 2),
                    'location'          => 'Estoque GLPI',
                    'date_mod'          => $now,
                ]);
            }
        }
    }

    /**
     * Alimenta dados de demonstração apenas se o GLPI não possuir nenhuma impressora real.
     */
    public static function seedInitialDataIfEmpty(): void
    {
        global $DB;

        if (!$DB->tableExists(self::TABLE)) {
            return;
        }

        // Se o GLPI já possui impressoras reais cadastradas, nunca executa seed
        if ($DB->tableExists('glpi_printers') && countElementsInTable('glpi_printers', ['is_deleted' => 0, 'is_template' => 0]) > 0) {
            return;
        }

        $count = countElementsInTable(self::TABLE);
        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $samplePrinters = [
            ['name' => 'HP LaserJet Pro M404dn', 'model' => 'HP LaserJet Pro M404dn', 'ip' => '192.168.1.150', 'sector' => 'Financeiro', 'serial' => 'VNB3R92144', 'status' => 'online', 'status_message' => 'Equipamento pronto', 'toner_level' => 85, 'drum_level' => 90, 'waste_level' => 10, 'page_counter' => 14520],
            ['name' => 'Samsung MultiXpress X4300', 'model' => 'Samsung MultiXpress X4300', 'ip' => '192.168.1.152', 'sector' => 'Recursos Humanos', 'serial' => 'ZX81B92001', 'status' => 'online', 'status_message' => 'Nível de toner baixo', 'toner_level' => 12, 'drum_level' => 45, 'waste_level' => 20, 'page_counter' => 32180],
            ['name' => 'Ricoh Aficio MP 301', 'model' => 'Ricoh Aficio MP 301', 'ip' => '192.168.3.11', 'sector' => 'Faturamento', 'serial' => 'RC09918231', 'status' => 'offline', 'status_message' => 'Sem resposta na rede', 'toner_level' => null, 'drum_level' => null, 'waste_level' => null, 'page_counter' => 58200],
            ['name' => 'Brother HL-L5100DN', 'model' => 'Brother HL-L5100DN', 'ip' => '10.0.10.45', 'sector' => 'TI / Suporte', 'serial' => 'E78291K201', 'status' => 'online', 'status_message' => 'Equipamento pronto', 'toner_level' => 67, 'drum_level' => 80, 'waste_level' => 5, 'page_counter' => 9840],
        ];

        foreach ($samplePrinters as $p) {
            $p['is_active'] = 1;
            $p['date_creation'] = $now;
            $p['date_mod'] = $now;
            $p['last_poll'] = $now;
            $p['last_seen'] = $p['status'] !== 'offline' ? $now : date('Y-m-d H:i:s', strtotime('-2 days'));
            $DB->insert(self::TABLE, $p);
        }
    }
}
