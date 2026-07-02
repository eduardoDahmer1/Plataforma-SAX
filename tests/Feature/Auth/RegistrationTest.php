<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
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

        $response = $this->from('/register')->post('/register', [
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
        $response = $this->from('/register')->post('/register', [
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
}
