<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $table = 'category_translations';
    
    public $timestamps = true;

    protected $fillable = [
        'category_id',
        'locale',
        'name'
    ];

    // Relacionamento reverso com a Categoria principal
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}