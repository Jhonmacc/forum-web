<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\User;
use App\Models\Mention;
use App\Notifications\MentionedInReply;
use App\Notifications\MentionedInComment;
use App\Notifications\CommentReplied;
use App\Notifications\CommentLiked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    private function extractMentions($text)
    {
        preg_match_all('/@([\w\s]+)/', $text, $matches);
        return array_unique($matches[1]);
    }

    public function index(Post $post)
    {
        $comments = $post->comments()->with('user', 'likes', 'replies.user', 'replies.likes', 'replies.children')->latest()->get();
        return response()->json($comments);
    }

    public function toggleLike(Request $request, Comment $comment)
    {
        $user = Auth::user();
        $like = $comment->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $user->id]);
            $liked = true;

            if ($comment->user_id !== $user->id) {
                $comment->user->notify(new CommentLiked($comment, $user->name));
            }
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }

    public function toggleLikeReply(Request $request, Reply $reply)
    {
        $user = Auth::user();
        $existingLike = $reply->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            $reply->likes()->create(['user_id' => $user->id]);
            $liked = true;

            if ($reply->user_id !== $user->id) {
                $reply->user->notify(new CommentLiked($reply, $user->name));
            }
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $reply->likes()->count(),
        ]);
    }

    public function show(Comment $comment)
    {
        $comment->load(['user', 'likes', 'replies.user', 'replies.likes', 'replies.children.user', 'replies.children.likes']);
        return response()->json($comment, 200);
    }

    public function replyToComment(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'mentions' => 'nullable|array',
        ]);

        $reply = $comment->replies()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $reply->load('user');

        if ($comment->user_id !== Auth::id()) {
            $comment->user->notify(new CommentReplied($reply, $comment));
        }

        if (!empty($validated['mentions'])) {
            $mentionedUsernames = collect($validated['mentions'])->pluck('name')->all(); // Tenta usar 'name'
            Log::info('Menções recebidas para replyToComment:', ['mentions' => $mentionedUsernames]);

            if (empty($mentionedUsernames)) {
                $mentionedUsernames = collect($validated['mentions'])->pluck('username')->all(); // Fallback para 'username'
                Log::info('Tentativa de fallback para username:', ['mentions' => $mentionedUsernames]);
            }

            $mentionedUsers = User::whereIn('name', $mentionedUsernames)->orWhereIn('username', $mentionedUsernames)->get();
            Log::info('Usuários encontrados:', ['users' => $mentionedUsers->pluck('id')->all()]);

            foreach ($mentionedUsers as $user) {
                if ($user->id !== Auth::id()) {
                    Mention::create([
                        'reply_id' => $reply->id,
                        'mentioned_user_id' => $user->id,
                    ]);
                    $user->notify(new MentionedInReply($reply, Auth::user()->name));
                    Log::info('Notificação enviada para:', ['user_id' => $user->id, 'username' => $user->username]);
                }
            }
        }

        return response()->json($reply, 201);
    }

    public function replyToReply(Request $request, Reply $reply)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'mentions' => 'nullable|array',
        ]);

        $newReply = $reply->children()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'comment_id' => $reply->comment_id,
        ]);

        $newReply->load('user', 'likes', 'children');

        Log::info('Nova resposta aninhada criada:', [
            'reply_id' => $newReply->id,
            'comment_id' => $newReply->comment_id,
            'parent_id' => $newReply->parent_id,
            'body' => $newReply->body,
        ]);

        if ($reply->user_id !== Auth::id()) {
            $reply->user->notify(new CommentReplied($newReply, $reply->comment));
        }

        if (!empty($validated['mentions'])) {
            $mentionedUsernames = collect($validated['mentions'])->pluck('name')->all(); // Tenta usar 'name'
            Log::info('Menções recebidas para replyToReply:', ['mentions' => $mentionedUsernames]);

            if (empty($mentionedUsernames)) {
                $mentionedUsernames = collect($validated['mentions'])->pluck('username')->all(); // Fallback para 'username'
                Log::info('Tentativa de fallback para username:', ['mentions' => $mentionedUsernames]);
            }

            $mentionedUsers = User::whereIn('name', $mentionedUsernames)->orWhereIn('username', $mentionedUsernames)->get();
            Log::info('Usuários encontrados:', ['users' => $mentionedUsers->pluck('id')->all()]);

            foreach ($mentionedUsers as $user) {
                if ($user->id !== Auth::id()) {
                    Mention::create([
                        'reply_id' => $newReply->id,
                        'mentioned_user_id' => $user->id,
                    ]);
                    $user->notify(new MentionedInReply($newReply, Auth::user()->name));
                    Log::info('Notificação enviada para:', ['user_id' => $user->id, 'username' => $user->username]);
                }
            }
        }

        return response()->json($newReply, 201);
    }

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'mentions' => 'nullable|array',
        ]);

        try {
            $comment = Comment::create([
                'content' => $validated['content'],
                'post_id' => $post->id,
                'user_id' => Auth::id(),
            ]);

            $comment->load('user');

            if (!empty($validated['mentions'])) {
                $mentionedUsernames = collect($validated['mentions'])->pluck('name')->all(); // Tenta usar 'name'
                Log::info('Menções recebidas para store:', ['mentions' => $mentionedUsernames]);

                if (empty($mentionedUsernames)) {
                    $mentionedUsernames = collect($validated['mentions'])->pluck('username')->all(); // Fallback para 'username'
                    Log::info('Tentativa de fallback para username:', ['mentions' => $mentionedUsernames]);
                }

                $mentionedUsers = User::whereIn('name', $mentionedUsernames)->orWhereIn('username', $mentionedUsernames)->get();
                Log::info('Usuários encontrados:', ['users' => $mentionedUsers->pluck('id')->all()]);

                foreach ($mentionedUsers as $user) {
                    Mention::create([
                        'comment_id' => $comment->id,
                        'mentioned_user_id' => $user->id,
                    ]);
                    if ($user->id !== Auth::id()) {
                        $user->notify(new MentionedInComment($post, $comment, Auth::user()->name));
                        Log::info('Notificação enviada para:', ['user_id' => $user->id, 'username' => $user->username]);
                    }
                }
            }

            return response()->json([
                'message' => 'Comentário adicionado com sucesso',
                'data' => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao adicionar comentário',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para editar este comentário.',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'mentions' => 'nullable|array',
        ]);

        try {
            $comment->update([
                'content' => $validated['content'],
            ]);

            $comment->mentions()->delete();

            if (!empty($validated['mentions'])) {
                $mentionedUsernames = collect($validated['mentions'])->pluck('name')->all(); // Tenta usar 'name'
                Log::info('Menções recebidas para update:', ['mentions' => $mentionedUsernames]);

                if (empty($mentionedUsernames)) {
                    $mentionedUsernames = collect($validated['mentions'])->pluck('username')->all(); // Fallback para 'username'
                    Log::info('Tentativa de fallback para username:', ['mentions' => $mentionedUsernames]);
                }

                $mentionedUsers = User::whereIn('name', $mentionedUsernames)->orWhereIn('username', $mentionedUsernames)->get();
                Log::info('Usuários encontrados:', ['users' => $mentionedUsers->pluck('id')->all()]);

                foreach ($mentionedUsers as $user) {
                    Mention::create([
                        'comment_id' => $comment->id,
                        'mentioned_user_id' => $user->id,
                    ]);
                    if ($user->id !== Auth::id()) {
                        $user->notify(new MentionedInComment($comment->post, $comment, Auth::user()->name));
                        Log::info('Notificação enviada para:', ['user_id' => $user->id, 'username' => $user->username]);
                    }
                }
            }

            $comment->load('user');

            return response()->json([
                'message' => 'Comentário atualizado com sucesso',
                'data' => $comment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar comentário',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateReply(Request $request, Reply $reply)
    {
        if ($reply->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para editar esta resposta.',
            ], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string',
            'mentions' => 'nullable|array',
        ]);

        try {
            $reply->update([
                'body' => $validated['body'],
            ]);

            Log::info('Removendo menções antigas da resposta', ['reply_id' => $reply->id]);
            $reply->mentions()->delete();

            if (!empty($validated['mentions'])) {
                $mentionedUsernames = collect($validated['mentions'])->pluck('name')->all(); // Tenta usar 'name'
                Log::info('Menções recebidas para updateReply:', ['mentions' => $mentionedUsernames]);

                if (empty($mentionedUsernames)) {
                    $mentionedUsernames = collect($validated['mentions'])->pluck('username')->all(); // Fallback para 'username'
                    Log::info('Tentativa de fallback para username:', ['mentions' => $mentionedUsernames]);
                }

                $mentionedUsers = User::whereIn('name', $mentionedUsernames)->orWhereIn('username', $mentionedUsernames)->get();
                Log::info('Usuários encontrados:', ['users' => $mentionedUsers->pluck('id')->all()]);

                foreach ($mentionedUsers as $user) {
                    Mention::create([
                        'reply_id' => $reply->id,
                        'mentioned_user_id' => $user->id,
                    ]);
                    if ($user->id !== Auth::id()) {
                        $user->notify(new MentionedInReply($reply, Auth::user()->name));
                        Log::info('Notificação enviada para:', ['user_id' => $user->id, 'username' => $user->username]);
                    }
                }
            }

            $reply->load('user');

            return response()->json([
                'message' => 'Resposta atualizada com sucesso',
                'data' => $reply,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar resposta', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erro ao atualizar resposta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para excluir este comentário.',
            ], 403);
        }

        try {
            $comment->delete();

            return response()->json([
                'message' => 'Comentário excluído com sucesso',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir comentário',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyReply(Reply $reply)
    {
        if ($reply->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para excluir esta resposta.',
            ], 403);
        }

        try {
            $reply->delete();

            return response()->json([
                'message' => 'Resposta excluída com sucesso',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir resposta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
