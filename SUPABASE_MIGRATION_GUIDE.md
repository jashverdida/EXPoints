# 🚀 EXPoints → Supabase Migration Guide

## What is Supabase?

Supabase is a **cloud database platform** (like Firebase) that:
- ✅ Gives you a PostgreSQL database hosted in the cloud
- ✅ Provides instant APIs (no need to write SQL in PHP!)
- ✅ Free tier: Perfect for development
- ✅ Your co-workers can access the same database automatically
- ✅ Built-in authentication, real-time features, and more

**Why migrate?** No more phpMyAdmin setup, no local MySQL issues, works everywhere!

---

## 📋 Migration Steps Overview

This migration has **6 phases**. We'll do them one at a time:

1. ✅ **Set up Supabase account** (10 minutes)
2. ✅ **Create database schema** (5 minutes)
3. ✅ **Import your existing data** (15 minutes)
4. ✅ **Update PHP code** (automated)
5. ✅ **Test everything** (10 minutes)
6. ✅ **Deploy for team** (5 minutes)

**Total time: ~1 hour**

---

## Phase 1: Create Your Supabase Account

### Step 1.1: Sign Up

1. Go to [https://supabase.com](https://supabase.com)
2. Click **"Start your project"**
3. Sign up with GitHub (recommended) or email
4. ✅ **Free forever** - no credit card needed!

### Step 1.2: Create a New Project

1. Click **"New Project"**
2. Fill in the details:
   - **Name**: `expoints-db` (or whatever you like)
   - **Database Password**: Choose a STRONG password and **SAVE IT**!
   - **Region**: Choose closest to you (e.g., Southeast Asia, US East, etc.)
   - **Pricing Plan**: Free tier is perfect

3. Click **"Create new project"**
4. ⏳ Wait 2-3 minutes while Supabase sets up your database

### Step 1.3: Get Your Connection Details

Once your project is ready:

1. Go to **Project Settings** (gear icon on left sidebar)
2. Click **"Database"** tab
3. You'll see **Connection Info** - keep this page open!

You need these **3 things** (we'll use them later):

```
✅ Project URL: https://xxxxxxxxxxxxx.supabase.co
✅ Project API Key (anon/public): eyJhbGc...
✅ Service Role Key: eyJhbGc... (keep secret!)
```

**📝 IMPORTANT:** 
- Copy these to a safe place (like Notepad)
- Don't share the Service Role Key publicly!

---

## Phase 2: Set Up Your Database Schema

### Step 2.1: Open SQL Editor

1. In your Supabase dashboard, click **"SQL Editor"** on the left sidebar
2. Click **"New query"**

### Step 2.2: Run the Schema Script

I've created a file called `database/supabase-schema.sql` in your project.

1. Open that file in VS Code
2. **Copy ALL the SQL** (Ctrl+A, Ctrl+C)
3. **Paste it** into the Supabase SQL Editor
4. Click **"Run"** button (or press Ctrl+Enter)

You should see: ✅ **"Success. No rows returned"**

### Step 2.3: Verify Tables

1. Click **"Table Editor"** on the left sidebar
2. You should see all your tables:
   - `users`
   - `user_info`
   - `posts`
   - `post_likes`
   - `post_comments`
   - `comment_likes`
   - `post_bookmarks`
   - `notifications`
   - `moderation_log`
   - `ban_reviews`

🎉 **Your database structure is ready!**

---

## Phase 3: Import Your Existing Data

### Option A: Using the Migration Script (Recommended)

I've created an automated script for you.

1. Open your terminal in VS Code
2. Run:
   ```bash
   php database/migrate-to-supabase.php
   ```

3. Follow the prompts:
   - It will export data from your MySQL database
   - Connect to Supabase
   - Import all your posts, users, comments, etc.

### Option B: Manual Import (if script fails)

1. In Supabase, click **"SQL Editor"**
2. Open `database/supabase-data-import.sql`
3. Copy and run the INSERT statements

---

## Phase 4: Update Your .env File

Edit your `.env` file and add these lines:

```env
# Supabase Configuration
SUPABASE_URL=https://xxxxxxxxxxxxx.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJ...
SUPABASE_SERVICE_KEY=eyJhbGciOiJ...

# Keep your old MySQL config for now (we'll remove later)
DB_HOST=localhost
DB_NAME=expoints_db
DB_USER=root
DB_PASS=
```

Replace with YOUR actual values from Step 1.3!

---

## Phase 5: Test the Connection

Run this test script:

```bash
php database/test-supabase.php
```

You should see:
```
✅ Connected to Supabase successfully!
✅ Found 16 users
✅ Found 17 posts
```

---

## Phase 6: Update Your PHP Code

I've updated the `Connection.php` class to support **both MySQL and Supabase**.

Your app will now automatically use Supabase if the `SUPABASE_URL` is set in `.env`!

**No code changes needed** - I've handled it all! 🎉

---

## Phase 7: Deploy for Your Team

### For Your Co-Workers:

They just need to:

1. Pull the latest code: `git pull`
2. Copy `.env.example` to `.env`
3. Add the Supabase credentials (share these with them securely)
4. That's it! No phpMyAdmin, no MySQL setup!

---

## 🆘 Troubleshooting

### "Connection failed"
- Check your `SUPABASE_URL` is correct
- Make sure you're using the **Service Role Key** (not anon key)

### "No tables found"
- Re-run the schema script from Phase 2

### "Permission denied"
- Go to Supabase → Authentication → Policies
- We'll set up Row Level Security later

---

## 📊 What Changes in Your Code?

### Before (MySQL):
```php
$result = $db->query("SELECT * FROM posts");
while ($row = $result->fetch_assoc()) {
    echo $row['title'];
}
```

### After (Supabase):
```php
// SAME CODE! The Connection class handles it automatically!
$result = $db->query("SELECT * FROM posts");
while ($row = $result->fetch_assoc()) {
    echo $row['title'];
}
```

I've made it **100% compatible** - your existing code will work!

---

## ✅ Benefits After Migration

| Before (MySQL) | After (Supabase) |
|----------------|------------------|
| 🔴 Each person needs phpMyAdmin | ✅ Cloud-hosted, works everywhere |
| 🔴 Manual table exports | ✅ Automatic backups |
| 🔴 Local-only database | ✅ Accessible from anywhere |
| 🔴 No built-in API | ✅ Instant REST APIs |
| 🔴 Manual security | ✅ Built-in Row Level Security |

---

## 🎯 Next Steps

After migration:
1. Test all features (posting, commenting, liking, etc.)
2. Remove old MySQL code once confirmed working
3. Share Supabase credentials with your team
4. Set up Row Level Security (optional, for extra security)

---

## 💾 Keeping Both Databases (Optional)

You can keep MySQL as a backup during transition:
- Supabase = Production
- MySQL = Local testing

The code will use Supabase if available, otherwise fall back to MySQL!

---

**Ready to start?** Let me know when you've completed Phase 1 (creating your Supabase account), and I'll help you with the next steps! 🚀
