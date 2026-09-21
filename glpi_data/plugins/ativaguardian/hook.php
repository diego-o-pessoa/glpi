<?php

declare(strict_types=1);

function plugin_ativaguardian_install(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaguardian_do_install();
}

function plugin_ativaguardian_uninstall(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativaguardian_do_uninstall();
}
