-- Add is_banned field to user_info table
ALTER TABLE user_info 
ADD COLUMN is_banned TINYINT(1) DEFAULT 0 AFTER exp_points,
ADD COLUMN ban_reason TEXT DEFAULT NULL AFTER is_banned,
ADD COLUMN banned_at DATETIME DEFAULT NULL AFTER ban_reason,
ADD COLUMN banned_by VARCHAR(255) DEFAULT NULL AFTER banned_at;

-- Update existing records to not be banned
UPDATE user_info SET is_banned = 0 WHERE is_banned IS NULL;
