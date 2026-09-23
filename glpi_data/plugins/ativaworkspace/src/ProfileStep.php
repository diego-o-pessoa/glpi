<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;

/**
 * Etapa de um perfil de provisionamento (glpi_plugin_ativaworkspace_profilesteps).
 * Estrutura pronta; a edicao de etapas e a execucao entram nas proximas etapas.
 */
final class ProfileStep extends CommonDBTM
{
    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_PROFILES;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Etapas do perfil' : 'Etapa do perfil';
    }
}
