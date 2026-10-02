<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use PluginAtivaramalProfile;
use Session;

/**
 * O que o usuario logado pode ver no dashboard.
 *
 * - Direito "Ver todos os setores e filiais" (ou gerenciar a integracao):
 *   ve tudo (escopo null).
 * - Demais: so a FILIAL dele (Localizacao do usuario no GLPI) e os SETORES
 *   dele (nomes dos grupos do GLPI de que participa). O filtro e aplicado no
 *   servidor (Dashboard::payload), nao so na tela.
 */
final class AccessScope
{
    /**
     * @return array{filial: string, setores: list<string>, ok: bool, motivo: string}|null null = ve tudo
     */
    public static function current(): ?array
    {
        if (PluginAtivaramalProfile::canViewAll()) {
            return null;
        }
        global $DB;

        $userId = (int) Session::getLoginUserID();
        $user = $DB->request([
            'SELECT'    => ['glpi_users.groups_id', 'glpi_locations.name AS location_name'],
            'FROM'      => 'glpi_users',
            'LEFT JOIN' => ['glpi_locations' => ['ON' => ['glpi_locations' => 'id', 'glpi_users' => 'locations_id']]],
            'WHERE'     => ['glpi_users.id' => $userId],
            'LIMIT'     => 1,
        ])->current();
        $filial = trim((string) ($user['location_name'] ?? ''));

        $groupIds = [(int) ($user['groups_id'] ?? 0)];
        foreach ($DB->request(['SELECT' => ['groups_id'], 'FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $userId]]) as $row) {
            $groupIds[] = (int) $row['groups_id'];
        }
        $groupIds = array_values(array_unique(array_filter($groupIds)));
        $sectors = [];
        if ($groupIds !== []) {
            foreach ($DB->request(['SELECT' => ['name'], 'FROM' => 'glpi_groups', 'WHERE' => ['id' => $groupIds]]) as $row) {
                $sectors[] = trim((string) $row['name']);
            }
        }
        $sectors = array_values(array_unique(array_filter($sectors)));

        $motivo = '';
        if ($filial === '') {
            $motivo = 'Seu usuário não tem Localização (filial) no GLPI. Peça ao T.I. para preencher.';
        } elseif ($sectors === []) {
            $motivo = 'Seu usuário não participa de nenhum grupo (setor) no GLPI. Peça ao T.I. para incluir.';
        }
        return ['filial' => $filial, 'setores' => $sectors, 'ok' => $motivo === '', 'motivo' => $motivo];
    }

    /** O ramal (filial/setor resolvidos) esta dentro do escopo? */
    public static function allows(?array $scope, string $filial, string $setor): bool
    {
        if ($scope === null) {
            return true;
        }
        if (!$scope['ok'] || self::norm($filial) !== self::norm($scope['filial'])) {
            return false;
        }
        foreach ($scope['setores'] as $allowed) {
            if (self::norm($allowed) === self::norm($setor)) {
                return true;
            }
        }
        return false;
    }

    /** Comparacao sem diferenca de maiusculas, acentos e espacos extras. */
    private static function norm(string $text): string
    {
        $text = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? ''));
        $plain = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        return $plain !== false ? $plain : $text;
    }
}
