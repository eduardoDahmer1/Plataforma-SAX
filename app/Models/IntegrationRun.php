<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationRun extends Model
{
    protected $fillable = [
        'integration_monitor_id',
        'run_id',
        'status',
        'started_at',
        'finished_at',
        'duration_seconds',
        'error_code',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'integer',
        'metadata' => 'array',
    ];

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(IntegrationMonitor::class, 'integration_monitor_id');
    }
}
