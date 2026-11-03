<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use RuntimeException;

/**
 * Lista de ramais: todos os ramais ativos de todas as filiais e setores,
 * com o estado do telefone (online, em ligacao, tocando). So consulta - sem
 * ligacoes do dia nem estatisticas, por isso e mais leve que o dashboard.
 * As consultas a TW usam o mesmo cache do dashboard (ramais 60 s, tempo
 * real 5 s): varias telas abertas nao multiplicam as chamadas a TW.
 */
final class Directory
{
    /** @return array<string, mixed> */
    public static function payload(): array
    {
        $errors = [];
        try {
            $extensions = TwApi::extensions();
        } catch (RuntimeException $exception) {
            $extensions = [];
            $errors[] = 'Falha ao consultar os ramais: ' . $exception->getMessage();
        }
        try {
            $channels = TwApi::realtime();
        } catch (RuntimeException $exception) {
            // Sem tempo real a lista continua util (so sem "em ligacao").
            $channels = [];
            $errors[] = 'Falha ao consultar o tempo real: ' . $exception->getMessage();
        }

        [$rows, , $byNumber] = Dashboard::extensionIndex($extensions, null);
        Dashboard::markLive($channels, $rows, $byNumber);

        $list = [];
        foreach ($rows as $row) {
            $list[] = [
                'ramal'      => (string) $row['ramal'],
                'nome'       => (string) $row['nome'],
                'pessoa'     => (string) ($row['pessoa'] ?? ''),
                'filial'     => (string) $row['filial'],
                'setor'      => (string) $row['setor'],
                'online'     => (bool) $row['online'],
                'em_ligacao' => (bool) $row['em_ligacao'],
                'tocando'    => (bool) $row['tocando'],
            ];
        }
        usort($list, static fn (array $a, array $b): int => [$a['filial'], $a['setor'], $a['nome']] <=> [$b['filial'], $b['setor'], $b['nome']]);

        $filiais = array_values(array_unique(array_column($list, 'filial')));
        $setores = array_values(array_unique(array_column($list, 'setor')));
        natcasesort($filiais);
        natcasesort($setores);

        $payload = [
            'updated' => date('H:i:s'),
            'errors'  => $errors,
            'kpis'    => [
                'total'      => count($list),
                'online'     => count(array_filter($list, static fn (array $r): bool => $r['online'])),
                'em_ligacao' => count(array_filter($list, static fn (array $r): bool => $r['em_ligacao'])),
                'filiais'    => count($filiais),
            ],
            'filiais' => array_values($filiais),
            'setores' => array_values($setores),
            'ramais'  => $list,
        ];
        $payload['signature'] = sha1((string) json_encode([$payload['kpis'], $list, $errors]));
        return $payload;
    }
}
