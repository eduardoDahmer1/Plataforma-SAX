<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAiPreparation extends Model
{
    public const STATUS_GENERATED = 'generated';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NOT_FOUND = 'not_found';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'product_id',
        'status',
        'sources',
        'confidence',
        'model',
        'error_message',
        'generated_at',
        'completed_at',
    ];

    protected $casts = [
        'sources' => 'array',
        'generated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
