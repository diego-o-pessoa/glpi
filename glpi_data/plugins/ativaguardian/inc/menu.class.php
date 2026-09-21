<?php

class PluginAtivaguardianMenu extends CommonGLPI
{
    public static $rightname = 'plugin_ativaguardian_view';

    public static function getMenuName(): string
    {
        return __('Ativa Guardian', 'ativaguardian');
    }

    public static function getMenuContent(): array
    {
        return [
            'title' => self::getMenuName(),
            'page'  => '/plugins/ativaguardian/front/dashboard.php',
            'icon'  => 'fas fa-shield-alt',
        ];
    }
}
