<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAiBatchItem extends Model
{
    public const STATUS_READY = 'ready';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NOT_FOUND = 'not_found';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_MISSING = 'missing';

    protected $fillable = [
        'batch_id', 'product_id', 'submitted_sku', 'source_skus', 'target_product_ids', 'status',
        'message', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'source_skus' => 'array',
        'target_product_ids' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(ProductAiBatch::class, 'batch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
