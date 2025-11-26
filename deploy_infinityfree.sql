-- =====================================================
-- Certificate Generator — Deployment SQL for InfinityFree (phpMyAdmin)
-- Instructions:
-- 1) Create a new database via your InfinityFree control panel (they provide a name like epiz_12345678_dbname).
-- 2) Open phpMyAdmin for that database and import this SQL file.
-- 3) Update `includes/config.php` in the project to use the provided DB_HOST, DB_USER, DB_PASS, DB_NAME from InfinityFree.
-- 4) After import, visit the site and test login using the default admin credentials below (change immediately).
-- Note: This SQL intentionally DOES NOT include a CREATE DATABASE statement — phpMyAdmin will import into the database you select.
-- =====================================================

-- Use database: (phpMyAdmin will import into the currently selected DB)

-- =====================================================
-- Users Table
-- =====================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stream` enum('aided','sfs') DEFAULT NULL,
  `level` enum('ug','pg') DEFAULT NULL,
  `reg_no` varchar(50) DEFAULT NULL,
  `program_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `phone_no` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_reg_no` (`reg_no`),
  KEY `idx_stream_level` (`stream`,`level`),
  KEY `idx_program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: enforce college email domain (some hosts may not allow CHECKs; if your MySQL version rejects it, remove the next statement)
-- ALTER TABLE `users` ADD CONSTRAINT chk_email_domain CHECK (email LIKE '%@mcc.edu.in');

-- =====================================================
-- Admins Table
-- =====================================================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Certificate Logs
-- =====================================================
DROP TABLE IF EXISTS `certificate_logs`;
CREATE TABLE `certificate_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `certificate_no` varchar(100) DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `certified_for` text DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `generation_type` enum('single','bulk') DEFAULT 'single',
  `bulk_count` int DEFAULT 1,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Activity Logs
-- =====================================================
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `user_type` varchar(20) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_activity_type` (`activity_type`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Admin Logs
-- =====================================================
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- User Sessions
-- =====================================================
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_type` varchar(20) DEFAULT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `logout_time` timestamp NULL DEFAULT NULL,
  `status` enum('active','logged_out','expired') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_token` (`session_token`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Default admin account (change password after import)
-- Username: Admin@MCC
-- Password: Admin123 (bcrypt hash below)
-- IMPORTANT: Change this password immediately after deployment.
-- =====================================================
INSERT INTO `admins` (`username`, `password`, `email`, `status`) VALUES
('Admin@MCC', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@yourdomain.com', 'active')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- =====================================================
-- Optional sample data (commented). Remove or leave commented in production.
-- =====================================================
/*
INSERT INTO `users` (stream, level, reg_no, program_id, name, department, phone_no, email, password) VALUES
('aided', 'ug', '2024001', 'AIDED-UG-2024-A1B2', 'John Doe', 'Computer Science', '9876543210', 'john.doe@mcc.edu.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
*/

-- =====================================================
-- Helpful diagnostic queries (optional)
-- SELECT COUNT(*) FROM users;
-- SELECT id, username, email, status FROM admins;
-- SHOW TABLES;
-- =====================================================

-- End of file
