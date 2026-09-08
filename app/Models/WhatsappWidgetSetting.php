<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappWidgetSetting extends Model
{
    protected $fillable = [
        'title',
        'title_en',
        'title_es',
        'subtitle',
        'subtitle_en',
        'subtitle_es',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function translated(string $field): ?string
    {
        $locale = app()->getLocale();
        $suffix = str_starts_with($locale, 'en') ? '_en' : (str_starts_with($locale, 'es') ? '_es' : '');

        return $this->getAttribute($field.$suffix) ?: $this->getAttribute($field);
    }
}
