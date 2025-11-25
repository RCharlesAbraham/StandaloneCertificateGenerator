-- Migration to add email validation constraint for @mcc.edu.in domain
-- Run this after backing up your database

-- Add CHECK constraint for email domain validation
-- Note: MySQL 8.0.16+ supports CHECK constraints

-- For MySQL 8.0.16+:
ALTER TABLE users 
ADD CONSTRAINT chk_email_domain 
CHECK (email LIKE '%@mcc.edu.in');

-- Update existing records that don't match (if any)
-- This will show you records that need manual review
SELECT id, name, email
FROM users
WHERE email NOT LIKE '%@mcc.edu.in';

-- If you want to update existing non-compliant emails, you would need to do it manually:
-- UPDATE users SET email = CONCAT(SUBSTRING_INDEX(email, '@', 1), '@mcc.edu.in') WHERE email NOT LIKE '%@mcc.edu.in';

-- Add comment to email column for documentation
ALTER TABLE users 
MODIFY COLUMN email VARCHAR
(100) NOT NULL UNIQUE COMMENT 'College email - must end with @mcc.edu.in';

-- Verify the constraint
SHOW
CREATE TABLE users;
