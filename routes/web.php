<?php
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WordController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\UserProfileController;

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

        // Search Users
        Route::get('/users/search', [UserController::class, 'search']);

  // Fórum
        Route::prefix('forum')->group(function () {
        Route::get('/', [ForumController::class, 'index'])->name('forum.index');
    });
    Route::get('/users/{id}', [UserProfileController::class, 'show'])->name('users.show');
    Route::get('/users/by-username/{username}', [UserProfileController::class, 'getUserByUsername']);
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostsController::class, 'store'])->name('posts.store');
        Route::post('/{id}/like', [PostsController::class, 'like'])->middleware('auth');
        Route::get('/{postId}', [PostsController::class, 'show'])->name('posts.show');
        Route::get('/{postId}/edit', [PostsController::class, 'edit'])->name('posts.edit');
        Route::put('/{postId}', [PostsController::class, 'update'])->name('posts.update');
        Route::delete('/{postId}', [PostsController::class, 'destroy'])->name('posts.destroy');
    });
    // Tags
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('tags.index');  // Rota de exibição de tags
        Route::post('/', [TagController::class, 'store'])->name('tags.store');  // Rota de criação de tags
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');  // Rota de exclusão de tags
    });
        Route::get('/show', [TagController::class, 'show'])->name('tags.show');   // Rota carrega as tags

    // Rotas Commentários
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->middleware('auth');
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

    // Notificações
    // buscar notificações
    Route::get('/notifications', function () {
        $user = auth()->user();
        return response()->json([
            'notifications' => $user->notifications, // Todas as notificações
            'unread_count' => $user->unreadNotifications->count(), // Contagem de notificações não lidas
        ]);
    })->middleware('auth');
    // marcar notificações como lidas
    Route::post('/notifications/mark-as-read', function () {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Notificações marcadas como lidas.']);
    })->middleware('auth');

    // Rotas de repostas de comentários e curtidas
    Route::middleware('auth')->group(function () {
    Route::post('/comments/{comment}/like', [CommentController::class, 'likeComment']);
    Route::post('/comments/{comment}/reply', [CommentController::class, 'replyToComment']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'toggleLike']);
    Route::post('/replies/{reply}/reply', [CommentController::class, 'replyToReply']);
    Route::get('/comments/{comment}', [CommentController::class, 'show']);
    Route::get('/replies/{reply}', [ReplyController::class, 'show']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']); // Nova rota para atualizar comentários
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']); // Nova rota para excluir comentários
    });
    // Rotas para respostas
    Route::post('/replies/{reply}/like', [CommentController::class, 'toggleLikeReply']);
    Route::put('/replies/{reply}', [CommentController::class, 'updateReply']); // Nova rota para atualizar respostas
    Route::delete('/replies/{reply}', [CommentController::class, 'destroyReply']); // Nova rota para excluir respostas
    // Words Pages
    Route::post('/words', [WordController::class, 'store'])->name('words.store');
    Route::get('/api/words', [WordController::class, 'index'])->name('api.words.index');
    Route::get('/words', fn () => Inertia::render('Document/ListWord'))->name('words.list');
    Route::get('/words/{id}', [WordController::class, 'show'])->name('words.view');
});
