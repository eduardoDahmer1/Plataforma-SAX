<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    // Se sua tabela se chama 'attributes', não precisa definir o nome.
    // Mas se for diferente, ative a linha abaixo:
    // protected $table = 'attributes';

    // Permitir campos que podem ser atualizados em massa
    protected $fillable = ['header_image'];
}
