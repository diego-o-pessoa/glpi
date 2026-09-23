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
            `employee_name` varchar(255) NOT NULL DEFAULT '',
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

        // 0.2.0: funcionario que vai usar a maquina (texto: o funcionario novo
        // pode ainda nao existir como usuario do GLPI).
        $jobsTable = 'glpi_plugin_ativaworkspace_jobs';
        if (!$DB->fieldExists($jobsTable, 'employee_name')) {
            $migration->addField($jobsTable, 'employee_name', "varchar(255) NOT NULL DEFAULT ''", ['after' => 'status']);
        }

        // 0.3.0 (Etapa 2): catalogo de aplicativos com instalador e etapas
        // completas nos perfis. So adiciona colunas: nenhum dado e perdido.
        $newFields = [
            'glpi_plugin_ativaworkspace_applications' => [
                'icon'                  => "varchar(64) NOT NULL DEFAULT ''",
                'category'              => "varchar(64) NOT NULL DEFAULT ''",
                'installer_type'        => "varchar(16) NOT NULL DEFAULT 'OTHER'",
                'architecture'          => "varchar(16) NOT NULL DEFAULT 'any'",
                'timeout_minutes'       => "int NOT NULL DEFAULT '30'",
                'max_attempts'          => "int NOT NULL DEFAULT '3'",
                'requires_reboot'       => "tinyint NOT NULL DEFAULT '0'",
                'expected_signer'       => "varchar(255) NOT NULL DEFAULT ''",
                'notes'                 => 'text',
                // Configuracao interna de instalacao por tipo (preparada; o
                // tecnico nao digita linha de comando).
                'install_config'        => 'longtext',
                // Instalador armazenado (fora da area publica, em GLPI_PLUGIN_DOC_DIR).
                'file_name'             => "varchar(255) NOT NULL DEFAULT ''",
                'file_stored_name'      => "varchar(128) NOT NULL DEFAULT ''",
                'file_size'             => "bigint NOT NULL DEFAULT '0'",
                'file_sha256'           => "char(64) NOT NULL DEFAULT ''",
                'file_signature_status' => "varchar(32) NOT NULL DEFAULT 'not_checked'",
                'file_uploaded_at'      => 'timestamp NULL DEFAULT NULL',
                'file_users_id'         => "int {$sign} NOT NULL DEFAULT '0'",
            ],
            'glpi_plugin_ativaworkspace_profilesteps' => [
                'is_mandatory'      => "tinyint NOT NULL DEFAULT '1'",
                'continue_on_error' => "tinyint NOT NULL DEFAULT '0'",
                // 0 = usa o padrao do aplicativo (SOFTWARE) ou do tipo de etapa.
                'timeout_minutes'   => "int NOT NULL DEFAULT '0'",
                'max_attempts'      => "int NOT NULL DEFAULT '0'",
            ],
        ];
        foreach ($newFields as $table => $fields) {
            foreach ($fields as $field => $definition) {
                if (!$DB->fieldExists($table, $field)) {
                    $migration->addField($table, $field, $definition);
                }
            }
        }
        $appsTable = 'glpi_plugin_ativaworkspace_applications';
        if (!$DB->fieldExists($appsTable, 'file_sha256')) {
            $migration->addKey($appsTable, 'file_sha256');
            $migration->addKey($appsTable, 'category');
        }

        // 0.4.0 (Etapa 3): Job Engine. Job e etapas guardam um snapshot
        // completo do perfil; eventos ligam tambem a etapa do job.
        $engineFields = [
            'glpi_plugin_ativaworkspace_jobs' => [
                'upn'                              => "varchar(255) NOT NULL DEFAULT ''",
                'profile_name'                     => "varchar(255) NOT NULL DEFAULT ''",
                'plugin_ativaworkspace_jobsteps_id'=> "int {$sign} NOT NULL DEFAULT '0'",
                'progress'                         => "int NOT NULL DEFAULT '0'",
            ],
            'glpi_plugin_ativaworkspace_jobsteps' => [
                'plugin_ativaworkspace_applications_id' => "int {$sign} NOT NULL DEFAULT '0'",
                'config'            => 'longtext',
                // Copia do aplicativo/perfil no momento da criacao do job.
                'snapshot'          => 'longtext',
                'is_mandatory'      => "tinyint NOT NULL DEFAULT '1'",
                'continue_on_error' => "tinyint NOT NULL DEFAULT '0'",
                'timeout_minutes'   => "int NOT NULL DEFAULT '0'",
                'max_attempts'      => "int NOT NULL DEFAULT '1'",
                'attempts'          => "int NOT NULL DEFAULT '0'",
            ],
            'glpi_plugin_ativaworkspace_events' => [
                'plugin_ativaworkspace_jobsteps_id' => "int {$sign} NOT NULL DEFAULT '0'",
            ],
        ];
        foreach ($engineFields as $table => $fields) {
            foreach ($fields as $field => $definition) {
                if (!$DB->fieldExists($table, $field)) {
                    $migration->addField($table, $field, $definition);
                    if (str_ends_with($field, '_id')) {
                        $migration->addKey($table, $field);
                    }
                }
            }
        }

        $migration->executeMigration();

        // 0.4.0: status passam a ser os do Job Engine (maiusculos). Linhas de
        // versoes anteriores sao convertidas; nenhuma e apagada.
        $statusMap = [
            'glpi_plugin_ativaworkspace_jobs' => [
                'pending' => 'QUEUED', 'running' => 'RUNNING', 'waiting_intervention' => 'WAITING_INTERVENTION',
                'failed' => 'FAILED', 'completed' => 'COMPLETED', 'cancelled' => 'CANCELED',
            ],
            'glpi_plugin_ativaworkspace_jobsteps' => [
                'pending' => 'PENDING', 'running' => 'RUNNING', 'waiting_intervention' => 'WAITING_HUMAN',
                'failed' => 'FAILED', 'completed' => 'SUCCESS', 'skipped' => 'SKIPPED', 'cancelled' => 'CANCELED',
            ],
        ];
        foreach ($statusMap as $table => $map) {
            foreach ($map as $old => $new) {
                $DB->update($table, ['status' => $new], ['status' => $old]);
            }
        }
        // Jobs de versoes anteriores: grava o nome do perfil no proprio job.
        foreach ($DB->request([
            'SELECT'    => ['j.id', 'p.name'],
            'FROM'      => 'glpi_plugin_ativaworkspace_jobs AS j',
            'INNER JOIN' => [
                'glpi_plugin_ativaworkspace_provisioningprofiles AS p' => [
                    'ON' => ['p' => 'id', 'j' => 'plugin_ativaworkspace_provisioningprofiles_id'],
                ],
            ],
            'WHERE'     => ['j.profile_name' => ''],
        ]) as $row) {
            $DB->update('glpi_plugin_ativaworkspace_jobs', ['profile_name' => (string) $row['name']], ['id' => (int) $row['id']]);
        }

        if ($DB->fieldExists('glpi_plugin_ativaworkspace_jobs', 'status')) {
            $DB->doQuery("ALTER TABLE `glpi_plugin_ativaworkspace_jobs` ALTER `status` SET DEFAULT 'QUEUED'");
            $DB->doQuery("ALTER TABLE `glpi_plugin_ativaworkspace_jobsteps` ALTER `status` SET DEFAULT 'PENDING'");
        }

        Config::setConfigurationValues(PLUGIN_ATIVAWORKSPACE_CONFIG_CONTEXT, [
            'schema_version' => PLUGIN_ATIVAWORKSPACE_VERSION,
        ]);
        // Modo de simulacao do Job Engine: sempre nasce desligado.
        $current = Config::getConfigurationValues(PLUGIN_ATIVAWORKSPACE_CONFIG_CONTEXT, ['simulation_enabled']);
        if (!array_key_exists('simulation_enabled', $current)) {
            Config::setConfigurationValues(PLUGIN_ATIVAWORKSPACE_CONFIG_CONTEXT, ['simulation_enabled' => 0]);
        }

        // Reconciliacao periodica dos jobs ativos (timeouts, etapa atual).
        CronTask::register(
            'GlpiPlugin\\Ativaworkspace\\ProvisioningEngine',
            'ProcessJobs',
            MINUTE_TIMESTAMP,
            ['state' => CronTask::STATE_WAITING, 'comment' => 'Ativa Workspace - Job Engine']
        );

        require_once PLUGIN_ATIVAWORKSPACE_DIR . '/inc/profile.class.php';
        PluginAtivaworkspaceProfile::installRights();

        // Diretorio dos instaladores (fora do public/ do plugin).
        require_once PLUGIN_ATIVAWORKSPACE_DIR . '/src/InstallerStorage.php';
        GlpiPlugin\Ativaworkspace\InstallerStorage::ensureDirectory();
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

    Config::deleteConfigurationValues(PLUGIN_ATIVAWORKSPACE_CONFIG_CONTEXT, ['schema_version', 'simulation_enabled']);
    CronTask::unregister('ativaworkspace');

    // Os instaladores so fazem sentido com as tabelas; saem juntos.
    require_once PLUGIN_ATIVAWORKSPACE_DIR . '/src/InstallerStorage.php';
    GlpiPlugin\Ativaworkspace\InstallerStorage::removeAll();

    return true;
}
