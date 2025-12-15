# 🎯 EXPoints - Quick Setup (TL;DR)

**For developers who just pulled the repo and need to get started FAST.**

## One-Command Setup (after cloning)

```bash
# 1. Install dependencies
composer install

# 2. Copy environment config
Copy-Item .env.example .env    # Windows PowerShell
# cp .env.example .env         # Mac/Linux

# 3. Edit .env with your MySQL password (if needed)
# Change: DB_PASS=

# 4. Run database installer
php database/install.php

# 5. Start server
php -S localhost:8000
```

## That's It! 🎉

Access: `http://localhost:8000`

## Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| "Access denied" | Check MySQL password in `.env` |
| "Unknown database" | Run `php database/install.php` |
| "Table doesn't exist" | Run `php database/install.php` |
| After `git pull` | Run `php database/install.php` again |

## Making Yourself Admin

```sql
-- In phpMyAdmin or MySQL:
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

---

**Need more details?** See [DEVELOPMENT_SETUP.md](DEVELOPMENT_SETUP.md)
