<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationMonitor extends Model
{
    protected $fillable = [
        'source',
        'name',
        'status',
        'last_run_id',
        'last_started_at',
        'last_finished_at',
        'last_success_at',
        'last_failure_at',
        'last_heartbeat_at',
        'outage_started_at',
        'consecutive_failures',
        'error_code',
        'error_message',
        'duration_seconds',
        'metadata',
        'last_failure_notification_at',
    ];

    protected $casts = [
        'last_started_at' => 'datetime',
        'last_finished_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'outage_started_at' => 'datetime',
        'last_failure_notification_at' => 'datetime',
        'consecutive_failures' => 'integer',
        'duration_seconds' => 'integer',
        'metadata' => 'array',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(IntegrationRun::class);
    }
}
