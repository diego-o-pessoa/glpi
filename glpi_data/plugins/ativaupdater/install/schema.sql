CREATE TABLE IF NOT EXISTS `glpi_plugin_ativaupdater_releases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(32) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `sha256` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
