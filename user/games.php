<?php
require_once __DIR__ . '/../config/session.php';
startSecureSession();

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php?error=' . urlencode('Please login to continue'));
    exit();
}



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

// Get all unique games from posts with post counts
$gamesStmt = $db->prepare("
    SELECT 
        game,
        COUNT(*) as post_count,
        MAX(created_at) as last_post_date
    FROM posts
    WHERE game IS NOT NULL AND game != ''
    GROUP BY game
    ORDER BY post_count DESC
");
$gamesStmt->execute();
$gamesResult = $gamesStmt->get_result();
$games = $gamesResult->fetch_all(MYSQLI_ASSOC);
$gamesStmt->close();

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games - Browse by Game | +EXPoints</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/index.css">
    <style>
        /* Arcade Gaming Theme - Neon & Retro */
        body {
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Pixel Background */
        .pixel-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.15;
            background: 
                repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0, 255, 255, 0.1) 2px, rgba(0, 255, 255, 0.1) 4px),
                repeating-linear-gradient(90deg, transparent, transparent 2px, rgba(255, 0, 255, 0.1) 2px, rgba(255, 0, 255, 0.1) 4px);
            animation: pixelScroll 20s linear infinite;
        }

        @keyframes pixelScroll {
            0% { background-position: 0 0, 0 0; }
            100% { background-position: 0 40px, 40px 0; }
        }

        /* Floating Game Icons */
        .game-icon-float {
            position: fixed;
            font-size: 2.5rem;
            opacity: 0.1;
            animation: floatIcons 15s ease-in-out infinite;
            z-index: -1;
            pointer-events: none;
        }

        @keyframes floatIcons {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.1; }
            50% { transform: translateY(-30px) rotate(180deg); opacity: 0.2; }
        }

        /* Hero Section - Arcade Style */
        .games-hero {
            background: linear-gradient(135deg, 
                rgba(255, 0, 255, 0.15) 0%,
                rgba(0, 255, 255, 0.15) 50%,
                rgba(255, 0, 255, 0.15) 100%);
            border: 3px solid transparent;
            background-clip: padding-box;
            position: relative;
            border-radius: 1.5rem;
            padding: 3.5rem 2rem;
            margin-bottom: 2.5rem;
            text-align: center;
            box-shadow: 
                0 0 40px rgba(255, 0, 255, 0.3),
                0 0 80px rgba(0, 255, 255, 0.2),
                inset 0 0 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .games-hero::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                #ff00ff, #00ffff, #ff00ff, #00ffff);
            border-radius: 1.5rem;
            z-index: -1;
            animation: borderGlow 3s linear infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .games-hero h1 {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(90deg, #ff00ff, #00ffff, #ff00ff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 30px rgba(255, 0, 255, 0.5);
            animation: neonPulse 2s ease-in-out infinite;
            letter-spacing: 2px;
        }

        @keyframes neonPulse {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.3); }
        }

        .games-hero .hero-icon {
            font-size: 3rem;
            display: inline-block;
            animation: arcadeBounce 1s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(0, 255, 255, 0.8));
        }

        @keyframes arcadeBounce {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.1); }
        }

        .games-hero p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }

        /* Stats Bar */
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-bottom: 2.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(255, 0, 255, 0.1), rgba(0, 255, 255, 0.1));
            border: 2px solid rgba(0, 255, 255, 0.3);
            border-radius: 1rem;
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.2);
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ff00ff, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
            text-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Search Bar - Neon Style */
        .search-games {
            margin-bottom: 2.5rem;
            position: relative;
        }

        .search-games input {
            width: 100%;
            padding: 1.25rem 1.5rem 1.25rem 3.5rem;
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(0, 255, 255, 0.3);
            border-radius: 1rem;
            color: #fff;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.2);
        }

        .search-games input:focus {
            outline: none;
            border-color: rgba(0, 255, 255, 0.8);
            background: rgba(0, 0, 0, 0.6);
            box-shadow: 
                0 0 30px rgba(0, 255, 255, 0.5),
                0 0 60px rgba(255, 0, 255, 0.3);
        }

        .search-games input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .search-games::before {
            content: '🎮';
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            pointer-events: none;
            animation: controllerSpin 3s ease-in-out infinite;
        }

        @keyframes controllerSpin {
            0%, 100% { transform: translateY(-50%) rotate(0deg); }
            50% { transform: translateY(-50%) rotate(15deg); }
        }

        /* Game Cards - Enhanced Neon */
        .game-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(20, 0, 40, 0.6) 100%);
            border: 2px solid rgba(255, 0, 255, 0.3);
            border-radius: 1.25rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            display: block;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 4px 20px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(255, 0, 255, 0.2);
        }

        .game-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .game-card:hover::before {
            left: 100%;
        }

        .game-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(0, 255, 255, 0.8);
            box-shadow: 
                0 15px 40px rgba(0, 255, 255, 0.4),
                0 0 60px rgba(255, 0, 255, 0.4),
                inset 0 0 30px rgba(0, 255, 255, 0.1);
        }

        .game-card-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        }

        .game-card-title i {
            font-size: 2rem;
            background: linear-gradient(135deg, #ff00ff, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 10px rgba(0, 255, 255, 0.6));
        }

        .game-card-stats {
            display: flex;
            gap: 2rem;
            align-items: center;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .game-card-stats span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid rgba(0, 255, 255, 0.3);
            border-radius: 0.75rem;
        }

        .game-card-stats i {
            color: #00ffff;
            filter: drop-shadow(0 0 5px rgba(0, 255, 255, 0.6));
        }

        .game-card-stats strong {
            color: #ff00ff;
            text-shadow: 0 0 10px rgba(255, 0, 255, 0.6);
        }

        /* Popular Badge */
        .popular-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, #ff00ff, #ff0080);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.6);
            animation: popularPulse 2s ease-in-out infinite;
        }

        @keyframes popularPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 30px rgba(255, 0, 255, 0.8); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-state i {
            font-size: 5rem;
            background: linear-gradient(135deg, #ff00ff, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 20px rgba(0, 255, 255, 0.5));
            margin-bottom: 1.5rem;
            animation: emptyBounce 2s ease-in-out infinite;
        }

        @keyframes emptyBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .empty-state h3 {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .empty-state p {
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .games-hero h1 {
                font-size: 2rem;
            }

            .stats-bar {
                flex-direction: column;
                gap: 1.5rem;
            }

            .game-card-stats {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
        
        /* ================= MOBILE RESPONSIVE FIXES ================= */
        
        /* Mobile Fix for Games Page */
        @media (max-width: 768px) {
            /* Hero section */
            .games-hero {
                padding: 2rem 1rem;
                margin-bottom: 1.5rem;
            }
            
            .games-hero h1 {
                font-size: 1.8rem;
                letter-spacing: 1px;
            }
            
            .games-hero .hero-icon {
                font-size: 2.5rem;
            }
            
            .games-hero p {
                font-size: 1rem;
            }
            
            /* Stats bar */
            .stats-bar {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            
            .stat-value {
                font-size: 1.75rem;
            }
            
            .stat-label {
                font-size: 0.85rem;
            }
            
            /* Search bar */
            .search-games {
                margin-bottom: 1.5rem;
            }
            
            .search-games input {
                padding: 1rem 1.25rem 1rem 3rem;
                font-size: 1rem;
            }
            
            .search-games::before {
                font-size: 1.3rem;
                left: 1rem;
            }
            
            /* Game cards */
            .game-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .game-card-title {
                font-size: 1.1rem;
            }
            
            .game-card-stats {
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start;
                font-size: 0.85rem;
            }
            
            .popular-badge {
                font-size: 0.75rem;
                padding: 0.35rem 0.75rem;
            }
            
            /* Empty state */
            .empty-state i {
                font-size: 4rem;
            }
            
            .empty-state h3 {
                font-size: 1.5rem;
            }
            
            .empty-state p {
                font-size: 1rem;
            }
            
            /* Floating icons - reduce on mobile */
            .game-icon-float {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            /* Hero section - smaller */
            .games-hero {
                padding: 1.5rem 0.75rem;
                margin-bottom: 1rem;
            }
            
            .games-hero h1 {
                font-size: 1.5rem;
            }
            
            .games-hero .hero-icon {
                font-size: 2rem;
            }
            
            .games-hero p {
                font-size: 0.9rem;
            }
            
            /* Stats bar - compact */
            .stats-bar {
                padding: 0.75rem;
                gap: 0.75rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            /* Search bar */
            .search-games input {
                padding: 0.85rem 1rem 0.85rem 2.75rem;
                font-size: 0.95rem;
            }
            
            .search-games::before {
                font-size: 1.2rem;
                left: 0.85rem;
            }
            
            /* Game cards - compact */
            .game-card {
                padding: 1.25rem;
            }
            
            .game-card-title {
                font-size: 1rem;
            }
            
            .game-card-title i {
                font-size: 1.1rem;
            }
            
            .game-card-stats {
                font-size: 0.8rem;
            }
            
            .popular-badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.65rem;
            }
            
            /* Empty state */
            .empty-state i {
                font-size: 3.5rem;
            }
            
            .empty-state h3 {
                font-size: 1.3rem;
            }
            
            .empty-state p {
                font-size: 0.9rem;
            }
            
            /* Hide floating icons on very small screens */
            .game-icon-float {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Pixel Grid Background -->
    <div class="pixel-bg"></div>

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
        <!-- Hero Section -->
        <div class="games-hero">
            <div class="hero-icon">🎮</div>
            <h1>ARCADE LOBBY</h1>
            <p>Explore the ultimate gaming universe • Connect • Review • Discover</p>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-value" id="totalGames"><?php echo count($games); ?></span>
                <div class="stat-label">Games Available</div>
            </div>
            <div class="stat-item">
                <span class="stat-value" id="totalReviews">
                    <?php 
                    $totalReviews = array_sum(array_column($games, 'post_count'));
                    echo $totalReviews;
                    ?>
                </span>
                <div class="stat-label">Total Reviews</div>
            </div>
            <div class="stat-item">
                <span class="stat-value" id="activeGames">
                    <?php 
                    $activeGames = 0;
                    foreach ($games as $game) {
                        if (strtotime($game['last_post_date']) > strtotime('-7 days')) {
                            $activeGames++;
                        }
                    }
                    echo $activeGames;
                    ?>
                </span>
                <div class="stat-label">Active This Week</div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-games">
            <input type="text" id="gameSearch" placeholder="Search for your favorite game..." />
        </div>

        <!-- Games Grid -->
        <div id="gamesContainer">
            <?php if (empty($games)): ?>
                <div class="empty-state">
                    <i class="bi bi-controller"></i>
                    <h3>No Games Yet</h3>
                    <p>Be the first to post a review and start the gaming community!</p>
                </div>
            <?php else: ?>
                <?php 
                // Sort games by post count for popular badge
                $sortedGames = $games;
                usort($sortedGames, function($a, $b) {
                    return $b['post_count'] - $a['post_count'];
                });
                
                foreach ($sortedGames as $index => $game): 
                    $isPopular = $index < 3 && $game['post_count'] >= 5; // Top 3 with at least 5 posts
                ?>
                    <a href="game-posts.php?game=<?php echo urlencode($game['game']); ?>" 
                       class="game-card" 
                       data-game-name="<?php echo htmlspecialchars(strtolower($game['game'])); ?>">
                        <?php if ($isPopular): ?>
                            <div class="popular-badge">🔥 HOT</div>
                        <?php endif; ?>
                        <div class="game-card-title">
                            <i class="bi bi-joystick"></i>
                            <span><?php echo htmlspecialchars($game['game']); ?></span>
                        </div>
                        <div class="game-card-stats">
                            <span>
                                <i class="bi bi-file-text-fill"></i>
                                <strong><?php echo $game['post_count']; ?></strong> <?php echo $game['post_count'] == 1 ? 'review' : 'reviews'; ?>
                            </span>
                            <span>
                                <i class="bi bi-clock-history"></i>
                                <?php 
                                $daysDiff = floor((time() - strtotime($game['last_post_date'])) / (60 * 60 * 24));
                                if ($daysDiff == 0) {
                                    echo 'Active today';
                                } elseif ($daysDiff == 1) {
                                    echo 'Active yesterday';
                                } elseif ($daysDiff < 7) {
                                    echo 'Active ' . $daysDiff . ' days ago';
                                } else {
                                    echo 'Last: ' . date('M j, Y', strtotime($game['last_post_date']));
                                }
                                ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
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

    <script>
        // Create floating game icons
        function createFloatingIcons() {
            const icons = ['🎮', '🕹️', '👾', '🎯', '🏆', '⚔️', '🛡️', '💎', '⭐', '🎪'];
            const container = document.body;
            
            for (let i = 0; i < 15; i++) {
                const icon = document.createElement('div');
                icon.className = 'game-icon-float';
                icon.textContent = icons[Math.floor(Math.random() * icons.length)];
                icon.style.left = Math.random() * 100 + '%';
                icon.style.top = Math.random() * 100 + '%';
                icon.style.animationDelay = Math.random() * 5 + 's';
                icon.style.animationDuration = (10 + Math.random() * 10) + 's';
                container.appendChild(icon);
            }
        }
        
        // Initialize floating icons
        createFloatingIcons();

        // Search functionality with animation
        const gameSearch = document.getElementById('gameSearch');
        const gameCards = document.querySelectorAll('.game-card');

        gameSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            
            gameCards.forEach(card => {
                const gameName = card.dataset.gameName;
                if (gameName.includes(searchTerm)) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, visibleCount * 50);
                    visibleCount++;
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });

        // Logout functionality
        const logoutBtn = document.querySelector('.logout-btn-sidebar');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                sessionStorage.removeItem('welcomeShown');
                window.location.href = 'index.php';
            });
        }
    </script>
</body>
</html>
