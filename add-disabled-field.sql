-- Add is_disabled field to users table for moderator accounts
ALTER TABLE users 
ADD COLUMN is_disabled TINYINT(1) DEFAULT 0 AFTER role,
ADD COLUMN disabled_reason TEXT DEFAULT NULL AFTER is_disabled,
ADD COLUMN disabled_at DATETIME DEFAULT NULL AFTER disabled_reason,
ADD COLUMN disabled_by VARCHAR(255) DEFAULT NULL AFTER disabled_at;

-- Update existing records to not be disabled
UPDATE users SET is_disabled = 0 WHERE is_disabled IS NULL;
