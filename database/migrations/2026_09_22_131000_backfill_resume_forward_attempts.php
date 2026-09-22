<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contacts')
            ->where('contact_type', 2)
            ->where(function ($query) {
                $query->whereNotNull('hr_sent_at')->orWhereNotNull('hr_last_error');
            })
            ->orderBy('id')
            ->chunkById(100, function ($contacts) {
                foreach ($contacts as $contact) {
                    $sent = $contact->hr_sent_at !== null;
                    DB::table('resume_forward_attempts')->insert([
                        'contact_id' => $contact->id,
                        'candidate_name' => $contact->name,
                        'candidate_email' => $contact->email,
                        'store_name' => $contact->store_name,
                        'destination' => $contact->hr_sent_to,
                        'source' => 'legacy',
                        'status' => $sent ? 'sent' : 'failed',
                        'started_at' => $contact->hr_attempted_at,
                        'finished_at' => $contact->hr_sent_at ?: $contact->hr_attempted_at,
                        'error' => $sent ? null : $contact->hr_last_error,
                        'created_at' => $contact->hr_attempted_at ?: $contact->created_at,
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('resume_forward_attempts')->where('source', 'legacy')->delete();
    }
};
