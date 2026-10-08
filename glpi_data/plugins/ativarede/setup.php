<?php

declare(strict_types=1);

use Glpi\Http\SessionManager;
use Glpi\Plugin\Hooks;

define('PLUGIN_ATIVAREDE_VERSION', '0.1.6');
define('PLUGIN_ATIVAREDE_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVAREDE_MAX_GLPI', '12.0.0');
define('PLUGIN_ATIVAREDE_DIR', __DIR__);

/**
 * Roda ANTES de o GLPI iniciar a sessao PHP. A API recebe os relatorios do
 * Ativa Guardian (token Bearer, servico -> servidor): registrada aqui como
 * stateless, cada envio nao cria arquivo em files/_sessions.
 */
function plugin_ativarede_boot(): void
{
    SessionManager::registerPluginStatelessPath('ativarede', '#^/api/v1(?:/|$)#');
}

function plugin_init_ativarede(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['ativarede'] = true;

    $plugin = new Plugin();
    if (!$plugin->isActivated('ativarede')) {
        return;
    }

    Plugin::registerClass(PluginAtivaredeMenu::class);
    Plugin::registerClass(PluginAtivaredeProfile::class, ['addtabon' => [Profile::class]]);
    Plugin::registerClass(PluginAtivaredeComputerTab::class, ['addtabon' => [Computer::class]]);

    if (PluginAtivaredeProfile::canView()) {
        // Chave nova + valor em array = secao propria na barra lateral.
        $PLUGIN_HOOKS['menu_toadd']['ativarede'] = [
            'ativarede' => [PluginAtivaredeMenu::class],
        ];
    }
}

function plugin_version_ativarede(): array
{
    return [
        'name'         => 'Ativa Rede',
        'version'      => PLUGIN_ATIVAREDE_VERSION,
        'author'       => 'Ativa Locacao',
        'license'      => 'GPLv3+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ATIVAREDE_MIN_GLPI,
                'max' => PLUGIN_ATIVAREDE_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_ativarede_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_ATIVAREDE_MIN_GLPI, '<') || version_compare(GLPI_VERSION, PLUGIN_ATIVAREDE_MAX_GLPI, '>=')) {
        echo 'Este plugin requer o GLPI >= ' . PLUGIN_ATIVAREDE_MIN_GLPI . ' e < ' . PLUGIN_ATIVAREDE_MAX_GLPI;
        return false;
    }
    return true;
}

function plugin_ativarede_check_config(): bool
{
    return true;
}
