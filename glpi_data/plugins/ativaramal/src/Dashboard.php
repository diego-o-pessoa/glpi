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

    /** @return array<string, mixed> */
    public static function payload(): array
    {
        $errors = [];
        $extensions = self::safe(static fn () => TwApi::extensions(), 'ramais', $errors);
        $calls = self::safe(static fn () => TwApi::callsToday(), 'ligações', $errors);
        $channels = self::safe(static fn () => TwApi::realtime(), 'tempo real', $errors);

        // --- ramais (com filial/setor) e indices para atribuir as ligacoes
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

        // --- ligacoes de hoje
        $totals = self::emptyStats() + ['nao_atendidas_sem_ramal' => 0];
        $recent = [];
        usort($calls, static fn ($a, $b) => strcmp((string) ($b['calldate'] ?? ''), (string) ($a['calldate'] ?? '')));
        foreach ($calls as $call) {
            $direction = self::DIRECTIONS[(int) ($call['direction'] ?? 0)] ?? 'outra';
            $answered = (int) ($call['disposition'] ?? 0) === self::DISPOSITION_ANSWERED;
            $key = self::attribute($call, $direction, $byId, $byNumber);

            self::count($totals, $direction, $answered, (int) ($call['billsec'] ?? 0));
            if ($key !== null) {
                self::count($rows[$key]['stats'], $direction, $answered, (int) ($call['billsec'] ?? 0));
            } elseif (!$answered && $direction === 'entrada') {
                $totals['nao_atendidas_sem_ramal']++;
            }

            if (count($recent) < self::RECENT_LIMIT) {
                $recent[] = [
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

        // --- tempo real: agrupa os canais por ligacao (Linkedid)
        $live = [];
        foreach ($channels as $channel) {
            $linked = (string) ($channel['Linkedid'] ?? $channel['Uniqueid'] ?? '');
            if ($linked === '') {
                continue;
            }
            $live[$linked] ??= ['externo' => '', 'ramal' => '', 'fila' => '', 'duracao' => '00:00:00', 'estado' => ''];
            $ext = self::channelExtension((string) ($channel['Channel'] ?? ''), (string) ($channel['CallerIDNum'] ?? ''), $byNumber);
            if ($ext !== null) {
                $rows[$ext]['em_ligacao'] = true;
                $live[$linked]['ramal'] = $rows[$ext]['nome'] . ' (' . $rows[$ext]['ramal'] . ')';
                $live[$linked]['filial'] = $rows[$ext]['filial'];
                $live[$linked]['setor'] = $rows[$ext]['setor'];
                $other = (string) ($channel['ConnectedLineNum'] ?? '');
                if ($live[$linked]['externo'] === '' && $other !== '' && !isset($byNumber[$other])) {
                    $live[$linked]['externo'] = $other;
                }
            } elseif ($live[$linked]['externo'] === '') {
                $live[$linked]['externo'] = (string) ($channel['CallerIDNum'] ?? '');
            }
            if (($channel['Context'] ?? '') === 'Fila' || ($channel['Application'] ?? '') === 'Queue') {
                $live[$linked]['fila'] = (string) ($channel['Exten'] ?? '');
            }
            if (strcmp((string) ($channel['Duration'] ?? ''), $live[$linked]['duracao']) > 0) {
                $live[$linked]['duracao'] = (string) $channel['Duration'];
            }
            $live[$linked]['estado'] = (string) ($channel['ChannelStateDesc'] ?? $live[$linked]['estado']);
        }
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
                'em_fila'         => count(array_filter($live, static fn ($l) => $l['fila'] !== '' && $l['ramal'] === '')),
            ],
            'tree'    => $tree,
            'live'    => $live,
            'recent'  => $recent,
        ];
        $payload['signature'] = sha1((string) json_encode([$payload['kpis'], $tree, $live, $recent, $errors]));
        return $payload;
    }

    /** @return array<string, int> */
    private static function emptyStats(): array
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
    private static function attribute(array $call, string $direction, array $byId, array $byNumber): ?string
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
        if (preg_match('#^PJSIP/(\d+)-#', $channel, $matches) && isset($byNumber[$matches[1]])) {
            return $byNumber[$matches[1]];
        }
        return str_starts_with($channel, 'PJSIP/saida') ? null : ($byNumber[$callerId] ?? null);
    }

    /** "Fulano <1006>" -> "Fulano"; so numero -> "". */
    private static function callerName(string $clid): string
    {
        $name = trim(preg_replace('/\s*<[^>]*>\s*$/', '', $clid) ?? '', " \"'");
        return preg_match('/^\+?\d+$/', $name) ? '' : mb_substr($name, 0, 60);
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
