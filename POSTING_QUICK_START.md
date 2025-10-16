# 🚀 Quick Start - Posting System

## Setup (2 Minutes)

### 1. Run SQL Script
```sql
-- Copy and paste this into phpMyAdmin SQL tab

-- Add username to posts table
ALTER TABLE `posts` 
ADD COLUMN `username` varchar(50) DEFAULT NULL AFTER `user_email`;

-- Create likes table
CREATE TABLE IF NOT EXISTS `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`post_id`, `user_id`),
  CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create comments table (for future use)
CREATE TABLE IF NOT EXISTS `post_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `post_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Test It!
1. Go to `http://localhost:8000/user/dashboard.php`
2. Click "What's on your mind"
3. Fill out the form
4. Click "Post Review"
5. Your post appears!

## Features

✅ **Create** - Post reviews with title & content
✅ **Read** - View all posts in feed
✅ **Update** - Edit your own posts (three-dot menu)
✅ **Delete** - Remove your posts (three-dot menu)
✅ **Like** - Star posts (one like per user)
✅ **Unlike** - Click again to remove like
✅ **Ownership** - Only you can edit/delete your posts
✅ **Persistence** - Likes saved on reload

## How to Use

### Create a Post
1. Click the "What's on your mind" input box
2. Form expands
3. Select game (optional for now)
4. Enter title and content
5. Click "Post Review"

### Like a Post
- Click the star icon
- Star fills gold when liked
- Click again to unlike

### Edit Your Post
1. Click three-dot menu (top right of your post)
2. Click "Edit"
3. Change title/content
4. Click "Save"

### Delete Your Post
1. Click three-dot menu
2. Click "Delete"
3. Confirm deletion

## Files Created

✅ `/api/posts.php` - Backend API
✅ `/assets/js/dashboard-posts.js` - Frontend JavaScript
✅ `setup-posts-tables.sql` - Database setup
✅ Dashboard updated with new functionality

## Notes

- Dummy "Elden Ring" post stays visible as reference
- Game dropdown is UI-only (functionality coming later)
- One like per user per post (database enforced)
- Comments table created but not yet implemented
- All operations require authentication

---
**Ready!** Just run the SQL and start posting! 🎮
