<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'sign',
        'value',
        'is_default',
        'decimal_separator',
        'thousands_separator',
        'decimal_digits',
        'description',
    ];

    public $timestamps = true;
}
