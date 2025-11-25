-- Migration to drop user_type from users table
-- Run with care and take a DB backup before running

ALTER TABLE users DROP COLUMN user_type;

-- If you also want to remove user_type from other tables (e.g., activity_logs, user_sessions), run similar commands:
-- ALTER TABLE activity_logs DROP COLUMN user_type;
-- ALTER TABLE user_sessions DROP COLUMN user_type;

-- Note: After dropping the column, code should be updated to derive the role from reg_no or designation as implemented in the application.
