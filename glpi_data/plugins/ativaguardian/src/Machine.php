<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

use CommonDBTM;

/**
 * Tipo de item da tabela glpi_plugin_ativaguardian_machines. Existe para o
 * motor de busca do GLPI conseguir montar a coluna "Usuario do Windows
 * (Guardian)" na lista de Computadores (o GLPI 11 exige um itemtype para cada
 * tabela usada numa coluna). Somente leitura: nao ha formulario nem edicao;
 * os registros sao gravados pela API do Guardian.
 */
class Machine extends CommonDBTM
{
    public static $rightname = 'plugin_ativaguardian_view';

    public static function getTypeName($nb = 0): string
    {
        return 'Máquina (Ativa Guardian)';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canUpdate(): bool
    {
        return false;
    }

    public static function canDelete(): bool
    {
        return false;
    }

    public static function canPurge(): bool
    {
        return false;
    }
}
