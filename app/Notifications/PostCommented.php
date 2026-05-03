<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostCommented extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $post,
        public $comment,
        public $commentedBy
    ) {
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
            'message' => __('notifications.post_commented', [
                'name' => $this->commentedBy->name,
                'title' => $this->post->title,
            ]),
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
            'type' => 'comment',
        ];
    }
}
