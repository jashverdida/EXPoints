// Bookmarks Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const bookmarksContainer = document.getElementById('bookmarksContainer');
    const loadingMessage = document.getElementById('loadingMessage');
    const emptyMessage = document.getElementById('emptyMessage');
    
    // Load bookmarked posts
    function loadBookmarks() {
        console.log('Loading bookmarked posts...'); // Debug
        fetch('../api/posts.php?action=get_bookmarked_posts')
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data); // Debug
                if (data.success) {
                    loadingMessage.style.display = 'none';
                    
                    if (data.posts.length === 0) {
                        emptyMessage.style.display = 'block';
                    } else {
                        emptyMessage.style.display = 'none';
                        renderBookmarks(data.posts);
                    }
                } else {
                    console.error('API returned error:', data.error);
                    loadingMessage.style.display = 'none';
                    showAlert('Error loading bookmarks: ' + data.error, 'danger');
                }
            })
            .catch(error => {
                console.error('Error loading bookmarks:', error);
                loadingMessage.style.display = 'none';
                showAlert('Error loading bookmarks', 'danger');
            });
    }
    
    // Render bookmarked posts
    function renderBookmarks(posts) {
        console.log('Rendering', posts.length, 'bookmarks'); // Debug
        
        posts.forEach((post, index) => {
            console.log(`Creating bookmark ${index + 1}:`, post.title); // Debug
            const postElement = document.createElement('div');
            postElement.innerHTML = createPostHTML(post);
            const postNode = postElement.firstElementChild;
            bookmarksContainer.appendChild(postNode);
            addPostEventListeners(postNode);
        });
    }
    
    // Create HTML for a single post
    function createPostHTML(post) {
        const likeIcon = post.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = post.user_liked ? 'liked' : '';
        
        return `
            <article class="card-post" data-post-id="${post.id}">
                <div class="post-header">
                    <div class="row gap-3 align-items-start">
                        <div class="col-auto"><div class="avatar-lg"></div></div>
                        <div class="col">
                            <h2 class="title mb-1">${escapeHtml(post.title)}</h2>
                            <div class="handle mb-3">@${escapeHtml(post.username)}</div>
                            <p class="mb-0">${escapeHtml(post.content)}</p>
                        </div>
                    </div>
                    <div class="post-menu">
                        <button class="icon bookmark-btn bookmarked" data-post-id="${post.id}" title="Remove Bookmark" aria-label="Remove Bookmark">
                            <i class="bi bi-bookmark-fill"></i>
                        </button>
                        ${post.is_owner ? `
                        <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="post-dropdown">
                            <button class="dropdown-item edit-post"><i class="bi bi-pencil"></i> Edit</button>
                            <button class="dropdown-item delete-post"><i class="bi bi-trash"></i> Delete</button>
                        </div>
                        ` : ''}
                    </div>
                </div>
                <div class="actions">
                    <span class="a like-btn ${likeClass}" data-post-id="${post.id}" data-liked="${post.user_liked}">
                        <i class="bi ${likeIcon}"></i><b>${post.like_count}</b>
                    </span>
                    <span class="a comment-btn" data-comments="${post.comment_count}">
                        <i class="bi bi-chat-left-text"></i><b>${post.comment_count}</b>
                    </span>
                </div>
                <div class="comments-section" style="display: none;">
                    <div class="comments-list">
                        <!-- Comments will be loaded here -->
                    </div>
                    <div class="comment-input-container">
                        <input type="text" class="comment-input" placeholder="Write a comment...">
                        <button class="comment-submit-btn">Post</button>
                    </div>
                </div>
            </article>
        `;
    }
    
    // Add event listeners to a post
    function addPostEventListeners(postElement) {
        const postId = postElement.dataset.postId;
        
        // Like button
        const likeBtn = postElement.querySelector('.like-btn');
        if (likeBtn) {
            likeBtn.addEventListener('click', function() {
                toggleLike(postId, this);
            });
        }
        
        // Bookmark button
        const bookmarkBtn = postElement.querySelector('.bookmark-btn');
        if (bookmarkBtn) {
            bookmarkBtn.addEventListener('click', function() {
                toggleBookmark(postId, this, postElement);
            });
        }
        
        // Comment button
        const commentBtn = postElement.querySelector('.comment-btn');
        const commentsSection = postElement.querySelector('.comments-section');
        if (commentBtn && commentsSection) {
            commentBtn.addEventListener('click', function() {
                if (commentsSection.style.display === 'none') {
                    commentsSection.style.display = 'block';
                    loadComments(postId, postElement);
                } else {
                    commentsSection.style.display = 'none';
                }
            });
        }
        
        // More menu toggle
        const moreBtn = postElement.querySelector('.more');
        const dropdown = postElement.querySelector('.post-dropdown');
        if (moreBtn && dropdown) {
            moreBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close all other dropdowns first
                document.querySelectorAll('.post-dropdown').forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('show');
            });
        }
    }
    
    // Close all dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.post-menu')) {
            document.querySelectorAll('.post-dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
    
    // Toggle like on post
    function toggleLike(postId, button) {
        fetch(`../api/posts.php?action=like&post_id=${postId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const icon = button.querySelector('i');
                const count = button.querySelector('b');
                
                if (data.liked) {
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill');
                    button.classList.add('liked');
                    button.dataset.liked = 'true';
                } else {
                    icon.classList.remove('bi-star-fill');
                    icon.classList.add('bi-star');
                    button.classList.remove('liked');
                    button.dataset.liked = 'false';
                }
                
                count.textContent = data.like_count;
            }
        })
        .catch(error => console.error('Error toggling like:', error));
    }
    
    // Toggle bookmark on post
    function toggleBookmark(postId, button, postElement) {
        fetch(`../api/posts.php?action=bookmark&post_id=${postId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!data.bookmarked) {
                    // Bookmark removed - remove post from view
                    postElement.style.opacity = '0';
                    setTimeout(() => {
                        postElement.remove();
                        
                        // Check if there are any bookmarks left
                        const remainingPosts = bookmarksContainer.querySelectorAll('.card-post');
                        if (remainingPosts.length === 0) {
                            emptyMessage.style.display = 'block';
                        }
                    }, 300);
                }
            }
        })
        .catch(error => console.error('Error toggling bookmark:', error));
    }
    
    // Load comments for a post
    function loadComments(postId, postElement) {
        const commentsList = postElement.querySelector('.comments-list');
        
        // Show loading state
        commentsList.innerHTML = '<p style="color: var(--muted); text-align: center;">Loading comments...</p>';
        
        fetch(`../api/posts.php?action=get_comments&post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    commentsList.innerHTML = '';
                    if (data.comments.length === 0) {
                        commentsList.innerHTML = '<p style="color: var(--muted); text-align: center;">No comments yet. Be the first to comment!</p>';
                    } else {
                        data.comments.forEach(comment => {
                            const commentElement = createCommentHTML(comment);
                            commentsList.insertAdjacentHTML('beforeend', commentElement);
                        });
                    }
                } else {
                    commentsList.innerHTML = '<p style="color: #ff6b6b; text-align: center;">Error loading comments</p>';
                }
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                commentsList.innerHTML = '<p style="color: #ff6b6b; text-align: center;">Error loading comments</p>';
            });
    }
    
    // Create HTML for a comment
    function createCommentHTML(comment) {
        return `
            <div class="comment-item">
                <div class="row g-3 align-items-start">
                    <div class="col-auto"><div class="avatar-sm"></div></div>
                    <div class="col">
                        <div class="comment-author">@${escapeHtml(comment.username)}</div>
                        <div class="comment-text">${escapeHtml(comment.comment)}</div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Show alert message
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-xl');
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initial load
    loadBookmarks();
});
