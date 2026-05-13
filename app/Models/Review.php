<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_name', 'rating', 'content', 'platform', 'sentiment', 'reply', 'status', 'review_date',
    ];

    protected $casts = ['review_date' => 'datetime'];

    public function getSentimentAttribute(): string
    {
        return match(true) {
            $this->rating >= 4 => 'positive',
            $this->rating == 3 => 'neutral',
            default            => 'negative',
        };
    }

    protected $appends = ['sentiment'];
}
