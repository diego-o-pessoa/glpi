CREATE TABLE IF NOT EXISTS `glpi_plugin_ativaguardian_machines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `machine_id` varchar(128) NOT NULL,
  `computers_id` int unsigned DEFAULT NULL,
  `hostname` varchar(255) NOT NULL DEFAULT '',
  `guardian_version` varchar(32) NOT NULL DEFAULT '',
  `antivirus` varchar(128) NOT NULL DEFAULT '',
  `overall_status` varchar(32) NOT NULL DEFAULT 'unknown',
  `last_ip` varchar(64) NOT NULL DEFAULT '',
  `first_contact` datetime NOT NULL,
  `last_contact` datetime NOT NULL,
  `date_creation` datetime NULL,
  `date_mod` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `machine_id` (`machine_id`),
  KEY `overall_status` (`overall_status`),
  KEY `last_contact` (`last_contact`),
  KEY `computers_id` (`computers_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_ativaguardian_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `machines_id` int(11) NOT NULL,
  `component` varchar(64) NOT NULL,
  `action` varchar(32) NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `started_at` datetime NULL,
  `finished_at` datetime NULL,
  `result` varchar(32) NULL,
  `error_message` varchar(500) NULL,
  PRIMARY KEY (`id`),
  KEY `machine_status` (`machines_id`,`status`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_ativaguardian_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `machines_id` int(11) NOT NULL,
  `component` varchar(64) NOT NULL,
  `version` varchar(64) NOT NULL DEFAULT '',
  `status` varchar(32) NOT NULL DEFAULT 'unknown',
  `date_mod` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `machine_component` (`machines_id`,`component`),
  KEY `component` (`component`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
