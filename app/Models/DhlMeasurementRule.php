<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhlMeasurementRule extends Model
{
    public const CACHE_KEY = 'dhl:measurement-rules:active';

    protected $fillable = [
        'hierarchy_key', 'level', 'category_id', 'subcategory_id', 'childcategory_id',
        'scope_name', 'profile_code', 'active', 'requires_manual_review',
        'weight_kg', 'length_cm', 'width_cm', 'height_cm',
    ];

    protected $casts = [
        'active' => 'boolean',
        'requires_manual_review' => 'boolean',
        'weight_kg' => 'float',
        'length_cm' => 'float',
        'width_cm' => 'float',
        'height_cm' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function childcategory()
    {
        return $this->belongsTo(CategoriasFilhas::class, 'childcategory_id');
    }
}
