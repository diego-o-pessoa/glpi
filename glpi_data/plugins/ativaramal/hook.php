<?php

declare(strict_types=1);

use GlpiPlugin\Ativaramal\RamalConfig;

/**
 * Instalacao/atualizacao. Nesta etapa nao ha tabelas: a configuracao fica em
 * glpi_configs (contexto plugin:ativaramal), com os segredos criptografados
 * pelo proprio GLPI. Idempotente: reinstalar nao apaga credenciais.
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
    return true;
}

/**
 * Desinstalacao: remove configuracao (inclusive tokens), direitos e a tarefa.
 * O arquivo de log (files/_log/ativaramal.log) fica para auditoria.
 */
function plugin_ativaramal_uninstall(): bool
{
    require_once PLUGIN_ATIVARAMAL_DIR . '/src/RamalConfig.php';
    require_once PLUGIN_ATIVARAMAL_DIR . '/inc/profile.class.php';

    CronTask::unregister('ativaramal');
    RamalConfig::removeAll();
    PluginAtivaramalProfile::uninstallRights();
    return true;
}
