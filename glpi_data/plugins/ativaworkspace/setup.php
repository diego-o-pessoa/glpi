<?php

declare(strict_types=1);

use Glpi\Http\SessionManager;
use Glpi\Plugin\HookManager;
use Glpi\Plugin\Hooks;

define('PLUGIN_ATIVAWORKSPACE_VERSION', '0.6.2');
define('PLUGIN_ATIVAWORKSPACE_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVAWORKSPACE_MAX_GLPI', '11.1.0');
define('PLUGIN_ATIVAWORKSPACE_DIR', __DIR__);

function plugin_version_ativaworkspace(): array
{
    return [
        'name'         => 'Ativa Workspace',
        'version'      => PLUGIN_ATIVAWORKSPACE_VERSION,
        'author'       => 'Ativa Locacao',
        'license'      => 'GPLv3+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ATIVAWORKSPACE_MIN_GLPI,
                'max' => PLUGIN_ATIVAWORKSPACE_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_init_ativaworkspace(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['ativaworkspace'] = true;

    // Faz o GLPI reconhecer o segredo do Graph como configuracao sensivel.
    // Alem de criptografar na gravacao, isto inclui o valor nas rotacoes da
    // glpicrypt.key e mascara o historico de alteracoes da configuracao.
    (new HookManager('ativaworkspace'))->registerSecureConfigs([
        'graph_client_secret',
    ]);

    // API do executor (servico nas maquinas): token Bearer proprio, sem sessao.
    SessionManager::registerPluginStatelessPath('ativaworkspace', '#^/api/v1(?:/|$)#');

    $plugin = new Plugin();
    if (!$plugin->isActivated('ativaworkspace')) {
        return;
    }

    Plugin::registerClass(PluginAtivaworkspaceMenu::class);
    Plugin::registerClass(PluginAtivaworkspaceProfile::class, ['addtabon' => [Profile::class]]);

    if (PluginAtivaworkspaceProfile::canViewWorkspace()) {
        // Chave nova + valor em array = secao propria na barra lateral
        // (Html::generateMenuSession), com os subitens do menu multi-entradas.
        $PLUGIN_HOOKS['menu_toadd']['ativaworkspace'] = [
            'ativaworkspace' => [PluginAtivaworkspaceMenu::class],
        ];
    }

    if (Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_CONFIG, READ)) {
        $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['ativaworkspace'] = 'front/settings.php';
    }

    $PLUGIN_HOOKS[Hooks::ADD_CSS]['ativaworkspace'] = ['css/workspace.css'];
}

function plugin_ativaworkspace_check_prerequisites(): bool
{
    if (version_compare(PHP_VERSION, '8.2.0', '<')) {
        echo 'Ativa Workspace requer PHP 8.2 ou superior.';
        return false;
    }

    if (!defined('GLPI_VERSION') || version_compare(GLPI_VERSION, PLUGIN_ATIVAWORKSPACE_MIN_GLPI, '<')) {
        echo 'Ativa Workspace requer GLPI 11.0 ou superior.';
        return false;
    }

    if (!extension_loaded('curl') || !extension_loaded('sodium')) {
        echo 'Ativa Workspace requer as extensões PHP cURL e Sodium.';
        return false;
    }

    return true;
}

function plugin_ativaworkspace_check_config(): bool
{
    return true;
}
