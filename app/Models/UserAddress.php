<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'country',
        'postal_code',
        'state',
        'city',
        'street',
        'number',
        'district',
        'complement',
        'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formatted(): string
    {
        return collect([
            trim($this->street . ', ' . $this->number),
            $this->complement ? "({$this->complement})" : null,
            $this->district,
            trim(($this->city ?: '') . ($this->state ? ' - ' . $this->state : '')),
            $this->postal_code,
        ])->filter()->implode(' · ');
    }
}
