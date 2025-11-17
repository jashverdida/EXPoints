-- ========================================
-- EXPoints Supabase Schema (PostgreSQL)
-- ========================================
-- Run this in Supabase SQL Editor to create all tables
-- ========================================

-- Enable UUID extension (useful for future features)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS users (
  id BIGSERIAL PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'mod', 'admin')),
  is_disabled SMALLINT DEFAULT 0,
  disabled_reason TEXT,
  disabled_at TIMESTAMP,
  disabled_by VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index on email for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- 2. USER_INFO TABLE
CREATE TABLE IF NOT EXISTS user_info (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  username VARCHAR(50) UNIQUE NOT NULL,
  first_name VARCHAR(50),
  middle_name VARCHAR(50),
  last_name VARCHAR(50),
  suffix VARCHAR(10),
  bio TEXT,
  profile_picture VARCHAR(255),
  exp_points INTEGER DEFAULT 0,
  is_banned SMALLINT DEFAULT 0,
  ban_reason TEXT,
  banned_at TIMESTAMP,
  banned_by VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_user_info_user_id ON user_info(user_id);
CREATE INDEX IF NOT EXISTS idx_user_info_username ON user_info(username);
CREATE INDEX IF NOT EXISTS idx_user_info_exp_points ON user_info(exp_points);

-- 3. POSTS TABLE
CREATE TABLE IF NOT EXISTS posts (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
  game VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  username VARCHAR(100) NOT NULL,
  likes INTEGER DEFAULT 0,
  comments INTEGER DEFAULT 0,
  hidden SMALLINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_posts_user_id ON posts(user_id);
CREATE INDEX IF NOT EXISTS idx_posts_username ON posts(username);
CREATE INDEX IF NOT EXISTS idx_posts_game ON posts(game);
CREATE INDEX IF NOT EXISTS idx_posts_hidden ON posts(hidden);
CREATE INDEX IF NOT EXISTS idx_posts_created_at ON posts(created_at DESC);

-- 4. POST_LIKES TABLE
CREATE TABLE IF NOT EXISTS post_likes (
  id BIGSERIAL PRIMARY KEY,
  post_id BIGINT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(post_id, user_id)
);

CREATE INDEX IF NOT EXISTS idx_post_likes_post_id ON post_likes(post_id);
CREATE INDEX IF NOT EXISTS idx_post_likes_user_id ON post_likes(user_id);

-- 5. POST_COMMENTS TABLE
CREATE TABLE IF NOT EXISTS post_comments (
  id BIGSERIAL PRIMARY KEY,
  parent_comment_id BIGINT REFERENCES post_comments(id) ON DELETE CASCADE,
  post_id BIGINT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  username VARCHAR(50),
  comment TEXT NOT NULL,
  like_count INTEGER DEFAULT 0,
  reply_count INTEGER DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_post_comments_post_id ON post_comments(post_id);
CREATE INDEX IF NOT EXISTS idx_post_comments_user_id ON post_comments(user_id);
CREATE INDEX IF NOT EXISTS idx_post_comments_parent ON post_comments(parent_comment_id);

-- 6. COMMENT_LIKES TABLE
CREATE TABLE IF NOT EXISTS comment_likes (
  id BIGSERIAL PRIMARY KEY,
  comment_id BIGINT NOT NULL REFERENCES post_comments(id) ON DELETE CASCADE,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(comment_id, user_id)
);

CREATE INDEX IF NOT EXISTS idx_comment_likes_comment_id ON comment_likes(comment_id);
CREATE INDEX IF NOT EXISTS idx_comment_likes_user_id ON comment_likes(user_id);

-- 7. POST_BOOKMARKS TABLE
CREATE TABLE IF NOT EXISTS post_bookmarks (
  id BIGSERIAL PRIMARY KEY,
  post_id BIGINT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(post_id, user_id)
);

CREATE INDEX IF NOT EXISTS idx_post_bookmarks_post_id ON post_bookmarks(post_id);
CREATE INDEX IF NOT EXISTS idx_post_bookmarks_user_id ON post_bookmarks(user_id);

-- 8. NOTIFICATIONS TABLE
CREATE TABLE IF NOT EXISTS notifications (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  type VARCHAR(20) CHECK (type IN ('like', 'comment', 'level_up')),
  message TEXT NOT NULL,
  post_id BIGINT,
  from_user_id BIGINT,
  is_read SMALLINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at DESC);

-- 9. MODERATION_LOG TABLE
CREATE TABLE IF NOT EXISTS moderation_log (
  id BIGSERIAL PRIMARY KEY,
  post_id BIGINT NOT NULL,
  moderator VARCHAR(100) NOT NULL,
  action VARCHAR(50) NOT NULL,
  reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_moderation_log_post_id ON moderation_log(post_id);
CREATE INDEX IF NOT EXISTS idx_moderation_log_moderator ON moderation_log(moderator);
CREATE INDEX IF NOT EXISTS idx_moderation_log_created_at ON moderation_log(created_at DESC);

-- 10. BAN_REVIEWS TABLE
CREATE TABLE IF NOT EXISTS ban_reviews (
  id BIGSERIAL PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  post_id BIGINT,
  flagged_by VARCHAR(100) NOT NULL,
  reason TEXT NOT NULL,
  status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
  reviewed_by VARCHAR(100),
  reviewed_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ban_reviews_username ON ban_reviews(username);
CREATE INDEX IF NOT EXISTS idx_ban_reviews_status ON ban_reviews(status);
CREATE INDEX IF NOT EXISTS idx_ban_reviews_flagged_by ON ban_reviews(flagged_by);
CREATE INDEX IF NOT EXISTS idx_ban_reviews_created_at ON ban_reviews(created_at DESC);

-- 11. COMMENTS TABLE (Legacy - for backward compatibility)
CREATE TABLE IF NOT EXISTS comments (
  id BIGSERIAL PRIMARY KEY,
  post_id BIGINT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  username VARCHAR(100) NOT NULL,
  text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id);

-- ========================================
-- FUNCTIONS & TRIGGERS
-- ========================================

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for posts table
DROP TRIGGER IF EXISTS update_posts_updated_at ON posts;
CREATE TRIGGER update_posts_updated_at
  BEFORE UPDATE ON posts
  FOR EACH ROW
  EXECUTE FUNCTION update_updated_at_column();

-- ========================================
-- ROW LEVEL SECURITY (RLS) - Optional
-- ========================================
-- Uncomment these to enable security policies

-- Enable RLS on all tables
-- ALTER TABLE users ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE user_info ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE posts ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE post_likes ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE post_comments ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE comment_likes ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE post_bookmarks ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE notifications ENABLE ROW LEVEL SECURITY;

-- Example policy: Anyone can read posts
-- CREATE POLICY "Posts are viewable by everyone"
--   ON posts FOR SELECT
--   USING (true);

-- Example policy: Users can only update their own posts
-- CREATE POLICY "Users can update own posts"
--   ON posts FOR UPDATE
--   USING (auth.uid()::text = user_id::text);

-- ========================================
-- VERIFICATION
-- ========================================

-- Show all tables
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
ORDER BY table_name;

-- Show table row counts (will be 0 initially)
-- SELECT 
--   'users' as table_name, COUNT(*) as rows FROM users
-- UNION ALL SELECT 'user_info', COUNT(*) FROM user_info
-- UNION ALL SELECT 'posts', COUNT(*) FROM posts
-- UNION ALL SELECT 'post_likes', COUNT(*) FROM post_likes
-- UNION ALL SELECT 'post_comments', COUNT(*) FROM post_comments
-- UNION ALL SELECT 'comment_likes', COUNT(*) FROM comment_likes
-- UNION ALL SELECT 'post_bookmarks', COUNT(*) FROM post_bookmarks
-- UNION ALL SELECT 'notifications', COUNT(*) FROM notifications
-- UNION ALL SELECT 'moderation_log', COUNT(*) FROM moderation_log
-- UNION ALL SELECT 'ban_reviews', COUNT(*) FROM ban_reviews;
