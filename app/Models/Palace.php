<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Palace extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo', 
        'descricao', 
        'detalhes', 
        'slider_principal', 
        'galeria', 
        'imagem_gastronomia', 
        'imagem_bebidas', 
        'imagem_eventos'
    ];

    // Cast para transformar o JSON da galeria em array automaticamente
    protected $casts = [
        'galeria' => 'array',
    ];
}