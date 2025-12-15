# Games Browser Feature - Subreddit Style

## Overview
A complete game browsing system that allows users to explore posts organized by game titles, similar to how subreddits work on Reddit.

## Pages Created

### 1. **games.php** - Game Selection Hub
Main landing page that displays all games that have posts.

#### Features:
- **Hero Section**: Eye-catching header with title and description
- **Search Bar**: Real-time filtering of games by name
- **Game Cards**: Each game displayed as a clickable card showing:
  - Game name with joystick icon
  - Number of posts
  - Last activity date
- **Dynamic Data**: Automatically queries database for unique games from posts
- **Empty State**: Shows friendly message when no games exist yet

#### Styling:
- Gaming-themed blue gradient cards
- Hover effects with lift animation and glow
- Responsive grid layout
- Search bar with glassmorphism effect

### 2. **game-posts.php** - Game-Specific Posts View
Displays all posts for a selected game.

#### Features:
- **Game Header**: Shows game name and stats
- **Back Button**: Navigate back to games list
- **Post Count**: Real-time display of post quantity
- **Posts Feed**: Reuses dashboard post system with all features:
  - Like/unlike posts
  - Comment system
  - Nested replies
  - Profile hover modals
  - Clickable profiles
  - Edit/delete own posts
  - Timestamps
- **Loading State**: Spinner while fetching posts
- **Empty State**: Message when no posts exist for the game

#### Functionality:
- Fetches all posts from API
- Client-side filtering by game name
- Full post interaction support
- Sidebar navigation active on Games tab

## Database Schema

### Posts Table
- Uses existing `posts` table
- `game` column stores game names
- Query groups posts by `game` column

### Query Structure
```sql
-- Get all games with post counts
SELECT 
    game,
    COUNT(*) as post_count,
    MAX(created_at) as last_post_date
FROM posts
WHERE game IS NOT NULL AND game != ''
GROUP BY game
ORDER BY post_count DESC
```

## User Flow

### Browsing Games
1. User clicks "Games" in sidebar → goes to `games.php`
2. Sees all available games with post counts
3. Can search/filter games in real-time
4. Clicks on a game card

### Viewing Game Posts
1. Redirected to `game-posts.php?game={GameName}`
2. Sees game header with name and stats
3. All posts for that game load dynamically
4. Can interact with posts (like, comment, reply)
5. Can click profile pictures to view user profiles
6. Can click "Back to Games" to return

### Navigation
- **Sidebar**: Games button highlighted when on these pages
- **Header**: Back button on game-posts page
- **Logo**: Returns to dashboard
- **Logout**: Available in sidebar

## Styling & Design

### Color Scheme
- Primary Blue: `#38a0ff`
- Background Gradients: Dark blue (`#12225a` to `#0b1537`)
- Borders: Transparent blue with glow effects
- Text: White with various opacity levels

### Animations
- Card hover: Lift effect (`translateY(-5px)`)
- Border glow: Color transition on hover
- Back button: Slide left on hover
- Loading spinner: Continuous rotation

### Responsive Design
- Container: `.container-xl` (Bootstrap responsive)
- Cards: Full-width with padding
- Mobile-friendly sidebar
- Adaptive text sizes

## Integration with Existing Systems

### Posts System
- Uses `dashboard-posts.js` for post rendering
- Shares all post functionality:
  - Like system
  - Comment system (with likes & replies)
  - Profile interactions
  - Edit/delete capabilities
  - Timestamps

### Profile System
- Profile hover modals work
- Clickable avatars redirect to `view-profile.php`
- User data pulled from `user_info` table

### API Endpoints
- Uses existing `posts.php` API
- `get_posts` action returns all posts
- Client-side filtering by game name

## Key Features

✅ **Dynamic Content**: No hardcoded games
✅ **Real-time Search**: Instant filtering
✅ **Post Counts**: Shows activity level
✅ **Last Activity**: Timestamp of recent post
✅ **Full Interaction**: All post features work
✅ **Profile Integration**: View profiles, hover cards
✅ **Responsive Design**: Works on all devices
✅ **Empty States**: Friendly messages for no content
✅ **Loading States**: User feedback during data fetch
✅ **Navigation**: Easy back/forth between pages

## File Structure
```
user/
├── games.php           (Game selection hub)
├── game-posts.php      (Posts for specific game)
├── dashboard.php       (Home with all posts)
└── view-profile.php    (User profiles)

api/
└── posts.php           (Post data API)

assets/
├── css/
│   └── index.css       (Main styles)
└── js/
    └── dashboard-posts.js  (Post rendering & interactions)
```

## Future Enhancements
- Game cover images/thumbnails
- Sort options (most popular, newest, trending)
- Filter by date range
- Pinned posts per game
- Game moderators
- Subscribe/follow games
- Notifications for favorite games
- Game statistics (avg rating, total users)
- Related games suggestions
