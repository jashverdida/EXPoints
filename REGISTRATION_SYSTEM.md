# User Registration System - MySQL Implementation

## Overview
The registration system has been completely refactored to use MySQL/phpMyAdmin instead of Firebase. The new system stores user data in two tables: `users` and `user_info`.

## Database Structure

### 1. Users Table (existing)
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `email` (VARCHAR, UNIQUE)
- `password` (VARCHAR) - Plain text for now
- `role` (ENUM: 'user', 'mod', 'admin') - Defaults to 'user'
- `created_at` (TIMESTAMP)

### 2. User Info Table (NEW)
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `user_id` (INT, FOREIGN KEY → users.id)
- `username` (VARCHAR 50, UNIQUE)
- `first_name` (VARCHAR 50)
- `last_name` (VARCHAR 50)
- `bio` (TEXT, nullable)
- `exp_points` (INT, default: 0)
- `created_at` (TIMESTAMP)

## Setup Instructions

### Step 1: Create the user_info table
Run the SQL script in phpMyAdmin:
```bash
# Open the file: setup-user-info-table.sql
# Copy and paste the SQL into phpMyAdmin's SQL tab
# Click "Go" to execute
```

Or run directly in phpMyAdmin SQL console:
```sql
CREATE TABLE IF NOT EXISTS `user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `exp_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 2: Verify the setup
Check that the table was created:
```sql
SHOW TABLES;
DESCRIBE user_info;
```

## Registration Flow

### User Experience:
1. User fills out registration form:
   - First Name (required)
   - Middle Name (optional)
   - Last Name (required)
   - Suffix (optional)
   - Email (required)
   - Password (required, min 6 chars)
   - Confirm Password (required)

2. User clicks "CONTINUE"
   - Form validates all fields
   - Passwords are compared
   - Email format is checked

3. Username Modal appears
   - User enters desired username
   - Real-time validation (3-20 chars, alphanumeric + underscore)
   - Visual feedback (green checkmark or red error)

4. User clicks "Complete Registration"
   - Data sent to `process_register.php`
   - Check if email already exists
   - Check if username already taken
   - Insert into `users` table (gets auto-incremented ID)
   - Insert into `user_info` table (linked by user_id)
   - Session variables set
   - Redirect to dashboard

### Backend Processing:
**File: `process_register.php`**
- Receives JSON data
- Validates all inputs
- Uses transactions for data integrity
- Checks for duplicate email/username
- Creates user with role='user'
- Creates user_info profile
- Sets session variables
- Returns JSON response

## Files Modified/Created

### New Files:
1. **setup-user-info-table.sql** - SQL script to create user_info table
2. **process_register.php** - Backend registration handler
3. **REGISTRATION_SYSTEM.md** - This documentation

### Modified Files:
1. **user/register.php** - Complete rewrite:
   - Removed Firebase imports
   - Added username modal HTML
   - Added custom modal styles
   - Replaced Firebase logic with MySQL AJAX calls
   - Added real-time username validation

## Features

### Security:
- ✅ Email uniqueness check
- ✅ Username uniqueness check
- ✅ Password length validation (min 6 chars)
- ✅ Username format validation (alphanumeric + underscore, 3-20 chars)
- ✅ Transaction-based registration (rollback on error)
- ✅ Foreign key constraints (CASCADE delete)

### User Experience:
- ✅ Stylized username modal
- ✅ Real-time validation feedback
- ✅ Clean error messages
- ✅ Success alerts
- ✅ Automatic session creation
- ✅ Smooth redirect to dashboard

### Database Integrity:
- ✅ Auto-incrementing IDs (no manual ID assignment needed)
- ✅ Foreign key relationships
- ✅ Cascade delete (removing user also removes user_info)
- ✅ Unique constraints on email and username
- ✅ Default values (role='user', exp_points=0)

## Testing the Registration

1. Open `user/register.php` in your browser
2. Fill out the form:
   - First Name: "John"
   - Last Name: "Doe"
   - Email: "john.doe@example.com"
   - Password: "password123"
   - Confirm Password: "password123"
3. Click "CONTINUE"
4. Enter username in modal: "johndoe123"
5. Click "Complete Registration"
6. Should redirect to dashboard

## Database Queries

### Check registered users:
```sql
SELECT u.id, u.email, u.role, ui.username, ui.first_name, ui.last_name, ui.exp_points
FROM users u
LEFT JOIN user_info ui ON u.id = ui.user_id
ORDER BY u.id DESC;
```

### Get user profile:
```sql
SELECT u.*, ui.username, ui.first_name, ui.last_name, ui.bio, ui.exp_points
FROM users u
JOIN user_info ui ON u.id = ui.user_id
WHERE u.email = 'user@example.com';
```

## Future Enhancements

Recommended improvements:
1. **Password Hashing** - Use `password_hash()` instead of plain text
2. **Email Verification** - Send verification email before activation
3. **Avatar Upload** - Add profile picture to user_info
4. **Bio Editor** - Let users add/edit their bio
5. **XP System** - Implement XP earning through actions
6. **Username Availability API** - Real-time check while typing

## Notes

- The `id` field in both tables auto-increments, so no manual ID management needed
- Existing users (IDs 1-3) won't have user_info entries unless manually created
- Role is always 'user' for self-registration
- Admins/mods must be created manually in the database
- Session management is compatible with existing login system

## Troubleshooting

### "Table already exists" error:
- The table was already created, you can skip this step
- Or drop the table first: `DROP TABLE user_info;`

### "Foreign key constraint fails":
- Make sure the `users` table exists
- Check that the user_id references a valid user
- Verify InnoDB engine is being used

### "Username already taken":
- Check existing usernames: `SELECT username FROM user_info;`
- Use a different username

### Registration fails silently:
- Check browser console for JavaScript errors
- Check PHP error logs
- Verify database connection in `process_register.php`
- Test the endpoint directly: Send POST to `process_register.php` with JSON data

