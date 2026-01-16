<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generalsetting extends Model
{
    use HasFactory;

    protected $table = 'generalsettings';

    // 🔹 Apenas os campos que serão utilizados agora
    protected $fillable = [
        'site_name',
        'show_highlight_destaque',
        'show_highlight_lancamentos',
    ];

    // 🔹 Casts simplificados para boolean
    protected $casts = [
        'show_highlight_destaque' => 'boolean',
        'show_highlight_lancamentos' => 'boolean',
    ];
}