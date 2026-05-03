<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MentionedInReply extends Notification implements ShouldQueue
{
    use Queueable;

    public $reply;
    public $mentionedBy;

    public function __construct($reply, $mentionedBy)
    {
        $this->reply = $reply;
        $this->mentionedBy = $mentionedBy;
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
            'message' => __('notifications.mentioned_in_reply', [
                'name' => $this->mentionedBy,
                'title' => $this->reply->comment->post->title,
            ]),
            'reply_id' => $this->reply->id,
            'comment_id' => $this->reply->comment_id,
            'post_id' => $this->reply->comment->post_id,
        ];
    }
}
