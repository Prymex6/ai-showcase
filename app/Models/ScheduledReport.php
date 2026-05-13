<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduledReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'report_type', 'frequency', 'recipients', 'is_active', 'next_run', 'last_sent_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'next_run'     => 'datetime',
        'last_sent_at' => 'datetime',
    ];
}
