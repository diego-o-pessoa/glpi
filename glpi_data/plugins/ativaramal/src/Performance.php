<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use RuntimeException;

/**
 * Aba "Desempenho": ranking por pessoa e tempo medio de atendimento, por
 * periodo (hoje, semana, mes), dentro do escopo do usuario (AccessScope).
 *
 * Definicoes (mesma atribuicao de ramal do Dashboard):
 *   - Atendeu: ligacoes de ENTRADA atendidas pelo ramal;
 *   - Ligou: ligacoes de SAIDA feitas pelo ramal;
 *   - Falou: tempo falado (billsec) de todas as ligacoes atendidas;
 *   - TMA (tempo medio de atendimento): tempo falado medio das entradas atendidas.
 */
final class Performance
{
    public const PERIODS = ['hoje' => 'Hoje', 'semana' => 'Esta semana', 'mes' => 'Este mês'];
    private const RANK_SIZE = 10;

    /** @return array{0: string, 1: string} inicio e fim (Y-m-d H:i:s) do periodo */
    public static function range(string $period): array
    {
        $end = date('Y-m-d H:i:s');
        return match ($period) {
            'semana' => [date('Y-m-d', strtotime('monday this week')) . ' 00:00:00', $end],
            'mes'    => [date('Y-m-01') . ' 00:00:00', $end],
            default  => [date('Y-m-d') . ' 00:00:00', $end],
        };
    }

    /** @return array<string, mixed> */
    public static function payload(string $period, ?array $scope): array
    {
        $period = isset(self::PERIODS[$period]) ? $period : 'hoje';
        [$start, $end] = self::range($period);
        $error = '';
        $missingDays = [];
        try {
            [$rows, $byId, $byNumber] = Dashboard::extensionIndex(TwApi::extensions(), $scope);
            $result = TwApi::callsBetween($start, $end);
            $calls = $result['calls'];
            $missingDays = $result['falhas'];
        } catch (RuntimeException $exception) {
            $rows = $byId = $byNumber = $calls = [];
            $error = $exception->getMessage();
        }

        $people = [];
        foreach ($rows as $key => $row) {
            $people[$key] = [
                'nome' => $row['nome'], 'ramal' => $row['ramal'], 'pessoa' => $row['pessoa'],
                'filial' => $row['filial'], 'setor' => $row['setor'],
                'atendeu' => 0, 'ligou' => 0, 'falado' => 0, 'falado_entrada' => 0, 'nao_atendidas' => 0, 'total' => 0,
            ];
        }

        foreach ($calls as $call) {
            $direction = Dashboard::DIRECTIONS[(int) ($call['direction'] ?? 0)] ?? 'outra';
            $key = Dashboard::attribute($call, $direction, $byId, $byNumber);
            if ($key === null || !isset($people[$key])) {
                continue; // sem ramal, ou ramal fora do escopo do usuario
            }
            $answered = (int) ($call['disposition'] ?? 0) === Dashboard::DISPOSITION_ANSWERED;
            $billsec = max(0, (int) ($call['billsec'] ?? 0));
            $person = &$people[$key];
            $person['total']++;
            if ($direction === 'saida') {
                $person['ligou']++;
            }
            if ($answered) {
                $person['falado'] += $billsec;
                if ($direction === 'entrada') {
                    $person['atendeu']++;
                    $person['falado_entrada'] += $billsec;
                }
            } elseif ($direction === 'entrada') {
                $person['nao_atendidas']++;
            }
            unset($person);
        }

        foreach ($people as &$person) {
            $person['tma'] = $person['atendeu'] > 0 ? (int) round($person['falado_entrada'] / $person['atendeu']) : 0;
        }
        unset($person);

        // Setores: soma das pessoas.
        $sectors = [];
        foreach ($people as $person) {
            $key = $person['filial'] . ' · ' . $person['setor'];
            $sectors[$key] ??= ['filial' => $person['filial'], 'setor' => $person['setor'], 'pessoas' => 0,
                                'atendeu' => 0, 'ligou' => 0, 'falado' => 0, 'falado_entrada' => 0, 'nao_atendidas' => 0];
            $sectors[$key]['pessoas']++;
            foreach (['atendeu', 'ligou', 'falado', 'falado_entrada', 'nao_atendidas'] as $field) {
                $sectors[$key][$field] += $person[$field];
            }
        }
        foreach ($sectors as &$sector) {
            $sector['tma'] = $sector['atendeu'] > 0 ? (int) round($sector['falado_entrada'] / $sector['atendeu']) : 0;
        }
        unset($sector);
        uasort($sectors, static fn ($a, $b) => $b['atendeu'] <=> $a['atendeu'] ?: strnatcasecmp($a['setor'], $b['setor']));

        $active = array_values(array_filter($people, static fn ($p) => $p['total'] > 0));
        $rank = static function (string $field) use ($active): array {
            $list = array_values(array_filter($active, static fn ($p) => $p[$field] > 0));
            usort($list, static fn ($a, $b) => $b[$field] <=> $a[$field] ?: strnatcasecmp($a['nome'], $b['nome']));
            return array_slice($list, 0, self::RANK_SIZE);
        };
        usort($active, static fn ($a, $b) => $b['atendeu'] <=> $a['atendeu'] ?: strnatcasecmp($a['nome'], $b['nome']));

        $totalAnswered = array_sum(array_column($active, 'atendeu'));
        return [
            'period'   => $period,
            'label'    => self::PERIODS[$period],
            'start'    => $start,
            'end'      => $end,
            'error'    => $error,
            // Dias que a TW nao devolveu (HTTP 500): ficam fora dos numeros.
            'warning'  => $missingDays === [] ? '' : 'A TW não devolveu as ligações de '
                . implode(', ', array_map(static fn ($d) => date('d/m', strtotime($d)), $missingDays))
                . ' (erro no servidor da TW). Esses dias ficaram fora dos números.',
            'scope'    => $scope,
            'totals'   => [
                'atendeu' => $totalAnswered,
                'ligou'   => array_sum(array_column($active, 'ligou')),
                'falado'  => array_sum(array_column($active, 'falado')),
                'tma'     => $totalAnswered > 0 ? (int) round(array_sum(array_column($active, 'falado_entrada')) / $totalAnswered) : 0,
                'pessoas' => count($active),
            ],
            'ranking'  => [
                'atendeu' => $rank('atendeu'),
                'ligou'   => $rank('ligou'),
                'falado'  => $rank('falado'),
            ],
            'people'   => $active,
            'sectors'  => array_values($sectors),
        ];
    }
}
