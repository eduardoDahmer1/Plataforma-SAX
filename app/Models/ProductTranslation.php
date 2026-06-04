<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    protected $table = 'product_translations';

    protected $fillable = [
        'product_id',
        'locale',
        'name',
        'details',
        'ship',
        'policy',
        'meta_tag',
        'features',
        'tags'
    ];

    // Diz ao Laravel que essa tradução pertence a um produto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}