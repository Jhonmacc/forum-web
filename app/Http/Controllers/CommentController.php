<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Notifications\MentionedInComment;
use App\Models\User;
use App\Models\Mention;
use App\Models\Reply;
use App\Notifications\CommentReplied;
use App\Notifications\CommentLiked;


class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comments = $post->comments()->with('user', 'likes')->latest()->get();

        return response()->json([
            'post' => $post,
        ]);
    }
    public function toggleLike(Request $request, Comment $comment)
    {
        $user = auth()->user();

        // Verifica se o usuário já curtiu o comentário
        $like = $comment->likes()->where('user_id', $user->id)->first();

        if ($like) {
            // Se já curtiu, descurte (remove o like)
            $like->delete();
            $liked = false;
        } else {
            // Caso contrário, adiciona a curtida
            $comment->likes()->create(['user_id' => $user->id]);
            $liked = true;

            // Notifica o autor do comentário
            if ($comment->user_id !== $user->id) { // Não notifica se o autor curtir o próprio comentário
                $comment->user->notify(new CommentLiked($comment, $user->name));
            }
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }

    public function show(Comment $comment)
{
    $comment->load(['user', 'likes', 'replies.user', 'replies.children.user']);
    return response()->json($comment, 200);
}
public function replyToComment(Request $request, Comment $comment)
{
    $validated = $request->validate([
        'body' => 'required|string',
    ]);

    $reply = $comment->replies()->create([
        'user_id' => auth()->id(), // ID do autor da resposta
        'body' => $validated['body'],
    ]);

    // Carrega o autor da resposta
    $reply->load('user');

    // Notifica o autor do comentário, se necessário
    $comment->user->notify(new CommentReplied($reply, $comment));

    // Retorna a resposta completa para o front-end
    return response()->json($reply, 201);
}


    public function replyToReply(Request $request, Reply $reply)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $newReply = $reply->children()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'comment_id' => $reply->comment_id, // Vincula à mesma discussão/comentário
        ]);

        // Carrega o autor da nova resposta
        $newReply->load('user');

        // Notifica o autor da resposta original
        if ($reply->user_id !== auth()->id()) {
            $reply->user->notify(new CommentReplied($newReply, $reply->comment));
        }

        return response()->json($newReply, 201);
    }

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string', // Conteúdo do comentário
            'post_id' => 'required|exists:posts,id',
            'mentions' => 'array', // Lista de menções
        ]);

        try {
            $comment = Comment::create([
                'content' => $validated['content'],
                'post_id' => $post->id,
                'user_id' => auth()->id(),
            ]);

            $comment->load('user');

            // Processar menções
            $mentionedUsernames = collect($validated['mentions'] ?? [])->flatten()->toArray();
            if (!empty($mentionedUsernames)) {
                $mentionedUsers = User::whereIn('name', $mentionedUsernames)->get();
                foreach ($mentionedUsers as $user) {
                    Mention::create([
                        'comment_id' => $comment->id,
                        'mentioned_user_id' => $user->id,
                    ]);

                    // Notifica o usuário mencionado
                    $user->notify(new MentionedInComment($post, $comment, auth()->user()->name));
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
}
