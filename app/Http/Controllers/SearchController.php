<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchPosts(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([]);
        }

        $posts = Post::where('title', 'like', "%{$query}%")
            ->with('user')
            ->select('id', 'title', 'user_id')
            ->limit(5)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'user_name' => $post->user->name,
                    'url' => route('posts.show', $post->id) // Adiciona a URL correta para o post
                ];
            });

        return response()->json($posts);
    }

    public function searchPostReferences(Request $request)
    {
        $query = trim((string) $request->input('query', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $posts = Post::query()
            ->with('user:id,name,username')
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(8)
            ->get(['id', 'title', 'description', 'user_id', 'created_at'])
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'excerpt' => mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($post->description))), 0, 140),
                    'user_name' => $post->user?->name,
                    'url' => route('posts.show', $post->id),
                    'created_at' => $post->created_at,
                ];
            });

        return response()->json($posts);
    }
}
