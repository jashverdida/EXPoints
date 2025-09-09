# EXPoints Gaming Review Platform - AI Coding Instructions

## Project Overview
EXPoints is a PHP-based gaming review platform with Firebase/Firestore backend integration, featuring user authentication, reviews, comments, and a "StarUp" gamification system. The platform allows public browsing but requires authentication for interactions.

## Architecture & Data Flow

### Authentication Pattern
- **Frontend**: Firebase Auth (client-side JavaScript in login.php/register.php)
- **Backend**: PHP sessions synchronized with Firebase via `verify_user.php`
- **API**: RESTful endpoints in `/api/` directory with Firestore integration

**Critical**: Always use the hybrid auth pattern - Firebase Auth → PHP session sync → Firestore operations

### Core Components
```
Frontend (Pure PHP/Bootstrap): index.php → dashboard.php → games.php
Backend Services: config/firestore.php (FirestoreService class)
API Layer: api/ directory (users.php, reviews.php, comments.php)
Authentication: Firebase Auth + PHP sessions
Database: Firestore collections (users, reviews, comments, likes)
```

### Firestore Collections Schema
- `users`: { email, displayName, avatar, stats: {totalReviews, totalComments, totalLikes} }
- `reviews`: { userId, gameTitle, rating (1-5), content, platform, createdAt, likes }
- `comments`: { reviewId, userId, content, createdAt }
- `likes`: { reviewId, userId, createdAt }

## Development Patterns

### Adding New Pages
1. Follow naming: `feature.php` (no subdirectories for main pages)
2. Include session check: `session_start(); $isLoggedIn = isset($_SESSION['user_authenticated']);`
3. Use Bootstrap 5.3.7 + custom CSS in `assets/css/`
4. Include sidebar navigation pattern from `dashboard.php`

### Database Operations
- Use `FirestoreService` class from `config/firestore.php`
- Always wrap in try-catch with proper error responses
- Return format: `['success' => bool, 'data' => array, 'error' => string]`

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
