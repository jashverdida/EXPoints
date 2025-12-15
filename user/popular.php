<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php?error=' . urlencode('Please login to continue'));
    exit();
}

// Supabase-compatible database connection
require_once __DIR__ . '/../includes/db_helper.php';


// Get user info
$username = $_SESSION['username'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$userId = $_SESSION['user_id'] ?? null;
$userProfilePicture = '../assets/img/cat1.jpg'; // Default profile picture
$posts = [];

// Get database connection
$db = getDBConnection();

if ($db && $userId) {
    try {
        // Fetch user info
        $profileStmt = $db->prepare("SELECT username, profile_picture FROM user_info WHERE user_id = ?");
        $profileStmt->bind_param("i", $userId);
        $profileStmt->execute();
        $profileResult = $profileStmt->get_result();
        if ($profileData = $profileResult->fetch_assoc()) {
            $username = $profileData['username'];
            $_SESSION['username'] = $username;
            if (!empty($profileData['profile_picture'])) {
                $userProfilePicture = $profileData['profile_picture'];
            }
        }
        $profileStmt->close();
        
        // Query posts with like counts, ordered by popularity (likes DESC)
        $query = "SELECT p.*, 
                  (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count
                  FROM posts p
                  WHERE p.hidden = 0
                  ORDER BY like_count DESC, p.created_at DESC
                  LIMIT 100";
        $result = $db->query($query);
        
        if ($result) {
            $postsData = [];
            while ($post = $result->fetch_assoc()) {
                $postsData[] = $post;
            }
            
            // Build maps for batch lookups
            $userInfoMap = [];
            $commentCountMap = [];
            
            // Collect all unique usernames
            $uniqueUsernames = array_unique(array_column($postsData, 'username'));
            
            // Fetch ALL user info
            foreach ($uniqueUsernames as $postUsername) {
                $stmt = $db->prepare("SELECT username, profile_picture, exp_points, is_banned FROM user_info WHERE username = ?");
                $stmt->bind_param("s", $postUsername);
                $stmt->execute();
                $res = $stmt->get_result();
                
                if ($res && $res->num_rows > 0) {
                    $userInfoMap[$postUsername] = $res->fetch_assoc();
                }
                $stmt->close();
            }
            
            // Get comment counts
            foreach ($postsData as $post) {
                $postId = $post['id'];
                
                $commentStmt = $db->prepare("SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?");
                $commentStmt->bind_param("i", $postId);
                $commentStmt->execute();
                $commentResult = $commentStmt->get_result();
                $commentRow = $commentResult->fetch_assoc();
                $commentCountMap[$postId] = (int)($commentRow['count'] ?? 0);
                $commentStmt->close();
            }
            
            // Process all posts using cached data
            foreach ($postsData as $post) {
                $userInfo = $userInfoMap[$post['username']] ?? null;
                
                if ($userInfo && $userInfo['is_banned']) {
                    continue;
                }
                
                $post['author_profile_picture'] = $userInfo['profile_picture'] ?? '../assets/img/cat1.jpg';
                $post['exp_points'] = $userInfo['exp_points'] ?? 0;
                $post['comment_count'] = $commentCountMap[$post['id']] ?? 0;
                // like_count already in query result
                $post['comments_list'] = [];
                
                $posts[] = $post;
            }
        }
    } catch (Exception $e) {
        error_log("Popular posts error: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Popular Posts</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index.css">
  
  <style>
    /* Poppins font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0a0a2e 0%, #16213e 50%, #0f3460 100%);
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Animated Background Particles */
    .particles-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
    }
    
    .particle {
      position: absolute;
      border-radius: 50%;
      animation: float 20s infinite ease-in-out;
      opacity: 0.1;
    }
    
    @keyframes float {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(50px, -50px) rotate(90deg); }
      50% { transform: translate(-30px, -100px) rotate(180deg); }
      75% { transform: translate(-80px, -50px) rotate(270deg); }
    }
    
    /* Hero Section */
    .popular-hero {
      position: relative;
      z-index: 1;
      padding: 3rem 0 2rem;
      text-align: center;
      background: linear-gradient(180deg, rgba(255, 107, 107, 0.1) 0%, transparent 100%);
      border-radius: 20px;
      margin-bottom: 2rem;
      overflow: hidden;
    }
    
    .popular-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 0%, rgba(255, 107, 107, 0.2), transparent 70%);
      pointer-events: none;
    }
    
    .popular-hero h1 {
      font-size: 4rem;
      font-weight: 800;
      background: linear-gradient(135deg, #ff6b6b, #ff8e53, #ffb347);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      text-shadow: 0 0 40px rgba(255, 107, 107, 0.5);
      animation: glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes glow {
      from { filter: drop-shadow(0 0 10px rgba(255, 107, 107, 0.5)); }
      to { filter: drop-shadow(0 0 20px rgba(255, 107, 107, 0.8)); }
    }
    
    .popular-hero p {
      font-size: 1.3rem;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 300;
    }
    
    .fire-icon {
      display: inline-block;
      animation: bounce 1s infinite;
      font-size: 5rem;
      margin-bottom: 1rem;
      filter: drop-shadow(0 0 20px rgba(255, 107, 107, 0.6));
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }
    
    /* Stats Bar */
    .stats-bar {
      display: flex;
      justify-content: center;
      gap: 3rem;
      padding: 2rem;
      background: rgba(255, 107, 107, 0.05);
      border-radius: 20px;
      margin-bottom: 2rem;
      border: 1px solid rgba(255, 107, 107, 0.2);
      backdrop-filter: blur(10px);
    }
    
    .stat-item {
      text-align: center;
    }
    
    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      color: #ff6b6b;
      display: block;
    }
    
    .stat-label {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    /* Posts Container */
    #postsContainer {
      position: relative;
      z-index: 1;
    }
    
    /* Loading Animation */
    .loading-spinner {
      text-align: center;
      padding: 4rem 0;
    }
    
    .spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(255, 107, 107, 0.1);
      border-top-color: #ff6b6b;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Trophy Rankings */
    .rank-badge {
      position: absolute;
      top: -15px;
      left: -15px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.3rem;
      z-index: 100;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
    }
    
    .rank-badge.gold {
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #fff;
      animation: pulse-gold 2s infinite;
    }
    
    .rank-badge.silver {
      background: linear-gradient(135deg, #C0C0C0, #808080);
      color: #fff;
      animation: pulse-silver 2s infinite;
    }
    
    .rank-badge.bronze {
      background: linear-gradient(135deg, #CD7F32, #8B4513);
      color: #fff;
      animation: pulse-bronze 2s infinite;
    }
    
    @keyframes pulse-gold {
      0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6); }
      50% { transform: scale(1.1); box-shadow: 0 8px 35px rgba(255, 215, 0, 0.9); }
    }
    
    @keyframes pulse-silver {
      0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(192, 192, 192, 0.6); }
      50% { transform: scale(1.1); box-shadow: 0 8px 35px rgba(192, 192, 192, 0.9); }
    }
    
    @keyframes pulse-bronze {
      0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(205, 127, 50, 0.6); }
      50% { transform: scale(1.1); box-shadow: 0 8px 35px rgba(205, 127, 50, 0.9); }
    }
    
    /* Enhanced Post Cards */
    .card-post {
      position: relative;
      background: linear-gradient(135deg, rgba(26, 0, 51, 0.9) 0%, rgba(15, 52, 96, 0.9) 100%);
      border: 2px solid rgba(255, 107, 107, 0.3);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backdrop-filter: blur(10px);
      overflow: visible;
    }
    
    .card-post::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 107, 107, 0.1), transparent);
      transition: left 0.5s;
      border-radius: 20px;
    }
    
    .card-post:hover::before {
      left: 100%;
    }
    
    .card-post:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: #ff6b6b;
      box-shadow: 0 20px 60px rgba(255, 107, 107, 0.3), 
                  0 0 40px rgba(255, 107, 107, 0.2);
    }
    
    .trending-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
  </style>
</head>
<body data-user-id="<?php echo $userId; ?>">

  <!-- Animated Background Particles -->
  <div class="particles-bg" id="particlesBg"></div>

  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="dashboard.php" class="lp-brand" aria-label="+EXPoints home">
        <img src="../assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>

      <form class="search" role="search">
        <input type="text" placeholder="Search for a Review, a Game, Anything" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>

      <div class="right">
        <button class="icon" title="Filter"><i class="bi bi-funnel"></i></button>
        <button class="icon" title="Settings"><i class="bi bi-gear"></i></button>
        <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
        <a href="profile.php" class="avatar-nav">
          <img src="<?php echo htmlspecialchars($userProfilePicture); ?>" alt="Profile" class="avatar-img">
        </a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <!-- Hero Section -->
    <div class="popular-hero">
      <div class="fire-icon">🔥</div>
      <h1>TRENDING NOW</h1>
      <p>The hottest reviews getting all the love from the community</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-value" id="totalPosts"><?php echo count($posts); ?></span>
        <span class="stat-label">Hot Posts</span>
      </div>
      <div class="stat-item">
        <span class="stat-value" id="totalLikes"><?php 
          $totalLikes = 0;
          foreach ($posts as $p) {
            $totalLikes += (int)($p['like_count'] ?? 0);
          }
          echo $totalLikes;
        ?></span>
        <span class="stat-label">Total Likes</span>
      </div>
      <div class="stat-item">
        <span class="stat-value" id="totalComments"><?php 
          $totalComments = 0;
          foreach ($posts as $p) {
            $totalComments += (int)($p['comment_count'] ?? 0);
          }
          echo $totalComments;
        ?></span>
        <span class="stat-label">Comments</span>
      </div>
    </div>

    <!-- Posts Container -->
    <!-- Posts Container -->
    <div id="postsContainer">
      <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
          <div class="card-post" data-post-id="<?php echo $post['id']; ?>">
            <div class="post-header">
              <div class="row gap-3 align-items-start">
                <div class="col-auto">
                  <div class="avatar-us avatar-loading">
                    <div class="star-loader">⭐</div>
                    <img src="<?php echo htmlspecialchars($post['author_profile_picture'] ?? '../assets/img/cat1.jpg'); ?>" 
                         alt="Profile" 
                         loading="lazy"
                         class="profile-lazy-img"
                         onload="this.classList.add('loaded'); this.parentElement.classList.remove('avatar-loading');">
                  </div>
                </div>
                <div class="col">
                  <div class="game-badge"><?php echo htmlspecialchars($post['game']); ?></div>
                  <h2 class="title mb-1"><?php echo htmlspecialchars($post['title']); ?></h2>
                  <div class="handle mb-3">@<?php echo htmlspecialchars($post['username']); ?></div>
                  <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                  <div class="time-badge">
                    <i class="bi bi-clock"></i>
                    <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="actions">
              <span class="a like-btn" data-liked="false"><i class="bi bi-star"></i><b><?php echo $post['like_count'] ?? 0; ?></b></span>
              <span class="a comment-btn" data-comments="<?php echo $post['comment_count'] ?? 0; ?>"><i class="bi bi-chat-left-text"></i><b><?php echo $post['comment_count'] ?? 0; ?></b></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.3rem;">No popular posts yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Slide-in sidebar -->
  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <button class="side-btn" onclick="window.location.href='dashboard.php'" title="Home"><i class="bi bi-house"></i></button>
        <button class="side-btn" onclick="window.location.href='bookmarks.php'" title="Bookmarks"><i class="bi bi-bookmark"></i></button>
        <button class="side-btn" onclick="window.location.href='games.php'" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn active" title="Popular"><i class="bi bi-compass-fill"></i></button>
        <button class="side-btn" onclick="window.location.href='newest.php'" title="Newest"><i class="bi bi-star-fill"></i></button>
        <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
      </div>
    </div>
  </aside>

  <script>
    const currentUserId = <?php echo json_encode($userId); ?>;
    
    // Create animated particles
    function createParticles() {
      const container = document.getElementById('particlesBg');
      const colors = ['#ff6b6b', '#ff8e53', '#ffb347', '#38a0ff'];
      
      for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.width = Math.random() * 100 + 20 + 'px';
        particle.style.height = particle.style.width;
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.background = colors[Math.floor(Math.random() * colors.length)];
        particle.style.animationDuration = Math.random() * 10 + 15 + 's';
        particle.style.animationDelay = Math.random() * 5 + 's';
        container.appendChild(particle);
      }
    }
    
    createParticles();
  </script>
  <script src="../assets/js/popular-posts.js?v=<?php echo time(); ?>"></script>
</body>
</html>
