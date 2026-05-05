<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductViewHistory extends Model
{
    // Define o nome da tabela explicitamente
    protected $table = 'product_views_history';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['user_id', 'product_id', 'updated_at', 'created_at'];

    // Relacionamento: Este registro pertence a um produto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
