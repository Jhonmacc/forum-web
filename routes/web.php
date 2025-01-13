<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WordController;

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
    });
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostsController::class, 'store'])->name('posts.store');
        Route::get('/{postId}', [PostsController::class, 'show'])->name('posts.show');
        Route::get('/{postId}/edit', [PostsController::class, 'edit'])->name('posts.edit');
        Route::put('/{postId}', [PostsController::class, 'update'])->name('posts.update');
    });
    // Tags
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('tags.index');  // Rota de exibição de tags
        Route::post('/', [TagController::class, 'store'])->name('tags.store');  // Rota de criação de tags
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');  // Rota de exclusão de tags
    });
        Route::get('/show', [TagController::class, 'show'])->name('tags.show');   // Rota carrega as tags no

    // Words Pages
    Route::post('/words', [WordController::class, 'store'])->name('words.store');
    Route::get('/api/words', [WordController::class, 'index'])->name('api.words.index');
    Route::get('/words', fn () => Inertia::render('Document/ListWord'))->name('words.list');
    Route::get('/words/{id}', [WordController::class, 'show'])->name('words.view');
});
