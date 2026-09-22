<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeForwardAttempt extends Model
{
    protected $fillable = [
        'contact_id', 'candidate_name', 'candidate_email', 'store_name',
        'destination', 'source', 'initiated_by', 'canceled_by',
        'status', 'started_at', 'finished_at', 'error',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function canceler()
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }
}
