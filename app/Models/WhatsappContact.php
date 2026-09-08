<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappContact extends Model
{
    protected $fillable = [
        'title',
        'title_en',
        'title_es',
        'category',
        'category_en',
        'category_es',
        'icon',
        'phone',
        'message',
        'message_en',
        'message_es',
        'description',
        'description_en',
        'description_es',
        'page_contexts',
        'show_on_contact_page',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'page_contexts' => 'array',
        'show_on_contact_page' => 'boolean',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function whatsappUrl(): string
    {
        $phone = preg_replace('/\D+/', '', $this->phone);
        $message = trim((string) ($this->translated('message') ?: $this->translated('title')));

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    public function translated(string $field): ?string
    {
        $locale = app()->getLocale();
        $suffix = str_starts_with($locale, 'en') ? '_en' : (str_starts_with($locale, 'es') ? '_es' : '');

        return $this->getAttribute($field.$suffix) ?: $this->getAttribute($field);
    }
}
