<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaign;
use App\Mail\MarketingCampaignMail;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailOptOut;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminEmailMarketingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (require database_path('migrations/2014_10_12_000000_create_users_table.php'))->up();
        Schema::table('users', fn (Blueprint $table) => $table->integer('user_type')->default(0));
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->unsignedTinyInteger('contact_type')->default(1);
            $table->string('attachment')->nullable();
            $table->string('store_name')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_17_110000_add_read_status_to_contacts_table.php'))->up();
        (require database_path('migrations/2025_08_25_160934_create_system_settings_table.php'))->up();
        (require database_path('migrations/2026_09_17_100000_create_email_marketing_tables.php'))->up();
    }

    public function test_admin_can_save_a_reusable_template(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);

        $this->actingAs($admin)->post(route('admin.email-templates.store'), [
            'name' => 'Novidades',
            'subject' => 'Chegaram novidades, {{nome}}',
            'body' => '<h1>Nova coleção</h1><script>alert(1)</script>',
        ])->assertRedirect();

        $template = EmailTemplate::query()->firstOrFail();
        $this->assertSame('Novidades', $template->name);
        $this->assertStringNotContainsString('<script', $template->body);
        $this->assertSame($admin->id, $template->created_by);
    }

    public function test_campaign_resolves_specific_recipients_and_respects_opt_outs(): void
    {
        Bus::fake();
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        EmailOptOut::query()->create([
            'email' => 'nao@exemplo.com',
            'reason' => 'unsubscribe_link',
            'unsubscribed_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.emails.send'), [
            'audience' => 'specific',
            'specific_emails' => "sim@exemplo.com\nnao@exemplo.com\nsim@exemplo.com",
            'subject' => 'Cupom especial',
            'body' => '<p>Olá, {{nome}}</p>',
        ])->assertRedirect(route('admin.contatos.index', ['view' => 'history']));

        $campaign = EmailCampaign::query()->firstOrFail();
        $this->assertSame(1, $campaign->recipient_count);
        $this->assertDatabaseHas('email_campaign_recipients', ['email' => 'sim@exemplo.com']);
        $this->assertDatabaseMissing('email_campaign_recipients', ['email' => 'nao@exemplo.com']);
        Bus::assertDispatched(SendEmailCampaign::class, fn ($job) => $job->campaignId === $campaign->id);
    }

    public function test_admin_can_reply_to_a_contact_even_when_promotional_emails_are_disabled(): void
    {
        Bus::fake();
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $contact = Contact::query()->create([
            'name' => 'Cliente', 'email' => 'cliente@exemplo.com', 'phone' => null,
            'message' => 'Preciso de ajuda', 'contact_type' => 1,
        ]);
        EmailOptOut::query()->create([
            'email' => $contact->email, 'reason' => 'unsubscribe_link', 'unsubscribed_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.emails.send'), [
            'audience' => 'reply',
            'contact_id' => $contact->id,
            'subject' => 'Re: sua dúvida',
            'body' => '<p>Olá, {{nome}}. Como podemos ajudar?</p>',
        ])->assertRedirect();

        $this->assertDatabaseHas('email_campaigns', ['type' => 'reply', 'contact_id' => $contact->id]);
        $this->assertDatabaseHas('email_campaign_recipients', ['email' => $contact->email]);
    }

    public function test_campaign_job_sends_and_updates_the_history(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $campaign = EmailCampaign::query()->create([
            'created_by' => $admin->id,
            'type' => 'marketing',
            'audience' => 'specific',
            'subject' => 'Olá, {{nome}}',
            'body' => '<p>Novidades para você.</p>',
            'recipient_count' => 1,
            'status' => 'pending',
        ]);
        $campaign->recipients()->create([
            'email' => 'cliente@exemplo.com',
            'name' => 'Maria',
            'source' => 'manual',
            'unsubscribe_token' => str_repeat('a', 64),
        ]);

        (new SendEmailCampaign($campaign->id))->handle();

        Mail::assertSent(MarketingCampaignMail::class, fn ($mail) => $mail->renderedSubject === 'Olá, Maria');
        $campaign->refresh();
        $this->assertSame('completed', $campaign->status);
        $this->assertSame(1, $campaign->sent_count);
        $this->assertNotNull($campaign->finished_at);
    }

    public function test_admin_can_update_multiple_contacts_and_open_the_bulk_email_composer(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $contacts = collect([
            $this->contact('Primeiro', 'primeiro@exemplo.com'),
            $this->contact('Segundo', 'segundo@exemplo.com'),
        ]);

        $this->actingAs($admin)->post(route('admin.contacts.bulk'), [
            'action' => 'mark_read',
            'contact_ids' => $contacts->pluck('id')->all(),
        ])->assertRedirect();
        $this->assertSame(0, Contact::query()->whereKey($contacts->pluck('id'))->whereNull('read_at')->count());

        $this->actingAs($admin)->post(route('admin.contacts.bulk'), [
            'action' => 'email',
            'contact_ids' => $contacts->pluck('id')->all(),
        ])->assertRedirect(route('admin.emails.create', ['contacts' => $contacts->pluck('id')->join(',')]));

        $this->actingAs($admin)->post(route('admin.contacts.bulk'), [
            'action' => 'mark_unread',
            'contact_ids' => [$contacts->first()->id],
        ])->assertRedirect();
        $this->assertNull($contacts->first()->refresh()->read_at);

        $this->actingAs($admin)->post(route('admin.contacts.bulk'), [
            'action' => 'delete',
            'contact_ids' => $contacts->pluck('id')->all(),
        ])->assertRedirect();
        $this->assertSame(0, Contact::query()->whereKey($contacts->pluck('id'))->count());
    }

    public function test_opening_and_marking_all_contacts_as_read_updates_the_inbox(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $first = $this->contact('Primeiro', 'primeiro@exemplo.com');
        $second = $this->contact('Segundo', 'segundo@exemplo.com');

        $this->actingAs($admin)->patchJson(route('admin.contacts.read', $first))->assertOk();
        $this->assertNotNull($first->refresh()->read_at);
        $this->assertNull($second->refresh()->read_at);

        $this->actingAs($admin)->post(route('admin.contacts.read-all'))->assertRedirect();
        $this->assertNotNull($second->refresh()->read_at);
    }

    private function contact(string $name, string $email): Contact
    {
        return Contact::query()->create([
            'name' => $name,
            'email' => $email,
            'message' => 'Mensagem de teste',
            'contact_type' => 1,
        ]);
    }
}
