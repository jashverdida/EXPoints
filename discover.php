<?php
// Public discover page - no authentication required
// Guests can browse posts but cannot interact

// Database connection
function getDBConnection() {
    $host = '127.0.0.1';
    $dbname = 'expoints_db';
    $username = 'root';
    $password = '';
    
    try {
        $mysqli = new mysqli($host, $username, $password, $dbname);
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

$db = getDBConnection();
$posts = [];

if ($db) {
    try {
        // Get all posts with author info - exclude posts from banned users
        $query = "SELECT p.*, 
                  (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
                  (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count,
                  ui.profile_picture as author_profile_picture,
                  ui.exp_points
                  FROM posts p 
                  LEFT JOIN user_info ui ON p.user_id = ui.user_id
                  WHERE (p.hidden IS NULL OR p.hidden = 0)
                  AND (ui.is_banned IS NULL OR ui.is_banned = 0)
                  ORDER BY p.created_at DESC";
        
        $result = $db->query($query);
        
        if ($result) {
            while ($post = $result->fetch_assoc()) {
                $posts[] = $post;
            }
        }
        
    } catch (Exception $e) {
        error_log("Discover page error: " . $e->getMessage());
    }
}

$db->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Discover Reviews • EXPoints</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/index.css">

  <style>
    /* Additional styles for discover page */
    .discover-hero {
      background: linear-gradient(135deg, rgba(18, 34, 90, 0.95) 0%, rgba(11, 21, 55, 0.95) 100%);
      border: 1px solid rgba(56, 160, 255, 0.3);
      border-radius: 1rem;
      padding: 2rem;
      margin-bottom: 2rem;
      text-align: center;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }
    
    .discover-hero h1 {
      color: #38a0ff;
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      text-shadow: 0 0 20px rgba(56, 160, 255, 0.5);
    }
    
    .discover-hero p {
      color: rgba(255, 255, 255, 0.8);
      font-size: 1.1rem;
      margin-bottom: 1.5rem;
    }
    
    .guest-notice {
      background: rgba(255, 193, 7, 0.1);
      border: 1px solid rgba(255, 193, 7, 0.3);
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .guest-notice i {
      font-size: 1.5rem;
      color: #ffc107;
    }
    
    .guest-notice-text {
      flex: 1;
      color: rgba(255, 255, 255, 0.9);
    }
    
    .guest-notice a {
      color: #38a0ff;
      text-decoration: none;
      font-weight: 600;
    }
    
    .guest-notice a:hover {
      text-decoration: underline;
    }
    
    .post-disabled {
      opacity: 0.9;
      position: relative;
    }
    
    .post-disabled .actions {
      opacity: 0.5;
      pointer-events: none;
    }
    
    .post-disabled .post-menu {
      display: none;
    }
    
    .no-interaction-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      cursor: not-allowed;
      z-index: 1;
      display: none;
    }
    
    .post-disabled:hover .no-interaction-overlay {
      display: block;
      background: rgba(0, 0, 0, 0.1);
      border-radius: 1rem;
    }
  </style>
</head>
<body>
  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="user/index.php" class="lp-brand" aria-label="+EXPoints home">
        <img src="assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>

      <div class="search">
        <input type="text" id="searchInput" placeholder="Search for a Review, a Game, Anything" autocomplete="off" />
        <input type="hidden" id="searchFilterInput" value="title" />
        <button class="icon" aria-label="Search"><i class="bi bi-search"></i></button>
      </div>

      <div class="right">
        <button class="icon" id="filterButton" title="Filter" type="button"><i class="bi bi-funnel"></i></button>
        <a href="user/login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-outline-primary">Sign Up</a>
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

  <main class="container-xl py-4">
    <!-- Hero Section -->
    <div class="discover-hero">
      <h1>🎮 Discover Reviews</h1>
      <p>Browse the latest game reviews from our community</p>
    </div>

    <!-- Guest Notice -->
    <div class="guest-notice">
      <i class="bi bi-info-circle-fill"></i>
      <div class="guest-notice-text">
        <strong>Browsing as Guest</strong> - 
        <a href="user/login.php">Login</a> or <a href="register.php">Sign up</a> to like, comment, and share your own reviews!
      </div>
    </div>

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

    <!-- Posts Container -->
    <div id="postsContainer">
      <?php if (empty($posts)): ?>
        <div class="text-center py-5">
          <i class="bi bi-inbox" style="font-size: 4rem; color: rgba(255, 255, 255, 0.3);"></i>
          <h3 class="mt-3" style="color: rgba(255, 255, 255, 0.6);">No posts yet</h3>
          <p style="color: rgba(255, 255, 255, 0.4);">Be the first to share a review!</p>
          <a href="register.php" class="btn btn-primary mt-3">Sign Up to Post</a>
        </div>
      <?php else: ?>
        <?php 
        require_once 'includes/ExpSystem.php';
        foreach ($posts as $post): 
          $profilePicture = $post['author_profile_picture'] ?? 'assets/img/cat1.jpg';
          $expPoints = (int)($post['exp_points'] ?? 0);
          $level = ExpSystem::calculateLevel($expPoints);
        ?>
          <article class="card-post post-disabled" data-post-id="<?php echo $post['id']; ?>">
            <div class="post-header">
              <div class="row gap-3 align-items-start">
                <div class="col-auto">
                  <div class="avatar-lg">
                    <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
                  </div>
                </div>
                <div class="col">
                  <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 0.25rem;">
                    <h2 class="title" style="margin: 0;"><?php echo htmlspecialchars($post['title']); ?></h2>
                    <span class="post-timestamp" style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.5); font-weight: 400;">
                      <?php 
                        $time = strtotime($post['created_at']);
                        $now = time();
                        $diff = $now - $time;
                        
                        if ($diff < 60) echo 'Just now';
                        elseif ($diff < 3600) echo floor($diff / 60) . 'm';
                        elseif ($diff < 86400) echo floor($diff / 3600) . 'h';
                        elseif ($diff < 604800) echo floor($diff / 86400) . 'd';
                        else echo date('M j', $time);
                      ?>
                    </span>
                  </div>
                  <div class="handle mb-3">@<?php echo htmlspecialchars($post['username']); ?> • Level <?php echo $level; ?></div>
                  <p class="mb-0"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                </div>
              </div>
            </div>
            <div class="actions">
              <span class="a" title="Login to like">
                <i class="bi bi-star"></i><b><?php echo $post['like_count']; ?></b>
              </span>
              <span class="a" title="Login to comment">
                <i class="bi bi-chat-left-text"></i><b><?php echo $post['comment_count']; ?></b>
              </span>
            </div>
            <div class="no-interaction-overlay"></div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Search Functionality -->
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
      
      let currentFilter = 'title';
      
      // Real-time search functionality
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        performSearch(searchTerm, currentFilter);
      });
      
      // Perform client-side search
      function performSearch(searchTerm, filterType) {
        const postCards = document.querySelectorAll('.card-post');
        let visibleCount = 0;
        
        if (searchTerm === '') {
          // Show all posts
          postCards.forEach(card => {
            card.style.display = 'block';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          });
          searchResultsInfo.style.display = 'none';
          return;
        }
        
        // Filter posts
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
      
      // Clear search
      clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        performSearch('', currentFilter);
      });
      
      // Toggle filter dropdown
      filterButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isVisible = filterDropdown.style.display === 'block';
        filterDropdown.style.display = isVisible ? 'none' : 'block';
        
        const rect = filterButton.getBoundingClientRect();
        filterDropdown.style.top = (rect.bottom + 10) + 'px';
        filterDropdown.style.right = '20px';
      });
      
      // Handle filter selection
      filterOptions.forEach(option => {
        option.addEventListener('click', function() {
          const selectedFilter = this.getAttribute('data-filter');
          currentFilter = selectedFilter;
          
          searchFilterInput.value = selectedFilter;
          
          filterOptions.forEach(opt => opt.classList.remove('active'));
          this.classList.add('active');
          
          filterDropdown.style.display = 'none';
          
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
</body>
</html>
