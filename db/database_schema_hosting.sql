-- ============================================================
-- Certificate Generator Database Schema
-- For Shared Hosting (No CREATE DATABASE privileges)
-- ============================================================
-- INSTRUCTIONS:
-- 1. Create database through your hosting control panel (cPanel/phpMyAdmin)
-- 2. Select the database in phpMyAdmin
-- 3. Import this file OR copy-paste the SQL below
-- ============================================================

-- Users Table (Students and Staff)
CREATE TABLE
IF NOT EXISTS users
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM
('student', 'staff') NOT NULL,
    stream ENUM
('aided', 'sfs') NULL COMMENT 'Aided or Self-Financed Stream',
    level ENUM
('ug', 'pg') NULL COMMENT 'Undergraduate or Postgraduate',
    reg_no VARCHAR
(50) NULL COMMENT 'For students only',
    program_id VARCHAR
(50) NULL COMMENT 'Auto-generated program identifier',
    name VARCHAR
(255) NOT NULL,
    designation VARCHAR
(255) NULL COMMENT 'For staff only',
    department VARCHAR
(255) NOT NULL,
    phone_no VARCHAR
(20) NOT NULL,
    email VARCHAR
(255) NOT NULL UNIQUE,
    password VARCHAR
(255) NOT NULL,
    college VARCHAR
(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM
('active', 'inactive') DEFAULT 'active',
    INDEX idx_user_type
(user_type),
    INDEX idx_stream
(stream),
    INDEX idx_level
(level),
    INDEX idx_reg_no
(reg_no),
    INDEX idx_program_id
(program_id),
    INDEX idx_email
(email)
);

-- Admin Table
CREATE TABLE
IF NOT EXISTS admins
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR
(100) NOT NULL UNIQUE,
    password VARCHAR
(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Insert default admin (username: Admin@MCC, password: Admin123)
INSERT INTO admins
    (username, password)
VALUES
    ('Admin@MCC', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY
UPDATE username=username;

-- Insert demo users (for testing)
INSERT INTO users
    (user_type, stream, level, reg_no, name, designation, department, phone_no, email, password, college, status)
VALUES
    -- Demo Student (Aided-UG)
    ('student', 'aided', 'ug', 'STU2024001', 'John Doe', NULL, 'Computer Science', '9876543210', 'john.doe@student.mcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Madras Christian College', 'active'),
    -- Demo Staff
    ('staff', NULL, NULL, NULL, 'Dr. Jane Smith', 'Associate Professor', 'Mathematics', '9876543211', 'jane.smith@staff.mcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Madras Christian College', 'active')
ON DUPLICATE KEY
UPDATE email=email;

-- Certificate Generation Logs
CREATE TABLE
IF NOT EXISTS certificate_logs
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM
('student', 'staff') NOT NULL,
    certificate_type VARCHAR
(100) NOT NULL,
    certificate_no VARCHAR
(100) NULL,
    recipient_name VARCHAR
(255) NOT NULL,
    certified_for TEXT NULL,
    from_date DATE NULL,
    to_date DATE NULL,
    template_used VARCHAR
(255) NULL,
    generation_type ENUM
('single', 'bulk') DEFAULT 'single',
    bulk_count INT DEFAULT 1,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR
(45) NULL,
    FOREIGN KEY
(user_id) REFERENCES users
(id) ON
DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_generation_date
(generated_at),
    INDEX idx_user_type
(user_type)
);

-- User Activity Logs
CREATE TABLE
IF NOT EXISTS activity_logs
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM
('student', 'staff') NOT NULL,
    activity_type VARCHAR
(100) NOT NULL,
    activity_description TEXT NULL,
    ip_address VARCHAR
(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY
(user_id) REFERENCES users
(id) ON
DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_date
(created_at)
);

-- Admin Activity Logs
CREATE TABLE
IF NOT EXISTS admin_logs
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    activity_type VARCHAR
(100) NOT NULL,
    activity_description TEXT NULL,
    ip_address VARCHAR
(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY
(admin_id) REFERENCES admins
(id) ON
DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_activity_date
(created_at)
);

-- Sessions Table (for tracking active users)
CREATE TABLE
IF NOT EXISTS user_sessions
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM
('student', 'staff', 'admin') NOT NULL,
    session_token VARCHAR
(255) NOT NULL UNIQUE,
    ip_address VARCHAR
(45) NULL,
    user_agent TEXT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP,
    logout_time TIMESTAMP
NULL,
    status ENUM
('active', 'expired', 'logged_out') DEFAULT 'active',
    INDEX idx_session_token
(session_token),
    INDEX idx_user_id
(user_id),
    INDEX idx_status
(status)
);
