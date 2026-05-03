<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommentLiked extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;
    public $likedBy;

    public function __construct($comment, $likedBy)
    {
        $this->comment = $comment;
        $this->likedBy = $likedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $locale = $notifiable->locale ?? config('app.locale');
        app()->setLocale($locale);

        if ($this->comment instanceof \App\Models\Comment) {
            $type = __('notifications.comment_type');
            $post = $this->comment->post;
            $postId = $this->comment->post_id;
            $commentId = $this->comment->id;
        } elseif ($this->comment instanceof \App\Models\Reply) {
            $type = __('notifications.reply_type');
            $post = $this->comment->comment->post;
            $postId = $this->comment->comment->post_id;
            $commentId = $this->comment->id;
        } else {
            throw new \Exception('Invalid comment type for notification');
        }

        return [
            'message' => __('notifications.comment_liked', [
                'name' => $this->likedBy,
                'type' => $type,
                'title' => $post->title,
            ]),
            'comment_id' => $commentId,
            'post_id' => $postId,
            'type' => $type,
        ];
    }
}
