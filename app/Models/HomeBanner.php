<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBanner extends Model
{
    public const GROUP_MAIN = 'main';
    public const GROUP_EDITORIAL = 'editorial';

    protected $fillable = ['group', 'image', 'link', 'sort_order', 'is_active'];

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
}
