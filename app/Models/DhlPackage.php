<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhlPackage extends Model
{
    protected $fillable = [
        'code', 'name', 'dhl_package_code', 'active', 'length_cm', 'width_cm',
        'height_cm', 'tare_weight_kg', 'max_gross_weight_kg', 'max_items',
        'fill_ratio_percent', 'product_examples', 'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'length_cm' => 'float',
        'width_cm' => 'float',
        'height_cm' => 'float',
        'tare_weight_kg' => 'float',
        'max_gross_weight_kg' => 'float',
        'max_items' => 'integer',
        'fill_ratio_percent' => 'float',
        'sort_order' => 'integer',
    ];

    public function volumeCm3(): float
    {
        return $this->length_cm * $this->width_cm * $this->height_cm;
    }

    public function usableVolumeCm3(): float
    {
        return $this->volumeCm3() * max(0.1, min(1, $this->fill_ratio_percent / 100));
    }
}
