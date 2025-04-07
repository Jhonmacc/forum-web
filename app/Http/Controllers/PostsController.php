<?php

namespace App\Http\Controllers;

use DOMDocument;
use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
 * Faz upload de uma imagem e retorna a URL pública.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */
/**
 * Faz upload de uma imagem e retorna a URL pública.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */
public function uploadImage(Request $request)
{
    if (!Auth::check()) {
        \Log::warning('Tentativa de upload de imagem por usuário não autenticado.');
        return response()->json(['message' => 'Usuário não autenticado'], 401);
    }

    try {
        \Log::info('Iniciando upload de imagem...');
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');
        \Log::info('Imagem recebida: ' . $image->getClientOriginalName());
        \Log::info('Tamanho da imagem: ' . $image->getSize() . ' bytes');
        \Log::info('MIME type da imagem: ' . $image->getMimeType());

        // Salvar a imagem usando o mesmo método do WordController
        $path = $image->store('images', 'public');
        \Log::info('Caminho retornado pelo store: ' . $path);

        // Verificar se o arquivo realmente foi salvo
        $absolutePath = storage_path('app/public/' . $path);
        \Log::info('Caminho absoluto do arquivo: ' . $absolutePath);

        if (file_exists($absolutePath)) {
            \Log::info('Arquivo confirmado no disco: ' . $absolutePath);
        } else {
            \Log::error('Arquivo NÃO foi salvo no disco: ' . $absolutePath);
            return response()->json(['message' => 'Erro ao salvar a imagem no disco'], 500);
        }

        // Gerar a URL pública
        $url = Storage::url($path);
        \Log::info('URL pública gerada: ' . $url);

        return response()->json(['url' => $url], 200);
    } catch (\Exception $e) {
        \Log::error('Erro ao fazer upload da imagem: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json(['message' => 'Erro ao fazer upload da imagem: ' . $e->getMessage()], 500);
    }
}
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

        // Extrair a primeira imagem da descrição (se houver)
        $description = $validated['description'];
        $imagePath = null;

        // Carregar o HTML da descrição em um DOMDocument
        $doc = new DOMDocument();
        @$doc->loadHTML($description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $doc->getElementsByTagName('img');

        if ($images->length > 0) {
            $image = $images->item(0); // Pegamos a primeira imagem
            $src = $image->getAttribute('src');

            // Verificamos se o src é uma URL válida (não Base64, já que o upload foi feito via uploadImage)
            if (filter_var($src, FILTER_VALIDATE_URL)) {
                $imagePath = $src; // Armazenamos a URL da imagem
            }
        }

        // Cria o post
        $post = Post::create([
            'title' => $validated['title'],
            'description' => $description, // A descrição já contém as URLs das imagens
            'images' => $imagePath, // Armazena a URL da primeira imagem
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

        // Verifica se a requisição é AJAX/JSON
        if (request()->wantsJson()) {
            return response()->json($post, 200);
        }

        // Renderiza a view para requisições normais
        return Inertia::render('Forum/EditPost', [
            'post' => $post
        ]);
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

    // Extrair a primeira imagem da descrição (se houver)
    $description = $validated['description'];
    $imagePath = $post->images; // Manter a imagem existente, se houver

    // Carregar o HTML da descrição em um DOMDocument
    $doc = new DOMDocument();
    @$doc->loadHTML($description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $images = $doc->getElementsByTagName('img');

    if ($images->length > 0) {
        $image = $images->item(0); // Pegamos a primeira imagem
        $src = $image->getAttribute('src');

        // Verificamos se o src é uma URL válida
        if (filter_var($src, FILTER_VALIDATE_URL)) {
            $imagePath = $src; // Atualizamos a URL da imagem
        }
    } else {
        $imagePath = null; // Se não houver imagens, limpamos a coluna images
    }

    // Atualiza o post
    $post->update([
        'title' => $validated['title'],
        'description' => $description,
        'images' => $imagePath,
    ]);

    // Atualizar as tags do post
    $post->tags()->sync($validated['tags']);

    return response()->json($post, 200);
}

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json($post, 200);
    }
}
