<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['tags', 'user']) // Carrega tags e usuário
                    ->withCount(['comments', 'likes']) // Contagem de comentários e likes
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return Inertia::render('Forum/Index', [
            'posts' => $posts,
        ]);
    }
}
