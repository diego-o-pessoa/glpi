<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;

/**
 * Etapa de um provisionamento (glpi_plugin_ativaworkspace_jobsteps).
 * Estrutura pronta para o Job Engine das proximas etapas.
 */
final class JobStep extends CommonDBTM
{
    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_PROVISION;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Etapas do provisionamento' : 'Etapa do provisionamento';
    }
}
