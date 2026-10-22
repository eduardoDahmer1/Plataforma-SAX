<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    protected $guarded = ['id'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $casts = ['boolean_value' => 'boolean', 'number_value' => 'decimal:6', 'evidence' => 'array'];

    public function attribute()
    {
        return $this->belongsTo(CatalogAttribute::class, 'attribute_id');
    }

    public function options()
    {
        return $this->belongsToMany(CatalogAttributeOption::class, 'product_attribute_value_options', 'value_id', 'option_id');
    }
}
