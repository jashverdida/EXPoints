<?php
require_once __DIR__ . '/../config/supabase-session.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get ban details from session
$ban_reason = $_SESSION['ban_reason'] ?? 'Your account has been banned for violating community guidelines.';
$banned_at = $_SESSION['banned_at'] ?? null;
$banned_by = $_SESSION['banned_by'] ?? 'Administrator';

// Clear session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Banned - EXPoints</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap");
    
    body {
      font-family: "Poppins", sans-serif !important;
      background: linear-gradient(135deg, #1a0a0a 0%, #2d0a0a 50%, #1a0505 100%);
      min-height: 100vh;
      color: #f6f9ff;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    /* Animated danger background */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 30% 40%, rgba(239, 68, 68, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 70% 60%, rgba(220, 38, 38, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(185, 28, 28, 0.15) 0%, transparent 60%);
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
    
    .ban-container {
      position: relative;
      z-index: 1;
      max-width: 600px;
      width: 90%;
    }
    
    .ban-card {
      background: linear-gradient(135deg, rgba(20, 10, 10, 0.95), rgba(40, 15, 15, 0.95));
      backdrop-filter: blur(20px);
      border: 3px solid rgba(239, 68, 68, 0.5);
      border-radius: 1.5rem;
      padding: 3rem;
      box-shadow: 0 20px 60px rgba(239, 68, 68, 0.4), 
                  0 0 100px rgba(239, 68, 68, 0.2);
      text-align: center;
      position: relative;
      overflow: hidden;
      animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .ban-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.3), transparent);
      animation: shine 2s infinite;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .ban-icon {
      font-size: 5rem;
      color: #ef4444;
      margin-bottom: 1.5rem;
      animation: bounce 1s ease-in-out infinite;
      filter: drop-shadow(0 10px 30px rgba(239, 68, 68, 0.6));
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .ban-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: #fca5a5;
      margin-bottom: 1rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-shadow: 0 0 20px rgba(239, 68, 68, 0.8);
    }
    
    .ban-message {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 2rem;
      line-height: 1.7;
    }
    
    .ban-reason-box {
      background: rgba(239, 68, 68, 0.1);
      border: 2px solid rgba(239, 68, 68, 0.4);
      border-radius: 1rem;
      padding: 1.5rem;
      margin: 2rem 0;
      text-align: left;
    }
    
    .ban-reason-label {
      font-size: 0.9rem;
      color: #fca5a5;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .ban-reason-text {
      color: rgba(255, 255, 255, 0.95);
      line-height: 1.6;
      font-size: 1rem;
    }
    
    .ban-meta {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.6);
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    .btn-understood {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      border: none;
      color: white;
      padding: 1rem 3rem;
      border-radius: 0.75rem;
      font-weight: 700;
      font-size: 1.1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: all 0.3s;
      box-shadow: 0 8px 30px rgba(239, 68, 68, 0.5);
      cursor: pointer;
    }
    
    .btn-understood:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(239, 68, 68, 0.7);
      background: linear-gradient(135deg, #dc2626, #b91c1c);
    }
    
    .warning-stripe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: repeating-linear-gradient(
        45deg,
        #ef4444,
        #ef4444 20px,
        #fbbf24 20px,
        #fbbf24 40px
      );
      animation: stripe-move 1s linear infinite;
    }
    
    @keyframes stripe-move {
      0% { background-position: 0 0; }
      100% { background-position: 40px 0; }
    }
  </style>
</head>
<body>
  <div class="ban-container">
    <div class="ban-card">
      <div class="warning-stripe"></div>
      
      <div class="ban-icon">
        <i class="bi bi-shield-fill-x"></i>
      </div>
      
      <h1 class="ban-title">
        ⚠️ You Are Banned! ⚠️
      </h1>
      
      <p class="ban-message">
        Your account has been permanently suspended from accessing EXPoints. You will not be able to log in or participate in the community.
      </p>
      
      <div class="ban-reason-box">
        <div class="ban-reason-label">
          <i class="bi bi-exclamation-triangle-fill"></i>
          Reason for Ban
        </div>
        <div class="ban-reason-text">
          <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($ban_reason); ?>
        </div>
        
        <?php
require_once __DIR__ . '/../config/supabase-session.php'; if ($banned_at): ?>
          <div class="ban-meta">
            <div><i class="bi bi-calendar-x"></i> Banned on: <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo date('F j, Y \a\t g:i A', strtotime($banned_at)); ?></div>
            <div><i class="bi bi-person-badge"></i> Banned by: <?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($banned_by); ?></div>
          </div>
        <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>
      </div>
      
      <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.95rem; margin-bottom: 2rem;">
        If you believe this ban was issued in error, please contact the administrators at <strong style="color: #60a5fa;">support@expoints.com</strong>
      </p>
      
      <button class="btn-understood" onclick="window.location.href='login.php'">
        <i class="bi bi-check-circle-fill"></i> UNDERSTOOD
      </button>
    </div>
  </div>
</body>
</html>
