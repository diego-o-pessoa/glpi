<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\DashboardService;
use GlpiPlugin\Ativawallpaper\ClientRepository;
use GlpiPlugin\Ativawallpaper\WallpaperManager;

class PluginAtivawallpaperClient extends CommonDBTM
{
    public static $rightname = PluginAtivawallpaperProfile::RIGHT_VIEW;
    public $dohistory = true;

    public static function getTypeName($nb = 0): string
    {
        return $nb === 1 ? 'Cliente Ativa Wallpaper' : 'Clientes Ativa Wallpaper';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Computer && Session::haveRight(self::$rightname, READ)) {
            return self::createTabEntry('Wallpaper', 0, $item::getType(), 'ti ti-photo');
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if (!$item instanceof Computer) {
            return false;
        }
        Session::checkRight(self::$rightname, READ);
        $client = (new ClientRepository())->findByComputer((int) $item->getID());
        $current = (new WallpaperManager())->current();
        $computedStatus = $client === null ? 'unregistered' : (new DashboardService())->computeStatus($client, $current);
        TemplateRenderer::getInstance()->display('@ativawallpaper/computer_tab.html.twig', [
            'client'          => $client,
            'current'         => $current,
            'computed_status' => $computedStatus,
            'can_manage'      => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CLIENTS, UPDATE),
        ]);
        return true;
    }
}
