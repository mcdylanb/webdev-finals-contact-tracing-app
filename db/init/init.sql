-- SQL Database Schema Initialization for Sentinel Access Contact Tracing Kiosk
-- Target DB: pickleball (or as configured in docker-compose)

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `last_name` VARCHAR(100) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `barangay` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `phone_number` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `usc_id_number` VARCHAR(50) DEFAULT NULL,
  INDEX `idx_usc_id` (`usc_id_number`),
  INDEX `idx_names` (`last_name`, `first_name`),
  INDEX `idx_address` (`province`, `city`, `barangay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logs` (
  `entry_id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_number` INT NOT NULL,
  `datetime_login` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datetime_logout` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_logs_contacts` FOREIGN KEY (`id_number`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_datetime_login` (`datetime_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
