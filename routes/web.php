<?php
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WordController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\LinkPreviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;

// Rota pública — Landing page com timeline
Route::get('/', [ForumController::class, 'publicIndex'])->name('home');
Route::get('/login', fn () => auth()->check()
    ? redirect()->route('forum.index')
    : redirect()->route('home', ['auth' => 'login']));
Route::get('/register', fn () => auth()->check()
    ? redirect()->route('forum.index')
    : redirect()->route('home', ['auth' => 'register']));

// Rotas públicas de leitura
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->get('/posts/search', [SearchController::class, 'searchPostReferences'])->name('posts.search');
Route::get('/posts/{postId}', [PostsController::class, 'show'])->name('posts.show');
Route::get('/search-posts', [SearchController::class, 'searchPosts'])->name('search.posts');
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

// Rotas protegidas por autenticação
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard administrativo
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('admin')
        ->name('dashboard');

    // Search Users
    Route::get('/users/search', [UserController::class, 'search']);
    Route::post('/links/preview', [LinkPreviewController::class, 'store'])->name('links.preview');

    // Rota para upload de imagens
    Route::post('/posts/upload-image', [PostsController::class, 'uploadImage'])->name('posts.upload-image');

    // Fórum (autenticado)
    Route::prefix('forum')->group(function () {
        Route::get('/', [ForumController::class, 'index'])->name('forum.index');
        Route::get('/tags', [TagController::class, 'index'])->middleware('admin')->name('forum.tags.index');
    });

    // Perfil dos Usuários
    Route::get('/users/{id}', [UserProfileController::class, 'show'])->name('users.show');
    Route::get('/users/by-username/{username}', [UserProfileController::class, 'getUserByUsername']);

    // Posts (escrita)
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostsController::class, 'store'])->name('posts.store');
        Route::post('/{id}/like', [PostsController::class, 'like']);
        Route::get('/{postId}/edit', [PostsController::class, 'edit'])->name('posts.edit');
        Route::put('/{postId}', [PostsController::class, 'update'])->name('posts.update');
        Route::delete('/{postId}', [PostsController::class, 'destroy'])->name('posts.destroy');
    });

    // Tags
    Route::middleware('admin')->prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('tags.index');
        Route::post('/', [TagController::class, 'store'])->name('tags.store');
        Route::put('/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    });
    Route::get('/show', [TagController::class, 'show'])->name('tags.show');

    // Comentários (escrita)
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);

    // Notificações
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    // Troca de idioma
    Route::post('/locale', function (\Illuminate\Http\Request $request) {
        $locale = $request->validate(['locale' => 'required|string|in:pt-BR,en'])['locale'];

        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return response()->json(['message' => __('messages.locale_updated')])
            ->cookie('locale', $locale, 525600);
    })->name('locale.update');

    // Respostas e curtidas de comentários
    Route::post('/comments/{comment}/like', [CommentController::class, 'toggleLike']);
    Route::post('/comments/{comment}/reply', [CommentController::class, 'replyToComment']);
    Route::post('/replies/{reply}/reply', [CommentController::class, 'replyToReply']);
    Route::get('/comments/{comment}', [CommentController::class, 'show']);
    Route::get('/replies/{reply}', [ReplyController::class, 'show']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/replies/{reply}/like', [CommentController::class, 'toggleLikeReply']);
    Route::put('/replies/{reply}', [CommentController::class, 'updateReply']);
    Route::delete('/replies/{reply}', [CommentController::class, 'destroyReply']);

    // Words
    Route::post('/words', [WordController::class, 'store'])->name('words.store');
    Route::get('/api/words', [WordController::class, 'index'])->name('api.words.index');
    Route::get('/words', fn () => Inertia::render('Document/ListWord'))->name('words.list');
    Route::get('/words/{id}', [WordController::class, 'show'])->name('words.view');

    // Admin
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::put('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    });
});
