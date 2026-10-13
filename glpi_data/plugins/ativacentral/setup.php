<?php

declare(strict_types=1);

use Glpi\Plugin\Hooks;
use GlpiPlugin\Ativacentral\Shell;

define('PLUGIN_ATIVACENTRAL_VERSION', '1.0.0');
define('PLUGIN_ATIVACENTRAL_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVACENTRAL_MAX_GLPI', '12.0.0');
define('PLUGIN_ATIVACENTRAL_DIR', __DIR__);

/**
 * Ativa Central: novo visual da interface de administracao do GLPI (barra
 * lateral, topo, pagina inicial e telas de modulo com botoes).
 *
 * Nada do nucleo do GLPI e alterado: tudo entra por ganchos de plugin e pode
 * ser desligado em Configurar > Plugins > Ativa Central (ou desativando o
 * plugin). Com ele desligado o GLPI volta exatamente ao visual original.
 */
function plugin_init_ativacentral(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['ativacentral'] = true;

    $plugin = new Plugin();
    if (!$plugin->isActivated('ativacentral')) {
        return;
    }

    $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['ativacentral'] = 'front/config.php';

    // O visual novo nunca pode derrubar o GLPI: qualquer falha aqui e
    // registrada e a pagina segue com o visual original.
    try {
        Shell::boot();
    } catch (Throwable $exception) {
        Toolbox::logInFile('ativacentral', 'Falha ao iniciar o Ativa Central: ' . $exception->getMessage() . "\n");
    }
}

function plugin_version_ativacentral(): array
{
    return [
        'name'         => 'Ativa Central',
        'version'      => PLUGIN_ATIVACENTRAL_VERSION,
        'author'       => 'Ativa Locacao',
        'license'      => 'GPLv3+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ATIVACENTRAL_MIN_GLPI,
                'max' => PLUGIN_ATIVACENTRAL_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_ativacentral_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_ATIVACENTRAL_MIN_GLPI, '<') || version_compare(GLPI_VERSION, PLUGIN_ATIVACENTRAL_MAX_GLPI, '>=')) {
        echo 'Este plugin requer o GLPI >= ' . PLUGIN_ATIVACENTRAL_MIN_GLPI . ' e < ' . PLUGIN_ATIVACENTRAL_MAX_GLPI;
        return false;
    }
    return true;
}

function plugin_ativacentral_check_config(): bool
{
    return true;
}
