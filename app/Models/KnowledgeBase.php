<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'category', 'question', 'answer', 'keywords', 'action', 'confidence', 'is_active', 'usage_count',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
