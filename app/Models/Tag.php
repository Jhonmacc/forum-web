<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    // Adiciona 'code' ao array de atributos que podem ser atribuídos em massa
    protected $fillable = ['code', 'name', 'color'];

    // Relacionamento com os posts
    public function posts()
{
    return $this->belongsToMany(Post::class);
}

}
