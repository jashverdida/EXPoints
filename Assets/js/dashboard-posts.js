// Dashboard Posts Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const simplePostBox = document.getElementById('simplePostBox');
    const simplePostInput = document.getElementById('simplePostInput');
    const expandedPostForm = document.getElementById('expandedPostForm');
    const postForm = document.getElementById('postForm');
    const cancelPostBtn = document.getElementById('cancelPost');
    const postsContainer = document.getElementById('postsContainer');
    
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
    });
    
    // Submit new post
    postForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted!'); // Debug log
        
        const title = document.getElementById('postTitle').value.trim();
        const content = document.getElementById('postContent').value.trim();
        const game = document.getElementById('gameSelect').value;
        
        console.log('Form data:', { title, content, game }); // Debug log
        
        if (!title || !content) {
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
                    ${post.is_owner ? `
                    <div class="post-menu">
                        <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="post-dropdown">
                            <button class="dropdown-item edit-post"><i class="bi bi-pencil"></i> Edit</button>
                            <button class="dropdown-item delete-post"><i class="bi bi-trash"></i> Delete</button>
                        </div>
                    </div>
                    ` : ''}
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
        
        // Comment button
        const commentBtn = postElement.querySelector('.comment-btn');
        const commentsSection = postElement.querySelector('.comments-section');
        if (commentBtn && commentsSection) {
            commentBtn.addEventListener('click', function() {
                if (commentsSection.style.display === 'none') {
                    commentsSection.style.display = 'block';
                } else {
                    commentsSection.style.display = 'none';
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
    
    // Initial load
    loadPosts();
});
