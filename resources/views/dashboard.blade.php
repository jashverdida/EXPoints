@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Post a Review Form -->
<section class="card-post-form">
    <div class="row gap-3 align-items-start">
        <div class="col-auto">
            <div class="avatar-us" style="background-image: url('{{ $userProfilePicture }}');"></div>
        </div>
        <div class="col">
            <h3 class="form-title mb-3">Post a Review</h3>
            <form id="postForm" class="post-form" method="POST" action="{{ route('posts.store') }}">
                @csrf
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
                <div class="form-actions">
                    <button type="button" id="cancelPost" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-post">Post Review</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Reviews Feed -->
<section class="reviews-feed mt-4">
    <h3 class="feed-title">Recent Reviews</h3>
    
    @forelse($posts as $post)
    <article class="review-card mb-4" data-post-id="{{ $post['id'] }}">
        <div class="review-header">
            <a href="{{ route('profile.view', $post['username']) }}" class="user-info">
                <img src="{{ $post['profile_picture'] }}" alt="{{ $post['username'] }}" class="user-avatar">
                <div class="user-details">
                    <span class="username">{{ $post['username'] }}</span>
                    <span class="user-level">Level {{ floor(($post['exp_points'] ?? 0) / 10) + 1 }}</span>
                </div>
            </a>
            <span class="post-time">{{ \Carbon\Carbon::parse($post['created_at'])->diffForHumans() }}</span>
        </div>
        
        <div class="review-content">
            <span class="game-badge">{{ $post['game'] }}</span>
            <h4 class="review-title">{{ $post['title'] }}</h4>
            <p class="review-text">{{ $post['content'] }}</p>
        </div>
        
        <div class="review-actions">
            <button class="action-btn like-btn {{ $post['user_liked'] ? 'liked' : '' }}" data-post-id="{{ $post['id'] }}">
                <i class="bi bi-star{{ $post['user_liked'] ? '-fill' : '' }}"></i>
                <span class="like-count">{{ $post['likes'] }}</span>
            </button>
            <button class="action-btn comment-btn" data-post-id="{{ $post['id'] }}">
                <i class="bi bi-chat"></i>
                <span>{{ $post['comments'] }}</span>
            </button>
            <button class="action-btn bookmark-btn" data-post-id="{{ $post['id'] }}">
                <i class="bi bi-bookmark"></i>
            </button>
            <button class="action-btn share-btn">
                <i class="bi bi-share"></i>
            </button>
        </div>
    </article>
    @empty
    <div class="no-posts text-center py-5">
        <i class="bi bi-newspaper" style="font-size: 3rem; color: #ccc;"></i>
        <p class="mt-3 text-muted">No reviews yet. Be the first to post!</p>
    </div>
    @endforelse
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Like button functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            try {
                const response = await fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.classList.toggle('liked', data.liked);
                    this.querySelector('i').className = data.liked ? 'bi bi-star-fill' : 'bi bi-star';
                    this.querySelector('.like-count').textContent = data.count;
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
            try {
                const response = await fetch(`/posts/${postId}/bookmark`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.classList.toggle('bookmarked', data.bookmarked);
                    this.querySelector('i').className = data.bookmarked ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
});
</script>
@endpush
