<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function protectionPayload(): array
    {
        $response = $this->get('/register');
        preg_match('/name="_registration_token" value="([^"]+)"/', $response->getContent(), $matches);
        $this->travel(3)->seconds();

        return [
            '_registration_token' => html_entity_decode($matches[1] ?? ''),
            'company_website' => '',
        ];
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', $this->protectionPayload() + [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'document' => '123456789',
            'phone_country' => '55',
            'phone_number' => '(11) 99999-9999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('user.profile.edit'));
    }

    public function test_registration_fails_when_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'duplicado@example.com',
        ]);

        $response = $this->from('/register')->post('/register', $this->protectionPayload() + [
            'name' => 'Outro Usuario',
            'email' => 'duplicado@example.com',
            'document' => 'ABC12345',
            'phone_country' => '595',
            'phone_number' => '(595) 981 000 000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
    }

    public function test_registration_fails_when_password_is_weak(): void
    {
        $response = $this->from('/register')->post('/register', $this->protectionPayload() + [
            'name' => 'Usuario Fraco',
            'email' => 'fraco@example.com',
            'document' => 'DOC-12345',
            'phone_country' => '55',
            'phone_number' => '(41) 99888-7766',
            'password' => 'abcdefg',
            'password_confirmation' => 'abcdefg',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
    }

    public function test_registration_blocks_spam_names_before_creating_or_emailing_an_account(): void
    {
        $response = $this->from('/register')->post('/register', $this->protectionPayload() + [
            'name' => 'ACCOUNT ALERT: claim $3000 at https://spam.example/promo',
            'email' => 'victim@example.com',
            'document' => 'ABC12345',
            'phone_country' => '595',
            'phone_number' => '981123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/register')->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', ['email' => 'victim@example.com']);
    }

    public function test_registration_blocks_honeypot_and_missing_form_token(): void
    {
        $payload = [
            'name' => 'Cliente Real',
            'email' => 'cliente@example.com',
            'document' => 'ABC12345',
            'phone_country' => '595',
            'phone_number' => '981123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->from('/register')->post('/register', $payload)
            ->assertRedirect('/register')->assertSessionHasErrors('registration');

        $this->from('/register')->post('/register', array_merge($this->protectionPayload(), $payload, [
            'company_website' => 'https://bot.example',
        ]))->assertRedirect('/register')->assertSessionHasErrors('registration');

        $this->assertDatabaseMissing('users', ['email' => 'cliente@example.com']);
    }

    public function test_enabled_turnstile_is_validated_on_the_server(): void
    {
        config()->set('services.turnstile.enabled', true);
        config()->set('services.turnstile.site_key', 'test-site-key');
        config()->set('services.turnstile.secret_key', 'test-secret-key');
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'register',
            ]),
        ]);

        $this->post('/register', $this->protectionPayload() + [
            'name' => 'Cliente Protegido',
            'email' => 'protegido@example.com',
            'document' => 'ABC12345',
            'phone_country' => '595',
            'phone_number' => '981123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cf-turnstile-response' => 'valid-token',
        ])->assertRedirect(route('user.profile.edit'));

        Http::assertSent(fn ($request) => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'valid-token');
    }
}
