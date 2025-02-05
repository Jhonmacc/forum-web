<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'user_id', 'body'];

    // Relacionamento com o comentário pai
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class, 'comment_id')->with('user'); // Relacionamento com a tabela replies
    }
    
        public function parent()
    {
        return $this->belongsTo(Reply::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Reply::class, 'parent_id')->with('user');
    }

    // Relacionamento com o autor da resposta
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
