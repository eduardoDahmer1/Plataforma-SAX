<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generalsetting extends Model
{
    use HasFactory;

    protected $table = 'generalsettings';

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'site_name',
        'show_highlight_destaque',
        'show_highlight_mais_vendidos',
        'show_highlight_melhores_avaliacoes',
        'show_highlight_super_desconto',
        'show_highlight_famosos',
        'show_highlight_lancamentos',
        'show_highlight_tendencias',
        'show_highlight_promocoes',
        'show_highlight_ofertas_relampago',
        'show_highlight_navbar',
    ];

    // Casts para boolean (facilita usar $settings->show_highlight_destaque como true/false)
    protected $casts = [
        'show_highlight_destaque' => 'boolean',
        'show_highlight_mais_vendidos' => 'boolean',
        'show_highlight_melhores_avaliacoes' => 'boolean',
        'show_highlight_super_desconto' => 'boolean',
        'show_highlight_famosos' => 'boolean',
        'show_highlight_lancamentos' => 'boolean',
        'show_highlight_tendencias' => 'boolean',
        'show_highlight_promocoes' => 'boolean',
        'show_highlight_ofertas_relampago' => 'boolean',
        'show_highlight_navbar' => 'boolean',
    ];
}
