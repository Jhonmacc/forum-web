<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\TagController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rotas protegidas por autenticação
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

   // Fórum
    Route::prefix('forum')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/post/{id}', [ForumController::class, 'show'])->name('forum.post.show');
    });


    // Posts
Route::prefix('posts')->group(function () {
    Route::post('/', [PostsController::class, 'store'])->name('posts.store');
    Route::get('/{id}/edit', [ForumController::class, 'edit'])->name('posts.edit');
    Route::put('/{id}', [ForumController::class, 'update'])->name('posts.update');
});

    // Tags
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('tags.index');  // Rota de exibição de tags
        Route::post('/', [TagController::class, 'store'])->name('tags.store');  // Rota de criação de tags
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');  // Rota de exclusão de tags
    });
    Route::get('/show', [TagController::class, 'show'])->name('tags.show');  // Rota carrega as tags no Mult-Select
});
