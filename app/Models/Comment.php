<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'parent_id', 'content'];

    // Relacionamento com o post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Relacionamento com o autor do comentário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com curtidas
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Relacionamento com menções
    public function mentions()
    {
        return $this->hasMany(Mention::class);
    }

    // Respostas ao comentário
    public function replies()
{
    return $this->hasMany(Reply::class, 'comment_id')
        ->whereNull('parent_id') // Garantir que apenas respostas de nível 1 sejam carregadas aqui
        ->with('user', 'likes', 'children');
}

    // Método para excluir em cascata
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($comment) {
            // Excluir todas as respostas associadas (e suas respostas aninhadas, curtidas, etc.)
            $comment->replies()->each(function ($reply) {
                $reply->delete();
            });

            // Excluir curtidas associadas ao comentário
            $comment->likes()->delete();

            // Excluir menções associadas ao comentário
            $comment->mentions()->delete();
        });
    }
}
