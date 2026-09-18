<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = [
        'template_id', 'contact_id', 'created_by', 'type', 'audience', 'subject', 'body',
        'recipient_count', 'sent_count', 'failed_count', 'status', 'started_at', 'finished_at',
    ];

    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];

    public function recipients()
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
