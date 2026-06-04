<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogTranslation extends Model
{
    protected $table = 'blog_translations';
    
    public $timestamps = true;

    protected $fillable = [
        'blog_id',
        'locale',
        'title',
        'content',
        'meta_tag',
        'meta_description'
    ];

    // Relacionamento reverso com o Post do Blog principal
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }
}