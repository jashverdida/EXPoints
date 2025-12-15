<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints - Bookmarks</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    /* Floating Bookmark Icons Background */
    .floating-icons-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
      overflow: hidden;
    }

    .floating-icon {
      position: absolute;
      font-size: 3rem;
      opacity: 0.05;
      animation: float-drift 20s infinite ease-in-out;
    }

    @keyframes float-drift {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(100px, -100px) rotate(90deg); }
      50% { transform: translate(-50px, -200px) rotate(180deg); }
      75% { transform: translate(-150px, -100px) rotate(270deg); }
    }

    /* Hero Section */
    .bookmarks-hero {
      position: relative;
      z-index: 1;
      padding: 3rem 0 2rem;
      text-align: center;
      background: linear-gradient(180deg, rgba(251, 197, 49, 0.1) 0%, transparent 100%);
      border-radius: 20px;
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .bookmarks-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 0%, rgba(251, 197, 49, 0.15), transparent 70%);
      pointer-events: none;
    }

    .bookmarks-hero h1 {
      font-size: 4rem;
      font-weight: 800;
      background: linear-gradient(135deg, #fbc531, #f39c12, #e67e22);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      animation: treasure-glow 2s ease-in-out infinite alternate;
    }

    @keyframes treasure-glow {
      from { filter: drop-shadow(0 0 10px rgba(251, 197, 49, 0.5)); }
      to { filter: drop-shadow(0 0 25px rgba(251, 197, 49, 0.8)); }
    }

    .bookmarks-hero p {
      font-size: 1.3rem;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 300;
    }

    .treasure-icon {
      display: inline-block;
      animation: treasure-bounce 2s ease-in-out infinite;
      font-size: 5rem;
      margin-bottom: 1rem;
      filter: drop-shadow(0 0 20px rgba(251, 197, 49, 0.6));
    }

    @keyframes treasure-bounce {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-20px) scale(1.1); }
    }

    /* Stats Bar */
    .stats-bar {
      display: flex;
      justify-content: center;
      gap: 3rem;
      padding: 2rem;
      background: rgba(251, 197, 49, 0.05);
      border-radius: 20px;
      margin-bottom: 2rem;
      border: 1px solid rgba(251, 197, 49, 0.2);
      backdrop-filter: blur(10px);
    }

    .stat-item {
      text-align: center;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      color: #fbc531;
      display: block;
    }

    .stat-label {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Loading Animation */
    .loading-spinner {
      text-align: center;
      padding: 4rem 0;
    }

    .spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(251, 197, 49, 0.1);
      border-top-color: #fbc531;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Bookmarked Badge */
    .bookmarked-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(135deg, #fbc531, #f39c12);
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 4px 15px rgba(251, 197, 49, 0.4);
      animation: shine 2s infinite;
    }

    @keyframes shine {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }

    /* Enhanced Post Cards */
    .card-post {
      position: relative;
      background: linear-gradient(135deg, rgba(26, 26, 46, 0.9) 0%, rgba(22, 33, 62, 0.9) 100%);
      border: 2px solid rgba(251, 197, 49, 0.3);
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
      background: linear-gradient(90deg, transparent, rgba(251, 197, 49, 0.1), transparent);
      transition: left 0.5s;
      border-radius: 20px;
    }

    .card-post:hover::before {
      left: 100%;
    }

    .card-post:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: #fbc531;
      box-shadow: 0 20px 60px rgba(251, 197, 49, 0.3),
                  0 0 40px rgba(251, 197, 49, 0.2);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 5rem 2rem;
      background: rgba(251, 197, 49, 0.02);
      border-radius: 20px;
      border: 2px dashed rgba(251, 197, 49, 0.2);
    }

    .empty-state i {
      font-size: 6rem;
      color: rgba(251, 197, 49, 0.3);
      margin-bottom: 2rem;
      animation: sway 3s ease-in-out infinite;
    }

    @keyframes sway {
      0%, 100% { transform: rotate(-5deg); }
      50% { transform: rotate(5deg); }
    }

    .cta-button {
      margin-top: 2rem;
      padding: 1rem 2.5rem;
      background: linear-gradient(135deg, #fbc531, #f39c12);
      border: none;
      border-radius: 30px;
      color: white;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      box-shadow: 0 10px 30px rgba(251, 197, 49, 0.3);
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .cta-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(251, 197, 49, 0.5);
      color: white;
    }

    /* Post Menu */
    .post-menu {
      position: absolute;
      top: 1rem;
      right: 1rem;
    }

    .bookmark-btn {
      background: rgba(251, 197, 49, 0.1);
      border: 1px solid rgba(251, 197, 49, 0.3);
      color: rgba(255, 255, 255, 0.7);
      padding: 0.5rem;
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.3s;
    }

    .bookmark-btn:hover, .bookmark-btn.bookmarked {
      background: rgba(251, 197, 49, 0.3);
      color: #fbc531;
    }

    .bookmark-btn.bookmarked i {
      color: #fbc531;
    }

    /* Comments Section */
    .comments-section {
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(251, 197, 49, 0.2);
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
      border: 1px solid rgba(251, 197, 49, 0.3);
      border-radius: 0.75rem;
      color: white;
      font-family: 'Poppins', sans-serif;
      font-size: 0.95rem;
      resize: none;
    }

    .comment-input:focus {
      outline: none;
      border-color: #fbc531;
    }

    .btn-submit-comment {
      padding: 0.5rem 1.5rem;
      background: linear-gradient(135deg, #fbc531, #f39c12);
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
      box-shadow: 0 4px 15px rgba(251, 197, 49, 0.4);
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
      color: #fbc531;
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
  <!-- Floating Icons Background -->
  <div class="floating-icons-bg" id="floatingIcons"></div>

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
    <div class="bookmarks-hero">
      <div class="treasure-icon">💎</div>
      <h1>YOUR COLLECTION</h1>
      <p>Treasured reviews saved for later</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-value" id="totalBookmarks">{{ count($posts) }}</span>
        <span class="stat-label">Saved Posts</span>
      </div>
      <div class="stat-item">
        @php
          $uniqueGames = [];
          foreach ($posts as $p) {
            if (!empty($p['game']) && !in_array($p['game'], $uniqueGames)) {
              $uniqueGames[] = $p['game'];
            }
          }
        @endphp
        <span class="stat-value" id="totalGames">{{ count($uniqueGames) }}</span>
        <span class="stat-label">Games Featured</span>
      </div>
    </div>

    <!-- Bookmarks Container -->
    <div id="bookmarksContainer">
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
            <div class="post-menu">
              <button class="icon bookmark-btn bookmarked" data-post-id="{{ $post['id'] }}" title="Remove from Collection">
                <i class="bi bi-bookmark-fill"></i>
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
          <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.3rem;">No bookmarked posts yet. Start bookmarking posts you like!</p>
          <a href="{{ route('dashboard') }}" class="cta-button">Explore Posts</a>
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
        <button class="side-btn active" title="Bookmarks"><i class="bi bi-bookmark-fill"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('games') }}'" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('popular') }}'" title="Popular"><i class="bi bi-compass"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('newest') }}'" title="Newest"><i class="bi bi-star-fill"></i></button>
        <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
      </div>
    </div>
  </aside>

  <script>
    const currentUserId = @json(session('user_id'));
    const baseUrl = '{{ url('') }}';
    const csrfToken = '{{ csrf_token() }}';

    // Create floating bookmark icons
    function createFloatingIcons() {
      const container = document.getElementById('floatingIcons');
      const icons = ['📖', '⭐', '💎', '🔖', '✨', '🎮', '🏆', '💫'];

      for (let i = 0; i < 20; i++) {
        const icon = document.createElement('div');
        icon.className = 'floating-icon';
        icon.textContent = icons[Math.floor(Math.random() * icons.length)];
        icon.style.left = Math.random() * 100 + '%';
        icon.style.top = Math.random() * 100 + '%';
        icon.style.animationDuration = Math.random() * 10 + 15 + 's';
        icon.style.animationDelay = Math.random() * 5 + 's';
        container.appendChild(icon);
      }
    }

    createFloatingIcons();

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

    // Bookmark button handler (with remove animation for bookmarks page)
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
      btn.addEventListener('click', async function() {
        const postId = this.dataset.postId;
        const postElement = this.closest('.card-post');

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
            // If unbookmarked, fade out and remove the post
            if (!data.bookmarked) {
              postElement.style.transition = 'all 0.3s ease';
              postElement.style.opacity = '0';
              postElement.style.transform = 'scale(0.95)';

              setTimeout(() => {
                postElement.remove();

                // Update stats
                const remainingPosts = document.querySelectorAll('.card-post');
                document.getElementById('totalBookmarks').textContent = remainingPosts.length;

                // Check if collection is empty
                if (remainingPosts.length === 0) {
                  document.getElementById('bookmarksContainer').innerHTML = `
                    <div class="empty-state">
                      <i class="bi bi-inbox"></i>
                      <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.3rem;">No bookmarked posts yet. Start bookmarking posts you like!</p>
                      <a href="${baseUrl}/dashboard" class="cta-button">Explore Posts</a>
                    </div>
                  `;
                }
              }, 300);
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
