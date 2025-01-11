<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['title', 'description', 'user_id'];

    // Relacionamento com o usuário (autor do post)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com as tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }


    // Escopo para pegar um excerto da descrição
    public function getExcerptAttribute()
    {
        return substr($this->description, 0, 100) . '...';
    }
}
