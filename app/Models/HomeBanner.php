<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBanner extends Model
{
    public const GROUP_MAIN = 'main';

    public const GROUP_EDITORIAL = 'editorial';

    protected $fillable = [
        'group', 'image', 'mobile_image', 'link', 'sort_order', 'is_active',
        'title_pt', 'description_pt',
        'title_en', 'description_en',
        'title_es', 'description_es',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url', 'mobile_image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->resolveImageUrl($this->image);
    }

    public function getMobileImageUrlAttribute(): string
    {
        return filled($this->mobile_image)
            ? $this->resolveImageUrl($this->mobile_image)
            : $this->image_url;
    }

    public function translated(string $field, ?string $locale = null): string
    {
        abort_unless(in_array($field, ['title', 'description'], true), 500);

        $locale ??= app()->getLocale();
        $language = str_starts_with($locale, 'en') ? 'en' : (str_starts_with($locale, 'es') ? 'es' : 'pt');

        return (string) ($this->getAttribute("{$field}_{$language}") ?? '');
    }

    private function resolveImageUrl(string $path): string
    {
        if (str_starts_with($path, 'home-banners/')) {
            return Storage::disk('public')->url($path);
        }

        return asset('storage/uploads/'.ltrim($path, '/'));
    }
}
