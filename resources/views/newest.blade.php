<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints - Newest Posts</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">

  <style>
    /* Poppins font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    /* Animated Starfield Background */
    .stars-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
    }

    .star {
      position: absolute;
      background: white;
      border-radius: 50%;
      animation: twinkle 3s infinite ease-in-out;
    }

    @keyframes twinkle {
      0%, 100% { opacity: 0.2; transform: scale(1); }
      50% { opacity: 1; transform: scale(1.2); }
    }

    @keyframes shoot {
      0% { transform: translateX(0) translateY(0); opacity: 1; }
      100% { transform: translateX(-1000px) translateY(500px); opacity: 0; }
    }

    .shooting-star {
      position: absolute;
      width: 2px;
      height: 2px;
      background: linear-gradient(to right, white, transparent);
      animation: shoot 2s linear infinite;
    }

    /* Hero Section */
    .newest-hero {
      position: relative;
      z-index: 1;
      padding: 3rem 0 2rem;
      text-align: center;
      background: linear-gradient(180deg, rgba(138, 43, 226, 0.1) 0%, transparent 100%);
      border-radius: 20px;
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .newest-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 0%, rgba(138, 43, 226, 0.2), transparent 70%);
      pointer-events: none;
    }

    .newest-hero h1 {
      font-size: 4rem;
      font-weight: 800;
      background: linear-gradient(135deg, #a78bfa, #c084fc, #e879f9);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
      0%, 100% { filter: brightness(1); }
      50% { filter: brightness(1.3); }
    }

    .newest-hero p {
      font-size: 1.3rem;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 300;
    }

    .sparkle-icon {
      display: inline-block;
      animation: sparkle 2s ease-in-out infinite;
      font-size: 5rem;
      margin-bottom: 1rem;
      filter: drop-shadow(0 0 20px rgba(167, 139, 250, 0.6));
    }

    @keyframes sparkle {
      0%, 100% { transform: rotate(0deg) scale(1); }
      25% { transform: rotate(-10deg) scale(1.1); }
      50% { transform: rotate(10deg) scale(1); }
      75% { transform: rotate(-10deg) scale(1.1); }
    }

    /* Stats Bar */
    .stats-bar {
      display: flex;
      justify-content: center;
      gap: 3rem;
      padding: 2rem;
      background: rgba(138, 43, 226, 0.05);
      border-radius: 20px;
      margin-bottom: 2rem;
      border: 1px solid rgba(167, 139, 250, 0.2);
      backdrop-filter: blur(10px);
    }

    .stat-item {
      text-align: center;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      color: #a78bfa;
      display: block;
    }

    .stat-label {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Posts Container */
    #postsContainer {
      position: relative;
      z-index: 1;
    }

    /* Loading Animation */
    .loading-spinner {
      text-align: center;
      padding: 4rem 0;
    }

    .spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(167, 139, 250, 0.1);
      border-top-color: #a78bfa;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* New Badge */
    .new-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(135deg, #a78bfa, #c084fc);
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 4px 15px rgba(167, 139, 250, 0.4);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    /* Enhanced Post Cards */
    .card-post {
      position: relative;
      background: linear-gradient(135deg, rgba(26, 0, 51, 0.9) 0%, rgba(48, 43, 99, 0.9) 100%);
      border: 2px solid rgba(167, 139, 250, 0.3);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backdrop-filter: blur(10px);
      overflow: visible;
    }

    .card-post::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(167, 139, 250, 0.1), transparent);
      transition: left 0.5s;
      border-radius: 20px;
    }

    .card-post:hover::before {
      left: 100%;
    }

    .card-post:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: #a78bfa;
      box-shadow: 0 20px 60px rgba(167, 139, 250, 0.3),
                  0 0 40px rgba(167, 139, 250, 0.2);
    }

    .time-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.3rem 1rem;
      background: rgba(167, 139, 250, 0.1);
      border: 1px solid rgba(167, 139, 250, 0.3);
      border-radius: 20px;
      color: #a78bfa;
      font-size: 0.9rem;
      font-weight: 500;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 5rem 2rem;
      background: rgba(167, 139, 250, 0.02);
      border-radius: 20px;
      border: 2px dashed rgba(167, 139, 250, 0.2);
    }

    .empty-state i {
      font-size: 6rem;
      color: rgba(167, 139, 250, 0.3);
      margin-bottom: 2rem;
    }
  </style>
</head>
<body data-user-id="{{ session('user_id') }}">
  <!-- Animated Starfield Background -->
  <div class="stars-bg" id="starsBg"></div>

  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="{{ route('dashboard') }}" class="lp-brand" aria-label="+EXPoints home">
        <img src="{{ asset('assets/img/EXPoints Logo.png') }}" alt="+EXPoints" class="lp-brand-img">
      </a>

      <form class="search" role="search">
        <input type="text" placeholder="Search for a Review, a Game, Anything" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>

      <div class="right">
        <button class="icon" title="Filter"><i class="bi bi-funnel"></i></button>
        <button class="icon" title="Settings"><i class="bi bi-gear"></i></button>
        <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
        <a href="{{ route('profile.show') }}" class="avatar-nav">
          <img src="{{ asset($userProfilePicture) }}" alt="Profile" class="avatar-img" onerror="this.src='{{ asset('assets/img/cat1.jpg') }}'">
        </a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <!-- Hero Section -->
    <div class="newest-hero">
      <div class="sparkle-icon">✨</div>
      <h1>FRESH CONTENT</h1>
      <p>The latest reviews hot off the press</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-value" id="totalPosts">{{ count($posts) }}</span>
        <span class="stat-label">New Posts</span>
      </div>
      <div class="stat-item">
        @php
          $today = date('Y-m-d');
          $todayCount = 0;
          foreach ($posts as $p) {
            if (date('Y-m-d', strtotime($p['created_at'])) == $today) {
              $todayCount++;
            }
          }
        @endphp
        <span class="stat-value" id="todayPosts">{{ $todayCount }}</span>
        <span class="stat-label">Posted Today</span>
      </div>
    </div>

    <!-- Posts Container -->
    <div id="postsContainer">
      @if(count($posts) > 0)
        @foreach($posts as $post)
          <div class="card-post" data-post-id="{{ $post['id'] }}">
            <div class="post-header">
              <div class="row gap-3 align-items-start">
                <div class="col-auto">
                  <div class="avatar-us avatar-loading">
                    <div class="star-loader">⭐</div>
                    <img src="{{ asset($post['profile_picture'] ?? 'assets/img/cat1.jpg') }}"
                         alt="Profile"
                         loading="lazy"
                         class="profile-lazy-img"
                         onerror="this.src='{{ asset('assets/img/cat1.jpg') }}'"
                         onload="this.classList.add('loaded'); this.parentElement.classList.remove('avatar-loading');">
                  </div>
                </div>
                <div class="col">
                  <div class="game-badge">{{ $post['game'] }}</div>
                  <h2 class="title mb-1">{{ $post['title'] }}</h2>
                  <div class="handle mb-3">{{ '@' . $post['username'] }}</div>
                  <p class="mb-3">{!! nl2br(e($post['content'])) !!}</p>
                  <div class="time-badge">
                    <i class="bi bi-clock"></i>
                    {{ \Carbon\Carbon::parse($post['created_at'])->format('M j, Y g:i A') }}
                  </div>
                </div>
              </div>
            </div>
            <div class="actions">
              <span class="a like-btn" data-liked="false"><i class="bi bi-star"></i><b>{{ $post['likes'] ?? 0 }}</b></span>
              <span class="a comment-btn" data-comments="{{ $post['comments'] ?? 0 }}"><i class="bi bi-chat-left-text"></i><b>{{ $post['comments'] ?? 0 }}</b></span>
            </div>
          </div>
        @endforeach
      @else
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.3rem;">No posts yet. Be the first to share!</p>
        </div>
      @endif
    </div>
  </main>

  <!-- Slide-in sidebar -->
  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <button class="side-btn" onclick="window.location.href='{{ route('dashboard') }}'" title="Home"><i class="bi bi-house"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('bookmarks') }}'" title="Bookmarks"><i class="bi bi-bookmark"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('games') }}'" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('popular') }}'" title="Popular"><i class="bi bi-compass"></i></button>
        <button class="side-btn active" title="Newest"><i class="bi bi-star-fill"></i></button>
        <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
      </div>
    </div>
  </aside>

  <script>
    const currentUserId = @json(session('user_id'));

    // Create animated starfield
    function createStars() {
      const container = document.getElementById('starsBg');

      // Regular stars
      for (let i = 0; i < 100; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.width = Math.random() * 3 + 1 + 'px';
        star.style.height = star.style.width;
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.animationDuration = Math.random() * 3 + 2 + 's';
        star.style.animationDelay = Math.random() * 3 + 's';
        container.appendChild(star);
      }

      // Shooting stars
      setInterval(() => {
        const shootingStar = document.createElement('div');
        shootingStar.className = 'shooting-star';
        shootingStar.style.left = Math.random() * 100 + '%';
        shootingStar.style.top = Math.random() * 50 + '%';
        shootingStar.style.width = Math.random() * 100 + 50 + 'px';
        container.appendChild(shootingStar);

        setTimeout(() => shootingStar.remove(), 2000);
      }, 3000);
    }

    createStars();

    // Logout functionality
    document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.querySelector('.logout-btn-sidebar');

      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          sessionStorage.removeItem('welcomeShown');
          window.location.href = '{{ route('logout') }}';
        });
      }
    });
  </script>
</body>
</html>
