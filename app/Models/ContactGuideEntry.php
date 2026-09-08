<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactGuideEntry extends Model
{
    protected $fillable = [
        'location_id',
        'floor',
        'floor_en',
        'floor_es',
        'sector',
        'sector_en',
        'sector_es',
        'description',
        'description_en',
        'description_es',
        'phone',
        'whatsapp_url',
        'brands',
        'is_optical',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'brands' => 'array',
        'is_optical' => 'boolean',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(ContactGuideLocation::class, 'location_id');
    }

    public function whatsappUrl(): ?string
    {
        if ($this->whatsapp_url) {
            return $this->whatsapp_url;
        }

        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        return $digits ? "https://wa.me/{$digits}" : null;
    }

    public function translated(string $field): ?string
    {
        $locale = app()->getLocale();
        $suffix = str_starts_with($locale, 'en') ? '_en' : (str_starts_with($locale, 'es') ? '_es' : '');

        return $this->getAttribute($field.$suffix) ?: $this->getAttribute($field);
    }
}
