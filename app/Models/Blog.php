<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'read_time',
        'slug',
        'image',
        'image_caption',
        'content',
        'meta_description',
        'is_active',
        'featured',
        'published_at',
        'author',
        'category_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'published_at' => 'datetime',
        'read_time' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }
}