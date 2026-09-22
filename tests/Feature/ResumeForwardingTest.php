<?php

namespace Tests\Feature;

use App\Mail\ResumeForwardMail;
use App\Models\Contact;
use App\Models\User;
use App\Services\StoreControlService;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
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
        Storage::fake('public');
        Mail::fake();
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
            $this->assertSame(Contact::HR_EMAILS[$key], $contact->hr_sent_to);
            $this->assertNotNull($contact->hr_sent_at);
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
        ])->assertRedirect();

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
            ->assertRedirect()->assertSessionHas('error');

        $this->assertNull($contact->fresh()->hr_sent_at);
        $this->assertNotNull($contact->fresh()->hr_attempted_at);
        $this->assertNotNull($contact->fresh()->hr_last_error);
        Mail::assertNothingSent();
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

        $this->assertSame('cv.pjc@sax.com.py', $contact->fresh()->hr_sent_to);
        Mail::assertSentCount(1);
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
}
