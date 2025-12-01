# EXPoints Gaming Review Platform - AI Coding Instructions

## Project Overview
EXPoints is a PHP-based gaming review platform with **Supabase (PostgreSQL)** backend integration, featuring user authentication, posts/reviews, comments, and an EXP gamification system. The platform allows public browsing but requires authentication for interactions.

## Architecture & Data Flow

### Authentication Pattern
- **Frontend**: Supabase Auth (client-side JavaScript in login.php/register.php)
- **Backend**: PHP sessions synchronized with Supabase JWT verification
- **API**: RESTful endpoints in `/api/` directory with PostgreSQL integration

**Critical**: Always use the hybrid auth pattern - Supabase Auth → PHP session sync → PostgreSQL operations

### Core Components
```
Frontend (Pure PHP/Bootstrap): index.php → dashboard.php → games.php
Backend Services: config/supabase.php (SupabaseService class)
                 config/supabase-compat.php (MySQL compatibility layer)
API Layer: api/ directory (users.php, posts.php, comments.php)
Authentication: Supabase Auth + PHP sessions
Database: PostgreSQL tables (users, user_info, posts, post_likes, post_comments, notifications)
```

### PostgreSQL Database Schema
- `users`: { id, email, password, role, is_disabled, created_at }
- `user_info`: { id, user_id, username, first_name, last_name, bio, profile_picture, exp_points, is_banned }
- `posts`: { id, user_id, game, title, content, likes, comments, is_hidden, created_at }
- `post_likes`: { id, post_id, user_id, created_at } UNIQUE(post_id, user_id)
- `post_comments`: { id, post_id, user_id, username, comment, parent_id, created_at }
- `post_bookmarks`: { id, post_id, user_id, created_at } UNIQUE(post_id, user_id)
- `notifications`: { id, user_id, type, title, message, link, is_read, created_at }
- `moderators`: { id, user_id, assigned_by, created_at }
- `moderation_reports`: { id, post_id, reporter_id, reason, status, reviewed_by, created_at }

## Development Patterns

### Adding New Pages
1. Follow naming: `feature.php` (no subdirectories for main pages)
2. Include session check: `session_start(); $isLoggedIn = isset($_SESSION['user_authenticated']);`
3. Use Bootstrap 5.3.7 + custom CSS in `assets/css/`
4. Include sidebar navigation pattern from `dashboard.php`

### Database Operations
- Use `getDBConnection()` from `includes/db_helper.php`
- Returns MySQL-compatible Supabase wrapper via `SupabaseMySQLCompat` class
- Existing MySQL-style queries work automatically (prepare, bind_param, execute, get_result)
- Always wrap in try-catch with proper error responses
- Direct Supabase service available via `getSupabaseService()` for new code

### API Endpoints Pattern
```php
// Clean output, set headers
ob_clean();
header('Content-Type: application/json');

// Handle request method
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': // Read operations
    case 'POST': // Create operations  
    case 'PUT': // Update operations
}
```

### CSS Architecture
- Global styles: `assets/css/index.css` (shared dashboard styles)
- Page-specific: `assets/css/[page-name].css`
- Authentication pages use glass-morphism design pattern

## Setup & Development

### Initial Setup
1. `composer install` (installs Firebase PHP SDK)
2. Configure `config/firebase-service-account.json` (from Firebase Console)
3. Run `setup-database.php` for sample data
4. Set Firestore security rules from `firestore-security-rules.txt`

### Testing Database
Use `api/test-firestore.php` for Firestore connectivity tests

### Firebase Functions
Located in `/functions/` - Node.js 18, deployed separately via `firebase deploy --only functions`

## Key Files & Responsibilities
- `config/database.php`: Direct REST API approach to Firestore (legacy)
- `config/firestore.php`: Modern Firebase Admin SDK integration (preferred)
- `verify_user.php`: Critical auth bridge between Firebase and PHP sessions
- `FIRESTORE_SETUP.md`: Current Firebase permissions status and fixes

## Common Patterns

### User State Management
```php
$isLoggedIn = isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : 'Guest';
```

### Error Handling in APIs
```php
try {
    $result = $firestoreService->operation($data);
    if ($result['success']) {
        echo json_encode(['success' => true, 'data' => $result['data']]);
    } else {
        throw new Exception($result['error']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### Frontend Firebase Integration
Import Firebase v10.7.1 modules, handle auth state, sync with PHP via fetch to verify endpoints.

## Security Considerations
- Firestore rules enforce authenticated writes, public reads
- PHP sessions provide server-side state management
- Input validation in both client and server
- CORS headers configured for API endpoints

## Admin Features
Located in `/admin/` - moderator management, reporting system, user account oversight.
