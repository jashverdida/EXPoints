<?php
// Public profile view page
session_start();

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php?error=' . urlencode('Please login to continue'));
    exit();
}

// Database connection function
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
$viewUserId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$currentUserId = $_SESSION['user_id'];

// Redirect to own profile if viewing self
if ($viewUserId == $currentUserId) {
    header('Location: profile.php');
    exit();
}

// Get user data from database
$userStmt = $db->prepare("
    SELECT 
        u.id,
        u.email,
        u.role,
        u.created_at,
        ui.username,
        ui.first_name,
        ui.middle_name,
        ui.last_name,
        ui.suffix,
        ui.bio,
        ui.profile_picture,
        ui.exp_points
    FROM users u
    LEFT JOIN user_info ui ON u.id = ui.user_id
    WHERE u.id = ?
");
$userStmt->bind_param("i", $viewUserId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    header('Location: dashboard.php?error=' . urlencode('User not found'));
    exit();
}

$userData = $userResult->fetch_assoc();
$userStmt->close();

// Calculate level using EXP System
require_once '../includes/ExpSystem.php';
$expPoints = (int)($userData['exp_points'] ?? 0);
$level = ExpSystem::calculateLevel($expPoints);

// Get user stats
$statsStmt = $db->prepare("
    SELECT 
        (SELECT COUNT(*) FROM posts WHERE user_id = ?) as total_posts,
        (SELECT COUNT(*) FROM post_likes WHERE user_id = ?) as total_likes_given,
        (SELECT COUNT(*) FROM post_likes pl JOIN posts p ON pl.post_id = p.id WHERE p.user_id = ?) as total_likes_received
");
$statsStmt->bind_param("iii", $viewUserId, $viewUserId, $viewUserId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

// Get user's best posts (top 3 by likes)
$bestPostsStmt = $db->prepare("
    SELECT 
        p.id, 
        p.title,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
        (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count
    FROM posts p
    WHERE p.user_id = ?
    ORDER BY like_count DESC
    LIMIT 3
");
$bestPostsStmt->bind_param("i", $viewUserId);
$bestPostsStmt->execute();
$bestPostsResult = $bestPostsStmt->get_result();
$bestPosts = $bestPostsResult->fetch_all(MYSQLI_ASSOC);
$bestPostsStmt->close();

$db->close();

// Format profile picture
$profilePicture = $userData['profile_picture'] ?? '../assets/img/cat1.jpg';
$username = $userData['username'] ?? 'User';
$fullName = trim(($userData['first_name'] ?? '') . ' ' . ($userData['middle_name'] ?? '') . ' ' . ($userData['last_name'] ?? '') . ' ' . ($userData['suffix'] ?? ''));
$bio = $userData['bio'] ?? 'This user has not set a bio yet.';
$dateStarted = date('m/d/y', strtotime($userData['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($username); ?> - Profile | +EXPoints</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/profile.css" />
</head>
<body class="bg-exp">

  <div class="container py-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <a href="dashboard.php">
        <img id="brand_logo" src="../assets/Assets/EXPoints Logo.png" alt="EXPoints" class="brand-logo" style="height: 50px; cursor: pointer;" />
      </a>
    </div>

    <div class="row g-4">
      <!-- MAIN -->
      <div class="col-md-8">
        <div class="card card-glass p-3 p-md-4 position-relative overflow-visible">

          <!-- Toggle Name Button -->
          <div class="edit-controls">
            <button id="btnToggleName" class="btn btn-sm btn-outline-light">Toggle Name</button>
          </div>

          <!-- Avatar -->
          <div class="avatar-wrap">
            <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Avatar" class="avatar-xl" />
          </div>

          <!-- Header -->
          <div class="content-shift">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h2 id="display_name" class="profile-name mb-0" 
                  data-fullname="<?php echo htmlspecialchars($fullName ?: $username); ?>" 
                  data-username="<?php echo htmlspecialchars($username); ?>">
                <?php echo htmlspecialchars($fullName ?: $username); ?>
              </h2>
              <span class="profile-handle">@<?php echo htmlspecialchars($username); ?></span>
            </div>

            <!-- Stats -->
            <div class="d-flex align-items-center gap-3 mt-2 stats-row">
              <span><i class="bi bi-star-fill"></i><span><?php echo (int)$stats['total_likes_received']; ?></span></span>
              <span><i class="bi bi-book"></i><span><?php echo (int)$stats['total_posts']; ?></span></span>
            </div>

            <!-- Level -->
            <div class="d-flex align-items-center gap-3 mt-2 level-wrap">
              <span class="lvl-pill">LVL <span><?php echo (int)$level; ?></span></span>
              <div class="progress level-bar flex-grow-1">
                <div class="progress-bar" style="width: <?php echo ($expPoints % 1000) / 10; ?>%"></div>
              </div>
            </div>

            <!-- Bio -->
            <?php if (empty($bio)): ?>
              <p class="profile-bio mt-3" style="color: var(--muted); font-style: italic;">This user has not set a bio yet.</p>
            <?php else: ?>
              <p class="profile-bio mt-3"><?php echo nl2br(htmlspecialchars($bio)); ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- SIDEBAR -->
      <div class="col-md-4">
        <!-- Date Started -->
        <div class="card card-glass p-3 mb-3">
          <h5 class="sidebar-title" style="color: #fff;">Date Started</h5>
          <p class="sidebar-value mb-0" style="color: rgba(255, 255, 255, 0.9);"><?php echo $dateStarted; ?></p>
        </div>

        <!-- Favorite Game -->
        <div class="card card-glass p-3 mb-3">
          <h5 class="sidebar-title" style="color: #fff;">Favorite Game</h5>
          <div class="favorite-game-img-wrap">
            <img src="../assets/Assets/Default Fav Game.png" alt="Favorite Game" class="img-fluid rounded" />
          </div>
        </div>

        <!-- Favorite Genres -->
        <div class="card card-glass p-3 mb-3">
          <h5 class="sidebar-title" style="color: #fff;">Favorite Genres</h5>
          <div class="genre-icon-grid">
            <img src="../assets/Assets/JRPG.png" alt="JRPG" class="genre-icon" />
          </div>
        </div>

        <!-- Best Posts -->
        <?php if (!empty($bestPosts)): ?>
        <div class="card card-glass p-3">
          <h5 class="sidebar-title mb-3" style="color: #fff;">Best Posts:</h5>
          <div class="best-posts-container">
            <?php foreach ($bestPosts as $post): ?>
            <div class="best-post-item">
              <div class="best-post-title" style="color: rgba(255, 255, 255, 0.9);"><?php echo htmlspecialchars($post['title']); ?></div>
              <div class="best-post-stats" style="color: rgba(255, 255, 255, 0.7);">
                <span><i class="bi bi-star-fill"></i> <?php echo $post['like_count']; ?></span>
                <span><i class="bi bi-chat-left-text"></i> <?php echo $post['comment_count']; ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Toggle between full name and username
    const btnToggleName = document.getElementById('btnToggleName');
    const displayName = document.getElementById('display_name');
    let showingFullName = true;

    if (btnToggleName) {
      btnToggleName.addEventListener('click', function() {
        const fullName = displayName.dataset.fullname;
        const username = displayName.dataset.username;
        
        if (showingFullName) {
          displayName.textContent = username;
          showingFullName = false;
        } else {
          displayName.textContent = fullName;
          showingFullName = true;
        }
      });
    }
  </script>
</body>
</html>
