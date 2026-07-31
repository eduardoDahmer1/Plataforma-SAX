<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function translatedTitle(): string
    {
        return $this->translatedPart('title', (string) $this->title);
    }

    public function translatedMessage(): string
    {
        return $this->translatedPart('message', (string) $this->message);
    }

    private function translatedPart(string $part, string $fallback): string
    {
        $key = 'messages.notification_'.preg_replace('/[^a-z0-9_]/', '', strtolower((string) $this->type)).'_'.$part;
        $translation = __($key, $this->translationParameters());

        if ($translation === $key || preg_match('/:[a-zA-Z_][a-zA-Z0-9_]*/', $translation)) {
            return $fallback;
        }

        return $translation;
    }

    private function translationParameters(): array
    {
        $data = is_array($this->data) ? $this->data : [];
        $parameters = is_array($data['translation_params'] ?? null)
            ? $data['translation_params']
            : [];

        return array_filter(
            array_merge($this->legacyTranslationParameters(), $parameters),
            static fn ($value) => $value !== null && $value !== ''
        );
    }

    private function legacyTranslationParameters(): array
    {
        $message = (string) $this->message;
        $data = is_array($this->data) ? $this->data : [];
        $parameters = [];

        if (preg_match('/#([^\s.]+)/u', $message, $match)) {
            $parameters['reference'] = $match[1];
        }

        if (in_array($this->type, ['new_contact', 'new_resume'], true)
            && preg_match('/^(.+?) enviou /u', $message, $match)) {
            $parameters['name'] = $match[1];
        }

        if ($this->type === 'new_user' && preg_match('/^(.+?) criou uma conta/u', $message, $match)) {
            $parameters['name'] = $match[1];
        }

        if ($this->type === 'out_of_stock' && preg_match('/^(.+?) ficou sem estoque/u', $message, $match)) {
            $parameters['product'] = $match[1];
        }

        if ($this->type === 'low_stock'
            && preg_match('/^(.+?) está com apenas (\d+) unidade/u', $message, $match)) {
            $parameters['product'] = $match[1];
            $parameters['stock'] = $match[2];
        }

        if ($this->type === 'high_value_abandoned_cart'
            && preg_match('/carrinho de (.+?) foi abandonado/u', $message, $match)) {
            $parameters['total'] = $match[1];
        }

        if ($this->type === 'abandoned_cart_feedback') {
            $parameters['cart'] = $data['abandoned_cart_id'] ?? null;
        }

        if ($this->type === 'new_brand' && preg_match('/^A marca (.+?) chegou/u', $message, $match)) {
            $parameters['brand'] = $match[1];
        }

        if ($this->type === 'new_category' && preg_match('/categoria (.+?)\.$/u', $message, $match)) {
            $parameters['category'] = $match[1];
        }

        if ($this->type === 'new_coupon'
            && preg_match('/cupom (.+?) e aproveite (.+?) de desconto/u', $message, $match)) {
            $parameters['code'] = $match[1];
            $parameters['discount'] = $match[2];
        }

        if ($this->type === 'customer_email_changed'
            && preg_match('/alterado para (.+?)\.$/u', $message, $match)) {
            $parameters['email'] = $match[1];
        }

        return $parameters;
    }
}
