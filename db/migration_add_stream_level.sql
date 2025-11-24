-- Migration Script: Add Stream, Level, and Program ID to Users Table
-- Run this script if you have already created the database with the old schema
-- Date: 2024-11-24

USE certificate_generator;

-- Add new columns to existing users table
ALTER TABLE users 
ADD COLUMN stream ENUM
('aided', 'sfs') NULL COMMENT 'Aided or Self-Financed Stream' AFTER user_type,
ADD COLUMN level ENUM
('ug', 'pg') NULL COMMENT 'Undergraduate or Postgraduate' AFTER stream,
ADD COLUMN program_id VARCHAR
(50) NULL COMMENT 'Auto-generated program identifier' AFTER reg_no;

-- Add indexes for new columns
ALTER TABLE users 
ADD INDEX idx_stream (stream)
,
ADD INDEX idx_level
(level),
ADD INDEX idx_program_id
(program_id);

-- Display confirmation
SELECT 'Migration completed successfully. New columns added: stream, level, program_id' AS Status;
