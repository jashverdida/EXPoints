// Dashboard Posts Management JavaScript

// Facebook-style time ago function
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
    
    // Format as date for older posts
    const options = { month: 'short', day: 'numeric' };
    if (past.getFullYear() !== now.getFullYear()) {
        options.year = 'numeric';
    }
    return past.toLocaleDateString('en-US', options);
}

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const simplePostBox = document.getElementById('simplePostBox');
    const simplePostInput = document.getElementById('simplePostInput');
    const expandedPostForm = document.getElementById('expandedPostForm');
    const postForm = document.getElementById('postForm');
    const cancelPostBtn = document.getElementById('cancelPost');
    const postsContainer = document.getElementById('postsContainer');
    const gameSelect = document.getElementById('gameSelect');
    const customGameGroup = document.getElementById('customGameGroup');
    const customGameInput = document.getElementById('customGame');
    
    // Show/hide custom game input based on selection
    gameSelect.addEventListener('change', function() {
        if (this.value === 'Other') {
            customGameGroup.style.display = 'block';
            customGameInput.required = true;
        } else {
            customGameGroup.style.display = 'none';
            customGameInput.required = false;
            customGameInput.value = '';
        }
    });
    
    // Click on simple input to expand form
    simplePostInput.addEventListener('click', function() {
        simplePostBox.style.display = 'none';
        expandedPostForm.style.display = 'block';
        document.getElementById('postTitle').focus();
    });
    
    // Cancel button - collapse form
    cancelPostBtn.addEventListener('click', function() {
        expandedPostForm.style.display = 'none';
        simplePostBox.style.display = 'block';
        postForm.reset();
        customGameGroup.style.display = 'none';
        customGameInput.required = false;
    });
    
    // Submit new post
    postForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted!'); // Debug log
        
        const title = document.getElementById('postTitle').value.trim();
        const content = document.getElementById('postContent').value.trim();
        let game = document.getElementById('gameSelect').value;
        
        // If "Other" is selected, use the custom game input
        if (game === 'Other') {
            const customGame = document.getElementById('customGame').value.trim();
            if (!customGame) {
                showAlert('Please specify the game name', 'danger');
                return;
            }
            game = customGame;
        }
        
        console.log('Form data:', { title, content, game }); // Debug log
        
        if (!title || !content || !game) {
            showAlert('Please fill in all required fields', 'danger');
            return;
        }
        
        // Show loading and disable multiple submissions
        const submitBtn = postForm.querySelector('.btn-post');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Posting...';
        submitBtn.disabled = true;
        
        fetch('../api/posts.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: title,
                content: content,
                game: game
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success modal
                showSuccessModal('Post created successfully!');
                
                // Reset form and collapse
                postForm.reset();
                expandedPostForm.style.display = 'none';
                simplePostBox.style.display = 'block';
                
                // Hide custom game input
                customGameGroup.style.display = 'none';
                customGameInput.required = false;
                
                // Reload posts to show the new one
                loadPosts();
            } else {
                showAlert(data.error || 'Failed to create post', 'danger');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            showAlert('Error: ' + error.message, 'danger');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        })
        .finally(() => {
            // Re-enable button after successful post
            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 1000);
        });
    });
    
    // Load all posts
    function loadPosts() {
        console.log('Loading posts from API...'); // Debug
        fetch('../api/posts.php?action=get_posts')
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data); // Debug
                if (data.success) {
                    console.log('Number of posts:', data.posts.length); // Debug
                    renderPosts(data.posts);
                } else {
                    console.error('API returned error:', data.error);
                }
            })
            .catch(error => console.error('Error loading posts:', error));
    }
    
    // Render posts
    function renderPosts(posts) {
        console.log('Rendering posts. Count:', posts.length); // Debug
        
        // Remove all dynamically loaded posts (not the dummy post)
        const dynamicPosts = postsContainer.querySelectorAll('.card-post:not([data-post-id="dummy"])');
        console.log('Removing', dynamicPosts.length, 'existing dynamic posts'); // Debug
        dynamicPosts.forEach(post => post.remove());
        
        if (posts.length === 0) {
            console.log('No posts to display'); // Debug
            // If no posts, just show the dummy post (which is already there)
            return;
        }
        
        // Create and append each post after the dummy post
        posts.forEach((post, index) => {
            console.log(`Creating post ${index + 1}:`, post.title); // Debug
            const postElement = document.createElement('div');
            postElement.innerHTML = createPostHTML(post);
            const postNode = postElement.firstElementChild;
            postsContainer.appendChild(postNode);
            addPostEventListeners(postNode);
        });
        
        console.log('All posts rendered successfully'); // Debug
    }
    
    // Create HTML for a single post
    function createPostHTML(post) {
        const likeIcon = post.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = post.user_liked ? 'liked' : '';
        const bookmarkIcon = post.user_bookmarked ? 'bi-bookmark-fill' : 'bi-bookmark';
        const bookmarkClass = post.user_bookmarked ? 'bookmarked' : '';
        const profilePicture = post.author_profile_picture || '../assets/img/cat1.jpg';
        const timestamp = timeAgo(post.created_at);
        
        return `
            <article class="card-post" data-post-id="${post.id}">
                <div class="post-header">
                    <div class="row gap-3 align-items-start">
                        <div class="col-auto">
                            <div class="avatar-lg user-profile-avatar" 
                                 data-user-id="${post.user_id}" 
                                 data-username="${escapeHtml(post.username)}"
                                 data-profile-picture="${escapeHtml(profilePicture)}"
                                 data-exp="${post.exp_points || 0}">
                                <img src="${escapeHtml(profilePicture)}" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
                            </div>
                        </div>
                        <div class="col">
                            <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 0.25rem;">
                                <h2 class="title" style="margin: 0;">${escapeHtml(post.title)}</h2>
                                <span class="post-timestamp" style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.5); font-weight: 400;">${timestamp}</span>
                            </div>
                            <div class="handle mb-3">@${escapeHtml(post.username)}</div>
                            <p class="mb-0">${escapeHtml(post.content)}</p>
                        </div>
                    </div>
                    <div class="post-menu">
                        <button class="icon bookmark-btn ${bookmarkClass}" data-post-id="${post.id}" title="Bookmark" aria-label="Bookmark">
                            <i class="bi ${bookmarkIcon}"></i>
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
                toggleBookmark(postId, this);
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
        
        // Comment submit button
        const commentInput = postElement.querySelector('.comment-input');
        const commentSubmitBtn = postElement.querySelector('.comment-submit-btn');
        if (commentInput && commentSubmitBtn) {
            commentSubmitBtn.addEventListener('click', function() {
                submitComment(postId, postElement, commentInput);
            });
            
            commentInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    submitComment(postId, postElement, commentInput);
                }
            });
        }
        
        // Edit button
        const editBtn = postElement.querySelector('.edit-post');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                editPost(postId, postElement);
            });
        }
        
        // Delete button
        const deleteBtn = postElement.querySelector('.delete-post');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                deletePost(postId, postElement);
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
    function toggleBookmark(postId, button) {
        fetch(`../api/posts.php?action=bookmark&post_id=${postId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const icon = button.querySelector('i');
                
                if (data.bookmarked) {
                    icon.classList.remove('bi-bookmark');
                    icon.classList.add('bi-bookmark-fill');
                    button.classList.add('bookmarked');
                    button.title = 'Remove Bookmark';
                } else {
                    icon.classList.remove('bi-bookmark-fill');
                    icon.classList.add('bi-bookmark');
                    button.classList.remove('bookmarked');
                    button.title = 'Bookmark';
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
                        
                        // Add event listeners to comment actions
                        commentsList.querySelectorAll('.comment-like-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const commentId = this.getAttribute('data-comment-id');
                                toggleCommentLike(commentId, this);
                            });
                        });
                        
                        commentsList.querySelectorAll('.comment-reply-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const commentId = this.getAttribute('data-comment-id');
                                showReplyInput(commentId);
                            });
                        });
                        
                        commentsList.querySelectorAll('.view-replies-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const commentId = this.getAttribute('data-comment-id');
                                loadReplies(commentId);
                            });
                        });
                        
                        commentsList.querySelectorAll('.reply-submit-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const commentItem = this.closest('.comment-item');
                                const commentId = commentItem.getAttribute('data-comment-id');
                                const replyInput = commentItem.querySelector('.reply-input');
                                submitReply(commentId, postId, replyInput);
                            });
                        });
                        
                        commentsList.querySelectorAll('.reply-cancel-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const commentItem = this.closest('.comment-item');
                                const commentId = commentItem.getAttribute('data-comment-id');
                                hideReplyInput(commentId);
                            });
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
        const profilePicture = comment.commenter_profile_picture || '../assets/img/cat1.jpg';
        const likeIcon = comment.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = comment.user_liked ? 'liked' : '';
        const timestamp = timeAgo(comment.created_at);
        
        return `
            <div class="comment-item" data-comment-id="${comment.id}">
                <div class="row g-3 align-items-start">
                    <div class="col-auto">
                        <div class="avatar-sm user-profile-avatar" 
                             data-user-id="${comment.user_id}" 
                             data-username="${escapeHtml(comment.username)}"
                             data-profile-picture="${escapeHtml(profilePicture)}"
                             data-exp="${comment.exp_points || 0}">
                            <img src="${escapeHtml(profilePicture)}" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
                        </div>
                    </div>
                    <div class="col">
                        <div class="comment-author">@${escapeHtml(comment.username)}</div>
                        <div class="comment-text">${escapeHtml(comment.comment)}</div>
                        <div class="comment-actions" style="display: flex; gap: 1rem; margin-top: 0.5rem; font-size: 0.875rem;">
                            <button class="comment-like-btn ${likeClass}" data-comment-id="${comment.id}" style="background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="bi ${likeIcon}"></i>
                                <span class="comment-like-count">${comment.like_count || 0}</span>
                            </button>
                            <button class="comment-reply-btn" data-comment-id="${comment.id}" style="background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0;">
                                Reply
                            </button>
                            <span class="comment-time" style="color: rgba(255, 255, 255, 0.4);">${timestamp}</span>
                            ${comment.reply_count > 0 ? `
                                <button class="view-replies-btn" data-comment-id="${comment.id}" style="background: none; border: none; color: rgba(56, 160, 255, 0.8); cursor: pointer; padding: 0;">
                                    View ${comment.reply_count} ${comment.reply_count === 1 ? 'reply' : 'replies'}
                                </button>
                            ` : ''}
                        </div>
                        <div class="replies-container" data-comment-id="${comment.id}" style="margin-top: 1rem; margin-left: 1rem; display: none;"></div>
                        <div class="reply-input-container" data-comment-id="${comment.id}" style="margin-top: 1rem; display: none;">
                            <input type="text" class="reply-input" placeholder="Write a reply..." style="width: 100%; padding: 0.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 0.5rem; color: white;">
                            <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                <button class="reply-submit-btn" style="padding: 0.375rem 1rem; background: #38a0ff; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">Post</button>
                                <button class="reply-cancel-btn" style="padding: 0.375rem 1rem; background: rgba(255, 255, 255, 0.1); color: white; border: none; border-radius: 0.5rem; cursor: pointer;">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Submit a comment
    function submitComment(postId, postElement, commentInput) {
        const commentText = commentInput.value.trim();
        
        if (!commentText) {
            showAlert('Please enter a comment', 'warning');
            return;
        }
        
        // Disable input while submitting
        commentInput.disabled = true;
        
        fetch('../api/posts.php?action=add_comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `post_id=${postId}&comment=${encodeURIComponent(commentText)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                commentInput.value = '';
                
                // Reload comments
                loadComments(postId, postElement);
                
                // Update comment count
                const commentBtn = postElement.querySelector('.comment-btn');
                const countElement = commentBtn.querySelector('b');
                const currentCount = parseInt(countElement.textContent);
                countElement.textContent = currentCount + 1;
                commentBtn.setAttribute('data-comments', currentCount + 1);
            } else {
                showAlert(data.error || 'Failed to add comment', 'danger');
            }
        })
        .catch(error => {
            console.error('Error adding comment:', error);
            showAlert('Error adding comment', 'danger');
        })
        .finally(() => {
            commentInput.disabled = false;
        });
    }
    
    // Toggle like on a comment
    function toggleCommentLike(commentId, likeBtn) {
        fetch('../api/posts.php?action=like_comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `comment_id=${commentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const icon = likeBtn.querySelector('i');
                const countSpan = likeBtn.querySelector('.comment-like-count');
                
                if (data.liked) {
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill');
                    likeBtn.classList.add('liked');
                } else {
                    icon.classList.remove('bi-star-fill');
                    icon.classList.add('bi-star');
                    likeBtn.classList.remove('liked');
                }
                
                countSpan.textContent = data.like_count;
            } else {
                showAlert(data.error || 'Failed to like comment', 'danger');
            }
        })
        .catch(error => {
            console.error('Error liking comment:', error);
            showAlert('Error liking comment', 'danger');
        });
    }
    
    // Show reply input for a comment
    function showReplyInput(commentId) {
        const replyContainer = document.querySelector(`.reply-input-container[data-comment-id="${commentId}"]`);
        replyContainer.style.display = 'block';
        replyContainer.querySelector('.reply-input').focus();
    }
    
    // Hide reply input
    function hideReplyInput(commentId) {
        const replyContainer = document.querySelector(`.reply-input-container[data-comment-id="${commentId}"]`);
        replyContainer.style.display = 'none';
        replyContainer.querySelector('.reply-input').value = '';
    }
    
    // Submit a reply to a comment
    function submitReply(parentCommentId, postId, replyInput) {
        const replyText = replyInput.value.trim();
        
        if (!replyText) {
            showAlert('Please enter a reply', 'warning');
            return;
        }
        
        replyInput.disabled = true;
        
        fetch('../api/posts.php?action=add_reply', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `parent_comment_id=${parentCommentId}&post_id=${postId}&comment=${encodeURIComponent(replyText)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input and hide
                hideReplyInput(parentCommentId);
                
                // Load replies for this comment
                loadReplies(parentCommentId);
                
                // Update reply count button
                const commentItem = document.querySelector(`.comment-item[data-comment-id="${parentCommentId}"]`);
                let viewRepliesBtn = commentItem.querySelector('.view-replies-btn');
                const commentActions = commentItem.querySelector('.comment-actions');
                
                if (!viewRepliesBtn) {
                    // Create the button if it doesn't exist
                    viewRepliesBtn = document.createElement('button');
                    viewRepliesBtn.className = 'view-replies-btn';
                    viewRepliesBtn.setAttribute('data-comment-id', parentCommentId);
                    viewRepliesBtn.style.cssText = 'background: none; border: none; color: rgba(56, 160, 255, 0.8); cursor: pointer; padding: 0;';
                    commentActions.appendChild(viewRepliesBtn);
                    
                    // Add event listener
                    viewRepliesBtn.addEventListener('click', function() {
                        loadReplies(parentCommentId);
                    });
                }
                
                // Update button text
                const currentCount = parseInt(viewRepliesBtn.textContent.match(/\d+/)[0] || 0) + 1;
                viewRepliesBtn.textContent = `View ${currentCount} ${currentCount === 1 ? 'reply' : 'replies'}`;
            } else {
                showAlert(data.error || 'Failed to add reply', 'danger');
            }
        })
        .catch(error => {
            console.error('Error adding reply:', error);
            showAlert('Error adding reply', 'danger');
        })
        .finally(() => {
            replyInput.disabled = false;
        });
    }
    
    // Load replies for a comment
    function loadReplies(parentCommentId) {
        const repliesContainer = document.querySelector(`.replies-container[data-comment-id="${parentCommentId}"]`);
        
        // Toggle visibility
        if (repliesContainer.style.display === 'block') {
            repliesContainer.style.display = 'none';
            return;
        }
        
        repliesContainer.innerHTML = '<p style="color: rgba(255, 255, 255, 0.5); text-align: center;">Loading replies...</p>';
        repliesContainer.style.display = 'block';
        
        fetch(`../api/posts.php?action=get_replies&parent_comment_id=${parentCommentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.replies.length === 0) {
                        repliesContainer.innerHTML = '<p style="color: rgba(255, 255, 255, 0.5); text-align: center;">No replies yet</p>';
                    } else {
                        repliesContainer.innerHTML = data.replies.map(reply => createReplyHTML(reply)).join('');
                        
                        // Add event listeners to reply like buttons
                        repliesContainer.querySelectorAll('.comment-like-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const replyId = this.getAttribute('data-comment-id');
                                toggleCommentLike(replyId, this);
                            });
                        });
                    }
                } else {
                    repliesContainer.innerHTML = '<p style="color: #ff6b6b; text-align: center;">Error loading replies</p>';
                }
            })
            .catch(error => {
                console.error('Error loading replies:', error);
                repliesContainer.innerHTML = '<p style="color: #ff6b6b; text-align: center;">Error loading replies</p>';
            });
    }
    
    // Create HTML for a reply (similar to comment but simpler)
    function createReplyHTML(reply) {
        const profilePicture = reply.commenter_profile_picture || '../assets/img/cat1.jpg';
        const likeIcon = reply.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = reply.user_liked ? 'liked' : '';
        const timestamp = timeAgo(reply.created_at);
        
        return `
            <div class="reply-item" data-comment-id="${reply.id}" style="margin-bottom: 1rem;">
                <div class="row g-3 align-items-start">
                    <div class="col-auto">
                        <div class="avatar-sm" style="width: 32px; height: 32px;">
                            <img src="${escapeHtml(profilePicture)}" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
                        </div>
                    </div>
                    <div class="col">
                        <div class="comment-author">@${escapeHtml(reply.username)}</div>
                        <div class="comment-text">${escapeHtml(reply.comment)}</div>
                        <div class="comment-actions" style="display: flex; gap: 1rem; margin-top: 0.5rem; font-size: 0.875rem;">
                            <button class="comment-like-btn ${likeClass}" data-comment-id="${reply.id}" style="background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="bi ${likeIcon}"></i>
                                <span class="comment-like-count">${reply.like_count || 0}</span>
                            </button>
                            <span class="comment-time" style="color: rgba(255, 255, 255, 0.4);">${timestamp}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Edit post
    function editPost(postId, postElement) {
        const titleElement = postElement.querySelector('.title');
        const contentElement = postElement.querySelector('p');
        
        const currentTitle = titleElement.textContent;
        const currentContent = contentElement.textContent;
        
        // Create edit form
        const editForm = document.createElement('div');
        editForm.className = 'edit-form';
        editForm.innerHTML = `
            <div class="mb-3">
                <input type="text" class="form-input" id="edit-title-${postId}" value="${escapeHtml(currentTitle)}">
            </div>
            <div class="mb-3">
                <textarea class="form-textarea" id="edit-content-${postId}" rows="4">${escapeHtml(currentContent)}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn-cancel cancel-edit">Cancel</button>
                <button class="btn-post save-edit">Save</button>
            </div>
        `;
        
        // Replace content with edit form
        const contentContainer = postElement.querySelector('.col');
        const originalContent = contentContainer.innerHTML;
        contentContainer.innerHTML = '';
        contentContainer.appendChild(editForm);
        
        // Cancel edit
        editForm.querySelector('.cancel-edit').addEventListener('click', function() {
            contentContainer.innerHTML = originalContent;
            addPostEventListeners(postElement);
        });
        
        // Save edit
        editForm.querySelector('.save-edit').addEventListener('click', function() {
            const newTitle = document.getElementById(`edit-title-${postId}`).value.trim();
            const newContent = document.getElementById(`edit-content-${postId}`).value.trim();
            
            if (!newTitle || !newContent) {
                showAlert('Title and content cannot be empty', 'danger');
                return;
            }
            
            // Show confirmation modal
            showConfirmModal(
                'Are you sure you want to save changes?',
                function() {
                    // User clicked Yes
                    fetch('../api/posts.php?action=update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            post_id: postId,
                            title: newTitle,
                            content: newContent
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessModal('Post updated successfully!');
                            loadPosts(); // Reload all posts
                        } else {
                            showAlert(data.error || 'Failed to update post', 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Error: ' + error.message, 'danger');
                    });
                }
            );
        });
    }
    
    // Delete post
    function deletePost(postId, postElement) {
        showConfirmModal(
            'Are you sure you want to delete this post?',
            function() {
                // User clicked Yes
                fetch('../api/posts.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${postId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessModal('Post deleted successfully!');
                        postElement.remove();
                    } else {
                        showAlert(data.error || 'Failed to delete post', 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Error: ' + error.message, 'danger');
                });
            }
        );
    }
    
    // Show alert message
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-xxl');
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Show success modal
    function showSuccessModal(message) {
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        modal.innerHTML = `
            <div class="custom-modal-content success-modal">
                <div class="modal-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h3>${message}</h3>
                <button class="modal-btn" onclick="this.closest('.custom-modal').remove()">OK</button>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Auto close after 2 seconds
        setTimeout(() => {
            modal.remove();
        }, 2000);
    }
    
    // Show confirmation modal
    function showConfirmModal(message, onConfirm) {
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        modal.innerHTML = `
            <div class="custom-modal-content confirm-modal">
                <div class="modal-icon warning">
                    <i class="bi bi-question-circle-fill"></i>
                </div>
                <h3>${message}</h3>
                <div class="modal-buttons">
                    <button class="modal-btn btn-cancel" data-action="cancel">No</button>
                    <button class="modal-btn btn-confirm" data-action="confirm">Yes</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Handle button clicks
        modal.querySelector('[data-action="cancel"]').addEventListener('click', function() {
            modal.remove();
        });
        
        modal.querySelector('[data-action="confirm"]').addEventListener('click', function() {
            modal.remove();
            onConfirm();
        });
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Profile Hover Modal functionality
    const profileHoverModal = document.getElementById('profileHoverModal');
    const hoverProfilePic = document.getElementById('hoverProfilePic');
    const hoverProfileUsername = document.getElementById('hoverProfileUsername');
    const hoverProfileLevel = document.getElementById('hoverProfileLevel');
    const hoverProfileExp = document.getElementById('hoverProfileExp');
    
    let hoverTimeout;
    
    // Handle profile avatar hover and click
    document.addEventListener('mouseover', function(e) {
        const avatar = e.target.closest('.user-profile-avatar');
        if (avatar) {
            const userId = avatar.dataset.userId;
            const username = avatar.dataset.username;
            const profilePicture = avatar.dataset.profilePicture;
            const exp = parseInt(avatar.dataset.exp) || 0;
            const level = Math.floor(exp / 1000) + 1;
            
            // Update modal content
            hoverProfilePic.src = profilePicture;
            hoverProfileUsername.textContent = '@' + username;
            hoverProfileLevel.textContent = 'LVL ' + level;
            hoverProfileExp.textContent = exp + ' EXP';
            
            // Position the modal near the cursor
            clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                const rect = avatar.getBoundingClientRect();
                profileHoverModal.style.left = (rect.right + 15) + 'px';
                profileHoverModal.style.top = (rect.top) + 'px';
                profileHoverModal.style.display = 'block';
            }, 300);
        }
    });
    
    document.addEventListener('mouseout', function(e) {
        const avatar = e.target.closest('.user-profile-avatar');
        if (avatar) {
            clearTimeout(hoverTimeout);
            profileHoverModal.style.display = 'none';
        }
    });
    
    // Handle profile avatar click
    document.addEventListener('click', function(e) {
        const avatar = e.target.closest('.user-profile-avatar');
        if (avatar) {
            const userId = avatar.dataset.userId;
            // Redirect to profile page
            window.location.href = 'view-profile.php?id=' + userId;
        }
    });
    
    // Initial load
    loadPosts();
});
