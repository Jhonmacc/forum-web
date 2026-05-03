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
use App\Notifications\PostCommented;
use App\Services\HtmlContentSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    private function extractMentions($text)
    {
        return app(HtmlContentSanitizer::class)->extractMentionUsernames($text);
    }

    private function validateRichContent(Request $request, string $field): array
    {
        $validator = Validator::make($request->all(), [
            $field => 'required|string|max:' . config('forum.limits.comment_body_html'),
            'mentions' => 'nullable|array',
        ]);

        $validator->after(function ($validator) use ($request, $field) {
            $plainText = app(HtmlContentSanitizer::class)->plainText($request->input($field));

            if (mb_strlen($plainText) > config('forum.limits.comment_body')) {
                $validator->errors()->add($field, __('validation.max.string', [
                    'attribute' => $field,
                    'max' => config('forum.limits.comment_body'),
                ]));
            }
        });

        return $validator->validate();
    }

    private function mentionedUsernames(array $validated, string $content): array
    {
        $requested = collect($validated['mentions'] ?? [])
            ->flatMap(fn ($mention) => [$mention['username'] ?? null, $mention['name'] ?? null])
            ->filter()
            ->all();

        return array_values(array_unique(array_merge($requested, $this->extractMentions($content))));
    }

    public function index(Post $post)
    {
        $comments = $post->comments()->with('user', 'likes', 'replies.user', 'replies.likes', 'replies.children')->latest()->get();
        $sanitizer = app(HtmlContentSanitizer::class);
        $comments->each(function ($comment) use ($sanitizer) {
            $comment->content = $sanitizer->cleanComment($comment->content);
            $comment->replies->each(function ($reply) use ($sanitizer) {
                $this->sanitizeReplyTree($reply, $sanitizer);
            });
        });
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
        $sanitizer = app(HtmlContentSanitizer::class);
        $comment->content = $sanitizer->cleanComment($comment->content);
        $comment->replies->each(function ($reply) use ($sanitizer) {
            $this->sanitizeReplyTree($reply, $sanitizer);
        });
        return response()->json($comment, 200);
    }

    private function sanitizeReplyTree(Reply $reply, HtmlContentSanitizer $sanitizer): void
    {
        $reply->body = $sanitizer->cleanComment($reply->body);
        $reply->children->each(function ($child) use ($sanitizer) {
            $this->sanitizeReplyTree($child, $sanitizer);
        });
    }

    public function replyToComment(Request $request, Comment $comment)
    {
        $validated = $this->validateRichContent($request, 'body');
        $body = app(HtmlContentSanitizer::class)->cleanComment($validated['body']);

        $reply = $comment->replies()->create([
            'user_id' => Auth::id(),
            'body' => $body,
        ]);

        $reply->load('user');

        if ($comment->user_id !== Auth::id()) {
            $comment->user->notify(new CommentReplied($reply, $comment));
        }

        $mentionedUsernames = $this->mentionedUsernames($validated, $body);

        if (!empty($mentionedUsernames)) {
            Log::info('Menções recebidas para replyToComment:', ['mentions' => $mentionedUsernames]);

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
        $validated = $this->validateRichContent($request, 'body');
        $body = app(HtmlContentSanitizer::class)->cleanComment($validated['body']);

        $newReply = $reply->children()->create([
            'user_id' => Auth::id(),
            'body' => $body,
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

        $mentionedUsernames = $this->mentionedUsernames($validated, $body);

        if (!empty($mentionedUsernames)) {
            Log::info('Menções recebidas para replyToReply:', ['mentions' => $mentionedUsernames]);

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
        $validated = $this->validateRichContent($request, 'content');
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);
        $content = app(HtmlContentSanitizer::class)->cleanComment($validated['content']);

        try {
            $comment = Comment::create([
                'content' => $content,
                'post_id' => $post->id,
                'user_id' => Auth::id(),
            ]);

            $comment->load('user');

            if ($post->user_id !== Auth::id()) {
                $post->user->notify(new PostCommented($post, $comment, Auth::user()));
            }

            $mentionedUsernames = $this->mentionedUsernames($validated, $content);

            if (!empty($mentionedUsernames)) {
                Log::info('Menções recebidas para store:', ['mentions' => $mentionedUsernames]);

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
                'message' => __('messages.comment_added'),
                'data' => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('messages.comment_add_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'message' => __('messages.no_permission_edit_comment'),
            ], 403);
        }

        $validated = $this->validateRichContent($request, 'content');
        $content = app(HtmlContentSanitizer::class)->cleanComment($validated['content']);

        try {
            $comment->update([
                'content' => $content,
            ]);

            $comment->mentions()->delete();

            $mentionedUsernames = $this->mentionedUsernames($validated, $content);

            if (!empty($mentionedUsernames)) {
                Log::info('Menções recebidas para update:', ['mentions' => $mentionedUsernames]);

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
                'message' => __('messages.comment_updated'),
                'data' => $comment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('messages.comment_update_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateReply(Request $request, Reply $reply)
    {
        if ($reply->user_id !== Auth::id()) {
            return response()->json([
                'message' => __('messages.no_permission_edit_reply'),
            ], 403);
        }

        $validated = $this->validateRichContent($request, 'body');
        $body = app(HtmlContentSanitizer::class)->cleanComment($validated['body']);

        try {
            $reply->update([
                'body' => $body,
            ]);

            Log::info('Removendo menções antigas da resposta', ['reply_id' => $reply->id]);
            $reply->mentions()->delete();

            $mentionedUsernames = $this->mentionedUsernames($validated, $body);

            if (!empty($mentionedUsernames)) {
                Log::info('Menções recebidas para updateReply:', ['mentions' => $mentionedUsernames]);

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
                'message' => __('messages.reply_updated'),
                'data' => $reply,
            ], 200);
        } catch (\Exception $e) {
            Log::error(__('messages.reply_update_error'), ['error' => $e->getMessage()]);
            return response()->json([
                'message' => __('messages.reply_update_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'message' => __('messages.no_permission_delete_comment'),
            ], 403);
        }

        try {
            $comment->delete();

            return response()->json([
                'message' => __('messages.comment_deleted'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('messages.comment_delete_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyReply(Reply $reply)
    {
        if ($reply->user_id !== Auth::id()) {
            return response()->json([
                'message' => __('messages.no_permission_delete_reply'),
            ], 403);
        }

        try {
            $reply->delete();

            return response()->json([
                'message' => __('messages.reply_deleted'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('messages.reply_delete_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
