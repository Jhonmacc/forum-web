<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CommentLiked extends Notification
{
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
        return [
            'message' => "@{$this->likedBy} curtiu seu comentário no post '{$this->comment->post->title}'.",
            'comment_id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
        ];
    }
}
