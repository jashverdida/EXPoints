# 🎯 EXPoints Command Reference

Quick reference for common database and development tasks.

## 🚀 Initial Setup

```bash
# Install PHP dependencies
composer install

# Set up environment
Copy-Item .env.example .env

# Install database
php database/install.php

# Start development server
php -S localhost:8000
```

## 🔄 Daily Development

```bash
# Pull latest changes
git pull origin main

# Update database (after pulling changes)
php database/install.php

# Start server
php -S localhost:8000
```

## 💾 Database Management

```bash
# Create backup
php database/backup.php

# Install/update database schema
php database/install.php

# Check PHP syntax
php -l path/to/file.php
```

## 🔍 Database Queries

### Make user admin
```sql
UPDATE users SET role = 'admin' WHERE email = 'user@example.com';
```

### Check all tables
```sql
USE expoints_db;
SHOW TABLES;
```

### View user roles
```sql
SELECT id, email, username, role FROM users;
```

### Reset a user's password (manually)
```sql
UPDATE users SET password = '$2y$10$...' WHERE email = 'user@example.com';
-- Generate hash in PHP: password_hash('newpassword', PASSWORD_DEFAULT)
```

### Count posts by user
```sql
SELECT username, COUNT(*) as post_count 
FROM posts 
GROUP BY username 
ORDER BY post_count DESC;
```

## 🛠️ Troubleshooting

```bash
# Reset database completely
# In MySQL/phpMyAdmin:
DROP DATABASE expoints_db;
CREATE DATABASE expoints_db;

# Then reinstall:
php database/install.php
```

```bash
# Check Connection.php is loading .env
# Add this temporarily to Connection.php:
var_dump(getenv('DB_NAME')); exit;
```

```bash
# Test database connection
php -r "
\$db = new mysqli('localhost', 'root', '', 'expoints_db');
echo \$db->connect_error ? 'Failed' : 'Connected!';
"
```

## 📦 Git Workflow

```bash
# Before starting work
git pull origin main
php database/install.php

# After making changes
git status
git add .
git commit -m "Description of changes"
git push origin main
```

## 🔐 Environment Variables

Edit `.env` file:
```env
DB_HOST=localhost
DB_NAME=expoints_db
DB_USER=root
DB_PASS=your_password
```

## 🧪 Testing

```bash
# Test specific page
php -S localhost:8000
# Open: http://localhost:8000/dashboard.php

# Check for PHP errors
php -l src/Database/Connection.php
php -l admin/dashboard.php
```

## 📊 Useful SQL Queries

```sql
-- Count users by role
SELECT role, COUNT(*) FROM users GROUP BY role;

-- Recent posts
SELECT * FROM posts ORDER BY created_at DESC LIMIT 10;

-- Posts with most likes
SELECT title, likes, username FROM posts ORDER BY likes DESC LIMIT 10;

-- Users with most XP
SELECT u.username, ui.exp_points 
FROM users u 
JOIN user_info ui ON u.id = ui.user_id 
ORDER BY ui.exp_points DESC 
LIMIT 10;

-- Moderation activity
SELECT * FROM moderation_log ORDER BY created_at DESC LIMIT 20;
```

## 🆘 Emergency Commands

```bash
# Backup before emergency fix
php database/backup.php

# Restore from backup
mysql -u root -p expoints_db < database/backups/expoints_backup_YYYY-MM-DD_HHMMSS.sql

# Check what changed
git status
git diff

# Undo uncommitted changes
git restore path/to/file.php

# Reset to last commit (DANGER!)
git reset --hard HEAD
```

---

**💡 Tip:** Bookmark this file for quick reference!
