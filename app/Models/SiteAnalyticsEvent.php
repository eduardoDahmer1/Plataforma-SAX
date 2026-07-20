<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteAnalyticsEvent extends Model
{
    protected $fillable = [
        'event_date',
        'event_type',
        'visitor_hash',
        'user_id',
        'path',
        'page_title',
        'target',
        'element_text',
        'device_type',
        'referrer_host',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];
}
