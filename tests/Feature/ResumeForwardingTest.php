<?php

namespace Tests\Feature;

use App\Mail\ResumeForwardMail;
use App\Jobs\SendResumeToHr;
use App\Models\Contact;
use App\Models\ResumeForwardAttempt;
use App\Models\User;
use App\Services\StoreControlService;
use App\Services\ResumeForwardService;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Controllers\Admin\ContactControllerAdmin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeForwardingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestsDuringMaintenance::class);

        (require database_path('migrations/2014_10_12_000000_create_users_table.php'))->up();
        Schema::table('users', fn (Blueprint $table) => $table->integer('user_type')->default(0));
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->unsignedTinyInteger('contact_type');
            $table->string('attachment')->nullable();
            $table->string('store_name')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_22_120000_add_hr_forwarding_to_contacts_table.php'))->up();
        (require database_path('migrations/2026_09_22_130000_create_resume_forward_attempts_table.php'))->up();
        Storage::fake('public');
        Mail::fake();
        Queue::fake();
        $this->mock(StoreControlService::class, function ($mock) {
            $mock->shouldReceive('isOtica')->andReturn(false);
            $mock->shouldReceive('settings')->andReturn((new StoreControlService())->defaults());
        });
    }

    public function test_new_resumes_go_to_the_selected_store_and_are_recorded(): void
    {
        foreach (Contact::STORES as $key => $store) {
            $this->post(route('contact.store'), [
                'name' => 'Candidata '.$key,
                'email' => $key.'@example.com',
                'phone' => '123456',
                'message' => 'Tenho interesse.',
                'contact_type' => 2,
                'store_name' => $store,
                'attachment' => UploadedFile::fake()->create('curriculo.pdf', 50, 'application/pdf'),
            ])->assertRedirect();

            $contact = Contact::query()->where('email', $key.'@example.com')->firstOrFail();
            $this->assertNull($contact->hr_sent_at);
            $this->assertNotNull($contact->hr_attempted_at);
            $this->assertDatabaseHas('resume_forward_attempts', ['contact_id' => $contact->id, 'status' => 'queued', 'destination' => Contact::HR_EMAILS[$key]]);
        }
        Queue::assertPushed(SendResumeToHr::class, 3);
        $this->runQueuedResumes();

        foreach (Contact::STORES as $key => $store) {
            $contact = Contact::query()->where('email', $key.'@example.com')->firstOrFail();
            $this->assertSame(Contact::HR_EMAILS[$key], $contact->hr_sent_to);
            $this->assertNotNull($contact->hr_sent_at);
            $this->assertDatabaseHas('resume_forward_attempts', ['contact_id' => $contact->id, 'status' => 'sent']);
            Mail::assertSent(ResumeForwardMail::class, fn ($mail) =>
                $mail->contact->is($contact)
                && $mail->hasTo(Contact::HR_EMAILS[$key])
                && count($mail->attachments()) === 1
            );
        }
        Mail::assertSentCount(3);
    }

    public function test_admin_can_send_old_resumes_individually_and_in_bulk_without_resending(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $cde = $this->oldResume('Ciudad del Este');
        $asu = $this->oldResume('Asunción');
        $pjc = $this->oldResume('Pedro Juan Caballero');
        $other = Contact::create(['name' => 'Consulta', 'email' => 'consulta@example.com', 'message' => 'Olá', 'contact_type' => 1]);

        $this->actingAs($admin)->post(route('admin.contacts.send-hr', $cde))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.contacts.bulk'), [
            'action' => 'send_hr',
            'contact_ids' => [$cde->id, $asu->id, $pjc->id, $other->id],
        ])->assertRedirect()->assertSessionHas('hr_progress_ids', [$asu->id, $pjc->id]);

        Queue::assertPushed(SendResumeToHr::class, 3);
        $progressUrl = route('admin.contacts.hr-progress', ['ids' => implode(',', [$cde->id, $asu->id, $pjc->id])]);
        $this->actingAs($admin)->getJson($progressUrl)
            ->assertOk()->assertJson(['total' => 3, 'sent' => 0, 'failed' => 0, 'pending' => 3]);
        Queue::pushed(SendResumeToHr::class)->first()->handle(app(ResumeForwardService::class));
        $this->actingAs($admin)->getJson($progressUrl)
            ->assertOk()->assertJson(['total' => 3, 'sent' => 1, 'failed' => 0, 'pending' => 2]);
        $this->runQueuedResumes();
        $this->actingAs($admin)->getJson($progressUrl)
            ->assertOk()->assertJson(['total' => 3, 'sent' => 3, 'failed' => 0, 'pending' => 0]);
        $this->assertSame('cv.cde@sax.com.py', $cde->fresh()->hr_sent_to);
        $this->assertSame('cv.asu@sax.com.py', $asu->fresh()->hr_sent_to);
        $this->assertSame('cv.pjc@sax.com.py', $pjc->fresh()->hr_sent_to);
        $this->assertNull($other->fresh()->hr_sent_at);
        Mail::assertSentCount(3);
    }

    public function test_missing_old_attachment_stays_pending_with_a_failure_record(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $contact = Contact::create([
            'name' => 'Candidata',
            'email' => 'candidata@example.com',
            'message' => 'Quero trabalhar na SAX.',
            'contact_type' => 2,
            'store_name' => 'Asunción',
            'attachment' => 'attachments/arquivo-ausente.pdf',
        ]);

        $this->actingAs($admin)->post(route('admin.contacts.send-hr', $contact))
            ->assertRedirect()->assertSessionHas('success');

        $this->runQueuedResumes();

        $this->assertNull($contact->fresh()->hr_sent_at);
        $this->assertNotNull($contact->fresh()->hr_attempted_at);
        $this->assertNotNull($contact->fresh()->hr_last_error);
        $this->assertDatabaseHas('resume_forward_attempts', ['contact_id' => $contact->id, 'status' => 'failed']);
        Mail::assertNothingSent();
    }

    public function test_admin_can_cancel_a_queued_resume_but_not_one_already_processing(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $contact = $this->oldResume('Ciudad del Este');
        $this->actingAs($admin)->post(route('admin.contacts.send-hr', $contact))->assertRedirect();
        $attempt = ResumeForwardAttempt::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.contacts.hr-cancel', $attempt))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('canceled', $attempt->fresh()->status);
        $this->assertNull($contact->fresh()->hr_attempted_at);
        $this->actingAs($admin)->getJson(route('admin.contacts.hr-progress', ['ids' => (string) $contact->id]))
            ->assertOk()->assertJson(['total' => 1, 'sent' => 0, 'failed' => 0, 'canceled' => 1, 'pending' => 0]);
        $this->runQueuedResumes();
        Mail::assertNothingSent();

        $this->actingAs($admin)->post(route('admin.contacts.send-hr', $contact))->assertRedirect();
        $processing = ResumeForwardAttempt::query()->latest('id')->firstOrFail();
        $processing->update(['status' => 'processing', 'started_at' => now()]);
        $this->actingAs($admin)->post(route('admin.contacts.hr-cancel', $processing))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame('processing', $processing->fresh()->status);
    }

    public function test_old_resumes_without_a_store_require_an_explicit_destination(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $contact = $this->oldResume('');

        $this->actingAs($admin)->post(route('admin.contacts.send-hr', $contact))
            ->assertSessionHasErrors('store_name');
        Mail::assertNothingSent();

        $this->actingAs($admin)->post(route('admin.contacts.bulk'), [
            'action' => 'send_hr', 'contact_ids' => [$contact->id],
            'store_names' => [$contact->id => 'Pedro Juan Caballero'],
        ])->assertRedirect();

        $this->runQueuedResumes();
        $this->assertSame('cv.pjc@sax.com.py', $contact->fresh()->hr_sent_to);
        Mail::assertSentCount(1);
    }

    public function test_previous_results_are_included_in_the_hr_history(): void
    {
        $sent = $this->oldResume('Asunción');
        $sent->update([
            'hr_attempted_at' => now()->subMinute(),
            'hr_sent_at' => now(),
            'hr_sent_to' => 'cv.asu@sax.com.py',
        ]);
        $failed = $this->oldResume('Ciudad del Este');
        $failed->update(['hr_attempted_at' => now(), 'hr_last_error' => 'Falha no SMTP']);

        (require database_path('migrations/2026_09_22_131000_backfill_resume_forward_attempts.php'))->up();

        $this->assertDatabaseHas('resume_forward_attempts', ['contact_id' => $sent->id, 'status' => 'sent', 'source' => 'legacy']);
        $this->assertDatabaseHas('resume_forward_attempts', ['contact_id' => $failed->id, 'status' => 'failed', 'source' => 'legacy']);
    }

    public function test_admin_can_open_the_hr_queue_and_history_pages(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $contact = $this->oldResume('Ciudad del Este');
        $this->actingAs($admin)->post(route('admin.contacts.send-hr', $contact))->assertRedirect();
        $controller = app(ContactControllerAdmin::class);
        $queue = $controller->index(new Request(['view' => 'hr-queue']));
        $history = $controller->index(new Request(['view' => 'hr-history']));

        $this->assertSame('admin.contacts.index', $queue->getName());
        $this->assertSame('hr-queue', $queue->getData()['panel']);
        $this->assertCount(1, $queue->getData()['hrAttempts']);
        $this->assertSame('hr-history', $history->getData()['panel']);
        $this->assertCount(1, $history->getData()['hrAttempts']);
    }

    private function oldResume(string $store): Contact
    {
        $path = UploadedFile::fake()->create('curriculo.pdf', 50, 'application/pdf')->store('attachments', 'public');

        return Contact::create([
            'name' => 'Candidata',
            'email' => 'candidata@example.com',
            'message' => 'Quero trabalhar na SAX.',
            'contact_type' => 2,
            'store_name' => $store,
            'attachment' => $path,
        ]);
    }

    private function runQueuedResumes(): void
    {
        Queue::pushed(SendResumeToHr::class)->each(
            fn ($job) => $job->handle(app(ResumeForwardService::class))
        );
    }
}
