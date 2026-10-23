<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutStockReservation extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'quantity',
        'status',
        'expires_at',
        'committed_at',
        'released_at',
        'release_reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'committed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
