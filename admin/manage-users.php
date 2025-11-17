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
$username = $_SESSION['username'] ?? 'Administrator';

// Get database connection
$db = getDBConnection();

// Fetch banned users
$banned_users = [];
$total_banned = 0;

if ($db) {
    $query = "SELECT ui.*, u.email 
              FROM user_info ui
              JOIN users u ON ui.user_id = u.id
              WHERE ui.is_banned = 1
              ORDER BY ui.banned_at DESC";
    $result = $db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $banned_users[] = $row;
        }
        $total_banned = count($banned_users);
    }
    
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Banned Users - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
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
        radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.15) 0%, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(239, 68, 68, 0.15) 0%, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(96, 165, 250, 0.1) 0%, transparent 50%);
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
    
    .container-xl {
      position: relative;
      z-index: 1;
    }
    
    .topbar {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(29, 78, 216, 0.2));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(59, 130, 246, 0.3);
      padding: 1rem 1.5rem;
      border-radius: 1rem;
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2);
    }
    
    .admin-badge {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      padding: 0.35rem 1rem;
      border-radius: 1.5rem;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-block;
      margin-left: 0.5rem;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); }
      50% { transform: scale(1.05); box-shadow: 0 4px 25px rgba(239, 68, 68, 0.6); }
    }
    
    .page-header {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(29, 78, 216, 0.2));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1.25rem;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2);
      position: relative;
      overflow: hidden;
    }
    
    .page-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.2), transparent);
      animation: shine 3s infinite;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .page-header h1 {
      font-size: 2rem;
      font-weight: 800;
      color: #60a5fa;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .page-header p {
      margin: 0.5rem 0 0 0;
      color: rgba(255, 255, 255, 0.8);
    }
    
    .stats-bar {
      display: flex;
      gap: 2rem;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(59, 130, 246, 0.3);
    }
    
    .stat-item {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    
    .stat-num {
      font-size: 2rem;
      font-weight: 800;
      color: #3b82f6;
      line-height: 1;
    }
    
    .stat-label {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .user-card {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(29, 78, 216, 0.1));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(59, 130, 246, 0.3);
      border-radius: 1.25rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }
    
    .user-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: linear-gradient(180deg, #ef4444, #dc2626);
    }
    
    .user-card:hover {
      transform: translateY(-5px);
      border-color: #3b82f6;
      box-shadow: 0 12px 48px rgba(59, 130, 246, 0.3);
    }
    
    .user-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .user-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: 800;
      color: white;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
      border: 3px solid rgba(239, 68, 68, 0.3);
    }
    
    .user-avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .user-details h3 {
      font-size: 1.25rem;
      font-weight: 700;
      color: #60a5fa;
      margin: 0;
    }
    
    .user-details .meta {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      display: flex;
      gap: 1rem;
      margin-top: 0.25rem;
      flex-wrap: wrap;
    }
    
    .banned-badge {
      padding: 0.35rem 1rem;
      border-radius: 1.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }
    
    .user-body {
      margin-bottom: 1rem;
    }
    
    .ban-info-box {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 0.75rem;
      padding: 1rem;
      margin-top: 1rem;
    }
    
    .ban-label {
      font-size: 0.875rem;
      color: #fca5a5;
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .ban-text {
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.6;
    }
    
    .user-actions {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .btn-unban {
      flex: 1;
      background: linear-gradient(135deg, #10b981, #059669);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .btn-unban:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(16, 185, 129, 0.5);
      background: linear-gradient(135deg, #059669, #047857);
    }
    
    .btn-view-profile {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .btn-view-profile:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(59, 130, 246, 0.5);
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }
    
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(29, 78, 216, 0.1));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(59, 130, 246, 0.3);
      border-radius: 1.25rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .empty-state i {
      font-size: 4rem;
      color: #10b981;
      margin-bottom: 1rem;
    }
    
    .empty-state h3 {
      color: #60a5fa;
      margin-bottom: 0.5rem;
    }
    
    .empty-state p {
      color: rgba(255, 255, 255, 0.6);
    }
    
    .back-btn {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(29, 78, 216, 0.2));
      border: 2px solid rgba(59, 130, 246, 0.4);
      color: #60a5fa;
      padding: 0.5rem 1rem;
      border-radius: 0.75rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .back-btn:hover {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(29, 78, 216, 0.3));
      border-color: #3b82f6;
      transform: translateY(-2px);
      color: #93c5fd;
    }
    
    /* ================= MOBILE RESPONSIVE FIXES ================= */
    
    /* Mobile Fix for Manage Users Page */
    @media (max-width: 768px) {
      /* Topbar adjustments */
      .topbar {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
        align-items: flex-start;
      }
      
      .topbar h1 {
        font-size: 1.1rem;
      }
      
      .admin-badge {
        display: block;
        margin-left: 0;
        margin-top: 0.5rem;
        font-size: 0.75rem;
        padding: 0.3rem 0.75rem;
      }
      
      .back-btn {
        padding: 0.5rem 0.85rem;
        font-size: 0.9rem;
      }
      
      /* Page header */
      .page-header {
        padding: 1.5rem;
      }
      
      .page-header h1 {
        font-size: 1.6rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }
      
      .page-header p {
        font-size: 0.95rem;
      }
      
      /* Stats bar - stack vertically */
      .stats-bar {
        flex-direction: column;
        gap: 1rem;
        padding-top: 1rem;
        margin-top: 1rem;
      }
      
      .stat-item {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: rgba(59, 130, 246, 0.1);
        border-radius: 0.5rem;
      }
      
      .stat-num {
        font-size: 1.75rem;
      }
      
      .stat-label {
        font-size: 0.8rem;
      }
      
      /* User cards */
      .user-card {
        padding: 1.25rem;
        margin-bottom: 1.25rem;
      }
      
      .user-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .user-info {
        width: 100%;
      }
      
      .user-avatar {
        width: 55px;
        height: 55px;
        font-size: 1.3rem;
      }
      
      .user-details h3 {
        font-size: 1.15rem;
      }
      
      .user-details .meta {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
      }
      
      .banned-badge {
        padding: 0.3rem 0.85rem;
        font-size: 0.825rem;
      }
      
      /* Ban info box */
      .ban-info-box {
        padding: 0.85rem;
      }
      
      .ban-label {
        font-size: 0.825rem;
      }
      
      .ban-text {
        font-size: 0.9rem;
      }
      
      /* User actions - stack buttons */
      .user-actions {
        flex-direction: column;
        gap: 0.75rem;
      }
      
      .btn-unban,
      .btn-view-profile {
        width: 100%;
        padding: 0.7rem 1.25rem;
        font-size: 0.95rem;
      }
      
      /* Empty state */
      .empty-state {
        padding: 3rem 1.5rem;
      }
      
      .empty-state i {
        font-size: 3.5rem;
      }
      
      .empty-state h3 {
        font-size: 1.3rem;
      }
      
      .empty-state p {
        font-size: 0.95rem;
      }
    }
    
    @media (max-width: 480px) {
      /* Further compress for small phones */
      .topbar {
        padding: 0.85rem;
      }
      
      .topbar h1 {
        font-size: 1rem;
      }
      
      .admin-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.65rem;
      }
      
      .back-btn {
        padding: 0.45rem 0.75rem;
        font-size: 0.85rem;
      }
      
      .page-header {
        padding: 1.25rem;
      }
      
      .page-header h1 {
        font-size: 1.4rem;
      }
      
      .page-header p {
        font-size: 0.9rem;
      }
      
      .stat-num {
        font-size: 1.5rem;
      }
      
      .stat-label {
        font-size: 0.75rem;
      }
      
      .user-card {
        padding: 1rem;
      }
      
      .user-avatar {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
      }
      
      .user-details h3 {
        font-size: 1.05rem;
      }
      
      .user-details .meta {
        font-size: 0.825rem;
      }
      
      .banned-badge {
        padding: 0.25rem 0.75rem;
        font-size: 0.8rem;
      }
      
      .ban-info-box {
        padding: 0.75rem;
      }
      
      .btn-unban,
      .btn-view-profile {
        padding: 0.65rem 1rem;
        font-size: 0.9rem;
      }
      
      .empty-state {
        padding: 2.5rem 1.25rem;
      }
      
      .empty-state i {
        font-size: 3rem;
      }
      
      .empty-state h3 {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  <div class="container-xl py-4">
    <!-- Top Bar -->
    <div class="topbar">
      <div>
        <h1 class="h4 mb-0">
          Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>
          <span class="admin-badge">
            <i class="bi bi-shield-fill-check"></i> ADMINISTRATOR
          </span>
        </h1>
      </div>
      <div>
        <a href="dashboard.php" class="back-btn">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
      <h1>
        <i class="bi bi-people-fill"></i> Manage Banned Users
      </h1>
      <p>View and unban users who have been permanently suspended</p>
      
      <div class="stats-bar">
        <div class="stat-item">
          <span class="stat-num"><?php echo $total_banned; ?></span>
          <span class="stat-label">Total Banned</span>
        </div>
        <div class="stat-item">
          <span class="stat-num">0</span>
          <span class="stat-label">Unbanned Today</span>
        </div>
        <div class="stat-item">
          <span class="stat-num">0</span>
          <span class="stat-label">Active Bans</span>
        </div>
      </div>
    </div>

    <!-- User Cards -->
    <?php if (empty($banned_users)): ?>
      <div class="empty-state">
        <i class="bi bi-check-circle-fill"></i>
        <h3>No Banned Users!</h3>
        <p>There are currently no banned users in the system. Great community management!</p>
      </div>
    <?php else: ?>
      <?php foreach ($banned_users as $user): ?>
        <div class="user-card">
          <div class="user-header">
            <div class="user-info">
              <div class="user-avatar">
                <?php if (!empty($user['profile_picture']) && file_exists('../' . $user['profile_picture'])): ?>
                  <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Avatar">
                <?php else: ?>
                  <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                <?php endif; ?>
              </div>
              <div class="user-details">
                <h3>@<?php echo htmlspecialchars($user['username']); ?></h3>
                <div class="meta">
                  <span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                  <?php if (!empty($user['first_name']) || !empty($user['last_name'])): ?>
                    <span><i class="bi bi-person"></i> 
                      <?php 
                        $name_parts = array_filter([
                          $user['first_name'] ?? '',
                          $user['middle_name'] ?? '',
                          $user['last_name'] ?? '',
                          $user['suffix'] ?? ''
                        ]);
                        echo htmlspecialchars(implode(' ', $name_parts));
                      ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div>
              <span class="banned-badge">
                <i class="bi bi-shield-fill-x"></i> BANNED
              </span>
            </div>
          </div>

          <div class="user-body">
            <!-- Ban Info Box -->
            <div class="ban-info-box">
              <div class="ban-label">
                <i class="bi bi-exclamation-triangle-fill"></i> Ban Reason
              </div>
              <div class="ban-text">
                <?php echo !empty($user['ban_reason']) ? htmlspecialchars($user['ban_reason']) : 'No reason provided'; ?>
              </div>
              
              <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.875rem; color: rgba(255, 255, 255, 0.6);">
                <div><i class="bi bi-calendar-x"></i> Banned on: <?php echo !empty($user['banned_at']) ? date('F j, Y \a\t g:i A', strtotime($user['banned_at'])) : 'Unknown'; ?></div>
                <div style="margin-top: 0.25rem;"><i class="bi bi-person-badge"></i> Banned by: <?php echo !empty($user['banned_by']) ? htmlspecialchars($user['banned_by']) : 'Unknown'; ?></div>
              </div>
            </div>
          </div>

          <div class="user-actions">
            <button class="btn-unban" onclick="unbanUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
              <i class="bi bi-shield-fill-check"></i> Unban User
            </button>
            <button class="btn-view-profile" onclick="alert('Profile view coming soon!')">
              <i class="bi bi-person-circle"></i> View Profile
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function unbanUser(userId, username) {
      if (!confirm(`Are you sure you want to UNBAN @${username}?\n\nThis user will be able to log in and access the site again.`)) {
        return;
      }
      
      // Show loading state
      const buttons = document.querySelectorAll('.btn-unban, .btn-view-profile');
      buttons.forEach(btn => btn.disabled = true);
      
      fetch('../api/unban_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          user_id: userId,
          username: username
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ ' + data.message);
          location.reload();
        } else {
          alert('Error: ' + data.message);
          buttons.forEach(btn => btn.disabled = false);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while unbanning the user');
        buttons.forEach(btn => btn.disabled = false);
      });
    }
  </script>
</body>
</html>
