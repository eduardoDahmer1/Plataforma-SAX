<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'title', 
        'subtitle', 
        'read_time',        // Novo: Tempo de leitura
        'slug', 
        'image', 
        'image_caption',    // Novo: Créditos da imagem
        'content', 
        'meta_description', // Novo: SEO
        'is_active', 
        'featured',         // Novo: Artigo destaque
        'published_at', 
        'author',           // Campo que já existia no seu banco
        'category_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'featured' => 'boolean',     // Novo: Cast para booleano
        'published_at' => 'datetime',
        'read_time' => 'integer',    // Novo: Garantir que seja número
    ];

    /**
     * Boot da Model
     */
    public static function boot()
    {
        parent::boot();

        // Gera slug automaticamente ao salvar caso esteja vazio
        static::saving(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });
    }

    /**
     * Relacionamento com a Categoria
     */
    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Scope para pegar apenas posts ativos e já publicados
     * Útil para usar no Front-end: Blog::published()->get();
     */
    public function scopePublished($query)
    {
        return $query->where('is_active', true)
                     ->where('published_at', '<=', now())
                     ->orderBy('published_at', 'desc');
    }

    /**
     * Scope para pegar apenas os destaques
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }
}