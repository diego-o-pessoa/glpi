<?php

class PluginAtivaupdaterMenu extends CommonGLPI
{
    public static $rightname = 'plugin_ativaupdater_view';

    public static function getMenuName(): string
    {
        return __('Ativa Updater', 'ativaupdater');
    }

    public static function getMenuContent(): array
    {
        $menu = [
            'title' => self::getMenuName(),
            'page'  => '/plugins/ativaupdater/front/dashboard.php',
            'icon'  => 'fas fa-cloud-upload-alt'
        ];

        return $menu;
    }
}
