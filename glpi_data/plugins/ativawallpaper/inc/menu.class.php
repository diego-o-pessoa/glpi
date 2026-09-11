<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\ConfigService;

class PluginAtivawallpaperMenu extends CommonGLPI
{
    public static function getTypeName($nb = 0): string
    {
        return 'Agent';
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
        $landingPage = !ConfigService::getBool('setup_completed')
            && Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ)
            ? $base . '/settings.php'
            : $base . '/dashboard.php';
        $menu = [
            'title' => self::getTypeName(),
            'page'  => $landingPage,
            'icon'  => self::getIcon(),
            'links' => [
                'search' => $landingPage,
            ],
        ];

        $menu['options']['dashboard'] = [
            'title' => 'Dashboard',
            'page'  => $base . '/dashboard.php',
            'icon'  => 'ti ti-dashboard',
        ];
        $menu['options']['history'] = [
            'title' => 'Historico',
            'page'  => $base . '/history.php',
            'icon'  => 'ti ti-history',
        ];
        $menu['options']['events'] = [
            'title' => 'Alteracoes detectadas',
            'page'  => $base . '/events.php',
            'icon'  => 'ti ti-shield-check',
        ];
        if (Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ)) {
            $menu['options']['updates'] = [
                'title' => 'Atualizacoes',
                'page'  => $base . '/updates.php',
                'icon'  => 'ti ti-cloud-download',
            ];
            $menu['options']['settings'] = [
                'title' => 'Configuracoes',
                'page'  => $base . '/settings.php',
                'icon'  => 'ti ti-settings',
            ];
        }

        return $menu;
    }
}
