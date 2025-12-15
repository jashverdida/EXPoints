<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/discover', function () {
    return view('discover');
})->name('discover');

Route::get('/games/{game}', function ($game) {
    return view('games.show', ['game' => $game]);
})->name('games.show');

// Banned page
Route::get('/banned', function () {
    return view('banned');
})->name('banned');

// User dashboard and authenticated routes
Route::middleware(['auth', 'check.banned'])->group(function () {
    // User dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Bookmarks
    Route::get('/bookmarks', function () {
        return view('bookmarks');
    })->name('bookmarks');

    // Posts
    Route::get('/posts/create', function () {
        return view('posts.create');
    })->name('posts.create');

    Route::get('/posts/{id}', function ($id) {
        return view('posts.show', ['id' => $id]);
    })->name('posts.show');
});

// Moderator routes
Route::middleware(['auth', 'check.banned', 'role:mod,admin'])->prefix('mod')->name('mod.')->group(function () {
    Route::get('/dashboard', function () {
        return view('mod.dashboard');
    })->name('dashboard');

    Route::get('/ban-reviews', function () {
        return view('mod.ban-reviews');
    })->name('ban-reviews');
});

// Admin routes
Route::middleware(['auth', 'check.banned', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('users');

    Route::get('/moderators', function () {
        return view('admin.moderators');
    })->name('moderators');
});

require __DIR__.'/auth.php';
