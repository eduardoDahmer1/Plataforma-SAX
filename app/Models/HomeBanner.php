<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBanner extends Model
{
    public const GROUP_MAIN = 'main';
    public const GROUP_EDITORIAL = 'editorial';

    protected $fillable = [
        'group', 'image', 'link', 'sort_order', 'is_active',
        'title_pt', 'description_pt',
        'title_en', 'description_en',
        'title_es', 'description_es',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image, 'home-banners/')) {
            return Storage::disk('public')->url($this->image);
        }

        return asset('storage/uploads/' . ltrim($this->image, '/'));
    }

    public function translated(string $field, ?string $locale = null): string
    {
        abort_unless(in_array($field, ['title', 'description'], true), 500);

        $locale ??= app()->getLocale();
        $language = str_starts_with($locale, 'en') ? 'en' : (str_starts_with($locale, 'es') ? 'es' : 'pt');

        return (string) ($this->getAttribute("{$field}_{$language}") ?? '');
    }
}
