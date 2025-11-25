-- =====================================================
-- Certificate Generator Database Schema
-- Updated: November 2025
-- Clean database with all current features
-- =====================================================

-- Create database
CREATE DATABASE
IF NOT EXISTS certificate_generator;
USE certificate_generator;

-- =====================================================
-- Users Table
-- Stores student accounts (registration is student-only)
-- =====================================================
CREATE TABLE
IF NOT EXISTS users
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream ENUM
('aided', 'sfs') DEFAULT NULL COMMENT 'Student stream: Aided or Self-Financed Stream',
    level ENUM
('ug', 'pg') DEFAULT NULL COMMENT 'Student level: UG (Undergraduate) or PG (Postgraduate)',
    reg_no VARCHAR
(50) DEFAULT NULL COMMENT 'Student registration number - numbers only',
    program_id VARCHAR
(50) DEFAULT NULL COMMENT 'Auto-generated program ID format: STREAM-LEVEL-YEAR-RANDOM',
    name VARCHAR
(100) NOT NULL COMMENT 'Full name - letters and spaces only',
    department VARCHAR
(100) NOT NULL,
    phone_no VARCHAR
(20) NOT NULL,
    email VARCHAR
(100) NOT NULL UNIQUE COMMENT 'College email - must end with @mcc.edu.in',
    password VARCHAR
(255) NOT NULL COMMENT 'Bcrypt hashed password',
    status ENUM
('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_reg_no (reg_no),
    INDEX idx_email (email),
    INDEX idx_stream_level (stream, level),
    INDEX idx_program_id (program_id),
    
    -- Constraints
    CONSTRAINT chk_email_domain
CHECK
(email LIKE '%@mcc.edu.in')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Admins Table
-- Stores admin accounts for dashboard access
-- =====================================================
CREATE TABLE
IF NOT EXISTS admins
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR
(50) NOT NULL UNIQUE,
    password VARCHAR
(255) NOT NULL COMMENT 'Bcrypt hashed password',
    email VARCHAR
(100) DEFAULT NULL,
    status ENUM
('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_username
(username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Certificate Logs Table
-- Tracks all certificate generations
-- =====================================================
CREATE TABLE
IF NOT EXISTS certificate_logs
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    certificate_no VARCHAR
(100) DEFAULT NULL,
    recipient_name VARCHAR
(100) NOT NULL,
    certified_for TEXT DEFAULT NULL,
    from_date DATE DEFAULT NULL,
    to_date DATE DEFAULT NULL,
    generation_type ENUM
('single', 'bulk') DEFAULT 'single',
    bulk_count INT DEFAULT 1 COMMENT 'Number of certificates in bulk generation',
    ip_address VARCHAR
(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY
(user_id) REFERENCES users
(id) ON
DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_certificate_no
(certificate_no),
    INDEX idx_created_at
(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Activity Logs Table
-- Tracks user activities (login, logout, account creation, etc.)
-- =====================================================
CREATE TABLE
IF NOT EXISTS activity_logs
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_type VARCHAR
(20) DEFAULT NULL COMMENT 'Derived from reg_no presence: student or staff',
    activity_type VARCHAR
(50) NOT NULL COMMENT 'login, logout, account_created, certificate_generated, etc.',
    activity_description TEXT DEFAULT NULL,
    ip_address VARCHAR
(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY
(user_id) REFERENCES users
(id) ON
DELETE
SET NULL
,
    INDEX idx_user_id
(user_id),
    INDEX idx_activity_type
(activity_type),
    INDEX idx_created_at
(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Admin Logs Table
-- Tracks admin activities
-- =====================================================
CREATE TABLE
IF NOT EXISTS admin_logs
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    activity_type VARCHAR
(50) NOT NULL,
    activity_description TEXT DEFAULT NULL,
    ip_address VARCHAR
(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY
(admin_id) REFERENCES admins
(id) ON
DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_activity_type
(activity_type),
    INDEX idx_created_at
(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- User Sessions Table
-- Tracks active user sessions
-- =====================================================
CREATE TABLE
IF NOT EXISTS user_sessions
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type VARCHAR
(20) DEFAULT NULL COMMENT 'Derived from reg_no presence: student or staff',
    session_token VARCHAR
(255) NOT NULL,
    ip_address VARCHAR
(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP,
    logout_time TIMESTAMP
NULL DEFAULT NULL,
    status ENUM
('active', 'logged_out', 'expired') DEFAULT 'active',
    
    FOREIGN KEY
(user_id) REFERENCES users
(id) ON
DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token
(session_token),
    INDEX idx_status
(status),
    INDEX idx_login_time
(login_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insert Default Admin Account
-- Username: Admin@MCC
-- Password: Admin123
-- =====================================================
INSERT INTO admins
    (username, password, email, status)
VALUES
    ('Admin@MCC', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@mcc.edu.in', 'active')
ON DUPLICATE KEY
UPDATE username=username;

-- =====================================================
-- Sample Data (Optional - for testing)
-- =====================================================

-- Sample student data
-- Password for all: password123
/*
INSERT INTO users (stream, level, reg_no, program_id, name, department, phone_no, email, password) VALUES
('aided', 'ug', '2024001', 'AIDED-UG-2024-A1B2', 'John Doe', 'Computer Science', '9876543210', 'john.doe@mcc.edu.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('sfs', 'pg', '2024002', 'SFS-PG-2024-C3D4', 'Jane Smith', 'Business Administration', '9876543211', 'jane.smith@mcc.edu.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('aided', 'ug', '2024003', 'AIDED-UG-2024-E5F6', 'Raj Kumar', 'Mathematics', '9876543212', 'raj.kumar@mcc.edu.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
*/

-- =====================================================
-- Verification Queries
-- =====================================================

-- Show all tables
SHOW TABLES;

-- Verify users table structure
DESCRIBE users;

-- Verify admins table structure
DESCRIBE admins;

-- Check default admin account
SELECT id, username, email, status, created_at
FROM admins;

-- =====================================================
-- End of Schema
-- =====================================================
