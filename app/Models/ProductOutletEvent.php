<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOutletEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'user_id',
        'sku',
        'product_name',
        'action',
        'previous_status',
        'created_at',
    ];

    protected $casts = [
        'previous_status' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
