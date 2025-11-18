<?php
session_start();

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php?error=' . urlencode('Please login to continue'));
    exit();
}

// Get game name from URL
$gameName = isset($_GET['game']) ? trim($_GET['game']) : '';

if (empty($gameName)) {
    header('Location: games.php');
    exit();
}

// Supabase-compatible database connection
require_once __DIR__ . '/../includes/db_helper.php';

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// Get user profile info
$userStmt = $db->prepare("SELECT username, profile_picture FROM user_info WHERE user_id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();
$userStmt->close();

$username = $userData['username'] ?? 'User';
$userProfilePicture = $userData['profile_picture'] ?? '../assets/img/cat1.jpg';

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($gameName); ?> - Posts | +EXPoints</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/index.css">
    <style>
        .game-header {
            background: linear-gradient(135deg, rgba(18, 34, 90, 0.95) 0%, rgba(11, 21, 55, 0.95) 100%);
            border: 2px solid rgba(56, 160, 255, 0.3);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .game-header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .game-header-title h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
        }
        
        .game-header-title i {
            font-size: 2.5rem;
            color: #38a0ff;
        }
        
        .game-header-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(56, 160, 255, 0.3);
            border-radius: 0.75rem;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: rgba(56, 160, 255, 0.2);
            border-color: rgba(56, 160, 255, 0.6);
            color: #fff;
            transform: translateX(-3px);
        }
        
        .game-stats {
            display: flex;
            gap: 2rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }
        
        .game-stats span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .game-stats i {
            color: #38a0ff;
        }
        
        .loading {
            text-align: center;
            padding: 3rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .loading i {
            font-size: 3rem;
            color: #38a0ff;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .no-posts {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .no-posts i {
            font-size: 4rem;
            color: rgba(56, 160, 255, 0.3);
            margin-bottom: 1rem;
        }
        
        .no-posts h3 {
            color: rgba(255, 255, 255, 0.8);
        }
    </style>
</head>
<body>
    <!-- Top bar -->
    <div class="container-xl mt-3">
        <header class="topbar">
            <a href="dashboard.php" class="lp-brand" aria-label="+EXPoints home">
                <img src="../assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
            </a>

            <form class="search" role="search">
                <input type="text" placeholder="Search for a Review, a Game, Anything" />
                <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
            </form>

            <div class="right">
                <button class="icon" title="Filter"><i class="bi bi-funnel"></i></button>
                <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
                <a href="profile.php" class="avatar-nav">
                    <img src="<?php echo htmlspecialchars($userProfilePicture); ?>" alt="Profile" class="avatar-img">
                </a>
            </div>
        </header>
    </div>

    <!-- Main Content -->
    <main class="container-xl py-4">
        <!-- Game Header -->
        <div class="game-header">
            <div class="game-header-title">
                <i class="bi bi-joystick"></i>
                <h1><?php echo htmlspecialchars($gameName); ?></h1>
            </div>
            <div class="game-header-nav">
                <a href="games.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    Back to Games
                </a>
                <div class="game-stats">
                    <span>
                        <i class="bi bi-file-text"></i>
                        <strong id="postCount">0</strong> posts
                    </span>
                </div>
            </div>
        </div>

    <!-- Posts Container -->
    <div id="postsContainer">
        <div class="loading">
            <i class="bi bi-arrow-repeat"></i>
            <p>Loading posts...</p>
        </div>
    </div>
</main>

<!-- Sidebar -->
<aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
        <div class="side-box">
            <button class="side-btn" onclick="window.location.href='dashboard.php'" title="Home"><i class="bi bi-house"></i></button>
            <button class="side-btn" onclick="window.location.href='bookmarks.php'" title="Bookmarks"><i class="bi bi-bookmark"></i></button>
            <button class="side-btn active" onclick="window.location.href='games.php'" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
            <button class="side-btn" onclick="window.location.href='popular.php'" title="Popular"><i class="bi bi-compass"></i></button>
            <button class="side-btn" onclick="window.location.href='newest.php'" title="Newest"><i class="bi bi-star-fill"></i></button>
            <button class="side-btn side-bottom logout-btn-sidebar" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
        </div>
    </div>
</aside>

<!-- Profile Hover Modal -->
<div id="profileHoverModal" style="display: none; position: fixed; z-index: 10000; background: linear-gradient(135deg, #1a0033 0%, #2d1b4e 100%); border: 2px solid rgba(124, 58, 237, 0.3); border-radius: 12px; padding: 1.5rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); min-width: 250px;">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
        <div class="avatar-lg" id="modalAvatar" style="position: relative; width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3px;">
            <img id="modalProfilePic" src="" alt="Profile" style="position: absolute; top: 3px; left: 3px; width: calc(100% - 6px); height: calc(100% - 6px); object-fit: cover; border-radius: 50%; z-index: 3;">
        </div>
        <div>
            <h4 id="modalUsername" style="margin: 0; color: white; font-size: 1.1rem; font-weight: 600;"></h4>
            <p id="modalLevel" style="margin: 0; color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;"></p>
        </div>
    </div>
    <div style="background: rgba(255, 255, 255, 0.05); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">EXP Points</span>
            <span id="modalExp" style="color: #a78bfa; font-weight: 600; font-size: 1rem;"></span>
        </div>
    </div>
    <button id="modalViewProfile" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; transition: transform 0.2s;">
        View Profile
    </button>
</div>

<style>
    .avatar-xs {
        position: relative;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .avatar-xs:hover {
        transform: scale(1.1);
    }
    
    .replies-container {
        margin-left: 2.5rem;
        margin-top: 1rem;
        padding-left: 1rem;
        border-left: 2px solid rgba(255, 255, 255, 0.1);
    }
    
    .reply-item {
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 8px;
    }
    
    .reply-input-container {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .comment-like-btn.liked {
        color: #fbbf24 !important;
    }
    
    .comment-like-btn.liked i {
        color: #fbbf24 !important;
    }
    
    /* Ensure buttons are clickable */
    .like-btn, .comment-btn, .bookmark-btn {
        cursor: pointer !important;
        pointer-events: auto !important;
        user-select: none;
    }
    
    .like-btn:hover, .comment-btn:hover, .bookmark-btn:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }
    
    .actions .a {
        cursor: pointer !important;
        pointer-events: auto !important;
    }
    
    /* Comment dropdown styling */
    .comment-dropdown .dropdown-item:hover {
        background: rgba(124, 58, 237, 0.2);
    }
    
    .comment-menu {
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .comment-item:hover .comment-menu,
    .reply-item:hover .comment-menu {
        opacity: 1;
    }
</style>

<script>
    // Initialize global variables
    const gameName = <?php echo json_encode($gameName); ?>;
    const currentUserId = <?php echo json_encode($userId); ?>;
    const postsContainer = document.getElementById('postsContainer');
    const postCountEl = document.getElementById('postCount');

    // Wait for DOMContentLoaded to ensure all functions from dashboard-posts.js are loaded
    document.addEventListener('DOMContentLoaded', function() {
        loadGamePosts();
        setupProfileHoverModal();
    });

    // Setup profile hover modal
    function setupProfileHoverModal() {
        const modal = document.getElementById('profileHoverModal');
        const modalUsername = document.getElementById('modalUsername');
        const modalLevel = document.getElementById('modalLevel');
        const modalExp = document.getElementById('modalExp');
        const modalProfilePic = document.getElementById('modalProfilePic');
        const modalViewProfile = document.getElementById('modalViewProfile');
        
        let hoverTimeout;
        let currentUserId;
        
        // Delegate event listeners for dynamically added avatars
        document.addEventListener('mouseenter', function(e) {
            if (!e.target) return;
            
            // Check if the element itself or closest parent has the user-profile-avatar class
            let avatar = null;
            if (e.target.classList && e.target.classList.contains('user-profile-avatar')) {
                avatar = e.target;
            } else if (e.target.parentElement && e.target.parentElement.classList && e.target.parentElement.classList.contains('user-profile-avatar')) {
                avatar = e.target.parentElement;
            }
            
            if (!avatar) return;
            
            clearTimeout(hoverTimeout);
            
            const username = avatar.dataset.username || avatar.getAttribute('data-username');
            const exp = avatar.dataset.exp || avatar.getAttribute('data-exp') || '0';
            const profilePicture = avatar.dataset.profilePicture || avatar.getAttribute('data-profile-picture');
            currentUserId = avatar.dataset.userId || avatar.getAttribute('data-user-id');
            
            // Calculate level from EXP
            const expPoints = parseInt(exp);
            const level = Math.floor(expPoints / 100) + 1;
            
            // Update modal content
            modalUsername.textContent = '@' + username;
            modalLevel.textContent = `Level ${level}`;
            modalExp.textContent = expPoints + ' XP';
            modalProfilePic.src = profilePicture || '../assets/img/cat1.jpg';
            
            // Position modal near avatar
            const rect = avatar.getBoundingClientRect();
            modal.style.left = (rect.right + 10) + 'px';
            modal.style.top = rect.top + 'px';
            modal.style.display = 'block';
        }, true);
        
        document.addEventListener('mouseleave', function(e) {
            if (!e.target) return;
            
            let avatar = null;
            if (e.target.classList && e.target.classList.contains('user-profile-avatar')) {
                avatar = e.target;
            } else if (e.target.parentElement && e.target.parentElement.classList && e.target.parentElement.classList.contains('user-profile-avatar')) {
                avatar = e.target.parentElement;
            }
            
            if (!avatar) return;
            
            hoverTimeout = setTimeout(() => {
                if (!modal.matches(':hover')) {
                    modal.style.display = 'none';
                }
            }, 300);
        }, true);
        
        modal.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
        });
        
        modal.addEventListener('mouseleave', function() {
            modal.style.display = 'none';
        });
        
        modalViewProfile.addEventListener('click', function() {
            if (currentUserId) {
                window.location.href = `view-profile.php?user_id=${currentUserId}`;
            }
        });
    }

    // Load posts for this specific game
    async function loadGamePosts() {
        try {
            const response = await fetch(`../api/posts.php?action=get_posts`);
            const data = await response.json();
            
            console.log('API Response:', data); // Debug log
            
            if (data.success && data.posts) {
                // Filter posts by game name
                const gamePosts = data.posts.filter(post => post.game === gameName);
                
                console.log('Filtered posts for', gameName, ':', gamePosts); // Debug log
                
                if (gamePosts.length === 0) {
                    postsContainer.innerHTML = `
                        <div class="no-posts">
                            <i class="bi bi-inbox"></i>
                            <h3>No posts yet for ${escapeHtml(gameName)}</h3>
                            <p>Be the first to share your thoughts about this game!</p>
                        </div>
                    `;
                    postCountEl.textContent = '0';
                } else {
                    postCountEl.textContent = gamePosts.length;
                    postsContainer.innerHTML = '';
                    
                    // Render each post using the dashboard system
                    gamePosts.forEach(post => {
                        renderPost(post);
                    });
                }
            } else {
                console.error('API Error:', data);
                postsContainer.innerHTML = `
                    <div class="no-posts">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h3>Error loading posts</h3>
                        <p>${escapeHtml(data.error || 'Unknown error occurred')}</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            postsContainer.innerHTML = `
                <div class="no-posts">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h3>Error loading posts</h3>
                    <p>${escapeHtml(error.message)}</p>
                </div>
            `;
        }
    }

    // Render a single post (compatible with dashboard-posts.js)
    function renderPost(post) {
        const likeIcon = post.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = post.user_liked ? 'liked' : '';
        const bookmarkIcon = post.user_bookmarked ? 'bi-bookmark-fill' : 'bi-bookmark';
        const bookmarkClass = post.user_bookmarked ? 'bookmarked' : '';
        const profilePicture = post.author_profile_picture || '../assets/img/cat1.jpg';
        const timestamp = timeAgo(post.created_at);
        
        const postHTML = `
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
                    <span class="a like-btn ${likeClass}" data-post-id="${post.id}" data-liked="${post.user_liked ? 'true' : 'false'}">
                        <i class="bi ${likeIcon}"></i><b>${post.like_count}</b>
                    </span>
                    <span class="a comment-btn" data-comments="${post.comment_count}">
                        <i class="bi bi-chat-left-text"></i><b>${post.comment_count}</b>
                    </span>
                </div>
                <div class="comments-section" style="display: none;">
                    <div class="comments-list"></div>
                    <div class="comment-input-container">
                        <input type="text" class="comment-input" placeholder="Write a comment..." />
                        <button class="comment-submit-btn">Post</button>
                    </div>
                </div>
            </article>
        `;
        
        postsContainer.insertAdjacentHTML('beforeend', postHTML);
        attachPostEventListeners(postsContainer.lastElementChild);
    }

    // Time ago function
    function timeAgo(dateString) {
        const now = new Date();
        const past = new Date(dateString);
        const seconds = Math.floor((now - past) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + 'm';
        if (seconds < 86400) return Math.floor(seconds / 3600) + 'h';
        if (seconds < 172800) return 'Yesterday';
        if (seconds < 604800) return Math.floor(seconds / 86400) + 'd';
        
        const month = past.toLocaleDateString('en-US', { month: 'short' });
        const day = past.getDate();
        return `${month} ${day}`;
    }

    // Attach event listeners to a post element
    function attachPostEventListeners(postElement) {
        const postId = postElement.getAttribute('data-post-id') || postElement.dataset.postId;
        console.log('Attaching event listeners to post:', postId);
        
        if (!postId) {
            console.error('Post element missing data-post-id attribute!', postElement);
            return;
        }
        
        // Like button
        const likeBtn = postElement.querySelector('.like-btn');
        if (likeBtn) {
            console.log('Found like button for post', postId);
            likeBtn.addEventListener('click', function(e) {
                console.log('Like button clicked!', this.getAttribute('data-post-id'));
                e.preventDefault();
                e.stopPropagation();
                toggleLike(this);
            });
        } else {
            console.error('Like button not found for post', postId);
        }
        
        // Comment button
        const commentBtn = postElement.querySelector('.comment-btn');
        if (commentBtn) {
            console.log('Found comment button for post', postId);
            commentBtn.addEventListener('click', function(e) {
                console.log('Comment button clicked!', postId);
                e.preventDefault();
                e.stopPropagation();
                toggleComments(postId, postElement);
            });
        } else {
            console.error('Comment button not found for post', postId);
        }
        
        // Bookmark button
        const bookmarkBtn = postElement.querySelector('.bookmark-btn');
        if (bookmarkBtn) {
            console.log('Found bookmark button for post', postId);
            bookmarkBtn.addEventListener('click', function(e) {
                console.log('Bookmark button clicked!', this.getAttribute('data-post-id'));
                e.preventDefault();
                e.stopPropagation();
                toggleBookmark(this);
            });
        } else {
            console.error('Bookmark button not found for post', postId);
        }
        
        // More menu
        const moreBtn = postElement.querySelector('.more');
        if (moreBtn) {
            moreBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling;
                document.querySelectorAll('.post-dropdown.show').forEach(d => {
                    if (d !== dropdown) d.classList.remove('show');
                });
                dropdown.classList.toggle('show');
            });
        }
        
        // Edit button
        const editBtn = postElement.querySelector('.edit-post');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                const postId = postElement.dataset.postId;
                editPost(postId, postElement);
            });
        }
        
        // Delete button
        const deleteBtn = postElement.querySelector('.delete-post');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const postId = postElement.dataset.postId;
                deletePost(postId, postElement);
            });
        }
        
        // Comment submit button
        const commentInput = postElement.querySelector('.comment-input');
        const commentSubmitBtn = postElement.querySelector('.comment-submit-btn');
        if (commentSubmitBtn && commentInput) {
            commentSubmitBtn.addEventListener('click', function() {
                submitComment(postElement);
            });
            
            commentInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    submitComment(postElement);
                }
            });
        }
    }

    // Helper functions from dashboard-posts.js
    function toggleLike(btn) {
        console.log('toggleLike called', btn);
        const postId = btn.getAttribute('data-post-id') || btn.dataset.postId;
        const isLiked = (btn.getAttribute('data-liked') || btn.dataset.liked) === 'true';
        
        console.log('Post ID:', postId, 'Is Liked:', isLiked);
        
        if (!postId) {
            console.error('No post ID found!');
            return;
        }
        
        fetch('../api/posts.php?action=like', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: parseInt(postId) })
        })
        .then(response => {
            console.log('Like response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Like response data:', data);
            if (data.success) {
                const icon = btn.querySelector('i');
                const count = btn.querySelector('b');
                
                if (isLiked) {
                    icon.classList.remove('bi-star-fill');
                    icon.classList.add('bi-star');
                    btn.classList.remove('liked');
                    btn.setAttribute('data-liked', 'false');
                    count.textContent = parseInt(count.textContent) - 1;
                } else {
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill');
                    btn.classList.add('liked');
                    btn.setAttribute('data-liked', 'true');
                    count.textContent = parseInt(count.textContent) + 1;
                }
                console.log('Like toggled successfully');
            } else {
                console.error('Like failed:', data.error);
                alert('Failed to like post: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Like error:', error);
            alert('Error liking post: ' + error.message);
        });
    }

    function toggleBookmark(btn) {
        console.log('toggleBookmark called', btn);
        const postId = btn.getAttribute('data-post-id') || btn.dataset.postId;
        console.log('Bookmark Post ID:', postId);
        
        if (!postId) {
            console.error('No post ID found!');
            return;
        }
        
        fetch('../api/posts.php?action=bookmark', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: parseInt(postId) })
        })
        .then(response => {
            console.log('Bookmark response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Bookmark response data:', data);
            if (data.success) {
                const icon = btn.querySelector('i');
                if (btn.classList.contains('bookmarked')) {
                    icon.classList.remove('bi-bookmark-fill');
                    icon.classList.add('bi-bookmark');
                    btn.classList.remove('bookmarked');
                } else {
                    icon.classList.remove('bi-bookmark');
                    icon.classList.add('bi-bookmark-fill');
                    btn.classList.add('bookmarked');
                }
                console.log('Bookmark toggled successfully');
            } else {
                console.error('Bookmark failed:', data.error);
                alert('Failed to bookmark post: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Bookmark error:', error);
            alert('Error bookmarking post: ' + error.message);
        });
    }

    function toggleComments(postId, postElement) {
        console.log('toggleComments called', postId, postElement);
        
        if (!postId) {
            postId = postElement.getAttribute('data-post-id') || postElement.dataset.postId;
        }
        
        if (!postId) {
            console.error('No post ID found!');
            return;
        }
        
        const commentsSection = postElement.querySelector('.comments-section');
        const commentsList = postElement.querySelector('.comments-list');
        
        if (commentsSection.style.display === 'none' || !commentsSection.style.display) {
            commentsSection.style.display = 'block';
            loadComments(postId, commentsList);
            console.log('Comments section opened');
        } else {
            commentsSection.style.display = 'none';
            console.log('Comments section closed');
        }
    }

    function loadComments(postId, commentsList) {
        fetch(`../api/posts.php?action=get_comments&post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    commentsList.innerHTML = '';
                    data.comments.forEach(comment => {
                        const commentHTML = createCommentHTML(comment, postId);
                        commentsList.insertAdjacentHTML('beforeend', commentHTML);
                    });
                    
                    // Attach event listeners to all comment elements
                    attachCommentEventListeners(commentsList);
                }
            });
    }

    function createCommentHTML(comment, postId) {
        const profilePicture = comment.commenter_profile_picture || '../assets/img/cat1.jpg';
        const likeIcon = comment.user_liked ? 'bi-star-fill' : 'bi-star';
        const likeClass = comment.user_liked ? 'liked' : '';
        const timestamp = timeAgo(comment.created_at);
        const isOwner = comment.user_id == currentUserId;
        
        let repliesHTML = '';
        if (comment.replies && comment.replies.length > 0) {
            repliesHTML = '<div class="replies-container">';
            comment.replies.forEach(reply => {
                const replyPicture = reply.commenter_profile_picture || '../assets/img/cat1.jpg';
                const replyTime = timeAgo(reply.created_at);
                const isReplyOwner = reply.user_id == currentUserId;
                
                repliesHTML += `
                    <div class="reply-item" data-reply-id="${reply.id}" data-user-id="${reply.user_id}">
                        <div class="row g-2 align-items-start">
                            <div class="col-auto">
                                <div class="avatar-xs user-profile-avatar" 
                                     data-user-id="${reply.user_id}" 
                                     data-username="${escapeHtml(reply.username)}"
                                     data-profile-picture="${escapeHtml(replyPicture)}"
                                     data-exp="${reply.exp_points || 0}">
                                    <img src="${escapeHtml(replyPicture)}" alt="Profile" style="position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; width: calc(100% - 4px); height: calc(100% - 4px); object-fit: cover; border-radius: 50%; z-index: 3;">
                                </div>
                            </div>
                            <div class="col" style="position: relative;">
                                <div class="comment-author">@${escapeHtml(reply.username)}</div>
                                <div class="reply-text">${escapeHtml(reply.comment)}</div>
                                <span class="comment-time" style="color: rgba(255, 255, 255, 0.4); font-size: 0.75rem;">${replyTime}</span>
                                ${isReplyOwner ? `
                                <div class="comment-menu" style="position: absolute; top: 0; right: 0;">
                                    <button class="icon more-reply" style="border: 0; background: transparent; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0.25rem;">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="comment-dropdown" style="display: none; position: absolute; right: 0; top: 100%; background: #1a0033; border: 1px solid rgba(124, 58, 237, 0.3); border-radius: 8px; padding: 0.5rem; min-width: 120px; z-index: 1000; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">
                                        <button class="dropdown-item edit-reply" style="width: 100%; text-align: left; padding: 0.5rem; border: none; background: transparent; color: white; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="dropdown-item delete-reply" style="width: 100%; text-align: left; padding: 0.5rem; border: none; background: transparent; color: #ff4444; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            repliesHTML += '</div>';
        }
        
        return `
            <div class="comment-item" data-comment-id="${comment.id}" data-post-id="${postId}" data-user-id="${comment.user_id}">
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
                        <div class="comment-actions" style="display: flex; gap: 1rem; margin-top: 0.5rem; font-size: 0.875rem; align-items: center;">
                            <button class="comment-like-btn ${likeClass}" data-comment-id="${comment.id}" data-liked="${comment.user_liked || false}" style="background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="bi ${likeIcon}"></i>
                                <span class="comment-like-count">${comment.like_count || 0}</span>
                            </button>
                            <button class="reply-btn" data-comment-id="${comment.id}" style="background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0; font-size: 0.875rem;">
                                <i class="bi bi-reply"></i> Reply
                            </button>
                            <span class="comment-time" style="color: rgba(255, 255, 255, 0.4);">${timestamp}</span>
                        </div>
                        ${isOwner ? `
                        <div class="comment-menu" style="position: absolute; top: 0; right: 0;">
                            <button class="icon more-comment" style="border: 0; background: transparent; color: rgba(255, 255, 255, 0.6); cursor: pointer; padding: 0.25rem;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="comment-dropdown" style="display: none; position: absolute; right: 0; top: 100%; background: #1a0033; border: 1px solid rgba(124, 58, 237, 0.3); border-radius: 8px; padding: 0.5rem; min-width: 120px; z-index: 1000; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">
                                <button class="dropdown-item edit-comment" style="width: 100%; text-align: left; padding: 0.5rem; border: none; background: transparent; color: white; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="dropdown-item delete-comment" style="width: 100%; text-align: left; padding: 0.5rem; border: none; background: transparent; color: #ff4444; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        ` : ''}
                        ${repliesHTML}
                        <div class="reply-input-container" style="display: none; margin-top: 0.75rem;">
                            <input type="text" class="reply-input" placeholder="Write a reply..." style="flex: 1; padding: 0.5rem; border-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.05); color: white;" />
                            <button class="reply-submit-btn" style="padding: 0.5rem 1rem; background: #7c3aed; border: none; border-radius: 4px; color: white; cursor: pointer; margin-left: 0.5rem;">Reply</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function editPost(postId, postElement) {
        // Placeholder for edit functionality
        alert('Edit functionality coming soon!');
    }

    function deletePost(postId, postElement) {
        if (confirm('Are you sure you want to delete this post?')) {
            fetch('../api/posts.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    postElement.remove();
                    loadGamePosts(); // Reload to update count
                }
            });
        }
    }

    // Submit a new comment
    function submitComment(postElement) {
        console.log('submitComment called', postElement);
        const postId = postElement.getAttribute('data-post-id') || postElement.dataset.postId;
        const commentInput = postElement.querySelector('.comment-input');
        const commentText = commentInput.value.trim();
        
        console.log('Post ID:', postId, 'Comment:', commentText);
        
        if (!postId) {
            console.error('No post ID found!');
            alert('Error: Post ID not found');
            return;
        }
        
        if (!commentText) {
            console.log('Comment is empty, not submitting');
            return;
        }
        
        const requestData = { 
            post_id: parseInt(postId), 
            comment: commentText 
        };
        
        console.log('Sending comment data:', requestData);
        
        fetch('../api/posts.php?action=add_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Comment response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Comment response data:', data);
            if (data.success) {
                commentInput.value = '';
                const commentsList = postElement.querySelector('.comments-list');
                loadComments(postId, commentsList);
                
                // Update comment count
                const commentBtn = postElement.querySelector('.comment-btn b');
                if (commentBtn) {
                    const currentCount = parseInt(commentBtn.textContent);
                    commentBtn.textContent = currentCount + 1;
                }
                console.log('Comment submitted successfully');
            } else {
                console.error('Comment submission failed:', data.error);
                alert('Failed to post comment: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Comment submission error:', error);
            alert('Error posting comment: ' + error.message);
        });
    }

    // Attach event listeners to comment elements
    function attachCommentEventListeners(commentsList) {
        // Comment like buttons
        commentsList.querySelectorAll('.comment-like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                toggleCommentLike(this);
            });
        });
        
        // Reply buttons
        commentsList.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const commentItem = this.closest('.comment-item');
                const replyContainer = commentItem.querySelector('.reply-input-container');
                
                // Toggle reply input visibility
                if (replyContainer.style.display === 'none' || !replyContainer.style.display) {
                    replyContainer.style.display = 'flex';
                    replyContainer.querySelector('.reply-input').focus();
                } else {
                    replyContainer.style.display = 'none';
                }
            });
        });
        
        // Reply submit buttons
        commentsList.querySelectorAll('.reply-submit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const commentItem = this.closest('.comment-item');
                submitReply(commentItem);
            });
        });
        
        // Reply input enter key
        commentsList.querySelectorAll('.reply-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const commentItem = this.closest('.comment-item');
                    submitReply(commentItem);
                }
            });
        });
        
        // Comment menu dropdowns
        commentsList.querySelectorAll('.more-comment').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling;
                document.querySelectorAll('.comment-dropdown').forEach(d => {
                    if (d !== dropdown) d.style.display = 'none';
                });
                dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            });
        });
        
        // Reply menu dropdowns
        commentsList.querySelectorAll('.more-reply').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling;
                document.querySelectorAll('.comment-dropdown').forEach(d => {
                    if (d !== dropdown) d.style.display = 'none';
                });
                dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            });
        });
        
        // Edit comment buttons
        commentsList.querySelectorAll('.edit-comment').forEach(btn => {
            btn.addEventListener('click', function() {
                const commentItem = this.closest('.comment-item');
                editComment(commentItem);
            });
        });
        
        // Delete comment buttons
        commentsList.querySelectorAll('.delete-comment').forEach(btn => {
            btn.addEventListener('click', function() {
                const commentItem = this.closest('.comment-item');
                deleteComment(commentItem);
            });
        });
        
        // Edit reply buttons
        commentsList.querySelectorAll('.edit-reply').forEach(btn => {
            btn.addEventListener('click', function() {
                const replyItem = this.closest('.reply-item');
                editReply(replyItem);
            });
        });
        
        // Delete reply buttons
        commentsList.querySelectorAll('.delete-reply').forEach(btn => {
            btn.addEventListener('click', function() {
                const replyItem = this.closest('.reply-item');
                deleteReply(replyItem);
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.comment-menu')) {
                document.querySelectorAll('.comment-dropdown').forEach(d => d.style.display = 'none');
            }
        });
    }

    // Toggle comment like
    function toggleCommentLike(btn) {
        const commentId = btn.dataset.commentId;
        const isLiked = btn.dataset.liked === 'true';
        
        fetch('../api/posts.php?action=like_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const icon = btn.querySelector('i');
                const count = btn.querySelector('.comment-like-count');
                
                if (isLiked) {
                    icon.classList.remove('bi-star-fill');
                    icon.classList.add('bi-star');
                    btn.classList.remove('liked');
                    btn.dataset.liked = 'false';
                    count.textContent = parseInt(count.textContent) - 1;
                } else {
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill');
                    btn.classList.add('liked');
                    btn.dataset.liked = 'true';
                    count.textContent = parseInt(count.textContent) + 1;
                }
            }
        });
    }

    // Submit a reply to a comment
    function submitReply(commentItem) {
        const commentId = commentItem.dataset.commentId;
        const postId = commentItem.dataset.postId;
        const replyInput = commentItem.querySelector('.reply-input');
        const replyText = replyInput.value.trim();
        
        if (!replyText) return;
        
        fetch('../api/posts.php?action=add_reply', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                comment_id: commentId,
                post_id: postId,
                comment: replyText 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                replyInput.value = '';
                const replyContainer = commentItem.querySelector('.reply-input-container');
                replyContainer.style.display = 'none';
                
                // Reload comments to show the new reply
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                const commentsList = postElement.querySelector('.comments-list');
                loadComments(postId, commentsList);
            }
        });
    }

    // Edit comment
    function editComment(commentItem) {
        const commentId = commentItem.getAttribute('data-comment-id');
        const commentTextEl = commentItem.querySelector('.comment-text');
        const currentText = commentTextEl.textContent;
        
        const newText = prompt('Edit your comment:', currentText);
        if (newText === null || newText.trim() === '' || newText === currentText) {
            return;
        }
        
        fetch('../api/posts.php?action=update_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                comment_id: parseInt(commentId), 
                comment: newText.trim() 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                commentTextEl.textContent = newText.trim();
                document.querySelectorAll('.comment-dropdown').forEach(d => d.style.display = 'none');
            } else {
                alert('Failed to edit comment: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error editing comment: ' + error.message);
        });
    }

    // Delete comment
    function deleteComment(commentItem) {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }
        
        const commentId = commentItem.getAttribute('data-comment-id');
        const postId = commentItem.getAttribute('data-post-id');
        
        fetch('../api/posts.php?action=delete_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: parseInt(commentId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload comments to refresh the list
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                const commentsList = postElement.querySelector('.comments-list');
                loadComments(postId, commentsList);
                
                // Update comment count
                const commentBtn = postElement.querySelector('.comment-btn b');
                if (commentBtn) {
                    const currentCount = parseInt(commentBtn.textContent);
                    commentBtn.textContent = Math.max(0, currentCount - 1);
                }
            } else {
                alert('Failed to delete comment: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error deleting comment: ' + error.message);
        });
    }

    // Edit reply
    function editReply(replyItem) {
        const replyId = replyItem.getAttribute('data-reply-id');
        const replyTextEl = replyItem.querySelector('.reply-text');
        const currentText = replyTextEl.textContent;
        
        const newText = prompt('Edit your reply:', currentText);
        if (newText === null || newText.trim() === '' || newText === currentText) {
            return;
        }
        
        fetch('../api/posts.php?action=update_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                comment_id: parseInt(replyId), 
                comment: newText.trim() 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                replyTextEl.textContent = newText.trim();
                document.querySelectorAll('.comment-dropdown').forEach(d => d.style.display = 'none');
            } else {
                alert('Failed to edit reply: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error editing reply: ' + error.message);
        });
    }

    // Delete reply
    function deleteReply(replyItem) {
        if (!confirm('Are you sure you want to delete this reply?')) {
            return;
        }
        
        const replyId = replyItem.getAttribute('data-reply-id');
        const commentItem = replyItem.closest('.comment-item');
        const postId = commentItem.getAttribute('data-post-id');
        
        fetch('../api/posts.php?action=delete_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: parseInt(replyId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload comments to refresh the list
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                const commentsList = postElement.querySelector('.comments-list');
                loadComments(postId, commentsList);
            } else {
                alert('Failed to delete reply: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error deleting reply: ' + error.message);
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Logout functionality
    const logoutBtn = document.querySelector('.logout-btn-sidebar');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            sessionStorage.removeItem('welcomeShown');
            window.location.href = 'index.php';
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.post-menu')) {
            document.querySelectorAll('.post-dropdown').forEach(d => d.classList.remove('show'));
        }
    });
</script>
</body>
</html>