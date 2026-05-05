<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generalsetting extends Model
{
    use HasFactory;

    protected $table = 'generalsettings';

    // Adicionada a coluna 'show_highlight_famosos' para controlar os Mais Vistos
    protected $fillable = [
        'site_name',
        'show_highlight_destaque',
        'show_highlight_lancamentos',
        'show_highlight_famosos', // <--- Nova aqui
        'show_highlight_ofertas_relampago',  
    ];

    // Garantindo que o Laravel trate o valor como booleano (0 ou 1)
    protected $casts = [
        'show_highlight_destaque' => 'boolean',
        'show_highlight_lancamentos' => 'boolean',
        'show_highlight_famosos' => 'boolean', // <--- Nova aqui
        'show_highlight_ofertas_relampago'=> 'boolean',
    ];
}