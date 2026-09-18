<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignRecipient;
use App\Models\EmailOptOut;
use Illuminate\Http\Request;

class EmailUnsubscribeController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $recipient = EmailCampaignRecipient::query()->where('unsubscribe_token', $token)->firstOrFail();

        EmailOptOut::query()->updateOrCreate(
            ['email' => mb_strtolower($recipient->email)],
            ['reason' => 'unsubscribe_link', 'unsubscribed_at' => now()],
        );

        return response()->view('emails.unsubscribed', ['email' => $recipient->email]);
    }
}
