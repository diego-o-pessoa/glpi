<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Secao "Ativa Ramal" na barra lateral. Nesta etapa so ha a integracao com a
 * TW Solutions; chamadas, ramais e painel entram como novas entradas depois.
 */
class PluginAtivaramalMenu extends CommonGLPI
{
    public static $rightname = PluginAtivaramalProfile::RIGHT_VIEW;

    public static function getMenuName(): string
    {
        return 'Ativa Ramal';
    }

    /** Icone da secao na barra lateral (lido pelo Html::generateMenuSession). */
    public static function getIcon(): string
    {
        return 'ti ti-phone';
    }

    public static function getMenuContent(): array
    {
        global $CFG_GLPI;

        if (!PluginAtivaramalProfile::canView()) {
            return [];
        }

        $menu = [
            'title'            => self::getMenuName(),
            'is_multi_entries' => true,
        ];
        $menu['dashboard'] = [
            'title' => 'Dashboard',
            'page'  => $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/dashboard.php',
            'icon'  => 'ti ti-chart-bar',
        ];
        if (Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, READ)) {
            $menu['ramais'] = [
                'title' => 'Ramais (filial e setor)',
                'page'  => $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/ramais.php',
                'icon'  => 'ti ti-sitemap',
            ];
            $menu['config'] = [
                'title' => 'Integração TW Solutions',
                'page'  => $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/config.php',
                'icon'  => 'ti ti-plug-connected',
            ];
        }
        return $menu;
    }

    public static function canView(): bool
    {
        return PluginAtivaramalProfile::canView();
    }
}
