<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;
class ForumController extends Controller
{
    // Exibe os posts com paginação

    public function relativeTime($date) {
        return Carbon::parse($date)->diffForHumans();
    }
    public function index(Request $request)
{
    $posts = Post::with('tags', 'user') // Carrega os posts com tags e usuário
                ->orderBy('created_at', 'desc') // Ordena os posts pela data de criação (mais recentes primeiro)
                ->paginate(10); // Paginação com 10 posts por página

    return Inertia::render('Forum/Index', [
        'posts' => $posts,
    ]);
}



    // Exibe os detalhes de um post
    public function show($id)
    {
        $post = Post::with('tags', 'user')->findOrFail($id);
        return response()->json($post);
    }

    // Criação de um novo post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'required|array|exists:tags,id',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        // Associar tags ao post
        $post->tags()->sync($request->tags);

        return redirect()->route('forum.index');
    }

    // Edita um post (somente se for o autor)
    public function edit($id)
    {
        $post = Post::findOrFail($id);

        // Verifica se o usuário é o autor do post
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('forum.index')->with('error', 'Você não tem permissão para editar este post');
        }

        return view('forum.edit', compact('post'));
    }

    // Atualiza um post
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Verifica se o usuário é o autor do post
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('forum.index')->with('error', 'Você não tem permissão para editar este post');
        }

        $post->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        // Atualiza as tags associadas
        $post->tags()->sync($request->tags);

        return redirect()->route('forum.index');
    }
}
