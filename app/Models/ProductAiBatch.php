<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAiBatch extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'created_by', 'source_type', 'original_filename', 'status', 'input_count',
        'duplicate_count', 'eligible_count', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ProductAiBatchItem::class, 'batch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function refreshProgress(): void
    {
        $counts = $this->items()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $active = (int) ($counts[ProductAiBatchItem::STATUS_QUEUED] ?? 0)
            + (int) ($counts[ProductAiBatchItem::STATUS_PROCESSING] ?? 0);

        $this->update([
            'status' => $active > 0 ? self::STATUS_PROCESSING : self::STATUS_COMPLETED,
            'completed_at' => $active > 0 ? null : now(),
        ]);
    }
}
