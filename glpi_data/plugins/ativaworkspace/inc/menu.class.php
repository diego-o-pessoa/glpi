<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Page;

class PluginAtivaworkspaceMenu extends CommonGLPI
{
    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_VIEW;

    public static function getMenuName(): string
    {
        return 'Ativa Workspace';
    }

    /** Icone da secao na barra lateral (lido pelo Html::generateMenuSession). */
    public static function getIcon(): string
    {
        return 'ti ti-device-desktop';
    }

    /**
     * Menu multi-entradas: cada secao vira um subitem da secao "Ativa Workspace".
     * A chave 'title' so da nome a secao; o template do menu ignora o que nao
     * tem 'page'. As chaves das entradas sao as que o Html::header recebe.
     */
    public static function getMenuContent(): array
    {
        if (!PluginAtivaworkspaceProfile::canViewWorkspace()) {
            return [];
        }

        $menu = [
            'title'            => self::getMenuName(),
            'is_multi_entries' => true,
        ];

        foreach (Page::sections() as $key => $section) {
            if (!Page::canAccess($key)) {
                continue;
            }
            $menu[$key] = [
                'title' => $section['title'],
                'page'  => Page::url($key),
                'icon'  => $section['icon'],
            ];
            if (isset($section['form']) && Session::haveRight($section['right'], CREATE)) {
                $menu[$key]['links']['add'] = Page::url($section['form']);
            }
        }

        return $menu;
    }

    public static function canView(): bool
    {
        return PluginAtivaworkspaceProfile::canViewWorkspace();
    }
}
