<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkPreview extends Model
{
    protected $fillable = [
        'url_hash',
        'url',
        'domain',
        'title',
        'description',
        'image_url',
        'status',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];
}
