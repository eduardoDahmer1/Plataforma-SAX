<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'type',
        'name',
        'description',
        'bank_details',  // Adiciona a coluna 'bank_details'
        'credentials',    // Continua para o campo credentials (para gateway)
        'settings',
        'active'
    ];

    protected $casts = [
        'credentials' => 'array', // Transforma o JSON 'credentials' em array
        'settings' => 'array',
        'active' => 'boolean'
    ];
}
