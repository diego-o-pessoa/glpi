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

    public static function getMenuContent(): array
    {
        if (!PluginAtivaworkspaceProfile::canViewWorkspace()) {
            return [];
        }

        $menu = [
            'title' => self::getMenuName(),
            'page'  => Page::url('overview'),
            'icon'  => 'ti ti-rocket',
        ];

        foreach (Page::sections() as $key => $section) {
            if (!Page::canAccess($key)) {
                continue;
            }
            $menu['options'][$key] = [
                'title' => $section['title'],
                'page'  => Page::url($key),
                'icon'  => $section['icon'],
            ];
            if (isset($section['form']) && Session::haveRight($section['right'], CREATE)) {
                $menu['options'][$key]['links']['add'] = Page::url($section['form']);
            }
        }

        return $menu;
    }

    public static function canView(): bool
    {
        return PluginAtivaworkspaceProfile::canViewWorkspace();
    }
}
