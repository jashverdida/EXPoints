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

// Fetch ban reviews with post information
$ban_reviews = [];
$approved_today = 0;
$rejected_today = 0;

if ($db) {
    $query = "SELECT br.*, p.title, p.content, p.game 
              FROM ban_reviews br 
              LEFT JOIN posts p ON br.post_id = p.id 
              WHERE br.status = 'pending' 
              ORDER BY br.created_at DESC";
    $result = $db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $ban_reviews[] = $row;
        }
    }
    
    // Get stats for today
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM ban_reviews WHERE DATE(reviewed_at) = ? AND status = 'approved'");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $approved_today = $result->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM ban_reviews WHERE DATE(reviewed_at) = ? AND status = 'rejected'");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $rejected_today = $result->fetch_assoc()['count'];
    $stmt->close();
    
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ban Appeals - Admin Dashboard</title>
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
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(239, 68, 68, 0.4);
      border-radius: 1.25rem;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px rgba(239, 68, 68, 0.2);
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
      background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.2), transparent);
      animation: shine 3s infinite;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .page-header h1 {
      font-size: 2rem;
      font-weight: 800;
      color: #fca5a5;
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
      border-top: 1px solid rgba(239, 68, 68, 0.3);
    }
    
    .stat-item {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    
    .stat-num {
      font-size: 2rem;
      font-weight: 800;
      color: #ef4444;
      line-height: 1;
    }
    
    .stat-label {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .review-card {
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
    
    .review-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: linear-gradient(180deg, #ef4444, #dc2626);
    }
    
    .review-card:hover {
      transform: translateY(-5px);
      border-color: #3b82f6;
      box-shadow: 0 12px 48px rgba(59, 130, 246, 0.3);
    }
    
    .review-header {
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
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: 800;
      color: white;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }
    
    .user-details h3 {
      font-size: 1.25rem;
      font-weight: 700;
      color: #fca5a5;
      margin: 0;
    }
    
    .user-details .meta {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      display: flex;
      gap: 1rem;
      margin-top: 0.25rem;
    }
    
    .severity-badge {
      padding: 0.35rem 1rem;
      border-radius: 1.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
      animation: pulse 2s infinite;
    }
    
    .review-body {
      margin-bottom: 1rem;
    }
    
    .post-preview {
      background: rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(59, 130, 246, 0.2);
      border-radius: 0.75rem;
      padding: 1rem;
      margin-top: 1rem;
    }
    
    .post-preview-title {
      font-weight: 700;
      color: #60a5fa;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .post-preview-content {
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.6;
      max-height: 100px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .reason-box {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 0.75rem;
      padding: 1rem;
      margin-top: 1rem;
    }
    
    .reason-label {
      font-size: 0.875rem;
      color: #fca5a5;
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .reason-text {
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.6;
    }
    
    .review-actions {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .btn-approve {
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
    
    .btn-approve:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(16, 185, 129, 0.5);
      background: linear-gradient(135deg, #059669, #047857);
    }
    
    .btn-reject {
      flex: 1;
      background: linear-gradient(135deg, #6b7280, #4b5563);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
    }
    
    .btn-reject:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(107, 114, 128, 0.5);
      background: linear-gradient(135deg, #4b5563, #374151);
    }
    
    .btn-view {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      border: none;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 600;
      font-size: 0.875rem;
      transition: all 0.3s;
    }
    
    .btn-view:hover {
      transform: translateY(-2px);
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
      color: #3b82f6;
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
        <i class="bi bi-gavel"></i> Ban Appeals Review
      </h1>
      <p>Final review and decision on user ban requests flagged by moderators</p>
      
      <div class="stats-bar">
        <div class="stat-item">
          <span class="stat-num"><?php echo count($ban_reviews); ?></span>
          <span class="stat-label">Pending Appeals</span>
        </div>
        <div class="stat-item">
          <span class="stat-num"><?php echo $approved_today; ?></span>
          <span class="stat-label">Approved Today</span>
        </div>
        <div class="stat-item">
          <span class="stat-num"><?php echo $rejected_today; ?></span>
          <span class="stat-label">Rejected Today</span>
        </div>
      </div>
    </div>

    <!-- Review Cards -->
    <?php if (empty($ban_reviews)): ?>
      <div class="empty-state">
        <i class="bi bi-check-circle"></i>
        <h3>All Clear!</h3>
        <p>No ban appeals are currently pending review.</p>
      </div>
    <?php else: ?>
      <?php foreach ($ban_reviews as $review): ?>
        <div class="review-card">
          <div class="review-header">
            <div class="user-info">
              <div class="user-avatar">
                <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
              </div>
              <div class="user-details">
                <h3>@<?php echo htmlspecialchars($review['username']); ?></h3>
                <div class="meta">
                  <span><i class="bi bi-clock"></i> <?php echo date('M j, Y g:i A', strtotime($review['created_at'])); ?></span>
                  <span><i class="bi bi-person-badge"></i> Flagged by: <?php echo htmlspecialchars($review['flagged_by']); ?></span>
                </div>
              </div>
            </div>
            <div>
              <span class="severity-badge">
                <i class="bi bi-exclamation-triangle-fill"></i> REQUIRES ACTION
              </span>
            </div>
          </div>

          <div class="review-body">
            <!-- Reason Box -->
            <div class="reason-box">
              <div class="reason-label">
                <i class="bi bi-chat-square-quote"></i> Reason for Ban Request
              </div>
              <div class="reason-text">
                <?php echo htmlspecialchars($review['reason']); ?>
              </div>
            </div>

            <!-- Post Preview -->
            <?php if (!empty($review['title'])): ?>
              <div class="post-preview">
                <div class="post-preview-title">
                  <i class="bi bi-file-text"></i> Related Post
                  <?php if (!empty($review['game'])): ?>
                    <span style="color: #a78bfa; font-size: 0.875rem;">
                      <i class="bi bi-controller"></i> <?php echo htmlspecialchars($review['game']); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <h5 style="color: #93c5fd; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($review['title']); ?></h5>
                <div class="post-preview-content">
                  <?php echo htmlspecialchars(substr($review['content'], 0, 200)); ?>
                  <?php if (strlen($review['content']) > 200): ?>...<?php endif; ?>
                </div>
                <?php if (!empty($review['post_id'])): ?>
                  <button class="btn-view mt-2" onclick="viewPost(<?php echo $review['post_id']; ?>)">
                    <i class="bi bi-eye"></i> View Full Post
                  </button>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="review-actions">
            <button class="btn-approve" onclick="reviewBan(<?php echo $review['id']; ?>, 'approved', '<?php echo htmlspecialchars($review['username']); ?>')">
              <i class="bi bi-hammer"></i> Approve Ban
            </button>
            <button class="btn-reject" onclick="reviewBan(<?php echo $review['id']; ?>, 'rejected', '<?php echo htmlspecialchars($review['username']); ?>')">
              <i class="bi bi-x-circle-fill"></i> Reject & Clear
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Post View Modal -->
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function viewPost(postId) {
      const modal = new bootstrap.Modal(document.getElementById('postModal'));
      modal.show();
      
      // Fetch post details
      fetch(`../api/get_post.php?id=${postId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const post = data.post;
            document.getElementById('postModalBody').innerHTML = renderPost(post);
          } else {
            document.getElementById('postModalBody').innerHTML = `
              <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Failed to load post details'}
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('postModalBody').innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle"></i> Error loading post
            </div>
          `;
        });
    }
    
    function renderPost(post) {
      return `
        <article class="card-post" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9)); border: 2px solid rgba(59, 130, 246, 0.3); border-radius: 1rem; padding: 1.5rem; max-width: 100%;">
          <div class="top" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
              <div style="width: 50px; height: 50px; border-radius: 50%; border: 3px solid #3b82f6; padding: 3px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); animation: rotate 4s linear infinite;">
                <img src="${post.profile_picture}" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
              </div>
              <div>
                <p style="margin: 0; font-weight: 700; color: #60a5fa; font-size: 1.1rem;">${escapeHtml(post.title)}</p>
                <p style="margin: 0; font-size: 0.875rem; color: rgba(255, 255, 255, 0.6);">${formatDate(post.created_at)}</p>
              </div>
            </div>
          </div>
          <div style="margin-bottom: 1rem;">
            <p style="margin: 0; color: #3b82f6; font-weight: 600; font-size: 0.9rem;">@${escapeHtml(post.username)}</p>
          </div>
          <div style="background: rgba(0, 0, 0, 0.3); padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #3b82f6;">
            <p style="margin: 0; line-height: 1.7; color: rgba(255, 255, 255, 0.9); white-space: pre-wrap;">${escapeHtml(post.content)}</p>
          </div>
        </article>
      `;
    }
    
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    function formatDate(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays === 0) {
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        if (diffHours === 0) {
          const diffMins = Math.floor(diffTime / (1000 * 60));
          return diffMins + ' mins ago';
        }
        return diffHours + 'h ago';
      } else if (diffDays < 7) {
        return diffDays + 'd ago';
      } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      }
    }
    
    function reviewBan(reviewId, action, username) {
      const actionText = action === 'approved' ? 'BAN' : 'reject the ban request for';
      const confirmText = action === 'approved' 
        ? `⚠️ WARNING ⚠️\n\nYou are about to permanently BAN user @${username}.\n\nThis user will:\n• Be immediately logged out\n• Unable to login again\n• See a "You are Banned" message\n\nAre you absolutely sure?`
        : `Are you sure you want to ${actionText} @${username}?`;
      
      if (!confirm(confirmText)) {
        return;
      }
      
      // Show loading state
      const buttons = document.querySelectorAll('.btn-approve, .btn-reject');
      buttons.forEach(btn => btn.disabled = true);
      
      fetch('../api/review_ban.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          review_id: reviewId,
          action: action,
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
        alert('An error occurred while processing the review');
        buttons.forEach(btn => btn.disabled = false);
      });
    }
  </script>
</body>
</html>
