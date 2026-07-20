<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    // Relacionamento com Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereHas('product', fn (Builder $product) => $product->sellable());
    }

    // Opcional: relacionamento com User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // O cupom do carrinho vive na sessão (CuponService), não numa coluna de carts.
}
