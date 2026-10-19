<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Menu do Ativa Print para o GLPI e Ativa Central.
 */
class PluginAtivaprintMenu extends CommonGLPI
{
    public static $rightname = 'plugin_ativaprint';

    public static function getMenuName(): string
    {
        return 'Ativa Print';
    }

    public static function getIcon(): string
    {
        return 'ti ti-printer';
    }

    public static function getMenuContent(): array
    {
        global $CFG_GLPI;

        $menu = [];
        if (Session::haveRight(self::$rightname, READ) || Session::haveRight('config', UPDATE)) {
            $landingPage = $CFG_GLPI['root_doc'] . '/plugins/ativaprint/front/dashboard.php';
            $menu['title'] = self::getMenuName();
            $menu['page']  = $landingPage;
            $menu['icon']  = self::getIcon();
            $menu['options']['dashboard'] = [
                'title' => 'Painel de Controle',
                'page'  => $landingPage,
                'icon'  => self::getIcon(),
            ];
            if (Session::haveRight('config', UPDATE)) {
                $menu['options']['config'] = [
                    'title' => 'Configurações',
                    'page'  => $CFG_GLPI['root_doc'] . '/plugins/ativaprint/front/config.php',
                    'icon'  => 'ti ti-settings',
                ];
            }
        }

        return $menu;
    }

    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ) || Session::haveRight('config', UPDATE);
    }
}

