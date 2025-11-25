-- Migration to drop designation from users table
-- Run with care and take a DB backup before running

ALTER TABLE users DROP COLUMN designation;

-- If you want to remove designation from other tables, run similar commands:
-- ALTER TABLE user_sessions DROP COLUMN user_type; -- (if used)
-- ALTER TABLE activity_logs DROP COLUMN user_type; -- activity logs may store user_type separate, but that is a different column.

-- Note: After dropping the column, update any queries that previously relied on designation to derive staff (use reg_no absence instead).
