-- ===================================
-- Rate Limiting System için Tablo
-- ===================================
-- Bu dosyayı phpMyAdmin'den içe aktarın (Import)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ===================================
-- Contact Rate Limits Table
-- ===================================
CREATE TABLE IF NOT EXISTS `contact_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `submission_count` int(11) DEFAULT 1,
  `first_submission` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_submission` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  KEY `blocked_until` (`blocked_until`),
  KEY `last_submission` (`last_submission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Schema Migrations Table
-- ===================================
CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration_name` VARCHAR(255) NOT NULL,
  `migration_hash` VARCHAR(64) NOT NULL,
  `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_name` (`migration_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Analytics Head Setting
-- ===================================
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `category`)
VALUES ('head_analytics_code', '', 'textarea', 'general');

COMMIT;
