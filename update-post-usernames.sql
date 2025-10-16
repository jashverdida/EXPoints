-- Update existing posts with usernames from user_info table
-- Run this AFTER creating the username column and user_info entries

UPDATE posts p
JOIN users u ON p.user_email = u.email
JOIN user_info ui ON u.id = ui.user_id
SET p.username = ui.username
WHERE p.username IS NULL;

-- Verify the update
SELECT id, user_email, username, title FROM posts;
