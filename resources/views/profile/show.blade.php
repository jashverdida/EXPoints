<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Profile</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}" />

  <style>
    /* Full-page loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #0a1a4d 0%, #1b378d 50%, #0a1a4d 100%);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
        animation: gradientShift 3s ease infinite;
    }

    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .loading-overlay.active {
        display: flex;
    }

    .loading-stars-container {
        position: relative;
        width: 200px;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Single glowing star with halo */
    .loading-star-main {
        font-size: 5rem;
        position: relative;
        animation: starRotateGlow 2s ease-in-out infinite;
        filter: drop-shadow(0 0 30px rgba(255, 215, 0, 1));
    }

    /* Halo rings around star */
    .star-halo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 2px solid rgba(255, 215, 0, 0.4);
        border-radius: 50%;
        animation: haloExpand 2s ease-out infinite;
    }

    .star-halo:nth-child(1) {
        width: 120px;
        height: 120px;
        animation-delay: 0s;
    }

    .star-halo:nth-child(2) {
        width: 160px;
        height: 160px;
        animation-delay: 0.5s;
    }

    .star-halo:nth-child(3) {
        width: 200px;
        height: 200px;
        animation-delay: 1s;
    }

    @keyframes starRotateGlow {
        0% {
            transform: rotate(0deg) scale(1);
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
        }
        50% {
            transform: rotate(180deg) scale(1.2);
            filter: drop-shadow(0 0 40px rgba(255, 215, 0, 1)) drop-shadow(0 0 60px rgba(255, 215, 0, 0.6));
        }
        100% {
            transform: rotate(360deg) scale(1);
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
        }
    }

    @keyframes haloExpand {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0;
            border-width: 3px;
        }
        50% {
            opacity: 0.6;
            border-width: 2px;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.3);
            opacity: 0;
            border-width: 1px;
        }
    }

    .loading-text {
        margin-top: 3rem;
        color: white;
        font-size: 1.5rem;
        font-weight: 600;
        animation: textFade 2s ease-in-out infinite;
        text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
    }

    @keyframes textFade {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }

    /* Progress bar container */
    .progress-container {
        width: 400px;
        height: 6px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
        margin-top: 2rem;
        position: relative;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }

    .loading-overlay .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
        background-size: 200% 100%;
        border-radius: 10px;
        animation: progressGlow 2s ease-in-out infinite, progressMove 15s ease-out forwards;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.8), inset 0 0 10px rgba(255, 255, 255, 0.5);
        width: 0%;
    }

    @keyframes progressGlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    @keyframes progressMove {
        0% { width: 0%; }
        95% { width: 95%; }
        100% { width: 100%; }
    }

    .progress-text {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }

    /* Floating Particles Background */
    .profile-particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
      overflow: hidden;
    }

    .profile-particle {
      position: absolute;
      font-size: 1.5rem;
      opacity: 0.3;
      animation: floatParticle 20s linear infinite;
    }

    @keyframes floatParticle {
      0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 0.3;
      }
      90% {
        opacity: 0.3;
      }
      100% {
        transform: translateY(-100px) rotate(360deg);
        opacity: 0;
      }
    }

    /* Enhanced level bar */
    .level-bar {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 999px;
      height: 12px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(56, 160, 255, 0.3) inset;
    }

    .level-bar .progress-bar {
      background: linear-gradient(90deg, #ffd700, #38a0ff, #ffd700);
      background-size: 200% 100%;
      height: 100%;
      border-radius: 999px;
      box-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
      animation: progressShimmer 3s ease-in-out infinite;
    }

    @keyframes progressShimmer {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    /* ===== PROFILE AVATAR & CARD IMPROVEMENTS ===== */

    .avatar-wrap {
      position: relative;
      width: 160px;
      height: 160px;
      margin: 0 auto 1.5rem;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 5px;
      box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.4),
        0 0 0 3px rgba(110, 160, 255, 0.3) inset,
        0 0 40px rgba(102, 126, 234, 0.3);
      z-index: 2;
      transition: all 0.3s ease;
    }

    .avatar-wrap:hover {
      transform: scale(1.05);
      box-shadow:
        0 12px 48px rgba(0, 0, 0, 0.5),
        0 0 0 3px rgba(110, 160, 255, 0.5) inset,
        0 0 60px rgba(102, 126, 234, 0.5);
    }

    .avatar-xl {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      object-position: center;
      display: block;
      border: 3px solid rgba(255, 255, 255, 0.1);
    }

    .content-shift {
      text-align: center;
    }

    .card-glass {
      border: 1px solid rgba(60, 78, 145, 0.5);
      border-radius: 24px;
      background: linear-gradient(180deg, rgba(15, 22, 49, 0.95), rgba(17, 26, 58, 0.9));
      box-shadow:
        0 0 0 1px rgba(110, 160, 255, 0.15) inset,
        0 20px 60px rgba(0, 0, 0, 0.6),
        0 0 80px rgba(56, 160, 255, 0.1);
      backdrop-filter: blur(20px);
    }

    .profile-name {
      color: #fff;
      font-weight: 800;
      font-size: 2rem;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .profile-handle {
      color: rgba(255, 255, 255, 0.7);
      font-weight: 600;
      font-size: 1.1rem;
    }

    .lvl-pill {
      display: inline-block;
      padding: 0.4rem 1rem;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 999px;
      border: 2px solid rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    .stats-row > span {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.3rem 0.8rem;
      background: rgba(102, 126, 234, 0.15);
      border-radius: 999px;
      border: 1px solid rgba(110, 160, 255, 0.2);
      color: #fff;
    }

    .edit-controls {
      position: absolute;
      top: 1rem;
      right: 1rem;
      display: flex;
      gap: 0.5rem;
      z-index: 10;
    }

    .mini-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(110, 160, 255, 0.2);
      border-radius: 12px;
      padding: 1rem;
      text-align: center;
    }

    .mini-title {
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.8rem;
      margin-bottom: 0.5rem;
    }

    .mini-value {
      color: #fff;
      font-weight: 600;
    }

    .thumb-box img, .genre-badge {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
    }

    .card-pill {
      background: linear-gradient(180deg, rgba(15, 22, 49, 0.95), rgba(17, 26, 58, 0.9));
      border: 1px solid rgba(60, 78, 145, 0.5);
      border-radius: 16px;
    }

    .best-title {
      color: #fff;
      font-weight: 700;
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }

    .best-post {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(110, 160, 255, 0.2);
      border-radius: 8px;
      padding: 0.75rem;
      color: #fff;
      transition: all 0.3s ease;
    }

    .best-post:hover {
      background: rgba(110, 160, 255, 0.15);
      border-color: rgba(110, 160, 255, 0.4);
    }

    .profile-bio {
      color: rgba(255, 255, 255, 0.8);
    }

    /* Modal styles */
    .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(5px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9998;
    }

    .modal-card {
      background: linear-gradient(180deg, rgba(15, 22, 49, 0.98), rgba(17, 26, 58, 0.95));
      border: 1px solid rgba(110, 160, 255, 0.3);
      border-radius: 16px;
      padding: 2rem;
      color: #fff;
      max-width: 500px;
      width: 90%;
    }

    .post-selection-item:hover {
      background: rgba(110, 160, 255, 0.15) !important;
      border-color: rgba(110, 160, 255, 0.5) !important;
    }

    .post-selection-item.selected {
      background: rgba(110, 160, 255, 0.25) !important;
      border-color: #38a0ff !important;
    }

    .bg-exp {
      background: linear-gradient(135deg, #0a1a4d 0%, #1b378d 50%, #0a1a4d 100%);
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
    }

    @media (max-width: 768px) {
      .avatar-wrap {
        width: 130px;
        height: 130px;
      }

      .profile-name {
        font-size: 1.6rem;
      }

      .progress-container {
        width: 280px;
      }
    }
  </style>
</head>
<body class="bg-exp">

  <!-- Loading Overlay -->
  <div class="loading-overlay" id="loadingOverlay">
      <div class="loading-stars-container">
          <div class="star-halo"></div>
          <div class="star-halo"></div>
          <div class="star-halo"></div>
          <div class="loading-star-main">⭐</div>
      </div>
      <div class="loading-text">Loading your dashboard...</div>
      <div class="progress-container">
          <div class="progress-bar"></div>
      </div>
      <div class="progress-text">Please wait...</div>
  </div>

  <div class="container py-4">
    <!-- Floating particles effect -->
    <div class="profile-particles" id="profileParticles"></div>

    <div class="d-flex align-items-center gap-2 mb-3">
      <a href="{{ route('dashboard') }}">
        <img id="brand_logo" src="{{ asset('assets/Assets/EXPoints Logo.png') }}" alt="EXPoints" class="brand-logo" style="height: 120px; cursor: pointer; filter: drop-shadow(0 0 20px rgba(56, 160, 255, 0.4)); transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.filter='drop-shadow(0 0 30px rgba(56, 160, 255, 0.6))'" onmouseout="this.style.transform='scale(1)'; this.style.filter='drop-shadow(0 0 20px rgba(56, 160, 255, 0.4))'" />
      </a>
    </div>

    <div class="row g-4">
      <!-- MAIN -->
      <div class="col-md-8">
        <div class="card card-glass p-3 p-md-4 position-relative overflow-visible">

          <!-- Edit controls -->
          <div class="edit-controls">
            <button id="btnToggleName"   class="btn btn-sm btn-outline-light">Toggle Name</button>
            <button id="btnEdit"         class="btn btn-sm btn-outline-light">Edit Profile</button>
            <button id="btnCancel"       class="btn btn-sm btn-danger d-none">Cancel</button>
            <button id="btnSave"         class="btn btn-sm btn-success d-none">Save Changes</button>
          </div>

          <!-- Avatar with proper sizing, centering, and error handling -->
          <div class="avatar-wrap">
            <img id="avatar"
                 src="{{ $profilePicture }}"
                 alt="{{ $displayName }}'s Profile"
                 class="avatar-xl"
                 data-edit="img"
                 onerror="this.src='{{ asset('assets/img/cat1.jpg') }}'" />
          </div>

          <!-- Header -->
          <div class="content-shift">
            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-2">
              <h2 id="display_name" class="profile-name mb-0" data-edit="text"
                data-fullname="{{ $fullName ?? '' }}">{{ $displayName }}</h2>
            </div>

            <span id="handle" class="profile-handle d-block mb-3">{{ $handle }}</span>

            <!-- Level & Stats -->
            <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
              <span class="lvl-pill">LVL {{ $level }}</span>
              <div class="level-bar flex-grow-1" style="max-width: 200px;">
                <div class="progress-bar" style="width: {{ $levelProgress }}%"></div>
              </div>
            </div>

            <!-- Stats Row -->
            <div class="d-flex justify-content-center gap-3 mb-3 stats-row flex-wrap">
              <span><i class="bi bi-star-fill"></i><span id="stars">{{ $totalStars ?? 0 }}</span></span>
              <span><i class="bi bi-file-earmark-text-fill"></i><span id="reviews">{{ $totalReviews ?? 0 }}</span></span>
              <span><i class="bi bi-trophy-fill"></i>{{ $expPoints }} EXP</span>
            </div>

            <!-- Bio -->
            @if(empty($bio))
              <p id="bio" class="profile-bio mt-3" data-edit="textarea" style="color: var(--muted); font-style: italic;">Enter Your Bio!</p>
            @else
              <p id="bio" class="profile-bio mt-3" data-edit="textarea">{!! nl2br(e($bio)) !!}</p>
            @endif
          </div>

          <!-- Info grid -->
          <div class="row g-3 align-items-stretch mt-2">
            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Date Started</div>
                <div id="date_started" class="mini-value">
                  {{ $startedDate }}
                </div>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Favorite Game</div>
                <div class="thumb-box">
                  <img id="game_img" src="{{ asset('assets/img/Favorite Game.png') }}" alt="Favorite Game" data-edit="img" />
                </div>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Favorite Genres</div>
                <img id="genre_img" src="{{ asset('assets/img/Favorite Genre.png') }}" alt="Favorite Genres" class="genre-badge" data-edit="img" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT SIDEBAR -->
      <div class="col-md-4">
        <div class="card card-pill p-3 p-md-4">
          <h3 class="best-title">Best Posts:</h3>
          <div id="best_posts_list">
            @if(empty($bestPosts) || count($bestPosts) === 0)
              <p class="text-muted small">No posts yet. Create your first post!</p>
            @else
              @foreach($bestPosts as $p)
                <div class="best-post d-block mb-2" data-post-id="{{ $p['id'] }}">
                  <div class="small fw-semibold text-truncate">{{ $p['title'] }}</div>
                  <div class="d-flex align-items-center gap-3 small mt-1">
                    <span><i class="bi bi-star-fill me-1"></i>{{ (int)($p['like_count'] ?? 0) }}</span>
                    <span><i class="bi bi-chat-fill me-1"></i>{{ (int)($p['comment_count'] ?? 0) }}</span>
                  </div>
                </div>
              @endforeach
            @endif
          </div>
          <!-- Hidden until Edit -->
          <button id="btnEditShowcase" class="btn btn-sm btn-light-subtle mt-2 w-100 d-none">Edit Selection</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Best Posts Selection Modal -->
  <div id="postsSelectionModal" class="modal-backdrop d-none">
    <div class="modal-card" style="max-width: 600px; max-height: 80vh; overflow-y: auto;">
      <h5 class="mb-3">Select Your Best Posts (Choose up to 3)</h5>
      <div id="postsSelectionList">
        @foreach($allPosts ?? [] as $post)
          <div class="post-selection-item" data-post-id="{{ $post['id'] }}" style="padding: 1rem; margin-bottom: 0.5rem; border: 2px solid transparent; border-radius: 0.5rem; background: rgba(255,255,255,0.05); cursor: pointer; transition: all 0.3s;">
            <div class="fw-semibold">{{ $post['title'] }}</div>
            <div class="d-flex align-items-center gap-3 small mt-1">
              <span><i class="bi bi-star-fill me-1"></i>{{ (int)($post['like_count'] ?? 0) }}</span>
              <span><i class="bi bi-chat-fill me-1"></i>{{ (int)($post['comment_count'] ?? 0) }}</span>
            </div>
          </div>
        @endforeach
      </div>
      <div class="d-flex justify-content-end gap-2 mt-3">
        <button id="closePostsModal" class="btn btn-danger btn-sm px-4">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Save confirmation -->
  <div id="saveModal" class="modal-backdrop d-none">
    <div class="modal-card">
      <h5 class="mb-2 text-center">Save Changes?</h5>
      <p class="text-center mb-3">All changes here will reflect on your profile</p>
      <div class="d-flex justify-content-center gap-2">
        <button id="confirmYes" class="btn btn-success btn-sm px-4">YES</button>
        <button id="confirmNo"  class="btn btn-danger  btn-sm px-4">NO</button>
      </div>
    </div>
  </div>

  <script>
    // Pass data to JavaScript
    const allPosts = @json($allPosts ?? []);
    const userId = @json(session('user_id'));
    const userData = @json([
        'first_name' => $firstName ?? '',
        'middle_name' => $middleName ?? '',
        'last_name' => $lastName ?? '',
        'suffix' => $suffix ?? '',
        'username' => $displayName,
        'bio' => $bio ?? '',
        'profile_picture' => $profilePicture,
        'exp_points' => $expPoints ?? 0
    ]);

    // Create floating particles
    function createProfileParticles() {
      const container = document.getElementById('profileParticles');
      const particles = ['⭐', '🎮', '🏆', '⚡', '💫', '🌟'];
      const particleCount = 15;

      for (let i = 0; i < particleCount; i++) {
        setTimeout(() => {
          const particle = document.createElement('div');
          particle.className = 'profile-particle';
          particle.textContent = particles[Math.floor(Math.random() * particles.length)];

          // Random horizontal position
          particle.style.left = Math.random() * 100 + '%';

          // Random animation duration
          const duration = 15 + Math.random() * 10;
          particle.style.animationDuration = duration + 's';

          // Random delay
          const delay = Math.random() * 5;
          particle.style.animationDelay = delay + 's';

          // Random size
          const scale = 0.8 + Math.random() * 0.7;
          particle.style.transform = `scale(${scale})`;

          container.appendChild(particle);

          // Recreate particle after animation
          particle.addEventListener('animationiteration', () => {
            particle.style.left = Math.random() * 100 + '%';
          });
        }, i * 150);
      }
    }

    // Initialize particles on load
    document.addEventListener('DOMContentLoaded', createProfileParticles);

    // Loading screen logic
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('loadingOverlay');

        // Show loading briefly on page load
        overlay.classList.add('active');

        // Hide after short delay
        setTimeout(() => {
            overlay.classList.remove('active');
        }, 1500);
    });

    // Edit mode functionality
    document.addEventListener('DOMContentLoaded', function() {
        const btnToggleName = document.getElementById('btnToggleName');
        const btnEdit = document.getElementById('btnEdit');
        const btnCancel = document.getElementById('btnCancel');
        const btnSave = document.getElementById('btnSave');
        const btnEditShowcase = document.getElementById('btnEditShowcase');
        const displayName = document.getElementById('display_name');
        const saveModal = document.getElementById('saveModal');
        const postsSelectionModal = document.getElementById('postsSelectionModal');
        const closePostsModal = document.getElementById('closePostsModal');
        const confirmYes = document.getElementById('confirmYes');
        const confirmNo = document.getElementById('confirmNo');

        let showingFullName = false;
        let isEditMode = false;

        // Toggle between username and full name
        if (btnToggleName) {
            btnToggleName.addEventListener('click', function() {
                const fullName = displayName.getAttribute('data-fullname');
                const username = userData.username;

                if (showingFullName) {
                    displayName.textContent = username;
                } else {
                    displayName.textContent = fullName || username;
                }
                showingFullName = !showingFullName;
            });
        }

        // Enter edit mode
        if (btnEdit) {
            btnEdit.addEventListener('click', function() {
                isEditMode = true;
                btnEdit.classList.add('d-none');
                btnCancel.classList.remove('d-none');
                btnSave.classList.remove('d-none');
                btnEditShowcase.classList.remove('d-none');

                // Make editable elements interactive
                document.querySelectorAll('[data-edit]').forEach(el => {
                    el.style.outline = '2px dashed rgba(110, 160, 255, 0.5)';
                    el.style.cursor = 'pointer';
                });
            });
        }

        // Cancel edit mode
        if (btnCancel) {
            btnCancel.addEventListener('click', function() {
                isEditMode = false;
                btnEdit.classList.remove('d-none');
                btnCancel.classList.add('d-none');
                btnSave.classList.add('d-none');
                btnEditShowcase.classList.add('d-none');

                // Remove edit styling
                document.querySelectorAll('[data-edit]').forEach(el => {
                    el.style.outline = '';
                    el.style.cursor = '';
                });
            });
        }

        // Save changes
        if (btnSave) {
            btnSave.addEventListener('click', function() {
                saveModal.classList.remove('d-none');
            });
        }

        // Confirm save
        if (confirmYes) {
            confirmYes.addEventListener('click', function() {
                saveModal.classList.add('d-none');
                // TODO: Implement save to Supabase
                alert('Changes saved!');
                btnCancel.click(); // Exit edit mode
            });
        }

        // Cancel save
        if (confirmNo) {
            confirmNo.addEventListener('click', function() {
                saveModal.classList.add('d-none');
            });
        }

        // Open posts selection modal
        if (btnEditShowcase) {
            btnEditShowcase.addEventListener('click', function() {
                postsSelectionModal.classList.remove('d-none');
            });
        }

        // Close posts selection modal
        if (closePostsModal) {
            closePostsModal.addEventListener('click', function() {
                postsSelectionModal.classList.add('d-none');
            });
        }

        // Post selection
        document.querySelectorAll('.post-selection-item').forEach(item => {
            item.addEventListener('click', function() {
                const selected = document.querySelectorAll('.post-selection-item.selected');

                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                } else if (selected.length < 3) {
                    this.classList.add('selected');
                } else {
                    alert('You can only select up to 3 posts!');
                }
            });
        });
    });
  </script>
  <script src="{{ asset('assets/js/loading-screen.js') }}"></script>
  <script src="{{ asset('assets/js/profile.js') }}"></script>
</body>
</html>
