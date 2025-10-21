-- Add hidden column to posts table if it doesn't exist
ALTER TABLE posts 
ADD COLUMN IF NOT EXISTS hidden TINYINT(1) DEFAULT 0,
ADD INDEX idx_hidden (hidden);

-- Create moderation_log table
CREATE TABLE IF NOT EXISTS moderation_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    moderator VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_post_id (post_id),
    INDEX idx_moderator (moderator),
    INDEX idx_created_at (created_at)
);

-- Create ban_reviews table
CREATE TABLE IF NOT EXISTS ban_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    post_id INT,
    flagged_by VARCHAR(100) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by VARCHAR(100),
    reviewed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_flagged_by (flagged_by),
    INDEX idx_created_at (created_at)
);
