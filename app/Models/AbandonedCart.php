<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $fillable = ['user_id', 'total', 'items_count', 'currency_sign', 'currency_value', 'status', 'abandoned_at', 'restored_at', 'recovery_token', 'help_email_sent_at', 'feedback_reason', 'feedback_message', 'feedback_at'];

    protected $casts = ['abandoned_at' => 'datetime', 'restored_at' => 'datetime', 'help_email_sent_at' => 'datetime', 'feedback_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(AbandonedCartItem::class); }
}
