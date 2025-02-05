<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MentionedInComment extends Notification
{
    use Queueable;

    protected $post;

    public function __construct($post, $comment, $mentionedBy)
    {
        $this->post = $post;
        $this->comment = $comment;
        $this->mentionedBy = $mentionedBy;  // Nome do usuário que fez a menção
    }

    public function via($notifiable)
    {
        return ['database']; // Armazena na tabela de notificações
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "<strong>@{$this->mentionedBy},</strong> mencionou você em um comentário no post: '{$this->post->title}'",
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id
        ];
    }
}
