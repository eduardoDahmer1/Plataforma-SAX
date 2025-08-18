<?php

namespace App\Models;

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

    // Opcional: relacionamento com User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
