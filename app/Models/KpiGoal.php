<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'metric', 'target_value', 'current_value', 'unit', 'period', 'is_active',
    ];

    protected $attributes = [
        'current_value' => 0,
        'unit'          => 'PLN',
        'period'        => 'monthly',
        'is_active'     => true,
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function getProgressAttribute(): float
    {
        if ($this->target_value == 0) return 0;
        return min(100, round(($this->current_value / $this->target_value) * 100, 1));
    }

    protected $appends = ['progress'];
}
