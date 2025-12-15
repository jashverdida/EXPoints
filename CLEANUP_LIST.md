# 🗑️ Supabase Migration - Files to Delete

## ✅ Safe to Delete Immediately

### 1. Test/Debug Files (15 files)
These are development testing files - **safe to delete**:

```
test-api-directly.php
test-api-logic.php
test-basic-posts.php
test-coalesce.php
test-count.php
test-dashboard-approach.php
test-dashboard-query.php
test-dashboard-render.php
test-db-connection.php
test-db-structure.php
test-env.php
test-exp-system.php
test-firebase-api.php
test-firestore-connection.php
test-fixed-query.php
test-json.php
test-posts-display.php
test-prepare.php
test-simple-approach.php
test-simple-dashboard.php
test-simple-db.php
test-simple-query.php
test-with-real-user.php
test_api.php
test_register.php
test_verify.php
debug-posts.php
check-existing-users.sql
check-factory.php
check-hidden-posts.php
check-methods.php
check-user.php
check-usernames.php
test-supabase-connection.php (keep this for now, useful for verification)
test-supabase-functionality.php (keep this for now)
```

### 2. MySQL Setup Scripts (14 files)
These were for setting up local MySQL - **no longer needed with Supabase**:

```
setup-database.php
setup-ban-field.php
setup-bookmarks-table.sql
setup-comment-features.php
setup-comment-features.sql
setup-complete-posts-system.sql
setup-disabled-field.php
setup-firebase.php
setup-instructions.html
setup-moderation-tables.sql
setup-moderation.php
setup-notifications-table.sql
setup-notifications.php
setup-posts-system.php
setup-posts-tables.sql
setup-roles.sql
setup-tables.php
setup-test-user.php
setup-user-info-table.sql
add-banned-field.sql
add-disabled-field.sql
add-name-fields.sql
add-profile-picture-column.php
update-all-user-exp.php
update-existing-posts.sql
update-post-usernames.sql
unhide-all-posts.php
```

### 3. Firebase/Firestore Files (Already Deleted)
✅ These were already removed:
- config/firestore.php
- config/database.php
- api/test-firestore.php
- api/test-database-setup.php
- functions/ (directory)
- firestore-security-rules.txt
- FIRESTORE_SETUP.md
- firebase.json
- .firebaserc

### 4. Old Documentation (13 files)
Migration guides that are now obsolete - **safe to delete**:

```
AUTHENTICATION_FIX.md
BAN_SYSTEM_COMPLETE.md
BOOKMARKS_PAGE_FIXES.md
CLIENT_SIDE_SEARCH_IMPLEMENTATION.md
COMMENT_AVATARS_FIX.md
COMMENT_FEATURES_COMPLETE.md
CRITICAL_FIX_README.md
CUSTOM_GAME_FEATURE.md
DASHBOARD_FIXES.md
DASHBOARD_UX_IMPROVEMENTS.md
DATABASE_COLUMN_FIX.md
DATABASE_SETUP_COMPLETE.md
DEVELOPMENT_SETUP.md
EXP_LEVELING_SYSTEM.md
GAMES_BROWSER_FEATURE.md
HEADER_AVATAR_CSS_FIX.md
LOGOUT_BUTTON_RELOCATED.md
LOGOUT_ZINDEX_FIX.md
MODERATION_SYSTEM.md
MOD_DASHBOARD_FIXES.md
MOD_DASHBOARD_UI_IMPROVEMENTS.md
NEWEST_PAGE_FIXES.md
NOTIFICATION_SYSTEM_COMPLETE.md
PLAYSTATION_PARTICLES.md
POPULAR_PAGE_FIXES.md
POSTING_FIX_README.md
POSTING_QUICK_START.md
POSTING_SYSTEM_GUIDE.md
POSTS_DISPLAY_FIX.md
POSTS_SYSTEM_COMPLETE.md
POST_IMPROVEMENTS_COMPLETE.md
PROFILE_HOVER_VIEW_FEATURE.md
PROFILE_PICTURE_SYNC.md
PROFILE_UPDATE_COMPLETE.md
QUICK_FIX_SUMMARY.md
QUICK_SETUP.md
QUICK_START.md
QUICK_START_POSTS.md
REAL_TIME_SEARCH_FEATURE.md
REGISTRATION_SYSTEM.md
ROLE_BASED_ROUTING.md
ROUTING_SETUP.md
SETUP_GUIDE.md
SUPABASE_COMPATIBILITY_REPORT.md
SUPABASE_MIGRATION_GUIDE.md
SUPABASE_QUICK_START.md
WELCOME_MODAL_GUIDE.md
WELCOME_USERNAME_FIX.md
```

---

## ⚠️ Keep These Files

### Essential Files to KEEP:
```
README.md (main documentation)
COMMANDS.md (if has useful commands)
.env (your Supabase credentials)
.env.example (template for new developers)
.gitignore
config/env.php (Supabase environment loader)
config/supabase.php (Supabase service class)
config/supabase-compat.php (MySQL compatibility layer)
includes/db_helper.php (database connection helper)
supabase-migration-schema.sql (your database schema - for reference)
```

---

## 🚀 PowerShell Cleanup Commands

Run these commands from your project root to delete all obsolete files:

```powershell
# Delete test files
Remove-Item test-*.php, debug-*.php, check-*.php, check-*.sql -ErrorAction SilentlyContinue

# Delete setup scripts
Remove-Item setup-*.php, setup-*.sql, add-*.sql, update-*.sql, update-*.php, unhide-*.php -ErrorAction SilentlyContinue

# Delete old documentation (be careful - review first!)
Remove-Item *_FIX*.md, *_FIXES*.md, *_COMPLETE*.md, *_GUIDE.md, *_SYSTEM.md, *_FEATURE.md, QUICK_*.md, SUPABASE_*.md -ErrorAction SilentlyContinue

# Delete setup instructions
Remove-Item setup-instructions.html -ErrorAction SilentlyContinue

# Confirm deletion
Write-Output "✅ Cleanup complete! Obsolete files removed."
```

---

## 📊 Summary

- **Test Files**: 35+ files → DELETE
- **Setup Scripts**: 25+ files → DELETE  
- **Documentation**: 45+ files → DELETE (or archive in /docs/archive/)
- **Firebase Files**: Already deleted ✅
- **Essential Files**: Keep 10 files

**Total Space Saved**: ~2-3 MB of obsolete code

---

## ⚠️ Recommended Approach

Instead of deleting immediately, create an archive:

```powershell
# Create archive folder
New-Item -ItemType Directory -Path "archive/old-mysql-setup" -Force
New-Item -ItemType Directory -Path "archive/old-docs" -Force

# Move instead of delete
Move-Item test-*.php, debug-*.php, check-*.php, setup-*.php -Destination "archive/old-mysql-setup/" -ErrorAction SilentlyContinue
Move-Item *_FIX*.md, *_COMPLETE*.md, *_GUIDE.md -Destination "archive/old-docs/" -ErrorAction SilentlyContinue
```

This way you can review before permanent deletion!
