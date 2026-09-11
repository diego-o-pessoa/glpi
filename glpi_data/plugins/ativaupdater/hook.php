<?php

declare(strict_types=1);

function plugin_ativaupdater_install(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaupdater_do_install();
}

function plugin_ativaupdater_uninstall(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaupdater_do_uninstall();
}
