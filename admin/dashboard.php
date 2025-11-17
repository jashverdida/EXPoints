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

// Check if user has admin role (moderators merged into admin)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect non-admin users to regular dashboard
    header('Location: ../user/dashboard.php');
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
$recent_posts = [];

if ($db) {
    try {
        // Get user count
        $result = $db->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_users = $row['count'];
        }
        
        // Get admin count (moderators merged into admin)
        $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_admins = $row['count'];
        }
        
        // No more separate moderator role
        $total_mods = 0;
        
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
        // Get actual counts from post_likes and comments tables
        $query = "SELECT p.id, p.game, p.title, p.content, p.username, p.user_id, p.created_at,
                  (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) as like_count,
                  (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) as comment_count
                  FROM posts p 
                  LEFT JOIN user_info ui ON p.user_id = ui.user_id 
                  WHERE (ui.is_banned IS NULL OR ui.is_banned = 0) 
                  ORDER BY p.created_at DESC 
                  LIMIT 50";
        $result = $db->query($query);
        if ($result) {
            while ($post = $result->fetch_assoc()) {
                $recent_posts[] = $post;
            }
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
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .admin-card:hover {
      box-shadow: 0 12px 40px rgba(59, 130, 246, 0.6), 
                  0 0 30px rgba(59, 130, 246, 0.3);
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
    
    /* Posts Grid */
    .posts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 1.25rem;
      margin-top: 1.5rem;
      max-height: 600px;
      overflow-y: auto;
      padding-right: 0.5rem;
    }
    
    /* Custom Scrollbar */
    .posts-grid::-webkit-scrollbar {
      width: 8px;
    }
    
    .posts-grid::-webkit-scrollbar-track {
      background: rgba(30, 58, 138, 0.2);
      border-radius: 10px;
    }
    
    .posts-grid::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      border-radius: 10px;
    }
    
    .posts-grid::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, #2563eb, #1e40af);
    }
    
    /* Search Bar */
    .search-container {
      margin-bottom: 1rem;
    }
    
    .search-box {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }
    
    .search-input {
      flex: 1;
      background: rgba(30, 58, 138, 0.3);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      color: white;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
      background: rgba(30, 58, 138, 0.4);
    }
    
    .search-input::placeholder {
      color: rgba(255, 255, 255, 0.5);
    }
    
    .search-select {
      background: rgba(30, 58, 138, 0.3);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      color: white;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .search-select:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
    }
    
    .search-select option {
      background: #1e3a8a;
      color: white;
    }
    
    .post-card {
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.25), rgba(37, 99, 235, 0.2));
      backdrop-filter: blur(10px);
      border: 2px solid rgba(59, 130, 246, 0.3);
      border-radius: 1rem;
      padding: 1.25rem;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    
    .post-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(59, 130, 246, 0.5), 
                  0 0 30px rgba(59, 130, 246, 0.3);
      border-color: rgba(59, 130, 246, 0.6);
    }
    
    .post-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }
    
    .post-id {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      font-weight: 600;
    }
    
    .post-date {
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    
    .post-title {
      color: white;
      font-size: 1.125rem;
      font-weight: 700;
      margin: 0;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .post-meta {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.7);
    }
    
    .post-meta span {
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }
    
    .post-author {
      color: #60a5fa;
      font-weight: 600;
    }
    
    .post-game {
      color: #a78bfa;
    }
    
    .post-content-preview {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
      line-height: 1.5;
      background: rgba(0, 0, 0, 0.2);
      padding: 0.75rem;
      border-radius: 0.5rem;
      border-left: 3px solid rgba(59, 130, 246, 0.5);
    }
    
    .post-card-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: auto;
      padding-top: 0.5rem;
    }
    
    .btn-view, .btn-flag {
      flex: 1;
      padding: 0.625rem 1rem;
      border: none;
      border-radius: 0.5rem;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .btn-view {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
    }
    
    .btn-view:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
    }
    
    .btn-flag {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
    }
    
    .btn-flag:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }
    
    /* ================= MOBILE RESPONSIVE FIXES ================= */
    
    /* Mobile Fix for Admin Dashboard */
    @media (max-width: 768px) {
      /* Fix body scrolling on mobile */
      body {
        overflow-x: hidden !important;
        overflow-y: auto !important;
        height: auto !important;
        min-height: 100vh;
      }
      
      /* Topbar adjustments - more compact */
      .topbar {
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.85rem;
        align-items: flex-start;
      }
      
      .topbar h1 {
        font-size: 1.3rem !important;
        margin-bottom: 0 !important;
      }
      
      .topbar p {
        font-size: 0.75rem !important;
        margin-bottom: 0 !important;
      }
      
      .topbar .lp-brand-img {
        max-height: 50px;
      }
      
      .topbar .right {
        width: 100%;
        justify-content: space-between;
      }
      
      .topbar .icon {
        font-size: 1.1rem;
      }
      
      /* Admin badge adjustments */
      .admin-badge {
        display: block;
        margin-left: 0;
        margin-top: 0.35rem;
        text-align: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
      }
      
      /* Admin grid - single column */
      .admin-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
      }
      
      /* Admin cards - much more compact */
      .admin-card {
        padding: 0.85rem;
      }
      
      /* Section titles - smaller */
      .section-title {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
      }
      
      .section-title i {
        font-size: 0.95rem;
      }
      
      /* Metrics grid - 2x2 compact layout */
      .metrics {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
      }
      
      .metric {
        padding: 0.6rem 0.5rem;
      }
      
      .m-num {
        font-size: 1.35rem !important;
      }
      
      .m-label {
        font-size: 0.7rem !important;
      }
      
      /* Activity list */
      .activity-list {
        font-size: 0.9rem;
      }
      
      .activity-item {
        padding: 0.75rem;
        gap: 0.75rem;
      }
      
      .activity-icon {
        width: 38px;
        height: 38px;
        font-size: 1rem;
      }
      
      .activity-text {
        font-size: 0.9rem;
      }
      
      .activity-time {
        font-size: 0.75rem;
      }
      
      /* Admin Tools - compact buttons */
      .btn-admin,
      .btn-outline-secondary {
        padding: 0.6rem 0.75rem !important;
        font-size: 0.85rem !important;
      }
      
      .btn-admin i,
      .btn-outline-secondary i {
        font-size: 0.9rem;
      }
      
      .d-grid {
        gap: 0.5rem !important;
      }
      
      /* Quick links - single column */
      .quick-links {
        grid-template-columns: 1fr;
        gap: 10px;
      }
      
      .quick-link {
        padding: 0.85rem 1rem;
        font-size: 0.95rem;
      }
      
      /* Posts grid - single column, no max-height to allow natural scrolling */
      .posts-grid {
        grid-template-columns: 1fr;
        gap: 0.85rem;
        max-height: none !important;
        overflow-y: visible !important;
        padding-right: 0;
      }
      
      /* Search box - more compact */
      .search-box {
        flex-direction: column;
        gap: 0.4rem;
        margin-bottom: 0.75rem;
      }
      
      .search-input,
      .search-select {
        width: 100%;
        padding: 0.6rem 0.75rem;
        font-size: 0.85rem;
      }
      
      .search-container {
        margin-bottom: 0.5rem !important;
      }
      
      /* Post cards - smaller and more compact */
      .post-card {
        padding: 0.85rem;
        gap: 0.5rem;
      }
      
      .post-card-header {
        margin-bottom: 0.35rem;
      }
      
      .post-id {
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
      }
      
      .post-date {
        font-size: 0.75rem;
      }
      
      .post-title {
        font-size: 0.95rem;
        line-height: 1.3;
        -webkit-line-clamp: 1;
        margin-bottom: 0.35rem;
      }
      
      .post-meta {
        font-size: 0.75rem;
        gap: 0.5rem;
      }
      
      .post-content-preview {
        font-size: 0.8rem;
        padding: 0.5rem;
        max-height: 60px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
      }
      
      .post-card-actions {
        flex-direction: row;
        gap: 0.5rem;
        margin-top: 0.5rem;
      }
      
      .btn-view,
      .btn-flag {
        flex: 1;
        padding: 0.6rem 0.5rem;
        font-size: 0.8rem;
      }
      
      .btn-view i,
      .btn-flag i {
        font-size: 0.85rem;
      }
      
      /* Stats cards */
      .stat-card {
        padding: 1.25rem;
      }
      
      .stat-value {
        font-size: 1.75rem;
      }
      
      .stat-label {
        font-size: 0.875rem;
      }
    }
    
    @media (max-width: 480px) {
      /* Further compress for small phones */
      .topbar {
        padding: 0.7rem;
      }
      
      .topbar .lp-brand-img {
        max-height: 45px;
      }
      
      .admin-badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
      }
      
      .admin-card {
        padding: 0.7rem;
      }
      
      .section-title {
        font-size: 1rem;
        margin-bottom: 0.6rem;
      }
      
      /* Metrics - even more compact on small screens */
      .metrics {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.4rem;
      }
      
      .metric {
        padding: 0.5rem 0.4rem;
      }
      
      .m-num {
        font-size: 1.2rem !important;
      }
      
      .m-label {
        font-size: 0.65rem !important;
      }
      
      /* Admin tools even more compact */
      .btn-admin,
      .btn-outline-secondary {
        padding: 0.5rem 0.6rem !important;
        font-size: 0.8rem !important;
      }
      
      .d-grid {
        gap: 0.4rem !important;
      }
      
      .activity-item {
        padding: 0.65rem;
        font-size: 0.85rem;
      }
      
      .activity-icon {
        width: 35px;
        height: 35px;
        font-size: 0.95rem;
      }
      
      .quick-link {
        padding: 0.75rem 0.9rem;
        font-size: 0.9rem;
      }
      
      /* Make post cards even smaller on tiny screens */
      .post-card {
        padding: 0.7rem;
        gap: 0.4rem;
      }
      
      .post-id {
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
      }
      
      .post-date {
        font-size: 0.7rem;
      }
      
      .post-title {
        font-size: 0.9rem;
        line-height: 1.2;
      }
      
      .post-meta {
        font-size: 0.7rem;
        gap: 0.4rem;
      }
      
      .post-content-preview {
        font-size: 0.75rem;
        padding: 0.4rem;
        max-height: 50px;
        -webkit-line-clamp: 2;
      }
      
      .btn-view,
      .btn-flag {
        padding: 0.5rem 0.4rem;
        font-size: 0.75rem;
      }
      
      .post-meta {
        font-size: 0.8rem;
      }
      
      .post-content-preview {
        font-size: 0.85rem;
        padding: 0.6rem;
      }
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
      <section class="admin-card">
        <h2 class="section-title">
          <i class="bi bi-bar-chart-fill"></i> System Overview
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
            <span class="m-num"><?php echo number_format($total_admins); ?></span>
            <span class="m-label">Administrators</span>
          </div>
        </div>
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
            <i class="bi bi-shield-check"></i> Manage Admins
          </a>
        </div>
      </section>

      <!-- Posts for Moderation - Full Width -->
      <section class="admin-card" style="grid-column: span 2;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h2 class="section-title" style="margin-bottom: 0;">
            <i class="bi bi-list-check"></i> Posts for Moderation
          </h2>
          <span style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">
            <span id="displayedCount"><?php echo count($recent_posts); ?></span> / <?php echo count($recent_posts); ?> posts
          </span>
        </div>
        
        <!-- Search Bar -->
        <div class="search-container">
          <div class="search-box">
            <input type="text" id="searchInput" class="search-input" placeholder="Search posts by title or author...">
            <select id="searchType" class="search-select">
              <option value="title">Search by Title</option>
              <option value="author">Search by Author</option>
            </select>
          </div>
        </div>
        
        <?php if (empty($recent_posts)): ?>
          <div style="text-align: center; padding: 4rem 2rem; color: rgba(255, 255, 255, 0.5);">
            <i class="bi bi-inbox" style="font-size: 4rem; margin-bottom: 1rem; display: block;"></i>
            <p style="font-size: 1.2rem;">No posts yet</p>
          </div>
        <?php else: ?>
          <div class="posts-grid" id="postsGrid">
            <?php foreach ($recent_posts as $post): ?>
              <div class="post-card" id="post-card-<?php echo $post['id']; ?>">
                <div class="post-card-header">
                  <div class="post-id">#<?php echo htmlspecialchars($post['id']); ?></div>
                  <div class="post-date">
                    <i class="bi bi-calendar"></i>
                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                  </div>
                </div>
                
                <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                
                <div class="post-meta">
                  <span class="post-author">
                    <i class="bi bi-person-circle"></i>
                    @<?php echo htmlspecialchars($post['username']); ?>
                  </span>
                  <span class="post-game">
                    <i class="bi bi-controller"></i>
                    <?php echo htmlspecialchars($post['game']); ?>
                  </span>
                </div>
                
                <div class="post-content-preview">
                  <?php echo htmlspecialchars(substr($post['content'], 0, 120)) . (strlen($post['content']) > 120 ? '...' : ''); ?>
                </div>
                
                <div class="post-card-actions">
                  <button class="btn-view" onclick="viewPost(<?php echo $post['id']; ?>)">
                    <i class="bi bi-eye"></i> View
                  </button>
                  <button class="btn-flag" onclick="flagForBan(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['username']); ?>')">
                    <i class="bi bi-flag-fill"></i> Flag User
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <!-- Custom Fullscreen Post Detail Overlay -->
  <div id="postModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.95); z-index: 9999; overflow: hidden;">
    <div style="width: 100%; height: 100%; display: flex; flex-direction: column; padding: 2rem;">
      <!-- Header -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h5 style="color: #60a5fa; font-weight: 700; font-size: 1.5rem; margin: 0;">
          <i class="bi bi-eye-fill"></i> Post Review
        </h5>
        <button onclick="closePostModal()" style="background: rgba(239, 68, 68, 0.8); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;" onmouseover="this.style.background='#ef4444'" onmouseout="this.style.background='rgba(239, 68, 68, 0.8)'">
          <i class="bi bi-x"></i>
        </button>
      </div>
      
      <!-- Content -->
      <div id="postModalBody" style="flex: 1; overflow-y: auto; background: linear-gradient(135deg, rgba(10, 10, 30, 0.95), rgba(22, 33, 62, 0.95)); backdrop-filter: blur(20px); border: 2px solid rgba(59, 130, 246, 0.5); border-radius: 1rem; padding: 2rem;">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      
      <!-- Footer -->
      <div style="margin-top: 1.5rem; text-align: right;">
        <button onclick="closePostModal()" style="background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%); color: white; border: none; padding: 0.75rem 2rem; border-radius: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.5);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(59, 130, 246, 0.6)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(59, 130, 246, 0.5)'">
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- Flag for Ban Modal -->
  <div class="modal fade" id="flagBanModal" tabindex="-1" aria-labelledby="flagBanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.95), rgba(37, 99, 235, 0.95)); backdrop-filter: blur(20px); border: 2px solid rgba(239, 68, 68, 0.5);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(239, 68, 68, 0.3);">
          <h5 class="modal-title" id="flagBanModalLabel" style="color: #ef4444; font-weight: 700;">
            <i class="bi bi-flag-fill"></i> Flag User for Ban
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p style="color: #f6f9ff; margin-bottom: 1rem;">
            You are about to flag user <strong id="flagUsername" style="color: #ef4444;"></strong> for ban review.
          </p>
          <div class="alert" style="background: rgba(251, 191, 36, 0.2); border: 1px solid rgba(251, 191, 36, 0.5); color: #fbbf24; border-radius: 0.5rem; padding: 1rem;">
            <i class="bi bi-exclamation-triangle"></i> This will create a ban review that you can approve or reject.
          </div>
          <div class="mb-3">
            <label for="banReason" class="form-label" style="color: #ef4444; font-weight: 600;">Reason (Required) *</label>
            <textarea class="form-control" id="banReason" rows="4" placeholder="Explain why this user should be banned..." required style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(239, 68, 68, 0.3); color: white;"></textarea>
            <small style="color: rgba(255, 255, 255, 0.7);">Please provide detailed reasons for the ban.</small>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 2px solid rgba(239, 68, 68, 0.3);">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmFlagBan()" style="background: linear-gradient(135deg, #ef4444, #dc2626); border: none; font-weight: 600;">
            <i class="bi bi-flag-fill"></i> Flag for Ban
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Store current post/user for modals
    let currentPostId = null;
    let currentUsername = null;
    
    // Store all posts for search
    const allPosts = <?php echo json_encode($recent_posts); ?>;

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
    
    // Search functionality
    function searchPosts() {
      const searchInput = document.getElementById('searchInput');
      const searchType = document.getElementById('searchType');
      const query = searchInput.value.toLowerCase().trim();
      const type = searchType.value;
      const postCards = document.querySelectorAll('.post-card');
      let visibleCount = 0;
      
      postCards.forEach(card => {
        const title = card.querySelector('.post-title').textContent.toLowerCase();
        const author = card.querySelector('.post-author').textContent.toLowerCase();
        
        let shouldShow = false;
        if (query === '') {
          shouldShow = true;
        } else if (type === 'title' && title.includes(query)) {
          shouldShow = true;
        } else if (type === 'author' && author.includes(query)) {
          shouldShow = true;
        }
        
        if (shouldShow) {
          card.style.display = 'flex';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });
      
      document.getElementById('displayedCount').textContent = visibleCount;
    }
    
    // Add event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const searchType = document.getElementById('searchType');
      
      if (searchInput) {
        searchInput.addEventListener('input', searchPosts);
      }
      if (searchType) {
        searchType.addEventListener('change', searchPosts);
      }
    });

    // View post details with custom fullscreen overlay
    function viewPost(postId) {
      document.getElementById('postModal').style.display = 'block';
      // Only prevent body scroll on desktop, allow on mobile
      if (window.innerWidth > 768) {
        document.body.style.overflow = 'hidden';
      }
      
      fetch(`../api/get_post.php?id=${postId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayPostDetails(data.post);
          } else {
            document.getElementById('postModalBody').innerHTML = `
              <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> Failed to load post details
              </div>
            `;
          }
        })
        .catch(error => {
          document.getElementById('postModalBody').innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle"></i> Error loading post
            </div>
          `;
        });
    }
    
    function closePostModal() {
      document.getElementById('postModal').style.display = 'none';
      // Restore overflow based on screen size
      if (window.innerWidth > 768) {
        document.body.style.overflow = 'auto';
      } else {
        document.body.style.overflow = '';
      }
    }

    function displayPostDetails(post) {
      const modalBody = document.getElementById('postModalBody');
      const profilePicture = post.profile_picture || '../assets/img/default-avatar.png';
      
      modalBody.innerHTML = `
        <div style="display: grid; grid-template-rows: auto 1fr; height: 100%; gap: 2rem;">
          <!-- Post Header -->
          <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 2rem; padding: 2rem; background: rgba(30, 58, 138, 0.4); border: 2px solid rgba(59, 130, 246, 0.5); border-radius: 1rem;">
            <img src="${escapeHtml(profilePicture)}" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(59, 130, 246, 0.6); box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);">
            
            <div>
              <h2 style="color: white; margin-bottom: 1rem; font-size: 2.5rem; font-weight: 700;">${escapeHtml(post.title)}</h2>
              <div style="color: rgba(255, 255, 255, 0.8); font-size: 1.2rem; display: flex; flex-wrap: wrap; gap: 2rem;">
                <span><i class="bi bi-person" style="margin-right: 0.5rem;"></i>@${escapeHtml(post.username)}</span>
                <span><i class="bi bi-controller" style="margin-right: 0.5rem;"></i>${escapeHtml(post.game)}</span>
                <span><i class="bi bi-calendar" style="margin-right: 0.5rem;"></i>${new Date(post.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
              </div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 1rem; justify-content: center;">
              <div style="padding: 1rem 1.5rem; background: rgba(239, 68, 68, 0.2); border: 2px solid rgba(239, 68, 68, 0.5); border-radius: 0.75rem; text-align: center;">
                <i class="bi bi-heart-fill" style="font-size: 1.8rem; color: #ef4444; display: block; margin-bottom: 0.5rem;"></i>
                <span style="color: white; font-weight: 700; font-size: 1.5rem; display: block;">${post.like_count || 0}</span>
                <span style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">likes</span>
              </div>
              <div style="padding: 1rem 1.5rem; background: rgba(59, 130, 246, 0.2); border: 2px solid rgba(59, 130, 246, 0.5); border-radius: 0.75rem; text-align: center;">
                <i class="bi bi-chat-fill" style="font-size: 1.8rem; color: #60a5fa; display: block; margin-bottom: 0.5rem;"></i>
                <span style="color: white; font-weight: 700; font-size: 1.5rem; display: block;">${post.comment_count || 0}</span>
                <span style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">comments</span>
              </div>
            </div>
          </div>
          
          <!-- Post Content -->
          <div style="padding: 2.5rem; background: rgba(0, 0, 0, 0.5); border: 2px solid rgba(59, 130, 246, 0.3); border-radius: 1rem; overflow-y: auto;">
            <div style="color: white; line-height: 2.2; white-space: pre-wrap; word-wrap: break-word; font-size: 1.25rem; letter-spacing: 0.3px;">
              ${escapeHtml(post.content)}
            </div>
          </div>
        </div>
      `;
    }

    function flagForBan(postId, username) {
      currentPostId = postId;
      currentUsername = username;
      document.getElementById('flagUsername').textContent = '@' + username;
      document.getElementById('banReason').value = '';
      const modal = new bootstrap.Modal(document.getElementById('flagBanModal'));
      modal.show();
    }

    function confirmFlagBan() {
      const reason = document.getElementById('banReason').value.trim();
      if (!reason) {
        alert('Please provide a reason for the ban');
        return;
      }

      fetch('../api/moderate_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          post_id: currentPostId,
          action: 'flag_ban',
          reason: reason
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ User flagged for ban review');
          bootstrap.Modal.getInstance(document.getElementById('flagBanModal')).hide();
        } else {
          alert('Error: ' + (data.error || 'Failed to flag user'));
        }
      })
      .catch(error => {
        alert('Error flagging user');
      });
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // Initialize particles on page load
    createParticles();
  </script>
</body>
</html>
