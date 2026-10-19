CREATE TABLE IF NOT EXISTS `glpi_plugin_ativaprint_printers` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `entities_id` int unsigned NOT NULL DEFAULT '0',
    `is_recursive` tinyint(1) NOT NULL DEFAULT '1',
    `printers_id` int unsigned DEFAULT NULL,
    `name` varchar(255) NOT NULL,
    `model` varchar(255) DEFAULT NULL,
    `ip` varchar(45) NOT NULL,
    `sector` varchar(255) DEFAULT NULL,
    `serial` varchar(255) DEFAULT NULL,
    `status` varchar(32) NOT NULL DEFAULT 'offline',
    `status_message` varchar(255) DEFAULT NULL,
    `toner_level` smallint DEFAULT NULL,
    `drum_level` smallint DEFAULT NULL,
    `waste_level` smallint DEFAULT NULL,
    `page_counter` bigint unsigned DEFAULT '0',
    `supplies` longtext DEFAULT NULL,
    `sys_descr` varchar(255) DEFAULT NULL,
    `snmp_community` varchar(64) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `last_seen` datetime DEFAULT NULL,
    `last_poll` datetime DEFAULT NULL,
    `date_creation` datetime DEFAULT NULL,
    `date_mod` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ip` (`ip`),
    KEY `status` (`status`),
    KEY `sector` (`sector`),
    KEY `is_active` (`is_active`),
    KEY `last_poll` (`last_poll`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `glpi_plugin_ativaprint_counters` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `printers_id` int unsigned NOT NULL,
    `date` date NOT NULL,
    `page_counter` bigint unsigned NOT NULL DEFAULT '0',
    `date_mod` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `printer_date` (`printers_id`, `date`),
    KEY `printers_id` (`printers_id`),
    KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `glpi_plugin_ativaprint_supplies` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `entities_id` int unsigned NOT NULL DEFAULT '0',
    `name` varchar(255) NOT NULL,
    `type` varchar(32) NOT NULL DEFAULT 'toner',
    `compatible_models` varchar(255) DEFAULT NULL,
    `quantity` int NOT NULL DEFAULT '0',
    `minimum_quantity` int NOT NULL DEFAULT '2',
    `location` varchar(255) NOT NULL DEFAULT 'Armário TI',
    `date_mod` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `type` (`type`),
    KEY `quantity` (`quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

