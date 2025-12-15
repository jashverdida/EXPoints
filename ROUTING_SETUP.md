# EXPoints Routing Setup

## Root Access Configuration

The EXPoints application can now be accessed from the root URL while maintaining all existing routes.

### URL Structure

#### Root Level (`http://localhost:8000/`)
- **`/`** → Redirects to `user/index.php` (Landing Page)
- **`/index.php`** → Redirects to `user/index.php` (Landing Page)
- **`/logout.php`** → Logs out and redirects to landing page

#### User Routes (`http://localhost:8000/user/`)
- **`/user/index.php`** → Landing Page (works independently)
- **`/user/login.php`** → Login Page
- **`/user/register.php`** → Registration Page
- **`/user/dashboard.php`** → User Dashboard (requires login)
- **`/user/profile.php`** → User Profile (requires login)
- **`/user/posts.php`** → Posts Page
- **`/user/games.php`** → Games Page
- **`/user/popular.php`** → Popular Posts
- **`/user/newest.php`** → Newest Posts

#### Moderator Routes (`http://localhost:8000/mod/`)
- **`/mod/dashboard.php`** → Moderator Dashboard (requires mod role)

#### Admin Routes (`http://localhost:8000/admin/`)
- **`/admin/dashboard.php`** → Admin Dashboard (requires admin role)
- **`/admin/index.php`** → Admin Home
- **`/admin/moderators.php`** → Moderator Management
- **`/admin/reporting.php`** → Reports Management

#### API Routes (`http://localhost:8000/api/`)
- **`/api/comments.php`** → Comments API
- **`/api/reviews.php`** → Reviews API
- **`/api/users.php`** → Users API
- **`/api/get_post.php`** → Get single post details

#### Process Routes (Backend Handlers)
- **`/process_register.php`** → Registration handler (JSON API)
- **`/authenticate_user.php`** → User authentication
- **`/register_user.php`** → Legacy registration

## How It Works

### Root Redirect
The root `index.php` file contains a simple redirect:
```php
<?php
header('Location: user/index.php');
exit();
?>
```

This means:
- ✅ `http://localhost:8000/` → Shows landing page
- ✅ `http://localhost:8000/user/index.php` → Shows landing page (direct access)
- ✅ All existing routes continue to work as before
- ✅ No route conflicts or broken links

### Benefits
1. **User-Friendly URLs** - Users can access the site from the root
2. **Maintains Structure** - All existing file paths remain unchanged
3. **No Breaking Changes** - All internal links continue to work
4. **Clean Navigation** - Logout redirects to landing page properly

### Navigation Flow

```
User enters site
     ↓
http://localhost:8000/
     ↓
Redirects to user/index.php (Landing Page)
     ↓
User clicks "LOGIN!" or "REGISTER!"
     ↓
Goes to login.php or register.php
     ↓
After login → Redirects to appropriate dashboard based on role
     ↓
User clicks logout
     ↓
Goes to logout.php → Back to landing page
```

## Asset Paths

All asset paths in `user/index.php` use relative paths:
- Images: `../assets/img/`
- CSS: `../assets/css/`
- These work correctly from both root and user directory

## Testing

To verify everything works:

1. **Root Access**
   ```
   http://localhost:8000/
   ```
   Should show landing page

2. **Direct Access**
   ```
   http://localhost:8000/user/index.php
   ```
   Should show landing page

3. **Login Flow**
   ```
   http://localhost:8000/ → Click "LOGIN!" → Login → Dashboard
   ```
   
4. **Logout Flow**
   ```
   Dashboard → Logout → Back to landing page
   ```

5. **Registration Flow**
   ```
   http://localhost:8000/ → Click "REGISTER!" → Register → Dashboard
   ```

## Notes

- The root `index.php` is just a redirect file (lightweight)
- The actual landing page is still `user/index.php`
- All relative paths remain unchanged
- No modifications needed to existing pages
- Session management works across all routes
- Role-based routing (user/mod/admin) continues to function

## Troubleshooting

### Issue: "404 Not Found" on root
**Solution:** Make sure `index.php` exists in the root directory

### Issue: Assets not loading
**Solution:** Asset paths in `user/index.php` should use `../assets/` (already configured)

### Issue: Redirect loop
**Solution:** Check that `logout.php` redirects to `index.php`, not `user/index.php`

### Issue: Routes not working
**Solution:** Make sure PHP server is running from the project root:
```bash
php -S localhost:8000
```

---
**Status:** ✅ Root routing configured and working
**Last Updated:** October 15, 2025
