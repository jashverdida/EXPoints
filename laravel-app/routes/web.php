<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PostController::class, 'index'])->name('dashboard');
    
    // Post views
    Route::get('/newest', [PostController::class, 'newest'])->name('posts.newest');
    Route::get('/popular', [PostController::class, 'popular'])->name('posts.popular');
    Route::get('/bookmarks', [PostController::class, 'bookmarks'])->name('posts.bookmarks');
    Route::get('/games/{game}', [PostController::class, 'byGame'])->name('posts.by-game');
    
    // Games browser
    Route::get('/games', function () {
        $user = auth()->user();
        $userInfo = $user->userInfo;
        
        $games = \App\Models\Post::selectRaw('game, COUNT(*) as post_count, MAX(created_at) as last_post_date')
            ->where('hidden', false)
            ->whereNotNull('game')
            ->where('game', '!=', '')
            ->groupBy('game')
            ->orderByDesc('post_count')
            ->get();
        
        return view('games.index', compact('games', 'userInfo'));
    })->name('games');
    
    // Profile
    Route::get('/profile', function () {
        $user = auth()->user();
        $userInfo = $user->userInfo;
        $posts = $user->posts()->withCount(['likes', 'comments'])->newest()->get();
        
        return view('profile', compact('userInfo', 'posts'));
    })->name('profile');
});
