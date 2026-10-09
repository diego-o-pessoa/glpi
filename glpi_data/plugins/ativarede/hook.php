<?php

declare(strict_types=1);

use GlpiPlugin\Ativarede\ReportService;
use GlpiPlugin\Ativarede\Seed;
use GlpiPlugin\Ativarede\Settings;
use GlpiPlugin\Ativarede\Watchdog;

/**
 * Instalacao/atualizacao idempotente: cria as tabelas que faltam, a planta
 * inicial (sala principal - Anexo, com as mesas do layout), os direitos e a
 * tarefa automatica. Reinstalar nunca apaga mesas, historico nem alertas.
 */
function plugin_ativarede_install(): bool
{
    require_once PLUGIN_ATIVAREDE_DIR . '/inc/profile.class.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Settings.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Seed.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Watchdog.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Events.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/ReportService.php';

    plugin_ativarede_install_tables();
    Settings::installDefaults();
    Seed::install();
    PluginAtivaredeProfile::installRights();
    // 0.1.2: switches falsos (anuncio LLDP do proprio Windows) gravados antes do filtro.
    ReportService::cleanupOwnAnnouncements();
    // 0.1.3: monitores com serie generica ("SerialNumber") gravados antes do filtro.
    ReportService::cleanupGenericSerials();
    // 0.1.7: tela virtual do Windows (MS_0001) nao e monitor fisico.
    ReportService::cleanupVirtualMonitors();
    // 0.1.8: setores da sala principal (so preenche mesas ainda sem setor).
    Seed::applyAnexoSectors();
    // 0.1.9: F1-F3 passam do Comercial para o Suporte (uma vez).
    Seed::applyAnexoFixups();

    // Monitor ausente, mesa vazia e maquina sem relatorio: conferidos a cada hora.
    CronTask::register(
        Watchdog::class,
        'Watchdog',
        HOUR_TIMESTAMP,
        ['state' => CronTask::STATE_WAITING, 'comment' => 'Ativa Rede - monitores ausentes, mesas vazias e maquinas sem relatorio']
    );
    return true;
}

function plugin_ativarede_install_tables(): void
{
    global $DB;

    $charset = DBConnection::getDefaultCharset();
    $collation = DBConnection::getDefaultCollation();
    $sign = DBConnection::getDefaultPrimaryKeySignOption();
    $tail = "ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation} ROW_FORMAT=DYNAMIC";

    $tables = [
        // Switches vistos pelo LLDP. chassis_id e o identificador do proprio
        // switch (MAC base), estavel mesmo que o IP de gerencia mude.
        'glpi_plugin_ativarede_switches' => "
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `chassis_id` varchar(64) NOT NULL,
            `label` varchar(64) NOT NULL DEFAULT '',
            `system_name` varchar(255) NOT NULL DEFAULT '',
            `description` varchar(255) NOT NULL DEFAULT '',
            `mgmt_ip` varchar(64) NOT NULL DEFAULT '',
            `first_seen` timestamp NULL DEFAULT NULL,
            `last_seen` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `chassis_id` (`chassis_id`)",

        // Plantas (salas). width/height = sistema de coordenadas da planta
        // (viewBox do SVG); mesas usam essas unidades.
        'glpi_plugin_ativarede_plans' => "
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT '',
            `locations_id` int {$sign} NOT NULL DEFAULT '0',
            `background` varchar(255) NOT NULL DEFAULT '',
            `width` int NOT NULL DEFAULT '1600',
            `height` int NOT NULL DEFAULT '400',
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `locations_id` (`locations_id`)",

        // Mesas. (switches_id, port) identifica a tomada; zero/vazio = ainda
        // sem porta definida. chair = lado da cadeira (desenho).
        'glpi_plugin_ativarede_desks' => "
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `plans_id` int {$sign} NOT NULL DEFAULT '0',
            `name` varchar(100) NOT NULL DEFAULT '',
            `x` decimal(8,2) NOT NULL DEFAULT '0.00',
            `y` decimal(8,2) NOT NULL DEFAULT '0.00',
            `w` decimal(8,2) NOT NULL DEFAULT '44.00',
            `h` decimal(8,2) NOT NULL DEFAULT '40.00',
            `chair` varchar(8) NOT NULL DEFAULT 'down',
            `switches_id` int {$sign} NOT NULL DEFAULT '0',
            `port` varchar(64) NOT NULL DEFAULT '',
            `machines_id` int {$sign} NOT NULL DEFAULT '0',
            `sector` varchar(64) NOT NULL DEFAULT '',
            `comment` varchar(255) NOT NULL DEFAULT '',
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `plans_id` (`plans_id`),
            KEY `switch_port` (`switches_id`,`port`),
            KEY `machines_id` (`machines_id`)",

        // Posicao de cada maquina (uma linha por Guardian). switches_id/port =
        // posicao confirmada; pending_* = posicao nova aguardando confirmacao.
        'glpi_plugin_ativarede_machines' => "
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `machine_id` varchar(128) NOT NULL,
            `computers_id` int {$sign} NOT NULL DEFAULT '0',
            `hostname` varchar(255) NOT NULL DEFAULT '',
            `bios_serial` varchar(128) NOT NULL DEFAULT '',
            `ip` varchar(64) NOT NULL DEFAULT '',
            `mac` varchar(32) NOT NULL DEFAULT '',
            `link` varchar(16) NOT NULL DEFAULT 'unknown',
            `switches_id` int {$sign} NOT NULL DEFAULT '0',
            `port` varchar(64) NOT NULL DEFAULT '',
            `port_id` varchar(128) NOT NULL DEFAULT '',
            `since` timestamp NULL DEFAULT NULL,
            `pending_switches_id` int {$sign} NOT NULL DEFAULT '0',
            `pending_port` varchar(64) NOT NULL DEFAULT '',
            `pending_count` tinyint NOT NULL DEFAULT '0',
            `guardian_version` varchar(64) NOT NULL DEFAULT '',
            `first_report` timestamp NULL DEFAULT NULL,
            `last_report` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `machine_id` (`machine_id`),
            KEY `computers_id` (`computers_id`),
            KEY `switch_port` (`switches_id`,`port`)",

        // Monitores pela identidade EDID. machines_id = onde esta (ou estava,
        // se missing_since preenchido).
        'glpi_plugin_ativarede_monitors' => "
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `fingerprint` varchar(64) NOT NULL,
            `manufacturer` varchar(32) NOT NULL DEFAULT '',
            `product_code` varchar(32) NOT NULL DEFAULT '',
            `model` varchar(128) NOT NULL DEFAULT '',
            `serial` varchar(128) NOT NULL DEFAULT '',
            `has_serial` tinyint NOT NULL DEFAULT '1',
            `year` smallint NOT NULL DEFAULT '0',
            `week` tinyint NOT NULL DEFAULT '0',
            `connection` varchar(32) NOT NULL DEFAULT '',
            `machines_id` int {$sign} NOT NULL DEFAULT '0',
            `since` timestamp NULL DEFAULT NULL,
            `first_seen` timestamp NULL DEFAULT NULL,
            `last_seen` timestamp NULL DEFAULT NULL,
            `missing_since` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `fingerprint` (`fingerprint`),
            KEY `machines_id` (`machines_id`),
            KEY `serial` (`serial`)",

        // Historico e alertas. status: open | authorized | ignored | ticket.
        'glpi_plugin_ativarede_events' => "
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `type` varchar(32) NOT NULL,
            `status` varchar(16) NOT NULL DEFAULT 'open',
            `machines_id` int {$sign} NOT NULL DEFAULT '0',
            `monitors_id` int {$sign} NOT NULL DEFAULT '0',
            `desks_id` int {$sign} NOT NULL DEFAULT '0',
            `from_switches_id` int {$sign} NOT NULL DEFAULT '0',
            `from_port` varchar(64) NOT NULL DEFAULT '',
            `to_switches_id` int {$sign} NOT NULL DEFAULT '0',
            `to_port` varchar(64) NOT NULL DEFAULT '',
            `from_machines_id` int {$sign} NOT NULL DEFAULT '0',
            `to_machines_id` int {$sign} NOT NULL DEFAULT '0',
            `details` varchar(500) NOT NULL DEFAULT '',
            `tickets_id` int {$sign} NOT NULL DEFAULT '0',
            `users_id` int {$sign} NOT NULL DEFAULT '0',
            `date_creation` timestamp NULL DEFAULT NULL,
            `resolved_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `status` (`status`),
            KEY `type` (`type`),
            KEY `machines_id` (`machines_id`),
            KEY `monitors_id` (`monitors_id`),
            KEY `date_creation` (`date_creation`)",
    ];

    foreach ($tables as $table => $columns) {
        if (!$DB->tableExists($table)) {
            $DB->doQuery("CREATE TABLE `{$table}` ({$columns}) {$tail}");
        }
    }

    // 0.1.3: mesa com maquina sem porta (notebook no Wi-Fi), vinculo direto.
    if (!$DB->fieldExists('glpi_plugin_ativarede_desks', 'machines_id')) {
        $DB->doQuery(
            "ALTER TABLE `glpi_plugin_ativarede_desks`
                ADD `machines_id` int {$sign} NOT NULL DEFAULT '0' AFTER `port`,
                ADD KEY `machines_id` (`machines_id`)"
        );
    }

    // 0.1.8: setor de cada mesa (areas com nome na planta).
    if (!$DB->fieldExists('glpi_plugin_ativarede_desks', 'sector')) {
        $DB->doQuery("ALTER TABLE `glpi_plugin_ativarede_desks` ADD `sector` varchar(64) NOT NULL DEFAULT '' AFTER `machines_id`");
    }

    // Depois da 0.1.9 (tambem aplicado sob demanda, sem subir a versao).
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Inventory.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Desks.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Schema.php';
    \GlpiPlugin\Ativarede\Schema::upgrade();
}

/**
 * Desinstalacao: remove tabelas (inclusive historico), direitos, tarefa e
 * configuracao. Nao toca em computadores, monitores nem localizacoes do GLPI.
 */
function plugin_ativarede_uninstall(): bool
{
    global $DB;

    require_once PLUGIN_ATIVAREDE_DIR . '/inc/profile.class.php';
    require_once PLUGIN_ATIVAREDE_DIR . '/src/Settings.php';

    CronTask::unregister('ativarede');
    foreach ([
        'glpi_plugin_ativarede_rejections',
        'glpi_plugin_ativarede_events',
        'glpi_plugin_ativarede_monitors',
        'glpi_plugin_ativarede_machines',
        'glpi_plugin_ativarede_desks',
        'glpi_plugin_ativarede_plans',
        'glpi_plugin_ativarede_switches',
    ] as $table) {
        if ($DB->tableExists($table)) {
            $DB->dropTable($table);
        }
    }
    Settings::removeAll();
    PluginAtivaredeProfile::uninstallRights();
    return true;
}
