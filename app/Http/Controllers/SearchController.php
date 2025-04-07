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
}
