<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
