<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogAttributeOption extends Model
{
    protected $guarded = ['id'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $casts = ['is_active' => 'boolean'];

    public function attribute()
    {
        return $this->belongsTo(CatalogAttribute::class, 'attribute_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $column = str_starts_with($locale, 'pt') ? 'pt' : (in_array($locale, ['es', 'en']) ? $locale : 'es');

        return (string) ($this->language?->{$column} ?: $this->language?->es ?: $this->code);
    }
}
