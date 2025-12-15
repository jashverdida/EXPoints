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
            if ($userInfo && !empty($userInfo['profile_picture'])) {
                $userProfilePicture = $userInfo['profile_picture'];
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

                $posts[] = [
                    'id' => $post['id'],
                    'game' => $post['game'] ?? 'Unknown Game',
                    'title' => $post['title'] ?? '',
                    'content' => $post['content'] ?? '',
                    'username' => $post['username'],
                    'user_email' => $post['user_email'] ?? '',
                    'profile_picture' => $authorInfo['profile_picture'] ?? '/assets/img/cat1.jpg',
                    'exp_points' => $authorInfo['exp_points'] ?? 0,
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
    public function newest()
    {
        return $this->index(request());
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
            if ($userInfo && !empty($userInfo['profile_picture'])) {
                $userProfilePicture = $userInfo['profile_picture'];
            }

            // Fetch all posts
            $postsData = $this->supabase->select(
                'posts',
                '*',
                ['hidden' => 0],
                ['limit' => 100]
            );

            // Get user info and counts for each post
            foreach ($postsData as $post) {
                $authorInfo = $this->supabase->findBy('user_info', 'username', $post['username']);
                $likeCount = $this->supabase->count('post_likes', ['post_id' => $post['id']]);
                $commentCount = $this->supabase->count('post_comments', ['post_id' => $post['id']]);

                $posts[] = [
                    'id' => $post['id'],
                    'game' => $post['game'] ?? 'Unknown Game',
                    'title' => $post['title'] ?? '',
                    'content' => $post['content'] ?? '',
                    'username' => $post['username'],
                    'profile_picture' => $authorInfo['profile_picture'] ?? '/assets/img/cat1.jpg',
                    'exp_points' => $authorInfo['exp_points'] ?? 0,
                    'likes' => $likeCount,
                    'comments' => $commentCount,
                    'created_at' => $post['created_at'],
                ];
            }

            // Sort by likes (most popular)
            usort($posts, fn($a, $b) => $b['likes'] - $a['likes']);
            $posts = array_slice($posts, 0, 50);

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
            if ($userInfo && !empty($userInfo['profile_picture'])) {
                $userProfilePicture = $userInfo['profile_picture'];
            }

            // Get bookmarked post IDs
            $bookmarks = $this->supabase->select('bookmarks', 'post_id', ['user_id' => $userId]);

            foreach ($bookmarks as $bookmark) {
                $post = $this->supabase->find('posts', $bookmark['post_id']);
                if ($post && empty($post['hidden'])) {
                    $authorInfo = $this->supabase->findBy('user_info', 'username', $post['username']);
                    $likeCount = $this->supabase->count('post_likes', ['post_id' => $post['id']]);
                    $commentCount = $this->supabase->count('post_comments', ['post_id' => $post['id']]);

                    $posts[] = [
                        'id' => $post['id'],
                        'game' => $post['game'] ?? 'Unknown Game',
                        'title' => $post['title'] ?? '',
                        'content' => $post['content'] ?? '',
                        'username' => $post['username'],
                        'profile_picture' => $authorInfo['profile_picture'] ?? '/assets/img/cat1.jpg',
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
