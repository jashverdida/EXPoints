<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Exception;

class DashboardController extends Controller
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Normalize profile picture path from database
     * Converts "..\assets\img\..." to "/assets/img/..."
     */
    protected function normalizeProfilePicture(?string $path): string
    {
        $default = '/assets/img/cat1.jpg';

        if (empty($path)) {
            return $default;
        }

        // Convert backslashes to forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove leading ".." or "../"
        $path = preg_replace('/^\.\.\//', '', $path);

        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return $path;
    }

    /**
     * Show the dashboard
     */
    public function index(Request $request)
    {
        $userId = session('user_id');
        $username = session('username', 'User');
        $userEmail = session('user_email', '');

        $posts = [];
        $userProfilePicture = '/assets/img/cat1.jpg';

        try {
            // Get user profile picture
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            if ($userInfo) {
                $userProfilePicture = $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null);
                // Update username from DB (in case it changed)
                $username = $userInfo['username'] ?? $username;
                session(['username' => $username]);
            }

            // Handle search
            $searchQuery = $request->input('search', '');
            $searchFilter = $request->input('filter', 'title');

            // Fetch posts from Supabase
            $postsData = $this->supabase->select(
                'posts',
                '*',
                ['hidden' => 0],
                ['order' => 'created_at.desc', 'limit' => 50]
            );

            // Get user info for each post author
            foreach ($postsData as $post) {
                $authorInfo = $this->supabase->findBy('user_info', 'username', $post['username']);

                // Debug: Log author lookup
                \Log::info("Author lookup for '{$post['username']}'", [
                    'found' => $authorInfo !== null,
                    'profile_picture_raw' => $authorInfo ? ($authorInfo['profile_picture'] ?? 'NULL') : 'AUTHOR_NOT_FOUND'
                ]);

                // Get like count
                $likeCount = $this->supabase->count('post_likes', ['post_id' => $post['id']]);

                // Get comment count
                $commentCount = $this->supabase->count('post_comments', ['post_id' => $post['id']]);

                // Check if current user liked this post
                $userLiked = false;
                if ($userId) {
                    $userLike = $this->supabase->select('post_likes', 'id', [
                        'post_id' => $post['id'],
                        'user_id' => $userId
                    ], ['limit' => 1]);
                    $userLiked = !empty($userLike);
                }

                // Handle null authorInfo safely - check if authorInfo exists first
                $rawProfilePic = $authorInfo ? ($authorInfo['profile_picture'] ?? null) : null;
                $profilePic = $this->normalizeProfilePicture($rawProfilePic);
                $expPoints = $authorInfo ? ($authorInfo['exp_points'] ?? 0) : 0;

                // Debug: Log normalized path
                \Log::info("Profile picture for '{$post['username']}'", [
                    'raw' => $rawProfilePic,
                    'normalized' => $profilePic
                ]);

                $posts[] = [
                    'id' => $post['id'],
                    'game' => $post['game'] ?? 'Unknown Game',
                    'title' => $post['title'] ?? '',
                    'content' => $post['content'] ?? '',
                    'username' => $post['username'],
                    'user_email' => $post['user_email'] ?? '',
                    'profile_picture' => $profilePic,
                    'exp_points' => $expPoints,
                    'likes' => $likeCount,
                    'comments' => $commentCount,
                    'user_liked' => $userLiked,
                    'created_at' => $post['created_at'],
                ];
            }

            // Apply search filter if needed
            if (!empty($searchQuery)) {
                $posts = array_filter($posts, function($post) use ($searchQuery, $searchFilter) {
                    $searchLower = strtolower($searchQuery);
                    return match($searchFilter) {
                        'title' => str_contains(strtolower($post['title']), $searchLower),
                        'content' => str_contains(strtolower($post['content']), $searchLower),
                        'username' => str_contains(strtolower($post['username']), $searchLower),
                        'game' => str_contains(strtolower($post['game']), $searchLower),
                        default => str_contains(strtolower($post['title']), $searchLower),
                    };
                });
                $posts = array_values($posts);
            }

        } catch (Exception $e) {
            \Log::error("Dashboard error: " . $e->getMessage());
        }

        return view('dashboard', [
            'username' => $username,
            'userEmail' => $userEmail,
            'userProfilePicture' => $userProfilePicture,
            'posts' => $posts,
            'searchQuery' => $searchQuery ?? '',
            'searchFilter' => $searchFilter ?? 'title',
        ]);
    }

    /**
     * Show newest posts
     */
    public function newest(Request $request)
    {
        $userId = session('user_id');
        $username = session('username', 'User');
        $userProfilePicture = '/assets/img/cat1.jpg';

        $posts = [];

        try {
            // Get user profile picture
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            if ($userInfo) {
                $userProfilePicture = $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null);
            }

            // Fetch posts ordered by newest (created_at DESC) - limit to 20 for performance
            $postsData = $this->supabase->select(
                'posts',
                '*',
                ['hidden' => 0],
                ['order' => 'created_at.desc', 'limit' => 20]
            );

            // Cache user info lookups to avoid duplicate calls
            $userCache = [];

            // Get user info and counts for each post
            foreach ($postsData as $post) {
                $authorUsername = $post['username'];

                // Check cache first
                if (!isset($userCache[$authorUsername])) {
                    $userCache[$authorUsername] = $this->supabase->findBy('user_info', 'username', $authorUsername);
                }
                $authorInfo = $userCache[$authorUsername];

                // Handle null authorInfo safely - check if authorInfo exists first
                $profilePic = $this->normalizeProfilePicture(
                    $authorInfo ? ($authorInfo['profile_picture'] ?? null) : null
                );

                $posts[] = [
                    'id' => $post['id'],
                    'game' => $post['game'] ?? 'Unknown Game',
                    'title' => $post['title'] ?? '',
                    'content' => $post['content'] ?? '',
                    'username' => $authorUsername,
                    'profile_picture' => $profilePic,
                    'likes' => $post['likes'] ?? 0,
                    'comments' => $post['comments'] ?? 0,
                    'created_at' => $post['created_at'],
                ];
            }

        } catch (Exception $e) {
            \Log::error("Newest posts error: " . $e->getMessage());
        }

        return view('newest', [
            'username' => $username,
            'userProfilePicture' => $userProfilePicture,
            'posts' => $posts,
        ]);
    }

    /**
     * Show popular posts
     */
    public function popular(Request $request)
    {
        $userId = session('user_id');
        $username = session('username', 'User');
        $userProfilePicture = '/assets/img/cat1.jpg';

        $posts = [];

        try {
            // Get user profile picture
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            if ($userInfo) {
                $userProfilePicture = $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null);
            }

            // Fetch posts - limit to 30 for performance
            $postsData = $this->supabase->select(
                'posts',
                '*',
                ['hidden' => 0],
                ['limit' => 30]
            );

            // Cache user info lookups to avoid duplicate calls
            $userCache = [];

            // Get user info for each post
            foreach ($postsData as $post) {
                $authorUsername = $post['username'];

                // Check cache first
                if (!isset($userCache[$authorUsername])) {
                    $userCache[$authorUsername] = $this->supabase->findBy('user_info', 'username', $authorUsername);
                }
                $authorInfo = $userCache[$authorUsername];

                // Handle null authorInfo safely - check if authorInfo exists first
                $profilePic = $this->normalizeProfilePicture(
                    $authorInfo ? ($authorInfo['profile_picture'] ?? null) : null
                );
                $expPoints = $authorInfo ? ($authorInfo['exp_points'] ?? 0) : 0;

                $posts[] = [
                    'id' => $post['id'],
                    'game' => $post['game'] ?? 'Unknown Game',
                    'title' => $post['title'] ?? '',
                    'content' => $post['content'] ?? '',
                    'username' => $authorUsername,
                    'profile_picture' => $profilePic,
                    'exp_points' => $expPoints,
                    'likes' => $post['likes'] ?? 0,
                    'comments' => $post['comments'] ?? 0,
                    'created_at' => $post['created_at'],
                ];
            }

            // Sort by likes (most popular)
            usort($posts, fn($a, $b) => $b['likes'] - $a['likes']);
            $posts = array_slice($posts, 0, 20);

        } catch (Exception $e) {
            \Log::error("Popular posts error: " . $e->getMessage());
        }

        return view('popular', [
            'username' => $username,
            'userProfilePicture' => $userProfilePicture,
            'posts' => $posts,
        ]);
    }

    /**
     * Show games page
     */
    public function games(Request $request)
    {
        $userId = session('user_id');
        $username = session('username', 'User');
        $userProfilePicture = '/assets/img/cat1.jpg';

        $games = [];

        try {
            // Get user profile picture
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            if ($userInfo) {
                $userProfilePicture = $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null);
            }

            // Fetch all posts to extract unique games
            $postsData = $this->supabase->select(
                'posts',
                'game',
                ['hidden' => 0],
                ['limit' => 1000]
            );

            // Count reviews per game
            $gameCounts = [];
            foreach ($postsData as $post) {
                $gameName = $post['game'] ?? 'Unknown Game';
                if (!isset($gameCounts[$gameName])) {
                    $gameCounts[$gameName] = 0;
                }
                $gameCounts[$gameName]++;
            }

            // Sort by review count (most reviewed first)
            arsort($gameCounts);

            // Build games array
            foreach ($gameCounts as $gameName => $count) {
                $games[] = [
                    'name' => $gameName,
                    'review_count' => $count,
                ];
            }

        } catch (Exception $e) {
            \Log::error("Games page error: " . $e->getMessage());
        }

        return view('games', [
            'username' => $username,
            'userProfilePicture' => $userProfilePicture,
            'games' => $games,
            'totalGames' => count($games),
            'totalReviews' => array_sum(array_column($games, 'review_count')),
        ]);
    }

    /**
     * Show bookmarks
     */
    public function bookmarks(Request $request)
    {
        $userId = session('user_id');
        $username = session('username', 'User');
        $userProfilePicture = '/assets/img/cat1.jpg';

        $posts = [];

        try {
            // Get user profile picture
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            if ($userInfo) {
                $userProfilePicture = $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null);
            }

            // Get bookmarked post IDs
            $bookmarks = $this->supabase->select('post_bookmarks', 'post_id', ['user_id' => $userId]);

            foreach ($bookmarks as $bookmark) {
                $post = $this->supabase->find('posts', $bookmark['post_id']);
                if ($post && empty($post['hidden'])) {
                    $authorInfo = $this->supabase->findBy('user_info', 'username', $post['username']);
                    $likeCount = $this->supabase->count('post_likes', ['post_id' => $post['id']]);
                    $commentCount = $this->supabase->count('post_comments', ['post_id' => $post['id']]);

                    // Handle null authorInfo safely - check if authorInfo exists first
                    $profilePic = $this->normalizeProfilePicture(
                        $authorInfo ? ($authorInfo['profile_picture'] ?? null) : null
                    );

                    $posts[] = [
                        'id' => $post['id'],
                        'game' => $post['game'] ?? 'Unknown Game',
                        'title' => $post['title'] ?? '',
                        'content' => $post['content'] ?? '',
                        'username' => $post['username'],
                        'profile_picture' => $profilePic,
                        'likes' => $likeCount,
                        'comments' => $commentCount,
                        'created_at' => $post['created_at'],
                    ];
                }
            }

        } catch (Exception $e) {
            \Log::error("Bookmarks error: " . $e->getMessage());
        }

        return view('bookmarks', [
            'username' => $username,
            'userProfilePicture' => $userProfilePicture,
            'posts' => $posts,
        ]);
    }
}
