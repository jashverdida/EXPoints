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

// Get user info
$username = $_SESSION['username'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-text">
                Welcome, <?php echo htmlspecialchars($username); ?>!
            </span>
            <button class="btn btn-outline-light" onclick="window.location.href='../logout.php'">Logout</button>
        </div>
    </nav>

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
        <div class="settings-dropdown">
          <button class="icon settings-btn" title="Settings"><i class="bi bi-gear"></i></button>
          <div class="dropdown-menu">
            <button class="dropdown-item logout-btn">
              <i class="bi bi-box-arrow-right"></i>
              Logout
            </button>
          </div>
        </div>
        <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
        <a href="profile.php" class="avatar-nav">
          <img src="/EXPoints/assets/img/lara.jpg" alt="Profile" class="avatar-img">
        </a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <div class="mb-4">
      <h1 class="title" style="font-size: 2.5rem; margin-bottom: 0.5rem;">Your Bookmarks</h1>
      <p style="color: var(--muted); font-size: 1.1rem;">Posts you've saved for later</p>
    </div>

    <!-- Bookmarked Posts Container -->
    <div id="bookmarksContainer">
      <div class="text-center py-5" id="loadingMessage">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3" style="color: var(--muted);">Loading your bookmarks...</p>
      </div>
      <div class="text-center py-5" id="emptyMessage" style="display: none;">
        <i class="bi bi-bookmark" style="font-size: 4rem; color: var(--muted);"></i>
        <p class="mt-3" style="color: var(--muted); font-size: 1.2rem;">You haven't bookmarked any posts yet.</p>
        <a href="dashboard.php" class="btn btn-primary mt-3">Browse Posts</a>
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
        <button class="side-btn side-bottom" onclick="window.location.href='newest.php'" title="Newest"><i class="bi bi-star-fill"></i></button>
      </div>
    </div>
  </aside>

  <script>
    // Settings dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
      const settingsBtn = document.querySelector('.settings-btn');
      const dropdownMenu = document.querySelector('.dropdown-menu');
      const logoutBtn = document.querySelector('.logout-btn');
      
      if (settingsBtn && dropdownMenu) {
        settingsBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          dropdownMenu.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.settings-dropdown')) {
            dropdownMenu.classList.remove('show');
          }
        });
      }
      
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          window.location.href = '../logout.php';
        });
      }
    });
  </script>

  <!-- Bookmarks Management Script -->
  <script src="../assets/js/bookmarks.js?v=<?php echo time(); ?>"></script>

</body>
</html>
