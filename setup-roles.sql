-- ========================================
-- EXPoints Role Setup SQL Script
-- ========================================
-- Run this in phpMyAdmin SQL tab

-- 1. Add role column if it doesn't exist
-- (If you get an error, the column already exists - that's OK!)
ALTER TABLE users 
ADD COLUMN role ENUM('user', 'mod', 'admin') DEFAULT 'user' 
AFTER password;

-- 2. Set default role for all existing users
UPDATE users SET role = 'user' WHERE role IS NULL;

-- ========================================
-- Example: Set specific users as moderators or admins
-- ========================================

-- Make a specific user an ADMIN
-- UPDATE users SET role = 'admin' WHERE email = 'your-admin@email.com';

-- Make a specific user a MODERATOR
-- UPDATE users SET role = 'mod' WHERE email = 'your-mod@email.com';

-- Make a specific user a regular USER
-- UPDATE users SET role = 'user' WHERE email = 'regular-user@email.com';

-- ========================================
-- Verify roles
-- ========================================
SELECT id, email, role FROM users ORDER BY role, id;

-- ========================================
-- Useful Queries
-- ========================================

-- Count users by role
SELECT role, COUNT(*) as count 
FROM users 
GROUP BY role;

-- List all admins
SELECT id, email, role 
FROM users 
WHERE role = 'admin';

-- List all moderators
SELECT id, email, role 
FROM users 
WHERE role = 'mod';

-- List all regular users
SELECT id, email, role 
FROM users 
WHERE role = 'user';
