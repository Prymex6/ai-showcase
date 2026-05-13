<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'content', 'platforms', 'hashtags', 'status', 'scheduled_at', 'published_at',
    ];

    protected $casts = [
        'platforms'    => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];
}
