<?php

declare(strict_types=1);

const PLUGIN_ATIVAWORKSPACE_CONFIG_CONTEXT = 'plugin:ativaworkspace';

/**
 * Tabelas do plugin, na ordem de criacao (pais antes dos filhos).
 * O desinstalador remove na ordem inversa.
 *
 * @return array<string, string> tabela => colunas/indices (sem PRIMARY KEY)
 */
function plugin_ativaworkspace_tables(): array
{
    $sign = DBConnection::getDefaultPrimaryKeySignOption();

    return [
        // Perfis de provisionamento (ex.: "Notebook Comercial").
        'glpi_plugin_ativaworkspace_provisioningprofiles' => "
            `name` varchar(255) NOT NULL DEFAULT '',
            `comment` text,
            `entities_id` int {$sign} NOT NULL DEFAULT '0',
            `is_recursive` tinyint NOT NULL DEFAULT '0',
            `is_active` tinyint NOT NULL DEFAULT '1',
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            KEY `name` (`name`),
            KEY `entities_id` (`entities_id`),
            KEY `is_recursive` (`is_recursive`),
            KEY `is_active` (`is_active`),
            KEY `date_creation` (`date_creation`),
            KEY `date_mod` (`date_mod`)",

        // Aplicativos do catalogo. provider diz quem instala (execucao na Etapa 2+).
        'glpi_plugin_ativaworkspace_applications' => "
            `name` varchar(255) NOT NULL DEFAULT '',
            `comment` text,
            `provider` varchar(32) NOT NULL DEFAULT 'manual',
            `desired_version` varchar(64) NOT NULL DEFAULT '',
            `is_active` tinyint NOT NULL DEFAULT '1',
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            KEY `name` (`name`),
            KEY `provider` (`provider`),
            KEY `is_active` (`is_active`),
            KEY `date_creation` (`date_creation`),
            KEY `date_mod` (`date_mod`)",

        // Etapas de um perfil, em ordem (step_order). config guarda parametros em JSON.
        'glpi_plugin_ativaworkspace_profilesteps' => "
            `plugin_ativaworkspace_provisioningprofiles_id` int {$sign} NOT NULL DEFAULT '0',
            `name` varchar(255) NOT NULL DEFAULT '',
            `step_type` varchar(64) NOT NULL DEFAULT '',
            `plugin_ativaworkspace_applications_id` int {$sign} NOT NULL DEFAULT '0',
            `step_order` int NOT NULL DEFAULT '0',
            `config` longtext,
            `is_active` tinyint NOT NULL DEFAULT '1',
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            KEY `plugin_ativaworkspace_provisioningprofiles_id` (`plugin_ativaworkspace_provisioningprofiles_id`),
            KEY `plugin_ativaworkspace_applications_id` (`plugin_ativaworkspace_applications_id`),
            KEY `step_order` (`step_order`),
            KEY `is_active` (`is_active`)",

        // Um provisionamento de um computador com um perfil.
        'glpi_plugin_ativaworkspace_jobs' => "
            `entities_id` int {$sign} NOT NULL DEFAULT '0',
            `computers_id` int {$sign} NOT NULL DEFAULT '0',
            `plugin_ativaworkspace_provisioningprofiles_id` int {$sign} NOT NULL DEFAULT '0',
            `status` varchar(32) NOT NULL DEFAULT 'pending',
            `users_id` int {$sign} NOT NULL DEFAULT '0',
            `message` varchar(255) NOT NULL DEFAULT '',
            `date_start` timestamp NULL DEFAULT NULL,
            `date_end` timestamp NULL DEFAULT NULL,
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            KEY `entities_id` (`entities_id`),
            KEY `computers_id` (`computers_id`),
            KEY `plugin_ativaworkspace_provisioningprofiles_id` (`plugin_ativaworkspace_provisioningprofiles_id`),
            KEY `status` (`status`),
            KEY `users_id` (`users_id`),
            KEY `date_end` (`date_end`),
            KEY `date_creation` (`date_creation`),
            KEY `date_mod` (`date_mod`)",

        // Copia das etapas do perfil no momento do job, com o estado de cada uma.
        'glpi_plugin_ativaworkspace_jobsteps' => "
            `plugin_ativaworkspace_jobs_id` int {$sign} NOT NULL DEFAULT '0',
            `plugin_ativaworkspace_profilesteps_id` int {$sign} NOT NULL DEFAULT '0',
            `name` varchar(255) NOT NULL DEFAULT '',
            `step_type` varchar(64) NOT NULL DEFAULT '',
            `step_order` int NOT NULL DEFAULT '0',
            `status` varchar(32) NOT NULL DEFAULT 'pending',
            `message` text,
            `date_start` timestamp NULL DEFAULT NULL,
            `date_end` timestamp NULL DEFAULT NULL,
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            KEY `plugin_ativaworkspace_jobs_id` (`plugin_ativaworkspace_jobs_id`),
            KEY `plugin_ativaworkspace_profilesteps_id` (`plugin_ativaworkspace_profilesteps_id`),
            KEY `step_order` (`step_order`),
            KEY `status` (`status`)",

        // Eventos/logs (INFO, WARNING, ERROR, SECURITY).
        'glpi_plugin_ativaworkspace_events' => "
            `date` timestamp NULL DEFAULT NULL,
            `level` varchar(16) NOT NULL DEFAULT 'INFO',
            `source` varchar(64) NOT NULL DEFAULT '',
            `message` varchar(255) NOT NULL DEFAULT '',
            `context` longtext,
            `entities_id` int {$sign} NOT NULL DEFAULT '0',
            `users_id` int {$sign} NOT NULL DEFAULT '0',
            `plugin_ativaworkspace_jobs_id` int {$sign} NOT NULL DEFAULT '0',
            KEY `date` (`date`),
            KEY `level` (`level`),
            KEY `source` (`source`),
            KEY `entities_id` (`entities_id`),
            KEY `users_id` (`users_id`),
            KEY `plugin_ativaworkspace_jobs_id` (`plugin_ativaworkspace_jobs_id`)",
    ];
}

function plugin_ativaworkspace_do_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAWORKSPACE_VERSION);
    $migration->displayMessage('Instalando Ativa Workspace ' . PLUGIN_ATIVAWORKSPACE_VERSION);

    $charset   = DBConnection::getDefaultCharset();
    $collation = DBConnection::getDefaultCollation();
    $sign      = DBConnection::getDefaultPrimaryKeySignOption();

    try {
        // Idempotente: reinstalar/atualizar nao recria nem apaga dados. Colunas
        // novas de versoes futuras entram aqui com $migration->addField().
        foreach (plugin_ativaworkspace_tables() as $table => $columns) {
            if ($DB->tableExists($table)) {
                continue;
            }
            $DB->doQuery(
                "CREATE TABLE `{$table}` (
                    `id` int {$sign} NOT NULL AUTO_INCREMENT,
                    {$columns},
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation} ROW_FORMAT=DYNAMIC"
            );
        }

        $migration->executeMigration();

        Config::setConfigurationValues(PLUGIN_ATIVAWORKSPACE_CONFIG_CONTEXT, [
            'schema_version' => PLUGIN_ATIVAWORKSPACE_VERSION,
        ]);

        require_once PLUGIN_ATIVAWORKSPACE_DIR . '/inc/profile.class.php';
        PluginAtivaworkspaceProfile::installRights();
    } catch (Throwable $exception) {
        $migration->displayMessage('Falha na instalacao: ' . $exception->getMessage());
        return false;
    }

    return true;
}

function plugin_ativaworkspace_do_uninstall(): bool
{
    global $DB;

    require_once PLUGIN_ATIVAWORKSPACE_DIR . '/inc/profile.class.php';
    PluginAtivaworkspaceProfile::uninstallRights();

    foreach (array_reverse(array_keys(plugin_ativaworkspace_tables())) as $table) {
        if ($DB->tableExists($table)) {
            $DB->doQuery('DROP TABLE `' . $table . '`');
        }
    }

    Config::deleteConfigurationValues(PLUGIN_ATIVAWORKSPACE_CONFIG_CONTEXT, ['schema_version']);

    return true;
}
