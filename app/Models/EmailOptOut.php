<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOptOut extends Model
{
    protected $fillable = ['email', 'reason', 'unsubscribed_at'];

    protected $casts = ['unsubscribed_at' => 'datetime'];
}
