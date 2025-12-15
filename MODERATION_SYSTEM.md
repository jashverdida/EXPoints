# Moderator Dashboard - Post Moderation System

## Overview
The moderator dashboard now includes powerful moderation tools to review posts and manage users effectively.

## Features Implemented

### 1. **Hide Post** 🙈
- **Purpose**: Remove posts from all user feeds and dashboards
- **How to use**: Click the yellow eye-slash button on any post
- **Modal**: Stylish yellow-themed modal with optional reason field
- **Effect**: Post becomes hidden from all users (marked with `hidden = 1` in database)
- **Visual Feedback**: Post row becomes faded and struck through after hiding

### 2. **Flag User for Ban Review** 🚩
- **Purpose**: Report users to administrators for potential account ban
- **How to use**: Click the red flag button on any post
- **Modal**: Red-themed warning modal with required reason field
- **Effect**: Creates entry in `ban_reviews` table for admin review
- **Notification**: Sends flag to admin dashboard for review

### 3. **View Post Details** 👁️
- **Purpose**: View full post content and metadata
- **How to use**: Click the blue eye button
- **Effect**: Opens detailed post modal

## Database Structure

### New Tables Created:

#### `moderation_log`
```sql
- id: INT (Primary Key)
- post_id: INT (Post being moderated)
- moderator: VARCHAR(100) (Who took action)
- action: VARCHAR(50) (hide, unhide, flag_ban)
- reason: TEXT (Why action was taken)
- created_at: DATETIME (When action occurred)
```

#### `ban_reviews`
```sql
- id: INT (Primary Key)
- username: VARCHAR(100) (User being flagged)
- post_id: INT (Post that triggered flag)
- flagged_by: VARCHAR(100) (Moderator who flagged)
- reason: TEXT (Why user should be reviewed)
- status: ENUM (pending, approved, rejected)
- reviewed_by: VARCHAR(100) (Admin who reviewed)
- reviewed_at: DATETIME (When reviewed)
- created_at: DATETIME (When flagged)
```

#### Posts Table Update:
```sql
- hidden: TINYINT(1) (0 = visible, 1 = hidden)
```

## UI Enhancements

### Action Buttons:
- **View (Blue)**: Opens post details modal
- **Hide (Yellow/Warning)**: Hides post from all feeds
- **Flag (Red/Danger)**: Flags user for admin ban review

### Modals:
1. **Hide Post Modal**:
   - Yellow gradient theme (#fbbf24)
   - Optional reason textarea
   - Confirmation required
   - Stylish backdrop blur effect

2. **Flag for Ban Modal**:
   - Red gradient theme (#ef4444)
   - Required reason textarea
   - Shows username being flagged
   - Warning alert about admin notification
   - Stylish backdrop blur effect

### Notifications:
- Toast-style notifications slide in from the right
- Auto-dismiss after 3 seconds
- Success (green), Danger (red), Warning (yellow)
- Smooth slide animations

## API Endpoint

**File**: `/api/moderate_post.php`

**Actions**:
- `hide`: Hide a post
- `unhide`: Unhide a post (admin only)
- `flag_ban`: Flag user for ban review

**Request Format**:
```json
{
  "post_id": 123,
  "action": "hide",
  "reason": "Violates community guidelines"
}
```

**Response Format**:
```json
{
  "success": true,
  "message": "Post hidden successfully"
}
```

## Security

✅ **Authentication Check**: Requires active session
✅ **Role Verification**: Only mods and admins can access
✅ **Input Validation**: All inputs sanitized and validated
✅ **SQL Injection Protection**: Prepared statements used
✅ **XSS Protection**: HTML escaping on output
✅ **Audit Trail**: All actions logged in moderation_log

## Setup Instructions

1. Run the setup script:
   ```bash
   php setup-moderation.php
   ```

2. Tables will be created automatically:
   - `moderation_log`
   - `ban_reviews`
   - `hidden` column added to `posts`

3. Access mod dashboard:
   ```
   /mod/dashboard.php
   ```

## Future Enhancements (Coming Soon)

- [ ] Unhide posts functionality
- [ ] View moderation history
- [ ] Bulk moderation actions
- [ ] Advanced filtering (show hidden posts)
- [ ] Ban review management page
- [ ] User suspension system
- [ ] Appeal system for banned users
- [ ] Automated moderation rules

## Usage Guide for Moderators

### To Hide a Post:
1. Find the post in the "Recent Posts" table
2. Click the yellow eye-slash button
3. Optionally enter a reason
4. Click "Hide Post"
5. Post will be removed from all feeds

### To Flag a User for Ban:
1. Find a problematic post
2. Click the red flag button
3. **Enter a detailed reason** (required)
4. Click "Flag for Ban Review"
5. Admin will be notified for review

### Best Practices:
- Always provide reasons for moderation actions
- Review post content carefully before taking action
- Use hide for minor violations
- Use ban flag for serious or repeat violations
- Document patterns of abuse in the reason field

## Notes

- Hidden posts are NOT deleted, just hidden from view
- All moderation actions are logged and auditable
- Ban reviews require admin approval
- Moderators cannot ban users directly (admin only)
- Search function works with all posts including hidden ones

---

**Powered by EXPoints Moderation System** 🛡️
