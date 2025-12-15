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

// Supabase database connection
require_once __DIR__ . '/../includes/db_helper.php';

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// ✅ FIX: Fetch profile picture FIRST (matching dashboard logic)
$userProfilePicture = '../assets/img/cat1.jpg'; // Default fallback

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
        error_log("Profile picture fetch error: " . $e->getMessage());
    }
}

// Get full user data from database
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

// Check if user data exists
if (!$userData) {
    $userData = [
        'first_name' => '',
        'middle_name' => '',
        'last_name' => '',
        'suffix' => '',
        'username' => $_SESSION['username'] ?? 'user',
        'bio' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'profile_picture' => $userProfilePicture,
        'exp_points' => 0
    ];
} else {
    // ✅ Override with fetched profile picture (ensures consistency)
    $userData['profile_picture'] = $userProfilePicture;
}

// Build full name
$fullName = trim(($userData['first_name'] ?? '') . ' ' . (($userData['middle_name'] ?? '') ? ($userData['middle_name'] ?? '') . ' ' : '') . ($userData['last_name'] ?? '') . (($userData['suffix'] ?? '') ? ' ' . ($userData['suffix'] ?? '') : ''));
$displayName = $userData['username'] ?? $fullName;
$handle = '@' . ($userData['username'] ?? 'user');
$bio = $userData['bio'] ?? '';
$dateStarted = $userData['created_at'] ?? '';

// ✅ Use consistent profile picture variable
$profilePicture = $userProfilePicture;

// Format date started
$started_fmt = '';
if (!empty($dateStarted)) {
    $dt = new DateTime($dateStarted);
    $started_fmt = $dt->format('n/j/y');
}

// Calculate level using EXP System
$expPoints = (int)($userData['exp_points'] ?? 0);
$level = 1;
if ($expPoints >= 1) {
    $level = 2 + floor(($expPoints - 1) / 10);
}

// Calculate level progress
$levelProgress = ($expPoints % 10) * 10;

// Get user stats (total likes and posts count)
$userId = $_SESSION['user_id'];
$statsStmt = $db->prepare("
    SELECT 
        (SELECT COUNT(*) FROM post_likes pl 
         JOIN posts p ON pl.post_id = p.id 
         WHERE p.user_id = ?) as total_stars,
        (SELECT COUNT(*) FROM posts WHERE user_id = ?) as total_reviews
");
$statsStmt->bind_param("ii", $userId, $userId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

// Get best posts (top 3 by likes)
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
$bestPostsStmt->bind_param("i", $userId);
$bestPostsStmt->execute();
$bestPostsResult = $bestPostsStmt->get_result();
$bestPosts = $bestPostsResult->fetch_all(MYSQLI_ASSOC);
$bestPostsStmt->close();

// Get all user posts for selection modal
$allPostsStmt = $db->prepare("
    SELECT 
        p.id,
        p.title,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
        (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count
    FROM posts p
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$allPostsStmt->bind_param("i", $userId);
$allPostsStmt->execute();
$allPostsResult = $allPostsStmt->get_result();
$allPosts = $allPostsResult->fetch_all(MYSQLI_ASSOC);
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
  
  <style>
    /* Full-page loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #0a1a4d 0%, #1b378d 50%, #0a1a4d 100%);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
        animation: gradientShift 3s ease infinite;
    }
    
    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .loading-overlay.active {
        display: flex;
    }
    
    .loading-stars-container {
        position: relative;
        width: 200px;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Single glowing star with halo */
    .loading-star-main {
        font-size: 5rem;
        position: relative;
        animation: starRotateGlow 2s ease-in-out infinite;
        filter: drop-shadow(0 0 30px rgba(255, 215, 0, 1));
    }
    
    /* Halo rings around star */
    .star-halo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 2px solid rgba(255, 215, 0, 0.4);
        border-radius: 50%;
        animation: haloExpand 2s ease-out infinite;
    }
    
    .star-halo:nth-child(1) {
        width: 120px;
        height: 120px;
        animation-delay: 0s;
    }
    
    .star-halo:nth-child(2) {
        width: 160px;
        height: 160px;
        animation-delay: 0.5s;
    }
    
    .star-halo:nth-child(3) {
        width: 200px;
        height: 200px;
        animation-delay: 1s;
    }
    
    @keyframes starRotateGlow {
        0% {
            transform: rotate(0deg) scale(1);
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
        }
        50% {
            transform: rotate(180deg) scale(1.2);
            filter: drop-shadow(0 0 40px rgba(255, 215, 0, 1)) drop-shadow(0 0 60px rgba(255, 215, 0, 0.6));
        }
        100% {
            transform: rotate(360deg) scale(1);
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
        }
    }
    
    @keyframes haloExpand {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0;
            border-width: 3px;
        }
        50% {
            opacity: 0.6;
            border-width: 2px;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.3);
            opacity: 0;
            border-width: 1px;
        }
    }
    
    .loading-text {
        margin-top: 3rem;
        color: white;
        font-size: 1.5rem;
        font-weight: 600;
        animation: textFade 2s ease-in-out infinite;
        text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
    }
    
    @keyframes textFade {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }
    
    /* Progress bar container */
    .progress-container {
        width: 400px;
        height: 6px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
        margin-top: 2rem;
        position: relative;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }
    
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
        background-size: 200% 100%;
        border-radius: 10px;
        animation: progressGlow 2s ease-in-out infinite, progressMove 15s ease-out forwards;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.8), inset 0 0 10px rgba(255, 255, 255, 0.5);
        width: 0%;
    }
    
    @keyframes progressGlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    @keyframes progressMove {
        0% { width: 0%; }
        95% { width: 95%; }
        100% { width: 100%; }
    }
    
    .progress-text {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }
    
    /* Floating Particles Background */
    .profile-particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
      overflow: hidden;
    }
    
    .profile-particle {
      position: absolute;
      font-size: 1.5rem;
      opacity: 0.3;
      animation: floatParticle 20s linear infinite;
    }
    
    @keyframes floatParticle {
      0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 0.3;
      }
      90% {
        opacity: 0.3;
      }
      100% {
        transform: translateY(-100px) rotate(360deg);
        opacity: 0;
      }
    }
    
    /* Enhanced level bar */
    .level-bar {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 999px;
      height: 12px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(56, 160, 255, 0.3) inset;
    }
    
    .level-bar .progress-bar {
      background: linear-gradient(90deg, #ffd700, #38a0ff, #ffd700);
      background-size: 200% 100%;
      height: 100%;
      border-radius: 999px;
      box-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
      animation: progressShimmer 3s ease-in-out infinite;
    }
    
    @keyframes progressShimmer {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    
    /* ===== PROFILE AVATAR & CARD IMPROVEMENTS ===== */
    
    .avatar-wrap {
      position: relative;
      width: 160px;
      height: 160px;
      margin: 0 auto 1.5rem;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 5px;
      box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.4),
        0 0 0 3px rgba(110, 160, 255, 0.3) inset,
        0 0 40px rgba(102, 126, 234, 0.3);
      z-index: 2;
      transition: all 0.3s ease;
    }
    
    .avatar-wrap:hover {
      transform: scale(1.05);
      box-shadow: 
        0 12px 48px rgba(0, 0, 0, 0.5),
        0 0 0 3px rgba(110, 160, 255, 0.5) inset,
        0 0 60px rgba(102, 126, 234, 0.5);
    }
    
    .avatar-xl {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      object-position: center;
      display: block;
      border: 3px solid rgba(255, 255, 255, 0.1);
    }
    
    .content-shift {
      text-align: center;
    }
    
    .card-glass {
      border: 1px solid rgba(60, 78, 145, 0.5);
      border-radius: 24px;
      background: linear-gradient(180deg, rgba(15, 22, 49, 0.95), rgba(17, 26, 58, 0.9));
      box-shadow: 
        0 0 0 1px rgba(110, 160, 255, 0.15) inset,
        0 20px 60px rgba(0, 0, 0, 0.6),
        0 0 80px rgba(56, 160, 255, 0.1);
      backdrop-filter: blur(20px);
    }
    
    .profile-name {
      color: #fff;
      font-weight: 800;
      font-size: 2rem;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .profile-handle {
      color: rgba(255, 255, 255, 0.7);
      font-weight: 600;
      font-size: 1.1rem;
    }
    
    .lvl-pill {
      display: inline-block;
      padding: 0.4rem 1rem;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 999px;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    .stats-row > span {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.3rem 0.8rem;
      background: rgba(102, 126, 234, 0.15);
      border-radius: 999px;
      border: 1px solid rgba(110, 160, 255, 0.2);
    }
    
    @media (max-width: 768px) {
      .avatar-wrap {
        width: 130px;
        height: 130px;
      }
      
      .profile-name {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body class="bg-exp">

  <!-- Loading Overlay -->
  <div class="loading-overlay" id="loadingOverlay">
      <div class="loading-stars-container">
          <div class="star-halo"></div>
          <div class="star-halo"></div>
          <div class="star-halo"></div>
          <div class="loading-star-main">⭐</div>
      </div>
      <div class="loading-text">Loading your dashboard...</div>
      <div class="progress-container">
          <div class="progress-bar"></div>
      </div>
      <div class="progress-text">Please wait...</div>
  </div>

  <div class="container py-4">
    <!-- Floating particles effect -->
    <div class="profile-particles" id="profileParticles"></div>
    
    <div class="d-flex align-items-center gap-2 mb-3">
      <a href="dashboard.php">
        <img id="brand_logo" src="../assets/Assets/EXPoints Logo.png" alt="EXPoints" class="brand-logo" style="height: 120px; cursor: pointer; filter: drop-shadow(0 0 20px rgba(56, 160, 255, 0.4)); transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.filter='drop-shadow(0 0 30px rgba(56, 160, 255, 0.6))'" onmouseout="this.style.transform='scale(1)'; this.style.filter='drop-shadow(0 0 20px rgba(56, 160, 255, 0.4))'" />
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

          <!-- ✅ FIXED: Avatar with proper sizing, centering, and error handling -->
          <div class="avatar-wrap">
            <img id="avatar" 
                 src="<?= h($profilePicture) ?>" 
                 alt="<?= h($displayName) ?>'s Profile" 
                 class="avatar-xl" 
                 data-edit="img"
                 onerror="this.src='../assets/img/cat1.jpg'" />
          </div>

          <!-- Header -->
          <div class="content-shift">
            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-2">
              <h2 id="display_name" class="profile-name mb-0" data-edit="text"
                data-fullname="<?= h($fullName) ?>"><?= h($displayName) ?></h2>
            </div>
            
            <span id="handle" class="profile-handle d-block mb-3"><?= h($handle) ?></span>

            <!-- Level & Stats -->
            <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
              <span class="lvl-pill">LVL <?= $level ?></span>
              <div class="level-bar flex-grow-1" style="max-width: 200px;">
                <div class="progress-bar" style="width: <?= $levelProgress ?>%"></div>
              </div>
            </div>

            <!-- Stats Row -->
            <div class="d-flex justify-content-center gap-3 mb-3 stats-row flex-wrap">
              <span><i class="bi bi-star-fill"></i><span id="stars"><?= (int)($stats['total_stars'] ?? 0) ?></span></span>
              <span><i class="bi bi-file-earmark-text-fill"></i><span id="reviews"><?= (int)($stats['total_reviews'] ?? 0) ?></span></span>
              <span><i class="bi bi-trophy-fill"></i><?= $expPoints ?> EXP</span>
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
    
    // Create floating particles
    function createProfileParticles() {
      const container = document.getElementById('profileParticles');
      const particles = ['⭐', '🎮', '🏆', '⚡', '💫', '🌟'];
      const particleCount = 15;
      
      for (let i = 0; i < particleCount; i++) {
        setTimeout(() => {
          const particle = document.createElement('div');
          particle.className = 'profile-particle';
          particle.textContent = particles[Math.floor(Math.random() * particles.length)];
          
          // Random horizontal position
          particle.style.left = Math.random() * 100 + '%';
          
          // Random animation duration
          const duration = 15 + Math.random() * 10;
          particle.style.animationDuration = duration + 's';
          
          // Random delay
          const delay = Math.random() * 5;
          particle.style.animationDelay = delay + 's';
          
          // Random size
          const scale = 0.8 + Math.random() * 0.7;
          particle.style.transform = `scale(${scale})`;
          
          container.appendChild(particle);
          
          // Recreate particle after animation
          particle.addEventListener('animationiteration', () => {
            particle.style.left = Math.random() * 100 + '%';
          });
        }, i * 150);
      }
    }
    
    // Initialize particles on load
    document.addEventListener('DOMContentLoaded', createProfileParticles);
  </script>
  <script src="../assets/js/loading-screen.js"></script>
  <script src="../assets/js/profile.js?v=<?= time() ?>"></script>
</body>
</html>
