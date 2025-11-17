# 🚀 EXPoints - Developer Setup Guide

This guide will help your team members set up the EXPoints system on their local machines.

## 📋 Prerequisites

Before you begin, make sure you have the following installed:
- **PHP 7.4 or higher** (with mysqli extension)
- **MySQL 5.7 or higher** / **MariaDB 10.3 or higher**
- **Composer** (for PHP dependencies)
- **Git** (to clone the repository)
- **Web server** (Apache/Nginx) or **PHP's built-in server**

## ⚙️ Installation Steps

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd EXPoints
```

### 2. Install PHP Dependencies

```bash
composer install
```

If you don't have composer installed, download it from [getcomposer.org](https://getcomposer.org/)

### 3. Configure Environment Variables

Copy the example environment file and configure it:

```bash
# On Windows (PowerShell)
Copy-Item .env.example .env

# On Mac/Linux
cp .env.example .env
```

Edit the `.env` file with your local database credentials:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=expoints_db
DB_USER=root
DB_PASS=your_password_here
```

**⚠️ IMPORTANT:** Never commit the `.env` file to git! It's already in `.gitignore`.

### 4. Set Up the Database

You have two options to set up the database:

#### Option A: Automated Installation (Recommended)

Run the installation script from the command line:

```bash
# From the project root directory
php database/install.php
```

Or access it via your browser:
```
http://localhost/database/install.php
```

This will:
- Create the `expoints_db` database if it doesn't exist
- Set up all required tables with proper relationships
- Verify the installation

#### Option B: Manual Installation

If you prefer to do it manually:

1. Open phpMyAdmin or your MySQL client
2. Create a new database named `expoints_db`
3. Import the schema file: `database/complete-schema.sql`

```sql
CREATE DATABASE expoints_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE expoints_db;
SOURCE /path/to/database/complete-schema.sql;
```

### 5. Verify Installation

Check that all tables were created:

```sql
USE expoints_db;
SHOW TABLES;
```

You should see these tables:
- `ban_reviews`
- `comment_likes`
- `comments`
- `moderation_log`
- `notifications`
- `post_bookmarks`
- `post_comments`
- `post_likes`
- `posts`
- `user_info`
- `users`

### 6. Start the Development Server

You can use PHP's built-in server for development:

```bash
# Start server on port 8000
php -S localhost:8000
```

Then access the application at: `http://localhost:8000`

Or configure your Apache/Nginx virtual host to point to the project directory.

### 7. Create Your First Admin User

Access the registration page and create your account, then manually set your role to admin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

## 🗂️ Database Schema Overview

The system uses the following main tables:

| Table | Purpose |
|-------|---------|
| **users** | Main authentication and user accounts |
| **user_info** | Extended user profiles (names, bio, exp points, etc.) |
| **posts** | User-generated content |
| **post_likes** | Tracks post likes |
| **post_comments** | Comments and replies on posts |
| **comment_likes** | Tracks comment likes |
| **post_bookmarks** | User bookmarks |
| **notifications** | User notifications system |
| **moderation_log** | Moderation actions history |
| **ban_reviews** | Ban review requests |

All tables use foreign keys to maintain referential integrity.

## 🔄 Syncing Database Changes

When pulling updates from the repository:

1. **Pull the latest code:**
   ```bash
   git pull origin main
   ```

2. **Check for database schema updates:**
   - Look for new `.sql` files in the project
   - Check the commit messages for database changes

3. **Apply schema updates:**
   ```bash
   # Re-run the installer (it's safe, uses IF NOT EXISTS)
   php database/install.php
   ```

## 🛠️ Troubleshooting

### Connection Issues

**Error:** "Access denied for user"
- Check your `.env` file has correct database credentials
- Verify MySQL is running
- Test credentials directly in phpMyAdmin/MySQL client

**Error:** "Unknown database 'expoints_db'"
- Run the installation script: `php database/install.php`
- Or manually create the database in phpMyAdmin

### Permission Issues

**Error:** "Can't create table"
- Ensure your MySQL user has CREATE/ALTER privileges
- For development, use root user or create a user with full permissions:

```sql
CREATE USER 'expoints_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON expoints_db.* TO 'expoints_user'@'localhost';
FLUSH PRIVILEGES;
```

### Missing Tables

If you see errors about missing tables:
```bash
# Re-run the complete installation
php database/install.php
```

### Foreign Key Constraints

If you need to reset the database:
```sql
DROP DATABASE expoints_db;
CREATE DATABASE expoints_db;
-- Then run the installer again
```

## 📝 Best Practices

1. **Never commit `.env` file** - It contains sensitive credentials
2. **Always pull before starting work** - Stay synced with the team
3. **Run installer after pulling** - Ensures you have latest schema
4. **Use migrations for schema changes** - Document any manual changes
5. **Backup your local database** - Before making major changes

## 🆘 Getting Help

If you encounter issues:
1. Check this guide first
2. Review error logs in PHP error log
3. Check MySQL error logs
4. Ask the team in your communication channel

## 🔐 Security Notes

- The `.env` file is in `.gitignore` and should NEVER be committed
- Change default credentials in production
- Keep your local MySQL password secure
- Don't share your `.env` file in chat/email

## ✅ Quick Start Checklist

- [ ] PHP and MySQL installed
- [ ] Repository cloned
- [ ] `composer install` completed
- [ ] `.env` file created and configured
- [ ] `php database/install.php` executed successfully
- [ ] Database tables verified
- [ ] Development server running
- [ ] Admin account created

---

**Ready to start developing!** 🎉

For more information about specific features, check the other documentation files in the repository.
