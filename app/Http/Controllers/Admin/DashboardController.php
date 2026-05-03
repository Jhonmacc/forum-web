<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Reply;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        $postsCount = Post::count();
        $membersCount = User::count();
        $commentsCount = Comment::count();
        $repliesCount = Reply::count();
        $votesCount = Like::whereNotNull('post_id')->count();
        $tagsCount = Tag::count();

        $topPosts = Post::query()
            ->with(['user:id,name,username,profile_photo_path', 'tags:id,name,color,icon'])
            ->withCount(['likes', 'comments'])
            ->orderByHot()
            ->limit(5)
            ->get()
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'excerpt' => str(strip_tags($post->description))->squish()->limit(120)->toString(),
                'author' => $post->user?->name,
                'author_photo' => $post->user?->profile_photo_url,
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'hot_score' => round(($post->likes_count * 3) + ($post->comments_count * 1.5), 1),
                'created_at' => $post->created_at?->toIso8601String(),
                'tags' => $post->tags->map(fn (Tag $tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                    'icon' => $tag->icon,
                ]),
            ]);

        $topTags = Tag::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(8)
            ->get(['id', 'name', 'color', 'icon'])
            ->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
                'icon' => $tag->icon,
                'posts_count' => $tag->posts_count,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'members' => $membersCount,
                'posts' => $postsCount,
                'comments' => $commentsCount + $repliesCount,
                'votes' => $votesCount,
                'tags' => $tagsCount,
                'today_posts' => Post::whereDate('created_at', $today)->count(),
                'today_members' => User::whereDate('created_at', $today)->count(),
                'week_posts' => Post::where('created_at', '>=', $startOfWeek)->count(),
                'week_comments' => Comment::where('created_at', '>=', $startOfWeek)->count()
                    + Reply::where('created_at', '>=', $startOfWeek)->count(),
            ],
            'growth' => $this->growthSeries(),
            'topPosts' => $topPosts,
            'topTags' => $topTags,
            'recentActivity' => $this->recentActivity(),
        ]);
    }

    private function growthSeries(): array
    {
        return collect(range(6, 0))
            ->map(function (int $daysAgo) {
                $date = Carbon::today()->subDays($daysAgo);

                return [
                    'label' => $date->format('d/m'),
                    'posts' => Post::whereDate('created_at', $date)->count(),
                    'members' => User::whereDate('created_at', $date)->count(),
                    'comments' => Comment::whereDate('created_at', $date)->count()
                        + Reply::whereDate('created_at', $date)->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function recentActivity(): Collection
    {
        $posts = Post::query()
            ->with('user:id,name,username,profile_photo_path')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Post $post) => [
                'type' => 'post',
                'icon' => 'fa-solid fa-message',
                'title' => 'Novo post publicado',
                'description' => $post->title,
                'user' => $post->user?->name,
                'created_at' => $post->created_at,
                'human_time' => $post->created_at?->toIso8601String(),
                'href' => route('posts.edit', $post->id),
            ]);

        $comments = Comment::query()
            ->with(['user:id,name,username,profile_photo_path', 'post:id,title'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Comment $comment) => [
                'type' => 'comment',
                'icon' => 'fa-solid fa-comment-dots',
                'title' => 'Comentário recente',
                'description' => $comment->post?->title,
                'user' => $comment->user?->name,
                'created_at' => $comment->created_at,
                'human_time' => $comment->created_at?->toIso8601String(),
                'href' => $comment->post ? route('posts.edit', $comment->post->id) : null,
            ]);

        $members = User::query()
            ->latest()
            ->limit(6)
            ->get(['id', 'name', 'username', 'email', 'profile_photo_path', 'created_at'])
            ->map(fn (User $user) => [
                'type' => 'member',
                'icon' => 'fa-solid fa-user-plus',
                'title' => 'Novo membro',
                'description' => $user->username ? '@'.$user->username : $user->email,
                'user' => $user->name,
                'created_at' => $user->created_at,
                'human_time' => $user->created_at?->toIso8601String(),
                'href' => route('users.show', $user->id),
            ]);

        return $posts
            ->merge($comments)
            ->merge($members)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();
    }
}
