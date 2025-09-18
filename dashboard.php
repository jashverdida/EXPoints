<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_post') {
        // Redirect to posts.php for processing
        header('Location: posts.php');
        exit;
    }
}

// Get posts from file storage
$postsFile = 'data/posts.json';
$posts = [];
if (file_exists($postsFile)) {
    $postsData = file_get_contents($postsFile);
    $posts = json_decode($postsData, true) ?: [];
}

// Handle success/error messages
$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'post_created':
            $successMessage = 'Post created successfully!';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_fields':
            $errorMessage = 'Please fill in all required fields.';
            break;
    }
}
?>
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
    <!-- Success/Error Messages -->
    <?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($successMessage); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($errorMessage); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Post a Review Section -->
    <section class="card-post-form">
      <div class="row gap-3 align-items-start">
        <div class="col-auto"><div class="avatar-us"></div></div>
        <div class="col">
          <!-- Simple textbox (initial state) -->
          <div id="simplePostBox" class="simple-post-box">
            <input type="text" id="simplePostInput" class="simple-post-input" placeholder="What's on your mind, @YourUsername?" readonly>
          </div>
          
          <!-- Expanded form (hidden initially) -->
          <div id="expandedPostForm" class="expanded-post-form" style="display: none;">
            <h3 class="form-title mb-3">Post a Review</h3>
            <form id="postForm" class="post-form" method="POST" action="posts.php">
              <input type="hidden" name="action" value="create">
              <div class="form-group mb-3">
                <label for="gameSelect" class="form-label">Select Game</label>
                <select id="gameSelect" name="game" class="form-select" required>
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
                <input type="text" id="postTitle" name="title" class="form-input" placeholder="Enter your review title..." required>
              </div>
              <div class="form-group mb-3">
                <label for="postContent" class="form-label">Your Review</label>
                <textarea id="postContent" name="content" class="form-textarea" placeholder="Share your thoughts about the game..." rows="4" required></textarea>
              </div>
              <div class="form-group mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" value="YourUsername" readonly>
              </div>
              <div class="form-actions">
                <button type="button" id="cancelPost" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-post">Post Review</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Dynamic Posts from Database -->
    <?php if (empty($posts)): ?>
    <!-- Default post when no posts exist -->
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
    <?php else: ?>
    <!-- Display posts from database -->
    <?php foreach (array_reverse($posts) as $post): ?>
    <article class="card-post" data-post-id="<?php echo $post['id']; ?>">
      <div class="post-header">
        <div class="row gap-3 align-items-start">
          <div class="col-auto"><div class="avatar-lg"></div></div>
          <div class="col">
            <div class="game-badge"><?php echo htmlspecialchars($post['game']); ?></div>
            <h2 class="title mb-1"><?php echo htmlspecialchars($post['title']); ?></h2>
            <div class="handle mb-3">@<?php echo htmlspecialchars($post['username']); ?></div>
            <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            <small class="text-white">Posted on <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></small>
          </div>
        </div>
        <div class="post-menu">
          <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
          <div class="post-dropdown">
            <button class="dropdown-item edit-post" data-post-id="<?php echo $post['id']; ?>"><i class="bi bi-pencil"></i> Edit</button>
            <button class="dropdown-item delete-post" data-post-id="<?php echo $post['id']; ?>"><i class="bi bi-trash"></i> Delete</button>
          </div>
        </div>
      </div>
      <div class="actions">
        <span class="a like-btn" data-liked="false"><i class="bi bi-star"></i><b><?php echo $post['likes']; ?></b></span>
        <span class="a comment-btn" data-comments="<?php echo $post['comments']; ?>"><i class="bi bi-chat-left-text"></i><b><?php echo $post['comments']; ?></b></span>
      </div>
      <div class="comments-section" style="display: none;">
        <div class="comments-list">
          <!-- Comments will be loaded here -->
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
    <?php endforeach; ?>
    <?php endif; ?>
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

      // Expand form when simple input is clicked
      simplePostInput.addEventListener('click', function() {
        simplePostBox.style.display = 'none';
        expandedPostForm.style.display = 'block';
        // Focus on the title input
        document.getElementById('postTitle').focus();
      });

      // Handle form submission - let it submit naturally to posts.php
      postForm.addEventListener('submit', function(e) {
        const gameSelect = document.getElementById('gameSelect').value;
        const postTitle = document.getElementById('postTitle').value;
        const postContent = document.getElementById('postContent').value;
        const username = document.getElementById('username').value;
        
        if (!gameSelect || !postTitle || !postContent || !username) {
          e.preventDefault();
          alert('Please fill in all fields');
          return;
        }
        
        // Form will submit to posts.php which will redirect back to dashboard
      });

      // Handle cancel button
      cancelPost.addEventListener('click', function() {
        postForm.reset();
        // Collapse back to simple textbox
        expandedPostForm.style.display = 'none';
        simplePostBox.style.display = 'block';
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
            const postId = editBtn.getAttribute('data-post-id');
            editPost(postElement, postId);
          });
        }

        // Delete post functionality
        if (deleteBtn) {
          deleteBtn.addEventListener('click', function() {
            dropdown.classList.remove('show');
            const postId = deleteBtn.getAttribute('data-post-id');
            deleteModal.style.display = 'flex';
            
            confirmDelete.onclick = function() {
              deletePost(postId);
              postElement.remove();
              deleteModal.style.display = 'none';
            };
          });
        }
      }

      // Function to edit post
      function editPost(postElement, postId) {
        const titleElement = postElement.querySelector('.title');
        const contentElement = postElement.querySelector('p');
        
        const currentTitle = titleElement.textContent;
        const currentContent = contentElement.textContent;
        
        // Create inline editing form
        const editForm = document.createElement('div');
        editForm.className = 'edit-form';
        editForm.innerHTML = `
          <div class="edit-form-container">
            <h4 class="edit-form-title">Edit Post</h4>
            <div class="form-group">
              <label class="form-label">Title</label>
              <input type="text" class="form-input edit-title-input" value="${currentTitle}" />
            </div>
            <div class="form-group">
              <label class="form-label">Content</label>
              <textarea class="form-textarea edit-content-input" rows="4">${currentContent}</textarea>
            </div>
            <div class="edit-form-actions">
              <button class="btn-cancel-edit">Cancel</button>
              <button class="btn-save-edit">Save Changes</button>
            </div>
          </div>
        `;
        
        // Replace content with edit form
        const postContent = postElement.querySelector('.col');
        const originalContent = postContent.innerHTML;
        postContent.innerHTML = '';
        postContent.appendChild(editForm);
        
        // Add event listeners
        const cancelBtn = editForm.querySelector('.btn-cancel-edit');
        const saveBtn = editForm.querySelector('.btn-save-edit');
        const titleInput = editForm.querySelector('.edit-title-input');
        const contentInput = editForm.querySelector('.edit-content-input');
        
        cancelBtn.addEventListener('click', function() {
          postContent.innerHTML = originalContent;
        });
        
        saveBtn.addEventListener('click', function() {
          const newTitle = titleInput.value.trim();
          const newContent = contentInput.value.trim();
          
          if (newTitle && newContent) {
            // Send PUT request to update post
            updatePost(postId, newTitle, newContent);
            titleElement.textContent = newTitle;
            contentElement.textContent = newContent;
            postContent.innerHTML = originalContent;
          } else {
            alert('Please fill in all fields');
          }
        });
      }

      // Function to update post via PUT request
      function updatePost(postId, title, content) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', postId);
        formData.append('title', title);
        formData.append('content', content);

        fetch('posts.php', {
          method: 'PUT',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Post updated successfully');
          } else {
            alert('Error updating post: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error updating post');
        });
      }

      // Function to delete post via DELETE request
      function deletePost(postId) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', postId);

        fetch('posts.php', {
          method: 'DELETE',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Post deleted successfully');
          } else {
            alert('Error deleting post: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error deleting post');
        });
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
            <div class="col-auto">
              <div class="comment-actions">
                <button class="btn-edit-comment" title="Edit Comment"><i class="bi bi-pencil"></i></button>
                <button class="btn-delete-comment" title="Delete Comment"><i class="bi bi-trash"></i></button>
              </div>
            </div>
          </div>
        `;
        commentsList.appendChild(commentElement);
        
        // Add event listeners for comment actions
        addCommentEventListeners(commentElement);
      }

      // Function to add event listeners to comments
      function addCommentEventListeners(commentElement) {
        const editBtn = commentElement.querySelector('.btn-edit-comment');
        const deleteBtn = commentElement.querySelector('.btn-delete-comment');
        const commentText = commentElement.querySelector('.comment-text');

        // Edit comment functionality
        if (editBtn) {
          editBtn.addEventListener('click', function() {
            const currentText = commentText.textContent;
            
            // Create inline editing form for comment
            const editForm = document.createElement('div');
            editForm.className = 'comment-edit-form';
            editForm.innerHTML = `
              <div class="comment-edit-container">
                <textarea class="form-textarea comment-edit-input" rows="2">${currentText}</textarea>
                <div class="comment-edit-actions">
                  <button class="btn-cancel-comment-edit">Cancel</button>
                  <button class="btn-save-comment-edit">Save</button>
                </div>
              </div>
            `;
            
            // Replace comment text with edit form
            const commentContainer = commentText.parentElement;
            const originalContent = commentContainer.innerHTML;
            commentContainer.innerHTML = '';
            commentContainer.appendChild(editForm);
            
            // Add event listeners
            const cancelBtn = editForm.querySelector('.btn-cancel-comment-edit');
            const saveBtn = editForm.querySelector('.btn-save-comment-edit');
            const textInput = editForm.querySelector('.comment-edit-input');
            
            cancelBtn.addEventListener('click', function() {
              commentContainer.innerHTML = originalContent;
            });
            
            saveBtn.addEventListener('click', function() {
              const newText = textInput.value.trim();
              if (newText) {
                commentText.textContent = newText;
                commentContainer.innerHTML = originalContent;
              } else {
                alert('Comment cannot be empty');
              }
            });
          });
        }

        // Delete comment functionality
        if (deleteBtn) {
          deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this comment?')) {
              commentElement.remove();
              
              // Update comment count
              const postElement = commentElement.closest('.card-post');
              const commentBtn = postElement.querySelector('.comment-btn');
              const countElement = commentBtn.querySelector('b');
              const currentCount = parseInt(countElement.textContent);
              countElement.textContent = Math.max(0, currentCount - 1);
              commentBtn.setAttribute('data-comments', Math.max(0, currentCount - 1));
            }
          });
        }
      }

      // Add event listeners to existing posts
      document.querySelectorAll('.card-post').forEach(addPostEventListeners);
    });
  </script>

</body>
</html>

</html>
