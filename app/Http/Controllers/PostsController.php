<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PostLikedNotification;
class PostsController extends Controller
{
      /**
     * Lista os posts paginados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    // public function index(Request $request)
    // {
    //     // Inicia a consulta com as tags associadas
    //     $query = Post::with('tags');

    //     // Filtra os posts pela tag, se necessário
    //     if ($request->has('tag')) {
    //         $query->whereHas('tags', function ($q) use ($request) {
    //             $q->where('name', $request->tag);
    //         });
    //     }

    //     // Ordena os posts pela data de criação em ordem decrescente
    //     $query->orderBy('created_at', 'desc');

    //     // Pagina os resultados após a ordenação
    //     $posts = $query->paginate(10);

    //     // Retorna a página com os posts ordenados
    //     return Inertia::render('Forum/Index', [
    //         'posts' => $posts,
    //     ]);
    // }
    /**
     * Armazena um novo post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function store(Request $request)
    {
        // Verifique se o usuário está autenticado
        if (!Auth::check()) {
            return response()->json(['message' => 'Usuário não autenticado'], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Cria o post
        $post = Post::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
        ]);

        // Sincroniza as tags, se houver
        if (!empty($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect('/forum')->with('message', 'Post criado com sucesso!');
    }

    public function show($postId)
{
    $post = Post::with([
        'tags',
        'user',
        'likes',
        'comments' => function ($query) {
            $query->with([
                'user',
                'likes',
                'replies' => function ($query) {
                    $query->with([
                        'user',
                        'likes',
                        'children' => function ($query) {
                            $query->with([
                                'user',
                                'likes',
                            ]);
                        },
                    ]);
                },
            ]);
        },
    ])
    ->withCount(['comments', 'likes'])
    ->findOrFail($postId);

    // Log para depuração
    \Log::info('Dados do post retornados:', $post->toArray());

    return response()->json($post, 200);
}

    public function like($id)
{
    $post = Post::findOrFail($id);
    $user = Auth::user();

    $existingLike = $post->likes()->where('user_id', $user->id)->first();

    if ($existingLike) {
        $existingLike->delete();
        $liked = false;
    } else {
        $post->likes()->create(['user_id' => $user->id]);
        $liked = true;

        if ($post->user_id !== $user->id) {
            $post->user->notify(new PostLikedNotification($post, $user));
        }
    }

    return response()->json([
        'liked' => $liked,
        'likes_count' => $post->likes()->count(),
    ], 200);
}

    public function edit($postId)
    {
        $post = Post::with('tags', 'user')->findOrFail($postId);

        // Renderiza a página de edição com os dados do post
        return Inertia::render('Forum/EditPost', [
            'post' => $post
        ]);
    }
    // Atualizar o post:
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $post = Post::findOrFail($id);
        $post->update($validated);

        // Atualizar as tags do post
        $post->tags()->sync($validated['tags']); // Sincronizando as tags com o post

        return response()->json($post, 200);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json($post, 200);
    }
}
