<?php

declare(strict_types=1);

class PluginAtivawallpaperMenu extends CommonGLPI
{
    public static function getTypeName($nb = 0): string
    {
        return 'Ativa Wallpaper';
    }

    public static function getIcon(): string
    {
        return 'ti ti-photo';
    }

    public static function canView(): bool
    {
        return Session::haveRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);
    }

    public static function getMenuName($nb = 0): string
    {
        return self::getTypeName($nb);
    }

    public static function getMenuContent(): array
    {
        global $CFG_GLPI;

        $base = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper/front';
        // Always open "Visao geral"; Configuracoes stays one tab away.
        $landingPage = $base . '/dashboard.php';
        $menu = [
            'title' => self::getTypeName(),
            'page'  => $landingPage,
            'icon'  => self::getIcon(),
            'links' => [
                'search' => $landingPage,
            ],
        ];

        $menu['options']['dashboard'] = [
            'title' => 'Visão geral',
            'page'  => $base . '/dashboard.php',
            'icon'  => 'ti ti-home',
        ];
        $menu['options']['history'] = [
            'title' => 'Histórico',
            'page'  => $base . '/history.php',
            'icon'  => 'ti ti-history',
        ];
        $menu['options']['events'] = [
            'title' => 'Alterações detectadas',
            'page'  => $base . '/events.php',
            'icon'  => 'ti ti-shield-check',
        ];
        // GLPI Agent and client updates are distributed by the Ativa Updater plugin.
        if (Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ)) {
            $menu['options']['settings'] = [
                'title' => 'Configurações',
                'page'  => $base . '/settings.php',
                'icon'  => 'ti ti-settings',
            ];
        }

        return $menu;
    }
}
