<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'name',          // Nome do produto (ou external_name)
        'external_name', // Caso seja produto externo/afiliado
        'slug',
        'sku',
        'product_brand',
        'product_size',
        'product_color',
        'shipping_profile',
        'shipping_weight_kg',
        'shipping_length_cm',
        'shipping_width_cm',
        'shipping_height_cm',
        'shipping_measurement_estimated',
    ];

    protected $casts = [
        'shipping_weight_kg' => 'float',
        'shipping_length_cm' => 'float',
        'shipping_width_cm' => 'float',
        'shipping_height_cm' => 'float',
        'shipping_measurement_estimated' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')
            ->withoutGlobalScope('minimum_visible_price');
    }
}
