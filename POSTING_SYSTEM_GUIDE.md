# 📝 Dashboard Posting System - Setup Guide

## Overview
Complete CRUD (Create, Read, Update, Delete) functionality for posts with likes system.

## Database Setup

### Step 1: Run the SQL Script
Open phpMyAdmin and run: `setup-posts-tables.sql`

This will:
1. Add `username` field to `posts` table
2. Create `post_likes` table (prevents duplicate likes per user)
3. Create `post_comments` table (for future comment functionality)

### Step 2: Verify Tables
Check that these tables exist:
- **posts**: id, user_email, username, game, title, content, created_at
- **post_likes**: id, post_id, user_id, created_at (UNIQUE constraint on post_id + user_id)
- **post_comments**: id, post_id, user_id, username, comment, created_at

## Features

### ✅ Create Posts
- Click on "What's on your mind" input
- Form expands with:
  - Game selection (dropdown - currently for UI only)
  - Review title
  - Review content
  - Username (auto-filled from session)
- Submit to create post
- Post appears immediately in feed

### ✅ Read Posts
- All posts load automatically on page load
- Shows:
  - Title
  - Username (@username)
  - Content
  - Like count
  - Comment count
- Dummy post serves as reference

### ✅ Update Posts
- Only post owner sees three-dot menu
- Click menu → Edit
- Inline editor appears
- Edit title and content
- Save or cancel

### ✅ Delete Posts
- Only post owner sees three-dot menu
- Click menu → Delete
- Confirmation dialog
- Post removed from database and UI

### ✅ Like System
- Click star icon to like/unlike
- One like per user per post (database enforced)
- Star fills when liked (gold color)
- Like count updates in real-time
- User's like status persists on reload

## File Structure

```
/api/
  └── posts.php          - Backend API for all post operations

/assets/
  └── js/
      └── dashboard-posts.js  - Frontend JavaScript

/user/
  └── dashboard.php      - Main dashboard with post UI

setup-posts-tables.sql   - Database setup script
```

## API Endpoints

### POST/GET `/api/posts.php`

**Actions:**
- `?action=create` - Create new post
- `?action=update` - Update existing post
- `?action=delete` - Delete post
- `?action=like` - Toggle like on post
- `?action=get_posts` - Get all posts with user like status

**Authentication:**
- All endpoints require active session
- Edit/Delete restricted to post owner

## How It Works

### Creating a Post
1. User clicks simple input box
2. Form expands
3. User fills title, content
4. JavaScript sends POST to `/api/posts.php?action=create`
5. Backend validates, inserts into `posts` table with username from `user_info`
6. Returns success + post_id
7. Frontend reloads posts

### Liking a Post
1. User clicks star icon
2. JavaScript sends POST to `/api/posts.php?action=like&post_id=X`
3. Backend checks if like exists in `post_likes`
4. If exists: DELETE (unlike)
5. If not: INSERT (like)
6. Returns like status + count
7. Frontend updates UI (fills/unfills star)

### Editing a Post
1. User clicks Edit from dropdown
2. Inline form replaces post content
3. User edits title/content
4. JavaScript sends POST to `/api/posts.php?action=update`
5. Backend verifies ownership
6. Updates `posts` table
7. Reloads all posts

### Deleting a Post
1. User clicks Delete from dropdown
2. Confirmation dialog
3. JavaScript sends POST to `/api/posts.php?action=delete`
4. Backend verifies ownership
5. Deletes from `posts` table (likes cascade delete)
6. Frontend removes post element

## Security Features

✅ **Session Authentication** - All API calls require login
✅ **Ownership Verification** - Only post owner can edit/delete
✅ **SQL Injection Prevention** - Prepared statements
✅ **XSS Prevention** - HTML escaping on output
✅ **Duplicate Like Prevention** - UNIQUE constraint in database
✅ **Foreign Key Cascade** - Likes deleted when post deleted

## Testing Checklist

- [ ] Run SQL script in phpMyAdmin
- [ ] Verify all 3 tables exist
- [ ] Login to dashboard
- [ ] Click "What's on your mind" - form should expand
- [ ] Create a new post - should appear in feed
- [ ] Like your own post - star should fill, count should increase
- [ ] Unlike post - star should unfill, count should decrease
- [ ] Click three-dot menu - Edit and Delete options should show
- [ ] Edit post - inline editor should appear
- [ ] Save edit - post should update
- [ ] Delete post - confirmation, then post should disappear
- [ ] Reload page - likes should persist
- [ ] Create another post - dummy post should remain visible

## Dummy Post

The Elden Ring post is kept as a reference example. It demonstrates:
- Proper title format
- Username display (@BethesdaFan321)
- Multi-paragraph content
- Like/comment counts
- Overall post styling

## Future Enhancements

🔮 **Planned Features:**
1. Comment functionality (table already created)
2. Game dropdown integration (real game database)
3. Post images/attachments
4. Star ratings (1-10 scale)
5. Tag system
6. Search/filter posts
7. User profiles

## Troubleshooting

### Issue: "Not authenticated" error
**Solution:** Check session is active, user is logged in

### Issue: Posts not loading
**Solution:** Check browser console, verify API endpoint accessible

### Issue: Like not working
**Solution:** Verify `post_likes` table exists with UNIQUE constraint

### Issue: Can't edit/delete other users' posts
**Solution:** This is correct behavior! Only owners can edit/delete

### Issue: Duplicate likes appearing
**Solution:** Check UNIQUE constraint on (post_id, user_id) in post_likes table

## Database Queries

### Check if user liked a post:
```sql
SELECT * FROM post_likes WHERE post_id = 1 AND user_id = 1;
```

### Get post with like count:
```sql
SELECT p.*, COUNT(pl.id) as like_count 
FROM posts p 
LEFT JOIN post_likes pl ON p.id = pl.post_id 
WHERE p.id = 1 
GROUP BY p.id;
```

### Get user's posts:
```sql
SELECT * FROM posts WHERE user_email = 'user@example.com' ORDER BY created_at DESC;
```

---
**Status:** ✅ Fully functional posting system with CRUD + Likes
**Last Updated:** October 15, 2025
