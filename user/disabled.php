<?php
require_once __DIR__ . '/../config/supabase-session.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get disable details from session
$disabled_reason = $_SESSION['disabled_reason'] ?? 'Your account has been disabled by an administrator.';
$disabled_at = $_SESSION['disabled_at'] ?? null;
$disabled_by = $_SESSION['disabled_by'] ?? 'Administrator';

// Clear session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Disabled - EXPoints</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap");
    
    body {
      font-family: "Poppins", sans-serif !important;
      background: linear-gradient(135deg, #0a1a2d 0%, #1a2a4d 50%, #0d1b3a 100%);
      min-height: 100vh;
      color: #f6f9ff;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    /* Animated blue background */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 30% 40%, rgba(59, 130, 246, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 70% 60%, rgba(29, 78, 216, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.15) 0%, transparent 60%);
      animation: pulse 4s ease-in-out infinite;
      pointer-events: none;
      z-index: 0;
    }
    
    @keyframes pulse {
      0%, 100% {
        opacity: 1;
        transform: scale(1);
      }
      50% {
        opacity: 0.8;
        transform: scale(1.05);
      }
    }
    
    .disabled-container {
      position: relative;
      z-index: 1;
      max-width: 600px;
      width: 90%;
    }
    
    .disabled-card {
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
      backdrop-filter: blur(20px);
      border: 3px solid rgba(59, 130, 246, 0.5);
      border-radius: 1.5rem;
      padding: 3rem;
      box-shadow: 0 20px 60px rgba(59, 130, 246, 0.4), 
                  0 0 100px rgba(59, 130, 246, 0.2);
      text-align: center;
      position: relative;
      overflow: hidden;
      animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: scale(0.95);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }
    
    .disabled-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
      animation: shine 2s infinite;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .disabled-icon {
      font-size: 5rem;
      color: #3b82f6;
      margin-bottom: 1.5rem;
      animation: bounce 1s ease-in-out infinite;
      filter: drop-shadow(0 10px 30px rgba(59, 130, 246, 0.6));
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .disabled-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: #60a5fa;
      margin-bottom: 1rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-shadow: 0 0 20px rgba(59, 130, 246, 0.8);
    }
    
    .disabled-message {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 2rem;
      line-height: 1.7;
    }
    
    .disabled-reason-box {
      background: rgba(59, 130, 246, 0.1);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1rem;
      padding: 1.5rem;
      margin: 2rem 0;
      text-align: left;
    }
    
    .disabled-reason-label {
      font-size: 0.9rem;
      color: #60a5fa;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .disabled-reason-text {
      color: rgba(255, 255, 255, 0.95);
      line-height: 1.6;
      font-size: 1rem;
    }
    
    .disabled-meta {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    .btn-understood {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      border: none;
      color: white;
      padding: 1rem 3rem;
      border-radius: 0.75rem;
      font-weight: 700;
      font-size: 1.1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: all 0.3s;
      box-shadow: 0 8px 30px rgba(59, 130, 246, 0.5);
      cursor: pointer;
    }
    
    .btn-understood:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(59, 130, 246, 0.7);
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }
    
    .info-stripe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, #3b82f6, #60a5fa, #3b82f6);
      background-size: 200% 100%;
      animation: gradient-move 3s linear infinite;
    }
    
    @keyframes gradient-move {
      0% { background-position: 0% 0%; }
      100% { background-position: 200% 0%; }
    }
  </style>
</head>
<body>
  <div class="disabled-container">
    <div class="disabled-card">
      <div class="info-stripe"></div>
      
      <div class="disabled-icon">
        <i class="bi bi-slash-circle"></i>
      </div>
      
      <h1 class="disabled-title">
        Account Disabled
      </h1>
      
      <p class="disabled-message">
        Your moderator account has been temporarily disabled by an administrator. You will not be able to access the moderator dashboard until your account is re-enabled.
      </p>
      
      <div class="disabled-reason-box">
        <div class="disabled-reason-label">
          <i class="bi bi-info-circle-fill"></i>
          Reason for Disable
        </div>
        <div class="disabled-reason-text">
          <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($disabled_reason); ?>
        </div>
        
        <?php
require_once __DIR__ . '/../config/supabase-session.php'; if ($disabled_at): ?>
          <div class="disabled-meta">
            <div><i class="bi bi-calendar-x"></i> Disabled on: <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo date('F j, Y \a\t g:i A', strtotime($disabled_at)); ?></div>
            <div style="margin-top: 0.25rem;"><i class="bi bi-person-badge"></i> Disabled by: <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($disabled_by); ?></div>
          </div>
        <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>
      </div>
      
      <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.95rem; margin-bottom: 2rem;">
        If you believe this was done in error, please contact an administrator at <strong style="color: #60a5fa;">admin@expoints.com</strong>
      </p>
      
      <button class="btn-understood" onclick="window.location.href='login.php'">
        <i class="bi bi-check-circle-fill"></i> UNDERSTOOD
      </button>
    </div>
  </div>
</body>
</html>
