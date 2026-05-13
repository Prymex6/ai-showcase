<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'client_id', 'status', 'issue_date', 'due_date',
        'net_total', 'vat_total', 'gross_total', 'currency', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
    ];

    public static array $statuses = [
        'draft'     => 'Szkic',
        'sent'      => 'Wysłana',
        'paid'      => 'Opłacona',
        'overdue'   => 'Zaległa',
        'cancelled' => 'Anulowana',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
