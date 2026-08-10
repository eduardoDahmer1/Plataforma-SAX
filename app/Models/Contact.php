<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /** Lojas disponíveis para o formulário de currículo (Trabalhe Conosco). */
    public const STORES = [
        'cde' => 'Ciudad del Este',
        'asuncion' => 'Asunción',
        'pjc' => 'Pedro Juan Caballero',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'contact_type',
        'attachment',  // TEM QUE TER AQUI
        'store_name',
    ];
}
