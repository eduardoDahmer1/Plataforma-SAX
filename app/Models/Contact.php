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

    public const HR_EMAILS = [
        'cde' => 'cv.cde@sax.com.py',
        'asuncion' => 'cv.asu@sax.com.py',
        'pjc' => 'cv.pjc@sax.com.py',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'contact_type',
        'attachment',  // TEM QUE TER AQUI
        'store_name',
        'read_at',
        'hr_sent_at',
        'hr_sent_to',
        'hr_attempted_at',
        'hr_last_error',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'hr_sent_at' => 'datetime',
        'hr_attempted_at' => 'datetime',
    ];
}
