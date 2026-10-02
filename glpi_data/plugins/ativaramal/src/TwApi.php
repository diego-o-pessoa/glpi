<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use RuntimeException;
use Throwable;
use Toolbox;

/**
 * Consultas a API V3 do TW Connect (documentacao oficial: ApiDiagnostics::DOCS_URL).
 *
 * Autenticacao: "Authorization: Bearer <Token da API>" (Token criado no painel
 * da TW, em /gerenciar-tokens). So consultas (GET). As respostas ficam alguns
 * segundos no cache do GLPI: varios usuarios com o dashboard aberto nao
 * multiplicam as chamadas a TW.
 */
final class TwApi
{
    private const TIMEOUT = 20;

    /** Validade do cache por consulta (segundos). */
    public const TTL_EXTENSIONS = 60;
    public const TTL_CALLS      = 20;
    public const TTL_REALTIME   = 8;

    /** @return list<array<string, mixed>> */
    public static function extensions(): array
    {
        // So os campos usados: a resposta traz a senha SIP (secret), voice_pass
        // e tokens de integracao, que nunca vao para o cache nem para a tela.
        return self::cached('ativaramal_ramais', self::TTL_EXTENSIONS, static fn () => self::only(
            self::list('/api/v3/ramal/consultarRamal'),
            ['id', 'ativo', 'callgroup', 'nome', 'ramal', 'alias', 'reg_status', 'reg_latency', 'reg_ip_address', 'in_use_callcenter']
        ));
    }

    /**
     * Ligacoes do dia (00:00 ate agora).
     *
     * @return list<array<string, mixed>>
     */
    public static function callsToday(): array
    {
        $day = date('Y-m-d');
        return self::cached('ativaramal_calls_' . str_replace('-', '', $day), self::TTL_CALLS, static fn () => self::only(
            self::list('/api/v3/chamada/consultarChamada', ['data_inicial' => $day . ' 00:00:00', 'data_final' => date('Y-m-d H:i:s')]),
            ['id', 'calldate', 'clid', 'src', 'dst', 'from_src', 'duration', 'billsec', 'direction', 'disposition', 'id_ramal', 'transfer', 'hangup', 'linkedid']
        ));
    }

    /** Campos das ligacoes usados pelos paineis. */
    private const CALL_FIELDS = ['id', 'calldate', 'clid', 'src', 'dst', 'from_src', 'duration', 'billsec',
                                 'direction', 'disposition', 'id_ramal', 'transfer', 'hangup', 'linkedid'];

    /**
     * Ligacoes de um periodo (aba Desempenho), em pedacos por mes. Cache de
     * 2 min: semana/mes trazem milhares de linhas e nao precisam ser ao vivo.
     *
     * A TW responde HTTP 500 em alguns trechos (ex.: periodo com dias de
     * setembro). Quando um mes falha, consulta dia a dia e pula so os dias
     * com erro, devolvidos em "falhas" para a tela avisar.
     *
     * @return array{calls: list<array<string, mixed>>, falhas: list<string>}
     */
    public static function callsBetween(string $start, string $end): array
    {
        $key = 'ativaramal_calls_' . preg_replace('/\D+/', '', $start . $end);
        return self::cached($key, 120, static function () use ($start, $end): array {
            $path = '/api/v3/chamada/consultarChamada';
            $calls = [];
            $failed = [];
            foreach (self::monthChunks($start, $end) as [$from, $to]) {
                try {
                    $rows = self::list($path, ['data_inicial' => $from, 'data_final' => $to]);
                } catch (RuntimeException) {
                    $rows = [];
                    foreach (self::dayChunks($from, $to) as [$dayFrom, $dayTo]) {
                        try {
                            array_push($rows, ...self::list($path, ['data_inicial' => $dayFrom, 'data_final' => $dayTo]));
                        } catch (RuntimeException) {
                            // Dia com erro: tenta hora a hora e pula so a hora ruim.
                            foreach (self::hourChunks($dayFrom, $dayTo) as [$hourFrom, $hourTo]) {
                                try {
                                    array_push($rows, ...self::list($path, ['data_inicial' => $hourFrom, 'data_final' => $hourTo]));
                                } catch (RuntimeException) {
                                    $failed[] = substr($hourFrom, 0, 13);
                                }
                            }
                        }
                    }
                }
                foreach ($rows as $call) {
                    $calls[(string) ($call['id'] ?? count($calls))] = $call;
                }
            }
            if ($failed !== []) {
                Logger::warning('API TW: dias sem resposta na consulta de ligações', ['dias' => implode(',', $failed)]);
            }
            return ['calls' => self::only(array_values($calls), self::CALL_FIELDS), 'falhas' => $failed];
        });
    }

    /**
     * Divide [inicio, fim] em horas.
     *
     * @return list<array{0: string, 1: string}>
     */
    private static function hourChunks(string $start, string $end): array
    {
        $chunks = [];
        $from = strtotime($start);
        $limit = strtotime($end);
        while ($from !== false && $limit !== false && $from <= $limit) {
            $hourEnd = strtotime(date('Y-m-d H:59:59', $from));
            $chunks[] = [date('Y-m-d H:i:s', $from), date('Y-m-d H:i:s', min($hourEnd, $limit))];
            $from = $hourEnd + 1;
        }
        return $chunks;
    }

    /**
     * Divide [inicio, fim] em dias.
     *
     * @return list<array{0: string, 1: string}>
     */
    private static function dayChunks(string $start, string $end): array
    {
        $chunks = [];
        $from = strtotime($start);
        $limit = strtotime($end);
        while ($from !== false && $limit !== false && $from <= $limit) {
            $dayEnd = strtotime(date('Y-m-d 23:59:59', $from));
            $chunks[] = [date('Y-m-d H:i:s', $from), date('Y-m-d H:i:s', min($dayEnd, $limit))];
            $from = $dayEnd + 1;
        }
        return $chunks;
    }

    /**
     * Divide [inicio, fim] em pedacos que nao atravessam a virada do mes.
     *
     * @return list<array{0: string, 1: string}>
     */
    private static function monthChunks(string $start, string $end): array
    {
        $chunks = [];
        $from = strtotime($start);
        $limit = strtotime($end);
        while ($from !== false && $limit !== false && $from <= $limit) {
            $monthEnd = strtotime(date('Y-m-t 23:59:59', $from));
            $to = min($monthEnd, $limit);
            $chunks[] = [date('Y-m-d H:i:s', $from), date('Y-m-d H:i:s', $to)];
            $from = $monthEnd + 1;
        }
        return $chunks;
    }

    /**
     * Canais ativos agora (eventos CoreShowChannel do Asterisk).
     *
     * @return list<array<string, mixed>>
     */
    public static function realtime(): array
    {
        return self::cached('ativaramal_realtime', self::TTL_REALTIME, static function (): array {
            // A TW devolve a saida do AMI: cabecalho ("Channels will follow"),
            // um item por canal (CoreShowChannel) e outros eventos que tambem
            // trazem o canal (ex.: RTCPSent). Vale todo item com canal, sem
            // repetir o mesmo canal; o nome do evento nao e exigido.
            $channels = [];
            foreach (self::list('/api/v3/chamada/consultarChamadaTempoReal') as $event) {
                $channel = (string) ($event['Channel'] ?? $event['channel'] ?? '');
                if ($channel === '' || isset($channels[$channel])) {
                    continue;
                }
                $channels[$channel] = $event;
            }
            return self::only(
                array_values($channels),
                ['Channel', 'ChannelStateDesc', 'CallerIDNum', 'CallerIDName', 'ConnectedLineNum', 'ConnectedLineName', 'Context', 'Exten', 'Application', 'Duration', 'Linkedid', 'Uniqueid']
            );
        });
    }

    /**
     * GET autenticado. Aceita os dois formatos da TW: {"RETORNO": [...]} ou a lista direta.
     *
     * @param array<string, string> $query
     * @return list<array<string, mixed>>
     * @throws RuntimeException mensagem segura para a tela
     */
    private static function list(string $path, array $query = []): array
    {
        $base = RamalConfig::baseUrl();
        $token = RamalConfig::secret('api_token');
        if (!RamalConfig::isHttpsUrl($base) || $token === '') {
            throw new RuntimeException('Integração incompleta: confira a URL base (HTTPS) e o Token da API em Ativa Ramal > Integração TW Solutions.');
        }
        $url = $base . $path . ($query !== [] ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');

        try {
            $response = Toolbox::getGuzzleClient(['timeout' => self::TIMEOUT, 'http_errors' => false, 'allow_redirects' => false])
                ->get($url, ['headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token]]);
        } catch (Throwable $exception) {
            Logger::warning('API TW: falha de conexão', ['caminho' => $path, 'erro' => get_class($exception)]);
            throw new RuntimeException('Não foi possível conectar à TW Solutions.');
        }

        $status = $response->getStatusCode();
        $data = json_decode((string) $response->getBody(), true);
        if ($status !== 200 || !is_array($data)) {
            Logger::warning('API TW: resposta inesperada', ['caminho' => $path, 'status' => $status]);
            throw new RuntimeException(sprintf('A TW respondeu HTTP %d em %s.', $status, $path));
        }
        $list = array_is_list($data) ? $data : ($data['RETORNO'] ?? []);
        // "Nenhum resultado" pode vir como texto em RETORNO.
        return is_array($list) ? array_values(array_filter($list, 'is_array')) : [];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string> $keys
     * @return list<array<string, mixed>>
     */
    private static function only(array $rows, array $keys): array
    {
        $flip = array_flip($keys);
        return array_map(static fn (array $row): array => array_intersect_key($row, $flip), $rows);
    }

    /**
     * @param callable(): list<array<string, mixed>> $fetch
     * @return list<array<string, mixed>>
     */
    private static function cached(string $key, int $ttl, callable $fetch): array
    {
        global $GLPI_CACHE;

        $hit = $GLPI_CACHE?->get($key);
        if (is_array($hit) && isset($hit['at'], $hit['data']) && time() - (int) $hit['at'] < $ttl) {
            return $hit['data'];
        }
        $data = $fetch();
        $GLPI_CACHE?->set($key, ['at' => time(), 'data' => $data], $ttl * 4);
        return $data;
    }
}
