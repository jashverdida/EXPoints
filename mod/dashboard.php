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
        
        // Get recent posts for moderation
        $result = $db->query("SELECT id, game, title, content, username, likes, comments, created_at FROM posts ORDER BY created_at DESC LIMIT 10");
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
      background: radial-gradient(900px 600px at 15% 15%, #1b378d66 0%, #0000 60%),
                  radial-gradient(800px 520px at 85% 80%, #1a3a9060 0%, #0000 60%),
                  linear-gradient(145deg, #08122e, #0c1f6f) !important;
      min-height: 100vh;
      color: #f6f9ff;
    }
    
    .mod-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
      color: #38a0ff;
    }
    
    .metrics {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }
    
    .metric {
      text-align: center;
      padding: 1rem;
      background: rgba(56, 160, 255, 0.1);
      border: 1px solid rgba(56, 160, 255, 0.2);
      border-radius: 0.5rem;
    }
    
    .m-num {
      display: block;
      font-size: 2rem;
      font-weight: 700;
      color: #38a0ff;
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
      border-bottom: 1px solid rgba(194, 213, 255, 0.2);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .activity li:last-child {
      border-bottom: none;
    }
    
    .activity li i {
      color: #38a0ff;
    }
    
    .btn-mod {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 600;
      transition: transform 0.2s;
    }
    
    .btn-mod:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
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
      color: #f6f9ff;
    }
    
    .table thead th {
      background: rgba(56, 160, 255, 0.2);
      border-color: rgba(194, 213, 255, 0.2);
      color: #38a0ff;
      font-weight: 700;
    }
    
    .table tbody tr {
      border-color: rgba(194, 213, 255, 0.2);
      transition: background 0.3s;
    }
    
    .table tbody tr:hover {
      background: rgba(56, 160, 255, 0.1);
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
  </style>
</head>
<body>

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
            <button class="btn btn-mod" onclick="searchPosts()">
              <i class="bi bi-search"></i> Search
            </button>
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
                  <tr>
                    <td>#<?php echo htmlspecialchars($post['id']); ?></td>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['username']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                    <td>
                      <button class="btn btn-sm btn-mod" onclick="viewPost(<?php echo $post['id']; ?>)">
                        <i class="bi bi-eye"></i> View
                      </button>
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
          <button class="btn btn-outline-secondary" onclick="alert('Feature coming soon!')">
            <i class="bi bi-flag"></i> Review Reports
          </button>
          <button class="btn btn-outline-secondary" onclick="alert('Feature coming soon!')">
            <i class="bi bi-person-x"></i> Manage Users
          </button>
        </div>
      </section>
    </div>
  </main>

  <!-- Post Detail Modal -->
  <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="postModalLabel">
            <i class="bi bi-file-text"></i> Post Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="postModalBody">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-mod" onclick="alert('Moderation actions coming soon!')">
            <i class="bi bi-shield-check"></i> Moderate
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
          console.error('Error:', error);
          document.getElementById('postModalBody').innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle"></i> Error loading post
            </div>
          `;
        });
    }
    
    function displayPostDetails(post) {
      const modalBody = document.getElementById('postModalBody');
      const createdDate = new Date(post.created_at).toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
      
      modalBody.innerHTML = `
        <div class="post-detail-card">
          <h3 style="color: #38a0ff; font-weight: 700; margin-bottom: 1rem;">
            ${escapeHtml(post.title)}
          </h3>
          
          <div class="post-meta">
            <div class="post-meta-item">
              <i class="bi bi-person-circle"></i>
              <strong>Author:</strong> ${escapeHtml(post.username)}
            </div>
            <div class="post-meta-item">
              <i class="bi bi-calendar"></i>
              <strong>Posted:</strong> ${createdDate}
            </div>
            <div class="post-meta-item">
              <i class="bi bi-controller"></i>
              <strong>Game:</strong> ${escapeHtml(post.game || 'N/A')}
            </div>
          </div>
          
          <div class="post-meta">
            <div class="post-meta-item">
              <i class="bi bi-heart-fill text-danger"></i>
              <strong>${post.likes || 0}</strong> Likes
            </div>
            <div class="post-meta-item">
              <i class="bi bi-chat-fill text-info"></i>
              <strong>${post.comments || 0}</strong> Comments
            </div>
          </div>
          
          <hr style="border-color: rgba(194, 213, 255, 0.2);">
          
          <div class="post-content-box">
            <h5 style="color: #38a0ff; margin-bottom: 1rem;">
              <i class="bi bi-file-text"></i> Content
            </h5>
            <p style="white-space: pre-wrap; line-height: 1.8;">
              ${escapeHtml(post.content)}
            </p>
          </div>
        </div>
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
          <tr>
            <td>#${post.id}</td>
            <td>${escapeHtml(post.title)}</td>
            <td>${escapeHtml(post.username)}</td>
            <td>${new Date(post.created_at).toLocaleDateString('en-US', {
              month: 'short',
              day: 'numeric',
              year: 'numeric'
            })}</td>
            <td>
              <button class="btn btn-sm btn-mod" onclick="viewPost(${post.id})">
                <i class="bi bi-eye"></i> View
              </button>
            </td>
          </tr>
        `).join('');
      }
    }
    
    function displayAllPosts() {
      const tableBody = document.querySelector('#postsTable tbody');
      tableBody.innerHTML = allPosts.map(post => `
        <tr>
          <td>#${post.id}</td>
          <td>${escapeHtml(post.title)}</td>
          <td>${escapeHtml(post.username)}</td>
          <td>${new Date(post.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
          })}</td>
          <td>
            <button class="btn btn-sm btn-mod" onclick="viewPost(${post.id})">
              <i class="bi bi-eye"></i> View
              </button>
          </td>
        </tr>
      `).join('');
    }
    
    // Enable search on Enter key
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        searchPosts();
      }
    });
  </script>
</body>
</html>
