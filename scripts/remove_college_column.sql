-- Migration to drop 'college' column from users table
-- Run this on a backup of your database first

ALTER TABLE users DROP COLUMN college;

-- If you had additional references or columns relying on college you may want to update them as well.
