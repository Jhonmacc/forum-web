<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $post;
    public $likedBy;

    public function __construct($post, $likedBy)
    {
        $this->post = $post;
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

        return [
            'message' => __('notifications.post_liked', [
                'name' => $this->likedBy->name,
                'title' => $this->post->title,
            ]),
            'post_id' => $this->post->id,
            'type' => 'post',
        ];
    }
}
