<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativarede\Inventory;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Aba "Posição física" na ficha do Computador: mesa, porta do switch,
 * monitores ligados e o historico de mudancas desta maquina. Somente leitura.
 */
class PluginAtivaredeComputerTab extends CommonGLPI
{
    public static $rightname = PluginAtivaredeProfile::RIGHT_VIEW;

    public static function getTypeName($nb = 0): string
    {
        return 'Posição física';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Computer && !$item->isNewItem() && PluginAtivaredeProfile::canView()) {
            return self::createTabEntry('Posição física', 0, $item::class, 'ti ti-map-pin');
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if (!$item instanceof Computer || !PluginAtivaredeProfile::canView()) {
            return false;
        }
        global $CFG_GLPI;
        TemplateRenderer::getInstance()->display('@ativarede/computer_tab.html.twig', [
            'data'      => Inventory::forComputer((int) $item->getID()),
            'plant_url' => $CFG_GLPI['root_doc'] . '/plugins/ativarede/front/planta.php',
        ]);
        return true;
    }
}
