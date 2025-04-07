<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'user_id', 'body', 'parent_id'];

    // Relacionamento com o comentário pai
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    // Relacionamento com respostas filhas
    public function children()
    {
        return $this->hasMany(Reply::class, 'parent_id')->with('user', 'likes', 'children');
    }

    // Relacionamento com o reply pai
    public function parent()
    {
        return $this->belongsTo(Reply::class, 'parent_id');
    }

    // Relacionamento com o autor da resposta
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com curtidas
    public function likes()
    {
        return $this->hasMany(Like::class, 'reply_id');
    }

    // Relacionamento com menções
    public function mentions()
    {
        return $this->hasMany(Mention::class, 'reply_id');
    }

    // Método para excluir em cascata
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($reply) {
            // Excluir todas as respostas aninhadas
            $reply->children()->each(function ($child) {
                $child->delete();
            });

            // Excluir curtidas associadas à resposta
            $reply->likes()->delete();

            // Excluir menções associadas à resposta
            $reply->mentions()->delete();
        });
    }
}
