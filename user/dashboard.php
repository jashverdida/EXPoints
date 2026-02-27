<?php
require_once __DIR__ . '/../config/supabase-session.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php?error=' . urlencode('Please login to continue'));
    exit();
}

// Redirect users with special roles to their dashboards
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
        exit();
    }
    // Moderator role has been merged into admin role
}

// Supabase database connection
require_once __DIR__ . '/../includes/db_helper.php';


// Get user info
$username = $_SESSION['username'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

// Get search parameters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchFilter = isset($_GET['filter']) ? $_GET['filter'] : 'title'; // Default: title

// Initialize variables
$posts = [];
$errorMessage = '';
$successMessage = '';
$userProfilePicture = '../assets/img/cat1.jpg'; // Default profile picture

// Check for success/error messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'post_created':
            $successMessage = 'Your review has been posted successfully!';
            break;
        default:
            $successMessage = htmlspecialchars($_GET['success']);
    }
}

if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars(urldecode($_GET['error']));
}

// Get database connection
$db = getDBConnection();

if ($db) {
    try {
        // Fetch CURRENT username from database to ensure it's up-to-date
        if ($userId) {
            $userInfoStmt = $db->prepare("SELECT username, profile_picture FROM user_info WHERE user_id = ?");
            $userInfoStmt->bind_param("i", $userId);
            $userInfoStmt->execute();
            $userInfoResult = $userInfoStmt->get_result();
            
            if ($userInfoData = $userInfoResult->fetch_assoc()) {
                // Update username from database (in case it changed)
                $username = $userInfoData['username'];
                $_SESSION['username'] = $username; // Update session too
                
                // DEBUG LOG
                error_log("DASHBOARD DEBUG: user_id=$userId, fetched username from DB: $username");
                
                // Update profile picture
                if (!empty($userInfoData['profile_picture'])) {
                    $userProfilePicture = $userInfoData['profile_picture'];
                }
            } else {
                error_log("DASHBOARD ERROR: No user_info found for user_id=$userId");
            }
            $userInfoStmt->close();
        } else {
            error_log("DASHBOARD ERROR: No userId in session");
        }
        
        // Create tables if they don't exist
        $db->query("CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game VARCHAR(255) NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            username VARCHAR(100) NOT NULL,
            user_email VARCHAR(255),
            likes INT DEFAULT 0,
            comments INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_created_at (created_at),
            INDEX idx_user_email (user_email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->query("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            username VARCHAR(100) NOT NULL,
            user_email VARCHAR(255),
            user_id INT,
            text TEXT NOT NULL,
            likes INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            INDEX idx_post_id (post_id),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Query posts from Supabase - Load all posts
        $query = "SELECT * FROM posts WHERE hidden = 0 ORDER BY created_at DESC LIMIT 50";
        $result = $db->query($query);
        
        if ($result) {
            $postsData = [];
            
            while ($post = $result->fetch_assoc()) {
                $postsData[] = $post;
            }
            
            // Build maps for batch lookups
            $userInfoMap = [];
            $commentCountMap = [];
            $likeCountMap = [];
            
            // Step 1: Collect all unique usernames
            $uniqueUsernames = array_unique(array_column($postsData, 'username'));
            
            // Step 2: Fetch ALL user info in parallel (no loop)
            foreach ($uniqueUsernames as $postUsername) {
                $userInfoStmt = $db->prepare("SELECT username, profile_picture, exp_points, is_banned FROM user_info WHERE username = ?");
                $userInfoStmt->bind_param("s", $postUsername);
                $userInfoStmt->execute();
                $userInfoResult = $userInfoStmt->get_result();
                
                if ($userInfoResult && $userInfoResult->num_rows > 0) {
                    $userInfo = $userInfoResult->fetch_assoc();
                    $userInfoMap[$postUsername] = $userInfo;
                }
                $userInfoStmt->close();
            }
            
            // Step 3: Get ALL comment counts using COUNT - one query per post (still better than before)
            // Step 4: Get ALL like counts using COUNT - one query per post
            foreach ($postsData as $post) {
                $postId = $post['id'];
                
                // Comment count with COUNT(*)
                $commentStmt = $db->prepare("SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?");
                $commentStmt->bind_param("i", $postId);
                $commentStmt->execute();
                $commentResult = $commentStmt->get_result();
                $commentRow = $commentResult->fetch_assoc();
                $commentCountMap[$postId] = (int)($commentRow['count'] ?? 0);
                $commentStmt->close();
                
                // Like count with COUNT(*)
                $likeStmt = $db->prepare("SELECT COUNT(*) as count FROM post_likes WHERE post_id = ?");
                $likeStmt->bind_param("i", $postId);
                $likeStmt->execute();
                $likeResult = $likeStmt->get_result();
                $likeRow = $likeResult->fetch_assoc();
                $likeCountMap[$postId] = (int)($likeRow['count'] ?? 0);
                $likeStmt->close();
            }
            
            // Step 5: Process all posts using cached data (much faster)
            foreach ($postsData as $post) {
                $userInfo = $userInfoMap[$post['username']] ?? null;
                
                if ($userInfo && $userInfo['is_banned']) {
                    continue; // Skip banned users
                }
                
                $post['author_profile_picture'] = $userInfo['profile_picture'] ?? '../assets/img/cat1.jpg';
                $post['exp_points'] = $userInfo['exp_points'] ?? 0;
                $post['comment_count'] = $commentCountMap[$post['id']] ?? 0;
                $post['like_count'] = $likeCountMap[$post['id']] ?? 0;
                
                // Apply search filter if needed
                if (!empty($searchQuery)) {
                    $matches = false;
                    switch ($searchFilter) {
                        case 'author':
                            $matches = stripos($post['username'], $searchQuery) !== false;
                            break;
                        case 'content':
                            $matches = stripos($post['content'], $searchQuery) !== false;
                            break;
                        case 'title':
                        default:
                            $matches = stripos($post['title'], $searchQuery) !== false;
                            break;
                    }
                    if (!$matches) {
                        continue;
                    }
                }
                
                $post['comments_list'] = [];
                $posts[] = $post;
            }
        } else {
            error_log("Dashboard: Query returned no result object");
        }
    } catch (Exception $e) {
        error_log("Dashboard database error: " . $e->getMessage());
        $errorMessage = "Unable to load posts. Please check your database connection.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <title>EXPoints • Home [<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $username; ?>]</title>

  <!-- Fix CSS paths by removing /EXPoints prefix since we're using localhost:8000 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index.css"> <!-- Fixed path -->
</head>
<body>
  <!-- PlayStation Button Particles Background -->
  <div class="particles-container" id="particlesContainer"></div>
  
  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="dashboard.php" class="lp-brand" aria-label="+EXPoints home">
        <img src="../assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>

      <div class="search">
        <input type="text" id="searchInput" placeholder="Search for a Review, a Game, Anything" autocomplete="off" />
        <input type="hidden" id="searchFilterInput" value="title" />
        <button class="icon" aria-label="Search"><i class="bi bi-search"></i></button>
      </div>

      <div class="right">
        <button class="icon" id="filterButton" title="Filter" type="button"><i class="bi bi-funnel"></i></button>
        <button class="icon" id="notificationButton" title="Notifications" type="button" style="position: relative;">
          <i class="bi bi-bell"></i>
          <span id="notificationBadge" class="notification-badge" style="display: none;">0</span>
        </button>
        <a href="profile.php" class="avatar-nav">
  <img src="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($userProfilePicture); ?>" alt="Profile" class="avatar-img" loading="eager">
</a>
</div>
    </header>
  </div>

  <!-- Filter Dropdown Modal -->
  <div class="filter-dropdown" id="filterDropdown" style="display: none;">
    <div class="filter-dropdown-content">
      <h6 class="filter-dropdown-title"><i class="bi bi-funnel-fill"></i> Search By</h6>
      <div class="filter-options">
        <button class="filter-option active" data-filter="title">
          <i class="bi bi-file-text"></i> Post Title
        </button>
        <button class="filter-option" data-filter="author">
          <i class="bi bi-person"></i> Author
        </button>
        <button class="filter-option" data-filter="content">
          <i class="bi bi-align-left"></i> Content
        </button>
      </div>
    </div>
  </div>

  <!-- Notifications Dropdown -->
  <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
    <div class="notification-dropdown-header">
      <h6><i class="bi bi-bell-fill"></i> Notifications</h6>
      <button class="btn-mark-all-read" id="markAllReadBtn" style="display: none;">
        <i class="bi bi-check-all"></i> Mark all as read
      </button>
    </div>
    <div class="notification-list" id="notificationList">
      <div class="notification-loading">
        <i class="bi bi-hourglass-split"></i> Loading notifications...
      </div>
    </div>
  </div>

  <main class="container-xl py-4">
    <!-- Search Results Info (dynamic) -->
    <div class="search-results-info" id="searchResultsInfo" style="display: none;">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <i class="bi bi-search"></i>
          Showing results for "<strong id="searchQueryDisplay"></strong>" in 
          <span class="badge bg-primary" id="searchFilterDisplay">Title</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary" id="clearSearchBtn">
          <i class="bi bi-x-circle"></i> Clear Search
        </button>
      </div>
      <div class="text-muted mt-2" id="searchResultsCount">
      </div>
    </div>
    
    <!-- Success/Error Messages -->
    <?php
require_once __DIR__ . '/../config/supabase-session.php'; if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($successMessage); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>
    
    <?php
require_once __DIR__ . '/../config/supabase-session.php'; if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($errorMessage); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>

    <!-- Post a Review Section -->
    <section class="card-post-form">
      <div class="row gap-3 align-items-start">
        <div class="col-auto">
          <div class="avatar-us">
            <img src="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($userProfilePicture); ?>" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
          </div>
        </div>
        <div class="col">
          <!-- Simple textbox (initial state) -->
          <div id="simplePostBox" class="simple-post-box">
            <input type="text" id="simplePostInput" class="simple-post-input" placeholder="What's on your mind, @<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($username); ?>?" readonly data-username="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($username); ?>" data-userid="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $userId; ?>">
            <!-- CACHE BUSTER: <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo time(); ?> | Username = <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $username; ?> | User ID = <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $userId; ?> -->
          </div>
          
          <!-- Expanded form (hidden initially) -->
          <div id="expandedPostForm" class="expanded-post-form" style="display: none;">
            <h3 class="form-title mb-3">Post a Review</h3>
            <form id="postForm" class="post-form">
              <input type="hidden" name="action" value="create">
              <div class="form-group mb-3">
                <label for="gameSelect" class="form-label">Select Game</label>
                <select id="gameSelect" name="game" class="form-select" required>
                  <option value="">Choose a game to review...</option>
                  <option value="Elden Ring">Elden Ring</option>
                  <option value="Cyberpunk 2077">Cyberpunk 2077</option>
                  <option value="Baldur's Gate 3">Baldur's Gate 3</option>
                  <option value="Spider-Man 2">Spider-Man 2</option>
                  <option value="The Legend of Zelda: Tears of the Kingdom">The Legend of Zelda: Tears of the Kingdom</option>
                  <option value="Hogwarts Legacy">Hogwarts Legacy</option>
                  <option value="Diablo IV">Diablo IV</option>
                  <option value="Starfield">Starfield</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="form-group mb-3" id="customGameGroup" style="display: none;">
                <label for="customGame" class="form-label">Specify Game Name</label>
                <input type="text" id="customGame" name="custom_game" class="form-input" placeholder="Enter the game name...">
              </div>
              <div class="form-group mb-3">
                <label for="postTitle" class="form-label">Review Title</label>
                <input type="text" id="postTitle" name="title" class="form-input" placeholder="Enter your review title..." required>
              </div>
              <div class="form-group mb-3">
                <label for="postContent" class="form-label">Your Review</label>
                <textarea id="postContent" name="content" class="form-textarea" placeholder="Share your thoughts about the game..." rows="4" required></textarea>
              </div>
              <div class="form-group mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" value="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($username); ?>" readonly>
              </div>
              <input type="hidden" name="email" value="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($_SESSION['user_email']); ?>">
              <div class="form-actions">
                <button type="button" id="cancelPost" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-post">Post Review</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Dynamic Posts Container -->
    <div id="postsContainer">
      <!-- Posts will be loaded here dynamically -->
      <?php
require_once __DIR__ . '/../config/supabase-session.php'; if (count($posts) > 0): ?>
        <?php
require_once __DIR__ . '/../config/supabase-session.php'; foreach ($posts as $post): ?>
          <?php
require_once __DIR__ . '/../config/supabase-session.php'; 
            $isOwnPost = ($post['username'] === $username);
            // Check if user has bookmarked this post
            $isBookmarked = false;
            if ($db) {
              $bookmarkStmt = $db->prepare("SELECT id FROM post_bookmarks WHERE post_id = ? AND user_id = ?");
              $bookmarkStmt->bind_param("ii", $post['id'], $userId);
              $bookmarkStmt->execute();
              $bookmarkResult = $bookmarkStmt->get_result();
              $isBookmarked = ($bookmarkResult->num_rows > 0);
              $bookmarkStmt->close();
            }
          ?>
          <div class="card-post" data-post-id="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $post['id']; ?>" data-is-bookmarked="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $isBookmarked ? 'true' : 'false'; ?>">
            <div class="post-header">
              <div class="row gap-3 align-items-start">
                <div class="col-auto">
                  <div class="avatar-us avatar-loading">
                    <div class="star-loader">⭐</div>
                    <img src="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($post['author_profile_picture'] ?? '../assets/img/cat1.jpg'); ?>" 
                         alt="Profile" 
                         loading="lazy"
                         class="profile-lazy-img"
                         onload="this.classList.add('loaded'); this.parentElement.classList.remove('avatar-loading');">
                  </div>
                </div>
                <div class="col">
                  <div class="game-badge"><?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($post['game']); ?></div>
                  <h2 class="title mb-1"><?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($post['title']); ?></h2>
                  <div class="handle mb-3">@<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($post['username']); ?></div>
                  <p class="mb-3"><?php
require_once __DIR__ . '/../config/supabase-session.php'; echo nl2br(htmlspecialchars($post['content'])); ?></p>
                </div>
              </div>
              <div class="post-menu">
                <?php
require_once __DIR__ . '/../config/supabase-session.php'; if ($isOwnPost): ?>
                  <!-- Show edit/delete menu for own posts -->
                  <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
                  <div class="post-dropdown">
                    <button class="dropdown-item edit-post"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="dropdown-item delete-post"><i class="bi bi-trash"></i> Delete</button>
                  </div>
                <?php
require_once __DIR__ . '/../config/supabase-session.php'; else: ?>
                  <!-- Show bookmark icon for other users' posts -->
                  <button class="icon bookmark-btn <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $isBookmarked ? 'bookmarked' : ''; ?>" aria-label="Bookmark" data-post-id="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $post['id']; ?>">
                    <i class="bi bi-bookmark-fill"></i>
                  </button>
                <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>
              </div>
            </div>
            <div class="actions">
              <span class="a like-btn" data-liked="false"><i class="bi bi-star"></i><b><?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $post['like_count'] ?? 0; ?></b></span>
              <span class="a comment-btn" data-comments="<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $post['comment_count'] ?? 0; ?>"><i class="bi bi-chat-left-text"></i><b><?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $post['comment_count'] ?? 0; ?></b></span>
            </div>
            
            <!-- Comments Section (hidden by default) -->
            <div class="comments-section" style="display: none;">
              <div class="comments-header">
                <h4>Comments</h4>
                <button class="close-comments"><i class="bi bi-x-lg"></i></button>
              </div>
              <div class="add-comment-form">
                <textarea class="comment-input" placeholder="Write a comment..." rows="2"></textarea>
                <button class="btn-submit-comment">Post Comment</button>
              </div>
              <div class="comments-list">
                <!-- Comments will be loaded here -->
                <div class="loading-comments">
                  <i class="bi bi-arrow-repeat spin"></i> Loading comments...
                </div>
              </div>
            </div>
          </div>
        <?php
require_once __DIR__ . '/../config/supabase-session.php'; endforeach; ?>
      <?php
require_once __DIR__ . '/../config/supabase-session.php'; else: ?>
        <div class="no-posts-message">
          <i class="bi bi-inbox"></i>
          <p>No posts to display yet. Be the first to share your review!</p>
          <?php
require_once __DIR__ . '/../config/supabase-session.php'; if ($errorMessage): ?>
            <div style="background: #ff4444; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
              <strong>Error:</strong> <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($errorMessage); ?>
            </div>
          <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>
          <?php
require_once __DIR__ . '/../config/supabase-session.php'; if (!$db): ?>
            <div style="background: #ff9800; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
              <strong>Debug:</strong> Database connection failed. Check your .env file and Supabase credentials.
            </div>
          <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>
          <div style="background: #2196F3; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <strong>Debug Info:</strong>
            <ul style="text-align: left; margin-top: 10px;">
              <li>Posts array count: <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo count($posts); ?></li>
              <li>Database connected: <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo $db ? 'Yes (' . get_class($db) . ')' : 'No'; ?></li>
              <li>Test page: <a href="../test-posts-simple.php" style="color: #fff; text-decoration: underline;">Click here to run diagnostics</a></li>
            </ul>
          </div>
        </div>
      <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>
    </div>
    <!-- End of posts container -->
  </main>

  <!-- Slide-in sidebar (inside the body) -->
  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <button class="side-btn" onclick="window.location.href='dashboard.php'" title="Home"><i class="bi bi-house"></i></button>
        <button class="side-btn" onclick="window.location.href='bookmarks.php'" title="Bookmarks"><i class="bi bi-bookmark"></i></button>
        <button class="side-btn" onclick="window.location.href='games.php'" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn" onclick="window.location.href='popular.php'" title="Popular"><i class="bi bi-compass"></i></button>
        <button class="side-btn" onclick="window.location.href='newest.php'" title="Newest"><i class="bi bi-star-fill"></i></button>
        <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
      </div>
    </div>
  </aside>

  <!-- Profile Hover Modal -->
  <div id="profileHoverModal" class="profile-hover-modal">
    <div class="profile-hover-content">
      <div class="profile-hover-avatar">
        <img id="hoverProfilePic" src="" alt="Profile">
        <div id="hoverProfileLevel" class="hover-level-badge">LVL 1</div>
      </div>
      <div class="profile-hover-info">
        <h4 id="hoverProfileUsername" class="hover-username">Username</h4>
        <div id="hoverProfileExp" class="hover-exp">0 EXP</div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div id="confirmationModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Post Created Successfully!</h4>
      </div>
      <div class="modal-body">
        <p>Your review has been posted and is now visible to other users.</p>
      </div>
      <div class="modal-footer">
        <button id="closeModal" class="btn-modal">Got it!</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Delete Post</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this post? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button id="cancelDelete" class="btn-cancel">Cancel</button>
        <button id="confirmDelete" class="btn-delete">Delete</button>
      </div>
    </div>
  </div>

  <!-- Welcome Modal -->
  <div id="welcomeModal" class="welcome-modal-overlay">
    <div class="welcome-modal-content">
      <div class="welcome-panda-container">
        <img src="../assets/img/Login Panda Controller.png" alt="Welcome Panda" class="welcome-panda-img">
      </div>
      <h1 class="welcome-title">Welcome, <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($username); ?>!</h1>
      <p class="welcome-message">Let's make this space positive and fun. Please share only appropriate and respectful content. Thanks for keeping it chill!</p>
      <button id="welcomeUnderstood" class="welcome-btn">Understood!</button>
    </div>
  </div>

  <script>
    // Logout and Welcome Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.querySelector('.logout-btn-sidebar');
      
      // Welcome Modal functionality
      const welcomeModal = document.getElementById('welcomeModal');
      const welcomeUnderstoodBtn = document.getElementById('welcomeUnderstood');
      let hideTimeout;
      
      // Function to hide modal
      function hideWelcomeModal() {
        clearTimeout(hideTimeout);
        welcomeModal.style.opacity = '0';
        setTimeout(() => {
          welcomeModal.style.display = 'none';
        }, 500);
      }
      
      // Check if user just logged in (using sessionStorage to show only once per session)
      if (!sessionStorage.getItem('welcomeShown')) {
        // Show welcome modal
        welcomeModal.style.display = 'flex';
        sessionStorage.setItem('welcomeShown', 'true');
        
        // Start hide timer (3 seconds)
        hideTimeout = setTimeout(() => {
          hideWelcomeModal();
        }, 3000);
        
        // Reset timer on hover
        welcomeModal.addEventListener('mouseenter', function() {
          clearTimeout(hideTimeout);
        });
        
        // Restart timer when mouse leaves
        welcomeModal.addEventListener('mouseleave', function() {
          hideTimeout = setTimeout(() => {
            hideWelcomeModal();
          }, 3000);
        });
        
        // Close modal when "Understood!" button is clicked
        welcomeUnderstoodBtn.addEventListener('click', function() {
          hideWelcomeModal();
        });
      }
      
      // Handle logout button click
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          // Clear welcome modal flag on logout
          sessionStorage.removeItem('welcomeShown');
          
          // Redirect to landing page
          window.location.href = 'index.php';
        });
      }

      // Post form functionality
      const simplePostBox = document.getElementById('simplePostBox');
      const simplePostInput = document.getElementById('simplePostInput');
      const expandedPostForm = document.getElementById('expandedPostForm');
      const postForm = document.getElementById('postForm');
      const cancelPost = document.getElementById('cancelPost');
      const confirmationModal = document.getElementById('confirmationModal');
      const deleteModal = document.getElementById('deleteModal');
      const closeModal = document.getElementById('closeModal');
      const cancelDelete = document.getElementById('cancelDelete');
      const confirmDelete = document.getElementById('confirmDelete');

      // Expand form when simple input is clicked
      simplePostInput.addEventListener('click', function() {
        simplePostBox.style.display = 'none';
        expandedPostForm.style.display = 'block';
        // Focus on the title input
        document.getElementById('postTitle').focus();
      });

      // Handle form submission via API
      postForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const gameSelect = document.getElementById('gameSelect');
        const customGameInput = document.getElementById('customGame');
        const postTitle = document.getElementById('postTitle').value;
        const postContent = document.getElementById('postContent').value;
        
        // Determine final game value
        let game = gameSelect.value === 'Other' ? customGameInput.value : gameSelect.value;
        
        if (!game || !postTitle || !postContent) {
          alert('Please fill in all fields');
          return;
        }
        
        try {
          const response = await fetch('../api/posts.php?action=create', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              game: game,
              title: postTitle,
              content: postContent
            })
          });
          
          const data = await response.json();
          
          if (data.success) {
            // Reset form and collapse
            postForm.reset();
            expandedPostForm.style.display = 'none';
            simplePostBox.style.display = 'block';
            
            // Reload page to show new post
            window.location.href = 'dashboard.php?success=post_created';
          } else {
            alert('Error creating post: ' + (data.error || 'Unknown error'));
          }
        } catch (error) {
          console.error('Error:', error);
          alert('Failed to create post. Please try again.');
        }
      });

      // Handle cancel button
      cancelPost.addEventListener('click', function() {
        postForm.reset();
        // Collapse back to simple textbox
        expandedPostForm.style.display = 'none';
        simplePostBox.style.display = 'block';
      });

      // Close confirmation modal
      closeModal.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
      });

      // Close modal when clicking outside
      confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
          confirmationModal.style.display = 'none';
        }
      });

      // Delete modal functionality
      cancelDelete.addEventListener('click', function() {
        deleteModal.style.display = 'none';
      });

      deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
          deleteModal.style.display = 'none';
        }
      });

      // Function to create new post
      function createNewPost(game, title, content) {
        const main = document.querySelector('main.container-xl');
        const existingPosts = document.querySelector('.card-post');
        
        const newPost = document.createElement('article');
        newPost.className = 'card-post user-post';
        newPost.innerHTML = `
          <div class="post-header">
            <div class="row gap-3 align-items-start">
              <div class="col-auto"><div class="avatar-us"></div></div>
              <div class="col">
                <div class="game-badge">${getGameDisplayName(game)}</div>
                <h2 class="title mb-1">${title}</h2>
                <div class="handle mb-3">@YourUsername</div>
                <p class="mb-3">${content}</p>
              </div>
            </div>
            <div class="post-menu">
              <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
              <div class="post-dropdown">
                <button class="dropdown-item edit-post"><i class="bi bi-pencil"></i> Edit</button>
                <button class="dropdown-item delete-post"><i class="bi bi-trash"></i> Delete</button>
              </div>
            </div>
          </div>
          <div class="actions">
            <span class="a like-btn" data-liked="false"><i class="bi bi-star"></i><b>0</b></span>
            <span class="a comment-btn" data-comments="0"><i class="bi bi-chat-left-text"></i><b>0</b></span>
          </div>
          <div class="comments-section" style="display: none;">
            <div class="comments-list"></div>
            <div class="comment-input-section">
              <div class="row g-3 align-items-center">
                <div class="col-auto"><div class="avatar-sm"></div></div>
                <div class="col">
                  <input class="comment-input" placeholder="Write a Comment on this post!" />
                </div>
                <div class="col-auto">
                  <button class="btn-comment">Post</button>
                </div>
              </div>
            </div>
          </div>
        `;
        
        // Insert new post before existing posts
        main.insertBefore(newPost, existingPosts);
        
        // Add event listeners to new post
        addPostEventListeners(newPost);
      }

      // Function to get display name for game
      function getGameDisplayName(gameValue) {
        const gameNames = {
          'elden-ring': 'Elden Ring',
          'cyberpunk-2077': 'Cyberpunk 2077',
          'baldurs-gate-3': 'Baldur\'s Gate 3',
          'spider-man-2': 'Spider-Man 2',
          'zelda-totk': 'The Legend of Zelda: Tears of the Kingdom',
          'hogwarts-legacy': 'Hogwarts Legacy',
          'diablo-4': 'Diablo IV',
          'starfield': 'Starfield',
          'other': 'Other'
        };
        return gameNames[gameValue] || gameValue;
      }

      // Function to add event listeners to posts
      function addPostEventListeners(postElement) {
        const moreBtn = postElement.querySelector('.more');
        const dropdown = postElement.querySelector('.post-dropdown');
        const editBtn = postElement.querySelector('.edit-post');
        const deleteBtn = postElement.querySelector('.delete-post');
        const likeBtn = postElement.querySelector('.like-btn');
        const commentBtn = postElement.querySelector('.comment-btn');
        const commentsSection = postElement.querySelector('.comments-section');
        const commentInput = postElement.querySelector('.comment-input');
        const postCommentBtn = postElement.querySelector('.btn-comment');

        // Toggle dropdown
        moreBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          // Close all other dropdowns first
          document.querySelectorAll('.post-dropdown.show').forEach(dd => {
            if (dd !== dropdown) dd.classList.remove('show');
          });
          dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.post-menu')) {
            dropdown.classList.remove('show');
          }
        });

        // Like functionality
        if (likeBtn) {
          likeBtn.addEventListener('click', function() {
            const isLiked = likeBtn.getAttribute('data-liked') === 'true';
            const countElement = likeBtn.querySelector('b');
            const iconElement = likeBtn.querySelector('i');
            let currentCount = parseInt(countElement.textContent);
            
            if (isLiked) {
              // Unlike
              likeBtn.setAttribute('data-liked', 'false');
              countElement.textContent = currentCount - 1;
              iconElement.className = 'bi bi-star';
            } else {
              // Like
              likeBtn.setAttribute('data-liked', 'true');
              countElement.textContent = currentCount + 1;
              iconElement.className = 'bi bi-star-fill';
            }
          });
        }

        // Comment toggle functionality
        if (commentBtn && commentsSection) {
          commentBtn.addEventListener('click', function() {
            const isVisible = commentsSection.style.display !== 'none';
            if (isVisible) {
              commentsSection.style.display = 'none';
            } else {
              commentsSection.style.display = 'block';
              // Load comments when opening
              loadComments(postElement);
            }
          });
        }

        // Close comments button
        const closeCommentsBtn = postElement.querySelector('.close-comments');
        if (closeCommentsBtn) {
          closeCommentsBtn.addEventListener('click', function() {
            commentsSection.style.display = 'none';
          });
        }

        // Post comment functionality
        const submitCommentBtn = postElement.querySelector('.btn-submit-comment');
        if (submitCommentBtn && commentInput) {
          submitCommentBtn.addEventListener('click', function() {
            const commentText = commentInput.value.trim();
            if (commentText) {
              const postId = postElement.getAttribute('data-post-id');
              submitComment(postId, commentText, postElement);
            }
          });

          // Allow posting comment with Enter key (Ctrl+Enter for new line)
          commentInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey) {
              e.preventDefault();
              submitCommentBtn.click();
            }
          });
        }

        // Bookmark functionality
        const bookmarkBtn = postElement.querySelector('.bookmark-btn');
        if (bookmarkBtn) {
          bookmarkBtn.addEventListener('click', function() {
            const postId = postElement.getAttribute('data-post-id');
            toggleBookmark(postId, postElement, bookmarkBtn);
          });
        }
      }

      // Load comments for a post
      async function loadComments(postElement) {
        const postId = postElement.getAttribute('data-post-id');
        const commentsList = postElement.querySelector('.comments-list');
        
        commentsList.innerHTML = '<div class="loading-comments"><i class="bi bi-arrow-repeat spin"></i> Loading comments...</div>';
        
        try {
          const response = await fetch(`../api/posts.php?action=get_comments&post_id=${postId}`);
          const data = await response.json();
          
          if (data.success && data.comments) {
            displayComments(data.comments, commentsList, postElement);
          } else {
            commentsList.innerHTML = '<p style="color: rgba(255,255,255,0.5); text-align: center;">No comments yet. Be the first!</p>';
          }
        } catch (error) {
          console.error('Error loading comments:', error);
          commentsList.innerHTML = '<p style="color: #ff4444; text-align: center;">Failed to load comments</p>';
        }
      }

      // Display comments
      function displayComments(comments, commentsList, postElement) {
        if (comments.length === 0) {
          commentsList.innerHTML = '<p style="color: rgba(255,255,255,0.5); text-align: center;">No comments yet. Be the first!</p>';
          return;
        }
        
        commentsList.innerHTML = '';
        comments.forEach(comment => {
          const commentDiv = document.createElement('div');
          commentDiv.className = 'comment-item';
          commentDiv.setAttribute('data-comment-id', comment.id);
          
          const isOwnComment = (comment.user_id == currentUserId);
          
          commentDiv.innerHTML = `
            <div class="comment-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <img src="${comment.profile_picture || '../assets/img/cat1.jpg'}" alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                <span class="comment-author">@${escapeHtml(comment.username)}</span>
                <span style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">${timeAgo(comment.created_at)}</span>
              </div>
              ${isOwnComment ? `
                <div class="comment-menu">
                  <button class="icon comment-more" style="padding: 0.25rem 0.5rem;"><i class="bi bi-three-dots-vertical"></i></button>
                  <div class="comment-dropdown" style="display: none;">
                    <button class="dropdown-item edit-comment"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="dropdown-item delete-comment"><i class="bi bi-trash"></i> Delete</button>
                  </div>
                </div>
              ` : ''}
            </div>
            <p class="comment-text">${escapeHtml(comment.comment_text)}</p>
            <div class="comment-actions" style="display: flex; gap: 1rem; margin-top: 0.5rem;">
              <button class="comment-like-btn" data-liked="${comment.user_liked ? 'true' : 'false'}" style="background: none; border: none; color: rgba(255,255,255,0.6); cursor: pointer; display: flex; align-items: center; gap: 0.25rem;">
                <i class="bi bi-star${comment.user_liked ? '-fill' : ''}" style="color: ${comment.user_liked ? '#38a0ff' : 'inherit'}"></i>
                <span>${comment.like_count || 0}</span>
              </button>
            </div>
          `;
          
          commentsList.appendChild(commentDiv);
          
          // Add event listeners for comment actions
          if (isOwnComment) {
            setupCommentActions(commentDiv, postElement);
          }
          
          // Add like functionality
          const likeBtn = commentDiv.querySelector('.comment-like-btn');
          likeBtn.addEventListener('click', function() {
            toggleCommentLike(comment.id, likeBtn);
          });
        });
      }

      // Setup comment edit/delete actions
      function setupCommentActions(commentDiv, postElement) {
        const moreBtn = commentDiv.querySelector('.comment-more');
        const dropdown = commentDiv.querySelector('.comment-dropdown');
        const editBtn = commentDiv.querySelector('.edit-comment');
        const deleteBtn = commentDiv.querySelector('.delete-comment');
        
        if (moreBtn && dropdown) {
          moreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
          });
          
          document.addEventListener('click', function(e) {
            if (!e.target.closest('.comment-menu')) {
              dropdown.style.display = 'none';
            }
          });
        }
        
        if (editBtn) {
          editBtn.addEventListener('click', function() {
            const commentText = commentDiv.querySelector('.comment-text');
            const currentText = commentText.textContent;
            const commentId = commentDiv.getAttribute('data-comment-id');
            
            commentText.innerHTML = `
              <textarea class="comment-edit-input" style="width: 100%; padding: 0.5rem; background: rgba(15,30,90,0.5); border: 1px solid rgba(194,213,255,0.2); border-radius: 0.5rem; color: white;">${currentText}</textarea>
              <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <button class="save-comment-edit" style="padding: 0.5rem 1rem; background: #38a0ff; border: none; border-radius: 0.5rem; color: white; cursor: pointer;">Save</button>
                <button class="cancel-comment-edit" style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); border-radius: 0.5rem; color: white; cursor: pointer;">Cancel</button>
              </div>
            `;
            
            const saveBtn = commentText.querySelector('.save-comment-edit');
            const cancelBtn = commentText.querySelector('.cancel-comment-edit');
            const editInput = commentText.querySelector('.comment-edit-input');
            
            saveBtn.addEventListener('click', async function() {
              const newText = editInput.value.trim();
              if (newText) {
                try {
                  const response = await fetch('../api/posts.php?action=edit_comment', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({comment_id: commentId, comment_text: newText})
                  });
                  const data = await response.json();
                  if (data.success) {
                    commentText.textContent = newText;
                  }
                } catch (error) {
                  console.error('Error editing comment:', error);
                }
              }
            });
            
            cancelBtn.addEventListener('click', function() {
              commentText.textContent = currentText;
            });
            
            dropdown.style.display = 'none';
          });
        }
        
        if (deleteBtn) {
          deleteBtn.addEventListener('click', async function() {
            if (confirm('Delete this comment?')) {
              const commentId = commentDiv.getAttribute('data-comment-id');
              try {
                const response = await fetch('../api/posts.php?action=delete_comment', {
                  method: 'POST',
                  headers: {'Content-Type': 'application/json'},
                  body: JSON.stringify({comment_id: commentId})
                });
                const data = await response.json();
                if (data.success) {
                  commentDiv.remove();
                  // Update comment count
                  const commentBtn = postElement.querySelector('.comment-btn');
                  const countElement = commentBtn.querySelector('b');
                  const currentCount = parseInt(countElement.textContent);
                  countElement.textContent = Math.max(0, currentCount - 1);
                }
              } catch (error) {
                console.error('Error deleting comment:', error);
              }
            }
            dropdown.style.display = 'none';
          });
        }
      }

      // Submit comment
      async function submitComment(postId, commentText, postElement) {
        const commentInput = postElement.querySelector('.comment-input');
        const commentsList = postElement.querySelector('.comments-list');
        
        try {
          const response = await fetch('../api/posts.php?action=add_comment', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({post_id: postId, comment_text: commentText})
          });
          const data = await response.json();
          
          if (data.success) {
            commentInput.value = '';
            // Reload comments
            loadComments(postElement);
            // Update comment count
            const commentBtn = postElement.querySelector('.comment-btn');
            const countElement = commentBtn.querySelector('b');
            const currentCount = parseInt(countElement.textContent);
            countElement.textContent = currentCount + 1;
          }
        } catch (error) {
          console.error('Error posting comment:', error);
          alert('Failed to post comment');
        }
      }

      // Toggle comment like
      async function toggleCommentLike(commentId, likeBtn) {
        try {
          const response = await fetch('../api/posts.php?action=like_comment', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({comment_id: commentId})
          });
          const data = await response.json();
          
          if (data.success) {
            const isLiked = likeBtn.getAttribute('data-liked') === 'true';
            const icon = likeBtn.querySelector('i');
            const count = likeBtn.querySelector('span');
            const currentCount = parseInt(count.textContent);
            
            if (isLiked) {
              likeBtn.setAttribute('data-liked', 'false');
              icon.className = 'bi bi-star';
              icon.style.color = 'inherit';
              count.textContent = Math.max(0, currentCount - 1);
            } else {
              likeBtn.setAttribute('data-liked', 'true');
              icon.className = 'bi bi-star-fill';
              icon.style.color = '#38a0ff';
              count.textContent = currentCount + 1;
            }
          }
        } catch (error) {
          console.error('Error toggling comment like:', error);
        }
      }

      // Toggle bookmark
      async function toggleBookmark(postId, postElement, bookmarkBtn) {
        const isBookmarked = postElement.getAttribute('data-is-bookmarked') === 'true';
        
        try {
          const response = await fetch('../api/posts.php?action=bookmark', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({post_id: postId})
          });
          const data = await response.json();
          
          if (data.success) {
            if (isBookmarked) {
              bookmarkBtn.classList.remove('bookmarked');
              postElement.setAttribute('data-is-bookmarked', 'false');
            } else {
              bookmarkBtn.classList.add('bookmarked');
              postElement.setAttribute('data-is-bookmarked', 'true');
              // Show brief success feedback
              showToast('Post bookmarked!');
            }
          }
        } catch (error) {
          console.error('Error toggling bookmark:', error);
          alert('Failed to update bookmark');
        }
      }

      // Helper function to show toast notification
      function showToast(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = 'position: fixed; bottom: 2rem; right: 2rem; background: #38a0ff; color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 10000; animation: slideIn 0.3s ease;';
        document.body.appendChild(toast);
        setTimeout(() => {
          toast.style.animation = 'slideOut 0.3s ease';
          setTimeout(() => toast.remove(), 300);
        }, 2000);
      }

      // Helper functions
      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      function timeAgo(dateString) {
        const now = new Date();
        const past = new Date(dateString);
        const seconds = Math.floor((now - past) / 1000);
        
        if (seconds < 60) return 'Just now';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + 'm';
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + 'h';
        const days = Math.floor(hours / 24);
        if (days === 1) return 'Yesterday';
        if (days < 7) return days + 'd';
        const weeks = Math.floor(days / 7);
        if (weeks < 4) return weeks + 'w';
        
        const options = { month: 'short', day: 'numeric' };
        if (past.getFullYear() !== now.getFullYear()) {
          options.year = 'numeric';
        }
        return past.toLocaleDateString('en-US', options);
      }

      // Initialize all existing posts
      document.querySelectorAll('.card-post').forEach(attachPostEventListeners);

        // Edit post functionality
        if (editBtn) {
          editBtn.addEventListener('click', function() {
            dropdown.classList.remove('show');
            const postId = editBtn.getAttribute('data-post-id');
            editPost(postElement, postId);
          });
        }

        // Delete post functionality
        if (deleteBtn) {
          deleteBtn.addEventListener('click', function() {
            dropdown.classList.remove('show');
            const postId = deleteBtn.getAttribute('data-post-id');
            deleteModal.style.display = 'flex';
            
            confirmDelete.onclick = function() {
              deletePost(postId);
              postElement.remove();
              deleteModal.style.display = 'none';
            };
          });
        }
      }

      // Function to edit post
      function editPost(postElement, postId) {
        const titleElement = postElement.querySelector('.title');
        const contentElement = postElement.querySelector('p');
        
        const currentTitle = titleElement.textContent;
        const currentContent = contentElement.textContent;
        
        // Create inline editing form
        const editForm = document.createElement('div');
        editForm.className = 'edit-form';
        editForm.innerHTML = `
          <div class="edit-form-container">
            <h4 class="edit-form-title">Edit Post</h4>
            <div class="form-group">
              <label class="form-label">Title</label>
              <input type="text" class="form-input edit-title-input" value="${currentTitle}" />
            </div>
            <div class="form-group">
              <label class="form-label">Content</label>
              <textarea class="form-textarea edit-content-input" rows="4">${currentContent}</textarea>
            </div>
            <div class="edit-form-actions">
              <button class="btn-cancel-edit">Cancel</button>
              <button class="btn-save-edit">Save Changes</button>
            </div>
          </div>
        `;
        
        // Replace content with edit form
        const postContent = postElement.querySelector('.col');
        const originalContent = postContent.innerHTML;
        postContent.innerHTML = '';
        postContent.appendChild(editForm);
        
        // Add event listeners
        const cancelBtn = editForm.querySelector('.btn-cancel-edit');
        const saveBtn = editForm.querySelector('.btn-save-edit');
        const titleInput = editForm.querySelector('.edit-title-input');
        const contentInput = editForm.querySelector('.edit-content-input');
        
        cancelBtn.addEventListener('click', function() {
          postContent.innerHTML = originalContent;
        });
        
        saveBtn.addEventListener('click', function() {
          const newTitle = titleInput.value.trim();
          const newContent = contentInput.value.trim();
          
          if (newTitle && newContent) {
            // Send PUT request to update post
            updatePost(postId, newTitle, newContent, function(success) {
              if (success) {
                // Force page reload to show updated content
                window.location.reload();
              }
            });
          } else {
            alert('Please fill in all fields');
          }
        });
      }

      // Function to update post via PUT request
      function updatePost(postId, title, content, callback) {
        // Send data as URL-encoded string instead of FormData
        const data = `action=update&id=${encodeURIComponent(postId)}&title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`;

        console.log('Sending PUT request with:', {
          postId: postId,
          title: title,
          content: content
        });

        fetch('posts.php', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: data
        })
        .then(response => {
          console.log('Response status:', response.status);
          return response.json();
        })
        .then(data => {
          console.log('Response data:', data);
          if (data.success) {
            console.log('Post updated successfully');
            if (callback) callback(true);
          } else {
            alert('Error updating post: ' + data.message);
            if (callback) callback(false);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error updating post: ' + error.message);
          if (callback) callback(false);
        });
      }

      // Function to delete post via DELETE request
      function deletePost(postId) {
        const data = `action=delete&id=${encodeURIComponent(postId)}`;

        fetch('posts.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: data
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Post deleted successfully');
          } else {
            alert('Error deleting post: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error deleting post');
        });
      }

      // Function to save comment to backend
      function saveComment(postId, commentText, callback) {
        const data = `action=add_comment&post_id=${encodeURIComponent(postId)}&comment_text=${encodeURIComponent(commentText)}&username=YourUsername`;

        fetch('posts.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: data
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Comment saved successfully');
            if (callback) callback(true);
          } else {
            alert('Error saving comment: ' + data.message);
            if (callback) callback(false);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error saving comment');
          if (callback) callback(false);
        });
      }

      // Function to add comment
      function addComment(postElement, commentText) {
        const commentsList = postElement.querySelector('.comments-list');
        const commentElement = document.createElement('div');
        commentElement.className = 'comment-item';
        commentElement.innerHTML = `
          <div class="row g-3 align-items-center">
            <div class="col-auto"><div class="avatar-sm"></div></div>
            <div class="col">
              <div class="comment-author">@YourUsername</div>
              <div class="comment-text">${commentText}</div>
            </div>
            <div class="col-auto">
              <div class="comment-actions">
                <button class="btn-edit-comment" title="Edit Comment"><i class="bi bi-pencil"></i></button>
                <button class="btn-delete-comment" title="Delete Comment"><i class="bi bi-trash"></i></button>
              </div>
            </div>
          </div>
        `;
        commentsList.appendChild(commentElement);
        
        // Add event listeners for comment actions
        addCommentEventListeners(commentElement);
      }

      // Function to add event listeners to comments
      function addCommentEventListeners(commentElement) {
        const editBtn = commentElement.querySelector('.btn-edit-comment');
        const deleteBtn = commentElement.querySelector('.btn-delete-comment');
        const commentText = commentElement.querySelector('.comment-text');

        // Edit comment functionality
        if (editBtn) {
          editBtn.addEventListener('click', function() {
            const currentText = commentText.textContent;
            
            // Create inline editing form for comment
            const editForm = document.createElement('div');
            editForm.className = 'comment-edit-form';
            editForm.innerHTML = `
              <div class="comment-edit-container">
                <textarea class="form-textarea comment-edit-input" rows="2">${currentText}</textarea>
                <div class="comment-edit-actions">
                  <button class="btn-cancel-comment-edit">Cancel</button>
                  <button class="btn-save-comment-edit">Save</button>
                </div>
              </div>
            `;
            
            // Replace comment text with edit form
            const commentContainer = commentText.parentElement;
            const originalContent = commentContainer.innerHTML;
            commentContainer.innerHTML = '';
            commentContainer.appendChild(editForm);
            
            // Add event listeners
            const cancelBtn = editForm.querySelector('.btn-cancel-comment-edit');
            const saveBtn = editForm.querySelector('.btn-save-comment-edit');
            const textInput = editForm.querySelector('.comment-edit-input');
            
            cancelBtn.addEventListener('click', function() {
              commentContainer.innerHTML = originalContent;
            });
            
            saveBtn.addEventListener('click', function() {
              const newText = textInput.value.trim();
              if (newText) {
                // Update the comment text
                commentText.textContent = newText;
                
                // Restore the original content with updated text
                commentContainer.innerHTML = originalContent;
                
                // Update the comment text in the restored content
                const updatedCommentText = commentContainer.querySelector('.comment-text');
                if (updatedCommentText) {
                  updatedCommentText.textContent = newText;
                }
                
                // Re-add event listeners to the restored content
                addCommentEventListeners(commentContainer.closest('.comment-item'));
              } else {
                alert('Comment cannot be empty');
              }
            });
          });
        }

        // Delete comment functionality
        if (deleteBtn) {
          deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this comment?')) {
              commentElement.remove();
              
              // Update comment count
              const postElement = commentElement.closest('.card-post');
              const commentBtn = postElement.querySelector('.comment-btn');
              const countElement = commentBtn.querySelector('b');
              const currentCount = parseInt(countElement.textContent);
              countElement.textContent = Math.max(0, currentCount - 1);
              commentBtn.setAttribute('data-comments', Math.max(0, currentCount - 1));
            }
          });
        }
      }

      // Add event listeners to existing posts
      document.querySelectorAll('.card-post').forEach(addPostEventListeners);
      
      // Add event listeners to all existing comment buttons
      document.querySelectorAll('.comment-item').forEach(function(commentElement) {
        addCommentEventListeners(commentElement);
      });

      // Handle comment star-ups
      document.querySelectorAll('.comment-like-btn').forEach(function(button) {
        button.addEventListener('click', function() {
          const commentId = this.getAttribute('data-comment-id');
          const isLiked = this.getAttribute('data-liked') === 'true';
          const likeCount = this.querySelector('.like-count');
          const starIcon = this.querySelector('i');
          
          fetch('posts.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=like_comment&comment_id=${commentId}&unlike=${isLiked}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const currentLikes = parseInt(likeCount.textContent);
              if (isLiked) {
                likeCount.textContent = currentLikes - 1;
                this.setAttribute('data-liked', 'false');
                starIcon.classList.remove('bi-star-fill');
                starIcon.classList.add('bi-star');
              } else {
                likeCount.textContent = currentLikes + 1;
                this.setAttribute('data-liked', 'true');
                starIcon.classList.remove('bi-star');
                starIcon.classList.add('bi-star-fill');
              }
            }
          });
        });
      });
    });
  </script>

  <style>
    /* Search animations and transitions */
    .card-post {
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
    
    /* Profile picture styling for posts */
    .avatar-us img {
      position: absolute;
      top: 2px;
      left: 2px;
      right: 2px;
      bottom: 2px;
      width: calc(100% - 4px);
      height: calc(100% - 4px);
      object-fit: cover;
      border-radius: 50%;
      z-index: 3;
    }
    
    /* Override the default ::after background */
    .card-post .avatar-us::after {
      display: none;
    }
    
    /* Custom game input transition */
    #customGameGroup {
      transition: all 0.3s ease;
      overflow: hidden;
      max-height: 0;
      opacity: 0;
    }
    
    #customGameGroup[style*="display: block"] {
      max-height: 100px;
      opacity: 1;
    }
    
    /* Profile Hover Modal */
    .profile-hover-modal {
      position: fixed;
      display: none;
      z-index: 10000;
      pointer-events: none;
    }
    
    .profile-hover-content {
      background: linear-gradient(135deg, rgba(18, 34, 90, 0.98) 0%, rgba(11, 21, 55, 0.98) 100%);
      border: 2px solid rgba(56, 160, 255, 0.4);
      border-radius: 1rem;
      padding: 1.25rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5), 0 0 20px rgba(56, 160, 255, 0.2);
      backdrop-filter: blur(20px);
      min-width: 280px;
      animation: slideInUp 0.2s ease-out;
    }
    
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .profile-hover-avatar {
      position: relative;
      width: 80px;
      height: 80px;
      margin: 0 auto 1rem;
    }
    
    .profile-hover-avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(56, 160, 255, 0.5);
      box-shadow: 0 0 20px rgba(56, 160, 255, 0.3);
    }
    
    .hover-level-badge {
      position: absolute;
      bottom: -5px;
      right: -5px;
      background: linear-gradient(135deg, #38a0ff, #1b378d);
      color: white;
      font-size: 0.75rem;
      font-weight: 700;
      padding: 0.25rem 0.5rem;
      border-radius: 1rem;
      border: 2px solid rgba(11, 21, 55, 0.9);
      box-shadow: 0 2px 10px rgba(56, 160, 255, 0.4);
    }
    
    .profile-hover-info {
      text-align: center;
    }
    
    .hover-username {
      color: #fff;
      font-size: 1.1rem;
      font-weight: 600;
      margin: 0 0 0.5rem 0;
    }
    
    .hover-exp {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.25rem;
    }
    
    /* Make profile pictures clickable */
    .user-profile-avatar {
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .user-profile-avatar:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(56, 160, 255, 0.5);
    }
    
    .comment-footer {
      margin-top: 0.5rem;
    }
    
    .comment-like-btn {
      cursor: pointer;
      color: #fff;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.25rem 0.5rem;
      border-radius: 1rem;
      font-size: 0.875rem;
      background: transparent;
      border: none;
      transition: all 0.2s ease;
    }
    
    .comment-like-btn:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    
    .comment-like-btn[data-liked="true"] i {
      color: #ffd700;
    }
    
    .comment-like-btn i {
      transition: color 0.2s ease;
    }
    
    .comment-menu {
      position: relative;
    }
    
    .comment-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: rgba(25, 35, 75, 0.95);
      border: 1px solid rgba(194, 213, 255, 0.2);
      border-radius: 0.5rem;
      min-width: 120px;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      margin-top: 0.25rem;
    }
    
    .comment-dropdown .dropdown-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0.75rem;
      background: transparent;
      border: none;
      color: white;
      width: 100%;
      text-align: left;
      cursor: pointer;
      transition: background 0.2s;
      font-size: 0.875rem;
    }
    
    .comment-dropdown .dropdown-item:hover {
      background: rgba(56, 160, 255, 0.2);
    }
    
    .comments-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .comments-header h4 {
      color: #fff;
      margin: 0;
      font-size: 1.2rem;
    }
    
    .close-comments {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .close-comments:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: scale(1.1);
    }
    
    .add-comment-form {
      margin-bottom: 1.5rem;
    }
    
    .btn-submit-comment {
      padding: 0.5rem 1.5rem;
      background: #38a0ff;
      border: none;
      border-radius: 0.5rem;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 0.5rem;
    }
    
    .btn-submit-comment:hover {
      background: #2c8de0;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(56, 160, 255, 0.4);
    }
    
    .loading-comments {
      text-align: center;
      padding: 2rem;
      color: rgba(255, 255, 255, 0.6);
    }
    
    .spin {
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .liked .bi-star {
      display: none;
    }
    
    .liked .bi-star-fill {
      color: #ffd700 !important;
    }
    
    .post-menu {
      position: relative;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .bookmark-btn {
      position: relative;
      z-index: 10;
      transition: all 0.3s ease;
    }
    
    .bookmark-btn i {
      transition: all 0.3s ease;
    }
    
    .bookmark-btn:hover {
      background: rgba(56, 160, 255, 0.2);
      transform: scale(1.1);
    }
    
    .bookmark-btn.bookmarked i {
      color: #38a0ff;
    }
    
    .bookmark-btn:not(.bookmarked):hover i {
      color: #38a0ff;
    }
    
    .post-dropdown {
      display: none;
      position: absolute;
      top: 100%;
      right: 0;
      background: rgba(25, 35, 75, 0.95);
      border: 1px solid rgba(194, 213, 255, 0.2);
      border-radius: 0.5rem;
      min-width: 150px;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      margin-top: 0.25rem;
    }
    
    .post-dropdown.show {
      display: block;
    }
    
    .post-dropdown .dropdown-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1rem;
      background: transparent;
      border: none;
      color: white;
      width: 100%;
      text-align: left;
      cursor: pointer;
      transition: background 0.2s;
    }
    
    .post-dropdown .dropdown-item:hover {
      background: rgba(56, 160, 255, 0.2);
    }
    
    .post-dropdown .dropdown-item i {
      font-size: 0.875rem;
    }
    
    .edit-form {
      padding: 1rem;
      background: rgba(15, 30, 90, 0.3);
      border-radius: 0.5rem;
    }
    
    .form-actions {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-end;
    }
    
    /* Custom Modal Styles */
    .custom-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      animation: fadeIn 0.2s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .custom-modal-content {
      background: linear-gradient(135deg, #08122e 0%, #0c1f6f 100%);
      border: 2px solid rgba(56, 160, 255, 0.3);
      border-radius: 1rem;
      padding: 2rem;
      max-width: 400px;
      width: 90%;
      text-align: center;
      animation: slideDown 0.3s ease;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    }
    
    @keyframes slideDown {
      from { 
        transform: translateY(-50px);
        opacity: 0;
      }
      to { 
        transform: translateY(0);
        opacity: 1;
      }
    }
    
    .modal-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
    }
    
    .modal-icon i {
      color: #38a0ff;
    }
    
    .modal-icon.warning i {
      color: #ffc107;
    }
    
    .success-modal .modal-icon i {
      color: #28a745;
    }
    
    .custom-modal-content h3 {
      color: white;
      font-size: 1.25rem;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }
    
    .modal-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
    }
    
    .modal-btn {
      padding: 0.75rem 2rem;
      border: none;
      border-radius: 0.5rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 1rem;
    }
    
    .modal-btn.btn-cancel {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .modal-btn.btn-cancel:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    
    .modal-btn.btn-confirm {
      background: #38a0ff;
      color: white;
    }
    
    .modal-btn.btn-confirm:hover {
      background: #2c8de0;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(56, 160, 255, 0.4);
    }
    
    .success-modal .modal-btn {
      background: #28a745;
      color: white;
      padding: 0.75rem 3rem;
    }
    
    .success-modal .modal-btn:hover {
      background: #218838;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    }
    
    /* Comments section styling */
    .comments-section {
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(194, 213, 255, 0.2);
    }
    
    .comments-list {
      margin-bottom: 1rem;
    }
    
    .comment-item {
      padding: 1rem;
      background: rgba(15, 30, 90, 0.3);
      border-radius: 0.75rem;
      margin-bottom: 0.75rem;
    }
    
    .comment-author {
      color: var(--brand);
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .comment-text {
      color: #fff;
      line-height: 1.5;
    }
    
    .comment-input-container {
      display: flex;
      gap: 0.75rem;
      align-items: center;
      margin-bottom: 4rem; /* Add space to prevent overlap with like/comment buttons */
      padding-top: 1rem;
    }
    
    .comment-input {
      flex: 1;
      padding: 0.75rem 1rem;
      background: rgba(15, 30, 90, 0.5);
      border: 1px solid rgba(194, 213, 255, 0.2);
      border-radius: 0.5rem;
      color: white;
      font-size: 0.95rem;
    }
    
    .comment-input:focus {
      outline: none;
      border-color: var(--brand);
    }
    
    .comment-input::placeholder {
      color: var(--muted);
    }
    
    .comment-submit-btn {
      padding: 0.75rem 1.5rem;
      background: var(--brand);
      color: white;
      border: none;
      border-radius: 0.5rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .comment-submit-btn:hover {
      background: #2c8de0;
      transform: translateY(-2px);
    }
    
    /* Welcome Modal Styling */
    .welcome-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 1;
      transition: opacity 0.5s ease;
      backdrop-filter: blur(8px);
    }
    
    .welcome-modal-content {
      background: linear-gradient(135deg, #0f1e5a 0%, #1a2f7a 50%, #0c1f6f 100%);
      border: 2px solid rgba(56, 160, 255, 0.4);
      border-radius: 1.5rem;
      padding: 3rem 2.5rem;
      max-width: 600px;
      width: 90%;
      text-align: center;
      animation: welcomeSlideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(56, 160, 255, 0.2);
      position: relative;
      overflow: hidden;
    }
    
    .welcome-modal-content::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(56, 160, 255, 0.1) 0%, transparent 70%);
      animation: welcomeGlow 3s ease-in-out infinite;
    }
    
    @keyframes welcomeSlideIn {
      from {
        transform: translateY(-100px) scale(0.8);
        opacity: 0;
      }
      to {
        transform: translateY(0) scale(1);
        opacity: 1;
      }
    }
    
    @keyframes welcomeGlow {
      0%, 100% {
        opacity: 0.3;
      }
      50% {
        opacity: 0.6;
      }
    }
    
    .welcome-panda-container {
      margin-bottom: 1.5rem;
      position: relative;
      z-index: 1;
    }
    
    .welcome-panda-img {
      width: 180px;
      height: 180px;
      object-fit: contain;
      animation: welcomePandaBounce 2s ease-in-out infinite;
      filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
    }
    
    @keyframes welcomePandaBounce {
      0%, 100% {
        transform: translateY(0px);
      }
      50% {
        transform: translateY(-10px);
      }
    }
    
    .welcome-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: #ffffff;
      margin-bottom: 1rem;
      text-shadow: 0 2px 10px rgba(56, 160, 255, 0.5);
      position: relative;
      z-index: 1;
      background: linear-gradient(135deg, #ffffff 0%, #38a0ff 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .welcome-message {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.7;
      margin: 0;
      position: relative;
      z-index: 1;
      max-width: 500px;
      margin: 0 auto 1.5rem auto;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    
    .welcome-btn {
      padding: 0.875rem 2.5rem;
      background: linear-gradient(135deg, #38a0ff 0%, #2c8de0 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
      box-shadow: 0 4px 15px rgba(56, 160, 255, 0.4);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .welcome-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 25px rgba(56, 160, 255, 0.6);
      background: linear-gradient(135deg, #2c8de0 0%, #1a6dbf 100%);
    }
    
    .welcome-btn:active {
      transform: translateY(-1px);
      box-shadow: 0 3px 15px rgba(56, 160, 255, 0.5);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .welcome-modal-content {
        padding: 2rem 1.5rem;
      }
      
      .welcome-panda-img {
        width: 140px;
        height: 140px;
      }
      
      .welcome-title {
        font-size: 2rem;
      }
      
      .welcome-message {
        font-size: 1rem;
      }
      
      .welcome-btn {
        font-size: 1rem;
        padding: 0.75rem 2rem;
      }
    }
    
    /* PlayStation Particles Background */
    .particles-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
      overflow: hidden;
    }
    
    .particle {
      position: absolute;
      opacity: 0;
      animation: floatUp linear infinite;
    }
    
    /* X Button (Cross) */
    .particle-x {
      width: 30px;
      height: 30px;
      color: rgba(56, 160, 255, 0.4);
      font-size: 28px;
      font-weight: bold;
      line-height: 30px;
      text-align: center;
      font-family: Arial, sans-serif;
    }
    
    /* O Button (Circle) */
    .particle-o {
      width: 28px;
      height: 28px;
      border: 3px solid rgba(255, 85, 100, 0.4);
      border-radius: 50%;
    }
    
    /* Square Button */
    .particle-square {
      width: 26px;
      height: 26px;
      background: rgba(255, 130, 200, 0.4);
      border-radius: 3px;
    }
    
    /* Triangle Button */
    .particle-triangle {
      width: 0;
      height: 0;
      border-left: 15px solid transparent;
      border-right: 15px solid transparent;
      border-bottom: 26px solid rgba(100, 255, 150, 0.4);
    }
    
    @keyframes floatUp {
      0% {
        transform: translateY(0) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 0.6;
      }
      90% {
        opacity: 0.4;
      }
      100% {
        transform: translateY(-100vh) rotate(360deg);
        opacity: 0;
      }
    }
    
    /* Notification Styles */
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #ff3b3b;
      color: white;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.15rem 0.4rem;
      border-radius: 1rem;
      min-width: 18px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(255, 59, 59, 0.5);
      animation: pulse-badge 2s infinite;
    }
    
    @keyframes pulse-badge {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    .notification-dropdown {
      position: fixed;
      display: none;
      background: linear-gradient(135deg, rgba(18, 34, 90, 0.98) 0%, rgba(11, 21, 55, 0.98) 100%);
      border: 2px solid rgba(56, 160, 255, 0.4);
      border-radius: 1rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(20px);
      width: 380px;
      max-height: 500px;
      z-index: 10000;
      animation: slideInDown 0.3s ease-out;
    }
    
    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .notification-dropdown-header {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid rgba(56, 160, 255, 0.2);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .notification-dropdown-header h6 {
      margin: 0;
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-mark-all-read {
      background: transparent;
      border: 1px solid rgba(56, 160, 255, 0.3);
      color: #38a0ff;
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .btn-mark-all-read:hover {
      background: rgba(56, 160, 255, 0.1);
      border-color: #38a0ff;
    }
    
    .notification-list {
      max-height: 420px;
      overflow-y: auto;
    }
    
    .notification-item {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid rgba(56, 160, 255, 0.1);
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      gap: 0.75rem;
      align-items: start;
    }
    
    .notification-item:hover {
      background: rgba(56, 160, 255, 0.1);
    }
    
    .notification-item.unread {
      background: rgba(56, 160, 255, 0.05);
      border-left: 3px solid #38a0ff;
    }
    
    .notification-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
    }
    
    .notification-icon.like {
      background: linear-gradient(135deg, #ffd700, #ff8c00);
      color: #fff;
    }
    
    .notification-icon.comment {
      background: linear-gradient(135deg, #38a0ff, #1b378d);
      color: #fff;
    }
    
    .notification-icon.level_up {
      background: linear-gradient(135deg, #00ff88, #00cc6a);
      color: #fff;
    }
    
    .notification-content {
      flex: 1;
    }
    
    .notification-message {
      color: #fff;
      font-size: 0.9rem;
      margin-bottom: 0.25rem;
      line-height: 1.4;
    }
    
    .notification-time {
      color: rgba(255, 255, 255, 0.5);
      font-size: 0.75rem;
    }
    
    .notification-empty {
      padding: 2rem 1.25rem;
      text-align: center;
      color: rgba(255, 255, 255, 0.5);
    }
    
    .notification-empty i {
      font-size: 3rem;
      margin-bottom: 0.5rem;
      display: block;
      opacity: 0.3;
    }
    
    .notification-loading {
      padding: 2rem 1.25rem;
      text-align: center;
      color: rgba(255, 255, 255, 0.7);
    }
    
    .notification-loading i {
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    /* Profile Picture Lazy Loading with Star Loader */
    .avatar-us {
      position: relative;
      background: linear-gradient(135deg, rgba(56, 160, 255, 0.2), rgba(27, 55, 141, 0.3));
      border-radius: 50%;
      overflow: hidden;
      min-width: 60px;
      min-height: 60px;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .avatar-us.avatar-loading .star-loader {
      display: block;
    }
    
    .avatar-us:not(.avatar-loading) .star-loader {
      display: none;
    }
    
    .star-loader {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 2rem;
      z-index: 10;
      animation: starSpin 1s ease-in-out infinite, starPulse 1.5s ease-in-out infinite;
      filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.8));
    }
    
    .profile-lazy-img {
      opacity: 0;
      transition: opacity 0.5s ease-in;
      position: relative;
      z-index: 11;
    }
    
    .profile-lazy-img.loaded {
      opacity: 1;
    }
    
    @keyframes starSpin {
      0% { transform: translate(-50%, -50%) rotate(0deg) scale(1); }
      50% { transform: translate(-50%, -50%) rotate(180deg) scale(1.2); }
      100% { transform: translate(-50%, -50%) rotate(360deg) scale(1); }
    }
    
    @keyframes starPulse {
      0%, 100% { opacity: 1; filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.8)); }
      50% { opacity: 0.6; filter: drop-shadow(0 0 20px rgba(255, 215, 0, 1)); }
    }
  </style>

  <!-- Set current user ID for JavaScript -->
  <script>
    const currentUserId = <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo json_encode($userId); ?>;
  </script>

  <!-- Dashboard Posts Management Script -->
  <script src="../assets/js/dashboard-posts.js?v=<?php
require_once __DIR__ . '/../config/supabase-session.php'; echo time(); ?>"></script>
  
  <!-- Search Filter Functionality -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const filterButton = document.getElementById('filterButton');
      const filterDropdown = document.getElementById('filterDropdown');
      const filterOptions = document.querySelectorAll('.filter-option');
      const searchFilterInput = document.getElementById('searchFilterInput');
      const searchInput = document.getElementById('searchInput');
      const searchResultsInfo = document.getElementById('searchResultsInfo');
      const searchQueryDisplay = document.getElementById('searchQueryDisplay');
      const searchFilterDisplay = document.getElementById('searchFilterDisplay');
      const searchResultsCount = document.getElementById('searchResultsCount');
      const clearSearchBtn = document.getElementById('clearSearchBtn');
      
      let currentFilter = 'title'; // Default filter
      
      // Real-time search functionality (like games.php)
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        performSearch(searchTerm, currentFilter);
      });
      
      // Perform client-side search
      function performSearch(searchTerm, filterType) {
        const postCards = document.querySelectorAll('.card-post');
        let visibleCount = 0;
        
        if (searchTerm === '') {
          // Show all posts when search is empty
          postCards.forEach(card => {
            card.style.display = 'block';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          });
          searchResultsInfo.style.display = 'none';
          return;
        }
        
        // Filter posts based on search term and filter type
        postCards.forEach(card => {
          let matchFound = false;
          
          switch(filterType) {
            case 'title':
              const titleElement = card.querySelector('.title');
              if (titleElement) {
                const title = titleElement.textContent.toLowerCase();
                matchFound = title.includes(searchTerm);
              }
              break;
              
            case 'author':
              const handleElement = card.querySelector('.handle');
              if (handleElement) {
                const username = handleElement.textContent.toLowerCase().replace('@', '');
                matchFound = username.includes(searchTerm);
              }
              break;
              
            case 'content':
              const contentElement = card.querySelector('.col p');
              if (contentElement) {
                const content = contentElement.textContent.toLowerCase();
                matchFound = content.includes(searchTerm);
              }
              break;
          }
          
          if (matchFound) {
            card.style.display = 'block';
            setTimeout(() => {
              card.style.opacity = '1';
              card.style.transform = 'translateY(0)';
            }, visibleCount * 30);
            visibleCount++;
          } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(-20px)';
            setTimeout(() => {
              card.style.display = 'none';
            }, 200);
          }
        });
        
        // Show search results info
        searchResultsInfo.style.display = 'block';
        searchQueryDisplay.textContent = searchInput.value;
        
        // Update filter display text
        switch(filterType) {
          case 'author':
            searchFilterDisplay.textContent = 'Author';
            break;
          case 'content':
            searchFilterDisplay.textContent = 'Content';
            break;
          default:
            searchFilterDisplay.textContent = 'Title';
        }
        
        // Update results count
        const resultText = visibleCount === 0 
          ? '<i class="bi bi-info-circle"></i> No posts found matching your search criteria.'
          : `Found ${visibleCount} post${visibleCount !== 1 ? 's' : ''}`;
        searchResultsCount.innerHTML = resultText;
      }
      
      // Clear search button
      clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        performSearch('', currentFilter);
      });
      
      // Toggle filter dropdown
      filterButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isVisible = filterDropdown.style.display === 'block';
        filterDropdown.style.display = isVisible ? 'none' : 'block';
        
        // Position the dropdown near the filter button
        const rect = filterButton.getBoundingClientRect();
        filterDropdown.style.top = (rect.bottom + 10) + 'px';
        filterDropdown.style.right = '20px';
      });
      
      // Handle filter option selection
      filterOptions.forEach(option => {
        option.addEventListener('click', function() {
          const selectedFilter = this.getAttribute('data-filter');
          currentFilter = selectedFilter;
          
          // Update hidden input
          searchFilterInput.value = selectedFilter;
          
          // Update active state
          filterOptions.forEach(opt => opt.classList.remove('active'));
          this.classList.add('active');
          
          // Hide dropdown
          filterDropdown.style.display = 'none';
          
          // Re-run search with new filter if there's a search term
          const searchTerm = searchInput.value.toLowerCase().trim();
          if (searchTerm !== '') {
            performSearch(searchTerm, currentFilter);
          }
        });
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!filterDropdown.contains(e.target) && e.target !== filterButton) {
          filterDropdown.style.display = 'none';
        }
      });
    });
  </script>
  
  <!-- PlayStation Particles Script -->
  <script>
    // Create PlayStation button particles
    function createParticles() {
      const container = document.getElementById('particlesContainer');
      const particleTypes = ['x', 'o', 'square', 'triangle'];
      const particleCount = 15; // Number of particles
      
      for (let i = 0; i < particleCount; i++) {
        setTimeout(() => {
          const particle = document.createElement('div');
          const type = particleTypes[Math.floor(Math.random() * particleTypes.length)];
          
          particle.className = `particle particle-${type}`;
          
          // Random horizontal position
          particle.style.left = Math.random() * 100 + '%';
          
          // Random animation duration (15-30 seconds)
          const duration = 15 + Math.random() * 15;
          particle.style.animationDuration = duration + 's';
          
          // Random delay
          const delay = Math.random() * 10;
          particle.style.animationDelay = delay + 's';
          
          // Random size variation (0.7x to 1.3x)
          const scale = 0.7 + Math.random() * 0.6;
          particle.style.transform = `scale(${scale})`;
          
          // Add X content for cross
          if (type === 'x') {
            particle.textContent = '×';
          }
          
          container.appendChild(particle);
          
          // Remove and recreate particle after animation completes
          particle.addEventListener('animationiteration', () => {
            particle.style.left = Math.random() * 100 + '%';
            const newDuration = 15 + Math.random() * 15;
            particle.style.animationDuration = newDuration + 's';
          });
        }, i * 200); // Stagger particle creation
      }
    }
    
    // Initialize particles when page loads
    document.addEventListener('DOMContentLoaded', createParticles);
  </script>

  <!-- Notifications Script -->
  <script>
    // Notification System
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    let notificationsOpen = false;
    
    // Toggle notification dropdown
    notificationButton.addEventListener('click', function(e) {
      e.stopPropagation();
      notificationsOpen = !notificationsOpen;
      
      if (notificationsOpen) {
        loadNotifications();
        notificationDropdown.style.display = 'block';
        
        // Position dropdown
        const rect = notificationButton.getBoundingClientRect();
        notificationDropdown.style.top = (rect.bottom + 10) + 'px';
        notificationDropdown.style.right = '20px';
      } else {
        notificationDropdown.style.display = 'none';
      }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!notificationDropdown.contains(e.target) && e.target !== notificationButton) {
        notificationDropdown.style.display = 'none';
        notificationsOpen = false;
      }
    });
    
    // Load notifications
    function loadNotifications() {
      notificationList.innerHTML = '<div class="notification-loading"><i class="bi bi-hourglass-split"></i> Loading notifications...</div>';
      
      fetch('../api/notifications.php?action=get_notifications')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayNotifications(data.notifications);
            updateNotificationBadge(data.count);
          } else {
            notificationList.innerHTML = '<div class="notification-empty"><i class="bi bi-exclamation-circle"></i>Error loading notifications</div>';
          }
        })
        .catch(error => {
          console.error('Error loading notifications:', error);
          notificationList.innerHTML = '<div class="notification-empty"><i class="bi bi-exclamation-circle"></i>Error loading notifications</div>';
        });
    }
    
    // Display notifications
    function displayNotifications(notifications) {
      if (notifications.length === 0) {
        notificationList.innerHTML = '<div class="notification-empty"><i class="bi bi-bell-slash"></i><div>No new notifications</div></div>';
        markAllReadBtn.style.display = 'none';
        return;
      }
      
      markAllReadBtn.style.display = 'block';
      
      notificationList.innerHTML = notifications.map(notif => {
        const icon = getNotificationIcon(notif.type);
        const timeAgo = getTimeAgo(notif.created_at);
        
        return `
          <div class="notification-item unread" data-id="${notif.id}" data-post-id="${notif.post_id || ''}">
            <div class="notification-icon ${notif.type}">
              ${icon}
            </div>
            <div class="notification-content">
              <div class="notification-message">${escapeHtml(notif.message)}</div>
              <div class="notification-time">${timeAgo}</div>
            </div>
          </div>
        `;
      }).join('');
      
      // Add click handlers to notifications
      document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
          const notifId = this.getAttribute('data-id');
          const postId = this.getAttribute('data-post-id');
          
          // Mark as read
          markNotificationAsRead(notifId);
          
          // Navigate to post if applicable
          if (postId && postId !== 'null') {
            window.location.href = 'dashboard.php#post-' + postId;
          }
        });
      });
    }
    
    // Get notification icon
    function getNotificationIcon(type) {
      switch(type) {
        case 'like':
          return '<i class="bi bi-star-fill"></i>';
        case 'comment':
          return '<i class="bi bi-chat-fill"></i>';
        case 'level_up':
          return '<i class="bi bi-trophy-fill"></i>';
        default:
          return '<i class="bi bi-bell-fill"></i>';
      }
    }
    
    // Get time ago string
    function getTimeAgo(timestamp) {
      const now = new Date();
      const time = new Date(timestamp);
      const diff = Math.floor((now - time) / 1000); // seconds
      
      if (diff < 60) return 'Just now';
      if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
      if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
      if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
      return time.toLocaleDateString();
    }
    
    // Escape HTML
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    // Mark notification as read
    function markNotificationAsRead(notificationId) {
      fetch('../api/notifications.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notification_id=' + notificationId
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateNotificationCount();
        }
      });
    }
    
    // Mark all as read
    markAllReadBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      
      fetch('../api/notifications.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadNotifications();
        }
      });
    });
    
    // Update notification badge
    function updateNotificationBadge(count) {
      if (count > 0) {
        notificationBadge.textContent = count > 99 ? '99+' : count;
        notificationBadge.style.display = 'block';
      } else {
        notificationBadge.style.display = 'none';
      }
    }
    
    // Update notification count
    function updateNotificationCount() {
      fetch('../api/notifications.php?action=get_count')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            updateNotificationBadge(data.count);
          }
        });
    }
    
    // Check for new notifications every 30 seconds
    setInterval(updateNotificationCount, 30000);
    
    // Initial count load
    updateNotificationCount();
  </script>

  <!-- Profile Picture Lazy Loading Script -->
  <script>
    // Optimized lazy loading for profile pictures with preloading
    document.addEventListener('DOMContentLoaded', function() {
      const profileImages = document.querySelectorAll('.profile-lazy-img');
      
      // Preload visible images first (first 3 posts)
      const visibleImages = Array.from(profileImages).slice(0, 3);
      const belowFoldImages = Array.from(profileImages).slice(3);
      
      // Load visible images immediately
      visibleImages.forEach(img => {
        img.loading = 'eager';
        if (img.complete && img.naturalHeight !== 0) {
          img.classList.add('loaded');
        }
      });
      
      // Use Intersection Observer for below-the-fold images
      if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const img = entry.target;
              // Force load if not already loaded
              if (!img.classList.contains('loaded')) {
                const src = img.src;
                img.src = '';
                img.src = src;
              }
              imageObserver.unobserve(img);
            }
          });
        }, {
          rootMargin: '50px' // Start loading 50px before entering viewport
        });
        
        belowFoldImages.forEach(img => imageObserver.observe(img));
      } else {
        // Fallback for browsers without Intersection Observer
        belowFoldImages.forEach(img => {
          if (img.complete && img.naturalHeight !== 0) {
            img.classList.add('loaded');
          }
        });
      }
    });
  </script>

</body>
</html>
