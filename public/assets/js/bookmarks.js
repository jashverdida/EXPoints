// Bookmarks Page JavaScript

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

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    const bookmarksContainer = document.getElementById('bookmarksContainer');
    
    // Load bookmarked posts
    function loadBookmarks() {
        console.log('Loading bookmarked posts...'); // Debug
        fetch('../api/posts.php?action=get_bookmarked_posts')
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data); // Debug
                if (data.success) {
                    if (data.posts.length === 0) {
                        showEmptyState();
                    } else {
                        renderBookmarks(data.posts);
                    }
                } else {
                    console.error('API returned error:', data.error);
                    showError('Error loading bookmarks: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error loading bookmarks:', error);
                showError('Error loading bookmarks');
            });
    }
    
    // Show empty state
    function showEmptyState() {
        bookmarksContainer.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-bookmark"></i>
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 1.5rem; margin-bottom: 0.5rem;">
                    Your Collection is Empty
                </p>
                <p style="color: rgba(255, 255, 255, 0.4); font-size: 1.1rem;">
                    Start saving your favorite reviews to build your treasure chest!
                </p>
                <a href="dashboard.php" class="cta-button">
                    <i class="bi bi-search"></i> Discover Posts
                </a>
            </div>
        `;
    }
    
    // Show error
    function showError(message) {
        bookmarksContainer.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-exclamation-triangle" style="color: rgba(255, 107, 107, 0.5);"></i>
                <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.3rem;">${message}</p>
                <button onclick="location.reload()" class="cta-button">
                    <i class="bi bi-arrow-clockwise"></i> Try Again
                </button>
            </div>
        `;
    }
    
    // Render bookmarked posts
    function renderBookmarks(posts) {
        console.log('Rendering', posts.length, 'bookmarks'); // Debug
        
        // Update stats
        document.getElementById('totalBookmarks').textContent = posts.length;
        const uniqueGames = [...new Set(posts.map(p => p.game))];
        document.getElementById('totalGames').textContent = uniqueGames.length;
        
        bookmarksContainer.innerHTML = '';
        posts.forEach((post, index) => {
            console.log(`Creating bookmark ${index + 1}:`, post.title); // Debug
            const postElement = document.createElement('div');
            postElement.innerHTML = createPostHTML(post, index);
            const postNode = postElement.firstElementChild;
            bookmarksContainer.appendChild(postNode);
            addPostEventListeners(postNode);
        });
    }
    
    // Create HTML for a single post with treasure theme
    function createPostHTML(post, index) {
        const likeIcon = post.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = post.user_liked ? 'liked' : '';
        const profilePicture = post.author_profile_picture || '../assets/img/cat1.jpg';
        const timestamp = timeAgo(post.created_at);
        const bookmarkedBadge = '<span class="bookmarked-badge"><i class="bi bi-bookmark-fill"></i> Saved</span>';
        
        return `
            <article class="card-post" data-post-id="${post.id}">
                ${bookmarkedBadge}
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
                        <button class="icon bookmark-btn bookmarked" data-post-id="${post.id}" title="Remove from Collection" aria-label="Remove Bookmark">
                            <i class="bi bi-bookmark-fill"></i>
                        </button>
                    </div>
                </div>
                <div class="actions">
                    <span class="a like-btn ${likeClass}" data-post-id="${post.id}" data-liked="${post.user_liked}">
                        <i class="bi ${likeIcon}"></i><b>${post.like_count || 0}</b>
                    </span>
                    <span class="a comment-btn" data-comments="${post.comment_count || 0}">
                        <i class="bi bi-chat-left-text"></i><b>${post.comment_count || 0}</b>
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
        
        // Comment submit button
        const commentSubmitBtn = postElement.querySelector('.comment-submit-btn');
        const commentInput = postElement.querySelector('.comment-input');
        if (commentSubmitBtn && commentInput) {
            commentSubmitBtn.addEventListener('click', function() {
                addComment(postId, postElement);
            });
            
            // Allow Enter key to submit
            commentInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    addComment(postId, postElement);
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
                    // Bookmark removed - fade out and remove post from collection
                    postElement.style.opacity = '0';
                    postElement.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        postElement.remove();
                        
                        // Update stats and check if collection is empty
                        const remainingPosts = bookmarksContainer.querySelectorAll('.post-card');
                        if (remainingPosts.length === 0) {
                            showEmptyState();
                        } else {
                            // Update stats
                            document.getElementById('totalBookmarks').textContent = remainingPosts.length;
                            const games = [...new Set(Array.from(remainingPosts).map(p => {
                                const gameEl = p.querySelector('.post-game');
                                return gameEl ? gameEl.textContent : '';
                            }).filter(g => g))];
                            document.getElementById('totalGames').textContent = games.length;
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
        const currentUserId = parseInt(document.body.dataset.userId) || 0;
        
        // Show loading state
        commentsList.innerHTML = '<p style="color: rgba(255, 255, 255, 0.5); text-align: center; padding: 1rem;">Loading comments...</p>';
        
        fetch(`../api/posts.php?action=get_comments&post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    commentsList.innerHTML = '';
                    
                    // Update comment count in button
                    const commentBtn = postElement.querySelector('.comment-btn');
                    if (commentBtn) {
                        const countB = commentBtn.querySelector('b');
                        if (countB) {
                            countB.textContent = data.comments.length;
                        }
                        commentBtn.dataset.comments = data.comments.length;
                    }
                    
                    if (data.comments.length === 0) {
                        commentsList.innerHTML = '<p style="color: rgba(255, 255, 255, 0.5); text-align: center; padding: 1rem;">No comments yet. Be the first to comment!</p>';
                    } else {
                        data.comments.forEach(comment => {
                            const commentElement = createCommentHTML(comment, currentUserId);
                            commentsList.insertAdjacentHTML('beforeend', commentElement);
                        });
                        
                        // Add event listeners to comment actions
                        commentsList.querySelectorAll('.comment-item').forEach(commentItem => {
                            addCommentEventListeners(commentItem, postId, postElement);
                        });
                    }
                } else {
                    commentsList.innerHTML = '<p style="color: #ff6b6b; text-align: center; padding: 1rem;">Error loading comments</p>';
                }
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                commentsList.innerHTML = '<p style="color: #ff6b6b; text-align: center; padding: 1rem;">Error loading comments</p>';
            });
    }
    
    // Create HTML for a comment
    function createCommentHTML(comment, currentUserId) {
        const profilePicture = comment.commenter_profile_picture || '../assets/img/cat1.jpg';
        const likeIcon = comment.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = comment.user_liked ? 'liked' : '';
        const timestamp = timeAgo(comment.created_at);
        const isOwner = comment.user_id == currentUserId;
        
        return `
            <div class="comment-item" data-comment-id="${comment.id}" data-user-id="${comment.user_id}">
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
                    <div class="col" style="position: relative;">
                        <div class="comment-author">@${escapeHtml(comment.username)}</div>
                        <div class="comment-text">${escapeHtml(comment.comment)}</div>
                        <div class="comment-actions" style="display: flex; gap: 1rem; margin-top: 0.5rem; font-size: 0.875rem;">
                            <button class="comment-like-btn ${likeClass}" data-comment-id="${comment.id}" style="background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="bi ${likeIcon}"></i>
                                <span class="comment-like-count">${comment.like_count || 0}</span>
                            </button>
                            <span class="comment-time" style="color: rgba(255, 255, 255, 0.4);">${timestamp}</span>
                        </div>
                        ${isOwner ? `
                        <div class="comment-menu" style="position: absolute; top: 0; right: 0;">
                            <button class="icon more-comment" style="border: 0; background: transparent; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0.25rem;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="comment-dropdown" style="display: none; position: absolute; right: 0; top: 100%; background: #1a0033; border: 1px solid rgba(251, 197, 49, 0.3); border-radius: 8px; padding: 0.5rem; min-width: 120px; z-index: 1000; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">
                                <button class="dropdown-item edit-comment" style="width: 100%; text-align: left; padding: 0.5rem; border: none; background: transparent; color: white; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="dropdown-item delete-comment" style="width: 100%; text-align: left; padding: 0.5rem; border: none; background: transparent; color: #ff4444; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Add event listeners to comment actions
    function addCommentEventListeners(commentItem, postId, postElement) {
        // Comment like button
        const likeBtn = commentItem.querySelector('.comment-like-btn');
        if (likeBtn) {
            likeBtn.addEventListener('click', function() {
                toggleCommentLike(this);
            });
        }
        
        // More menu toggle
        const moreBtn = commentItem.querySelector('.more-comment');
        const dropdown = commentItem.querySelector('.comment-dropdown');
        if (moreBtn && dropdown) {
            moreBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close all other dropdowns
                document.querySelectorAll('.comment-dropdown').forEach(d => {
                    if (d !== dropdown) d.style.display = 'none';
                });
                
                dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            });
        }
        
        // Edit comment
        const editBtn = commentItem.querySelector('.edit-comment');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                editComment(commentItem, postId, postElement);
            });
        }
        
        // Delete comment
        const deleteBtn = commentItem.querySelector('.delete-comment');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                deleteComment(commentItem, postId, postElement);
            });
        }
    }
    
    // Toggle comment like
    function toggleCommentLike(button) {
        const commentId = button.dataset.commentId;
        
        fetch(`../api/posts.php?action=like_comment&comment_id=${commentId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const icon = button.querySelector('i');
                const count = button.querySelector('.comment-like-count');
                
                if (data.liked) {
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill');
                    button.classList.add('liked');
                } else {
                    icon.classList.remove('bi-star-fill');
                    icon.classList.add('bi-star');
                    button.classList.remove('liked');
                }
                
                count.textContent = data.like_count || 0;
            }
        })
        .catch(error => console.error('Error toggling comment like:', error));
    }
    
    // Add comment
    function addComment(postId, postElement) {
        const commentInput = postElement.querySelector('.comment-input');
        const commentText = commentInput.value.trim();
        
        if (!commentText) {
            return;
        }
        
        fetch('../api/posts.php?action=add_comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                post_id: postId,
                comment: commentText
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                commentInput.value = '';
                loadComments(postId, postElement);
            } else {
                alert('Failed to add comment: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error adding comment:', error);
            alert('Error adding comment');
        });
    }
    
    // Edit comment
    function editComment(commentItem, postId, postElement) {
        const commentId = commentItem.dataset.commentId;
        const commentTextElem = commentItem.querySelector('.comment-text');
        const currentText = commentTextElem.textContent.trim();
        
        showEditModal('Edit your comment:', currentText, (newText) => {
            fetch('../api/posts.php?action=update_comment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    comment_id: parseInt(commentId),
                    comment: newText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    commentTextElem.textContent = newText;
                    const dropdown = commentItem.querySelector('.comment-dropdown');
                    if (dropdown) dropdown.style.display = 'none';
                } else {
                    alert('Failed to edit comment: ' + (data.error || data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error editing comment');
            });
        });
    }
    
    // Delete comment
    function deleteComment(commentItem, postId, postElement) {
        const commentId = commentItem.dataset.commentId;
        
        showConfirmModal('Are you sure you want to delete this comment?', () => {
            fetch('../api/posts.php?action=delete_comment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    comment_id: parseInt(commentId)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadComments(postId, postElement);
                } else {
                    alert('Failed to delete comment: ' + (data.error || data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting comment');
            });
        });
    }
    
    // Show confirmation modal
    function showConfirmModal(message, onConfirm) {
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); display: flex; align-items: center; justify-content: center; z-index: 10000;';
        modal.innerHTML = `
            <div style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.95), rgba(20, 0, 40, 0.95)); border: 2px solid rgba(251, 197, 49, 0.3); border-radius: 1.5rem; padding: 2rem; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <i class="bi bi-question-circle-fill" style="font-size: 3rem; color: rgba(251, 197, 49, 0.8);"></i>
                </div>
                <h3 style="color: white; text-align: center; margin-bottom: 2rem; font-size: 1.25rem;">${message}</h3>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button class="modal-btn btn-cancel" style="flex: 1; padding: 0.75rem 1.5rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 0.75rem; color: white; cursor: pointer; font-weight: 600; transition: all 0.3s;">No</button>
                    <button class="modal-btn btn-confirm" style="flex: 1; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, rgba(251, 197, 49, 0.8), rgba(243, 156, 18, 0.8)); border: none; border-radius: 0.75rem; color: #0a0a0a; cursor: pointer; font-weight: 700; transition: all 0.3s;">Yes</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        modal.querySelector('.btn-cancel').addEventListener('click', () => modal.remove());
        modal.querySelector('.btn-confirm').addEventListener('click', () => {
            modal.remove();
            onConfirm();
        });
    }
    
    // Show edit modal
    function showEditModal(title, currentText, onSave) {
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); display: flex; align-items: center; justify-content: center; z-index: 10000;';
        modal.innerHTML = `
            <div style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.95), rgba(20, 0, 40, 0.95)); border: 2px solid rgba(251, 197, 49, 0.3); border-radius: 1.5rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);">
                <div style="text-align: center; margin-bottom: 1rem;">
                    <i class="bi bi-pencil-square" style="font-size: 2.5rem; color: rgba(251, 197, 49, 0.8);"></i>
                </div>
                <h3 style="color: white; text-align: center; margin-bottom: 1.5rem; font-size: 1.25rem;">${title}</h3>
                <textarea class="edit-modal-textarea" style="width: 100%; min-height: 120px; padding: 1rem; background: rgba(0, 0, 0, 0.5); border: 2px solid rgba(251, 197, 49, 0.3); border-radius: 0.75rem; color: white; font-family: 'Poppins', sans-serif; font-size: 1rem; resize: vertical; margin-bottom: 1.5rem;">${escapeHtml(currentText)}</textarea>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button class="modal-btn btn-cancel" style="flex: 1; padding: 0.75rem 1.5rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 0.75rem; color: white; cursor: pointer; font-weight: 600; transition: all 0.3s;">Cancel</button>
                    <button class="modal-btn btn-save" style="flex: 1; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, rgba(251, 197, 49, 0.8), rgba(243, 156, 18, 0.8)); border: none; border-radius: 0.75rem; color: #0a0a0a; cursor: pointer; font-weight: 700; transition: all 0.3s;">Save</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        const textarea = modal.querySelector('.edit-modal-textarea');
        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        
        modal.querySelector('.btn-cancel').addEventListener('click', () => modal.remove());
        modal.querySelector('.btn-save').addEventListener('click', () => {
            const newText = textarea.value.trim();
            if (newText && newText !== currentText) {
                modal.remove();
                onSave(newText);
            } else if (!newText) {
                alert('Comment cannot be empty');
            } else {
                modal.remove();
            }
        });
        
        textarea.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                modal.querySelector('.btn-save').click();
            }
        });
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
