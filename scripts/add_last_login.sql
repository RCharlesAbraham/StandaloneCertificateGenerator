-- Add last_login column to users table
-- Run this SQL query in phpMyAdmin or MySQL command line

USE certificate_generator;

-- Check if column exists and add it if not
ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER status;

-- Verify the column was added
DESCRIBE users;
