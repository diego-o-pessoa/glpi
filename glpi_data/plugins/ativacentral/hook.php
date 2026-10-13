<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Settings;

/**
 * Instalacao: so grava as opcoes padrao (o plugin nao cria tabelas).
 */
function plugin_ativacentral_install(): bool
{
    require_once PLUGIN_ATIVACENTRAL_DIR . '/src/Settings.php';

    Settings::installDefaults();
    return true;
}

/**
 * Desinstalacao: remove as opcoes. Nada do GLPI e alterado pelo plugin, entao
 * nao ha mais nada a desfazer.
 */
function plugin_ativacentral_uninstall(): bool
{
    require_once PLUGIN_ATIVACENTRAL_DIR . '/src/Settings.php';

    Settings::removeAll();
    return true;
}
