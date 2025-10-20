# User Profile Hover & View Feature

## Overview
Added functionality for users to preview and view other users' profiles by interacting with their profile pictures throughout the application.

## Features Implemented

### 1. **Profile Hover Modal**
- Stylish popup card appears when hovering over any user's profile picture
- Shows:
  - Profile picture
  - Username
  - Level (calculated from EXP)
  - Total EXP points
- 300ms delay before showing (prevents accidental triggers)
- Smooth slide-up animation
- Gaming-themed design with blue glow effects

### 2. **Clickable Profile Pictures**
- All user profile pictures in posts and comments are now clickable
- Clicking takes you to that user's profile page (`view-profile.php`)
- Visual feedback on hover (scale + glow effect)

### 3. **Public Profile View Page** (`view-profile.php`)
- Displays complete user profile information
- Shows:
  - Profile picture with level badge
  - Full name and username
  - Bio/About section
  - Stats: Total Likes Received, Total Posts
  - EXP progress bar to next level
  - Date started
  - Best posts (top 3 by likes)
- Read-only view (no edit buttons)
- Back to Dashboard button
- Automatically redirects to own profile if viewing self

## Files Modified

### 1. **`user/view-profile.php`** (NEW)
- Public profile viewing page
- Similar layout to personal profile page
- Fetches user data by ID from URL parameter
- Calculates level from EXP (1000 EXP per level)
- Displays user stats and best posts

### 2. **`user/dashboard.php`**
- Added Profile Hover Modal HTML structure
- Added CSS styling for hover modal:
  - `.profile-hover-modal` - Modal container
  - `.profile-hover-content` - Modal card with gradient background
  - `.profile-hover-avatar` - Circular avatar with glow
  - `.hover-level-badge` - Level indicator badge
  - `.hover-username` - Username display
  - `.hover-exp` - EXP display
  - `.user-profile-avatar` - Clickable avatar class

### 3. **`assets/js/dashboard-posts.js`**
- Updated `createPostHTML()` to add profile data attributes:
  - `data-user-id` - User's database ID
  - `data-username` - User's username
  - `data-profile-picture` - Profile picture URL
  - `data-exp` - User's EXP points
- Updated `createCommentHTML()` with same data attributes
- Added hover event listeners:
  - `mouseover` - Shows modal after 300ms delay
  - `mouseout` - Hides modal immediately
- Added click event listener:
  - Redirects to `view-profile.php?id={userId}`

### 4. **`api/posts.php`**
- Updated `get_posts` query to include `ui.exp_points`
- Updated `get_comments` query to include `ui.exp_points`
- Added `exp_points` to response data for both posts and comments

## Design Specifications

### Hover Modal Styling
```css
- Background: Blue gradient with glassmorphism effect
- Border: 2px solid rgba(56, 160, 255, 0.4)
- Border Radius: 1rem
- Box Shadow: Multi-layered with blue glow
- Animation: slideInUp (0.2s ease-out)
- Position: Fixed, follows cursor
- Z-Index: 10000 (above all content)
```

### Level Badge
```css
- Background: Linear gradient (blue shades)
- Font: 0.75rem, bold, white
- Padding: 0.25rem 0.5rem
- Position: Absolute, bottom-right of avatar
- Border: 2px solid dark blue
- Box Shadow: Blue glow
```

### Avatar Interactions
```css
- Cursor: pointer
- Hover Transform: scale(1.05)
- Hover Box Shadow: Blue glow (0 0 15px)
- Transition: 0.2s ease
```

## Level Calculation
```javascript
const level = Math.floor(exp / 1000) + 1;
```
- 0-999 EXP = Level 1
- 1000-1999 EXP = Level 2
- 2000-2999 EXP = Level 3
- And so on...

## User Flow

### Hover Interaction
1. User hovers over a profile picture in a post or comment
2. After 300ms, hover modal appears next to the cursor
3. Modal shows username, level, and EXP
4. User moves mouse away → modal disappears

### Click Interaction
1. User clicks on a profile picture
2. JavaScript captures click event
3. Extracts `data-user-id` from element
4. Redirects to `view-profile.php?id={userId}`
5. Profile page loads with user's public information

### Profile View
1. Page loads with user ID from URL
2. Database query fetches user data
3. If viewing own profile → redirects to `profile.php`
4. If user not found → redirects to dashboard with error
5. Displays complete profile information
6. User can click "Back to Dashboard" button

## Security
- ✅ Session authentication required
- ✅ SQL prepared statements (prevents SQL injection)
- ✅ HTML escaping (prevents XSS)
- ✅ User ID validation (intval)
- ✅ Own profile redirect (privacy)

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Edge, Safari)
- CSS animations supported
- JavaScript ES6+ features used

## Future Enhancements
- Add "Follow" button on public profiles
- Show mutual friends/followers
- Display recent activity
- Add private messaging option
- Show game preferences and favorite genres
