<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'home_carousel_image', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
