# User Notification System

## Overview
A complete notification system that alerts users about interactions with their content, including likes on posts/comments and level-up achievements.

## Features

### Notification Types
1. **Like Notifications** - When someone likes your post
2. **Comment Notifications** - When someone comments on your post
3. **Level Up Notifications** - When you reach a new level

### UI Components
- **Notification Bell Icon** - Located in the top navigation bar
- **Notification Badge** - Red badge showing unread count (with pulsing animation)
- **Notification Dropdown** - Elegant dropdown panel with smooth animations
- **Real-time Updates** - Checks for new notifications every 30 seconds

## Database Structure

### notifications Table
```sql
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('like', 'comment', 'level_up') NOT NULL,
    message TEXT NOT NULL,
    post_id INT DEFAULT NULL,
    from_user_id INT DEFAULT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)
```

## Setup Instructions

1. **Create the notifications table:**
   - Navigate to: `http://localhost:8000/setup-notifications.php`
   - Or run the SQL script: `setup-notifications-table.sql`

2. **Files Created:**
   - `includes/NotificationSystem.php` - Core notification logic
   - `api/notifications.php` - API endpoints for notifications
   - `setup-notifications.php` - Database setup script
   - `setup-notifications-table.sql` - SQL table definition

3. **Files Modified:**
   - `user/dashboard.php` - Added notification UI and JavaScript
   - `api/posts.php` - Integrated notification triggers for likes and comments
   - `includes/ExpSystem.php` - Added level-up tracking

## API Endpoints

### Get Notifications
```
GET /api/notifications.php?action=get_notifications
Response: {
  success: true,
  notifications: [...],
  count: 5
}
```

### Get Unread Count
```
GET /api/notifications.php?action=get_count
Response: {
  success: true,
  count: 5
}
```

### Mark as Read
```
POST /api/notifications.php
Body: action=mark_read&notification_id=123
Response: { success: true }
```

### Mark All as Read
```
POST /api/notifications.php
Body: action=mark_all_read
Response: { success: true }
```

## NotificationSystem Class Methods

### createNotification()
Creates a generic notification
```php
NotificationSystem::createNotification($db, $userId, $type, $message, $postId, $fromUserId)
```

### notifyLike()
Creates a like notification
```php
NotificationSystem::notifyLike($db, $postAuthorId, $likerId, $postId, $postTitle)
```

### notifyComment()
Creates a comment notification
```php
NotificationSystem::notifyComment($db, $postAuthorId, $commenterId, $postId, $postTitle)
```

### notifyLevelUp()
Creates a level-up notification
```php
NotificationSystem::notifyLevelUp($db, $userId, $newLevel)
```

### getUnreadNotifications()
Retrieves unread notifications for a user
```php
$notifications = NotificationSystem::getUnreadNotifications($db, $userId, $limit)
```

### getUnreadCount()
Gets the count of unread notifications
```php
$count = NotificationSystem::getUnreadCount($db, $userId)
```

### markAsRead()
Marks a single notification as read
```php
NotificationSystem::markAsRead($db, $notificationId)
```

### markAllAsRead()
Marks all user's notifications as read
```php
NotificationSystem::markAllAsRead($db, $userId)
```

## How It Works

### 1. Like Notification Flow
```
User A likes User B's post
↓
api/posts.php (like action)
↓
NotificationSystem::notifyLike()
↓
Creates notification in database
↓
User B sees notification badge
↓
User B clicks bell icon
↓
Notification appears in dropdown
```

### 2. Comment Notification Flow
```
User A comments on User B's post
↓
api/posts.php (add_comment action)
↓
NotificationSystem::notifyComment()
↓
Creates notification in database
↓
User B gets notified
```

### 3. Level Up Notification Flow
```
User receives a like (5 EXP)
↓
ExpSystem::updateUserExp() checks old vs new level
↓
If leveled up: NotificationSystem::notifyLevelUp()
↓
Creates level-up notification
↓
User sees celebration notification
```

## UI Features

### Visual Design
- **Gradient backgrounds** - Modern blue gradient theme
- **Icon indicators** - Different colors for each notification type:
  - 🌟 Gold gradient for likes
  - 💬 Blue gradient for comments
  - 🏆 Green gradient for level-ups
- **Smooth animations** - Slide-in effects and pulsing badge
- **Hover effects** - Interactive feedback on hover

### User Experience
- **One-click mark as read** - Click notification to mark as read and navigate to post
- **Mark all as read** - Convenient button to clear all notifications
- **Auto-refresh** - Checks for new notifications every 30 seconds
- **Time stamps** - Relative time display (e.g., "5m ago", "2h ago")
- **Unread indicators** - Visual distinction for unread notifications

## Smart Features

### Prevents Self-Notifications
- Users don't get notified when they like their own posts
- Users don't get notified when they comment on their own posts

### Efficient Querying
- Indexed database columns for fast queries
- Limits notification fetch to 10 most recent by default
- Only fetches unread notifications for badge count

### Level-Up Detection
- Compares old level vs new level after EXP update
- Only sends notification if level actually increased
- Works for both post likes and comment likes

## Customization

### Notification Messages
Edit in `includes/NotificationSystem.php`:
```php
// Like message
$message = "{$likerUsername} liked your post: \"{$postTitle}\"";

// Comment message
$message = "{$commenterUsername} commented on your post: \"{$postTitle}\"";

// Level up message
$message = "🎉 Congratulations! You've reached Level {$newLevel}!";
```

### Notification Colors
Edit in `user/dashboard.php` styles:
```css
.notification-icon.like {
  background: linear-gradient(135deg, #ffd700, #ff8c00);
}
.notification-icon.comment {
  background: linear-gradient(135deg, #38a0ff, #1b378d);
}
.notification-icon.level_up {
  background: linear-gradient(135deg, #00ff88, #00cc6a);
}
```

### Auto-Refresh Interval
Edit in `user/dashboard.php`:
```javascript
// Check for new notifications every 30 seconds
setInterval(updateNotificationCount, 30000); // Change 30000 to your preferred interval in ms
```

## Testing

### Test Notifications
1. **Like Notification:**
   - Log in as User A
   - Like User B's post
   - Log in as User B
   - Check notification bell

2. **Comment Notification:**
   - Log in as User A
   - Comment on User B's post
   - Log in as User B
   - Check notification bell

3. **Level Up Notification:**
   - Find a user at 0 or 5 EXP (close to level up)
   - Have someone like their post
   - Check if level-up notification appears

## Troubleshooting

### Notifications not appearing
1. Check if notifications table exists: `SHOW TABLES LIKE 'notifications';`
2. Check browser console for JavaScript errors
3. Verify API endpoint: `http://localhost:8000/api/notifications.php?action=get_count`

### Badge not updating
1. Check if JavaScript auto-refresh is working (console.log in updateNotificationCount)
2. Verify user_id is set in session
3. Check database foreign key constraints

### Level-up not triggering
1. Verify ExpSystem::updateUserExp() returns 'leveled_up' => true
2. Check if NotificationSystem is being required in api/posts.php
3. Ensure user actually crossed level threshold (1 EXP for level 2, then 10 EXP per level)

## Future Enhancements

Potential improvements:
- Push notifications (using Service Workers)
- Email notifications for important events
- Notification preferences/settings
- Notification history page
- Group similar notifications ("User A and 5 others liked your post")
- Notification sounds
- Mark as read on hover preview

## Summary

The notification system provides real-time feedback to users about interactions with their content, enhancing engagement and user experience. It's fully integrated with the existing EXP/leveling system and follows best practices for performance and user experience.
