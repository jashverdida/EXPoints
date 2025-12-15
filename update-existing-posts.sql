-- Update existing posts with user_id values
-- Run this AFTER the main setup-posts-tables.sql

-- This query matches existing posts to users by username
-- and populates the user_id field
-- Using COLLATE to handle character set differences
UPDATE posts p 
INNER JOIN user_info ui ON p.username COLLATE utf8mb4_unicode_ci = ui.username COLLATE utf8mb4_unicode_ci
INNER JOIN users u ON ui.user_id = u.id
SET p.user_id = u.id 
WHERE p.user_id IS NULL;

-- Verify the update
SELECT id, title, username, user_id FROM posts;
