<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriasFilhas extends Model
{
    // Vincula o model ao nome exato da tabela no banco de dados
    protected $table = 'childcategories';

    protected $fillable = [
        'name', 
        'slug', 
        'subcategory_id', 
        'category_id', 
        'photo', 
        'banner'
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function products()
    {
        /** * Verifique se na sua tabela 'products' a coluna ainda se chama 'child_category_id'.
         * Se sim, mantenha o segundo parâmetro abaixo.
         */
        return $this->hasMany(Product::class, 'childcategory_id');
    }
}