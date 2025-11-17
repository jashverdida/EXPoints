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

// Check if user has mod role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mod') {
    // Redirect based on actual role
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
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
$username = $_SESSION['username'] ?? 'Moderator';
$user_email = $_SESSION['user_email'] ?? '';

// Get database connection
$db = getDBConnection();

// Get statistics
$total_users = 0;
$total_posts = 0;
$total_comments = 0;
$recent_posts = [];

if ($db) {
    try {
        // Get user count
        $result = $db->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_users = $row['count'];
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
        
        // Get recent posts for moderation - exclude banned users' posts
        $result = $db->query("SELECT p.id, p.game, p.title, p.content, p.username, p.likes, p.comments, p.created_at FROM posts p LEFT JOIN user_info ui ON p.username = ui.username WHERE (ui.is_banned IS NULL OR ui.is_banned = 0) ORDER BY p.created_at DESC LIMIT 10");
        if ($result) {
            while ($post = $result->fetch_assoc()) {
                $recent_posts[] = $post;
            }
        }
    } catch (Exception $e) {
        error_log("Moderator dashboard error: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Moderator Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Poppins Font */
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap");
    
    body {
      font-family: "Poppins", sans-serif !important;
      background: linear-gradient(135deg, #0a0a2e 0%, #16213e 50%, #0f3460 100%);
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
        radial-gradient(circle at 20% 30%, rgba(102, 126, 234, 0.15) 0%, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(118, 75, 162, 0.15) 0%, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(56, 160, 255, 0.1) 0%, transparent 50%);
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
      opacity: 0.1;
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
    
    .mod-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.35rem 1rem;
      border-radius: 1.5rem;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-block;
      margin-left: 0.5rem;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
      50% { transform: scale(1.05); box-shadow: 0 4px 25px rgba(102, 126, 234, 0.6); }
    }
    
    .topbar {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(102, 126, 234, 0.3);
      padding: 1rem 1.5rem;
      border-radius: 1rem;
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
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
      background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.2), transparent);
      animation: shine 3s infinite;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .topbar .lp-brand-img {
      max-height: 50px;
      width: auto;
      filter: drop-shadow(0 4px 8px rgba(102, 126, 234, 0.5));
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
      color: #667eea;
      transform: translateY(-2px);
    }
    
    .admin-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }
    
    .admin-card {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(102, 126, 234, 0.3);
      border-radius: 1.25rem;
      padding: 2rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
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
      background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
      transition: left 0.5s;
    }
    
    .admin-card:hover::before {
      left: 100%;
    }
    
    .admin-card:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: #667eea;
      box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4), 
                  0 0 40px rgba(102, 126, 234, 0.2);
    }
    
    /* Disable hover effect on center card (the one that spans 2 columns) */
    .admin-card[style*="grid-column: span 2"]:hover {
      transform: none;
      border-color: rgba(102, 126, 234, 0.3);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .admin-card[style*="grid-column: span 2"]:hover::before {
      left: -100%;
    }
    
    .section-title {
      font-size: 1.5rem;
      font-weight: 800;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, #667eea, #764ba2);
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
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
      border: 2px solid rgba(102, 126, 234, 0.3);
      border-radius: 1rem;
      transition: all 0.3s;
    }
    
    .metric:hover {
      transform: scale(1.05);
      border-color: #667eea;
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    }
    
    .m-num {
      display: block;
      font-size: 2.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, #667eea, #764ba2);
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
      border-bottom: 1px solid rgba(102, 126, 234, 0.2);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      transition: all 0.3s;
    }
    
    .activity li:hover {
      background: rgba(102, 126, 234, 0.1);
      padding-left: 1.5rem;
    }
    
    .activity li:last-child {
      border-bottom: none;
    }
    
    .activity li i {
      color: #667eea;
      font-size: 1.25rem;
    }
    
    .btn-mod {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-mod:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
    }
    
    /* Search Bar Styles */
    .search-container {
      margin-bottom: 1.5rem;
    }
    
    .search-box {
      display: flex;
      gap: 1rem;
      align-items: center;
      background: rgba(15, 30, 90, 0.6);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(194, 213, 255, 0.2);
      padding: 1rem;
      border-radius: 0.75rem;
    }
    
    .search-input {
      flex: 1;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
    }
    
    .search-input::placeholder {
      color: rgba(255, 255, 255, 0.5);
    }
    
    .search-input:focus {
      outline: none;
      border-color: #38a0ff;
      background: rgba(255, 255, 255, 0.15);
    }
    
    .search-select {
      background: rgba(56, 160, 255, 0.2);
      border: 1px solid #38a0ff;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      min-width: 150px;
    }
    
    .search-select:focus {
      outline: none;
      border-color: #38a0ff;
    }
    
    /* Table Styles */
    .table {
      color: #f6f9ff !important;
      background: transparent !important;
      --bs-table-bg: transparent !important;
      --bs-table-striped-bg: transparent !important;
      --bs-table-hover-bg: rgba(37, 99, 235, 0.15) !important;
    }
    
    .table thead th {
      background: rgba(10, 20, 50, 0.8) !important;
      border: 1px solid rgba(59, 130, 246, 0.4) !important;
      color: #60a5fa !important;
      font-weight: 700;
      padding: 1rem;
      text-transform: uppercase;
      font-size: 0.875rem;
      letter-spacing: 0.5px;
    }
    
    .table tbody tr {
      background: rgba(10, 20, 40, 0.6) !important;
      border: 1px solid rgba(59, 130, 246, 0.3) !important;
      transition: all 0.3s;
    }
    
    .table tbody tr:hover {
      background: rgba(30, 58, 138, 0.5) !important;
      transform: translateX(5px);
    }
    
    .table tbody td {
      padding: 1rem;
      border: 1px solid rgba(59, 130, 246, 0.3) !important;
      vertical-align: middle;
      color: #f6f9ff !important;
      background: transparent !important;
    }
    
    .table-responsive {
      background: rgba(5, 10, 25, 0.5) !important;
      border-radius: 1rem;
      padding: 1rem;
      border: 2px solid rgba(59, 130, 246, 0.4);
    }
    
    /* Remove any white backgrounds from Bootstrap */
    .table-hover tbody tr:hover td,
    .table-hover tbody tr:hover th {
      background-color: rgba(30, 58, 138, 0.5) !important;
      color: #f6f9ff !important;
    }
    
    /* Modal Styles */
    .modal-content {
      background: linear-gradient(180deg, #0f1e5ae6, #0a1344e6);
      border: 1.5px solid #c2d5ff;
      color: #f6f9ff;
    }
    
    .modal-header {
      border-bottom: 1px solid rgba(194, 213, 255, 0.2);
    }
    
    .modal-footer {
      border-top: 1px solid rgba(194, 213, 255, 0.2);
    }
    
    .modal-title {
      color: #38a0ff;
      font-weight: 700;
    }
    
    .btn-close {
      filter: invert(1) grayscale(100%) brightness(200%);
    }
    
    /* Post Card Styles for Modal */
    .card-post {
      position: relative;
      overflow: visible;
    }
    
    .card-post .top {
      padding: 1.5rem;
    }
    
    .card-post .title {
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
      margin: 0;
    }
    
    .card-post .handle {
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.95rem;
    }
    
    .card-post .actions {
      display: flex;
      gap: 2rem;
      padding: 1rem 1.5rem;
      border-top: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    .card-post .actions .a {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: rgba(255, 255, 255, 0.7);
      font-size: 1rem;
      cursor: default;
    }
    
    .card-post .actions .a i {
      font-size: 1.25rem;
    }
    
    .card-post .actions .a b {
      font-weight: 600;
      color: rgba(255, 255, 255, 0.9);
    }
    
    /* Avatar Styles */
    .avatar-lg {
      width: 75px;
      height: 75px;
      border-radius: 50%;
      background: conic-gradient(from 0deg, #667eea 0%, #764ba2 25%, #667eea 50%, #764ba2 75%, #667eea 100%);
      padding: 3px;
      display: inline-block;
      position: relative;
      margin-right: 1rem;
    }
    
    .avatar-lg::before {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 50%;
      background: conic-gradient(from 0deg, #667eea 0%, #764ba2 25%, #667eea 50%, #764ba2 75%, #667eea 100%);
      animation: rotate 3s linear infinite;
      z-index: 1;
    }
    
    .avatar-lg::after {
      content: '';
      position: absolute;
      inset: 2px;
      background: #0a0a2e;
      border-radius: 50%;
      z-index: 2;
    }
    
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .post-detail-card {
      background: rgba(15, 30, 90, 0.4);
      border: 1px solid rgba(194, 213, 255, 0.2);
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1rem;
    }
    
    .post-meta {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
      font-size: 0.875rem;
      color: #cfe0ff;
    }
    
    .post-meta-item {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    
    .post-content-box {
      background: rgba(0, 0, 0, 0.2);
      padding: 1rem;
      border-radius: 0.5rem;
      margin-top: 1rem;
      line-height: 1.6;
    }
    
    /* Post Review Modal - Make it wider */
    #postModal .modal-dialog {
      max-width: 900px !important;
    }
    
    #postModal .card-post {
      max-width: 100% !important;
    }
  </style>
</head>
<body>

  <!-- Animated Floating Particles -->
  <div class="particles-bg" id="particlesBg"></div>

  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="../user/dashboard.php" class="lp-brand" aria-label="Dashboard">
        <img src="../assets/img/EXPoints Logo.png" alt="EXPoints" class="lp-brand-img">
      </a>
      <div class="right">
        <span style="color: white; font-weight: 600;">
          <?php echo htmlspecialchars($username); ?>
          <span class="mod-badge">MODERATOR</span>
        </span>
        <a href="../user/dashboard.php" class="icon" title="User Feed"><i class="bi bi-house-door"></i></a>
        <a href="../logout.php" class="icon" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <div class="row mb-4">
      <div class="col">
        <h1 style="color: #38a0ff; font-weight: 700;">
          <i class="bi bi-shield-check"></i> Moderator Dashboard
        </h1>
        <p style="color: #cfe0ff;">Manage content and help maintain the community</p>
      </div>
    </div>

    <div class="admin-grid">
      <section class="admin-card">
        <h2 class="section-title">
          <i class="bi bi-bar-chart"></i> Statistics
        </h2>
        <div class="metrics">
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
            <span class="m-num">0</span>
            <span class="m-label">Flagged Content</span>
          </div>
        </div>
      </section>

      <section class="admin-card" style="grid-column: span 2;">
        <h2 class="section-title">
          <i class="bi bi-clock-history"></i> Recent Posts
        </h2>
        
        <!-- Search Bar -->
        <div class="search-container">
          <div class="search-box">
            <input type="text" id="searchInput" class="search-input" placeholder="Search posts...">
            <select id="searchType" class="search-select">
              <option value="title">Search by Title</option>
              <option value="author">Search by Author</option>
            </select>
          </div>
        </div>
        
        <?php if (empty($recent_posts)): ?>
          <p class="text-center py-4" style="color: #cfe0ff;">No posts yet</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover" id="postsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Author</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_posts as $post): ?>
                  <tr id="post-row-<?php echo $post['id']; ?>">
                    <td>#<?php echo htmlspecialchars($post['id']); ?></td>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['username']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-mod" onclick="viewPost(<?php echo $post['id']; ?>)" title="View Post">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="flagForBan(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['username']); ?>')" title="Flag for Ban Review">
                          <i class="bi bi-flag-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <section class="admin-card">
        <h2 class="section-title">
          <i class="bi bi-tools"></i> Quick Actions
        </h2>
        <div class="d-grid gap-2">
          <a href="../user/dashboard.php" class="btn btn-mod">
            <i class="bi bi-compass"></i> View User Feed
          </a>
          <a href="ban-reviews.php" class="btn btn-outline-secondary">
            <i class="bi bi-flag"></i> Review Reports
          </a>
        </div>
      </section>
    </div>
  </main>

  <!-- Post Detail Modal -->
  <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content" style="background: linear-gradient(135deg, rgba(10, 10, 30, 0.98), rgba(22, 33, 62, 0.98)); backdrop-filter: blur(20px); border: 2px solid rgba(59, 130, 246, 0.5);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.3);">
          <h5 class="modal-title" id="postModalLabel" style="color: #60a5fa; font-weight: 700;">
            <i class="bi bi-eye-fill"></i> Post Review
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="postModalBody" style="padding: 2rem;">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 2px solid rgba(59, 130, 246, 0.3);">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Hide Post Modal -->
  <div class="modal fade" id="hidePostModal" tabindex="-1" aria-labelledby="hidePostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.95), rgba(37, 99, 235, 0.95)); backdrop-filter: blur(20px); border: 2px solid rgba(251, 191, 36, 0.5);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(251, 191, 36, 0.3);">
          <h5 class="modal-title" id="hidePostModalLabel" style="color: #fbbf24; font-weight: 700;">
            <i class="bi bi-eye-slash-fill"></i> Hide Post
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p style="color: #f6f9ff; margin-bottom: 1rem;">
            Are you sure you want to hide this post from all users? This action will remove it from all feeds and dashboards.
          </p>
          <div class="mb-3">
            <label for="hideReason" class="form-label" style="color: #fbbf24; font-weight: 600;">Reason (Optional)</label>
            <textarea class="form-control" id="hideReason" rows="3" placeholder="Enter reason for hiding this post..." style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(251, 191, 36, 0.3); color: white;"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 2px solid rgba(251, 191, 36, 0.3);">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" onclick="confirmHidePost()" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); border: none; font-weight: 600;">
            <i class="bi bi-eye-slash"></i> Hide Post
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Flag for Ban Modal -->
  <div class="modal fade" id="flagBanModal" tabindex="-1" aria-labelledby="flagBanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.95), rgba(37, 99, 235, 0.95)); backdrop-filter: blur(20px); border: 2px solid rgba(239, 68, 68, 0.5);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(239, 68, 68, 0.3);">
          <h5 class="modal-title" id="flagBanModalLabel" style="color: #ef4444; font-weight: 700;">
            <i class="bi bi-flag-fill"></i> Flag User for Ban Review
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p style="color: #f6f9ff; margin-bottom: 1rem;">
            You are about to flag user <strong id="flagUsername" style="color: #ef4444;"></strong> for admin ban review.
          </p>
          <div class="alert alert-warning" style="background: rgba(251, 191, 36, 0.2); border: 1px solid rgba(251, 191, 36, 0.5); color: #fbbf24;">
            <i class="bi bi-exclamation-triangle"></i> This will notify administrators to review this user's account for potential ban.
          </div>
          <div class="mb-3">
            <label for="banReason" class="form-label" style="color: #ef4444; font-weight: 600;">Reason (Required) *</label>
            <textarea class="form-control" id="banReason" rows="4" placeholder="Explain why this user should be reviewed for ban..." required style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(239, 68, 68, 0.3); color: white;"></textarea>
            <small style="color: rgba(255, 255, 255, 0.7);">Please provide detailed reasons for the ban review.</small>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 2px solid rgba(239, 68, 68, 0.3);">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmFlagBan()" style="background: linear-gradient(135deg, #ef4444, #dc2626); border: none; font-weight: 600;">
            <i class="bi bi-flag-fill"></i> Flag for Ban Review
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Store all posts data for search
    const allPosts = <?php echo json_encode($recent_posts); ?>;
    
    function viewPost(postId) {
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('postModal'));
      modal.show();
      
      // Fetch post details
      fetch(`../api/get_post.php?id=${postId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          console.log('API Response:', data); // Debug log
          if (data.success) {
            displayPostDetails(data.post);
          } else {
            document.getElementById('postModalBody').innerHTML = `
              <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> Failed to load post details
                <br><small>${data.message || 'Unknown error'}</small>
                ${data.error ? '<br><small>' + data.error + '</small>' : ''}
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('postModalBody').innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle"></i> Error loading post
              <br><small>${error.message}</small>
            </div>
          `;
        });
    }
    
    function displayPostDetails(post) {
      const modalBody = document.getElementById('postModalBody');
      
      // Calculate timestamp
      const now = new Date();
      const postDate = new Date(post.created_at);
      const diffMs = now - postDate;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);
      
      let timestamp;
      if (diffMins < 1) {
        timestamp = 'Just now';
      } else if (diffMins < 60) {
        timestamp = `${diffMins}m ago`;
      } else if (diffHours < 24) {
        timestamp = `${diffHours}h ago`;
      } else if (diffDays < 7) {
        timestamp = `${diffDays}d ago`;
      } else {
        timestamp = postDate.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: postDate.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
        });
      }
      
      // Get profile picture
      const profilePicture = post.profile_picture || '../assets/img/default-avatar.png';
      
      modalBody.innerHTML = `
        <article class="card-post" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.3), rgba(37, 99, 235, 0.2)); backdrop-filter: blur(15px); border: 2px solid rgba(59, 130, 246, 0.4); border-radius: 1rem; overflow: visible; position: relative; margin: 0;">
          <div class="top">
            <div class="row g-0">
              <div class="col-auto">
                <div class="avatar-lg" style="cursor: default;">
                  <img src="${escapeHtml(profilePicture)}" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
                </div>
              </div>
              <div class="col">
                <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 0.25rem;">
                  <h2 class="title" style="margin: 0; color: white; font-size: 1.5rem; font-weight: 700;">${escapeHtml(post.title)}</h2>
                  <span class="post-timestamp" style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.5); font-weight: 400;">${timestamp}</span>
                </div>
                <div class="handle mb-3" style="color: rgba(255, 255, 255, 0.6); font-size: 0.95rem;">@${escapeHtml(post.username)}</div>
                <p class="mb-0" style="color: rgba(255, 255, 255, 0.9); line-height: 1.6; white-space: pre-wrap;">${escapeHtml(post.content)}</p>
              </div>
            </div>
          </div>
        </article>
      `;
    }
    
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    function searchPosts() {
      const searchInput = document.getElementById('searchInput').value.toLowerCase();
      const searchType = document.getElementById('searchType').value;
      const tableBody = document.querySelector('#postsTable tbody');
      
      if (!searchInput.trim()) {
        // Show all posts if search is empty
        displayAllPosts();
        return;
      }
      
      // Filter posts based on search type
      const filteredPosts = allPosts.filter(post => {
        if (searchType === 'title') {
          return post.title.toLowerCase().includes(searchInput);
        } else if (searchType === 'author') {
          return post.username.toLowerCase().includes(searchInput);
        }
        return false;
      });
      
      // Display filtered posts
      if (filteredPosts.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center py-4" style="color: #cfe0ff;">
              <i class="bi bi-search"></i> No posts found matching "${searchInput}"
            </td>
          </tr>
        `;
      } else {
        tableBody.innerHTML = filteredPosts.map(post => `
          <tr id="post-row-${post.id}">
            <td>#${post.id}</td>
            <td>${escapeHtml(post.title)}</td>
            <td>${escapeHtml(post.username)}</td>
            <td>${new Date(post.created_at).toLocaleDateString('en-US', {
              month: 'short',
              day: 'numeric',
              year: 'numeric'
            })}</td>
            <td>
              <div class="btn-group" role="group">
                <button class="btn btn-sm btn-mod" onclick="viewPost(${post.id})" title="View Post">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="flagForBan(${post.id}, '${escapeHtml(post.username)}')" title="Flag for Ban Review">
                  <i class="bi bi-flag-fill"></i>
                </button>
              </div>
            </td>
          </tr>
        `).join('');
      }
    }
    
    function displayAllPosts() {
      const tableBody = document.querySelector('#postsTable tbody');
      tableBody.innerHTML = allPosts.map(post => `
        <tr id="post-row-${post.id}">
          <td>#${post.id}</td>
          <td>${escapeHtml(post.title)}</td>
          <td>${escapeHtml(post.username)}</td>
          <td>${new Date(post.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
          })}</td>
          <td>
            <div class="btn-group" role="group">
              <button class="btn btn-sm btn-mod" onclick="viewPost(${post.id})" title="View Post">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn btn-sm btn-danger" onclick="flagForBan(${post.id}, '${escapeHtml(post.username)}')" title="Flag for Ban Review">
                <i class="bi bi-flag-fill"></i>
              </button>
            </div>
          </td>
        </tr>
      `).join('');
    }
    
    // Enable real-time search on input
    document.getElementById('searchInput').addEventListener('input', function() {
      searchPosts();
    });
    
    // Also update search when filter type changes
    document.getElementById('searchType').addEventListener('change', function() {
      searchPosts();
    });
    
    // Create floating emoji particles
    function createParticles() {
      const particlesBg = document.getElementById('particlesBg');
      const emojis = ['🛡️', '⚔️', '👁️', '🔍', '⭐', '🎯'];
      const particleCount = 15;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        particle.textContent = emojis[Math.floor(Math.random() * emojis.length)];
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 5 + 's';
        particle.style.animationDuration = (15 + Math.random() * 10) + 's';
        particlesBg.appendChild(particle);
      }
    }
    
    createParticles();
    
    // Moderation functions
    let currentPostId = null;
    let currentUsername = null;
    
    function hidePost(postId) {
      currentPostId = postId;
      const modal = new bootstrap.Modal(document.getElementById('hidePostModal'));
      modal.show();
    }
    
    function confirmHidePost() {
      const reason = document.getElementById('hideReason').value;
      
      fetch('../api/moderate_post.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          post_id: currentPostId,
          action: 'hide',
          reason: reason
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Hide the modal
          bootstrap.Modal.getInstance(document.getElementById('hidePostModal')).hide();
          
          // Show success message
          showNotification('Post hidden successfully', 'success');
          
          // Remove or fade out the post row
          const row = document.getElementById(`post-row-${currentPostId}`);
          if (row) {
            row.style.opacity = '0.5';
            row.style.textDecoration = 'line-through';
          }
          
          // Clear the reason field
          document.getElementById('hideReason').value = '';
        } else {
          showNotification('Failed to hide post: ' + data.error, 'danger');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error hiding post', 'danger');
      });
    }
    
    function flagForBan(postId, username) {
      currentPostId = postId;
      currentUsername = username;
      document.getElementById('flagUsername').textContent = username;
      document.getElementById('banReason').value = '';
      
      const modal = new bootstrap.Modal(document.getElementById('flagBanModal'));
      modal.show();
    }
    
    function confirmFlagBan() {
      const reason = document.getElementById('banReason').value.trim();
      
      if (!reason) {
        showNotification('Please provide a reason for the ban review', 'warning');
        return;
      }
      
      fetch('../api/moderate_post.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          post_id: currentPostId,
          action: 'flag_ban',
          reason: reason
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Hide the modal
          bootstrap.Modal.getInstance(document.getElementById('flagBanModal')).hide();
          
          // Show success message
          showNotification(`User ${currentUsername} flagged for ban review`, 'success');
          
          // Clear the reason field
          document.getElementById('banReason').value = '';
        } else {
          showNotification('Failed to flag user: ' + data.error, 'danger');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error flagging user for ban review', 'danger');
      });
    }
    
    function showNotification(message, type) {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type} notification-toast`;
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
      `;
      notification.innerHTML = `
        <div class="d-flex align-items-center">
          <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
          <span>${message}</span>
        </div>
      `;
      
      document.body.appendChild(notification);
      
      // Remove after 3 seconds
      setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
      }, 3000);
    }
  </script>
  <style>
    @keyframes slideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes slideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(400px);
        opacity: 0;
      }
    }
  </style>
</body>
</html>
