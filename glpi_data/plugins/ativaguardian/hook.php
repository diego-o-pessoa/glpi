<?php

declare(strict_types=1);

function plugin_ativaguardian_install(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaguardian_do_install();
}

function plugin_ativaguardian_uninstall(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaguardian_do_uninstall();
}

/**
 * Coluna extra na lista de Computadores (Ativos): "Grupo do usuário" — os
 * grupos do usuário atribuído ao computador (definidos, por exemplo, no
 * Primeiro acesso). Vazio quando o computador não tem usuário ou o usuário
 * ainda não tem grupo. Só leitura: não altera nada.
 */
function plugin_ativaguardian_getAddSearchOptionsNew($itemtype): array
{
    if ($itemtype !== Computer::class) {
        return [];
    }
    return [
        [
            'id'            => '7801',
            'table'         => 'glpi_groups',
            'field'         => 'completename',
            'name'          => 'Grupo do usuário',
            'datatype'      => 'dropdown',
            'forcegroupby'  => true,
            'massiveaction' => false,
            'joinparams'    => [
                'beforejoin' => [
                    'table'      => 'glpi_groups_users',
                    'joinparams' => [
                        'jointype'   => 'child',
                        'beforejoin' => [
                            'table'      => 'glpi_users',
                            'joinparams' => [],
                        ],
                    ],
                ],
            ],
        ],
    ];
}
