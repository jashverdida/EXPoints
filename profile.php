<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;

if (!$isLoggedIn) {
    header('Location: login.php');
    exit();
}

// Get user data from session
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$userFirstName = $_SESSION['user_first_name'] ?? '';
$userLastName = $_SESSION['user_last_name'] ?? '';
$userAvatar = $_SESSION['user_avatar'] ?? 'cat1.jpg';
$joinDate = isset($_SESSION['login_time']) ? date('F Y', $_SESSION['login_time']) : 'Recently';
$uid = $_SESSION['user_id'] ?? '';
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profile • EXPoints</title>

  <!-- Bootstrap 5.3.7 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="assets/css/index.css" />
</head>

<body>

  <!-- Top bar (same as dashboard) -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="dashboard.php" class="lp-brand" aria-label="+EXPoints home">
        <img src="assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
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
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <!-- Profile Card -->
    <article class="card-post" style="max-width: 600px; margin: 0 auto;">
      <div class="row gap-3 align-items-center">
        <div class="col-auto">
          <div class="avatar-lg"></div>
        </div>
        <div class="col">
          <h2 class="title mb-1"><?php echo htmlspecialchars($userName); ?></h2>
          <div class="handle mb-2"><?php echo htmlspecialchars($userEmail); ?></div>
          <div class="mb-3" style="color: var(--muted); font-size: 0.9rem;">
            <i class="bi bi-calendar3"></i> Joined <?php echo $joinDate; ?>
          </div>
          
          <!-- Stats Row -->
          <div class="d-flex gap-4 mb-3">
            <div class="text-center">
              <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand);">0</div>
              <div style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase;">Reviews</div>
            </div>
            <div class="text-center">
              <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand);">0</div>
              <div style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase;">Likes</div>
            </div>
            <div class="text-center">
              <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand);">0</div>
              <div style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase;">Comments</div>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" onclick="editProfile()">
              <i class="bi bi-pencil"></i> Edit Profile
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="handleLogout()">
              <i class="bi bi-box-arrow-right"></i> Logout
            </button>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
              <i class="bi bi-arrow-left"></i> Back
            </a>
          </div>
        </div>
      </div>
    </article>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Settings dropdown functionality (same as dashboard)
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
      
      // Profile button functionality (already on profile page)
      if (profileBtn) {
        profileBtn.addEventListener('click', function() {
          // Already on profile page, just close dropdown
          dropdownMenu.classList.remove('show');
        });
      }
      
      // Handle logout button click in dropdown
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          handleLogout();
        });
      }
    });

    // Standalone logout function for both buttons
    function handleLogout() {
      if (confirm('Are you sure you want to logout?')) {
        // Redirect to logout script for proper session cleanup
        window.location.href = 'logout.php';
      }
    }

    // Profile action functions
    function editProfile() {
      alert('Edit profile functionality coming soon!');
    }

    function viewMyReviews() {
      // Filter dashboard to show only user's reviews
      window.location.href = 'dashboard.php?filter=my-reviews';
    }

    function viewSettings() {
      alert('Account settings functionality coming soon!');
    }

    function viewActivity() {
      alert('Activity history functionality coming soon!');
    }
  </script>
</body>
</html>
