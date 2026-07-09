<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\CategoryDisplayService;

class Subcategory extends Model
{
    protected $fillable = ['name', 'slug', 'category_id', 'photo', 'banner'];

    public function getNameAttribute($value)
    {
        $displayService = app(CategoryDisplayService::class);
        
        return $displayService->formatName($value, $this->attributes['slug'] ?? null);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categoriasfilhas()
    {
        return $this->hasMany(CategoriasFilhas::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}