<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCartItem extends Model
{
    protected $fillable = ['abandoned_cart_id', 'product_id', 'product_name', 'sku', 'image', 'unit_price', 'quantity'];

    public function cart() { return $this->belongsTo(AbandonedCart::class, 'abandoned_cart_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}
