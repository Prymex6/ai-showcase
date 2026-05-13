<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'company',
        'nip', 'address', 'city', 'postal_code', 'country',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function recurringInvoices()
    {
        return $this->hasMany(RecurringInvoice::class);
    }
}
