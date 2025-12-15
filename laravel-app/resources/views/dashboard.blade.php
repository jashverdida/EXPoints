@extends('layouts.app')

@section('title', 'Dashboard - EXPoints')

@section('content')
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <h1>Welcome back, <span class="text-primary">{{ $userInfo->username }}</span>! 👋</h1>
        <p>Level {{ $userInfo->level }} • {{ $userInfo->exp_points }} EXP</p>
    </div>
    
    <!-- Create Post Section -->
    <div class="card mb-4 bg-dark text-white border-primary">
        <div class="card-body">
            <h5 class="card-title">Share Your Review</h5>
            <form id="createPostForm">
                <div class="mb-3">
                    <input type="text" class="form-control" name="game" placeholder="Game Name" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="title" placeholder="Review Title" required>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" name="content" rows="4" placeholder="Write your review..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Post Review
                </button>
            </form>
        </div>
    </div>
    
    <!-- Search Section -->
    @if($search)
    <div class="alert alert-info">
        Showing results for "{{ $search }}" (filtered by {{ $filter }})
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light ms-2">Clear</a>
    </div>
    @endif
    
    <!-- Posts Section -->
    <div id="postsContainer">
        @forelse($posts as $post)
        <div class="card-post mb-4 bg-dark text-white border" data-post-id="{{ $post->id }}" data-is-bookmarked="{{ $post->is_bookmarked ? 'true' : 'false' }}">
            <div class="post-header">
                <div class="row gap-3 align-items-start">
                    <div class="col-auto">
                        <img src="{{ $post->authorInfo->profile_picture ?? asset('assets/img/cat1.jpg') }}" 
                             alt="Profile" 
                             class="rounded-circle"
                             style="width: 50px; height: 50px; object-fit: cover;">
                    </div>
                    <div class="col">
                        <div class="badge bg-primary mb-2">{{ $post->game }}</div>
                        <h3 class="h5 mb-1">{{ $post->title }}</h3>
                        <div class="text-muted mb-2">@<span>{{ $post->username }}</span></div>
                        <p class="mb-2">{{ $post->content }}</p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> {{ $post->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                
                <!-- Post Menu -->
                <div class="post-menu">
                    @if($post->user_id === auth()->id())
                    <!-- Owner: Show edit/delete -->
                    <button class="btn btn-sm btn-link text-white more-btn">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="post-dropdown" style="display: none;">
                        <button class="dropdown-item edit-post"><i class="bi bi-pencil"></i> Edit</button>
                        <button class="dropdown-item delete-post"><i class="bi bi-trash"></i> Delete</button>
                    </div>
                    @else
                    <!-- Not owner: Show bookmark -->
                    <button class="btn btn-sm btn-link text-white bookmark-btn {{ $post->is_bookmarked ? 'bookmarked' : '' }}" 
                            data-post-id="{{ $post->id }}">
                        <i class="bi bi-bookmark-fill"></i>
                    </button>
                    @endif
                </div>
            </div>
            
            <!-- Actions -->
            <div class="actions mt-3">
                <button class="btn btn-sm btn-outline-light like-btn me-2" data-liked="{{ $post->is_liked ? 'true' : 'false' }}">
                    <i class="bi bi-star{{ $post->is_liked ? '-fill' : '' }}"></i>
                    <span class="like-count">{{ $post->likes_count }}</span>
                </button>
                <button class="btn btn-sm btn-outline-light comment-btn">
                    <i class="bi bi-chat-left-text"></i>
                    <span class="comment-count">{{ $post->comments_count }}</span>
                </button>
            </div>
            
            <!-- Comments Section (hidden by default) -->
            <div class="comments-section mt-3" style="display: none;">
                <div class="comments-header d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Comments</h6>
                    <button class="btn btn-sm btn-close btn-close-white close-comments"></button>
                </div>
                
                <div class="add-comment-form mb-3">
                    <textarea class="form-control comment-input mb-2" placeholder="Write a comment..." rows="2"></textarea>
                    <button class="btn btn-sm btn-primary btn-submit-comment">Post Comment</button>
                </div>
                
                <div class="comments-list">
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-arrow-repeat spin"></i> Loading comments...
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: rgba(255,255,255,0.3);"></i>
            <p class="mt-3 text-muted">No posts yet. Be the first to share a review!</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
// Create Post Form
document.getElementById('createPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/posts', {
            method: 'POST',
            headers: defaultHeaders,
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Post created successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to create post');
    }
});

// Post interactions (like, bookmark, comment)
document.querySelectorAll('.card-post').forEach(attachPostEventListeners);

function attachPostEventListeners(postElement) {
    const postId = postElement.getAttribute('data-post-id');
    
    // Like button
    const likeBtn = postElement.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', async function() {
            try {
                const response = await fetch(`/api/posts/${postId}/like`, {
                    method: 'POST',
                    headers: defaultHeaders
                });
                const data = await response.json();
                
                if (data.success) {
                    const icon = likeBtn.querySelector('i');
                    const count = likeBtn.querySelector('.like-count');
                    const isLiked = likeBtn.getAttribute('data-liked') === 'true';
                    
                    if (isLiked) {
                        likeBtn.setAttribute('data-liked', 'false');
                        icon.classList.remove('bi-star-fill');
                        icon.classList.add('bi-star');
                    } else {
                        likeBtn.setAttribute('data-liked', 'true');
                        icon.classList.remove('bi-star');
                        icon.classList.add('bi-star-fill');
                    }
                    count.textContent = data.like_count;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }
    
    // Bookmark button
    const bookmarkBtn = postElement.querySelector('.bookmark-btn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', async function() {
            try {
                const response = await fetch(`/api/posts/${postId}/bookmark`, {
                    method: 'POST',
                    headers: defaultHeaders
                });
                const data = await response.json();
                
                if (data.success) {
                    bookmarkBtn.classList.toggle('bookmarked');
                    const isBookmarked = postElement.getAttribute('data-is-bookmarked') === 'true';
                    postElement.setAttribute('data-is-bookmarked', !isBookmarked);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }
    
    // Comment button - toggle comments section
    const commentBtn = postElement.querySelector('.comment-btn');
    const commentsSection = postElement.querySelector('.comments-section');
    if (commentBtn && commentsSection) {
        commentBtn.addEventListener('click', function() {
            const isVisible = commentsSection.style.display !== 'none';
            if (isVisible) {
                commentsSection.style.display = 'none';
            } else {
                commentsSection.style.display = 'block';
                loadComments(postId, postElement);
            }
        });
    }
    
    // Submit comment
    const submitCommentBtn = postElement.querySelector('.btn-submit-comment');
    const commentInput = postElement.querySelector('.comment-input');
    if (submitCommentBtn && commentInput) {
        submitCommentBtn.addEventListener('click', async function() {
            const commentText = commentInput.value.trim();
            if (!commentText) return;
            
            try {
                const response = await fetch(`/api/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({ comment_text: commentText })
                });
                const data = await response.json();
                
                if (data.success) {
                    commentInput.value = '';
                    loadComments(postId, postElement);
                    
                    // Update comment count
                    const count = commentBtn.querySelector('.comment-count');
                    count.textContent = parseInt(count.textContent) + 1;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }
}

// Load comments for a post
async function loadComments(postId, postElement) {
    const commentsList = postElement.querySelector('.comments-list');
    commentsList.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-arrow-repeat spin"></i> Loading comments...</div>';
    
    try {
        const response = await fetch(`/api/posts/${postId}/comments`);
        const data = await response.json();
        
        if (data.success && data.comments.length > 0) {
            commentsList.innerHTML = '';
            data.comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment-item p-3 mb-2 border rounded';
                commentDiv.innerHTML = `
                    <div class="d-flex gap-2 mb-2">
                        <img src="${comment.author_info?.profile_picture || '/assets/img/cat1.jpg'}" 
                             class="rounded-circle" 
                             style="width: 32px; height: 32px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <strong>@${comment.username}</strong>
                            <p class="mb-1">${escapeHtml(comment.comment_text)}</p>
                            <small class="text-muted">${formatTimeAgo(comment.created_at)}</small>
                        </div>
                    </div>
                `;
                commentsList.appendChild(commentDiv);
            });
        } else {
            commentsList.innerHTML = '<p class="text-center text-muted">No comments yet. Be the first!</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        commentsList.innerHTML = '<p class="text-center text-danger">Failed to load comments</p>';
    }
}

// Helper functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + 'm ago';
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + 'h ago';
    const days = Math.floor(hours / 24);
    return days + 'd ago';
}
</script>
@endpush
