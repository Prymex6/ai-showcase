<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResponseTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sentiment_type', 'min_rating', 'max_rating', 'content', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
