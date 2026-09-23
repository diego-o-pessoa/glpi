<?php

declare(strict_types=1);

function plugin_ativaworkspace_install(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaworkspace_do_install();
}

function plugin_ativaworkspace_uninstall(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaworkspace_do_uninstall();
}
