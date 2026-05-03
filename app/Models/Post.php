<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['title', 'description', 'images', 'user_id'];

    // Relacionamento com o usuário (autor do post)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Relacionamento com os comentários
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    // Relacionamento com as tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getExcerptAttribute()
    {
        return substr($this->description, 0, 100) . '...';
    }
    // Relacionamento com curtidas
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function scopeOrderByHot(Builder $query): Builder
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return $query
                ->orderByRaw('((likes_count * 3) + (comments_count * 1.5) + (MAX(0, 259200 - (? - strftime("%s", created_at))) / 86400.0)) DESC', [time()])
                ->orderByDesc('created_at');
        }

        if ($driver === 'pgsql') {
            return $query
                ->orderByRaw('((likes_count * 3) + (comments_count * 1.5) + (GREATEST(0, 259200 - EXTRACT(EPOCH FROM (NOW() - created_at))) / 86400.0)) DESC')
                ->orderByDesc('created_at');
        }

        return $query
            ->orderByRaw('((likes_count * 3) + (comments_count * 1.5) + (GREATEST(0, 259200 - TIMESTAMPDIFF(SECOND, created_at, NOW())) / 86400.0)) DESC')
            ->orderByDesc('created_at');
    }
}
