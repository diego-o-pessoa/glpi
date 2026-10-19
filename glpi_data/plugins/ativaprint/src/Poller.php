<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaprint;

use CronTask;
use RuntimeException;
use Throwable;
use Toolbox;

/**
 * Leitura das impressoras pela rede.
 *
 * 1) SNMP (Printer-MIB / Host-Resources-MIB): status, suprimentos e contador
 *    de paginas. Respondeu = online.
 * 2) Sem SNMP, tenta abrir uma porta TCP comum de impressora (9100, 80, 443,
 *    631, 515): respondeu = online, mas sem niveis (mantem a ultima leitura).
 * 3) Nada respondeu = offline.
 *
 * Roda pela tarefa automatica "Poll" (a cada 5 min) e pelos botoes da tela.
 */
final class Poller
{
    private const OID_SYS_DESCR       = '1.3.6.1.2.1.1.1.0';
    private const OID_DEVICE_STATUS   = '1.3.6.1.2.1.25.3.2.1.5.1';    // hrDeviceStatus
    private const OID_PRINTER_STATUS  = '1.3.6.1.2.1.25.3.5.1.1.1';    // hrPrinterStatus
    private const OID_ERROR_STATE     = '1.3.6.1.2.1.25.3.5.1.2.1';    // hrPrinterDetectedErrorState
    private const OID_LIFE_COUNT      = '1.3.6.1.2.1.43.10.2.1.4.1.1'; // prtMarkerLifeCount
    private const OID_SERIAL          = '1.3.6.1.2.1.43.5.1.1.17.1';   // prtGeneralSerialNumber
    private const OID_SUPPLY_TYPE     = '1.3.6.1.2.1.43.11.1.1.5.1';
    private const OID_SUPPLY_DESCR    = '1.3.6.1.2.1.43.11.1.1.6.1';
    private const OID_SUPPLY_MAX      = '1.3.6.1.2.1.43.11.1.1.8.1';
    private const OID_SUPPLY_LEVEL    = '1.3.6.1.2.1.43.11.1.1.9.1';

    /** prtMarkerSuppliesType -> categoria usada na tela. */
    private const SUPPLY_KIND = [
        3 => 'toner', 21 => 'toner', 5 => 'toner', 6 => 'toner',
        9 => 'drum', 10 => 'drum',
        4 => 'waste', 8 => 'waste',
    ];

    /** Bits de hrPrinterDetectedErrorState (do bit mais alto do 1o byte). */
    private const ERROR_BITS = [
        0  => ['Pouco papel', false],
        1  => ['Sem papel', true],
        2  => ['Toner baixo', false],
        3  => ['Sem toner', true],
        4  => ['Porta aberta', true],
        5  => ['Papel atolado', true],
        6  => ['Impressora offline', true],
        7  => ['Manutenção solicitada', true],
        8  => ['Bandeja de entrada ausente', true],
        9  => ['Bandeja de saída ausente', true],
        10 => ['Suprimento ausente', true],
        11 => ['Bandeja de saída quase cheia', false],
        12 => ['Bandeja de saída cheia', true],
        13 => ['Bandeja de entrada vazia', true],
        14 => ['Manutenção preventiva vencida', false],
    ];

    private const TCP_PORTS = [9100, 80, 443, 631, 515];

    /** Tempo maximo de uma rodada da tarefa automatica (s). */
    private const CRON_BUDGET = 240;

    // ------------------------------------------------------------------ cron

    public static function cronInfo(string $name): array
    {
        return $name === 'Poll'
            ? ['description' => 'Ativa Print: consulta status, toner e contador das impressoras (SNMP)']
            : [];
    }

    /** 1 = consultou alguma impressora, 0 = nada a fazer. */
    public static function cronPoll(CronTask $task): int
    {
        $done = self::pollMany(self::CRON_BUDGET, 0, static function (array $printer, array $result) use ($task): void {
            $task->addVolume(1);
            if ($result['status'] === 'offline') {
                $task->log(sprintf('%s (%s): sem resposta', $printer['name'], $printer['ip']));
            }
        });
        return $done > 0 ? 1 : 0;
    }

    /**
     * Consulta as impressoras ativas, das leituras mais antigas para as mais
     * novas, ate acabar o tempo.
     *
     * @param int $budget        segundos disponiveis
     * @param int $olderThan     so as lidas ha mais de N segundos (0 = todas)
     * @param callable|null $each fn(array $printer, array $result)
     */
    public static function pollMany(int $budget = 5, int $olderThan = 0, ?callable $each = null): int
    {
        global $DB;

        $where = [
            'is_active' => 1,
            'NOT' => [
                ['ip' => '0.0.0.0'],
                ['ip' => ''],
            ],
        ];
        if ($olderThan > 0) {
            $limit = date('Y-m-d H:i:s', time() - $olderThan);
            $where['OR'] = [['last_poll' => null], ['last_poll' => ['<', $limit]]];
        }
        $started = microtime(true);
        $done = 0;
        foreach ($DB->request(['FROM' => PrinterRepository::TABLE, 'WHERE' => $where, 'ORDER' => ['last_poll ASC', 'id ASC']]) as $printer) {
            if (microtime(true) - $started >= $budget || $done >= 10) {
                break;
            }
            $result = self::pollAndSave($printer);
            $done++;
            if ($each !== null) {
                $each($printer, $result);
            }
        }
        return $done;
    }

    /**
     * Consulta uma impressora e grava o resultado.
     *
     * @param array<string, mixed> $printer linha da tabela
     * @return array<string, mixed> campos gravados
     */
    public static function pollAndSave(array $printer): array
    {
        global $DB;

        try {
            $fields = self::poll($printer);
        } catch (Throwable $exception) {
            Toolbox::logInFile('ativaprint', sprintf("Falha ao consultar %s: %s\n", $printer['ip'] ?? '?', $exception->getMessage()));
            $fields = ['status' => 'offline', 'status_message' => 'Falha na consulta: ' . mb_substr($exception->getMessage(), 0, 200)];
        }

        $now = date('Y-m-d H:i:s');
        $fields['last_poll'] = $now;
        if ($fields['status'] !== 'offline') {
            $fields['last_seen'] = $now;
        }
        $DB->update(PrinterRepository::TABLE, $fields, ['id' => (int) $printer['id']]);

        if (!empty($fields['page_counter'])) {
            self::saveCounter((int) $printer['id'], (int) $fields['page_counter']);
        }
        return $fields;
    }

    /**
     * @param array<string, mixed> $printer
     * @return array<string, mixed>
     */
    public static function poll(array $printer): array
    {
        $host = trim((string) ($printer['ip'] ?? ''));
        if ($host === '' || $host === '0.0.0.0') {
            return [
                'status'         => 'offline',
                'status_message' => 'Sem endereço IP configurado no GLPI.',
            ];
        }

        $community = trim((string) ($printer['snmp_community'] ?? '')) ?: PrintConfig::get('snmp_community');
        $timeoutSec = max(0.2, min(2.0, PrintConfig::int('snmp_timeout') / 1000));
        $client = new SnmpClient($host, $community, PrintConfig::get('snmp_version'), $timeoutSec, 1);

        try {
            $base = $client->get([
                self::OID_SYS_DESCR, self::OID_DEVICE_STATUS, self::OID_PRINTER_STATUS,
                self::OID_ERROR_STATE, self::OID_LIFE_COUNT, self::OID_SERIAL,
            ]);
        } catch (RuntimeException $exception) {
            if (self::tcpReachable($host)) {
                return [
                    'status'         => 'online',
                    'status_message' => 'Responde na rede, mas não ao SNMP (confira a community e se o SNMP está ativo).',
                ];
            }
            return ['status' => 'offline', 'status_message' => 'Sem resposta na rede.'];
        }

        $supplies = [];
        try {
            $supplies = self::readSupplies($client);
        } catch (RuntimeException $exception) {
            // Status ja veio; so os niveis ficam sem atualizar nesta rodada.
        }

        [$status, $message] = self::status($base);
        $fields = [
            'status'         => $status,
            'status_message' => $message,
            'sys_descr'      => self::clean((string) ($base[self::OID_SYS_DESCR] ?? ''), 255),
        ];
        $counter = $base[self::OID_LIFE_COUNT] ?? null;
        if (is_int($counter) && $counter > 0) {
            $fields['page_counter'] = $counter;
        }
        $serial = self::clean((string) ($base[self::OID_SERIAL] ?? ''), 255);
        if ($serial !== '' && trim((string) ($printer['serial'] ?? '')) === '') {
            $fields['serial'] = $serial;
        }
        if ($supplies !== []) {
            $fields['supplies']    = json_encode($supplies, JSON_UNESCAPED_UNICODE);
            $fields['toner_level'] = self::lowest($supplies, 'toner');
            $fields['drum_level']  = self::lowest($supplies, 'drum');
            $fields['waste_level'] = self::lowest($supplies, 'waste');
        }
        return $fields;
    }

    /**
     * @param array<string, int|string> $base
     * @return array{0: string, 1: string}
     */
    private static function status(array $base): array
    {
        $device = (int) ($base[self::OID_DEVICE_STATUS] ?? 0);   // 2 running, 3 warning, 4 testing, 5 down
        $printer = (int) ($base[self::OID_PRINTER_STATUS] ?? 0); // 3 idle, 4 printing, 5 warmup

        $problems = [];
        $blocking = false;
        $bits = (string) ($base[self::OID_ERROR_STATE] ?? '');
        for ($byte = 0, $n = min(2, strlen($bits)); $byte < $n; $byte++) {
            $value = ord($bits[$byte]);
            for ($bit = 0; $bit < 8; $bit++) {
                $index = $byte * 8 + $bit;
                if (($value & (0x80 >> $bit)) && isset(self::ERROR_BITS[$index])) {
                    [$label, $isBlocking] = self::ERROR_BITS[$index];
                    $problems[] = $label;
                    $blocking = $blocking || $isBlocking;
                }
            }
        }
        $message = implode(', ', $problems);

        if ($device === 5 || $blocking) {
            return ['warning', $message !== '' ? $message : 'O equipamento informa que está parado.'];
        }
        if ($printer === 4) {
            return ['printing', $message];
        }
        if ($printer === 5) {
            return ['online', $message !== '' ? $message : 'Aquecendo.'];
        }
        return ['online', $message];
    }

    /** @return list<array{name: string, kind: string, level: int|null, some: bool}> */
    private static function readSupplies(SnmpClient $client): array
    {
        $names = self::byIndex($client->walk(self::OID_SUPPLY_DESCR));
        if ($names === []) {
            return [];
        }
        $types = self::byIndex($client->walk(self::OID_SUPPLY_TYPE));
        $max = self::byIndex($client->walk(self::OID_SUPPLY_MAX));
        $levels = self::byIndex($client->walk(self::OID_SUPPLY_LEVEL));

        $out = [];
        foreach ($names as $index => $rawName) {
            $name = self::clean((string) $rawName, 80);
            $type = (int) ($types[$index] ?? 0);
            $capacity = (int) ($max[$index] ?? -2);
            $level = (int) ($levels[$index] ?? -2);

            $percent = null;
            if ($capacity > 0 && $level >= 0) {
                $percent = (int) max(0, min(100, round($level * 100 / $capacity)));
            }
            $out[] = [
                'name'  => $name !== '' ? $name : 'Suprimento ' . $index,
                'kind'  => self::kind($type, $name),
                'level' => $percent,
                // -3 = "ainda tem" (o equipamento nao sabe quanto).
                'some'  => $level === -3,
            ];
            if (count($out) >= 24) {
                break;
            }
        }
        return $out;
    }

    private static function kind(int $type, string $name): string
    {
        if (isset(self::SUPPLY_KIND[$type])) {
            return self::SUPPLY_KIND[$type];
        }
        $lower = mb_strtolower($name);
        if (preg_match('/waste|res[ií]duo|coletor|recipiente/u', $lower)) {
            return 'waste';
        }
        if (preg_match('/drum|cilindro|imaging|fotocondutor|opc|unidade de imagem/u', $lower)) {
            return 'drum';
        }
        if (preg_match('/toner|cartucho|cartridge|ink|tinta/u', $lower)) {
            return 'toner';
        }
        return 'other';
    }

    /**
     * @param array<string, int|string> $walk
     * @return array<int, int|string> indice final do OID => valor
     */
    private static function byIndex(array $walk): array
    {
        $out = [];
        foreach ($walk as $oid => $value) {
            $parts = explode('.', (string) $oid);
            $out[(int) end($parts)] = $value;
        }
        ksort($out);
        return $out;
    }

    /** @param list<array{kind: string, level: int|null}> $supplies */
    private static function lowest(array $supplies, string $kind): ?int
    {
        $levels = [];
        foreach ($supplies as $supply) {
            if ($supply['kind'] === $kind && $supply['level'] !== null) {
                $levels[] = $supply['level'];
            }
        }
        return $levels === [] ? null : min($levels);
    }

    private static function tcpReachable(string $host): bool
    {
        if ($host === '' || $host === '0.0.0.0') {
            return false;
        }
        $target = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? '[' . $host . ']' : $host;
        foreach ([9100, 80] as $port) {
            $socket = @stream_socket_client('tcp://' . $target . ':' . $port, $errno, $error, 0.2);
            if (is_resource($socket)) {
                fclose($socket);
                return true;
            }
        }
        return false;
    }

    /** Uma linha por impressora por dia, com o maior contador visto no dia. */
    private static function saveCounter(int $printerId, int $counter): void
    {
        global $DB;

        $today = date('Y-m-d');
        $current = $DB->request([
            'SELECT' => ['id', 'page_counter'],
            'FROM'   => PrinterRepository::COUNTERS_TABLE,
            'WHERE'  => ['printers_id' => $printerId, 'date' => $today],
            'LIMIT'  => 1,
        ])->current();
        if (is_array($current)) {
            if ($counter > (int) $current['page_counter']) {
                $DB->update(PrinterRepository::COUNTERS_TABLE, ['page_counter' => $counter, 'date_mod' => date('Y-m-d H:i:s')], ['id' => (int) $current['id']]);
            }
            return;
        }
        $DB->insert(PrinterRepository::COUNTERS_TABLE, [
            'printers_id'  => $printerId,
            'date'         => $today,
            'page_counter' => $counter,
            'date_mod'     => date('Y-m-d H:i:s'),
        ]);
    }

    /** Texto vindo do equipamento: sem bytes nulos/controle e em UTF-8. */
    private static function clean(string $value, int $max): string
    {
        $value = str_replace("\0", '', $value);
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }
        $value = trim((string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value));
        return mb_substr($value, 0, $max);
    }
}
