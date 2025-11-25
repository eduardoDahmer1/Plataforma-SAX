<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = ['name', 'slug', 'category_id', 'photo', 'banner'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function childcategories()
    {
        return $this->hasMany(Childcategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

