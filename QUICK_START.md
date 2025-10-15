# 🎮 Registration System - Quick Start Guide

## What Was Done

✅ **Removed Firebase** - Completely replaced with MySQL/phpMyAdmin
✅ **Created user_info table** - Stores usernames, names, bio, XP points
✅ **New registration flow** - Username modal popup after initial form
✅ **Backend handler** - `process_register.php` handles registration
✅ **Database relationships** - Foreign keys linking users to user_info
✅ **Auto role assignment** - New users automatically get 'user' role

## Quick Setup (3 Steps)

### Step 1: Create the Database Table
Open phpMyAdmin and run this SQL:

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
  CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Or simply run the file:** `setup-user-info-table.sql`

### Step 2: Test the Registration
1. Navigate to: `http://localhost/EXPOINTS/user/register.php`
2. Fill in the form fields
3. Click "CONTINUE"
4. Username modal will appear
5. Enter a unique username
6. Click "Complete Registration"
7. Should redirect to dashboard!

### Step 3: Verify in Database
Check if the new user was created:

```sql
SELECT u.id, u.email, u.role, ui.username, ui.first_name, ui.last_name, ui.exp_points
FROM users u
JOIN user_info ui ON u.id = ui.user_id
ORDER BY u.id DESC;
```

## How It Works

### Registration Flow:

```
User fills form → Validates → Shows username modal → Complete registration
                                                      ↓
                                  process_register.php receives data
                                                      ↓
                            Check email exists? Check username taken?
                                                      ↓
                                INSERT into users table (gets ID)
                                                      ↓
                      INSERT into user_info table (linked by user_id)
                                                      ↓
                                    Set session variables
                                                      ↓
                              Return success → Redirect to dashboard
```

### Database Structure:

**users table:**
- id (auto-increment)
- email
- password
- role (defaults to 'user')
- created_at

**user_info table:**
- id (auto-increment)
- user_id → references users.id
- username (unique)
- first_name
- last_name
- bio
- exp_points (defaults to 0)
- created_at

## Files Created/Modified

### New Files:
📄 `setup-user-info-table.sql` - Creates the user_info table
📄 `process_register.php` - Backend registration handler
📄 `REGISTRATION_SYSTEM.md` - Full documentation
📄 `QUICK_START.md` - This file
📄 `check-existing-users.sql` - Helper to view users

### Modified Files:
📝 `user/register.php` - Complete rewrite with username modal

## Features

### What Users See:
- ✨ Modern registration form
- 🎯 Username selection modal with validation
- ✅ Real-time feedback (green check / red error)
- 🚀 Smooth transition to dashboard
- 📱 Fully responsive design

### What You Get:
- 🔒 Email uniqueness validation
- 👤 Username uniqueness validation
- 🔑 Password strength check (min 6 chars)
- 📊 Automatic XP system ready (starts at 0)
- 🎭 Role system integrated (user/mod/admin)
- 🔗 Proper database relationships

## Testing Checklist

- [ ] Run the SQL script to create user_info table
- [ ] Open register.php in browser
- [ ] Fill out registration form
- [ ] See username modal appear
- [ ] Enter username "testuser123"
- [ ] Click "Complete Registration"
- [ ] Check if redirected to dashboard
- [ ] Verify in phpMyAdmin that both tables have new entries
- [ ] Try registering with same email (should show error)
- [ ] Try registering with same username (should show error)

## Common Issues & Solutions

### Issue: "Table doesn't exist"
**Solution:** Run the SQL script from Step 1

### Issue: "Username already taken"
**Solution:** Try a different username or check `SELECT * FROM user_info;`

### Issue: Modal doesn't appear
**Solution:** Check browser console for JavaScript errors

### Issue: "Database connection failed"
**Solution:** Check `process_register.php` database credentials:
- Host: 127.0.0.1
- Database: expoints_db
- Username: root
- Password: (empty)

## Next Steps

Now that registration works, you can:
1. Update login to show username instead of email
2. Create profile page showing user_info data
3. Implement XP earning system
4. Add bio editing feature
5. Add avatar/profile picture upload
6. Implement password hashing for security

## Support

For detailed documentation, see: `REGISTRATION_SYSTEM.md`

---
**Ready to test!** Just run the SQL script and try registering a new user! 🎮
