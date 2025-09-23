<?php
session_start();

// Check if user is authenticated (but don't redirect if not - allow guest browsing)
$isLoggedIn = isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : 'Guest';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Newest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/EXPoints/assets/css/index.css">
</head>
<body>

  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="dashboard.php" class="lp-brand" aria-label="+EXPoints dashboard">
        <img src="/EXPoints/assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>

      <form class="search" role="search">
        <input type="text" placeholder="Search for a Review, a Game, Anything" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>

      <div class="right">
        <button class="icon" title="Filter"><i class="bi bi-funnel"></i></button>
        
        <?php if ($isLoggedIn): ?>
        <!-- Logged in user options -->
        <div class="settings-dropdown">
          <button class="icon settings-btn" title="Settings"><i class="bi bi-gear"></i></button>
          <div class="dropdown-menu">
            <button class="dropdown-item profile-btn">
              <i class="bi bi-person"></i>
              Profile
            </button>
            <button class="dropdown-item logout-btn">
              <i class="bi bi-box-arrow-right"></i>
              Logout
            </button>
          </div>
        </div>
        <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
        <div class="avatar-nav" title="<?php echo htmlspecialchars($userName); ?>"></div>
        <?php else: ?>
        <!-- Guest user navigation options -->
        <a href="games.php" class="icon" title="Games"><i class="bi bi-grid-3x3-gap"></i></a>
        <a href="popular.php" class="icon" title="Popular"><i class="bi bi-compass"></i></a>
        <a href="newest.php" class="icon" title="Newest"><i class="bi bi-star"></i></a>
        <a href="login.php" class="icon" title="Login"><i class="bi bi-box-arrow-in-right"></i></a>
        <a href="register.php" class="icon" title="Register"><i class="bi bi-person-plus"></i></a>
        <?php endif; ?>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <h1 class="title mb-3">Newest Posts</h1>

    <!-- Placeholder newest posts (chronological) -->
    <article class="card-post">
      <div class="row gap-3 align-items-start">
        <div class="col-auto"><div class="avatar-lg"></div></div>
        <div class="col">
          <h2 class="title mb-1">Fresh Patch Notes: 1.2.3</h2>
          <div class="handle mb-3">@dev-log</div>
          <p class="mb-3">Balance tweaks and stability improvements…</p>
        </div>
      </div>
      <div class="actions">
        <span class="a"><i class="bi bi-star"></i><b>12</b></span>
        <span class="a"><i class="bi bi-chat-left-text"></i><b>3</b></span>
      </div>
    </article>
  </main>

  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <a class="side-btn" href="dashboard.php" title="Home"><i class="bi bi-house"></i></a>
        <?php if ($isLoggedIn): ?>
        <a class="side-btn" href="bookmarks.php" title="Bookmarks"><i class="bi bi-bookmark"></i></a>
        <?php endif; ?>
        <a class="side-btn" href="games.php" title="Games"><i class="bi bi-grid-3x3-gap"></i></a>
        <a class="side-btn" href="popular.php" title="Popular"><i class="bi bi-compass"></i></a>
        <a class="side-btn side-bottom" href="newest.php" title="Newest"><i class="bi bi-star-fill"></i></a>
      </div>
    </div>
  </aside>

  <script>
    // Settings dropdown and navigation functionality
    document.addEventListener('DOMContentLoaded', function() {
      const settingsBtn = document.querySelector('.settings-btn');
      const dropdownMenu = document.querySelector('.dropdown-menu');
      const logoutBtn = document.querySelector('.logout-btn');
      const profileBtn = document.querySelector('.profile-btn');
      
      // Only setup dropdown if elements exist (logged in users)
      if (settingsBtn && dropdownMenu) {
        // Toggle dropdown when settings button is clicked
        settingsBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.settings-dropdown')) {
            dropdownMenu.classList.remove('show');
          }
        });
      }
      
      // Profile button functionality
      if (profileBtn) {
        profileBtn.addEventListener('click', function() {
          window.location.href = 'profile.php';
        });
      }
      
      // Handle logout button click
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          // Redirect to logout script for proper session cleanup
          window.location.href = 'logout.php';
        });
      }
    });
  </script>

</body>
</html>


