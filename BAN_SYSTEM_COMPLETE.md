# 🚫 Complete Ban System Documentation

## Overview
The EXPoints platform now has a complete ban system that allows moderators to flag users for ban review, and administrators to make the final ban decision.

## Database Structure

### New Fields in `user_info` Table:
```sql
- is_banned (TINYINT): 0 = Not banned, 1 = Banned
- ban_reason (TEXT): The reason for the ban
- banned_at (DATETIME): Timestamp when user was banned
- banned_by (VARCHAR): Username of admin who banned the user
```

## Workflow

### 1. **Moderator Flags User** (mod/dashboard.php)
   - Moderator reviews a post and clicks "Flag for Ban Review"
   - Enters reason for ban request
   - Creates entry in `ban_reviews` table with status = 'pending'
   
### 2. **Moderator Views Ban Requests** (mod/ban-reviews.php)
   - View-only page showing all pending ban requests
   - Can view full post details
   - Shows info message: "Pending Admin Review"
   - **NO approval/rejection buttons** (admin-only)

### 3. **Administrator Reviews Ban Appeal** (admin/ban-appeals.php)
   - Shows all pending ban requests from moderators
   - Displays:
     - User avatar and username
     - Reason for ban
     - Related post (if applicable)
     - Flagged by (moderator name)
     - Timestamp
   - Two action buttons:
     - **Approve Ban** (Green) - Bans the user
     - **Reject & Clear** (Gray) - Dismisses the ban request

### 4. **Ban Approval Process** (api/review_ban.php)
   When admin clicks "Approve Ban":
   ```
   1. Updates ban_reviews table: status = 'approved', reviewed_by, reviewed_at
   2. Updates user_info table: is_banned = 1, ban_reason, banned_at, banned_by
   3. Logs action in moderation_log table
   4. Returns success message
   ```

### 5. **User Login Check** (user/login.php)
   When a banned user tries to log in:
   ```
   1. Checks is_banned field in user_info
   2. If is_banned = 1:
      - Stores ban details in session
      - Redirects to banned.php
      - Does NOT allow login
   ```

### 6. **Banned User Page** (user/banned.php)
   - Dramatic red/black theme with warning effects
   - Shows "YOU ARE BANNED!" message
   - Displays:
     - Ban reason
     - Banned date
     - Banned by (admin name)
     - Contact email for appeals
   - **"UNDERSTOOD" button** - Returns to login page
   - Session is destroyed (user cannot access site)

## Pages Created

### Admin Pages:
- **admin/ban-appeals.php** - Review and approve/reject ban requests
  - Dark blue theme with red accents
  - Shows pending appeals with full details
  - Action buttons to ban or reject
  - Post preview with "View Full Post" button
  - Real-time stats: Pending, Approved Today, Rejected Today

### Moderator Pages:
- **mod/ban-reviews.php** - View-only ban request list
  - Purple theme matching mod dashboard
  - Shows all pending ban requests
  - Info message about admin review
  - NO action buttons (view-only)

### User Pages:
- **user/banned.php** - Banned user notification page
  - Red/black danger theme
  - Animated warning effects
  - Shows ban details
  - "UNDERSTOOD" button to return to login

## API Endpoints

### api/review_ban.php
**Method:** POST
**Access:** Admin only

**Request Body:**
```json
{
  "review_id": 123,
  "action": "approved" | "rejected",
  "username": "BannedUser"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Ban approved successfully. User has been banned."
}
```

**Actions:**
- `approved`: Bans user, updates ban_reviews and user_info, logs action
- `rejected`: Updates ban_reviews status only, user remains active

## UI Features

### Admin Ban Appeals Dashboard:
- ✅ Dark blue gradient background
- ✅ Red accent colors for warnings
- ✅ Animated shine effects on cards
- ✅ User avatars with first letter
- ✅ "HIGH PRIORITY" badges
- ✅ Ban reason in special red box
- ✅ Post preview with game tags
- ✅ Green "Approve Ban" button
- ✅ Gray "Reject & Clear" button
- ✅ Confirmation dialogs before banning
- ✅ Real-time statistics

### Banned User Page:
- ✅ Red/black danger theme
- ✅ Pulsing background animation
- ✅ Warning stripe at top (animated)
- ✅ Shield-X icon with bounce animation
- ✅ "YOU ARE BANNED" title
- ✅ Ban reason in red-bordered box
- ✅ Ban metadata (date, admin)
- ✅ Support email for appeals
- ✅ Shake animation on page load

## Security Features

1. **Login Protection:** Banned users cannot log in
2. **Session Validation:** Ban status checked at login
3. **Role-Based Access:** Only admins can approve bans
4. **Prepared Statements:** SQL injection protection
5. **Session Destruction:** Banned user sessions cleared

## Testing the System

### Test Ban Flow:
1. **As Moderator:**
   - Go to mod dashboard
   - Click "View" on a post
   - Click "Flag for Ban Review"
   - Enter reason: "Inappropriate content"
   - Submit

2. **As Admin:**
   - Go to admin dashboard
   - Click "Ban Appeals" button
   - View pending ban request
   - Click "Approve Ban"
   - Confirm action

3. **As Banned User:**
   - Log out
   - Try to log in as banned user
   - See "YOU ARE BANNED!" page
   - Click "UNDERSTOOD"
   - Redirected to login page

## Database Queries

### Check if user is banned:
```sql
SELECT is_banned, ban_reason, banned_at, banned_by 
FROM user_info 
WHERE user_id = ?
```

### Get pending ban appeals:
```sql
SELECT br.*, p.title, p.content, p.game 
FROM ban_reviews br 
LEFT JOIN posts p ON br.post_id = p.id 
WHERE br.status = 'pending' 
ORDER BY br.created_at DESC
```

### Ban a user:
```sql
UPDATE user_info 
SET is_banned = 1, 
    ban_reason = ?, 
    banned_at = NOW(), 
    banned_by = ? 
WHERE username = ?
```

## Files Modified/Created

### Created:
- ✅ `admin/ban-appeals.php` - Admin ban review dashboard
- ✅ `mod/ban-reviews.php` - Mod view-only ban list
- ✅ `user/banned.php` - Banned user notification page
- ✅ `api/review_ban.php` - Ban approval API
- ✅ `setup-ban-field.php` - Database setup script
- ✅ `add-banned-field.sql` - SQL migration

### Modified:
- ✅ `user/login.php` - Added ban status check
- ✅ `admin/dashboard.php` - Added "Ban Appeals" button
- ✅ `mod/dashboard.php` - Added "Review Reports" button

## Unban System

### 7. **Admin Manages Banned Users** (admin/manage-users.php)
   - Shows all currently banned users
   - Displays user information:
     - Avatar/Profile picture
     - Username and email
     - Full name (if available)
     - Ban reason
     - Ban date and admin who banned them
   - **"Unban User" button** (Green) - Removes the ban
   - **"View Profile" button** (Blue) - View user details (coming soon)

### 8. **Unban Process** (api/unban_user.php)
   When admin clicks "Unban User":
   ```
   1. Checks if user is actually banned
   2. Updates user_info table:
      - is_banned = 0
      - ban_reason = NULL
      - banned_at = NULL
      - banned_by = NULL
   3. Logs action in moderation_log
   4. Returns success message
   5. User can now log in normally
   ```

## Pages Created

### Admin Pages:
- **admin/ban-appeals.php** - Review and approve/reject ban requests
- **admin/manage-users.php** - View and unban banned users
  - Dark blue theme
  - Shows all banned users with full details
  - Green "Unban User" button
  - Empty state when no banned users
  - Real-time statistics

## API Endpoints

### api/unban_user.php
**Method:** POST
**Access:** Admin only

**Request Body:**
```json
{
  "user_id": 123,
  "username": "UnbannedUser"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User @UnbannedUser has been unbanned successfully. They can now log in again."
}
```

**Actions:**
- Validates user is actually banned
- Resets all ban fields to NULL/0
- Logs unban action in moderation_log
- User can immediately log in

## Files Modified/Created

### Created:
- ✅ `admin/ban-appeals.php` - Admin ban review dashboard
- ✅ `admin/manage-users.php` - Manage banned users page
- ✅ `mod/ban-reviews.php` - Mod view-only ban list
- ✅ `user/banned.php` - Banned user notification page
- ✅ `api/review_ban.php` - Ban approval API
- ✅ `api/unban_user.php` - Unban user API
- ✅ `setup-ban-field.php` - Database setup script
- ✅ `add-banned-field.sql` - SQL migration

### Modified:
- ✅ `user/login.php` - Added ban status check
- ✅ `admin/dashboard.php` - Added "Ban Appeals" and "Manage Users" buttons
- ✅ `mod/dashboard.php` - Added "Review Reports" button

## Future Enhancements

Potential additions:
- [ ] Ban appeal form for banned users
- [ ] Temporary bans with expiration dates
- [ ] Ban history/audit log page
- [ ] Email notifications for bans
- [ ] Bulk ban operations
- [ ] Ban statistics dashboard
- ✅ **Unban functionality for admins** (COMPLETE!)

## Success! 🎉

The complete ban system is now operational with full unban capabilities. Admins have complete control:
- ✅ Approve/reject ban requests from moderators
- ✅ Ban users permanently
- ✅ View all banned users
- ✅ Unban users to restore access
- ✅ Full audit logging of all ban/unban actions

Moderators can flag problematic users, and banned users are properly blocked from accessing the site with a clear notification.
