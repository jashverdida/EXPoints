<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout (authenticated only) - support both GET and POST
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth.supabase');

// Banned/Disabled pages
Route::get('/banned', [AuthController::class, 'banned'])->name('banned');
Route::get('/disabled', [AuthController::class, 'disabled'])->name('disabled');

// User authenticated routes
Route::middleware(['auth.supabase'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/popular', [DashboardController::class, 'popular'])->name('popular');
    Route::get('/newest', [DashboardController::class, 'newest'])->name('newest');
    Route::get('/bookmarks', [DashboardController::class, 'bookmarks'])->name('bookmarks');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::get('/user/{username}', [ProfileController::class, 'viewProfile'])->name('profile.view');

    // Posts routes
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{id}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/{id}/bookmark', [PostController::class, 'toggleBookmark'])->name('posts.bookmark');
    Route::post('/posts/{id}/comment', [PostController::class, 'addComment'])->name('posts.comment');
});

// Moderator routes
Route::middleware(['auth.supabase', 'role:mod'])->prefix('mod')->name('mod.')->group(function () {
    Route::get('/dashboard', function () {
        return view('mod.dashboard');
    })->name('dashboard');
});

// Admin routes
Route::middleware(['auth.supabase', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/moderators', [AdminController::class, 'moderators'])->name('moderators');
    Route::get('/ban-appeals', [AdminController::class, 'banAppeals'])->name('ban-appeals');
    Route::post('/flag-ban', [AdminController::class, 'flagBan'])->name('flag-ban');
    Route::post('/users/{id}/ban', [AdminController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{id}/unban', [AdminController::class, 'unbanUser'])->name('users.unban');
    Route::post('/users/{id}/disable', [AdminController::class, 'disableUser'])->name('users.disable');
    Route::post('/users/{id}/enable', [AdminController::class, 'enableUser'])->name('users.enable');
    Route::post('/users/{id}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::post('/posts/{id}/visibility', [AdminController::class, 'togglePostVisibility'])->name('posts.visibility');
});
