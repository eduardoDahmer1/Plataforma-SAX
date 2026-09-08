<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactGuideLocation extends Model
{
    protected $fillable = [
        'city',
        'city_en',
        'city_es',
        'name',
        'name_en',
        'name_es',
        'subtitle',
        'subtitle_en',
        'subtitle_es',
        'service_hours',
        'service_hours_en',
        'service_hours_es',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(ContactGuideEntry::class, 'location_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function translated(string $field): ?string
    {
        $locale = app()->getLocale();
        $suffix = str_starts_with($locale, 'en') ? '_en' : (str_starts_with($locale, 'es') ? '_es' : '');

        return $this->getAttribute($field.$suffix) ?: $this->getAttribute($field);
    }
}
