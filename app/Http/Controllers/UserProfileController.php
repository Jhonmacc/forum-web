<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);
        $currentUserId = auth()->id();

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->with(['tags', 'user'])
            ->withCount(['likes', 'comments'])
            ->orderByHot()
            ->get()
            ->map(function (Post $post) use ($currentUserId) {
                $post->liked_by_current_user = $currentUserId
                    ? $post->likes()->where('user_id', $currentUserId)->exists()
                    : false;

                return $post;
            });

        $bestPost = $posts
            ->sortByDesc(fn ($post) => ($post->likes_count * 3) + ($post->comments_count * 1.5))
            ->first();

        return Inertia::render('Forum/UserProfile', [
            'userId' => $user->id,
            'user' => $user,
            'posts' => $posts->values(),
            'isOwner' => $currentUserId === $user->id,
            'stats' => [
                'posts_count' => $posts->count(),
                'votes_count' => $posts->sum('likes_count'),
                'comments_count' => $posts->sum('comments_count'),
                'best_post' => $bestPost ? [
                    'id' => $bestPost->id,
                    'title' => $bestPost->title,
                    'likes_count' => $bestPost->likes_count,
                ] : null,
            ],
        ]);
    }

    public function getUserByUsername($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        return response()->json(['id' => $user->id]);
    }
}
