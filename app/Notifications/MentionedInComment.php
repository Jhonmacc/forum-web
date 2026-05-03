<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MentionedInComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $comment;
    protected $mentionedBy;

    public function __construct($post, $comment, $mentionedBy)
    {
        $this->post = $post;
        $this->comment = $comment;
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
            'message' => __('notifications.mentioned_in_comment', [
                'name' => $this->mentionedBy,
                'title' => $this->post->title,
            ]),
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
        ];
    }
}
