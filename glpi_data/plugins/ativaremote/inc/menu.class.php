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
        if (Session::haveRight(self::$rightname, READ)) {
            $menu['title'] = self::getMenuName();
            $menu['page']  = $CFG_GLPI['root_doc'] . '/plugins/ativaremote/front/dashboard.php';
            $menu['icon']  = 'ti ti-device-desktop';
        }

        return $menu;
    }

    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ);
    }
}
