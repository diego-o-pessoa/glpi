<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativawallpaper\OverviewPage;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);

global $CFG_GLPI;

Html::header('Ativa Wallpaper', '', 'admin', 'pluginativawallpapermenu', 'dashboard');
TemplateRenderer::getInstance()->display(
    '@ativawallpaper/dashboard.html.twig',
    OverviewPage::context($_GET, $CFG_GLPI['root_doc'])
);
Html::footer();
