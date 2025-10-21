<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../user/login.php?error=' . urlencode('Please login to continue'));
    exit();
}

// Check if user has admin role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect based on actual role
    if ($_SESSION['user_role'] === 'mod') {
        header('Location: ../mod/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

// Simple database connection function
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

// Get user info
$username = $_SESSION['username'] ?? 'Admin';
$user_email = $_SESSION['user_email'] ?? '';

// Get database connection
$db = getDBConnection();

// Get statistics
$total_users = 0;
$total_posts = 0;
$total_comments = 0;
$total_admins = 0;
$total_mods = 0;

if ($db) {
    try {
        // Get user count
        $result = $db->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_users = $row['count'];
        }
        
        // Get admin count
        $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_admins = $row['count'];
        }
        
        // Get mod count
        $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'mod'");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_mods = $row['count'];
        }
        
        // Get post count
        $result = $db->query("SELECT COUNT(*) as count FROM posts");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_posts = $row['count'];
        }
        
        // Get comment count
        $result = $db->query("SELECT COUNT(*) as count FROM comments");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_comments = $row['count'];
        }
    } catch (Exception $e) {
        error_log("Admin dashboard error: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Poppins Font */
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap");
    
    body {
      font-family: "Poppins", sans-serif !important;
      background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3d 50%, #0d1b3a 100%);
      min-height: 100vh;
      color: #f6f9ff;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Animated Background Particles */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 30%, rgba(30, 58, 138, 0.3) 0%, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(37, 99, 235, 0.2) 0%, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.15) 0%, transparent 50%);
      animation: float 20s ease-in-out infinite;
      pointer-events: none;
      z-index: 0;
    }
    
    @keyframes float {
      0%, 100% {
        transform: translate(0, 0) scale(1);
        opacity: 1;
      }
      50% {
        transform: translate(-5%, -5%) scale(1.05);
        opacity: 0.8;
      }
    }
    
    /* Floating Emoji Particles */
    .particles-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
      overflow: hidden;
    }
    
    .particle {
      position: absolute;
      font-size: 2rem;
      opacity: 0.08;
      filter: brightness(0) saturate(100%) invert(100%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(100%) contrast(100%);
      animation: floatEmoji 25s infinite ease-in-out;
    }
    
    @keyframes floatEmoji {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      25% { transform: translateY(-30px) rotate(90deg); }
      50% { transform: translateY(-60px) rotate(180deg); }
      75% { transform: translateY(-30px) rotate(270deg); }
    }
    
    .container-xl {
      position: relative;
      z-index: 1;
    }
    .admin-badge {
      background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%);
      color: white;
      padding: 0.35rem 1rem;
      border-radius: 1.5rem;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-block;
      margin-left: 0.5rem;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.6);
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.6); }
      50% { transform: scale(1.05); box-shadow: 0 4px 25px rgba(59, 130, 246, 0.8); }
    } 50% { transform: scale(1.05); box-shadow: 0 4px 25px rgba(245, 87, 108, 0.7); }
    }
    
    .topbar {
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.3), rgba(37, 99, 235, 0.2));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(59, 130, 246, 0.4);
      padding: 1rem 1.5rem;
      border-radius: 1rem;
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    .topbar::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
      animation: shine 3s infinite;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .topbar .lp-brand-img {
      max-height: 50px;
      width: auto;
      filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.6));
      transition: transform 0.3s;
    }
    
    .topbar .lp-brand-img:hover {
      transform: scale(1.05);
    }
    
    .topbar .right {
      display: flex;
      gap: 1rem;
      align-items: center;
    }
    
    .topbar .icon {
      color: white;
      font-size: 1.25rem;
      text-decoration: none;
      transition: all 0.3s;
      position: relative;
    }
    
    .topbar .icon:hover {
      color: #ef4444;
      transform: translateY(-2px);
    }
    
    .admin-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }
    
    .admin-card {
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.2), rgba(37, 99, 235, 0.15));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1.25rem;
      padding: 2rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
    }
    
    .admin-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .admin-card:hover::before {
      left: 100%;
    }
    
    .admin-card:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: #ef4444;
      box-shadow: 0 20px 60px rgba(239, 68, 68, 0.5), 
                  0 0 40px rgba(239, 68, 68, 0.3);
    }
    
    .section-title {
      font-size: 1.5rem;
      font-weight: 800;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, #60a5fa, #3b82f6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      display: inline-block;
    }
    
    .metrics {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }
    
    .metric {
      text-align: center;
      padding: 1.5rem;
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.3), rgba(37, 99, 235, 0.2));
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1rem;
      transition: all 0.3s;
    }
    
    .metric:hover {
      transform: scale(1.05);
      border-color: #ef4444;
      box-shadow: 0 8px 24px rgba(239, 68, 68, 0.5);
    }
    
    .m-num {
      display: block;
      font-size: 2.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, #60a5fa, #3b82f6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .m-label {
      display: block;
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.7);
      margin-top: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .activity {
      list-style: none;
      padding: 0;
    }
    
    .activity li {
      padding: 1rem;
      border-bottom: 1px solid rgba(59, 130, 246, 0.3);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      transition: all 0.3s;
    }
    
    .activity li:hover {
      background: rgba(239, 68, 68, 0.1);
      padding-left: 1.5rem;
    }
    
    .activity li:last-child {
      border-bottom: none;
    }
    
    .activity li i {
      color: #ef4444;
      font-size: 1.25rem;
    }
    
    .btn-admin {
      background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.5);
    }
    
    .btn-admin:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(239, 68, 68, 0.6);
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .btn-admin:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(245, 87, 108, 0.3);
    }
  </style>
</head>
<body>

  <div class="particles-bg" id="particlesBg"></div>

  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="../user/dashboard.php" class="lp-brand" aria-label="Dashboard">
        <img src="../assets/img/EXPoints Logo.png" alt="EXPoints" class="lp-brand-img">
      </a>
      <div class="right">
        <span style="color: white; font-weight: 600;">
          <?php echo htmlspecialchars($username); ?>
          <span class="admin-badge">ADMIN</span>
        </span>
        <a href="../user/dashboard.php" class="icon" title="User Feed"><i class="bi bi-house-door"></i></a>
        <a href="index.php" class="icon" title="Old Admin Panel"><i class="bi bi-speedometer"></i></a>
        <a href="../logout.php" class="icon" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <div class="row mb-4">
      <div class="col">
        <h1 style="color: #f5576c; font-weight: 700;">
          <i class="bi bi-shield-fill-check"></i> Admin Dashboard
        </h1>
        <p class="text-muted">Full system control and management</p>
      </div>
    </div>

    <div class="admin-grid">
      <section class="admin-card" style="grid-column: span 2;">
        <h2 class="section-title">
          <i class="bi bi-bar-chart-fill"></i> System Overview
        </h2>
        <div class="metrics" style="grid-template-columns: repeat(3, 1fr);">
          <div class="metric">
            <span class="m-num"><?php echo number_format($total_users); ?></span>
            <span class="m-label">Total Users</span>
          </div>
          <div class="metric">
            <span class="m-num"><?php echo number_format($total_posts); ?></span>
            <span class="m-label">Total Posts</span>
          </div>
          <div class="metric">
            <span class="m-num"><?php echo number_format($total_comments); ?></span>
            <span class="m-label">Total Comments</span>
          </div>
          <div class="metric">
            <span class="m-num"><?php echo number_format($total_admins); ?></span>
            <span class="m-label">Administrators</span>
          </div>
          <div class="metric">
            <span class="m-num"><?php echo number_format($total_mods); ?></span>
            <span class="m-label">Moderators</span>
          </div>
          <div class="metric">
            <span class="m-num">0</span>
            <span class="m-label">Reports</span>
          </div>
        </div>
      </section>

      <section class="admin-card">
        <h2 class="section-title">
          <i class="bi bi-clock-history"></i> Recent Activity
        </h2>
        <ul class="activity">
          <li><i class="bi bi-person-check"></i> System initialized successfully</li>
          <li><i class="bi bi-database"></i> Database connected</li>
          <li><i class="bi bi-shield-check"></i> Admin logged in</li>
        </ul>
      </section>

      <section class="admin-card">
        <h2 class="section-title">
          <i class="bi bi-tools"></i> Admin Tools
        </h2>
        <div class="d-grid gap-2">
          <a href="../user/dashboard.php" class="btn btn-admin">
            <i class="bi bi-compass"></i> View User Feed
          </a>
          <a href="ban-appeals.php" class="btn btn-admin" style="background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
            <i class="bi bi-gavel"></i> Ban Appeals
          </a>
          <a href="manage-users.php" class="btn btn-outline-secondary">
            <i class="bi bi-people"></i> Manage Users
          </a>
          <a href="manage-moderators.php" class="btn btn-outline-secondary">
            <i class="bi bi-shield-check"></i> Manage Moderators
          </a>
        </div>
      </section>

      <section class="admin-card" style="grid-column: span 2;">
        <h2 class="section-title">
          <i class="bi bi-info-circle"></i> Quick Info
        </h2>
        <div class="row">
          <div class="col-md-6">
            <h5>System Status</h5>
            <ul>
              <li><i class="bi bi-check-circle-fill text-success"></i> Database: Connected</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Authentication: Active</li>
              <li><i class="bi bi-check-circle-fill text-success"></i> Sessions: Working</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h5>Access Control</h5>
            <ul>
              <li><strong>Admins:</strong> Full system access</li>
              <li><strong>Moderators:</strong> Content moderation</li>
              <li><strong>Users:</strong> Post & comment</li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Create floating emoji particles
    function createParticles() {
      const particlesBg = document.getElementById('particlesBg');
      const emojis = ['👑', '🔐', '🛡️', '⚙️', '✨', '📊'];
      const particleCount = 15;

      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.textContent = emojis[Math.floor(Math.random() * emojis.length)];
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 5 + 's';
        particle.style.animationDuration = (15 + Math.random() * 10) + 's';
        particlesBg.appendChild(particle);
      }
    }

    // Initialize particles on page load
    createParticles();
  </script>
</body>
</html>
