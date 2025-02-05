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

    // Respostas ao comentário
    public function replies()
    {
        return $this->hasMany(Reply::class, 'comment_id')->with('user'); // Relacionamento com a tabela replies
    }
}
