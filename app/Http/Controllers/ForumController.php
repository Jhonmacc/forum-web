<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildPostQuery($request);

        return Inertia::render('Forum/Index', $data);
    }

    public function publicIndex(Request $request)
    {
        $data = $this->buildPostQuery($request);
        $data['isAuthenticated'] = auth()->check();
        $data['stats'] = [
            'members' => User::count(),
            'posts' => Post::count(),
        ];

        return Inertia::render('Welcome', $data);
    }

    private function buildPostQuery(Request $request): array
    {
        $validated = $request->validate([
            'tag' => 'nullable|string',
            'sort' => 'nullable|string|in:latest,newest,oldest,most_voted,hot',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $tag = $validated['tag'] ?? 'Todos';
        $sort = $validated['sort'] ?? 'latest';
        $perPage = $validated['per_page'] ?? 5;
        $page = $validated['page'] ?? 1;

        $query = Post::with(['tags', 'user'])
                     ->withCount(['comments', 'likes']);

        if ($tag !== 'Todos') {
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', $tag);
            });
        }

        switch ($sort) {
            case 'hot':
                $query->orderByHot();
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_voted':
                $query->orderBy('likes_count', 'desc');
                break;
            case 'latest':
            default:
                $query->orderBy('updated_at', 'desc');
                break;
        }

        $posts = $query->paginate($perPage, ['*'], 'page', $page);
        $currentUserId = auth()->id();
        $posts->getCollection()->transform(function (Post $post) use ($currentUserId) {
            $post->liked_by_current_user = $currentUserId
                ? $post->likes()->where('user_id', $currentUserId)->exists()
                : false;
            return $post;
        });

        $posts->appends([
            'tag' => $tag,
            'sort' => $sort,
            'per_page' => $perPage,
        ]);

        $tags = Tag::all(['id', 'code', 'name', 'color', 'icon', 'description']);

        $categoryCounts = Tag::withCount('posts')->pluck('posts_count', 'name');
        $categoryCounts['Todos'] = Post::count();

        return [
            'posts' => $posts,
            'filters' => [
                'tag' => $tag,
                'sort' => $sort,
                'per_page' => $perPage,
            ],
            'tags' => $tags,
            'categoryCounts' => $categoryCounts,
        ];
    }
}
