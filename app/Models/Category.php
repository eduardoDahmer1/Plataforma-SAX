<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\CategoryDisplayService;

class Category extends Model
{
    protected $fillable = ['name', 'slug','photo','banner','status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
    public function getNameAttribute($value)
    {
        // Instancia o service diretamente do container do Laravel
        $displayService = app(CategoryDisplayService::class);
        
        return $displayService->formatName($value, $this->attributes['slug'] ?? null);
    }
}

