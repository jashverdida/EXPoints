# Posts System Complete Setup Guide

## Overview
This update adds the following features to your EXPoints platform:
1. ✅ **Likes saved to database** - Post likes are now persisted
2. ✅ **Comments system fixed** - Comments display properly with counts
3. ✅ **Bookmark functionality** - Users can save posts for later
4. ✅ **Bookmarks page** - Dedicated page to view saved posts

## Setup Instructions

### Step 1: Run Database Setup
Open your browser and navigate to:
```
http://localhost:8000/setup-posts-system.php
```

This will automatically create the following tables:
- `post_likes` - Stores user likes on posts
- `post_comments` - Stores comments on posts
- `post_bookmarks` - Stores bookmarked posts

### Step 2: Verify Setup
The setup script will show you:
- Which tables were created
- If any errors occurred
- Current data counts

## New Features

### 1. Likes System
- **Location**: Star icon on each post
- **Functionality**: 
  - Click to like/unlike a post
  - Like count updates in real-time
  - Likes are saved to database
  - Filled star indicates you've liked the post
  - Your like state persists across page reloads

### 2. Comments System
- **Location**: Chat icon on each post
- **Functionality**:
  - Click chat icon to expand comments section
  - View all comments on a post
  - Add new comments
  - Comment count updates automatically
  - Comments load from database on click

### 3. Bookmark System
- **Location**: Bookmark icon next to triple dots menu
- **Functionality**:
  - Click to bookmark/unbookmark a post
  - Bookmarked posts show filled bookmark icon
  - Access all bookmarks via sidebar
  - Consistent styling with other UI elements

### 4. Bookmarks Page
- **Location**: Click bookmark icon in left sidebar
- **Functionality**:
  - Shows all your bookmarked posts
  - Remove bookmarks directly from page
  - Empty state when no bookmarks
  - Like and comment on bookmarked posts

## Files Created/Modified

### New Files:
1. `setup-posts-system.php` - Automated database setup
2. `setup-complete-posts-system.sql` - SQL commands for manual setup
3. `setup-bookmarks-table.sql` - Bookmark table creation
4. `user/bookmarks.php` - Bookmarks page
5. `assets/js/bookmarks.js` - Bookmarks page functionality

### Modified Files:
1. `api/posts.php` - Added bookmark, comments, and improved likes endpoints
2. `user/dashboard.php` - Added bookmark button styling and CSS
3. `assets/js/dashboard-posts.js` - Added bookmark and comments functionality
4. `assets/css/index.css` - Added spacing between posts

## API Endpoints

### Posts API (`api/posts.php`)
- `action=create` - Create new post
- `action=update` - Update existing post
- `action=delete` - Delete post
- `action=like` - Toggle like on post
- `action=bookmark` - Toggle bookmark on post
- `action=get_posts` - Get all posts with like/bookmark status
- `action=get_bookmarked_posts` - Get user's bookmarked posts
- `action=get_comments` - Get comments for a post
- `action=add_comment` - Add comment to post

## Database Schema

### post_likes
```sql
- id (INT, PRIMARY KEY)
- post_id (INT, FOREIGN KEY -> posts.id)
- user_id (INT, FOREIGN KEY -> users.id)
- created_at (TIMESTAMP)
- UNIQUE(post_id, user_id) - One like per user per post
```

### post_comments
```sql
- id (INT, PRIMARY KEY)
- post_id (INT, FOREIGN KEY -> posts.id)
- user_id (INT, FOREIGN KEY -> users.id)
- username (VARCHAR)
- comment (TEXT)
- created_at (TIMESTAMP)
```

### post_bookmarks
```sql
- id (INT, PRIMARY KEY)
- post_id (INT, FOREIGN KEY -> posts.id)
- user_id (INT, FOREIGN KEY -> users.id)
- created_at (TIMESTAMP)
- UNIQUE(post_id, user_id) - One bookmark per user per post
```

## Styling

### Bookmark Button
- Consistent with existing UI icons
- Hover effect with blue glow
- Filled icon when bookmarked
- Smooth transitions

### Comments Section
- Dark themed to match dashboard
- Clear visual separation
- Input field with submit button
- Comments display with user info

## Troubleshooting

### Issue: "Database connection failed"
**Solution**: 
- Verify MySQL is running
- Check database credentials in `api/posts.php`
- Ensure `expoints_db` database exists

### Issue: "Table doesn't exist" errors
**Solution**: 
- Run `setup-posts-system.php` in browser
- Or manually run SQL from `setup-complete-posts-system.sql`

### Issue: Likes not saving
**Solution**:
- Check browser console for errors
- Verify `post_likes` table exists
- Ensure you're logged in with valid session

### Issue: Comments not showing
**Solution**:
- Check `post_comments` table exists
- Verify foreign keys are properly set up
- Check browser console for API errors

### Issue: Bookmarks not working
**Solution**:
- Verify `post_bookmarks` table exists
- Check that sidebar button links to `bookmarks.php`
- Ensure you're logged in

## Testing Checklist

- [ ] Run setup script successfully
- [ ] Like a post - verify it saves to database
- [ ] Unlike a post - verify it removes from database
- [ ] Add a comment - verify it appears and count updates
- [ ] Bookmark a post - verify icon changes
- [ ] Visit bookmarks page - verify post appears
- [ ] Remove bookmark - verify post disappears from bookmarks
- [ ] Check spacing between posts - verify improved layout

## Next Steps

After setup is complete:
1. Clear browser cache
2. Refresh dashboard page
3. Test liking posts
4. Test adding comments
5. Test bookmarking posts
6. Visit bookmarks page

## Support

If you encounter any issues:
1. Check browser console for JavaScript errors
2. Check PHP error logs
3. Verify all tables were created in database
4. Ensure you're logged in with valid session
5. Try clearing browser cache

---

**Status**: All features implemented and ready to use!
**Version**: 1.0
**Date**: October 16, 2025
