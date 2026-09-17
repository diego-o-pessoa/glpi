<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginAtivaremoteMenu extends CommonGLPI
{
    public static $rightname = 'plugin_ativaremote';

    public static function getMenuName(): string
    {
        return 'Ativa Remote';
    }

    public static function getMenuContent(): array
    {
        global $CFG_GLPI;

        $menu = [];
        if (Session::haveRight(self::$rightname, READ) || Session::haveRight('config', UPDATE)) {
            $landingPage = $CFG_GLPI['root_doc'] . '/plugins/ativaremote/front/dashboard.php';
            $menu['title'] = self::getMenuName();
            $menu['page']  = $landingPage;
            $menu['icon']  = 'ti ti-device-desktop';
            $menu['options']['dashboard'] = [
                'title' => 'Painel de Controle',
                'page'  => $landingPage,
                'icon'  => 'ti ti-device-desktop',
            ];
        }

        return $menu;
    }

    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ) || Session::haveRight('config', UPDATE);
    }
}
