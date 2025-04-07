<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        // Validação dos parâmetros da query string
        $validated = $request->validate([
            'tag' => 'nullable|string',
            'sort' => 'nullable|string|in:últimas,mais novo,mais velho',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        // Parâmetros com valores padrão
        $tag = $validated['tag'] ?? 'Todos';
        $sort = $validated['sort'] ?? 'últimas';
        $perPage = $validated['per_page'] ?? 5;
        $page = $validated['page'] ?? 1;

        // Log para depuração
        Log::info('ForumController::index', [
            'tag' => $tag,
            'sort' => $sort,
            'per_page' => $perPage,
            'page' => $page,
        ]);

        // Inicia a query para buscar os posts
        $query = Post::with(['tags', 'user'])
                     ->withCount(['comments', 'likes']);

        // Filtra por tag, se não for "Todos"
        if ($tag !== 'Todos') {
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', $tag);
            });
        }

        // Ordena com base no parâmetro sort
        switch ($sort) {
            case 'mais novo':
                $query->orderBy('created_at', 'desc');
                break;
            case 'mais velho':
                $query->orderBy('created_at', 'asc');
                break;
            case 'últimas':
            default:
                // Ordena por última atividade (ex.: comentários ou criação)
                $query->orderBy('updated_at', 'desc');
                break;
        }

        // Executa a query com paginação
        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        // Log para depuração dos posts retornados
        Log::info('Posts retornados', [
            'current_page' => $posts->currentPage(),
            'per_page' => $posts->perPage(),
            'total' => $posts->total(),
            'data' => $posts->items(),
        ]);

        // Mantém os parâmetros na URL para a paginação
        $posts->appends([
            'tag' => $tag,
            'sort' => $sort,
            'per_page' => $perPage,
        ]);

        // Busca todas as tags disponíveis
        $tags = Tag::all(['id', 'code', 'name', 'color', 'icon', 'description']);

        return Inertia::render('Forum/Index', [
            'posts' => $posts,
            'filters' => [
                'tag' => $tag,
                'sort' => $sort,
                'per_page' => $perPage,
            ],
            'tags' => $tags,
        ]);
    }
}
