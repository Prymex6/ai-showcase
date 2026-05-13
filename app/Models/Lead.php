<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'company', 'email', 'phone',
        'service', 'message', 'status', 'score', 'source', 'notes',
    ];

    public static array $statuses = [
        'new'         => 'Nowy',
        'contact'     => 'Kontakt',
        'qualified'   => 'Kwalifikowany',
        'proposal'    => 'Propozycja',
        'closed_won'  => 'Zamknięty — wygrany',
        'closed_lost' => 'Zamknięty — przegrany',
    ];
}
