-- Add comment likes and replies functionality

-- Create comment_likes table
CREATE TABLE IF NOT EXISTS comment_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES post_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_comment_id (comment_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add parent_comment_id column to post_comments for replies
ALTER TABLE post_comments 
ADD COLUMN parent_comment_id INT DEFAULT NULL AFTER id,
ADD FOREIGN KEY (parent_comment_id) REFERENCES post_comments(id) ON DELETE CASCADE;

-- Add like_count column to post_comments
ALTER TABLE post_comments 
ADD COLUMN like_count INT DEFAULT 0 AFTER comment;

-- Add reply_count column to post_comments
ALTER TABLE post_comments 
ADD COLUMN reply_count INT DEFAULT 0 AFTER like_count;
