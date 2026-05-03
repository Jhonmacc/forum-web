<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommentReplied extends Notification implements ShouldQueue
{
    use Queueable;

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
        $locale = $notifiable->locale ?? config('app.locale');
        app()->setLocale($locale);

        return [
            'message' => __('notifications.comment_replied', [
                'name' => $this->reply->user->name,
                'title' => $this->comment->post->title,
            ]),
            'reply_id' => $this->reply->id,
            'comment_id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
        ];
    }
}
