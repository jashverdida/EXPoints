# ⚡ Quick Start: Let's Migrate Right Now!

Follow these steps **IN ORDER**. I'll keep it super simple!

---

## Step 1: Add Your Supabase Credentials to .env

1. **Open** the file `.env` in VS Code (in your project root)
   - If it doesn't exist, copy `.env.example` to `.env`

2. **Add these lines** at the TOP of the file:

```env
# Supabase Configuration
SUPABASE_URL=https://YOUR-PROJECT.supabase.co
SUPABASE_ANON_KEY=your-anon-key-here
SUPABASE_SERVICE_KEY=your-service-role-key-here
```

3. **Replace** with YOUR actual values:
   - Go to your Supabase dashboard
   - Click **Settings** (gear icon) → **API**
   - Copy `Project URL` → paste as `SUPABASE_URL`
   - Copy `service_role` key (the secret one) → paste as `SUPABASE_SERVICE_KEY`
   - Copy `anon` key → paste as `SUPABASE_ANON_KEY`

4. **Save** the file (Ctrl+S)

✅ **Done with Step 1!**

---

## Step 2: Create Database Tables in Supabase

1. **Go to** your Supabase dashboard

2. **Click** "SQL Editor" on the left sidebar

3. **Click** "New query"

4. **Open** the file `database/supabase-schema.sql` in VS Code

5. **Select ALL** the SQL code (Ctrl+A)

6. **Copy** it (Ctrl+C)

7. **Go back** to Supabase SQL Editor

8. **Paste** the code (Ctrl+V)

9. **Click** the **"Run"** button (or press Ctrl+Enter)

10. **Wait** a few seconds...

11. **You should see**: ✅ "Success. No rows returned"

✅ **Done with Step 2!**

---

## Step 3: Verify Tables Were Created

1. **In Supabase**, click **"Table Editor"** on the left sidebar

2. **You should see** all these tables:
   - users
   - user_info
   - posts
   - post_likes
   - post_comments
   - comment_likes
   - post_bookmarks
   - notifications
   - moderation_log
   - ban_reviews
   - comments

✅ **Done with Step 3!**

---

## Step 4: Migrate Your Data

1. **Open** your terminal in VS Code (Ctrl+`)

2. **Run** this command:

```bash
php database/migrate-to-supabase.php
```

3. **Watch** the migration happen! You'll see:
   - "Connecting to MySQL..."
   - "Migrating table: users..."
   - "Migrating table: posts..."
   - etc.

4. **Wait** until you see: ✅ "Migration Complete!"

✅ **Done with Step 4!**

---

## Step 5: Test the Connection

1. **In terminal**, run:

```bash
php database/test-supabase.php
```

2. **You should see**:
   - ✅ Connected successfully!
   - ✅ Found X users
   - ✅ Found X posts
   - etc.

✅ **Done with Step 5!**

---

## Step 6: Test Your Application

1. **Start your server**:

```bash
php -S localhost:8000
```

2. **Open** browser: `http://localhost:8000`

3. **Try**:
   - Logging in
   - Viewing posts
   - Creating a new post
   - Commenting
   - Liking posts

4. **Everything should work** exactly as before!

✅ **Done with Step 6!**

---

## 🎉 YOU'RE DONE!

Your app is now using Supabase! 

### What Changed?
- ✅ Database is now in the cloud
- ✅ Your co-workers can access it anywhere
- ✅ No more phpMyAdmin needed
- ✅ Automatic backups
- ✅ Your code works exactly the same!

### Next Steps:
1. **Test all features** thoroughly
2. **Commit changes** to git
3. **Share Supabase credentials** with your team (via secure channel)
4. **They just need** to add the same `.env` values - that's it!

---

## 🆘 Troubleshooting

### "Connection failed"
- Check your `SUPABASE_SERVICE_KEY` (not anon key!)
- Make sure `SUPABASE_URL` is correct

### "Table not found"
- Re-run Step 2 (the SQL schema)

### "Migration failed"
- Make sure your MySQL is running
- Check your MySQL credentials in `.env`

### Still stuck?
Tell me what error you see, and I'll help!

---

**Ready? Start with Step 1!** 🚀
