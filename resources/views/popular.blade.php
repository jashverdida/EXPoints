<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints - Popular Posts</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">

  <style>
    /* Poppins font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0a0a2e 0%, #16213e 50%, #0f3460 100%);
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    /* Animated Background Particles */
    .particles-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
    }

    .particle {
      position: absolute;
      border-radius: 50%;
      animation: float 20s infinite ease-in-out;
      opacity: 0.1;
    }

    @keyframes float {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(50px, -50px) rotate(90deg); }
      50% { transform: translate(-30px, -100px) rotate(180deg); }
      75% { transform: translate(-80px, -50px) rotate(270deg); }
    }

    /* Hero Section */
    .popular-hero {
      position: relative;
      z-index: 1;
      padding: 3rem 0 2rem;
      text-align: center;
      background: linear-gradient(180deg, rgba(255, 107, 107, 0.1) 0%, transparent 100%);
      border-radius: 20px;
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .popular-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 0%, rgba(255, 107, 107, 0.2), transparent 70%);
      pointer-events: none;
    }

    .popular-hero h1 {
      font-size: 4rem;
      font-weight: 800;
      background: linear-gradient(135deg, #ff6b6b, #ff8e53, #ffb347);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      text-shadow: 0 0 40px rgba(255, 107, 107, 0.5);
      animation: glow 2s ease-in-out infinite alternate;
    }

    @keyframes glow {
      from { filter: drop-shadow(0 0 10px rgba(255, 107, 107, 0.5)); }
      to { filter: drop-shadow(0 0 20px rgba(255, 107, 107, 0.8)); }
    }

    .popular-hero p {
      font-size: 1.3rem;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 300;
    }

    .fire-icon {
      display: inline-block;
      animation: bounce 1s infinite;
      font-size: 5rem;
      margin-bottom: 1rem;
      filter: drop-shadow(0 0 20px rgba(255, 107, 107, 0.6));
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    /* Stats Bar */
    .stats-bar {
      display: flex;
      justify-content: center;
      gap: 3rem;
      padding: 2rem;
      background: rgba(255, 107, 107, 0.05);
      border-radius: 20px;
      margin-bottom: 2rem;
      border: 1px solid rgba(255, 107, 107, 0.2);
      backdrop-filter: blur(10px);
    }

    .stat-item {
      text-align: center;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      color: #ff6b6b;
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
      border: 4px solid rgba(255, 107, 107, 0.1);
      border-top-color: #ff6b6b;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Trophy Rankings */
    .rank-badge {
      position: absolute;
      top: -15px;
      left: -15px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.3rem;
      z-index: 100;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
    }

    .rank-badge.gold {
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #fff;
      animation: pulse-gold 2s infinite;
    }

    .rank-badge.silver {
      background: linear-gradient(135deg, #C0C0C0, #808080);
      color: #fff;
      animation: pulse-silver 2s infinite;
    }

    .rank-badge.bronze {
      background: linear-gradient(135deg, #CD7F32, #8B4513);
      color: #fff;
      animation: pulse-bronze 2s infinite;
    }

    @keyframes pulse-gold {
      0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6); }
      50% { transform: scale(1.1); box-shadow: 0 8px 35px rgba(255, 215, 0, 0.9); }
    }

    @keyframes pulse-silver {
      0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(192, 192, 192, 0.6); }
      50% { transform: scale(1.1); box-shadow: 0 8px 35px rgba(192, 192, 192, 0.9); }
    }

    @keyframes pulse-bronze {
      0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(205, 127, 50, 0.6); }
      50% { transform: scale(1.1); box-shadow: 0 8px 35px rgba(205, 127, 50, 0.9); }
    }

    /* Enhanced Post Cards */
    .card-post {
      position: relative;
      background: linear-gradient(135deg, rgba(26, 0, 51, 0.9) 0%, rgba(15, 52, 96, 0.9) 100%);
      border: 2px solid rgba(255, 107, 107, 0.3);
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
      background: linear-gradient(90deg, transparent, rgba(255, 107, 107, 0.1), transparent);
      transition: left 0.5s;
      border-radius: 20px;
    }

    .card-post:hover::before {
      left: 100%;
    }

    .card-post:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: #ff6b6b;
      box-shadow: 0 20px 60px rgba(255, 107, 107, 0.3),
                  0 0 40px rgba(255, 107, 107, 0.2);
    }

    .trending-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 5rem 2rem;
      background: rgba(255, 107, 107, 0.02);
      border-radius: 20px;
      border: 2px dashed rgba(255, 107, 107, 0.2);
    }

    .empty-state i {
      font-size: 6rem;
      color: rgba(255, 107, 107, 0.3);
      margin-bottom: 2rem;
    }

    /* Post Menu */
    .post-menu {
      position: absolute;
      top: 1rem;
      right: 1rem;
    }

    .bookmark-btn {
      background: rgba(255, 107, 107, 0.1);
      border: 1px solid rgba(255, 107, 107, 0.3);
      color: rgba(255, 255, 255, 0.7);
      padding: 0.5rem;
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.3s;
    }

    .bookmark-btn:hover, .bookmark-btn.bookmarked {
      background: rgba(255, 107, 107, 0.3);
      color: #ff6b6b;
    }

    .bookmark-btn.bookmarked i {
      color: #ff6b6b;
    }

    /* Comments Section */
    .comments-section {
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(255, 107, 107, 0.2);
    }

    .comments-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .comments-header h4 {
      color: #fff;
      margin: 0;
      font-size: 1.1rem;
    }

    .close-comments {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      transition: background 0.3s;
    }

    .close-comments:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .add-comment-form {
      margin-bottom: 1rem;
    }

    .comment-input {
      width: 100%;
      padding: 0.75rem 1rem;
      background: rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 107, 107, 0.3);
      border-radius: 0.75rem;
      color: white;
      font-family: 'Poppins', sans-serif;
      font-size: 0.95rem;
      resize: none;
    }

    .comment-input:focus {
      outline: none;
      border-color: #ff6b6b;
    }

    .btn-submit-comment {
      padding: 0.5rem 1.5rem;
      background: linear-gradient(135deg, #ff6b6b, #ff8e53);
      border: none;
      border-radius: 0.5rem;
      color: white;
      font-weight: 600;
      cursor: pointer;
      margin-top: 0.5rem;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-submit-comment:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
    }

    .btn-submit-comment:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .comments-list {
      max-height: 400px;
      overflow-y: auto;
    }

    .comment-item {
      padding: 1rem;
      background: rgba(15, 30, 90, 0.3);
      border-radius: 0.75rem;
      margin-bottom: 0.75rem;
    }

    .comment-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
    }

    .comment-header img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
    }

    .comment-author {
      color: #ff6b6b;
      font-weight: 600;
    }

    .comment-text {
      color: #fff;
      line-height: 1.5;
      margin: 0;
    }

    .loading-comments {
      text-align: center;
      padding: 2rem;
      color: rgba(255, 255, 255, 0.6);
    }

    .spin {
      animation: spin 1s linear infinite;
    }
  </style>
</head>
<body data-user-id="{{ session('user_id') }}">

  <!-- Animated Background Particles -->
  <div class="particles-bg" id="particlesBg"></div>

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
    <div class="popular-hero">
      <div class="fire-icon">🔥</div>
      <h1>TRENDING NOW</h1>
      <p>The hottest reviews getting all the love from the community</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-value" id="totalPosts">{{ count($posts) }}</span>
        <span class="stat-label">Hot Posts</span>
      </div>
      <div class="stat-item">
        @php
          $totalLikes = 0;
          foreach ($posts as $p) {
            $totalLikes += (int)($p['likes'] ?? 0);
          }
        @endphp
        <span class="stat-value" id="totalLikes">{{ $totalLikes }}</span>
        <span class="stat-label">Total Likes</span>
      </div>
      <div class="stat-item">
        @php
          $totalComments = 0;
          foreach ($posts as $p) {
            $totalComments += (int)($p['comments'] ?? 0);
          }
        @endphp
        <span class="stat-value" id="totalComments">{{ $totalComments }}</span>
        <span class="stat-label">Comments</span>
      </div>
    </div>

    <!-- Posts Container -->
    <div id="postsContainer">
      @if(count($posts) > 0)
        @foreach($posts as $index => $post)
          <div class="card-post" data-post-id="{{ $post['id'] }}">
            @if($index === 0)
              <div class="rank-badge gold">🥇</div>
            @elseif($index === 1)
              <div class="rank-badge silver">🥈</div>
            @elseif($index === 2)
              <div class="rank-badge bronze">🥉</div>
            @endif
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
            <div class="post-menu">
              <button class="icon bookmark-btn" data-post-id="{{ $post['id'] }}" title="Bookmark">
                <i class="bi bi-bookmark"></i>
              </button>
            </div>
            <div class="actions">
              <span class="a like-btn" data-post-id="{{ $post['id'] }}" data-liked="false"><i class="bi bi-star"></i><b>{{ $post['likes'] ?? 0 }}</b></span>
              <span class="a comment-btn" data-post-id="{{ $post['id'] }}" data-comments="{{ $post['comments'] ?? 0 }}"><i class="bi bi-chat-left-text"></i><b>{{ $post['comments'] ?? 0 }}</b></span>
            </div>

            <!-- Comments Section (hidden by default) -->
            <div class="comments-section" style="display: none;">
              <div class="comments-header">
                <h4>Comments</h4>
                <button class="close-comments"><i class="bi bi-x-lg"></i></button>
              </div>
              <div class="add-comment-form">
                <textarea class="comment-input" placeholder="Write a comment..." rows="2"></textarea>
                <button class="btn-submit-comment">Post Comment</button>
              </div>
              <div class="comments-list">
                <div class="loading-comments">
                  <i class="bi bi-arrow-repeat spin"></i> Loading comments...
                </div>
              </div>
            </div>
          </div>
        @endforeach
      @else
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.3rem;">No popular posts yet.</p>
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
        <button class="side-btn active" title="Popular"><i class="bi bi-compass-fill"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('newest') }}'" title="Newest"><i class="bi bi-star-fill"></i></button>
        <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
      </div>
    </div>
  </aside>

  <script>
    const currentUserId = @json(session('user_id'));
    const baseUrl = '{{ url('') }}';
    const csrfToken = '{{ csrf_token() }}';

    // Create animated particles
    function createParticles() {
      const container = document.getElementById('particlesBg');
      const colors = ['#ff6b6b', '#ff8e53', '#ffb347', '#38a0ff'];

      for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.width = Math.random() * 100 + 20 + 'px';
        particle.style.height = particle.style.width;
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.background = colors[Math.floor(Math.random() * colors.length)];
        particle.style.animationDuration = Math.random() * 10 + 15 + 's';
        particle.style.animationDelay = Math.random() * 5 + 's';
        container.appendChild(particle);
      }
    }

    createParticles();

    // Logout functionality
    document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.querySelector('.logout-btn-sidebar');

      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          sessionStorage.removeItem('welcomeShown');
          window.location.href = '{{ route('logout') }}';
        });
      }

      // Like button functionality
      document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
          const postId = this.dataset.postId;
          try {
            const response = await fetch(`${baseUrl}/posts/${postId}/like`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              }
            });
            const data = await response.json();
            if (data.success) {
              const isLiked = this.getAttribute('data-liked') === 'true';
              const icon = this.querySelector('i');
              const count = this.querySelector('b');

              if (isLiked) {
                this.setAttribute('data-liked', 'false');
                icon.className = 'bi bi-star';
                count.textContent = parseInt(count.textContent) - 1;
              } else {
                this.setAttribute('data-liked', 'true');
                icon.className = 'bi bi-star-fill';
                count.textContent = parseInt(count.textContent) + 1;
              }

              // Update total likes counter
              const totalLikesEl = document.getElementById('totalLikes');
              if (totalLikesEl) {
                const currentTotal = parseInt(totalLikesEl.textContent);
                totalLikesEl.textContent = isLiked ? currentTotal - 1 : currentTotal + 1;
              }
            }
          } catch (error) {
            console.error('Error:', error);
          }
        });
      });
    });

    // Helper functions
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function timeAgo(dateString) {
      const now = new Date();
      const past = new Date(dateString);
      const seconds = Math.floor((now - past) / 1000);

      if (seconds < 60) return 'Just now';

      const minutes = Math.floor(seconds / 60);
      if (minutes < 60) return minutes + 'm';

      const hours = Math.floor(minutes / 60);
      if (hours < 24) return hours + 'h';

      const days = Math.floor(hours / 24);
      if (days === 1) return 'Yesterday';
      if (days < 7) return days + 'd';

      const weeks = Math.floor(days / 7);
      if (weeks < 4) return weeks + 'w';

      const options = { month: 'short', day: 'numeric' };
      if (past.getFullYear() !== now.getFullYear()) {
        options.year = 'numeric';
      }
      return past.toLocaleDateString('en-US', options);
    }

    // Comment button click handler
    document.querySelectorAll('.comment-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const postElement = this.closest('.card-post');
        const commentsSection = postElement.querySelector('.comments-section');
        const isVisible = commentsSection.style.display !== 'none';

        if (isVisible) {
          commentsSection.style.display = 'none';
        } else {
          commentsSection.style.display = 'block';
          loadComments(postElement);
        }
      });
    });

    // Close comments handler
    document.querySelectorAll('.close-comments').forEach(btn => {
      btn.addEventListener('click', function() {
        const commentsSection = this.closest('.comments-section');
        commentsSection.style.display = 'none';
      });
    });

    // Submit comment handler
    document.querySelectorAll('.btn-submit-comment').forEach(btn => {
      btn.addEventListener('click', function() {
        const postElement = this.closest('.card-post');
        const postId = postElement.getAttribute('data-post-id');
        const commentInput = postElement.querySelector('.comment-input');
        const commentText = commentInput.value.trim();

        if (commentText) {
          submitComment(postId, commentText, postElement);
        }
      });
    });

    // Enter key to submit comment
    document.querySelectorAll('.comment-input').forEach(input => {
      input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          const postElement = this.closest('.card-post');
          const postId = postElement.getAttribute('data-post-id');
          const commentText = this.value.trim();

          if (commentText) {
            submitComment(postId, commentText, postElement);
          }
        }
      });
    });

    // Bookmark button handler
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
      btn.addEventListener('click', async function() {
        const postId = this.dataset.postId;
        try {
          const response = await fetch(`${baseUrl}/posts/${postId}/bookmark`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            }
          });
          const data = await response.json();
          if (data.success) {
            const icon = this.querySelector('i');
            if (data.bookmarked) {
              icon.classList.remove('bi-bookmark');
              icon.classList.add('bi-bookmark-fill');
              this.classList.add('bookmarked');
            } else {
              icon.classList.remove('bi-bookmark-fill');
              icon.classList.add('bi-bookmark');
              this.classList.remove('bookmarked');
            }
          }
        } catch (error) {
          console.error('Error toggling bookmark:', error);
        }
      });
    });

    // Load comments function
    async function loadComments(postElement) {
      const postId = postElement.getAttribute('data-post-id');
      const commentsList = postElement.querySelector('.comments-list');

      commentsList.innerHTML = '<div class="loading-comments"><i class="bi bi-arrow-repeat spin"></i> Loading comments...</div>';

      try {
        const response = await fetch(`${baseUrl}/api/posts/${postId}/comments`);
        const data = await response.json();

        if (data.success) {
          displayComments(data.comments, commentsList, postElement);
        } else {
          commentsList.innerHTML = '<p style="color: #ff6b6b; text-align: center; padding: 1rem;">Error loading comments</p>';
        }
      } catch (error) {
        console.error('Error loading comments:', error);
        commentsList.innerHTML = '<p style="color: #ff6b6b; text-align: center; padding: 1rem;">Error loading comments</p>';
      }
    }

    // Display comments function
    function displayComments(comments, commentsList, postElement) {
      // Update comment count in button
      const commentBtn = postElement.querySelector('.comment-btn');
      if (commentBtn) {
        const countB = commentBtn.querySelector('b');
        if (countB) countB.textContent = comments.length;
        commentBtn.dataset.comments = comments.length;
      }

      // Update total comments counter
      const totalCommentsEl = document.getElementById('totalComments');
      if (totalCommentsEl) {
        // Recalculate total based on all visible comment counts
        let total = 0;
        document.querySelectorAll('.comment-btn').forEach(btn => {
          total += parseInt(btn.dataset.comments) || 0;
        });
        totalCommentsEl.textContent = total;
      }

      if (comments.length === 0) {
        commentsList.innerHTML = '<p style="color: rgba(255, 255, 255, 0.5); text-align: center; padding: 1rem;">No comments yet. Be the first!</p>';
        return;
      }

      commentsList.innerHTML = '';
      comments.forEach(comment => {
        const profilePic = comment.profile_picture || comment.commenter_profile_picture || '{{ asset("assets/img/cat1.jpg") }}';
        const timestamp = timeAgo(comment.created_at);

        const commentHtml = `
          <div class="comment-item" data-comment-id="${comment.id}">
            <div class="comment-header">
              <img src="${escapeHtml(profilePic)}" alt="Profile">
              <span class="comment-author">@${escapeHtml(comment.username)}</span>
              <span style="color: rgba(255, 255, 255, 0.4); font-size: 0.8rem; margin-left: auto;">${timestamp}</span>
            </div>
            <p class="comment-text">${escapeHtml(comment.comment || comment.text)}</p>
          </div>
        `;
        commentsList.insertAdjacentHTML('beforeend', commentHtml);
      });
    }

    // Submit comment function
    async function submitComment(postId, commentText, postElement) {
      const submitBtn = postElement.querySelector('.btn-submit-comment');
      const commentInput = postElement.querySelector('.comment-input');

      submitBtn.disabled = true;
      submitBtn.textContent = 'Posting...';

      try {
        const response = await fetch(`${baseUrl}/posts/${postId}/comment`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ text: commentText })
        });

        const data = await response.json();

        if (data.success) {
          commentInput.value = '';
          loadComments(postElement);
        } else {
          alert('Failed to post comment: ' + (data.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error posting comment:', error);
        alert('Error posting comment');
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Post Comment';
      }
    }
  </script>
</body>
</html>
