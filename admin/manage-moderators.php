<?php
require_once __DIR__ . '/../config/session.php';
startSecureSession();

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../user/login.php?error=' . urlencode('Please login to continue'));
    exit();
}

// Check if user has admin role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../user/dashboard.php');
    exit();
}



// Get user info
$username = $_SESSION['username'] ?? 'Administrator';

// Get database connection
$db = getDBConnection();

// Fetch all administrators
$moderators = [];
$total_mods = 0;
$active_mods = 0;
$disabled_mods = 0;

if ($db) {
    $query = "SELECT u.*, ui.username, ui.profile_picture 
              FROM users u
              LEFT JOIN user_info ui ON u.id = ui.user_id
              WHERE u.role = 'admin'
              ORDER BY u.is_disabled ASC, u.created_at DESC";
    $result = $db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $moderators[] = $row;
            if ($row['is_disabled'] == 1) {
                $disabled_mods++;
            } else {
                $active_mods++;
            }
        }
        $total_mods = count($moderators);
    }
    
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Admins - Admin Dashboard</title>
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
    
    /* Animated Background */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.15) 0%, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(139, 92, 246, 0.15) 0%, transparent 40%),
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
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    .page-header {
      background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(124, 58, 237, 0.2));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(139, 92, 246, 0.4);
      border-radius: 1.25rem;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px rgba(139, 92, 246, 0.2);
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
      background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.2), transparent);
      animation: shine 3s infinite;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .page-header h1 {
      font-size: 2rem;
      font-weight: 800;
      color: #c4b5fd;
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
      border-top: 1px solid rgba(139, 92, 246, 0.3);
    }
    
    .stat-item {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    
    .stat-num {
      font-size: 2rem;
      font-weight: 800;
      color: #a78bfa;
      line-height: 1;
    }
    
    .stat-label {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .btn-add-mod {
      background: linear-gradient(135deg, #10b981, #059669);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-add-mod:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(16, 185, 129, 0.5);
      background: linear-gradient(135deg, #059669, #047857);
    }
    
    .mod-card {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(139, 92, 246, 0.3);
      border-radius: 1.25rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }
    
    .mod-card.disabled {
      opacity: 0.7;
      border-color: rgba(107, 114, 128, 0.5);
    }
    
    .mod-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: linear-gradient(180deg, #8b5cf6, #7c3aed);
    }
    
    .mod-card.disabled::before {
      background: linear-gradient(180deg, #6b7280, #4b5563);
    }
    
    .mod-card:hover {
      transform: translateY(-5px);
      border-color: #8b5cf6;
      box-shadow: 0 12px 48px rgba(139, 92, 246, 0.3);
    }
    
    .mod-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(139, 92, 246, 0.2);
    }
    
    .mod-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .mod-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #8b5cf6, #7c3aed);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: 800;
      color: white;
      box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
      border: 3px solid rgba(139, 92, 246, 0.3);
    }
    
    .mod-avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .mod-details h3 {
      font-size: 1.25rem;
      font-weight: 700;
      color: #c4b5fd;
      margin: 0;
    }
    
    .mod-details .meta {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      display: flex;
      gap: 1rem;
      margin-top: 0.25rem;
    }
    
    .mod-badge {
      padding: 0.35rem 1rem;
      border-radius: 1.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      background: linear-gradient(135deg, #8b5cf6, #7c3aed);
      color: white;
      box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    }
    
    .disabled-badge {
      background: linear-gradient(135deg, #6b7280, #4b5563);
      box-shadow: 0 4px 15px rgba(107, 114, 128, 0.4);
    }
    
    .mod-actions {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .btn-disable {
      flex: 1;
      background: linear-gradient(135deg, #f59e0b, #d97706);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }
    
    .btn-disable:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(245, 158, 11, 0.5);
      background: linear-gradient(135deg, #d97706, #b45309);
    }
    
    .btn-enable {
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
    
    .btn-enable:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(16, 185, 129, 0.5);
      background: linear-gradient(135deg, #059669, #047857);
    }
    
    .btn-delete {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }
    
    .btn-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(239, 68, 68, 0.5);
      background: linear-gradient(135deg, #dc2626, #b91c1c);
    }
    
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(139, 92, 246, 0.3);
      border-radius: 1.25rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .empty-state i {
      font-size: 4rem;
      color: #8b5cf6;
      margin-bottom: 1rem;
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
    
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
      body {
        overflow-x: hidden;
        padding: 0.5rem;
      }
      
      .container-xl {
        padding: 0.5rem;
      }
      
      /* Topbar */
      .topbar {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
      }
      
      .topbar h1 {
        font-size: 1.5rem !important;
        text-align: center;
        margin-bottom: 0.5rem;
      }
      
      .admin-badge {
        align-self: center;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
      }
      
      /* Stats Bar */
      .stats-bar {
        flex-direction: column !important;
        gap: 1rem !important;
        margin-bottom: 1.5rem;
      }
      
      .stat-item {
        width: 100% !important;
        padding: 1rem !important;
        min-height: auto !important;
      }
      
      .stat-item h4 {
        font-size: 1.75rem !important;
      }
      
      .stat-item small {
        font-size: 0.9rem !important;
      }
      
      /* Action Buttons */
      .action-buttons {
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
      }
      
      .action-buttons .btn {
        width: 100%;
        justify-content: center;
        padding: 1rem !important;
        font-size: 1rem !important;
      }
      
      /* User Cards Grid */
      .moderators-grid {
        grid-template-columns: 1fr !important;
        gap: 1rem !important;
      }
      
      .moderator-card {
        padding: 1.25rem !important;
      }
      
      .moderator-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
        margin-bottom: 1rem;
      }
      
      .moderator-avatar {
        width: 60px !important;
        height: 60px !important;
        align-self: center;
      }
      
      .moderator-info {
        text-align: center;
        width: 100%;
      }
      
      .moderator-info h5 {
        font-size: 1.1rem !important;
      }
      
      .moderator-info small {
        font-size: 0.85rem !important;
      }
      
      .mod-badge {
        font-size: 0.7rem !important;
        padding: 0.25rem 0.6rem !important;
      }
      
      /* Moderator Stats */
      .moderator-stats {
        flex-direction: column !important;
        gap: 0.75rem !important;
        margin: 1rem 0;
      }
      
      .stat {
        width: 100% !important;
        padding: 0.75rem !important;
        text-align: center;
      }
      
      .stat-value {
        font-size: 1.25rem !important;
      }
      
      .stat-label {
        font-size: 0.8rem !important;
      }
      
      /* Action Buttons in Cards */
      .moderator-actions {
        flex-direction: column !important;
        gap: 0.75rem !important;
        margin-top: 1rem;
      }
      
      .moderator-actions .btn {
        width: 100% !important;
        justify-content: center;
        padding: 0.85rem 1rem !important;
        font-size: 0.95rem !important;
        min-height: 48px;
      }
      
      /* Search Box */
      .search-box {
        margin-bottom: 1.5rem;
      }
      
      .search-box input {
        font-size: 1rem !important;
        padding: 1rem !important;
      }
      
      /* Empty State */
      .empty-state {
        padding: 3rem 1.5rem !important;
      }
      
      .empty-state i {
        font-size: 3rem !important;
      }
      
      .empty-state h3 {
        font-size: 1.25rem !important;
      }
      
      .empty-state p {
        font-size: 0.9rem !important;
      }
      
      /* Back Button */
      .back-btn {
        width: 100%;
        justify-content: center;
        padding: 1rem !important;
        font-size: 1rem !important;
        margin-top: 1rem;
      }
      
      /* Form Modals */
      .modal-dialog {
        margin: 0.5rem;
      }
      
      .modal-content {
        border-radius: 1rem;
      }
      
      .modal-body {
        padding: 1.25rem !important;
      }
      
      .modal-body .form-control,
      .modal-body .form-select {
        font-size: 1rem !important;
        padding: 0.85rem !important;
        min-height: 48px;
      }
      
      .modal-footer {
        flex-direction: column;
        gap: 0.75rem;
      }
      
      .modal-footer .btn {
        width: 100%;
        padding: 1rem !important;
        font-size: 1rem !important;
      }
    }
    
    /* Small Mobile Adjustments */
    @media (max-width: 480px) {
      .topbar {
        padding: 0.85rem;
      }
      
      .topbar h1 {
        font-size: 1.35rem !important;
      }
      
      .moderator-card {
        padding: 1rem !important;
      }
      
      .moderator-avatar {
        width: 50px !important;
        height: 50px !important;
      }
      
      .moderator-info h5 {
        font-size: 1rem !important;
      }
      
      .stat-value {
        font-size: 1.1rem !important;
      }
      
      .stat-label {
        font-size: 0.75rem !important;
      }
      
      .empty-state {
        padding: 2.5rem 1rem !important;
      }
      
      .empty-state i {
        font-size: 2.5rem !important;
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
      <div class="d-flex gap-2">
        <button class="btn-add-mod" data-bs-toggle="modal" data-bs-target="#addModModal">
          <i class="bi bi-person-plus-fill"></i> Add New Admin
        </button>
        <a href="dashboard.php" class="back-btn">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
      <h1>
        <i class="bi bi-shield-check"></i> Manage Admins
      </h1>
      <p>Create new administrator accounts and manage existing admins</p>
      
      <div class="stats-bar">
        <div class="stat-item">
          <span class="stat-num"><?php echo $total_mods; ?></span>
          <span class="stat-label">Total Admins</span>
        </div>
        <div class="stat-item">
          <span class="stat-num"><?php echo $active_mods; ?></span>
          <span class="stat-label">Active</span>
        </div>
        <div class="stat-item">
          <span class="stat-num"><?php echo $disabled_mods; ?></span>
          <span class="stat-label">Disabled</span>
        </div>
      </div>
    </div>

    <!-- Admin Cards -->
    <?php if (empty($moderators)): ?>
      <div class="empty-state">
        <i class="bi bi-person-badge"></i>
        <h3>No Admins Yet</h3>
        <p>Click "Add New Admin" to create your first administrator account.</p>
      </div>
    <?php else: ?>
      <?php foreach ($moderators as $mod): ?>
        <div class="mod-card <?php echo $mod['is_disabled'] == 1 ? 'disabled' : ''; ?>">
          <div class="mod-header">
            <div class="mod-info">
              <div class="mod-avatar">
                <?php if (!empty($mod['profile_picture']) && file_exists('../' . $mod['profile_picture'])): ?>
                  <img src="../<?php echo htmlspecialchars($mod['profile_picture']); ?>" alt="Avatar">
                <?php else: ?>
                  <?php echo !empty($mod['username']) ? strtoupper(substr($mod['username'], 0, 1)) : strtoupper(substr($mod['email'], 0, 1)); ?>
                <?php endif; ?>
              </div>
              <div class="mod-details">
                <h3><?php echo !empty($mod['username']) ? '@' . htmlspecialchars($mod['username']) : htmlspecialchars($mod['email']); ?></h3>
                <div class="meta">
                  <span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($mod['email']); ?></span>
                  <span><i class="bi bi-calendar"></i> Joined: <?php echo date('M j, Y', strtotime($mod['created_at'])); ?></span>
                </div>
              </div>
            </div>
            <div>
              <span class="mod-badge <?php echo $mod['is_disabled'] == 1 ? 'disabled-badge' : ''; ?>">
                <i class="bi bi-shield-check"></i> <?php echo $mod['is_disabled'] == 1 ? 'DISABLED' : 'ADMIN'; ?>
              </span>
            </div>
          </div>

          <?php if ($mod['is_disabled'] == 1): ?>
            <div style="background: rgba(107, 114, 128, 0.1); border: 1px solid rgba(107, 114, 128, 0.3); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
              <div style="font-size: 0.875rem; color: #9ca3af; font-weight: 600; margin-bottom: 0.5rem;">
                <i class="bi bi-info-circle-fill"></i> Disabled Reason
              </div>
              <div style="color: rgba(255, 255, 255, 0.9);">
                <?php echo !empty($mod['disabled_reason']) ? htmlspecialchars($mod['disabled_reason']) : 'No reason provided'; ?>
              </div>
              <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(107, 114, 128, 0.2); font-size: 0.875rem; color: rgba(255, 255, 255, 0.6);">
                <div><i class="bi bi-calendar-x"></i> Disabled: <?php echo !empty($mod['disabled_at']) ? date('M j, Y g:i A', strtotime($mod['disabled_at'])) : 'Unknown'; ?></div>
                <div style="margin-top: 0.25rem;"><i class="bi bi-person-badge"></i> By: <?php echo !empty($mod['disabled_by']) ? htmlspecialchars($mod['disabled_by']) : 'Unknown'; ?></div>
              </div>
            </div>
          <?php endif; ?>

          <div class="mod-actions">
            <?php if ($mod['is_disabled'] == 1): ?>
              <button class="btn-enable" onclick="toggleModStatus(<?php echo $mod['id']; ?>, 'enable', '<?php echo htmlspecialchars($mod['email']); ?>')">
                <i class="bi bi-check-circle-fill"></i> Enable Admin
              </button>
            <?php else: ?>
              <button class="btn-disable" onclick="toggleModStatus(<?php echo $mod['id']; ?>, 'disable', '<?php echo htmlspecialchars($mod['email']); ?>')">
                <i class="bi bi-slash-circle-fill"></i> Disable Admin
              </button>
            <?php endif; ?>
            <button class="btn-delete" onclick="deleteMod(<?php echo $mod['id']; ?>, '<?php echo htmlspecialchars($mod['email']); ?>')">
              <i class="bi bi-trash-fill"></i> Delete
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Add Admin Modal -->
  <div class="modal fade" id="addModModal" tabindex="-1" aria-labelledby="addModModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background: linear-gradient(135deg, rgba(10, 10, 30, 0.98), rgba(22, 33, 62, 0.98)); backdrop-filter: blur(20px); border: 2px solid rgba(139, 92, 246, 0.5);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(139, 92, 246, 0.3);">
          <h5 class="modal-title" id="addModModalLabel" style="color: #c4b5fd; font-weight: 700;">
            <i class="bi bi-person-plus-fill"></i> Add New Admin
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
          <form id="addModForm">
            <div class="mb-3">
              <label for="modEmail" class="form-label" style="color: #c4b5fd; font-weight: 600;">Email Address</label>
              <input type="email" class="form-control" id="modEmail" required
                     style="background: rgba(139, 92, 246, 0.1); border: 2px solid rgba(139, 92, 246, 0.3); color: white; padding: 0.75rem;">
            </div>
            <div class="mb-3">
              <label for="modPassword" class="form-label" style="color: #c4b5fd; font-weight: 600;">Password</label>
              <input type="password" class="form-control" id="modPassword" required
                     style="background: rgba(139, 92, 246, 0.1); border: 2px solid rgba(139, 92, 246, 0.3); color: white; padding: 0.75rem;">
            </div>
            <div class="mb-3">
              <label for="modPasswordConfirm" class="form-label" style="color: #c4b5fd; font-weight: 600;">Confirm Password</label>
              <input type="password" class="form-control" id="modPasswordConfirm" required
                     style="background: rgba(139, 92, 246, 0.1); border: 2px solid rgba(139, 92, 246, 0.3); color: white; padding: 0.75rem;">
            </div>
            <div id="formError" class="alert alert-danger" style="display: none;"></div>
          </form>
        </div>
        <div class="modal-footer" style="border-top: 2px solid rgba(139, 92, 246, 0.3);">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="createModerator()" 
                  style="background: linear-gradient(135deg, #10b981, #059669); border: none; padding: 0.75rem 1.5rem; font-weight: 600;">
            <i class="bi bi-plus-circle-fill"></i> Create Admin
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function createModerator() {
      const email = document.getElementById('modEmail').value.trim();
      const password = document.getElementById('modPassword').value;
      const passwordConfirm = document.getElementById('modPasswordConfirm').value;
      const errorDiv = document.getElementById('formError');
      
      // Validation
      if (!email || !password || !passwordConfirm) {
        errorDiv.textContent = 'All fields are required';
        errorDiv.style.display = 'block';
        return;
      }
      
      if (password !== passwordConfirm) {
        errorDiv.textContent = 'Passwords do not match';
        errorDiv.style.display = 'block';
        return;
      }
      
      if (password.length < 6) {
        errorDiv.textContent = 'Password must be at least 6 characters';
        errorDiv.style.display = 'block';
        return;
      }
      
      errorDiv.style.display = 'none';
      
      // Send request
      fetch('../api/create_moderator.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email: email,
          password: password
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ ' + data.message);
          location.reload();
        } else {
          errorDiv.textContent = data.message;
          errorDiv.style.display = 'block';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred while creating the admin';
        errorDiv.style.display = 'block';
      });
    }
    
    function toggleModStatus(userId, action, email) {
      const actionText = action === 'disable' ? 'DISABLE' : 'ENABLE';
      let reason = '';
      
      if (action === 'disable') {
        reason = prompt(`Enter reason for disabling admin ${email}:`);
        if (!reason || reason.trim() === '') {
          alert('Reason is required to disable an admin');
          return;
        }
      } else {
        if (!confirm(`Are you sure you want to ${actionText} admin ${email}?`)) {
          return;
        }
      }
      
      fetch('../api/toggle_moderator.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          user_id: userId,
          action: action,
          reason: reason
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ ' + data.message);
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    }
    
    function deleteMod(userId, email) {
      if (!confirm(`⚠️ WARNING ⚠️\n\nAre you sure you want to PERMANENTLY DELETE admin ${email}?\n\nThis action cannot be undone!`)) {
        return;
      }
      
      fetch('../api/delete_moderator.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          user_id: userId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ ' + data.message);
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    }
  </script>
</body>
</html>
