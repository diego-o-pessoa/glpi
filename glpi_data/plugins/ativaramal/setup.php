<?php

declare(strict_types=1);

use Glpi\Http\SessionManager;
use Glpi\Plugin\HookManager;
use Glpi\Plugin\Hooks;

define('PLUGIN_ATIVARAMAL_VERSION', '0.2.6');
define('PLUGIN_ATIVARAMAL_MIN_GLPI', '11.0.0');
define('PLUGIN_ATIVARAMAL_MAX_GLPI', '12.0.0');
define('PLUGIN_ATIVARAMAL_DIR', __DIR__);

/**
 * Roda ANTES de o GLPI iniciar a sessao PHP. O webhook da TW Solutions
 * (servidor -> servidor, autenticado por token proprio) tem que ser
 * registrado aqui como stateless; no plugin_init (que roda depois), cada
 * chamada criaria um arquivo em files/_sessions.
 */
function plugin_ativaramal_boot(): void
{
    SessionManager::registerPluginStatelessPath('ativaramal', '#^/api/webhook$#');
}

/**
 * Segredos da integracao: o GLPI criptografa na gravacao (glpicrypt.key),
 * inclui nas rotacoes da chave e mascara no historico de configuracao.
 * Chamada no init e tambem na instalacao (quando o init ainda nao rodou).
 */
function plugin_ativaramal_register_secure_configs(): void
{
    (new HookManager('ativaramal'))->registerSecureConfigs([
        'client_secret',
        'access_token',
        'refresh_token',
        'webhook_token',
        'api_token',
        'api_key',
    ]);
}

function plugin_init_ativaramal(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['ativaramal'] = true;

    plugin_ativaramal_register_secure_configs();

    $plugin = new Plugin();
    if (!$plugin->isActivated('ativaramal')) {
        return;
    }

    Plugin::registerClass(PluginAtivaramalMenu::class);
    Plugin::registerClass(PluginAtivaramalProfile::class, ['addtabon' => [Profile::class]]);

    if (PluginAtivaramalProfile::canView()) {
        // Chave nova + valor em array = secao propria na barra lateral.
        $PLUGIN_HOOKS['menu_toadd']['ativaramal'] = [
            'ativaramal' => [PluginAtivaramalMenu::class],
        ];
    }

    // O GLPI 11 ja possui o endpoint seguro de troca de perfil. Exponha ao
    // menu do usuario apenas quando Ativa - Gestor estiver atribuido a sessao.
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['ativaramal'][] = 'js/profile-switch.js';
    if (($gestorProfileId = PluginAtivaramalProfile::availableGestorProfileId()) !== null) {
        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG]['ativaramal'][] = [
            'tag' => 'meta',
            'properties' => [
                'name'    => 'ativaramal-gestor-profile',
                'content' => (string) $gestorProfileId,
            ],
        ];
    }

    if (Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, READ)) {
        $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['ativaramal'] = 'front/config.php';
    }

    // Perfil "so dashboard" (ex.: Ativa - Gestor): a pagina inicial do GLPI
    // leva direto ao dashboard do Ativa Ramal.
    $PLUGIN_HOOKS[Hooks::DISPLAY_CENTRAL]['ativaramal'] = 'plugin_ativaramal_display_central';

    // A Localizacao define a filial que o gestor ve no dashboard: ele nao pode
    // troca-la sozinho depois de definida (o primeiro acesso ainda preenche).
    $PLUGIN_HOOKS[Hooks::PRE_ITEM_UPDATE]['ativaramal'] = [User::class => 'plugin_ativaramal_pre_user_update'];
}

function plugin_version_ativaramal(): array
{
    return [
        'name'         => 'Ativa Ramal',
        'version'      => PLUGIN_ATIVARAMAL_VERSION,
        'author'       => 'Ativa',
        'license'      => 'GPLv2+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ATIVARAMAL_MIN_GLPI,
                'max' => PLUGIN_ATIVARAMAL_MAX_GLPI,
            ],
        ],
    ];
}

function plugin_ativaramal_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_ATIVARAMAL_MIN_GLPI, '<') || version_compare(GLPI_VERSION, PLUGIN_ATIVARAMAL_MAX_GLPI, '>=')) {
        echo 'Este plugin requer o GLPI >= ' . PLUGIN_ATIVARAMAL_MIN_GLPI . ' e < ' . PLUGIN_ATIVARAMAL_MAX_GLPI;
        return false;
    }
    return true;
}

function plugin_ativaramal_check_config(): bool
{
    return true;
}
