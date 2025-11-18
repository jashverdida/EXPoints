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

// Get database connection and fetch user's profile picture
$db = getDBConnection();

if ($db && $userId) {
    try {
        $profileStmt = $db->prepare("SELECT profile_picture FROM user_info WHERE user_id = ?");
        $profileStmt->bind_param("i", $userId);
        $profileStmt->execute();
        $profileResult = $profileStmt->get_result();
        if ($profileData = $profileResult->fetch_assoc()) {
            if (!empty($profileData['profile_picture'])) {
                $userProfilePicture = $profileData['profile_picture'];
            }
        }
        $profileStmt->close();
    } catch (Exception $e) {
        error_log("Error fetching profile picture: " . $e->getMessage());
    }
    $db->close();
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Bookmarks</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index.css">
  
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Floating Bookmark Icons Background */
    .floating-icons-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
      overflow: hidden;
    }
    
    .floating-icon {
      position: absolute;
      font-size: 3rem;
      opacity: 0.05;
      animation: float-drift 20s infinite ease-in-out;
    }
    
    @keyframes float-drift {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(100px, -100px) rotate(90deg); }
      50% { transform: translate(-50px, -200px) rotate(180deg); }
      75% { transform: translate(-150px, -100px) rotate(270deg); }
    }
    
    /* Hero Section */
    .bookmarks-hero {
      position: relative;
      z-index: 1;
      padding: 3rem 0 2rem;
      text-align: center;
      background: linear-gradient(180deg, rgba(251, 197, 49, 0.1) 0%, transparent 100%);
      border-radius: 20px;
      margin-bottom: 2rem;
      overflow: hidden;
    }
    
    .bookmarks-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 0%, rgba(251, 197, 49, 0.15), transparent 70%);
      pointer-events: none;
    }
    
    .bookmarks-hero h1 {
      font-size: 4rem;
      font-weight: 800;
      background: linear-gradient(135deg, #fbc531, #f39c12, #e67e22);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      animation: treasure-glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes treasure-glow {
      from { filter: drop-shadow(0 0 10px rgba(251, 197, 49, 0.5)); }
      to { filter: drop-shadow(0 0 25px rgba(251, 197, 49, 0.8)); }
    }
    
    .bookmarks-hero p {
      font-size: 1.3rem;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 300;
    }
    
    .treasure-icon {
      display: inline-block;
      animation: treasure-bounce 2s ease-in-out infinite;
      font-size: 5rem;
      margin-bottom: 1rem;
      filter: drop-shadow(0 0 20px rgba(251, 197, 49, 0.6));
    }
    
    @keyframes treasure-bounce {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-20px) scale(1.1); }
    }
    
    /* Stats Bar */
    .stats-bar {
      display: flex;
      justify-content: center;
      gap: 3rem;
      padding: 2rem;
      background: rgba(251, 197, 49, 0.05);
      border-radius: 20px;
      margin-bottom: 2rem;
      border: 1px solid rgba(251, 197, 49, 0.2);
      backdrop-filter: blur(10px);
    }
    
    .stat-item {
      text-align: center;
    }
    
    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      color: #fbc531;
      display: block;
    }
    
    .stat-label {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    /* Loading Animation */
    .loading-spinner {
      text-align: center;
      padding: 4rem 0;
    }
    
    .spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(251, 197, 49, 0.1);
      border-top-color: #fbc531;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Bookmarked Badge */
    .bookmarked-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(135deg, #fbc531, #f39c12);
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 4px 15px rgba(251, 197, 49, 0.4);
      animation: shine 2s infinite;
    }
    
    @keyframes shine {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }
    
    /* Enhanced Post Cards */
    .card-post {
      position: relative;
      background: linear-gradient(135deg, rgba(26, 26, 46, 0.9) 0%, rgba(22, 33, 62, 0.9) 100%);
      border: 2px solid rgba(251, 197, 49, 0.3);
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
      background: linear-gradient(90deg, transparent, rgba(251, 197, 49, 0.1), transparent);
      transition: left 0.5s;
      border-radius: 20px;
    }
    
    .card-post:hover::before {
      left: 100%;
    }
    
    .card-post:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: #fbc531;
      box-shadow: 0 20px 60px rgba(251, 197, 49, 0.3), 
                  0 0 40px rgba(251, 197, 49, 0.2);
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 5rem 2rem;
      background: rgba(251, 197, 49, 0.02);
      border-radius: 20px;
      border: 2px dashed rgba(251, 197, 49, 0.2);
    }
    
    .empty-state i {
      font-size: 6rem;
      color: rgba(251, 197, 49, 0.3);
      margin-bottom: 2rem;
      animation: sway 3s ease-in-out infinite;
    }
    
    @keyframes sway {
      0%, 100% { transform: rotate(-5deg); }
      50% { transform: rotate(5deg); }
    }
    
    .cta-button {
      margin-top: 2rem;
      padding: 1rem 2.5rem;
      background: linear-gradient(135deg, #fbc531, #f39c12);
      border: none;
      border-radius: 30px;
      color: white;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      box-shadow: 0 10px 30px rgba(251, 197, 49, 0.3);
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    
    .cta-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(251, 197, 49, 0.5);
      color: white;
    }
  </style>
</head>
<body data-user-id="<?php echo $userId; ?>">
  <!-- Floating Icons Background -->
  <div class="floating-icons-bg" id="floatingIcons"></div>

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
    <div class="bookmarks-hero">
      <div class="treasure-icon">💎</div>
      <h1>YOUR COLLECTION</h1>
      <p>Treasured reviews saved for later</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-value" id="totalBookmarks">0</span>
        <span class="stat-label">Saved Posts</span>
      </div>
      <div class="stat-item">
        <span class="stat-value" id="totalGames">0</span>
        <span class="stat-label">Games Featured</span>
      </div>
    </div>

    <!-- Bookmarked Posts Container -->
    <div id="bookmarksContainer">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;">Loading your treasured posts...</p>
      </div>
    </div>
  </main>

  <!-- Slide-in sidebar -->
  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <button class="side-btn" onclick="window.location.href='dashboard.php'" title="Home"><i class="bi bi-house"></i></button>
        <button class="side-btn active" title="Bookmarks"><i class="bi bi-bookmark-fill"></i></button>
        <button class="side-btn" onclick="window.location.href='games.php'" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn" onclick="window.location.href='popular.php'" title="Popular"><i class="bi bi-compass"></i></button>
        <button class="side-btn" onclick="window.location.href='newest.php'" title="Newest"><i class="bi bi-star-fill"></i></button>
        <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
      </div>
    </div>
  </aside>

  <script>
    const currentUserId = <?php echo json_encode($userId); ?>;
    
    // Create floating bookmark icons
    function createFloatingIcons() {
      const container = document.getElementById('floatingIcons');
      const icons = ['📖', '⭐', '💎', '🔖', '✨', '🎮', '🏆', '💫'];
      
      for (let i = 0; i < 20; i++) {
        const icon = document.createElement('div');
        icon.className = 'floating-icon';
        icon.textContent = icons[Math.floor(Math.random() * icons.length)];
        icon.style.left = Math.random() * 100 + '%';
        icon.style.top = Math.random() * 100 + '%';
        icon.style.animationDuration = Math.random() * 10 + 15 + 's';
        icon.style.animationDelay = Math.random() * 5 + 's';
        container.appendChild(icon);
      }
    }
    
    createFloatingIcons();
    
    // Logout functionality
    document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.querySelector('.logout-btn-sidebar');
      
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          sessionStorage.removeItem('welcomeShown');
          window.location.href = '../logout.php';
        });
      }
    });
  </script>

  <!-- Bookmarks Management Script -->
  <script src="../assets/js/bookmarks.js?v=<?php echo time(); ?>"></script>

</body>
</html>
