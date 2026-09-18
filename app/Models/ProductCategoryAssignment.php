<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategoryAssignment extends Model
{
    protected $table = 'product_additional_categories';

    protected $fillable = [
        'product_id',
        'category_id',
        'subcategory_id',
        'childcategory_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
