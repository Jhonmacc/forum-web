<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CommentReplied extends Notification
{
    public $reply;
    public $comment;

    public function __construct($reply, $comment)
    {
        $this->reply = $reply;
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "@{$this->reply->user->name} respondeu seu comentário no post '{$this->comment->post->title}'.",
            'reply_id' => $this->reply->id,
            'comment_id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
        ];
    }
}
