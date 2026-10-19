<?php

declare(strict_types=1);

use GlpiPlugin\Ativaprint\PrintConfig;
use GlpiPlugin\Ativaprint\PrinterRepository;

/**
 * Instalação e atualização do plugin Ativa Print.
 */
function plugin_ativaprint_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ATIVAPRINT_VERSION);

    if (
        !$DB->tableExists(PrinterRepository::TABLE) ||
        !$DB->tableExists(PrinterRepository::COUNTERS_TABLE) ||
        !$DB->tableExists(PrinterRepository::SUPPLIES_TABLE)
    ) {
        $DB->runFile(__DIR__ . '/install/schema.sql');
    }

    $migration->executeMigration();

    require_once __DIR__ . '/src/PrintConfig.php';
    PrintConfig::ensureDefaults();

    require_once __DIR__ . '/inc/profile.class.php';
    PluginAtivaprintProfile::createAdminAccess();

    require_once __DIR__ . '/src/PrinterRepository.php';
    PrinterRepository::seedInitialDataIfEmpty();

    // Tarefa automática de monitoramento das impressoras (a cada 5 min)
    CronTask::register(
        'GlpiPlugin\\Ativaprint\\Poller',
        'Poll',
        5 * MINUTE_TIMESTAMP,
        ['state' => CronTask::STATE_WAITING, 'comment' => 'Ativa Print - monitoramento das impressoras via SNMP/rede']
    );

    return true;
}

/**
 * Desinstalação do plugin Ativa Print.
 */
function plugin_ativaprint_uninstall(): bool
{
    global $DB;

    CronTask::unregister('ativaprint');

    $tables = [
        PrinterRepository::TABLE,
        PrinterRepository::COUNTERS_TABLE,
        PrinterRepository::SUPPLIES_TABLE,
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->dropTable($table);
        }
    }

    require_once __DIR__ . '/src/PrintConfig.php';
    PrintConfig::removeAll();

    return true;
}

