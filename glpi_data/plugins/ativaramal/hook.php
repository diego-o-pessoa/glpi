<?php

declare(strict_types=1);

use GlpiPlugin\Ativaramal\RamalConfig;

/**
 * Instalacao/atualizacao. A configuracao fica em glpi_configs (contexto
 * plugin:ativaramal), com os segredos criptografados pelo proprio GLPI; o
 * cadastro de filial/setor, em tabelas proprias. Idempotente.
 */
function plugin_ativaramal_install(): bool
{
    require_once PLUGIN_ATIVARAMAL_DIR . '/src/RamalConfig.php';
    require_once PLUGIN_ATIVARAMAL_DIR . '/inc/profile.class.php';

    // O init do plugin ainda nao rodou: registra os segredos antes de gravar.
    plugin_ativaramal_register_secure_configs();
    RamalConfig::ensureDefaults();

    // Renovacao automatica do token OAuth (antes de expirar).
    CronTask::register(
        'GlpiPlugin\Ativaramal\TokenManager',
        'RefreshToken',
        5 * MINUTE_TIMESTAMP,
        ['state' => CronTask::STATE_WAITING, 'comment' => 'Ativa Ramal - renova o token OAuth da TW Solutions']
    );

    PluginAtivaramalProfile::installRights();
    plugin_ativaramal_install_tables();
    return true;
}

/**
 * 0.2.0 (dashboard): filial/setor dos ramais. Por grupo de captura da TW e
 * ajuste por ramal (o GLPI e consultado direto, sem copia). Idempotente.
 */
function plugin_ativaramal_install_tables(): void
{
    global $DB;

    $charset = DBConnection::getDefaultCharset();
    $collation = DBConnection::getDefaultCollation();
    $sign = DBConnection::getDefaultPrimaryKeySignOption();
    $tables = [
        'glpi_plugin_ativaramal_groups'     => 'callgroup',
        'glpi_plugin_ativaramal_extensions' => 'ramal',
    ];
    foreach ($tables as $table => $key) {
        if ($DB->tableExists($table)) {
            continue;
        }
        $DB->doQuery(
            "CREATE TABLE `{$table}` (
                `id` int {$sign} NOT NULL AUTO_INCREMENT,
                `{$key}` varchar(32) NOT NULL,
                `filial` varchar(255) NOT NULL DEFAULT '',
                `setor` varchar(255) NOT NULL DEFAULT '',
                `date_mod` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `{$key}` (`{$key}`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation} ROW_FORMAT=DYNAMIC"
        );
    }
}

/**
 * Desinstalacao: remove configuracao (inclusive tokens), direitos e a tarefa.
 * O arquivo de log (files/_log/ativaramal.log) fica para auditoria.
 */
function plugin_ativaramal_uninstall(): bool
{
    require_once PLUGIN_ATIVARAMAL_DIR . '/src/RamalConfig.php';
    require_once PLUGIN_ATIVARAMAL_DIR . '/inc/profile.class.php';

    global $DB;

    CronTask::unregister('ativaramal');
    foreach (['glpi_plugin_ativaramal_groups', 'glpi_plugin_ativaramal_extensions'] as $table) {
        if ($DB->tableExists($table)) {
            $DB->dropTable($table);
        }
    }
    RamalConfig::removeAll();
    PluginAtivaramalProfile::uninstallRights();
    return true;
}

/**
 * Pagina inicial do GLPI: quem so tem acesso ao Ativa Ramal (sem chamados,
 * ativos nem configuracao) vai direto ao dashboard. Os demais nao sao afetados.
 */
function plugin_ativaramal_display_central(): void
{
    global $CFG_GLPI;

    if (!class_exists(PluginAtivaramalProfile::class) || !PluginAtivaramalProfile::isDashboardOnly()) {
        return;
    }
    $url = $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/dashboard.php';
    echo '<script>window.location.replace(' . json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . ');</script>';
}

/**
 * O perfil Ativa - Gestor usa somente o Ativa Ramal. Remova as categorias
 * nativas de chamados e ferramentas da barra lateral, sem alterar os demais
 * perfis nem as permissões de administradores.
 */
function plugin_ativaramal_redefine_menus(array $menu): array
{
    $activeName = trim((string) ($_SESSION['glpiactiveprofile']['name'] ?? ''));
    if (strcasecmp($activeName, 'Ativa - Gestor') !== 0) {
        return $menu;
    }

    unset($menu['helpdesk'], $menu['tools']);
    return $menu;
}

/**
 * Usuario com dashboard restrito (sem "ver todos os setores") editando o
 * proprio cadastro: a Localizacao ja definida nao muda (ela e a filial que
 * ele enxerga). Primeiro preenchimento (vazia) continua liberado. Quem pode
 * editar usuarios no GLPI (T.I.) nao e afetado.
 */
function plugin_ativaramal_pre_user_update(User $user): void
{
    $self = (int) Session::getLoginUserID();
    if ($self <= 0 || (int) $user->getID() !== $self || Session::haveRight('user', UPDATE)) {
        return;
    }
    if (!class_exists(PluginAtivaramalProfile::class)
        || !PluginAtivaramalProfile::canView()
        || PluginAtivaramalProfile::canViewAll()) {
        return;
    }
    $current = (int) ($user->fields['locations_id'] ?? 0);
    if ($current > 0 && isset($user->input['locations_id']) && (int) $user->input['locations_id'] !== $current) {
        $user->input['locations_id'] = $current;
    }
}
