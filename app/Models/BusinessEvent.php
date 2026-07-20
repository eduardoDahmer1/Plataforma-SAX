<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEvent extends Model
{
    protected $fillable = ['category', 'severity', 'user_id', 'order_id', 'title', 'message', 'reference'];

    public function user() { return $this->belongsTo(User::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
