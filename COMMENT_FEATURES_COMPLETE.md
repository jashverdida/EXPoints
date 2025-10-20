# Comment Features & Post Timestamps - Complete ✅

## Features Implemented

### 1. **Comment Likes** ⭐
- Users can now like/unlike comments
- Like count displayed next to each comment
- Visual feedback (filled/unfilled star icon)
- Likes persist in database

### 2. **Comment Replies** 💬
- Users can reply to comments (nested comments)
- "Reply" button on each comment
- Reply count badge shows number of replies
- "View X replies" button to expand/collapse replies
- Replies displayed with smaller avatars (32px vs 36px)

### 3. **Facebook-Style Timestamps** ⏰
- Posts display time in human-readable format
- Examples: "Just now", "5m", "2h", "Yesterday", "3d", "2w", "Oct 20", etc.
- Positioned to the right of post title
- Styled consistently with username text

## Database Changes

### New Tables Created:

#### `comment_likes` Table
```sql
CREATE TABLE comment_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES post_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Columns Added to `post_comments`:

```sql
ALTER TABLE post_comments 
ADD COLUMN parent_comment_id INT DEFAULT NULL,      -- For nested replies
ADD COLUMN like_count INT DEFAULT 0,                -- Cache like count
ADD COLUMN reply_count INT DEFAULT 0;               -- Cache reply count
```

## API Endpoints Added

### 1. **Like Comment** - `POST /api/posts.php?action=like_comment`
**Parameters**: `comment_id`

**Response**:
```json
{
    "success": true,
    "liked": true,
    "like_count": 5
}
```

### 2. **Add Reply** - `POST /api/posts.php?action=add_reply`
**Parameters**: `parent_comment_id`, `post_id`, `comment`

**Response**:
```json
{
    "success": true,
    "reply_id": 123,
    "message": "Reply added successfully"
}
```

### 3. **Get Replies** - `GET /api/posts.php?action=get_replies`
**Parameters**: `parent_comment_id`

**Response**:
```json
{
    "success": true,
    "replies": [
        {
            "id": 123,
            "comment": "Great point!",
            "username": "JohnDoe",
            "user_id": 5,
            "created_at": "2025-10-20 14:30:00",
            "like_count": 2,
            "user_liked": false,
            "commenter_profile_picture": "../assets/img/profiles/user_5_1234567890.jpg",
            "is_owner": false
        }
    ]
}
```

### 4. **Updated Get Comments** - `GET /api/posts.php?action=get_comments`
Now includes:
- `like_count` - Number of likes on the comment
- `reply_count` - Number of replies to the comment
- `user_liked` - Boolean if current user liked this comment
- Only returns top-level comments (where `parent_comment_id IS NULL`)

## JavaScript Functions Added

### Time Formatting:
```javascript
timeAgo(dateString)
```
- Converts database timestamp to Facebook-style format
- Returns: "Just now", "5m", "2h", "Yesterday", "3d", "2w", "Oct 20", "Oct 20, 2024"

### Comment Interactions:
```javascript
toggleCommentLike(commentId, likeBtn)      // Like/unlike a comment
showReplyInput(commentId)                   // Show reply input box
hideReplyInput(commentId)                   // Hide reply input box
submitReply(parentCommentId, postId, input) // Submit a reply
loadReplies(parentCommentId)                // Load and display replies
createReplyHTML(reply)                      // Generate reply HTML
```

## UI/UX Changes

### Post Timestamps:
- Displayed to the right of post title
- Smaller font size (0.875rem)
- Muted color (rgba(255, 255, 255, 0.5))
- Same styling as username (@handle)

**Before**:
```html
<h2 class="title mb-1">Post Title</h2>
<div class="handle mb-3">@username</div>
```

**After**:
```html
<div style="display: flex; align-items: baseline; gap: 0.75rem;">
    <h2 class="title" style="margin: 0;">Post Title</h2>
    <span class="post-timestamp" style="...">5m</span>
</div>
<div class="handle mb-3">@username</div>
```

### Comment Actions:
Each comment now displays:
- **Like button**: Star icon with count
- **Reply button**: Click to show reply input
- **Timestamp**: e.g., "Just now", "5m ago"
- **View replies button**: Shows if comment has replies (e.g., "View 3 replies")

### Reply Features:
- Reply input appears below comment when "Reply" is clicked
- Includes "Post" and "Cancel" buttons
- Replies displayed in nested container with left margin
- Smaller avatars for replies (32px vs 36px for comments)

## File Changes

### 1. **setup-comment-features.php** (New)
- Automated database setup script
- Creates `comment_likes` table
- Adds columns to `post_comments` table
- Checks for existing columns before adding

### 2. **api/posts.php**
- Added `like_comment` endpoint
- Added `add_reply` endpoint
- Added `get_replies` endpoint
- Updated `get_comments` to include like/reply counts and filter top-level comments only

### 3. **assets/js/dashboard-posts.js**
- Added `timeAgo()` function for timestamp formatting
- Updated `createPostHTML()` to include timestamp
- Updated `createCommentHTML()` to include like/reply buttons
- Added `createReplyHTML()` for nested replies
- Added comment interaction functions
- Added event listeners for all new buttons

## How It Works

### Comment Like Flow:
1. User clicks star icon on comment
2. JavaScript calls `/api/posts.php?action=like_comment`
3. API checks if user already liked → toggle state
4. API updates `comment_likes` table
5. API updates `like_count` in `post_comments`
6. Returns updated like status and count
7. JavaScript updates UI (icon and count)

### Comment Reply Flow:
1. User clicks "Reply" on comment
2. Reply input box appears below comment
3. User types reply and clicks "Post"
4. JavaScript calls `/api/posts.php?action=add_reply`
5. API inserts reply with `parent_comment_id` set
6. API increments `reply_count` on parent comment
7. Reply appears in nested container
8. "View X replies" button updates

### View Replies Flow:
1. User clicks "View X replies" button
2. JavaScript calls `/api/posts.php?action=get_replies`
3. API fetches all comments where `parent_comment_id` matches
4. Replies displayed in nested container with smaller avatars
5. Each reply has its own like button
6. Clicking again collapses replies

### Timestamp Display:
1. Post/comment created with `created_at` timestamp
2. JavaScript converts to time ago format on render
3. Updates show relative time: "5m" → "6m" on refresh
4. Older posts show date: "Oct 20" or "Oct 20, 2024"

## Testing Checklist

- [x] Database setup completed successfully
- [x] Like a comment - see count increment
- [x] Unlike a comment - see count decrement  
- [x] Reply to a comment - see reply appear
- [x] View replies - see nested replies with smaller avatars
- [x] Like a reply - see star fill and count update
- [x] Post timestamps display correctly ("Just now", "5m", etc.)
- [x] Older posts show dates ("Oct 20")
- [x] Timestamp positioned correctly next to title

## Visual Examples

### Comment with Actions:
```
[@avatar] @username
          This is a great post!
          ⭐ 5   Reply   Just now   View 2 replies
          
          [Nested replies appear here when expanded]
          
          [Reply input box appears here when Reply clicked]
```

### Post with Timestamp:
```
Post Title About Gaming                    5m
@username
This is the post content...
⭐ 50  💬 4
```

## Benefits

✅ **Increased Engagement**: Users can like and reply to comments
✅ **Better Conversations**: Nested replies keep discussions organized
✅ **Clear Timing**: Facebook-style timestamps are intuitive
✅ **Real-time Feedback**: Instant visual updates on likes
✅ **Database Efficient**: Counts cached in columns for performance
✅ **User-Friendly**: Clean, familiar UI patterns

---

**Status**: ✅ All features implemented and tested
**Date**: 2025-10-20
**Database**: Updated with new tables and columns
**API**: 3 new endpoints + 1 updated endpoint
**Files Modified**: 2 files + 2 new files created
