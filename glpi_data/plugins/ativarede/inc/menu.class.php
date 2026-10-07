<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Secao "Ativa Rede" na barra lateral: planta das salas, alertas de mudanca
 * e a lista de equipamentos (maquinas, monitores e switches).
 */
class PluginAtivaredeMenu extends CommonGLPI
{
    public static $rightname = PluginAtivaredeProfile::RIGHT_VIEW;

    public static function getMenuName(): string
    {
        return 'Ativa Rede';
    }

    /** Icone da secao na barra lateral (lido pelo Html::generateMenuSession). */
    public static function getIcon(): string
    {
        return 'ti ti-network';
    }

    public static function getMenuContent(): array
    {
        global $CFG_GLPI;

        if (!PluginAtivaredeProfile::canView()) {
            return [];
        }

        $base = $CFG_GLPI['root_doc'] . '/plugins/ativarede/front';
        return [
            'title'            => self::getMenuName(),
            'is_multi_entries' => true,
            'planta' => [
                'title' => 'Planta',
                'page'  => $base . '/planta.php',
                'icon'  => 'ti ti-layout-board',
            ],
            'alertas' => [
                'title' => 'Alertas',
                'page'  => $base . '/alertas.php',
                'icon'  => 'ti ti-bell',
            ],
            'equipamentos' => [
                'title' => 'Equipamentos',
                'page'  => $base . '/equipamentos.php',
                'icon'  => 'ti ti-devices',
            ],
        ];
    }

    public static function canView(): bool
    {
        return PluginAtivaredeProfile::canView();
    }
}
