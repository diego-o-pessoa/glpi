<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use RuntimeException;

/**
 * Dados do dashboard (primeira renderizacao e atualizacao ao vivo).
 *
 * Codigos numericos da TW em consultarChamada: a documentacao mostra texto
 * ("ANSWERED"), mas este ambiente devolve numeros. O significado abaixo foi
 * DEDUZIDO dos dados de 01/10/2026 (disposition 1 sempre com billsec > 0;
 * direction 1 = ramal->ramal, 2 = ramal->externo, 3 = externo->DID) e deve
 * ser confirmado com a TW. Codigos desconhecidos aparecem como "codigo N".
 */
final class Dashboard
{
    public const DISPOSITION_ANSWERED = 1;
    public const DISPOSITIONS = [1 => 'Atendida', 4 => 'Não atendida'];
    public const DIRECTIONS = [1 => 'interna', 2 => 'saida', 3 => 'entrada'];

    private const RECENT_LIMIT = 40;

    /**
     * @param array{filial: string, setores: list<string>, ok: bool, motivo: string}|null $scope
     *        null = tudo; senao so os ramais da filial/setores (AccessScope)
     * @return array<string, mixed>
     */
    public static function payload(?array $scope = null): array
    {
        $errors = [];
        $extensions = self::safe(static fn () => TwApi::extensions(), 'ramais', $errors);
        $calls = self::safe(static fn () => TwApi::callsToday(), 'ligações', $errors);
        $channels = self::safe(static fn () => TwApi::realtime(), 'tempo real', $errors);

        [$rows, $byId, $byNumber] = self::extensionIndex($extensions, $scope);

        // --- tempo real: agrupa os canais por ligacao (Linkedid)
        $live = self::liveCalls($channels, $rows, $byNumber);
        // Trecho que ja terminou de uma ligacao que continua (transferencia,
        // fila -> atendente): o registro da TW sai no fim de cada trecho.
        $liveIds = array_flip(array_filter(array_column($live, 'linkedid')));

        // --- ligacoes de hoje
        $totals = self::emptyStats() + ['nao_atendidas_sem_ramal' => 0];
        $recent = [];
        usort($calls, static fn ($a, $b) => strcmp((string) ($b['calldate'] ?? ''), (string) ($a['calldate'] ?? '')));
        foreach ($calls as $call) {
            $direction = self::DIRECTIONS[(int) ($call['direction'] ?? 0)] ?? 'outra';
            $answered = (int) ($call['disposition'] ?? 0) === self::DISPOSITION_ANSWERED;
            $key = self::attribute($call, $direction, $byId, $byNumber);
            // Com escopo: so ligacoes de ramais visiveis (nada de URA/fila sem ramal).
            if ($scope !== null && ($key === null || !isset($rows[$key]))) {
                continue;
            }
            if ($key !== null && !isset($rows[$key])) {
                $key = null;
            }

            self::count($totals, $direction, $answered, (int) ($call['billsec'] ?? 0));
            if ($key !== null) {
                self::count($rows[$key]['stats'], $direction, $answered, (int) ($call['billsec'] ?? 0));
            } elseif (!$answered && $direction === 'entrada') {
                $totals['nao_atendidas_sem_ramal']++;
            }

            if (count($recent) < self::RECENT_LIMIT) {
                $linkedId = (string) ($call['linkedid'] ?? '');
                $ongoing = $linkedId !== '' && isset($liveIds[$linkedId]);
                $recent[] = [
                    // Este trecho acabou, mas a ligacao continua (ex.: transferida).
                    'em_andamento' => $ongoing,
                    'hora'     => substr((string) ($call['calldate'] ?? ''), 11, 5),
                    'direcao'  => $direction,
                    'de'       => (string) ($call['src'] ?? ''),
                    'para'     => (string) ($call['dst'] ?? ''),
                    'nome'     => self::callerName((string) ($call['clid'] ?? '')),
                    'ramal'    => $key !== null ? $rows[$key]['nome'] . ' (' . $rows[$key]['ramal'] . ')' : '',
                    'filial'   => $key !== null ? $rows[$key]['filial'] : '',
                    'setor'    => $key !== null ? $rows[$key]['setor'] : '',
                    'status'   => $answered ? 'atendida' : 'nao_atendida',
                    'status_label' => self::DISPOSITIONS[(int) ($call['disposition'] ?? 0)] ?? ('código ' . (int) ($call['disposition'] ?? 0)),
                    'falado'   => (int) ($call['billsec'] ?? 0),
                    'total'    => (int) ($call['duration'] ?? 0),
                ];
            }
        }

        // Nome da fila: cadastro do plugin (tela Ramais) e, se a TW liberar a
        // consulta de filas, o nome da TW.
        $queueRules = ExtensionDirectory::queueRules();
        $twQueues = TwApi::queueNames();
        foreach ($live as &$call) {
            $rule = $call['fila'] !== '' ? ($queueRules[$call['fila']] ?? null) : null;
            $call['fila_nome'] = $call['fila'] === '' ? '' : (($rule['nome'] ?? '') !== '' ? $rule['nome'] : ($twQueues[$call['fila']] ?? ''));
            $call['fila_filial'] = (string) ($rule['filial'] ?? '');
            $call['fila_setor'] = (string) ($rule['setor'] ?? '');
        }
        unset($call);
        if ($scope !== null) {
            // Ligacoes com ramal visivel, ou aguardando numa fila cadastrada
            // para a filial/setor do usuario (sem fila/URA de outros setores).
            $live = array_filter($live, static fn (array $call): bool => $call['visivel']
                || ($call['fila_setor'] !== '' && AccessScope::allows($scope, $call['fila_filial'], $call['fila_setor'])));
        }
        // Tocando primeiro (precisa de atencao), depois fila, conversa e chamando.
        $order = ['tocando' => 0, 'fila' => 1, 'chamando' => 2, 'conversa' => 3];
        usort($live, static fn (array $a, array $b): int => [$order[$a['fase']] ?? 9, -$a['segundos']] <=> [$order[$b['fase']] ?? 9, -$b['segundos']]);
        $live = array_values($live);

        // --- filial > setor > ramais
        $branches = [];
        foreach ($rows as $row) {
            $branches[$row['filial']]['setores'][$row['setor']]['ramais'][] = $row;
        }
        ksort($branches, SORT_NATURAL | SORT_FLAG_CASE);
        $tree = [];
        foreach ($branches as $branch => $data) {
            ksort($data['setores'], SORT_NATURAL | SORT_FLAG_CASE);
            $sectors = [];
            $branchStats = self::emptyStats();
            $branchOnline = $branchTotal = 0;
            foreach ($data['setores'] as $sector => $sectorData) {
                usort($sectorData['ramais'], static fn ($a, $b) => strnatcasecmp($a['nome'], $b['nome']));
                $sectorStats = self::emptyStats();
                $online = 0;
                foreach ($sectorData['ramais'] as $ramal) {
                    self::merge($sectorStats, $ramal['stats']);
                    $online += $ramal['online'] ? 1 : 0;
                }
                self::merge($branchStats, $sectorStats);
                $branchOnline += $online;
                $branchTotal += count($sectorData['ramais']);
                $sectors[] = [
                    'nome'   => (string) $sector,
                    'online' => $online,
                    'total'  => count($sectorData['ramais']),
                    'stats'  => $sectorStats,
                    'ramais' => $sectorData['ramais'],
                ];
            }
            $tree[] = ['nome' => (string) $branch, 'online' => $branchOnline, 'total' => $branchTotal, 'stats' => $branchStats, 'setores' => $sectors];
        }

        $online = count(array_filter($rows, static fn ($r) => $r['online']));
        $answered = $totals['atendidas'];
        $payload = [
            'now'     => time(),
            'updated' => date('H:i:s'),
            'errors'  => $errors,
            'kpis'    => [
                'ramais'          => count($rows),
                'online'          => $online,
                'ligacoes'        => $totals['total'],
                'atendidas'       => $answered,
                'nao_atendidas'   => $totals['nao_atendidas'],
                'sem_ramal'       => $totals['nao_atendidas_sem_ramal'],
                'entrada'         => $totals['entrada'],
                'saida'           => $totals['saida'],
                'interna'         => $totals['interna'],
                'taxa_atendimento' => $totals['entrada'] > 0 ? (int) round(100 * $totals['entrada_atendidas'] / $totals['entrada']) : null,
                'tempo_medio'     => $answered > 0 ? (int) round($totals['falado'] / $answered) : 0,
                'em_andamento'    => count($live),
                'em_fila'         => count(array_filter($live, static fn ($l) => $l['fase'] === 'fila')),
                'tocando'         => count(array_filter($live, static fn ($l) => $l['fase'] === 'tocando')),
                // Diagnostico: quantos canais a TW devolveu no tempo real.
                'canais_tw'       => count($channels),
            ],
            'tree'    => $tree,
            'live'    => $live,
            'recent'  => $recent,
            'scope'   => $scope,
        ];
        $payload['signature'] = sha1((string) json_encode([$payload['kpis'], $tree, $live, $recent, $errors]));
        return $payload;
    }

    /** Estados do Asterisk em que o aparelho ainda esta tocando / discando. */
    private const RINGING_STATES = ['Ringing'];
    private const DIALING_STATES = ['Ring', 'Dialing', 'Pre-ring', 'OffHook'];

    private const PHASE_LABELS = [
        'tocando'  => 'Tocando',
        'fila'     => 'Na fila',
        'chamando' => 'Chamando',
        'conversa' => 'Em conversa',
    ];

    /**
     * Ligacoes em andamento a partir dos canais do Asterisk (um canal por
     * ponta). Quem liga = canal mais antigo; os demais sao quem e chamado.
     * Fase: tocando (algum aparelho chamando), fila (aguardando sem ramal),
     * chamando (discando para fora) ou em conversa.
     *
     * Ramal de outro setor (fora do escopo) aparece so como "Outro setor".
     * Marca em $rows os ramais em ligacao e os que estao tocando.
     *
     * @param list<array<string, mixed>> $channels
     * @param array<string, array<string, mixed>> $rows
     * @param array<string, string> $byNumber
     * @return list<array<string, mixed>>
     */
    private static function liveCalls(array $channels, array &$rows, array $byNumber): array
    {
        $now = time();
        $groups = [];
        foreach ($channels as $channel) {
            $linked = (string) ($channel['Linkedid'] ?? $channel['Uniqueid'] ?? '');
            if ($linked === '') {
                continue;
            }
            $fetched = (int) ($channel['FetchedAt'] ?? $now);
            $groups[$linked][] = [
                'ext'     => self::channelExtension((string) ($channel['Channel'] ?? ''), (string) ($channel['CallerIDNum'] ?? ''), $byNumber),
                'state'   => (string) ($channel['ChannelStateDesc'] ?? ''),
                // Idade do canal agora (a consulta pode ter alguns segundos de cache).
                'seconds' => self::seconds((string) ($channel['Duration'] ?? '')) + max(0, $now - $fetched),
                'num'     => (string) ($channel['CallerIDNum'] ?? ''),
                'name'    => (string) ($channel['CallerIDName'] ?? ''),
                'cnum'    => (string) ($channel['ConnectedLineNum'] ?? ''),
                'cname'   => (string) ($channel['ConnectedLineName'] ?? ''),
                'queue'   => (($channel['Context'] ?? '') === 'Fila' || ($channel['Application'] ?? '') === 'Queue') ? (string) ($channel['Exten'] ?? '') : '',
            ];
        }

        $live = [];
        foreach ($groups as $linkedId => $list) {
            $linkedId = (string) $linkedId;
            usort($list, static fn (array $a, array $b): int => $b['seconds'] <=> $a['seconds']);
            $parties = [];
            $queue = '';
            $visible = false;
            foreach ($list as $c) {
                $queue = $queue !== '' ? $queue : $c['queue'];
                if ($c['ext'] !== null) {
                    $key = 'r' . $c['ext'];
                    if (isset($rows[$c['ext']])) {
                        $row = $rows[$c['ext']];
                        $visible = true;
                        $parties[$key] ??= ['tipo' => 'ramal', 'nome' => $row['nome'], 'numero' => $row['ramal'],
                                            'setor' => $row['setor'], 'filial' => $row['filial'], 'estado' => $c['state']];
                    } else {
                        $parties[$key] ??= ['tipo' => 'ramal', 'nome' => 'Outro setor', 'numero' => '', 'setor' => '', 'filial' => '', 'estado' => $c['state']];
                    }
                    // Outra ponta externa que a TW so mostra no canal do ramal.
                    if ($c['cnum'] !== '' && !isset($byNumber[$c['cnum']])) {
                        $parties['x' . $c['cnum']] ??= ['tipo' => 'externo', 'nome' => self::personName($c['cname'], $c['cnum']),
                                                        'numero' => $c['cnum'], 'setor' => '', 'filial' => '', 'estado' => ''];
                    }
                } elseif ($c['num'] !== '' && !isset($byNumber[$c['num']])) {
                    $key = 'x' . $c['num'];
                    $party = ['tipo' => 'externo', 'nome' => self::personName($c['name'], $c['num']), 'numero' => $c['num'],
                              'setor' => '', 'filial' => '', 'estado' => $c['state']];
                    // O canal externo traz o estado real (substitui o deduzido).
                    $parties[$key] = isset($parties[$key]) ? ['estado' => $c['state']] + $parties[$key] : $party;
                    if ($parties[$key]['nome'] === '') {
                        $parties[$key]['nome'] = $party['nome'];
                    }
                }
            }
            if ($parties === []) {
                continue;
            }

            $from = array_shift($parties);
            $to = array_values($parties);
            $states = array_column($list, 'state');
            $ringing = array_values(array_filter($to, static fn (array $p): bool => in_array($p['estado'], self::RINGING_STATES, true)));
            $answered = array_filter($to, static fn (array $p): bool => $p['estado'] === 'Up');

            if ($ringing !== []) {
                $phase = 'tocando';
            } elseif ($queue !== '' && array_filter($to, static fn (array $p): bool => $p['tipo'] === 'ramal') === []) {
                $phase = 'fila';
            } elseif ($answered === [] && array_intersect($states, self::DIALING_STATES) !== []) {
                $phase = 'chamando';
            } else {
                $phase = 'conversa';
            }

            // Ramais marcados na arvore (em ligacao / tocando).
            foreach ($list as $c) {
                if ($c['ext'] !== null && isset($rows[$c['ext']])) {
                    $rows[$c['ext']]['em_ligacao'] = true;
                    if (in_array($c['state'], self::RINGING_STATES, true)) {
                        $rows[$c['ext']]['tocando'] = true;
                    }
                }
            }

            $firstRamal = null;
            foreach (array_merge([$from], $to) as $p) {
                if ($p['tipo'] === 'ramal' && $p['numero'] !== '') {
                    $firstRamal = $p;
                    break;
                }
            }
            $external = null;
            foreach (array_merge([$from], $to) as $p) {
                if ($p['tipo'] === 'externo') {
                    $external = $p;
                    break;
                }
            }
            $seconds = (int) $list[0]['seconds'];
            $live[] = [
                // Identificador da ligacao inteira (todos os trechos).
                'linkedid'   => $linkedId,
                'fase'       => $phase,
                'fase_label' => self::PHASE_LABELS[$phase],
                'de'         => $from,
                'para'       => $phase === 'tocando' ? $ringing : $to,
                'segundos'   => $seconds,
                'visivel'    => $visible,
                // Campos anteriores (mantidos para quem ja usa o payload).
                'externo'      => $external['numero'] ?? '',
                'externo_nome' => $external['nome'] ?? '',
                'ramal'        => $firstRamal ? $firstRamal['nome'] . ' (' . $firstRamal['numero'] . ')' : '',
                'filial'       => $firstRamal['filial'] ?? '',
                'setor'        => $firstRamal['setor'] ?? '',
                'fila'         => $queue,
                'duracao'      => self::hms((string) $seconds),
                'estado'       => (string) ($list[0]['state'] ?? ''),
            ];
        }
        return $live;
    }

    /** "00:01:05" ou "65" -> segundos. */
    private static function seconds(string $value): int
    {
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $m)) {
            return (int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3];
        }
        return ctype_digit($value) ? (int) $value : 0;
    }

    /**
     * Ramais ativos com filial/setor e indices (id da TW, numero, alias) para
     * atribuir ligacoes. Com escopo, os ramais de fora saem de $rows, mas os
     * indices continuam apontando para eles (para descartar essas ligacoes
     * em vez de trata-las como "sem ramal").
     *
     * @param list<array<string, mixed>> $extensions
     * @return array{0: array<string, array<string, mixed>>, 1: array<int, string>, 2: array<string, string>}
     */
    public static function extensionIndex(array $extensions, ?array $scope): array
    {
        $rows = [];
        $byId = $byNumber = [];
        foreach ($extensions as $ext) {
            if ((int) ($ext['ativo'] ?? 1) !== 1) {
                continue;
            }
            $where = ExtensionDirectory::resolve($ext);
            $number = (string) ($ext['ramal'] ?? '');
            $key = $number !== '' ? $number : 'id' . (int) ($ext['id'] ?? 0);
            $rows[$key] = [
                'ramal'    => $number,
                'nome'     => trim((string) ($ext['nome'] ?? '')) ?: ('Ramal ' . $number),
                'pessoa'   => $where['pessoa'],
                'filial'   => $where['filial'],
                'setor'    => $where['setor'],
                'fonte'    => $where['fonte'],
                'online'   => (int) ($ext['reg_status'] ?? 0) === 1,
                'grupo'    => (string) ($ext['callgroup'] ?? ''),
                'em_ligacao' => false,
                'tocando'  => false,
                'stats'    => self::emptyStats(),
            ];
            $byId[(int) ($ext['id'] ?? 0)] = $key;
            if ($number !== '') {
                $byNumber[$number] = $key;
            }
            if ((string) ($ext['alias'] ?? '') !== '') {
                $byNumber[(string) $ext['alias']] = $key;
            }
        }

        // --- escopo: fora da filial/setor do usuario, o ramal some de tudo
        // (os indices continuam apontando para ele, para descartar as
        // ligacoes desses ramais em vez de deixa-las "sem ramal").
        if ($scope !== null) {
            foreach ($rows as $key => $row) {
                if (!AccessScope::allows($scope, $row['filial'], $row['setor'])) {
                    unset($rows[$key]);
                }
            }
        }
        return [$rows, $byId, $byNumber];
    }

    /** @return array<string, int> */
    public static function emptyStats(): array
    {
        return ['total' => 0, 'entrada' => 0, 'saida' => 0, 'interna' => 0, 'atendidas' => 0,
                'nao_atendidas' => 0, 'entrada_atendidas' => 0, 'falado' => 0];
    }

    /** @param array<string, int> $stats */
    private static function count(array &$stats, string $direction, bool $answered, int $billsec): void
    {
        $stats['total']++;
        if (isset($stats[$direction])) {
            $stats[$direction]++;
        }
        if ($answered) {
            $stats['atendidas']++;
            $stats['falado'] += $billsec;
            if ($direction === 'entrada') {
                $stats['entrada_atendidas']++;
            }
        } else {
            $stats['nao_atendidas']++;
        }
    }

    /**
     * @param array<string, int> $into
     * @param array<string, int> $from
     */
    private static function merge(array &$into, array $from): void
    {
        foreach ($from as $key => $value) {
            $into[$key] = ($into[$key] ?? 0) + $value;
        }
    }

    /**
     * Ramal responsavel pela ligacao: id_ramal (quando a TW informa); na saida,
     * quem ligou; senao, o destino se for um ramal. Entrada nao atendida na
     * URA/fila fica sem ramal.
     *
     * @param array<string, mixed> $call
     * @param array<int, string> $byId
     * @param array<string, string> $byNumber
     */
    public static function attribute(array $call, string $direction, array $byId, array $byNumber): ?string
    {
        $id = (int) ($call['id_ramal'] ?? 0);
        if ($id > 0 && isset($byId[$id])) {
            return $byId[$id];
        }
        $src = (string) ($call['src'] ?? '');
        $dst = (string) ($call['dst'] ?? '');
        if ($direction !== 'entrada' && isset($byNumber[$src])) {
            return $byNumber[$src];
        }
        return $byNumber[$dst] ?? null;
    }

    /**
     * Ramal de um canal: "PJSIP/31502-0000..." (alias) ou o CallerIDNum.
     *
     * @param array<string, string> $byNumber
     */
    private static function channelExtension(string $channel, string $callerId, array $byNumber): ?string
    {
        // "PJSIP/31502-...", "SIP/1503-...", "PJSIP/1503_app-..." (outro aparelho
        // do mesmo ramal) e "Local/1503@..." (fila, siga-me, transferencia).
        if (preg_match('#^(?:PJSIP|SIP|IAX2|Local)/(\d+)[-_@;]#', $channel, $matches) && isset($byNumber[$matches[1]])) {
            return $byNumber[$matches[1]];
        }
        return str_starts_with($channel, 'PJSIP/saida') ? null : ($byNumber[$callerId] ?? null);
    }

    /** Duracao do canal em HH:MM:SS (aceita "00:01:05" ou segundos). */
    private static function hms(string $value): string
    {
        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $value)) {
            return str_pad($value, 8, '0', STR_PAD_LEFT);
        }
        $seconds = ctype_digit($value) ? (int) $value : 0;
        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    /** Nome de quem liga, quando a operadora informa (so numero -> ""). */
    private static function personName(string $name, string $number): string
    {
        $name = trim($name, " \"'<>");
        return ($name === '' || $name === $number || preg_match('/^\+?\d+$/', $name) || self::isPlaceholderName($name))
            ? '' : mb_substr($name, 0, 60);
    }

    /** "Fulano <1006>" -> "Fulano"; so numero -> "". */
    private static function callerName(string $clid): string
    {
        $name = trim(preg_replace('/\s*<[^>]*>\s*$/', '', $clid) ?? '', " \"'<>");
        return (preg_match('/^\+?\d+$/', $name) || self::isPlaceholderName($name)) ? '' : mb_substr($name, 0, 60);
    }

    /** Textos que o Asterisk/operadora usam quando nao ha nome (ex.: "<unknown>"). */
    private static function isPlaceholderName(string $name): bool
    {
        return in_array(mb_strtolower(trim($name, " <>\"'")), ['', 'unknown', 'anonymous', 'anonimo', 'anônimo',
            'desconhecido', 'restricted', 'private', 'privado', 'unavailable', 'indisponivel', 'indisponível'], true);
    }

    /**
     * @param callable(): list<array<string, mixed>> $fetch
     * @param list<string> $errors
     * @return list<array<string, mixed>>
     */
    private static function safe(callable $fetch, string $label, array &$errors): array
    {
        try {
            return $fetch();
        } catch (RuntimeException $exception) {
            $errors[] = 'Falha ao consultar ' . $label . ': ' . $exception->getMessage();
            return [];
        }
    }
}
