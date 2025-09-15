<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Home</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="index.html" class="lp-brand" aria-label="+EXPoints home">
        <img src="assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>

      <form class="search" role="search">
        <input type="text" placeholder="Search for a Review, a Game, Anything" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>

      <div class="right">
        <button class="icon" title="Filter"><i class="bi bi-funnel"></i></button>
        <div class="settings-dropdown">
          <button class="icon settings-btn" title="Settings"><i class="bi bi-gear"></i></button>
          <div class="dropdown-menu">
            <button class="dropdown-item logout-btn">
              <i class="bi bi-box-arrow-right"></i>
              Logout
            </button>
          </div>
        </div>
        <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
        <a href="profile.php" class="avatar-nav">
  <img src="assets/img/lara.jpg" alt="Profile" class="avatar-img">
</a>
</div>
    </header>
  </div>

  <main class="container-xl py-4">
    <!-- Post a Review Form -->
    <section class="card-post-form">
      <div class="row gap-3 align-items-start">
        <div class="col-auto"><div class="avatar-us"></div></div>
        <div class="col">
          <h3 class="form-title mb-3">Post a Review</h3>
          <form id="postForm" class="post-form">
            <div class="form-group mb-3">
              <label for="gameSelect" class="form-label">Select Game</label>
              <select id="gameSelect" class="form-select" required>
                <option value="">Choose a game to review...</option>
                <option value="elden-ring">Elden Ring</option>
                <option value="cyberpunk-2077">Cyberpunk 2077</option>
                <option value="baldurs-gate-3">Baldur's Gate 3</option>
                <option value="spider-man-2">Spider-Man 2</option>
                <option value="zelda-totk">The Legend of Zelda: Tears of the Kingdom</option>
                <option value="hogwarts-legacy">Hogwarts Legacy</option>
                <option value="diablo-4">Diablo IV</option>
                <option value="starfield">Starfield</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="form-group mb-3">
              <label for="postTitle" class="form-label">Review Title</label>
              <input type="text" id="postTitle" class="form-input" placeholder="Enter your review title..." required>
            </div>
            <div class="form-group mb-3">
              <label for="postContent" class="form-label">Your Review</label>
              <textarea id="postContent" class="form-textarea" placeholder="Share your thoughts about the game..." rows="4" required></textarea>
            </div>
            <div class="form-actions">
              <button type="button" id="cancelPost" class="btn-cancel">Cancel</button>
              <button type="submit" class="btn-post">Post Review</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Existing Posts (Other Users) -->
    <article class="card-post">
      <div class="post-header">
        <div class="row gap-3 align-items-start">
          <div class="col-auto"><div class="avatar-lg"></div></div>
          <div class="col">
            <h2 class="title mb-1">Elden Ring Shadow of The Erdtree is BAD</h2>
            <div class="handle mb-3">@BethesdaFan321</div>
            <p class="mb-3">I give this DLC a 7/10. Quantity doesn't mean quality and I think many people highly rated the DLC just because of the amount of additional ER content.</p>
            <p class="mb-0">Even if I'm more positive than you, I quite felt the same way. My biggest disappointment regarding the build up of some bosses was the pink boss (I forgot her name). I loved that boss and overall I think most major bosses are spectacular, but she appeared and disappeared randomly within a region mostly unrelated to her. I don't think she even talked during the fight.</p>
          </div>
        </div>
        <div class="post-menu">
          <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
          <div class="post-dropdown">
            <button class="dropdown-item edit-post"><i class="bi bi-pencil"></i> Edit</button>
            <button class="dropdown-item delete-post"><i class="bi bi-trash"></i> Delete</button>
          </div>
        </div>
      </div>
      <div class="actions">
        <span class="a like-btn" data-liked="false"><i class="bi bi-star"></i><b>50</b></span>
        <span class="a comment-btn" data-comments="4"><i class="bi bi-chat-left-text"></i><b>4</b></span>
      </div>
      <div class="comments-section" style="display: none;">
        <div class="comments-list">
          <div class="comment-item">
            <div class="row g-3 align-items-center">
              <div class="col-auto"><div class="avatar-sm"></div></div>
              <div class="col">
                <div class="comment-author">Kenji Parilla</div>
                <div class="comment-text">Sounds like a skill issue ngl</div>
              </div>
            </div>
          </div>
        </div>
        <div class="comment-input-section">
          <div class="row g-3 align-items-center">
            <div class="col-auto"><div class="avatar-sm"></div></div>
            <div class="col">
              <input class="comment-input" placeholder="Write a Comment on this post!" />
            </div>
            <div class="col-auto">
              <button class="btn-comment">Post</button>
            </div>
          </div>
        </div>
      </div>
    </article>

    <!-- Reply -->
    <article class="card-reply">
      <div class="row g-3 align-items-center">
        <div class="col-auto"><div class="avatar-sm"></div></div>
        <div class="col">
          <div class="author fw-semibold">Kenji Parilla</div>
          <div class="reply">Sounds like a skill issue ngl</div>
        </div>
        <div class="col-auto">
          <div class="actions">
            <span class="a"><i class="bi bi-star"></i><b>4</b></span>
            <span class="a"><i class="bi bi-chat-left-text"></i></span>
            <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
          </div>
        </div>
      </div>
    </article>

    <!-- Comment -->
    <section class="card-input">
      <div class="row g-3 align-items-center">
        <div class="col-auto"><div class="avatar-sm"></div></div>
        <div class="col">
          <input class="comment" placeholder="Write a Comment on this post!" />
        </div>
      </div>
    </section>
  </main>

  <!-- Slide-in sidebar (inside the body) -->
  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <button class="side-btn" title="Home"><i class="bi bi-house"></i></button>
        <button class="side-btn" title="Bookmarks"><i class="bi bi-bookmark"></i></button>
        <button class="side-btn" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn" title="Popular"><i class="bi bi-compass"></i></button>
        <button class="side-btn side-bottom" title="Newest"><i class="bi bi-star-fill"></i></button>
      </div>
    </div>
  </aside>

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

  <script>
    // Settings dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
      const settingsBtn = document.querySelector('.settings-btn');
      const dropdownMenu = document.querySelector('.dropdown-menu');
      const logoutBtn = document.querySelector('.logout-btn');
      
      // Toggle dropdown when settings button is clicked
      settingsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.settings-dropdown')) {
          dropdownMenu.classList.remove('show');
        }
      });
      
      // Handle logout button click
      logoutBtn.addEventListener('click', function() {
        // Optional: Add logout logic here (clear session storage, etc.)
        
        // Redirect to landing page
        window.location.href = 'index.php';
      });

      // Post form functionality
      const postForm = document.getElementById('postForm');
      const cancelPost = document.getElementById('cancelPost');
      const confirmationModal = document.getElementById('confirmationModal');
      const deleteModal = document.getElementById('deleteModal');
      const closeModal = document.getElementById('closeModal');
      const cancelDelete = document.getElementById('cancelDelete');
      const confirmDelete = document.getElementById('confirmDelete');

      // Handle form submission
      postForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const gameSelect = document.getElementById('gameSelect').value;
        const postTitle = document.getElementById('postTitle').value;
        const postContent = document.getElementById('postContent').value;
        
        if (!gameSelect || !postTitle || !postContent) {
          alert('Please fill in all fields');
          return;
        }
        
        // Create new post element
        createNewPost(gameSelect, postTitle, postContent);
        
        // Reset form
        postForm.reset();
        
        // Show confirmation modal
        confirmationModal.style.display = 'flex';
      });

      // Handle cancel button
      cancelPost.addEventListener('click', function() {
        postForm.reset();
      });

      // Close confirmation modal
      closeModal.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
      });

      // Close modal when clicking outside
      confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
          confirmationModal.style.display = 'none';
        }
      });

      // Delete modal functionality
      cancelDelete.addEventListener('click', function() {
        deleteModal.style.display = 'none';
      });

      deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
          deleteModal.style.display = 'none';
        }
      });

      // Function to create new post
      function createNewPost(game, title, content) {
        const main = document.querySelector('main.container-xl');
        const existingPosts = document.querySelector('.card-post');
        
        const newPost = document.createElement('article');
        newPost.className = 'card-post user-post';
        newPost.innerHTML = `
          <div class="post-header">
            <div class="row gap-3 align-items-start">
              <div class="col-auto"><div class="avatar-us"></div></div>
              <div class="col">
                <div class="game-badge">${getGameDisplayName(game)}</div>
                <h2 class="title mb-1">${title}</h2>
                <div class="handle mb-3">@YourUsername</div>
                <p class="mb-3">${content}</p>
              </div>
            </div>
            <div class="post-menu">
              <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
              <div class="post-dropdown">
                <button class="dropdown-item edit-post"><i class="bi bi-pencil"></i> Edit</button>
                <button class="dropdown-item delete-post"><i class="bi bi-trash"></i> Delete</button>
              </div>
            </div>
          </div>
          <div class="actions">
            <span class="a like-btn" data-liked="false"><i class="bi bi-star"></i><b>0</b></span>
            <span class="a comment-btn" data-comments="0"><i class="bi bi-chat-left-text"></i><b>0</b></span>
          </div>
          <div class="comments-section" style="display: none;">
            <div class="comments-list"></div>
            <div class="comment-input-section">
              <div class="row g-3 align-items-center">
                <div class="col-auto"><div class="avatar-sm"></div></div>
                <div class="col">
                  <input class="comment-input" placeholder="Write a Comment on this post!" />
                </div>
                <div class="col-auto">
                  <button class="btn-comment">Post</button>
                </div>
              </div>
            </div>
          </div>
        `;
        
        // Insert new post before existing posts
        main.insertBefore(newPost, existingPosts);
        
        // Add event listeners to new post
        addPostEventListeners(newPost);
      }

      // Function to get display name for game
      function getGameDisplayName(gameValue) {
        const gameNames = {
          'elden-ring': 'Elden Ring',
          'cyberpunk-2077': 'Cyberpunk 2077',
          'baldurs-gate-3': 'Baldur\'s Gate 3',
          'spider-man-2': 'Spider-Man 2',
          'zelda-totk': 'The Legend of Zelda: Tears of the Kingdom',
          'hogwarts-legacy': 'Hogwarts Legacy',
          'diablo-4': 'Diablo IV',
          'starfield': 'Starfield',
          'other': 'Other'
        };
        return gameNames[gameValue] || gameValue;
      }

      // Function to add event listeners to posts
      function addPostEventListeners(postElement) {
        const moreBtn = postElement.querySelector('.more');
        const dropdown = postElement.querySelector('.post-dropdown');
        const editBtn = postElement.querySelector('.edit-post');
        const deleteBtn = postElement.querySelector('.delete-post');
        const likeBtn = postElement.querySelector('.like-btn');
        const commentBtn = postElement.querySelector('.comment-btn');
        const commentsSection = postElement.querySelector('.comments-section');
        const commentInput = postElement.querySelector('.comment-input');
        const postCommentBtn = postElement.querySelector('.btn-comment');

        // Toggle dropdown
        moreBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          // Close all other dropdowns first
          document.querySelectorAll('.post-dropdown.show').forEach(dd => {
            if (dd !== dropdown) dd.classList.remove('show');
          });
          dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.post-menu')) {
            dropdown.classList.remove('show');
          }
        });

        // Like functionality
        if (likeBtn) {
          likeBtn.addEventListener('click', function() {
            const isLiked = likeBtn.getAttribute('data-liked') === 'true';
            const countElement = likeBtn.querySelector('b');
            const iconElement = likeBtn.querySelector('i');
            let currentCount = parseInt(countElement.textContent);
            
            if (isLiked) {
              // Unlike
              likeBtn.setAttribute('data-liked', 'false');
              countElement.textContent = currentCount - 1;
              iconElement.className = 'bi bi-star';
            } else {
              // Like
              likeBtn.setAttribute('data-liked', 'true');
              countElement.textContent = currentCount + 1;
              iconElement.className = 'bi bi-star-fill';
            }
          });
        }

        // Comment toggle functionality
        if (commentBtn) {
          commentBtn.addEventListener('click', function() {
            const isVisible = commentsSection.style.display !== 'none';
            if (isVisible) {
              commentsSection.style.display = 'none';
            } else {
              commentsSection.style.display = 'block';
            }
          });
        }

        // Post comment functionality
        if (postCommentBtn && commentInput) {
          postCommentBtn.addEventListener('click', function() {
            const commentText = commentInput.value.trim();
            if (commentText) {
              addComment(postElement, commentText);
              commentInput.value = '';
              
              // Update comment count
              const countElement = commentBtn.querySelector('b');
              const currentCount = parseInt(countElement.textContent);
              countElement.textContent = currentCount + 1;
              commentBtn.setAttribute('data-comments', currentCount + 1);
            }
          });

          // Allow posting comment with Enter key
          commentInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
              postCommentBtn.click();
            }
          });
        }

        // Edit post functionality
        if (editBtn) {
          editBtn.addEventListener('click', function() {
            dropdown.classList.remove('show');
            editPost(postElement);
          });
        }

        // Delete post functionality
        if (deleteBtn) {
          deleteBtn.addEventListener('click', function() {
            dropdown.classList.remove('show');
            deleteModal.style.display = 'flex';
            
            confirmDelete.onclick = function() {
              postElement.remove();
              deleteModal.style.display = 'none';
            };
          });
        }
      }

      // Function to edit post
      function editPost(postElement) {
        const titleElement = postElement.querySelector('.title');
        const contentElement = postElement.querySelector('p');
        
        const currentTitle = titleElement.textContent;
        const currentContent = contentElement.textContent;
        
        const newTitle = prompt('Edit title:', currentTitle);
        if (newTitle !== null && newTitle.trim() !== '') {
          titleElement.textContent = newTitle;
        }
        
        const newContent = prompt('Edit content:', currentContent);
        if (newContent !== null && newContent.trim() !== '') {
          contentElement.textContent = newContent;
        }
      }

      // Function to add comment
      function addComment(postElement, commentText) {
        const commentsList = postElement.querySelector('.comments-list');
        const commentElement = document.createElement('div');
        commentElement.className = 'comment-item';
        commentElement.innerHTML = `
          <div class="row g-3 align-items-center">
            <div class="col-auto"><div class="avatar-sm"></div></div>
            <div class="col">
              <div class="comment-author">@YourUsername</div>
              <div class="comment-text">${commentText}</div>
            </div>
          </div>
        `;
        commentsList.appendChild(commentElement);
      }

      // Add event listeners to existing posts
      document.querySelectorAll('.card-post').forEach(addPostEventListeners);
    });
  </script>

</body>
</html>
