<?php

declare(strict_types=1);

function plugin_ativawallpaper_install(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativawallpaper_do_install();
}

function plugin_ativawallpaper_uninstall(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativawallpaper_do_uninstall();
}
