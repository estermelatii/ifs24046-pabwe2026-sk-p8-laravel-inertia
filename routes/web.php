<?php

use App\Features\Admin\Controllers\AdminUserController;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\AdminPostController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:1000-per-second')->group(function () {
    // Tingkat 1: Publik (tanpa login, tanpa sidebar)
    Route::get('/', [PublicPostController::class, 'index'])->name('home');
    Route::get('/articles', [PublicPostController::class, 'index'])->name('articles.index');
    Route::get('/articles/{slug}', [PublicPostController::class, 'show'])->name('articles.show');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
        Route::post('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');

        // Tingkat 2: Anggota / Penulis
        Route::middleware('role:member')->prefix('dashboard/posts')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('posts.index');
            Route::post('/', [PostController::class, 'store'])->name('posts.store');
            Route::get('/export', [PostController::class, 'export'])->name('posts.export');
            Route::post('/import', [PostController::class, 'import'])->name('posts.import');
            Route::post('/ai-generate', [PostController::class, 'aiGenerate'])->name('posts.ai-generate');
            Route::post('/ai-save', [PostController::class, 'aiSave'])->name('posts.ai-save');

            Route::middleware('owns.post')->group(function () {
                Route::get('/{post}', [PostController::class, 'show'])->name('posts.show');
                Route::put('/{post}', [PostController::class, 'update'])->name('posts.update');
                Route::delete('/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
                Route::patch('/{post}/publish', [PostController::class, 'togglePublish'])->name('posts.publish');
                Route::post('/{post}/cover', [PostController::class, 'uploadCover'])->name('posts.cover');
                Route::delete('/{post}/cover', [PostController::class, 'removeCover'])->name('posts.cover.remove');
            });
        });

        // Tingkat 3: Admin
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
            Route::get('/users/{user}/posts', [AdminUserController::class, 'posts'])->name('admin.users.posts');
            Route::get('/users/{user}/posts/export', [AdminUserController::class, 'exportPosts'])->name('admin.users.posts.export');
            Route::get('/posts', [AdminPostController::class, 'index'])->name('admin.posts');
            Route::get('/posts/export', [AdminPostController::class, 'export'])->name('admin.posts.export');
        });
    });
});
