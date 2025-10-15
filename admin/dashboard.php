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
      background: radial-gradient(900px 600px at 15% 15%, #1b378d66 0%, #0000 60%),
                  radial-gradient(800px 520px at 85% 80%, #1a3a9060 0%, #0000 60%),
                  linear-gradient(145deg, #08122e, #0c1f6f) !important;
      min-height: 100vh;
      color: #f6f9ff;
    }
    
    .admin-badge {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-block;
      margin-left: 0.5rem;
    }
    
    .topbar {
      background: rgba(15, 30, 90, 0.6);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(194, 213, 255, 0.2);
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .topbar .lp-brand-img {
      max-height: 50px;
      width: auto;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
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
      transition: opacity 0.3s;
    }
    
    .topbar .icon:hover {
      opacity: 0.8;
    }
    
    .admin-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }
    
    .admin-card {
      background: rgba(15, 30, 90, 0.6);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(194, 213, 255, 0.2);
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .section-title {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: #f5576c;
    }
    
    .metrics {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }
    
    .metric {
      text-align: center;
      padding: 1rem;
      background: rgba(245, 87, 108, 0.1);
      border: 1px solid rgba(245, 87, 108, 0.2);
      border-radius: 0.5rem;
    }
    
    .m-num {
      display: block;
      font-size: 2rem;
      font-weight: 700;
      color: #f5576c;
    }
    
    .m-label {
      display: block;
      font-size: 0.875rem;
      color: #cfe0ff;
      margin-top: 0.25rem;
    }
    
    .activity {
      list-style: none;
      padding: 0;
    }
    
    .activity li {
      padding: 0.75rem;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .activity li:last-child {
      border-bottom: none;
    }
    
    .activity li i {
      color: #f5576c;
    }
    
    .btn-admin {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 600;
      transition: transform 0.2s;
    }
    
    .btn-admin:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(245, 87, 108, 0.3);
    }
  </style>
</head>
<body style="background: #f8f9fa;">

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
          <button class="btn btn-outline-secondary" onclick="alert('Feature coming soon!')">
            <i class="bi bi-people"></i> Manage Users
          </button>
          <button class="btn btn-outline-secondary" onclick="alert('Feature coming soon!')">
            <i class="bi bi-shield-check"></i> Manage Moderators
          </button>
          <button class="btn btn-outline-secondary" onclick="alert('Feature coming soon!')">
            <i class="bi bi-flag"></i> View Reports
          </button>
          <button class="btn btn-outline-secondary" onclick="alert('Feature coming soon!')">
            <i class="bi bi-gear"></i> System Settings
          </button>
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
</body>
</html>
