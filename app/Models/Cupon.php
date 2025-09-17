<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'tipo',           // 'percentual' ou 'valor'
        'montante',       // valor ou percentual
        'quantidade',     // quantidade disponível
        'data_inicio',
        'data_final',
        'valor_minimo',   // mínimo para aplicar
        'valor_maximo',   // máximo de desconto
        'modelo',         // 'produto', 'categoria', 'marca' ou 'geral'
        'categoria_id',
        'marca_id',
        'produto_id',
    ];

    protected $casts = [
        'montante'      => 'float',
        'quantidade'    => 'integer',
        'valor_minimo'  => 'float',
        'valor_maximo'  => 'float',
        'data_inicio'   => 'datetime',
        'data_final'    => 'datetime',
    ];

    // Relação com produto específico
    public function product()
    {
        return $this->belongsTo(Product::class, 'produto_id');
    }

    // Relação opcional com categoria
    public function category()
    {
        return $this->belongsTo(Category::class, 'categoria_id');
    }

    // Relação opcional com marca
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'marca_id');
    }

    // Escopo para cupons ativos
    public function scopeAtivos($query)
    {
        return $query->where('data_inicio', '<=', now())
                     ->where('data_final', '>=', now());
    }

    // Verifica se ainda tem quantidade disponível
    public function isAvailable()
    {
        return is_null($this->quantidade) || $this->quantidade > 0;
    }
}
