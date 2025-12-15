<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>EXPoints - Home [{{ session('username', 'User') }}]</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
</head>
<body>
  <!-- PlayStation Button Particles Background -->
  <div class="particles-container" id="particlesContainer"></div>

  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="{{ route('dashboard') }}" class="lp-brand" aria-label="+EXPoints home">
        <img src="{{ asset('assets/img/EXPoints Logo.png') }}" alt="+EXPoints" class="lp-brand-img">
      </a>

      <div class="search">
        <input type="text" id="searchInput" placeholder="Search for a Review, a Game, Anything" autocomplete="off" />
        <input type="hidden" id="searchFilterInput" value="title" />
        <button class="icon" aria-label="Search"><i class="bi bi-search"></i></button>
      </div>

      <div class="right">
        <button class="icon" id="filterButton" title="Filter" type="button"><i class="bi bi-funnel"></i></button>
        <button class="icon" id="notificationButton" title="Notifications" type="button" style="position: relative;">
          <i class="bi bi-bell"></i>
          <span id="notificationBadge" class="notification-badge" style="display: none;">0</span>
        </button>
        <a href="{{ route('profile.show') }}" class="avatar-nav">
          <img src="{{ $userProfilePicture ?? asset('assets/img/cat1.jpg') }}" alt="Profile" class="avatar-img" loading="eager">
        </a>
      </div>
    </header>
  </div>

  <!-- Filter Dropdown Modal -->
  <div class="filter-dropdown" id="filterDropdown" style="display: none;">
    <div class="filter-dropdown-content">
      <h6 class="filter-dropdown-title"><i class="bi bi-funnel-fill"></i> Search By</h6>
      <div class="filter-options">
        <button class="filter-option active" data-filter="title">
          <i class="bi bi-file-text"></i> Post Title
        </button>
        <button class="filter-option" data-filter="author">
          <i class="bi bi-person"></i> Author
        </button>
        <button class="filter-option" data-filter="content">
          <i class="bi bi-align-left"></i> Content
        </button>
      </div>
    </div>
  </div>

  <!-- Notifications Dropdown -->
  <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
    <div class="notification-dropdown-header">
      <h6><i class="bi bi-bell-fill"></i> Notifications</h6>
      <button class="btn-mark-all-read" id="markAllReadBtn" style="display: none;">
        <i class="bi bi-check-all"></i> Mark all as read
      </button>
    </div>
    <div class="notification-list" id="notificationList">
      <div class="notification-loading">
        <i class="bi bi-hourglass-split"></i> Loading notifications...
      </div>
    </div>
  </div>

  <main class="container-xl py-4">
    <!-- Search Results Info (dynamic) -->
    <div class="search-results-info" id="searchResultsInfo" style="display: none;">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <i class="bi bi-search"></i>
          Showing results for "<strong id="searchQueryDisplay"></strong>" in
          <span class="badge bg-primary" id="searchFilterDisplay">Title</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary" id="clearSearchBtn">
          <i class="bi bi-x-circle"></i> Clear Search
        </button>
      </div>
      <div class="text-muted mt-2" id="searchResultsCount"></div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Post a Review Section -->
    <section class="card-post-form">
      <div class="row gap-3 align-items-start">
        <div class="col-auto">
          <div class="avatar-us">
            <img src="{{ $userProfilePicture ?? asset('assets/img/cat1.jpg') }}" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
          </div>
        </div>
        <div class="col">
          <!-- Simple textbox (initial state) -->
          <div id="simplePostBox" class="simple-post-box">
            <input type="text" id="simplePostInput" class="simple-post-input" placeholder="What's on your mind, @{{ session('username', 'User') }}?" readonly data-username="{{ session('username', 'User') }}" data-userid="{{ session('user_id') }}">
          </div>

          <!-- Expanded form (hidden initially) -->
          <div id="expandedPostForm" class="expanded-post-form" style="display: none;">
            <h3 class="form-title mb-3">Post a Review</h3>
            <form id="postForm" class="post-form" method="POST" action="{{ route('posts.store') }}">
              @csrf
              <input type="hidden" name="action" value="create">
              <div class="form-group mb-3">
                <label for="gameSelect" class="form-label">Select Game</label>
                <select id="gameSelect" name="game" class="form-select" required>
                  <option value="">Choose a game to review...</option>
                  <option value="Elden Ring">Elden Ring</option>
                  <option value="Cyberpunk 2077">Cyberpunk 2077</option>
                  <option value="Baldur's Gate 3">Baldur's Gate 3</option>
                  <option value="Spider-Man 2">Spider-Man 2</option>
                  <option value="The Legend of Zelda: Tears of the Kingdom">The Legend of Zelda: Tears of the Kingdom</option>
                  <option value="Hogwarts Legacy">Hogwarts Legacy</option>
                  <option value="Diablo IV">Diablo IV</option>
                  <option value="Starfield">Starfield</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="form-group mb-3" id="customGameGroup" style="display: none;">
                <label for="customGame" class="form-label">Specify Game Name</label>
                <input type="text" id="customGame" name="custom_game" class="form-input" placeholder="Enter the game name...">
              </div>
              <div class="form-group mb-3">
                <label for="postTitle" class="form-label">Review Title</label>
                <input type="text" id="postTitle" name="title" class="form-input" placeholder="Enter your review title..." required>
              </div>
              <div class="form-group mb-3">
                <label for="postContent" class="form-label">Your Review</label>
                <textarea id="postContent" name="content" class="form-textarea" placeholder="Share your thoughts about the game..." rows="4" required></textarea>
              </div>
              <div class="form-group mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="usernameField" name="username" class="form-input" value="{{ session('username', 'User') }}" readonly>
              </div>
              <input type="hidden" name="email" value="{{ session('user_email') }}">
              <div class="form-actions">
                <button type="button" id="cancelPost" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-post">Post Review</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Dynamic Posts Container -->
    <div id="postsContainer">
      @forelse($posts as $post)
        @php
          $isOwnPost = ($post['username'] === session('username'));
          $isBookmarked = $post['is_bookmarked'] ?? false;
        @endphp
        <div class="card-post" data-post-id="{{ $post['id'] }}" data-is-bookmarked="{{ $isBookmarked ? 'true' : 'false' }}">
          <div class="post-header">
            <div class="row gap-3 align-items-start">
              <div class="col-auto">
                <div class="avatar-us avatar-loading">
                  <div class="star-loader">&#11088;</div>
                  <img src="{{ $post['profile_picture'] ?? asset('assets/img/cat1.jpg') }}"
                       alt="Profile"
                       loading="lazy"
                       class="profile-lazy-img"
                       onload="this.classList.add('loaded'); this.parentElement.classList.remove('avatar-loading');">
                </div>
              </div>
              <div class="col">
                <div class="game-badge">{{ $post['game'] }}</div>
                <h2 class="title mb-1">{{ $post['title'] }}</h2>
                <div class="handle mb-3">@{{ $post['username'] }}</div>
                <p class="mb-3">{!! nl2br(e($post['content'])) !!}</p>
              </div>
            </div>
            <div class="post-menu">
              @if($isOwnPost)
                <!-- Show edit/delete menu for own posts -->
                <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
                <div class="post-dropdown">
                  <button class="dropdown-item edit-post" data-post-id="{{ $post['id'] }}"><i class="bi bi-pencil"></i> Edit</button>
                  <button class="dropdown-item delete-post" data-post-id="{{ $post['id'] }}"><i class="bi bi-trash"></i> Delete</button>
                </div>
              @else
                <!-- Show bookmark icon for other users' posts -->
                <button class="icon bookmark-btn {{ $isBookmarked ? 'bookmarked' : '' }}" aria-label="Bookmark" data-post-id="{{ $post['id'] }}">
                  <i class="bi bi-bookmark-fill"></i>
                </button>
              @endif
            </div>
          </div>
          <div class="actions">
            <span class="a like-btn" data-liked="{{ $post['user_liked'] ?? false ? 'true' : 'false' }}" data-post-id="{{ $post['id'] }}">
              <i class="bi bi-star{{ ($post['user_liked'] ?? false) ? '-fill' : '' }}"></i>
              <b>{{ $post['likes'] ?? 0 }}</b>
            </span>
            <span class="a comment-btn" data-comments="{{ $post['comments'] ?? 0 }}" data-post-id="{{ $post['id'] }}">
              <i class="bi bi-chat-left-text"></i>
              <b>{{ $post['comments'] ?? 0 }}</b>
            </span>
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
      @empty
        <div class="no-posts-message">
          <i class="bi bi-inbox"></i>
          <p>No posts to display yet. Be the first to share your review!</p>
        </div>
      @endforelse
    </div>
  </main>

  <!-- Slide-in sidebar -->
  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <button class="side-btn" onclick="window.location.href='{{ route('dashboard') }}'" title="Home"><i class="bi bi-house"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('bookmarks') }}'" title="Bookmarks"><i class="bi bi-bookmark"></i></button>
        <button class="side-btn" onclick="window.location.href='#'" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('popular') }}'" title="Popular"><i class="bi bi-compass"></i></button>
        <button class="side-btn" onclick="window.location.href='{{ route('newest') }}'" title="Newest"><i class="bi bi-star-fill"></i></button>
        <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
      </div>
    </div>
  </aside>

  <!-- Profile Hover Modal -->
  <div id="profileHoverModal" class="profile-hover-modal">
    <div class="profile-hover-content">
      <div class="profile-hover-avatar">
        <img id="hoverProfilePic" src="" alt="Profile">
        <div id="hoverProfileLevel" class="hover-level-badge">LVL 1</div>
      </div>
      <div class="profile-hover-info">
        <h4 id="hoverProfileUsername" class="hover-username">Username</h4>
        <div id="hoverProfileExp" class="hover-exp">0 EXP</div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div id="confirmationModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Post Created Successfully!</h4>
      </div>
      <div class="modal-body">
        <p>Your review has been posted and is now visible to other users.</p>
      </div>
      <div class="modal-footer">
        <button id="closeModal" class="btn-modal">Got it!</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Delete Post</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this post? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button id="cancelDelete" class="btn-cancel">Cancel</button>
        <button id="confirmDelete" class="btn-delete">Delete</button>
      </div>
    </div>
  </div>

  <!-- Welcome Modal -->
  <div id="welcomeModal" class="welcome-modal-overlay">
    <div class="welcome-modal-content">
      <div class="welcome-panda-container">
        <img src="{{ asset('assets/img/Login Panda Controller.png') }}" alt="Welcome Panda" class="welcome-panda-img">
      </div>
      <h1 class="welcome-title">Welcome, {{ session('username', 'User') }}!</h1>
      <p class="welcome-message">Let's make this space positive and fun. Please share only appropriate and respectful content. Thanks for keeping it chill!</p>
      <button id="welcomeUnderstood" class="welcome-btn">Understood!</button>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Store current user info for JavaScript
    const currentUserId = {{ session('user_id', 0) }};
    const currentUsername = '{{ session('username', 'User') }}';
    const csrfToken = '{{ csrf_token() }}';

    // Logout and Welcome Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.querySelector('.logout-btn-sidebar');

      // Welcome Modal functionality
      const welcomeModal = document.getElementById('welcomeModal');
      const welcomeUnderstoodBtn = document.getElementById('welcomeUnderstood');
      let hideTimeout;

      function hideWelcomeModal() {
        clearTimeout(hideTimeout);
        welcomeModal.style.opacity = '0';
        setTimeout(() => {
          welcomeModal.style.display = 'none';
        }, 500);
      }

      if (!sessionStorage.getItem('welcomeShown')) {
        welcomeModal.style.display = 'flex';
        sessionStorage.setItem('welcomeShown', 'true');

        hideTimeout = setTimeout(() => {
          hideWelcomeModal();
        }, 3000);

        welcomeModal.addEventListener('mouseenter', function() {
          clearTimeout(hideTimeout);
        });

        welcomeModal.addEventListener('mouseleave', function() {
          hideTimeout = setTimeout(() => {
            hideWelcomeModal();
          }, 3000);
        });

        welcomeUnderstoodBtn.addEventListener('click', function() {
          hideWelcomeModal();
        });
      }

      // Handle logout button click
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          sessionStorage.removeItem('welcomeShown');
          // Create and submit a logout form
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '{{ route('logout') }}';
          form.innerHTML = '<input type="hidden" name="_token" value="' + csrfToken + '">';
          document.body.appendChild(form);
          form.submit();
        });
      }

      // Post form functionality
      const simplePostBox = document.getElementById('simplePostBox');
      const simplePostInput = document.getElementById('simplePostInput');
      const expandedPostForm = document.getElementById('expandedPostForm');
      const postForm = document.getElementById('postForm');
      const cancelPost = document.getElementById('cancelPost');
      const confirmationModal = document.getElementById('confirmationModal');
      const deleteModal = document.getElementById('deleteModal');
      const closeModal = document.getElementById('closeModal');
      const cancelDelete = document.getElementById('cancelDelete');
      const confirmDelete = document.getElementById('confirmDelete');
      const gameSelect = document.getElementById('gameSelect');
      const customGameGroup = document.getElementById('customGameGroup');

      // Expand form when simple input is clicked
      if (simplePostInput) {
        simplePostInput.addEventListener('click', function() {
          simplePostBox.style.display = 'none';
          expandedPostForm.style.display = 'block';
          document.getElementById('postTitle').focus();
        });
      }

      // Show/hide custom game input
      if (gameSelect) {
        gameSelect.addEventListener('change', function() {
          if (this.value === 'Other') {
            customGameGroup.style.display = 'block';
          } else {
            customGameGroup.style.display = 'none';
          }
        });
      }

      // Handle cancel button
      if (cancelPost) {
        cancelPost.addEventListener('click', function() {
          postForm.reset();
          expandedPostForm.style.display = 'none';
          simplePostBox.style.display = 'block';
          customGameGroup.style.display = 'none';
        });
      }

      // Close confirmation modal
      if (closeModal) {
        closeModal.addEventListener('click', function() {
          confirmationModal.style.display = 'none';
        });
      }

      // Close modal when clicking outside
      if (confirmationModal) {
        confirmationModal.addEventListener('click', function(e) {
          if (e.target === confirmationModal) {
            confirmationModal.style.display = 'none';
          }
        });
      }

      // Delete modal functionality
      if (cancelDelete) {
        cancelDelete.addEventListener('click', function() {
          deleteModal.style.display = 'none';
        });
      }

      if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
          if (e.target === deleteModal) {
            deleteModal.style.display = 'none';
          }
        });
      }

      // Like button functionality
      document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
          const postId = this.dataset.postId;
          try {
            const response = await fetch(`/posts/${postId}/like`, {
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

      // Bookmark button functionality
      document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
          const postId = this.dataset.postId;
          const postElement = this.closest('.card-post');

          try {
            const response = await fetch(`/posts/${postId}/bookmark`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              }
            });
            const data = await response.json();
            if (data.success) {
              const isBookmarked = postElement.getAttribute('data-is-bookmarked') === 'true';
              if (isBookmarked) {
                this.classList.remove('bookmarked');
                postElement.setAttribute('data-is-bookmarked', 'false');
              } else {
                this.classList.add('bookmarked');
                postElement.setAttribute('data-is-bookmarked', 'true');
                showToast('Post bookmarked!');
              }
            }
          } catch (error) {
            console.error('Error:', error);
          }
        });
      });

      // Comment button functionality
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

      // Close comments button
      document.querySelectorAll('.close-comments').forEach(btn => {
        btn.addEventListener('click', function() {
          const commentsSection = this.closest('.comments-section');
          commentsSection.style.display = 'none';
        });
      });

      // Submit comment functionality
      document.querySelectorAll('.btn-submit-comment').forEach(btn => {
        btn.addEventListener('click', function() {
          const postElement = this.closest('.card-post');
          const commentInput = postElement.querySelector('.comment-input');
          const commentText = commentInput.value.trim();

          if (commentText) {
            const postId = postElement.getAttribute('data-post-id');
            submitComment(postId, commentText, postElement);
          }
        });
      });

      // Post dropdown menu
      document.querySelectorAll('.more').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.stopPropagation();
          const dropdown = this.nextElementSibling;
          document.querySelectorAll('.post-dropdown.show').forEach(dd => {
            if (dd !== dropdown) dd.classList.remove('show');
          });
          dropdown.classList.toggle('show');
        });
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.post-menu')) {
          document.querySelectorAll('.post-dropdown.show').forEach(dd => {
            dd.classList.remove('show');
          });
        }
      });

      // Delete post functionality
      document.querySelectorAll('.delete-post').forEach(btn => {
        btn.addEventListener('click', function() {
          const postId = this.dataset.postId;
          const postElement = this.closest('.card-post');

          deleteModal.style.display = 'flex';

          confirmDelete.onclick = async function() {
            try {
              const response = await fetch(`/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': csrfToken
                }
              });
              const data = await response.json();
              if (data.success) {
                postElement.remove();
                deleteModal.style.display = 'none';
              }
            } catch (error) {
              console.error('Error:', error);
            }
          };
        });
      });

      // Filter button functionality
      const filterButton = document.getElementById('filterButton');
      const filterDropdown = document.getElementById('filterDropdown');
      const searchInput = document.getElementById('searchInput');
      const searchFilterInput = document.getElementById('searchFilterInput');

      if (filterButton) {
        filterButton.addEventListener('click', function(e) {
          e.stopPropagation();
          filterDropdown.style.display = filterDropdown.style.display === 'none' ? 'block' : 'none';
        });
      }

      document.querySelectorAll('.filter-option').forEach(option => {
        option.addEventListener('click', function() {
          document.querySelectorAll('.filter-option').forEach(o => o.classList.remove('active'));
          this.classList.add('active');
          searchFilterInput.value = this.dataset.filter;
          filterDropdown.style.display = 'none';
        });
      });

      // Notifications
      const notificationButton = document.getElementById('notificationButton');
      const notificationDropdown = document.getElementById('notificationDropdown');
      const notificationBadge = document.getElementById('notificationBadge');
      const markAllReadBtn = document.getElementById('markAllReadBtn');

      if (notificationButton) {
        notificationButton.addEventListener('click', function(e) {
          e.stopPropagation();
          notificationDropdown.style.display = notificationDropdown.style.display === 'none' ? 'block' : 'none';
          if (notificationDropdown.style.display === 'block') {
            loadNotifications();
          }
        });
      }

      document.addEventListener('click', function(e) {
        if (!e.target.closest('#notificationDropdown') && !e.target.closest('#notificationButton')) {
          notificationDropdown.style.display = 'none';
        }
        if (!e.target.closest('#filterDropdown') && !e.target.closest('#filterButton')) {
          filterDropdown.style.display = 'none';
        }
      });
    });

    // Load comments for a post
    async function loadComments(postElement) {
      const postId = postElement.getAttribute('data-post-id');
      const commentsList = postElement.querySelector('.comments-list');

      commentsList.innerHTML = '<div class="loading-comments"><i class="bi bi-arrow-repeat spin"></i> Loading comments...</div>';

      try {
        const response = await fetch(`/api/posts/${postId}/comments`);
        const data = await response.json();

        if (data.success && data.comments && data.comments.length > 0) {
          displayComments(data.comments, commentsList, postElement);
        } else {
          commentsList.innerHTML = '<p style="color: rgba(255,255,255,0.5); text-align: center;">No comments yet. Be the first!</p>';
        }
      } catch (error) {
        console.error('Error loading comments:', error);
        commentsList.innerHTML = '<p style="color: rgba(255,255,255,0.5); text-align: center;">No comments yet. Be the first!</p>';
      }
    }

    // Display comments
    function displayComments(comments, commentsList, postElement) {
      commentsList.innerHTML = '';
      comments.forEach(comment => {
        const commentDiv = document.createElement('div');
        commentDiv.className = 'comment-item';
        commentDiv.innerHTML = `
          <div class="comment-header" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <img src="${comment.profile_picture || '{{ asset('assets/img/cat1.jpg') }}'}" alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
            <span class="comment-author">@${escapeHtml(comment.username)}</span>
            <span style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">${timeAgo(comment.created_at)}</span>
          </div>
          <p class="comment-text">${escapeHtml(comment.comment_text || comment.text)}</p>
        `;
        commentsList.appendChild(commentDiv);
      });
    }

    // Submit comment
    async function submitComment(postId, commentText, postElement) {
      const commentInput = postElement.querySelector('.comment-input');

      try {
        const response = await fetch(`/posts/${postId}/comment`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({comment_text: commentText})
        });
        const data = await response.json();

        if (data.success) {
          commentInput.value = '';
          loadComments(postElement);
          const commentBtn = postElement.querySelector('.comment-btn b');
          commentBtn.textContent = parseInt(commentBtn.textContent) + 1;
        }
      } catch (error) {
        console.error('Error posting comment:', error);
      }
    }

    // Load notifications
    async function loadNotifications() {
      const notificationList = document.getElementById('notificationList');

      try {
        const response = await fetch('/api/notifications');
        const data = await response.json();

        if (data.success && data.notifications && data.notifications.length > 0) {
          notificationList.innerHTML = data.notifications.map(n => `
            <div class="notification-item ${n.is_read ? '' : 'unread'}">
              <p>${escapeHtml(n.message)}</p>
              <span class="notification-time">${timeAgo(n.created_at)}</span>
            </div>
          `).join('');
        } else {
          notificationList.innerHTML = '<p style="text-align: center; color: rgba(255,255,255,0.5); padding: 1rem;">No notifications</p>';
        }
      } catch (error) {
        notificationList.innerHTML = '<p style="text-align: center; color: rgba(255,255,255,0.5); padding: 1rem;">No notifications</p>';
      }
    }

    // Helper function to show toast notification
    function showToast(message) {
      const toast = document.createElement('div');
      toast.textContent = message;
      toast.style.cssText = 'position: fixed; bottom: 2rem; right: 2rem; background: #38a0ff; color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 10000;';
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 2000);
    }

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

      return past.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    // Create PlayStation button particles
    function createParticles() {
      const container = document.getElementById('particlesContainer');
      const symbols = ['&#9650;', '&#9679;', '&#10005;', '&#9632;']; // Triangle, Circle, X, Square

      for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.innerHTML = symbols[Math.floor(Math.random() * symbols.length)];
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 20 + 's';
        particle.style.animationDuration = (15 + Math.random() * 10) + 's';
        container.appendChild(particle);
      }
    }
    createParticles();
  </script>

  <style>
    /* Additional inline styles for dashboard */
    .card-post { transition: opacity 0.3s ease, transform 0.3s ease; }
    .avatar-us img { position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3; }
    .card-post .avatar-us::after { display: none; }
    .profile-hover-modal { position: fixed; display: none; z-index: 10000; pointer-events: none; }
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .post-dropdown { display: none; position: absolute; top: 100%; right: 0; background: rgba(25, 35, 75, 0.95); border: 1px solid rgba(194, 213, 255, 0.2); border-radius: 0.5rem; min-width: 150px; z-index: 1000; }
    .post-dropdown.show { display: block; }
    .post-dropdown .dropdown-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: transparent; border: none; color: white; width: 100%; cursor: pointer; }
    .post-dropdown .dropdown-item:hover { background: rgba(56, 160, 255, 0.2); }
    .bookmark-btn.bookmarked i { color: #38a0ff; }
    .comments-section { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(194, 213, 255, 0.2); }
    .comment-item { padding: 1rem; background: rgba(15, 30, 90, 0.3); border-radius: 0.75rem; margin-bottom: 0.75rem; }
    .comment-author { color: #38a0ff; font-weight: 600; }
    .comment-text { color: #fff; line-height: 1.5; margin: 0; }
    .btn-submit-comment { padding: 0.5rem 1.5rem; background: #38a0ff; border: none; border-radius: 0.5rem; color: white; font-weight: 600; cursor: pointer; margin-top: 0.5rem; }
    .btn-submit-comment:hover { background: #2c8de0; }
    .loading-comments { text-align: center; padding: 2rem; color: rgba(255, 255, 255, 0.6); }
    .comments-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .comments-header h4 { color: #fff; margin: 0; }
    .close-comments { background: rgba(255, 255, 255, 0.1); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; }
    .particle { position: absolute; font-size: 1.5rem; color: rgba(56, 160, 255, 0.3); animation: float 20s linear infinite; pointer-events: none; }
    @keyframes float { 0% { transform: translateY(100vh) rotate(0deg); opacity: 0; } 10% { opacity: 0.5; } 90% { opacity: 0.5; } 100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; } }
  </style>
</body>
</html>
