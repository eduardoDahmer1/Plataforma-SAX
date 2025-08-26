<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    // Se sua tabela se chama 'attributes', não precisa definir o nome.
    // protected $table = 'attributes';

    // Permitir campos que podem ser atualizados em massa
    protected $fillable = [
        'header_image',
        'noimage',
        'banner1',
        'banner2',
        'banner3',
        'banner4',
        'banner5',
        'banner6',
        'banner7',
        'banner8',
        'banner9',
        'banner10',

    ];
}
