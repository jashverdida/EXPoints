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
$userId = $_SESSION['user_id'];

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
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$result = $userStmt->get_result();
$userData = $result->fetch_assoc();
$userStmt->close();

// Build full name
$fullName = trim($userData['first_name'] . ' ' . ($userData['middle_name'] ? $userData['middle_name'] . ' ' : '') . $userData['last_name'] . ($userData['suffix'] ? ' ' . $userData['suffix'] : ''));
$displayName = $userData['username'] ?? $fullName;
$handle = '@' . ($userData['username'] ?? 'user');
$bio = $userData['bio'] ?? '';
$dateStarted = $userData['created_at'] ?? '';
$profilePicture = $userData['profile_picture'] ?? '../assets/img/cat1.jpg';

// Calculate level using EXP System
require_once '../includes/ExpSystem.php';
$expPoints = (int)($userData['exp_points'] ?? 0);
$level = ExpSystem::calculateLevel($expPoints);

// Calculate progress to next level
$expToNext = ExpSystem::expToNextLevel($expPoints);
if ($level === 1) {
    $levelProgress = ($expPoints / 1) * 100; // Level 1->2 needs 1 EXP
} else {
    $currentLevelBase = 1 + ($level - 2) * 10;
    $expInCurrentLevel = $expPoints - $currentLevelBase;
    $levelProgress = ($expInCurrentLevel / 10) * 100; // Each level needs 10 EXP
}
$levelProgress = min(100, max(0, $levelProgress));

// Get stats (stars and reviews count)
$statsStmt = $db->prepare("
    SELECT 
        (SELECT COUNT(*) FROM post_likes pl 
         JOIN posts p ON pl.post_id = p.id 
         WHERE p.username = ?) as total_stars,
        (SELECT COUNT(*) FROM posts WHERE username = ?) as total_reviews
");
$statsStmt->bind_param("ss", $userData['username'], $userData['username']);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();
$statsStmt->close();

// Get best posts (top 3 by likes)
$postsStmt = $db->prepare("
    SELECT 
        p.id,
        p.title,
        p.content,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes,
        (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comments
    FROM posts p
    WHERE p.username = ?
    ORDER BY likes DESC
    LIMIT 3
");
$postsStmt->bind_param("s", $userData['username']);
$postsStmt->execute();
$postsResult = $postsStmt->get_result();
$bestPosts = [];
while ($post = $postsResult->fetch_assoc()) {
    $bestPosts[] = $post;
}
$postsStmt->close();

// Get all user posts for selection modal
$allPostsStmt = $db->prepare("
    SELECT 
        p.id,
        p.title,
        p.content,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes,
        (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comments
    FROM posts p
    WHERE p.username = ?
    ORDER BY p.created_at DESC
");
$allPostsStmt->bind_param("s", $userData['username']);
$allPostsStmt->execute();
$allPostsResult = $allPostsStmt->get_result();
$allPosts = [];
while ($post = $allPostsResult->fetch_assoc()) {
    $allPosts[] = $post;
}
$allPostsStmt->close();

$db->close();

function h(?string $s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$started_fmt = !empty($dateStarted) ? date('n/j/y', strtotime($dateStarted)) : '—';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Profile</title>

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

          <!-- Edit controls -->
          <div class="edit-controls">
            <button id="btnToggleName"   class="btn btn-sm btn-outline-light">Toggle Name</button>
            <button id="btnEdit"         class="btn btn-sm btn-outline-light">Edit Profile</button>
            <button id="btnCancel"       class="btn btn-sm btn-danger d-none">Cancel</button>
            <button id="btnSave"         class="btn btn-sm btn-success d-none">Save Changes</button>
          </div>

          <!-- Avatar -->
          <div class="avatar-wrap">
            <img id="avatar" src="<?= h($profilePicture) ?>" alt="Avatar" class="avatar-xl" data-edit="img" />
          </div>

          <!-- Header -->
          <div class="content-shift">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h2 id="display_name" class="profile-name mb-0" data-edit="text"
                data-fullname="<?= h($fullName) ?>"><?= h($displayName) ?></h2>
              <span id="handle" class="profile-handle"><?= h($handle) ?></span>
            </div>

            <!-- Stats -->
            <div class="d-flex align-items-center gap-3 mt-2 stats-row">
              <span><i class="bi bi-star-fill"></i><span id="stars"><?= (int)$stats['total_stars'] ?></span></span>
              <span><i class="bi bi-book"></i><span id="reviews"><?= (int)$stats['total_reviews'] ?></span></span>
            </div>

            <!-- Level -->
            <div class="d-flex align-items-center gap-3 mt-2 level-wrap">
              <span class="lvl-pill">LVL <span id="level_num"><?= (int)$level ?></span></span>
              <div class="progress level-bar flex-grow-1">
                <div id="level_bar" class="progress-bar" style="width: <?= (int)$levelProgress ?>%"></div>
              </div>
            </div>

            <!-- Bio -->
            <?php if (empty($bio)): ?>
              <p id="bio" class="profile-bio mt-3" data-edit="textarea" style="color: var(--muted); font-style: italic;">Enter Your Bio!</p>
            <?php else: ?>
              <p id="bio" class="profile-bio mt-3" data-edit="textarea"><?= nl2br(h($bio)) ?></p>
            <?php endif; ?>
          </div>

          <!-- Info grid -->
          <div class="row g-3 align-items-stretch mt-2">
            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Date Started</div>
                <div id="date_started" class="mini-value">
                  <?= h($started_fmt) ?>
                </div>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Favorite Game</div>
                <div class="thumb-box">
                  <img id="game_img" src="../assets/img/Favorite Game.png" alt="Favorite Game" data-edit="img" />
                </div>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Favorite Genres</div>
                <img id="genre_img" src="../assets/img/Favorite%20Genre.png" alt="Favorite Genres" class="genre-badge" data-edit="img" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT SIDEBAR -->
      <div class="col-md-4">
        <div class="card card-pill p-3 p-md-4">
          <h3 class="best-title">Best Posts:</h3>
          <div id="best_posts_list">
            <?php if (empty($bestPosts)): ?>
              <p class="text-muted small">No posts yet. Create your first post!</p>
            <?php else: ?>
              <?php foreach ($bestPosts as $p): ?>
                <div class="best-post d-block mb-2" data-post-id="<?= h($p['id']) ?>">
                  <div class="small fw-semibold text-truncate"><?= h($p['title']) ?></div>
                  <div class="d-flex align-items-center gap-3 small mt-1">
                    <span><i class="bi bi-star-fill me-1"></i><?= (int)$p['likes'] ?></span>
                    <span><i class="bi bi-chat-fill me-1"></i><?= (int)$p['comments'] ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <!-- Hidden until Edit -->
          <button id="btnEditShowcase" class="btn btn-sm btn-light-subtle mt-2 w-100 d-none">Edit Selection</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Best Posts Selection Modal -->
  <div id="postsSelectionModal" class="modal-backdrop d-none">
    <div class="modal-card" style="max-width: 600px; max-height: 80vh; overflow-y: auto;">
      <h5 class="mb-3">Select Your Best Posts (Choose up to 3)</h5>
      <div id="postsSelectionList">
        <?php foreach ($allPosts as $post): ?>
          <div class="post-selection-item" data-post-id="<?= h($post['id']) ?>" style="padding: 1rem; margin-bottom: 0.5rem; border: 2px solid transparent; border-radius: 0.5rem; background: rgba(255,255,255,0.05); cursor: pointer; transition: all 0.3s;">
            <div class="fw-semibold"><?= h($post['title']) ?></div>
            <div class="d-flex align-items-center gap-3 small mt-1">
              <span><i class="bi bi-star-fill me-1"></i><?= (int)$post['likes'] ?></span>
              <span><i class="bi bi-chat-fill me-1"></i><?= (int)$post['comments'] ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="d-flex justify-content-end gap-2 mt-3">
        <button id="closePostsModal" class="btn btn-danger btn-sm px-4">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Save confirmation -->
  <div id="saveModal" class="modal-backdrop d-none">
    <div class="modal-card">
      <h5 class="mb-2 text-center">Save Changes?</h5>
      <p class="text-center mb-3">All changes here will reflect on your profile</p>
      <div class="d-flex justify-content-center gap-2">
        <button id="confirmYes" class="btn btn-success btn-sm px-4">YES</button>
        <button id="confirmNo"  class="btn btn-danger  btn-sm px-4">NO</button>
      </div>
    </div>
  </div>

  <script>
    // Pass PHP data to JavaScript
    const allPosts = <?= json_encode($allPosts) ?>;
    const userId = <?= json_encode($userId) ?>;
    const userData = <?= json_encode($userData) ?>;
  </script>
  <script src="../assets/js/profile.js?v=<?= time() ?>"></script>
</body>
</html>
