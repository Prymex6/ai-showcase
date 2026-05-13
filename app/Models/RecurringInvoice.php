<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurringInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'name', 'amount', 'vat_rate',
        'interval', 'day_of_month', 'next_date', 'is_active', 'description',
    ];

    protected $casts = [
        'next_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
