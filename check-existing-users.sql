-- Quick setup script for existing users
-- This adds user_info entries for your existing 3 users if they don't have them yet

-- Check if user_info table exists
SELECT 'Checking user_info table...' AS status;

-- View existing users
SELECT 'Existing users:' AS status;
SELECT id, email, role, created_at FROM users;

-- Optional: Add user_info for existing users (UPDATE THESE WITH REAL DATA)
-- Uncomment and modify these if you want to add profiles for existing users:

/*
INSERT INTO user_info (user_id, username, first_name, last_name, exp_points)
VALUES 
  (1, 'user1', 'First', 'User', 0),
  (2, 'user2', 'Second', 'User', 0),
  (3, 'user3', 'Third', 'User', 0)
ON DUPLICATE KEY UPDATE user_id=user_id;
*/

-- View all users with their info
SELECT 'Users with profiles:' AS status;
SELECT 
  u.id,
  u.email,
  u.role,
  ui.username,
  ui.first_name,
  ui.last_name,
  ui.exp_points,
  u.created_at
FROM users u
LEFT JOIN user_info ui ON u.id = ui.user_id
ORDER BY u.id;
